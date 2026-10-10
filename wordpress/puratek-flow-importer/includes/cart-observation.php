<?php
if(!defined('ABSPATH'))exit;
/** Local fixture evidence from WooCommerce's actual cart, including Store API requests. */
final class PFI_Cart_Observation {
 const META='_pfi_cart_observation';
 public static function fingerprint(array $items): string {
  $normalized=[];
  foreach($items as$item){
   if(!is_array($item)||(int)($item['product_id']??0)<1||(int)($item['quantity']??0)<1)throw new RuntimeException('invalid_observed_items');
   $variation=$item['variation']??[];if(!is_array($variation))throw new RuntimeException('invalid_observed_variation');ksort($variation);
   $normalized[]=json_encode([(int)$item['product_id'],(int)($item['variation_id']??0),(int)$item['quantity'],$variation],JSON_THROW_ON_ERROR);
  }
  sort($normalized,SORT_STRING);return hash('sha256',json_encode($normalized,JSON_THROW_ON_ERROR));
 }
 public static function capture($cart): void {
  $uid=get_current_user_id();if(!PFI_Test_Recipient::allowed($uid)||!$cart instanceof WC_Cart)return;
  try{$items=$cart->get_cart();$data=['email'=>strtolower(get_userdata($uid)->user_email),'empty'=>!$items,'fingerprint'=>self::fingerprint($items),'total'=>round((float)$cart->get_total('edit'),2),'currency'=>get_woocommerce_currency()];
   $old=get_user_meta($uid,self::META,true);$at=time();if(is_array($old)){ $previous=$old;unset($previous['changed_at']);if($previous===$data)$at=(int)$old['changed_at']; }
   $data['changed_at']=$at;update_user_meta($uid,self::META,$data);
  }catch(Throwable $e){update_user_meta($uid,self::META,['invalid'=>true,'changed_at'=>time()]);}
 }
 public static function empty_cart(): void {if(function_exists('WC')&&WC()->cart)self::capture(WC()->cart);}
 public static function reason(array $observation,array $row,string $email): string {
  if(!$observation)return ''; // Older synthetic table fixtures have no frontend session.
  if(!empty($observation['invalid'])||($observation['email']??'')!==strtolower($email))return 'frontend_observation_invalid';
  if(!empty($observation['empty']))return 'frontend_cart_empty';
  $items=@unserialize((string)($row['items']??''),['allowed_classes'=>false,'max_depth'=>32]);
  if(!is_array($items))return 'native_cart_items_invalid';
  try{if(self::fingerprint($items)!==($observation['fingerprint']??''))return 'frontend_native_cart_mismatch';}catch(Throwable $e){return 'native_cart_items_invalid';}
  if(!isset($row['total'],$observation['total'])||!is_numeric($row['total'])||!is_numeric($observation['total'])||abs((float)$row['total']-(float)$observation['total'])>0.009||($row['currency']??'')!==($observation['currency']??''))return 'frontend_native_total_mismatch';
  $modified=strtotime(($row['last_modified']??'').' UTC');
  if(!$modified||$modified<(int)($observation['changed_at']??0))return 'frontend_activity_newer_than_native';
  return '';
 }
}
add_action('woocommerce_after_calculate_totals',[PFI_Cart_Observation::class,'capture'],1000);
add_action('woocommerce_cart_emptied',[PFI_Cart_Observation::class,'empty_cart'],1000);
// WC returns early from calculate_totals for an empty cart, so the totals hook alone misses removal.
add_action('woocommerce_cart_item_removed',static function($key,$cart){if($cart instanceof WC_Cart&&$cart->is_empty())PFI_Cart_Observation::capture($cart);},1000,2);
add_action('woocommerce_cart_loaded_from_session',static function($cart){if($cart instanceof WC_Cart&&$cart->is_empty())PFI_Cart_Observation::capture($cart);},1000);
