<?php
if (!defined('ABSPATH')) { exit; }

/** Read-only normalization. Evidence of historical opt-in is NOT current subscription permission. */
final class PFI_Integration_Map {
    public static function order($order, int $uid, int $now): array {
        $empty = ['valid'=>false,'paid'=>false,'consent_evidence'=>false,'country'=>'','dispatch_at'=>null,'delivery_at'=>null];
        if (!$order || $uid < 1 || (int)$order->get_customer_id() !== $uid) { return $empty; }
        $created=$order->get_date_created(); $paid=$order->get_date_paid();
        $createdAt=$created ? $created->getTimestamp() : 0;
        $paidAt=$paid ? $paid->getTimestamp() : 0;
        $refunded=(float)$order->get_total_refunded()>0 || $order->get_status()==='refunded';
        $valid=(int)$order->get_id()>0 && $createdAt>0 && $createdAt<=$now;
        return [
            'valid'=>$valid, 'order_id'=>(int)$order->get_id(), 'created_at'=>$createdAt,
            'paid'=>$valid && $order->is_paid() && $paidAt>0 && $paidAt>=$createdAt && $paidAt<=$now && !$refunded,
            'paid_at'=>$paidAt, 'refunded'=>$refunded,
            'country'=>strtoupper((string)$order->get_billing_country()),
            'consent_evidence'=>$order->get_meta('marketing_opt_in_consent',true)==='checkout',
            'consent_source'=>'omnisend-order-metadata',
            // AST date_shipped can be entered when a label is added. Neither label creation nor
            // WooCommerce completed establishes carrier dispatch or customer delivery.
            'tracking_present'=>!empty($order->get_meta('_wc_shipment_tracking_items',true)),
            'dispatch_at'=>null, 'delivery_at'=>null,
            'current_subscription_verified'=>false,
        ];
    }
    public static function gaps(): array {
        return [
            'signup'=>'Active WPCode snippets 46045/46048 collect research acknowledgement and perform OTP signup, but contain no marketing consent/country. Local bridge preserves explicit consent/country through OTP and waits for the verified flag. Production is locked.',
            'checkout_consent'=>'Omnisend order meta marketing_opt_in_consent=checkout proves stored opt-in only; current revocation and PRISM checkout propagation still need verification.',
            'paid'=>'PRISM calls WC_Order::payment_complete(). Require real paid date, matching registered customer and no refund. Guest identity is not mapped.',
            'cart'=>'Local native-table inspection validates registered fixture identity, cart status, inactivity/cool-off, USD amount and native recovery URL without creating queues. A candidate is not a verified abandonment event. Automatic native enrollment and browser recovery remain unverified.',
            'fulfillment'=>'AST stores _wc_shipment_tracking_items. Tracking creation/date_shipped is not verified dispatch or delivery; hold downstream steps.',
            'engagement'=>'Omnisend source has no verified FunnelKit unsubscribe bridge. Local external-status fixtures create sticky suppression; no live webhook/API sync exists yet. Meaningful engagement and support/dispute sources remain unverified.',
            'duplicates'=>'Existing FunnelKit cart and separate recovery email are enabled. Omnisend external flows remain uninspected. No site-wide frequency cap is established.',
        ];
    }
}

/** Only synthetic local recipients; independently rechecked when queued work executes. */
final class PFI_Test_Recipient {
    public static function matches(bool $local, int $uid, string $email, $fixture, $authorized): bool {
        return $local && $uid>0 && $fixture==='1' && is_string($authorized)
            && (bool)filter_var($email,FILTER_VALIDATE_EMAIL)
            && str_ends_with(strtolower($email),'@example.invalid')
            && hash_equals(strtolower($authorized),strtolower($email));
    }
    public static function allowed(int $uid): bool {
        $u=get_userdata($uid);
        return $u && self::matches(PFI_Welcome_Policy::local(),$uid,$u->user_email,
            get_user_meta($uid,'_pfi_test_fixture',true),get_user_meta($uid,'_pfi_test_authorized_email',true));
    }
}

/** No-send rehearsal. No DB, queues, native actions, mail calls or network calls. */
final class PFI_No_Send_Rehearsal {
    public static function evaluate(array $recipient, array $run, string $step, array $state, int $now): array {
        if (!PFI_Test_Recipient::matches(($recipient['local']??false)===true,(int)($recipient['id']??0),
            (string)($recipient['email']??''),$recipient['fixture']??null,$recipient['authorized_email']??null)) {
            return ['mode'=>'no-send','result'=>'stop','reason'=>'recipient_not_allowlisted','transport_invoked'=>false];
        }
        if ($now<=0 || !in_array($run['flow']??'',PFI_Lifecycle_Policy::FLOWS,true)
            || !is_int($run['at']??null) || $run['at']<=0 || $run['at']>$now
            || !is_array($run['context']??null) || !is_array($run['steps']??null)) {
            return ['mode'=>'no-send','result'=>'stop','reason'=>'invalid_fixture','transport_invoked'=>false];
        }
        if (($run['flow']??'')==='post-purchase' && empty($run['context']['paid_at'])) {
            return ['mode'=>'no-send','result'=>'stop','reason'=>'payment_unverified','transport_invoked'=>false];
        }
        $d=PFI_Lifecycle_Policy::decision($run,$step,$state,$now);
        if ($d['result']==='send') { $d['result']='would_send'; }
        return ['mode'=>'no-send','transport_invoked'=>false]+$d;
    }
}

add_action('admin_menu',static function(){
    add_management_page('Puratek Integration Readiness','Puratek Integration Readiness','manage_options','pfi-integration-readiness',static function(){
        if(!current_user_can('manage_options')) { return; }
        echo '<div class="wrap"><h1>Puratek Integration Readiness</h1><p><strong>Production execution remains locked. Imports stay inactive.</strong></p><p>Local native execution requires a synthetic account, its fixture marker and an exact authorized email. Changing the email removes eligibility. No-send rehearsal never invokes email transport or native actions.</p>';
        foreach(PFI_Integration_Map::gaps() as$key=>$text) { echo '<details open><summary><strong>'.esc_html(ucwords(str_replace('_',' ',$key))).'</strong></summary><p>'.esc_html($text).'</p></details>'; }
        if(PFI_Welcome_Policy::local()) {
            $posted=($_SERVER['REQUEST_METHOD']??'')==='POST' && isset($_POST['pfi_flow']);
            if($posted) { check_admin_referer('pfi_no_send_rehearsal'); }
            $flow=$posted?sanitize_key(wp_unslash((string)($_POST['pfi_flow']??''))):'welcome';
            $step=$posted?sanitize_key(wp_unslash((string)($_POST['pfi_step']??''))):'w1';
            $hours=$posted?min(9000,max(0,(int)($_POST['pfi_hours']??0))):0;
            $consent=!$posted || ($_POST['pfi_consent']??'')==='1';
            echo '<h2>No-send rehearsal</h2><p>Evaluate one synthetic step without creating contacts, orders, automations or emails. This is a policy preview, not a provider/queue/delivery test. Sample context: US opted-in test account; a $100 cart with two items; paid order for purchaser flows; no dispatch/delivery evidence. Coupon and campaign approvals are synthetic fixtures.</p>';
            echo '<form method="post">';wp_nonce_field('pfi_no_send_rehearsal');
            echo '<label>Flow <select name="pfi_flow">';foreach(PFI_Lifecycle_Policy::FLOWS as$f){echo '<option value="'.esc_attr($f).'" '.selected($flow,$f,false).'>'.esc_html($f).'</option>';}echo '</select></label> ';
            echo '<label>Step <select name="pfi_step">';foreach(array_unique(array_merge(...array_values(PFI_Lifecycle_Policy::STEPS)))as$choice){echo '<option '.selected($step,$choice,false).'>'.esc_html($choice).'</option>';}echo '</select></label> ';
            echo '<label>Hours since entry <input type="number" min="0" max="9000" name="pfi_hours" value="'.esc_attr((string)$hours).'"></label> <label><input type="checkbox" name="pfi_consent" value="1" '.checked($consent,true,false).'> Synthetic consent present</label> ';
            submit_button('Run no-send rehearsal');echo '</form>';
            if($posted) {
                $start=1800000000;$now=$start+$hours*3600;
                $r=['id'=>'no-send-demo','flow'=>$flow,'at'=>$start,'status'=>'active','steps'=>[],'context'=>['cart_id'=>'demo','paid_at'=>$start,'approved'=>true,'option'=>'b1']];
                $s=['consent'=>($_POST['pfi_consent']??'')==='1','country'=>'US','known_source'=>true,'orders'=>in_array($flow,['welcome','cart','support'],true)?0:1,'last_order_at'=>in_array($flow,['welcome','cart','support'],true)?0:$start,'engaged_at'=>$flow==='winback'?$start:$now,'winback_approved'=>true,'cart_id'=>'demo','cart_total'=>$flow==='support'?501:100,'cart_quantity'=>2,'coupon_valid'=>true,'offer_required'=>true];
                $recipient=['local'=>true,'id'=>1,'email'=>'rehearsal@example.invalid','fixture'=>'1','authorized_email'=>'rehearsal@example.invalid'];
                echo '<h3>Rehearsal result</h3><pre>'.esc_html(wp_json_encode(['flow'=>$flow,'step'=>$step,'hours'=>$hours,'decision'=>PFI_No_Send_Rehearsal::evaluate($recipient,$r,$step,$s,$now)],JSON_PRETTY_PRINT)).'</pre>';
            }
        }
        echo '<p>Local fixture authorization: <code>_pfi_test_fixture = 1</code> plus <code>_pfi_test_authorized_email</code> exactly matching the account email at <code>example.invalid</code>. These fields do not grant marketing consent.</p><p>No provider credentials, live settings or customer records are changed by this page.</p></div>';
    });
});
