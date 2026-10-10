<?php
if (!defined('ABSPATH')) { exit; }

/** Local verification profile. A production profile requires verified site integrations. */
final class PFI_Welcome_Policy {
    public static function local(): bool {
        return wp_get_environment_type() === 'local' && strtolower((string)wp_parse_url(home_url(), PHP_URL_HOST)) === 'email-test-puratek.local';
    }
    public static function now(): int {
        return self::local() ? (int)apply_filters('pfi_welcome_local_clock', time()) : time();
    }
    public static function running(int $aid): bool {
        global $wpdb;
        return self::local() && (int)$wpdb->get_var($wpdb->prepare("SELECT status FROM {$wpdb->prefix}bwfan_automations WHERE ID=%d AND event='pfi_welcome_opt_in'", $aid)) === 1;
    }
    public static function eligibility(int $uid): string {
        if (!self::local()) { return 'live_mapping_unverified'; }
        $user = get_userdata($uid);
        if (!$user || !str_ends_with(strtolower($user->user_email), '@example.invalid') || get_user_meta($uid, '_pfi_test_fixture', true) !== '1') { return 'not_local_test_contact'; }
        $consent = get_user_meta($uid, '_pfi_marketing_consent', true);
        if (!is_array($consent) || ($consent['granted'] ?? false) !== true || !is_numeric($consent['at'] ?? null) || (int)$consent['at'] <= 0 || (int)$consent['at'] > self::now() + 300 || ($consent['source'] ?? '') !== 'local-account-opt-in') { return 'consent_missing_or_unknown_source'; }
        if (strtoupper((string)get_user_meta($uid, '_pfi_country', true)) !== 'US') { return 'country_not_us'; }
        if (get_user_meta($uid, '_pfi_suppressed', true) === '1' || get_user_meta($uid, '_pfi_external_suppressed', true) === '1') { return 'suppressed'; }
        $contact = bwf_get_contact('', $user->user_email);
        if ($contact instanceof WooFunnels_Contact && in_array((int)$contact->get_status(), [2,3,4,5], true)) { return 'contact_suppressed'; }
        $unsubscribed = BWFAN_Model_Message_Unsubscribe::get_message_unsubscribe_row(['recipient' => [$user->user_email], 'mode' => 1], false);
        if (!empty($unsubscribed)) { return 'unsubscribed'; }
        if (!function_exists('wc_get_orders')) { return 'orders_unavailable'; }
        $statuses = array_keys(wc_get_order_statuses());
        // Any actual order placed ends welcome, including pending/failed orders. Drafts are excluded.
        foreach ([['customer_id' => $uid], ['billing_email' => $user->user_email]] as $identity) {
            if (wc_get_orders($identity + ['status' => $statuses, 'limit' => 1, 'return' => 'ids'])) { return 'order_placed'; }
        }
        return 'eligible';
    }
    public static function decision(array $state, string $template, int $enrolled, int $now): array {
        $offsets = ['w1' => 0, 'w2' => 172800, 'w3' => 345600];
        if (!isset($offsets[$template]) || $enrolled <= 0) { return ['result' => 'stop', 'reason' => 'invalid_schedule']; }
        if (($state['eligibility'] ?? '') !== 'eligible') { return ['result' => 'stop', 'reason' => $state['eligibility'] ?? 'missing_eligibility']; }
        if (!empty($state['sent'])) { return ['result' => 'skip', 'reason' => 'already_sent']; }
        if (!empty($state['uncertain'])) { return ['result' => 'stop', 'reason' => 'delivery_requires_review']; }
        $due = $enrolled + $offsets[$template];
        if ($now > $due + DAY_IN_SECONDS) { return ['result' => 'skip', 'reason' => 'queue_expired']; }
        if (empty($state['coupon_valid'])) { return ['result' => 'skip', 'reason' => 'coupon_invalid_or_expired']; }
        $next = max($due, (int)($state['last_marketing'] ?? 0) + DAY_IN_SECONDS, (int)($state['higher_priority_until'] ?? 0));
        if ($next > $due + DAY_IN_SECONDS) { return ['result' => 'skip', 'reason' => 'deferral_exceeds_expiry']; }
        if ($now < $next) { return ['result' => 'defer', 'reason' => 'schedule_or_frequency_or_priority', 'at' => $next]; }
        return ['result' => 'send', 'reason' => 'eligible'];
    }
    public static function coupon_valid(): bool {
        if (!class_exists('WC_Coupon')) { return false; }
        $coupon = new WC_Coupon('WELCOME10');
        $expires = $coupon->get_date_expires();
        return $coupon->get_id() > 0 && $coupon->get_discount_type() === 'percent' && (float)$coupon->get_amount() === 10.0
            && $coupon->get_individual_use() && (int)$coupon->get_usage_limit_per_user() === 1
            && (!$expires || $expires->getTimestamp() > self::now())
            && (!$coupon->get_usage_limit() || $coupon->get_usage_count() < $coupon->get_usage_limit());
    }
}

// The synthetic coupon cannot be redeemed by ineligible/returning local test accounts.
add_filter('woocommerce_coupon_is_valid', static function ($valid, $coupon) {
    if (!PFI_Welcome_Policy::local() || strtolower($coupon->get_code()) !== 'welcome10' || $coupon->get_meta('_pfi_test_fixture') !== '1') { return $valid; }
    return $valid && PFI_Welcome_Policy::eligibility(get_current_user_id()) === 'eligible';
}, 20, 2);
