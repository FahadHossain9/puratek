<?php
if(!defined('ABSPATH'))exit;

/** Local native-event adapter. No native trigger automation is installed/activated by this file. */
final class PFI_Native_Cart {
 public static function payload(int $uid,int $cartId): array {
  if(!PFI_Test_Recipient::allowed($uid))throw new RuntimeException('fixture_required');
  $row=BWFAN_Model_Abandonedcarts::get($cartId);
  if(!is_array($row)||!in_array((int)$row['status'],[0,1,4],true))throw new RuntimeException('cart_unavailable');
  global $wpdb;$u=get_userdata($uid);
  $observation=get_user_meta($uid,PFI_Cart_Observation::META,true);
  $reason=PFI_Cart_Observation::reason(is_array($observation)?$observation:[],$row,$u->user_email);
  if($reason!=='')throw new RuntimeException($reason);
  $newer=$wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}bwfan_abandonedcarts WHERE ID>%d AND (user_id=%d OR email=%s) LIMIT 1",$cartId,$uid,$u->user_email));
  if($wpdb->last_error!==''||$newer)throw new RuntimeException('cart_replaced_or_unreadable');
  // In-progress is accepted only by this local native adapter, never by the generic new-entry inspector.
  $row['status']=0;$u=get_userdata($uid);$s=PFI_Lifecycle::state_without_native_cart($uid);
  $who=['local'=>true,'id'=>$uid,'email'=>$u->user_email,'fixture'=>'1','authorized_email'=>$u->user_email];
  $m=PFI_Cart_Map::inspect($row,$who,BWFAN_Common::get_global_settings(),(int)($s['last_order_at']??0),PFI_Lifecycle::now(),[BWFAN_Common::class,'wc_get_cart_recovery_url']);
  if(($m['result']??'')!=='candidate_only')throw new RuntimeException($m['reason']??'cart_invalid');
  $items=[];
  foreach($m['products']as$item){$p=wc_get_product($item['variation_id']?:$item['product_id']);if(!$p||!$p->is_purchasable()||!$p->is_in_stock()||!$p->has_enough_stock($item['quantity']))throw new RuntimeException('product_unavailable');
   $items[]=['name'=>$p->get_name(),'size'=>'','quantity'=>$item['quantity'],'price'=>(string)$p->get_price().' USD'];
  }
  return ['cart_id'=>'native-'.$cartId,'native_cart_id'=>$cartId,'cart_total'=>$m['cart_total'],'cart_quantity'=>$m['cart_quantity'],'restore_url'=>$m['restore_url'],'items'=>$items,'cart_empty'=>false,'cart_recovered'=>false];
 }
 public static function refresh(int $uid,array $s): array {
  if(empty($s['native_cart_id'])||!empty($s['cart_empty'])||!empty($s['cart_recovered']))return $s;
  try{return array_merge($s,self::payload($uid,(int)$s['native_cart_id']));}
  catch(Throwable $e){$s['cart_empty']=true;return $s;}
 }
}

final class PFI_Native_Cart_Entry extends BWFAN_Action {
 private static $instance;
 public static function get_instance(){return self::$instance??=new self();}
 public function __construct(){$this->action_name='Puratek native cart entry — local only';$this->support_v2=true;$this->support_v1=false;}
 public function get_slug(){return 'pfi_native_cart_entry';}
 public function make_v2_data($automation,$step){return ['user_id'=>(int)($automation['global']['user_id']??0),'cart_id'=>(int)($automation['global']['cart_abandoned_id']??0),'aid'=>(int)($automation['automation_id']??$automation['aid']??$automation['id']??0)];}
 public function process_v2(){
  try{
   if(!PFI_Welcome_Policy::local())throw new RuntimeException('local_only');
   $a=(new BWFAN_Automation_Controller())->get_automation_data((int)($this->data['aid']??0));
   if(($a['meta']['pfi_profile']??'')!=='pfi-native-cart-entry-v1'||($a['event']??'')!=='ab_cart_abandoned'||(int)($a['status']??0)!==1)throw new RuntimeException('native_entry_profile_required');
   $uid=(int)$this->data['user_id'];$p=PFI_Native_Cart::payload($uid,(int)$this->data['cart_id']);PFI_Lifecycle::event($uid,'cart',$p);
   return ['status'=>self::$RESPONSE_SUCCESS,'message'=>'Local cart evaluated; duplicate event cannot create a duplicate lifecycle run'];
  }catch(Throwable $e){return ['status'=>self::$RESPONSE_FAILED,'message'=>'Native cart held: '.$e->getMessage()];}
 }
}
BWFAN_Load_Integrations::register_actions(PFI_Native_Cart_Entry::get_instance());
