<?php
if (!defined('ABSPATH')) { exit; }

/** Local-only bridge for the observed two-phase OTP registration snippet. */
final class PFI_Consent_Bridge {
    const REVISION='pfi-signup-v1';
    const APPROVED='pfi_signup_test_emails';
    private static array $waiting=[];
    public static function approved(string $email): bool {
        $list=get_option(self::APPROVED,[]);
        return PFI_Welcome_Policy::local() && is_array($list)
            && filter_var($email,FILTER_VALIDATE_EMAIL) && str_ends_with(strtolower($email),'@example.invalid')
            && in_array(strtolower($email),$list,true);
    }
    private static function key(string $token): string { return 'pfi_signup_'.hash('sha256',$token); }
    private static function token($token): bool { return is_string($token) && (bool)preg_match('/^[A-Za-z0-9]{24}$/D',$token); }
    public static function fields(): void {
        if(!PFI_Welcome_Policy::local()) { return; }
        wp_nonce_field('pfi_signup_consent', 'pfi_signup_nonce',false);
        echo '<p><label for="pfi_signup_country">Country / region</label><select id="pfi_signup_country" name="pfi_signup_country"><option value="">Select country / region</option>';
        foreach(WC()->countries->get_countries() as$code=>$label) { echo '<option value="'.esc_attr($code).'">'.esc_html($label).'</option>'; }
        echo '</select></p><p><label><input type="checkbox" name="pfi_signup_consent" value="1"> Email me Puratek news and offers. I can unsubscribe at any time.</label></p>';
    }
    public static function capture($name,$data,$expiration): void {
        if(!PFI_Welcome_Policy::local() || !is_string($name) || !str_starts_with($name,'woo_otp_pending_') || !is_array($data)) { return; }
        $token=substr($name,strlen('woo_otp_pending_'));
        if(!self::token($token) || ($_POST['action']??'')!=='woo_otp_send_code'
            || !is_string($_POST['nonce']??null) || !wp_verify_nonce($_POST['nonce'],'woo_otp_inline')
            || !is_string($_POST['pfi_signup_nonce']??null) || !wp_verify_nonce($_POST['pfi_signup_nonce'],'pfi_signup_consent')
            || !is_string($data['email']??null) || !self::approved($data['email'])) { return; }
        // Failed attempts/resends never read current POST fields or extend the original consent evidence.
        if(get_transient(self::key($token))!==false) { return; }
        $country=$_POST['pfi_signup_country']??'';
        $country=is_string($country)?strtoupper($country):'';
        if(!isset(WC()->countries->get_countries()[$country])) { $country=''; }
        $now=time();$expires=min((int)($data['expires']??0),$now+900);
        if($expires<=$now) { return; }
        set_transient(self::key($token),['email'=>strtolower($data['email']),'country'=>$country,
            'granted'=>($_POST['pfi_signup_consent']??'')==='1','at'=>$now,'expires'=>$expires,'revision'=>self::REVISION],$expires-$now);
    }
    public static function created($uid): void {
        if(!PFI_Welcome_Policy::local() || ($_POST['action']??'')!=='woo_otp_verify_code' || !self::token($_POST['token']??null)) { return; }
        $u=get_userdata((int)$uid);if(!$u || !self::approved($u->user_email)) { return; }
        // The source snippet calls created_customer before writing its verified-email flag.
        self::$waiting[(int)$uid]=$_POST['token'];
    }
    public static function verified($metaId,$uid,$key,$value): void {
        if($key!=='_woo_email_verified' || $value!=='1' || !isset(self::$waiting[(int)$uid]) || !PFI_Welcome_Policy::local()) { return; }
        $uid=(int)$uid;$token=self::$waiting[$uid];unset(self::$waiting[$uid]);
        $e=get_transient(self::key($token));$pending=get_transient('woo_otp_pending_'.$token);$u=get_userdata($uid);
        if(!$u || !self::approved($u->user_email) || !is_array($e) || !is_array($pending)
            || ($e['revision']??'')!==self::REVISION || (int)($e['expires']??0)<=time()
            || (int)($pending['expires']??0)<=time() || (int)($e['at']??0)<=0 || (int)($e['at']??0)>time()
            || strtolower($u->user_email)!==($e['email']??'') || strtolower((string)($pending['email']??''))!==$e['email']) { return; }
        PFI_Lifecycle::locked($uid,static function()use($uid,$u,$e,$token){
            if(get_user_meta($uid,'_pfi_signup_evidence',true)) { return; }
            update_user_meta($uid,'_pfi_signup_evidence',$e);
            update_user_meta($uid,'_pfi_test_fixture','1');
            update_user_meta($uid,'_pfi_test_authorized_email',strtolower($u->user_email));
            update_user_meta($uid,'_pfi_country',$e['country']);
            update_user_meta($uid,'_pfi_marketing_consent',['granted'=>$e['granted']===true,'at'=>$e['at'],'source'=>'local-account-opt-in','revision'=>self::REVISION]);
            delete_transient(self::key($token));
        });
        // Never clear suppression or a native unsubscribe as a side-effect of registration.
        PFI_Lifecycle::event($uid,'account');
    }
    public static function subscription($uid,$provider,$status,$at): void {
        $uid=(int)$uid;
        if(!PFI_Test_Recipient::allowed($uid) || !in_array($provider,['omnisend','funnelkit'],true)
            || !in_array($status,['subscribed','unsubscribed','unknown'],true) || !is_int($at) || $at<=0 || $at>time()) { return; }
        PFI_Lifecycle::locked($uid,static function()use($uid,$provider,$status,$at){
            $states=get_user_meta($uid,'_pfi_subscription_sources',true);$states=is_array($states)?$states:[];
            $old=$states[$provider]??[];
            if($at<(int)($old['at']??0)) { return; }
            // Revocation wins equal timestamps. A later subscribe never silently releases a hold.
            if($at===(int)($old['at']??0) && ($old['status']??'')==='unsubscribed') { return; }
            $states[$provider]=['status'=>$status,'at'=>$at];update_user_meta($uid,'_pfi_subscription_sources',$states);
            if($status!=='subscribed') { update_user_meta($uid,'_pfi_external_suppressed','1'); }
        });
    }
}
add_action('woocommerce_register_form',[PFI_Consent_Bridge::class,'fields'],50);
add_action('set_transient',[PFI_Consent_Bridge::class,'capture'],10,3);
add_action('woocommerce_created_customer',[PFI_Consent_Bridge::class,'created'],100);
add_action('added_user_meta',[PFI_Consent_Bridge::class,'verified'],100,4);
add_action('updated_user_meta',[PFI_Consent_Bridge::class,'verified'],100,4);
// In-process LOCAL fixture contract only, not an authenticated Omnisend webhook or live sync.
add_action('pfi_test_subscription_status',[PFI_Consent_Bridge::class,'subscription'],10,4);
