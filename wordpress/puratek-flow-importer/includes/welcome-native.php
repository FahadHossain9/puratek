<?php
if (!defined('ABSPATH')) { exit; }

/** Registered through FunnelKit's extension interfaces; no vendor files or license checks changed. */
final class PFI_Welcome_Opt_In extends BWFAN_Event {
    private static $instance;
    public static function get_instance() { return self::$instance ??= new self(); }
    public function __construct() {
        $this->event_name = 'Puratek Welcome — local verified opt-in';
        $this->event_desc = 'Local test profile only. Production consent mapping is not configured.';
        $this->v2 = true; $this->support_v1 = false; $this->event_merge_tag_groups = ['bwf_contact'];
    }
    public function get_slug() { return 'pfi_welcome_opt_in'; }
    public function load_hooks() { add_action('user_register', [$this, 'created'], 99, 1); }
    public function created($uid) {
        if (PFI_Welcome_Policy::eligibility((int)$uid) !== 'eligible') { return; }
        global $wpdb;
        // Lock by identity across revisions: one welcome enrollment per account.
        $lock = 'pfi_welcome_enroll_' . substr(hash('sha256', $wpdb->prefix . ':' . $uid), 0, 36);
        if ((string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,0)', $lock)) !== '1') { return; }
        try {
            if (get_user_meta($uid, '_pfi_welcome_enrolled', true)) { return; }
            $ids = $wpdb->get_col("SELECT ID FROM {$wpdb->prefix}bwfan_automations WHERE event='pfi_welcome_opt_in' AND status=1 ORDER BY ID");
            // Multiple active revisions require operator review, never enroll twice.
            if (count($ids) !== 1) { return; }
            $aid = (int)$ids[0];
            $raw = (new BWFAN_Automation_Controller())->get_automation_data($aid);
            if (($raw['meta']['pfi_profile'] ?? '') !== 'welcome-local-v1') { return; }
            $user = get_userdata($uid);
            $customer = BWFAN_Common::get_bwf_customer($user->user_email, $uid);
            $data = array_merge($raw, $raw['meta'], ['id' => $aid, 'version' => 2]);
            $this->global_data = ['global' => ['user_id' => (int)$uid, 'email' => $user->user_email, 'pfi_enrolled_at' => PFI_Welcome_Policy::now()]];
            $this->event_data = ['event_slug' => $this->get_slug(), 'event_source' => 'wp'];
            if ($this->handle_automation_run_v2($aid, $data)) { update_user_meta($uid, '_pfi_welcome_enrolled', ['aid' => $aid, 'at' => PFI_Welcome_Policy::now()]); }
        } finally { $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock)); }
    }
    public function validate_v2_before_start($row) {
        $data = json_decode($row['data'] ?? '{}', true);
        return PFI_Welcome_Policy::running((int)($row['aid'] ?? 0)) && PFI_Welcome_Policy::eligibility((int)($data['global']['user_id'] ?? 0)) === 'eligible';
    }
}

final class PFI_Welcome_Email extends BWFAN_Action {
    private static $instance;
    public static function get_instance() { return self::$instance ??= new self(); }
    public function __construct() {
        $this->action_name = 'Puratek Welcome email — guarded schedule';
        $this->action_desc = 'W1 immediately; W2 at +48h; W3 at +96h, with eligibility rechecks.';
        $this->support_v2 = true; $this->support_v1 = false;
    }
    public function get_slug() { return 'pfi_welcome_email'; }
    public function get_fields_schema() { return BWFAN_Wp_Sendemail::get_instance()->get_fields_schema(); }
    public function make_v2_data($automation, $step) {
        $email = BWFAN_Wp_Sendemail::get_instance()->make_v2_data($automation, $step);
        $email['pfi_template'] = $step['pfi_template'] ?? '';
        $email['pfi_enrolled_at'] = $automation['global']['pfi_enrolled_at'] ?? 0;
        return $email;
    }
    public function process_v2() {
        if (!PFI_Welcome_Policy::running((int)($this->data['automation_id'] ?? 0)) || !BWFAN_Common::check_for_lks()) { return ['status' => self::$RESPONSE_FAILED, 'message' => 'Active local profile/native license validation required']; }
        global $wpdb;
        $uid = (int)($this->data['user_id'] ?? 0); $template = $this->data['pfi_template'] ?? '';
        $lock = 'pfi_marketing_' . substr(hash('sha256', $wpdb->prefix . ':' . $uid), 0, 40);
        if ((string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,0)', $lock)) !== '1') { return ['status' => self::$RESPONSE_REATTEMPT, 'message' => 'Recipient busy', 'e_time' => time() + 60]; }
        try {
            $ledger = get_user_meta($uid, '_pfi_welcome_delivery', true); $ledger = is_array($ledger) ? $ledger : [];
            $state = ['eligibility' => PFI_Welcome_Policy::eligibility($uid), 'sent' => ($ledger[$template]['state'] ?? '') === 'sent', 'uncertain' => in_array($ledger[$template]['state'] ?? '', ['sending','uncertain'], true),
                'coupon_valid' => PFI_Welcome_Policy::coupon_valid(), 'last_marketing' => (int)get_user_meta($uid, '_pfi_last_marketing_at', true), 'higher_priority_until' => (int)get_user_meta($uid, '_pfi_higher_priority_until', true)];
            $decision = PFI_Welcome_Policy::decision($state, $template, (int)($this->data['pfi_enrolled_at'] ?? 0), PFI_Welcome_Policy::now());
            if ($decision['result'] === 'defer') { return ['status' => self::$RESPONSE_REATTEMPT, 'message' => $decision['reason'], 'e_time' => $decision['at']]; }
            if ($decision['result'] !== 'send') { return ['status' => $decision['result'] === 'stop' ? self::$RESPONSE_FAILED : self::$RESPONSE_SKIPPED, 'message' => $decision['reason']]; }
            $ledger[$template] = ['state' => 'sending', 'at' => PFI_Welcome_Policy::now()];
            if (!update_user_meta($uid, '_pfi_welcome_delivery', $ledger)) { return ['status' => self::$RESPONSE_FAILED, 'message' => 'Cannot reserve delivery']; }
            $native = BWFAN_Wp_Sendemail::get_instance();
            $native->reset_data(); $native->automation_id = $this->data['automation_id'] ?? 0;
            $native->set_data($this->data);
            try { $result = $native->process_v2(); }
            catch (Throwable $e) { $result = ['status' => self::$RESPONSE_FAILED, 'message' => 'Delivery outcome uncertain; review required']; }
            $success = ($result['status'] ?? 0) === self::$RESPONSE_SUCCESS;
            $ledger[$template] = ['state' => $success ? 'sent' : 'uncertain', 'at' => PFI_Welcome_Policy::now()];
            update_user_meta($uid, '_pfi_welcome_delivery', $ledger);
            if ($success) { update_user_meta($uid, '_pfi_last_marketing_at', PFI_Welcome_Policy::now()); }
            // Uncertain outcomes are held for review instead of automatic duplicate sends.
            return $success ? $result : ['status' => self::$RESPONSE_FAILED, 'message' => 'Delivery not confirmed; review required'];
        } finally { $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock)); }
    }
}

BWFAN_Load_Sources::register_events(PFI_Welcome_Opt_In::get_instance());
PFI_Welcome_Opt_In::get_instance()->load_hooks();
BWFAN_Load_Integrations::register_actions(PFI_Welcome_Email::get_instance());
