<?php
if (!defined('ABSPATH')) { exit; }

/** Read-only cart inspection. A candidate is NOT a native abandonment event or permission to send. */
final class PFI_Cart_Map {
    private static function timestamp($value): int {
        if (!is_string($value)) { return 0; }
        $d=DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',$value,new DateTimeZone('UTC'));
        return $d && $d->format('Y-m-d H:i:s')===$value ? $d->getTimestamp() : 0;
    }
    private static function integer($value, int $min, int $max): bool {
        return (is_int($value)||is_string($value)) && preg_match('/^\d+$/',(string)$value)
            && (float)$value >= $min && (float)$value <= $max;
    }
    public static function inspect(array $row, array $recipient, array $settings, int $lastOrder, int $now, callable $urlBuilder): array {
        $base=['mode'=>'no-send','transport_invoked'=>false,'queue_created'=>false,'abandoned_at'=>null];
        $hold=static fn(string $reason)=>$base+['result'=>'hold','reason'=>$reason];
        if (!PFI_Test_Recipient::matches(($recipient['local']??false)===true,(int)($recipient['id']??0),
            (string)($recipient['email']??''),$recipient['fixture']??null,$recipient['authorized_email']??null)) { return $hold('recipient_not_allowlisted'); }
        if (!$row) { return $hold('cart_missing'); }
        if (!self::integer($row['ID']??null,1,PHP_INT_MAX) || !self::integer($row['user_id']??null,1,PHP_INT_MAX)
            || (int)$row['user_id']!==(int)$recipient['id'] || !is_string($row['email']??null)
            || strtolower($row['email'])!==strtolower($recipient['email'])) { return $hold('cart_identity_mismatch'); }
        if (!self::integer($row['status']??null,0,5)) { return $hold('unknown_cart_status'); }
        // Never treat another automation's in-progress row as a new independent enrollment.
        if (!in_array((int)$row['status'],[0,4],true)) { return $hold('cart_status_'.(int)$row['status']); }
        if (!self::integer($row['order_id']??null,0,PHP_INT_MAX) || (int)$row['order_id']!==0) { return $hold('order_linked_cart'); }
        if (!in_array($settings['bwfan_ab_enable']??null,[1,'1',true],true)) { return $hold('tracking_disabled'); }
        foreach (['bwfan_ab_init_wait_time'=>525600,'bwfan_disable_abandonment_days'=>3650,'bwfan_ab_mark_lost_cart'=>3650] as $key=>$max) {
            if (!self::integer($settings[$key]??null,0,$max)) { return $hold('unknown_cart_settings'); }
        }
        $modified=self::timestamp($row['last_modified']??null);$created=self::timestamp($row['created_time']??null);
        if ($now<=0 || $created<=0 || $modified<$created || $modified>$now || $lastOrder<0 || $lastOrder>$now) { return $hold('invalid_cart_clock'); }
        $candidateAt=$modified+(int)$settings['bwfan_ab_init_wait_time']*60;
        if ($now<$candidateAt) { return $hold('inactivity_wait'); }
        // Conservative age hold; this does not replicate FunnelKit's scheduled lost-cart worker.
        $lostDays=(int)$settings['bwfan_ab_mark_lost_cart'];
        if ($lostDays<1 || $now-$modified >= $lostDays*86400) { return $hold('cart_age_review'); }
        $cooloff=(int)$settings['bwfan_disable_abandonment_days'];
        if ($lastOrder>0 && ($lastOrder>=$modified || ($cooloff>0 && $lastOrder >= $now-$cooloff*86400))) { return $hold('order_cooloff'); }
        if (($row['currency']??'')!=='USD') { return $hold('currency_not_usd'); }
        if (!is_scalar($row['total']??null) || is_bool($row['total']) || !is_numeric($row['total'])
            || !is_finite((float)$row['total']) || (float)$row['total']<0) { return $hold('invalid_cart_total'); }
        $raw=$row['items']??null;
        if (!is_string($raw)||strlen($raw)>1048576) { return $hold('invalid_cart_items'); }
        // Native WC carts can contain product objects in `data`. Do not instantiate any serialized class.
        $items=@unserialize($raw,['allowed_classes'=>false,'max_depth'=>32]);
        if (!is_array($items)||!$items||count($items)>50) { return $hold('empty_or_invalid_cart'); }
        $quantity=0;$products=[];
        foreach ($items as $item) {
            if (!is_array($item)||!self::integer($item['product_id']??null,1,PHP_INT_MAX)
                || !self::integer($item['quantity']??null,1,10000)
                || !self::integer($item['variation_id']??0,0,PHP_INT_MAX)) { return $hold('invalid_cart_item'); }
            $quantity+=(int)$item['quantity'];
            $products[]=['product_id'=>(int)$item['product_id'],'variation_id'=>(int)($item['variation_id']??0),'quantity'=>(int)$item['quantity']];
        }
        if (!is_string($row['token']??null)||!preg_match('/^[a-zA-Z0-9]{32}$/',$row['token'])) { return $hold('invalid_recovery_token'); }
        if (!is_string($row['checkout_data']??null) || strlen($row['checkout_data'])>1048576) { return $hold('invalid_checkout_data'); }
        $checkout=json_decode($row['checkout_data'],true);
        if (!is_array($checkout)) { return $hold('invalid_checkout_data'); }
        if (isset($checkout['lang'])&&!is_string($checkout['lang'])) { return $hold('invalid_checkout_data'); }
        try { $url=$urlBuilder($row['token'],'',$checkout['lang']??'',$checkout); }
        catch (Throwable $e) { return $hold('recovery_builder_failed'); }
        $parts=is_string($url)?parse_url($url):false;$query=[];
        if (is_array($parts)) { parse_str($parts['query']??'',$query); }
        if (!is_array($parts)||!in_array($parts['scheme']??'',['http','https'],true)
            || strtolower($parts['host']??'')!=='email-test-puratek.local' || isset($parts['user']) || isset($parts['pass'])
            || isset($parts['port']) || ($query['bwfan-ab-id']??null)!==$row['token']) { return $hold('unsafe_recovery_url'); }
        return $base+['result'=>'candidate_only','reason'=>'native_event_and_consent_still_required','cart_id'=>(int)$row['ID'],
            'candidate_after'=>$candidateAt,'proposed_flow'=>(float)$row['total']>500||$quantity>=10?'support':'cart',
            'cart_total'=>(float)$row['total'],'currency'=>'USD','cart_quantity'=>$quantity,'products'=>$products,
            // Private result for local verification only; admin summary redacts this bearer link.
            'restore_url'=>$url,'native_event_verified'=>false,'current_subscription_verified'=>false];
    }
    public static function local(int $uid,int $cartId): array {
        $held=['mode'=>'no-send','result'=>'hold','reason'=>'local_fixture_required','transport_invoked'=>false,'queue_created'=>false];
        if (!PFI_Test_Recipient::allowed($uid)||$cartId<1) { return $held; }
        if (!class_exists('BWFAN_Model_Abandonedcarts')||!class_exists('BWFAN_Common')||!function_exists('wc_get_orders')) { $held['reason']='dependencies_missing';return $held; }
        $u=get_userdata($uid);$latest=0;
        foreach ([['customer_id'=>$uid],['billing_email'=>$u->user_email]] as $identity) {
            foreach (wc_get_orders($identity+['status'=>array_keys(wc_get_order_statuses()),'limit'=>-1]) as $order) {
                $created=$order->get_date_created();if($created)$latest=max($latest,$created->getTimestamp());
            }
        }
        $recipient=['local'=>true,'id'=>$uid,'email'=>$u->user_email,'fixture'=>get_user_meta($uid,'_pfi_test_fixture',true),'authorized_email'=>get_user_meta($uid,'_pfi_test_authorized_email',true)];
        $row=BWFAN_Model_Abandonedcarts::get($cartId);
        return self::inspect(is_array($row)?$row:[],$recipient,BWFAN_Common::get_global_settings(),$latest,time(),[BWFAN_Common::class,'wc_get_cart_recovery_url']);
    }
    public static function summary(array $result): array {
        unset($result['restore_url']);
        return $result;
    }
}

add_action('admin_menu',static function(){
    if (!PFI_Welcome_Policy::local()) { return; }
    add_management_page('Puratek Cart Inspection','Puratek Cart Inspection','manage_options','pfi-cart-inspection',static function(){
        if (!current_user_can('manage_options')||!PFI_Welcome_Policy::local()) { return; }
        echo '<div class="wrap"><h1>Puratek Cart Inspection</h1><p>Read a local synthetic customer’s native FunnelKit cart. This preview creates no queue, changes no cart and sends no email. A candidate still needs a verified native abandonment event and consent. Recovery tokens are hidden.</p><form method="post">';
        wp_nonce_field('pfi_cart_inspection');
        echo '<label>Synthetic user ID <input type="number" min="1" name="pfi_cart_uid" required></label> <label>Native cart ID <input type="number" min="1" name="pfi_cart_id" required></label>';
        submit_button('Inspect without sending');echo '</form>';
        if (($_SERVER['REQUEST_METHOD']??'')==='POST') {
            check_admin_referer('pfi_cart_inspection');
            $r=PFI_Cart_Map::local(absint($_POST['pfi_cart_uid']??0),absint($_POST['pfi_cart_id']??0));
            echo '<pre>'.esc_html(wp_json_encode(PFI_Cart_Map::summary($r),JSON_PRETTY_PRINT)).'</pre>';
        }
        echo '</div>';
    });
});
