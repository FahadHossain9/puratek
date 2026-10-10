<?php
if (!defined('ABSPATH')) { exit; }

/** Deterministic policy shared by all seven local lifecycle flows. Times are UTC seconds. */
final class PFI_Lifecycle_Policy {
    const DAY = 86400;
    const FLOWS = ['welcome','cart','support','post-purchase','repeat','winback','broadcast'];
    const STEPS = ['welcome'=>['w1','w2','w3'],'cart'=>['c1','c2','c3'],'support'=>['h1'],'post-purchase'=>['p1','p2','p3','p4'],'repeat'=>['r1','r2','r3'],'winback'=>['x1','x2','x3'],'broadcast'=>['b']];
    const PRIORITY = ['cart'=>1,'support'=>1,'welcome'=>2,'post-purchase'=>3,'repeat'=>4,'winback'=>4,'broadcast'=>5];
    const REENTRY = ['welcome'=>3153600000,'cart'=>604800,'support'=>604800,'post-purchase'=>86400,'repeat'=>7776000,'winback'=>31536000,'broadcast'=>604800];
    public static function due(array $r, string $step): ?int {
        $c=$r['context']; $start=(int)$r['at'];
        $fixed=['w1'=>0,'w2'=>172800,'w3'=>345600,'c1'=>3600,'c2'=>86400,'c3'=>172800,'h1'=>3600,'r1'=>2592000,'r2'=>5184000,'r3'=>7776000,'x1'=>7776000,'b'=>0];
        if(isset($fixed[$step])) { return $start+$fixed[$step]; }
        if($step==='p1') { return (int)$c['paid_at']+7200; }
        if($step==='p2') { return empty($c['dispatch_at'])?null:(int)$c['dispatch_at']+86400; }
        if(in_array($step,['p3','p4'],true)) { return empty($c['delivery_at'])?null:(int)$c['delivery_at']+($step==='p3'?604800:1814400); }
        if(in_array($step,['x2','x3'],true)) { return empty($r['steps']['x1']['sent_at'])?null:(int)$r['steps']['x1']['sent_at']+($step==='x2'?604800:1209600); }
        return null;
    }
    public static function eligibility(array $r, array $s, int $now): string {
        if(!empty($s['uncertain_delivery'])) { return 'delivery_review'; }
        if(empty($s['consent']) || ($s['country']??'')!=='US' || empty($s['known_source']) || !empty($s['suppressed'])) { return 'suppressed'; }
        $flow=$r['flow']; $c=$r['context'];
        if(in_array($flow,['repeat','winback'],true)&&empty($s['orders'])) { return 'not_purchaser'; }
        if($flow==='welcome' && ($s['orders']??0)>0) { return 'order_exit'; }
        if(in_array($flow,['cart','support'],true)) {
            if(!empty($s['cart_empty']) || !empty($s['cart_recovered']) || ($s['cart_id']??'')!==($c['cart_id']??'')) { return 'cart_exit'; }
            if(($s['last_order_at']??0)>=(int)$r['at']) { return 'order_exit'; }
            $high=($s['cart_total']??0)>500 || ($s['cart_quantity']??0)>=10;
            if($flow==='cart' && $high) { return 'route_support'; }
            // High-value qualification is sticky for this cart: never send both sequences.
        }
        if(in_array($flow,['post-purchase','repeat','winback'],true)) {
            if(($s['last_order_at']??0)>(int)$r['at']) { return 'new_order_exit'; }
            if(!empty($s['support_issue']) || !empty($s['refund']) || !empty($s['dispute'])) { return 'hold_support'; }
        }
        if(in_array($flow,['repeat','broadcast'],true) && (empty($s['engaged_at']) || $now-(int)$s['engaged_at']>7776000)) { return 'inactive_exit'; }
        if($flow==='winback') {
            if(empty($s['winback_approved'])) { return 'hold_approval'; }
            if(($s['engaged_at']??0)>(int)$r['at']) { return 'engagement_exit'; }
        }
        if($flow==='broadcast') {
            if(empty($c['approved']) || !in_array($c['option']??'', ['b1','b2','b3'],true)) { return 'hold_approval'; }
            if(!empty($s['active_welcome_cart'])) { return 'hold_active_flow'; }
        }
        return 'eligible';
    }
    public static function decision(array $r, string $step, array $s, int $now): array {
        $result=static fn($v,$reason,$at=0)=>['result'=>$v,'reason'=>$reason,'at'=>$at];
        if(!in_array($step,self::STEPS[$r['flow']]??[],true)) { return $result('stop','invalid_step'); }
        $entry=$r['steps'][$step]??[];
        if(in_array($entry['state']??'', ['sent','skipped'],true)) { return $result('skip','already_finished'); }
        if(in_array($entry['state']??'', ['sending','uncertain'],true)) { return $result('stop','uncertain_delivery'); }
        if(($r['status']??'')!=='active') { return $result('stop','run_not_active'); }
        $eligible=self::eligibility($r,$s,$now);
        if(!in_array($eligible,['eligible','hold_support','hold_approval','hold_active_flow'],true)) { return $result('stop',$eligible); }
        $due=self::due($r,$step);
        if($due===null) {
            if(in_array($step,['x2','x3'],true)) { return $result('stop','x1_not_delivered'); }
            return $now>(int)$r['at']+15552000 ? $result('stop','missing_event_timeout') : $result('defer','waiting_verified_event',$now+3600);
        }
        if($now>$due+self::DAY) { return $result('skip','queue_expired'); }
        $next=max($due,empty($s['last_marketing'])?0:(int)$s['last_marketing']+self::DAY);
        if($r['flow']==='broadcast'&&!empty($s['last_broadcast'])) { $next=max($next,(int)$s['last_broadcast']+604800); }
        if($eligible!=='eligible') { $next=max($next,$now+3600); }
        if(!empty($s['higher_priority_due'])) { $next=max($next,$now+60); }
        if($next>$due+self::DAY) { return $result('skip','deferral_exceeds_expiry'); }
        if($next>$now) { return $result('defer',$eligible==='eligible'?'schedule_cap_or_priority':$eligible,$next); }
        if(!empty($s['offer_required']) && empty($s['coupon_valid'])) { return $result('skip','invalid_offer'); }
        return $result('send','eligible');
    }
    public static function rank(array $r): array { return [self::PRIORITY[$r['flow']], $r['at'], $r['flow'], $r['id']]; }
}
