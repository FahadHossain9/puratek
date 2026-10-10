<?php
if (!defined('ABSPATH')) { exit; }

/** Local adapter. External providers must be mapped and validated before a production adapter exists. */
final class PFI_Lifecycle {
    const META='_pfi_lifecycle_runs';
    public static function now(): int { return PFI_Welcome_Policy::now(); }
    public static function allowed(int $uid): bool {
        return PFI_Test_Recipient::allowed($uid);
    }
    public static function runs(int $uid): array { $v=get_user_meta($uid,self::META,true); return is_array($v)?$v:[]; }
    public static function save(int $uid,array $runs): void {
        if($runs===self::runs($uid)) { return; }
        if(!update_user_meta($uid,self::META,$runs)) { throw new RuntimeException('Could not persist lifecycle state; sending stopped.'); }
    }
    public static function locked(int $uid,callable $fn) {
        global $wpdb;
        $key='pfi_marketing_'.substr(hash('sha256',$wpdb->prefix.':'.$uid),0,40);
        if((string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,0)',$key))!=='1') { throw new RuntimeException('Recipient busy; retry event.'); }
        try { return $fn(); } finally { $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$key)); }
    }
    public static function state(int $uid,array $runs=[]): array {
        $s=self::state_without_native_cart($uid,$runs);
        return class_exists('PFI_Native_Cart')?PFI_Native_Cart::refresh($uid,$s):$s;
    }
    public static function state_without_native_cart(int $uid,array $runs=[]): array {
        $c=get_user_meta($uid,'_pfi_marketing_consent',true);$now=self::now();
        $s=get_user_meta($uid,'_pfi_lifecycle_state',true);$s=is_array($s)?$s:[];
        $s['consent']=is_array($c)&&($c['granted']??false)===true&&is_numeric($c['at']??null)&&$c['at']>0&&$c['at']<=$now+300;
        $s['known_source']=($c['source']??'')==='local-account-opt-in';
        $s['country']=strtoupper((string)get_user_meta($uid,'_pfi_country',true));
        $s['suppressed']=!self::allowed($uid)||get_user_meta($uid,'_pfi_suppressed',true)==='1'||get_user_meta($uid,'_pfi_sunset',true)==='1'||get_user_meta($uid,'_pfi_external_suppressed',true)==='1';
        $u=get_userdata($uid);
        if(!$u || !function_exists('wc_get_orders')) { $s['suppressed']=true; return $s; }
        $contact=bwf_get_contact('',$u->user_email);
        if($contact instanceof WooFunnels_Contact && in_array((int)$contact->get_status(),[2,3,4,5],true)) { $s['suppressed']=true; }
        if(BWFAN_Model_Message_Unsubscribe::get_message_unsubscribe_row(['recipient'=>[$u->user_email],'mode'=>1],false)) { $s['suppressed']=true; }
        $orders=[];
        foreach([['customer_id'=>$uid],['billing_email'=>$u->user_email]]as$identity) {
            foreach(wc_get_orders($identity+['status'=>array_keys(wc_get_order_statuses()),'limit'=>-1])as$o) { $orders[$o->get_id()]=$o; }
        }
        $s['orders']=count($orders);$s['last_order_at']=0;
        foreach($orders as$o) {
            $s['last_order_at']=max($s['last_order_at'],$o->get_date_created()?$o->get_date_created()->getTimestamp():0);
            // Re-read actual refund state at send time; a missed provider callback cannot release it.
            if((float)$o->get_total_refunded()>0 || $o->get_status()==='refunded') { $s['refund']=true; }
        }
        $s['last_marketing']=(int)get_user_meta($uid,'_pfi_last_marketing_at',true);
        $s['uncertain_delivery']=false;
        $s['last_broadcast']=0;
        foreach($runs as$r) { foreach($r['steps'] as$step) {
            if(($step['state']??'')==='sent') { $s['last_marketing']=max($s['last_marketing'],(int)$step['sent_at']); }
            if($r['flow']==='broadcast'&&($step['state']??'')==='sent') { $s['last_broadcast']=max($s['last_broadcast'],(int)$step['sent_at']); }
            if(in_array($step['state']??'', ['sending','uncertain'],true)) { $s['uncertain_delivery']=true; }
        } }
        $s['active_welcome_cart']=false;
        foreach($runs as$r) { if($r['status']==='active'&&in_array($r['flow'],['welcome','cart','support'],true)&&self::active_id($r['flow'])===$r['aid']&&PFI_Lifecycle_Policy::eligibility($r,$s,$now)==='eligible') { $s['active_welcome_cart']=true; } }
        return $s;
    }
    public static function active_id(string $flow): int {
        global $wpdb;
        $ids=$wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}bwfan_automations WHERE event=%s AND status=1",'pfi_lifecycle_'.str_replace('-','_',$flow)));
        if(count($ids)!==1) { return 0; }
        // Legacy Welcome cannot execute alongside the new shared coordinator.
        if($wpdb->get_var("SELECT ID FROM {$wpdb->prefix}bwfan_automations WHERE event='pfi_welcome_opt_in' AND status=1 LIMIT 1")) { return 0; }
        $p=(new BWFAN_Automation_Controller())->get_automation_data((int)$ids[0]);
        return ($p['meta']['pfi_profile']??'')==='lifecycle-local-v1'?(int)$ids[0]:0;
    }
    public static function enroll(int $uid,string $flow,array $ctx,int $at,string $eventId): string {
        if(!self::allowed($uid)||!in_array($flow,PFI_Lifecycle_Policy::FLOWS,true)||$at<=0||$at>self::now()+300||!preg_match('/^[a-zA-Z0-9_.:-]{1,100}$/',$eventId)) { return ''; }
        return self::locked($uid,static function()use($uid,$flow,$ctx,$at,$eventId){
            $runs=self::runs($uid);$id=substr(hash('sha256',$flow.':'.$eventId),0,32);
            if(isset($runs[$id])) { return $id; }
            foreach($runs as$r) {
                $same=$r['flow']===$flow || (in_array($flow,['cart','support'],true)&&in_array($r['flow'],['cart','support'],true));
                if($same && (int)$r['at']+PFI_Lifecycle_Policy::REENTRY[$flow]>$at) { return ''; }
            }
            if(count($runs)>=250) { throw new RuntimeException('Local run history limit reached; review required.'); }
            $aid=self::active_id($flow);if(!$aid) { return ''; }
            $r=['id'=>$id,'flow'=>$flow,'at'=>$at,'context'=>$ctx,'aid'=>$aid,'status'=>'active','steps'=>[]];
            $s=self::state($uid,$runs);$eligible=PFI_Lifecycle_Policy::eligibility($r,$s,self::now());
            if(!in_array($eligible,['eligible','hold_active_flow','hold_support','hold_approval'],true)) { return ''; }
            // Persist identity before calling native hooks. A failed/uncertain enrollment is held, never blindly retried.
            $runs[$id]=$r;self::save($uid,$runs);
            try { $ok=PFI_Lifecycle_Event::instance($flow)->enroll($uid,$r); }
            catch(Throwable $e) { $ok=false; }
            if(!$ok) { $runs[$id]['status']='enrollment_review';self::save($uid,$runs);return ''; }
            return $id;
        });
    }
    public static function event($uid,$event,$payload=[]): void {
        $uid=(int)$uid;if(!self::allowed($uid)||!is_array($payload)) { return; }
        $now=self::now();$event=(string)$event;
        // This PHP hook is a local integration contract, not an unauthenticated HTTP endpoint.
        if($event==='account') { self::enroll($uid,'welcome',[],$now,'account-'.$uid); return; }
        if($event==='cart') {
            foreach(['cart_id','cart_total','cart_quantity','restore_url','items']as$key) { if(!isset($payload[$key])) { throw new InvalidArgumentException('Cart event missing '.$key); } }
            $url=wp_parse_url($payload['restore_url']);
            if(($url['host']??'')!=='email-test-puratek.local'||!in_array($url['scheme']??'',['http','https'],true)) { throw new InvalidArgumentException('Local verified restore URL required.'); }
            if(!is_array($payload['items'])||!$payload['items']||count($payload['items'])>50||$payload['cart_total']<0||$payload['cart_quantity']<1) { throw new InvalidArgumentException('Invalid cart contents.'); }
            self::locked($uid,static function()use($uid,$payload){$s=get_user_meta($uid,'_pfi_lifecycle_state',true);$s=is_array($s)?$s:[];update_user_meta($uid,'_pfi_lifecycle_state',array_merge($s,$payload,['cart_empty'=>false,'cart_recovered'=>false]));});
            $flow=$payload['cart_total']>500||$payload['cart_quantity']>=10?'support':'cart';
            self::enroll($uid,$flow,$payload,$now,'cart-'.$payload['cart_id']);return;
        }
        if($event==='broadcast') {
            if(empty($payload['campaign_id'])||empty($payload['approved'])||!in_array($payload['option']??'', ['b1','b2','b3'],true)) { throw new InvalidArgumentException('Approved campaign ID and one broadcast option required.'); }
            self::enroll($uid,'broadcast',$payload,$now,'broadcast-'.$payload['campaign_id']);return;
        }
        if($event==='paid') {
            $o=wc_get_order((int)($payload['order_id']??0));$mapped=PFI_Integration_Map::order($o,$uid,$now);
            if(empty($mapped['paid'])) { return; }
            $at=$mapped['created_at'];$paid=$mapped['paid_at'];
            self::enroll($uid,'post-purchase',['order_id'=>$o->get_id(),'paid_at'=>$paid],$at,'order-'.$o->get_id());
            self::enroll($uid,'repeat',[],$at,'repeat-'.$o->get_id());
            $s=self::state($uid);if(!empty($s['engaged_at'])) { self::enroll($uid,'winback',[],(int)$s['engaged_at'],'inactive-'.$s['engaged_at']); }return;
        }
        self::locked($uid,static function()use($uid,$event,$payload,$now){
            $runs=self::runs($uid);$s=get_user_meta($uid,'_pfi_lifecycle_state',true);$s=is_array($s)?$s:[];
            if(in_array($event,['dispatch','delivery'],true)) {
                $o=wc_get_order((int)($payload['order_id']??0));if(!$o||(int)$o->get_customer_id()!==$uid||!$o->is_paid()) { throw new InvalidArgumentException('Verified paid order required.'); }
                $at=(int)($payload['at']??$now);if($at<=0||$at>$now) { throw new InvalidArgumentException('Invalid verified event timestamp.'); }
                foreach($runs as&$r) { if($r['flow']==='post-purchase'&&($r['context']['order_id']??0)===$o->get_id()) {
                    if($at<(int)$r['context']['paid_at']) { throw new InvalidArgumentException('Fulfillment event predates payment.'); }
                    $key=$event.'_at';if(!empty($r['context'][$key])&&$r['context'][$key]!==$at){throw new InvalidArgumentException('Conflicting event timestamp.');}$r['context'][$key]=$at;
                }}unset($r);self::save($uid,$runs);return;
            }
            if($event==='engagement') { $s['engaged_at']=$now; }
            elseif($event==='cart_recovered') { $s['cart_recovered']=true; }
            elseif($event==='cart_empty') { $s['cart_empty']=true; }
            elseif(in_array($event,['support_issue','refund','dispute','winback_approved'],true)) { $s[$event]=($payload['value']??true)===true; }
            else { throw new InvalidArgumentException('Unsupported lifecycle event.'); }
            update_user_meta($uid,'_pfi_lifecycle_state',$s);
        });
    }
    public static function offer(int $uid,array $r,string $template): bool {
        if(in_array($template,['w1','w2','w3'],true)) { return PFI_Welcome_Policy::coupon_valid(); }
        if(!in_array($template,['c2a','b3'],true)) { return true; }
        $c=new WC_Coupon($r['context']['coupon_code']??'');$expires=$c->get_date_expires();$u=get_userdata($uid);
        if(!$c->get_id()||!$expires||$expires->getTimestamp()<=self::now()||!$c->get_individual_use()||(int)$c->get_usage_limit_per_user()!==1||($c->get_usage_limit()&&$c->get_usage_count()>=$c->get_usage_limit())) { return false; }
        if($template==='c2a') {
            return $c->get_meta('_pfi_first_order_only')==='1'&&$c->get_discount_type()==='percent'&&(float)$c->get_amount()===10.0&&(int)$c->get_usage_limit()===1&&in_array(strtolower($u->user_email),array_map('strtolower',$c->get_email_restrictions()),true)&&$expires->getTimestamp()===(int)$r['at']+604800;
        }
        return !empty($r['context']['promotion_approved'])&&$c->get_discount_type()==='percent'&&(float)$c->get_amount()===10.0;
    }
    public static function decision(int $uid,array $r,string $step,array $runs): array {
        $s=self::state($uid,$runs);$now=self::now();
        $template=$step==='c2'?(($s['orders']??0)>0?'c2b':'c2a'):($step==='b'?($r['context']['option']??''):$step);
        $s['offer_required']=in_array($template,['w1','w2','w3','c2a','b3'],true);$s['coupon_valid']=self::offer($uid,$r,$template);
        foreach($runs as$other) {
            if($other['id']===$r['id']||self::active_id($other['flow'])!==$other['aid']||PFI_Lifecycle_Policy::rank($other)>=PFI_Lifecycle_Policy::rank($r)) { continue; }
            foreach(PFI_Lifecycle_Policy::STEPS[$other['flow']]as$next) {
                if(in_array($other['steps'][$next]['state']??'', ['sent','skipped'],true)) { continue; }
                $os=$s;$ot=$next==='c2'?($s['orders']>0?'c2b':'c2a'):($next==='b'?($other['context']['option']??''):$next);
                $os['offer_required']=in_array($ot,['w1','w2','w3','c2a','b3'],true);$os['coupon_valid']=self::offer($uid,$other,$ot);
                if(PFI_Lifecycle_Policy::decision($other,$next,$os,$now)['result']==='send') { $s['higher_priority_due']=true; }break;
            }
        }
        return PFI_Lifecycle_Policy::decision($r,$step,$s,$now)+['template'=>$template];
    }
    public static function hydrate(array $email,array $r,string $template): array {
        $c=$r['context'];$rows='';
        foreach(($c['items']??[])as$item) {
            $rows.='<tr><td>'.esc_html($item['name']??'').'</td><td>'.esc_html($item['size']??'').'</td><td>'.(int)($item['quantity']??0).'</td><td>'.esc_html($item['price']??'').'</td></tr>';
        }
        $coupon=new WC_Coupon($c['coupon_code']??'');$expiry=$coupon->get_date_expires();
        $map=['PFI_RUNTIME_CART_ROWS'=>$rows,'PFI_RUNTIME_CART_SUBTOTAL'=>esc_html(number_format((float)($c['cart_total']??0),2).' USD'),'PFI_RUNTIME_CART_URL'=>esc_url($c['restore_url']??''),'PFI_RUNTIME_COUPON'=>esc_html($c['coupon_code']??''),'PFI_RUNTIME_EXPIRY'=>esc_html($expiry?gmdate('Y-m-d H:i',$expiry->getTimestamp()).' UTC':'')];
        $email['template']=str_replace(array_keys($map),array_values($map),$email['template']);
        if (($email['mode'] ?? 0) === 5 && isset($email['data']['block'])) { $email['data']['block']['template'] = $email['template']; }
        if(preg_match('/PFI_RUNTIME_|%%|__CART_/',$email['template'])) { throw new RuntimeException('Unresolved lifecycle email content.'); }
        return $email;
    }
}

add_action('pfi_lifecycle_event',[PFI_Lifecycle::class,'event'],10,3);
add_action('user_register',static function($uid){PFI_Lifecycle::event($uid,'account');},100);
add_action('woocommerce_payment_complete',static function($id){$o=wc_get_order($id);if($o){PFI_Lifecycle::event($o->get_customer_id(),'paid',['order_id'=>$id]);}},100);

add_filter('woocommerce_coupon_is_valid',static function($valid,$coupon){
    if(!PFI_Welcome_Policy::local()||$coupon->get_meta('_pfi_first_order_only')!=='1'){return $valid;}
    $uid=get_current_user_id();return $valid&&PFI_Lifecycle::allowed($uid)&&empty(PFI_Lifecycle::state($uid)['orders']);
},21,2);
