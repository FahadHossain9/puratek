<?php
/** Pure rehearsal policy; the WordPress service remains local-only.
 * Inputs must eventually come from server-owned records, never from event/HTTP request fields.
 * Even an accepted request returns only a simulation decision, never a send permission.
 */
final class PFI_Live_Rehearsal_Gate {
    public static function evaluate(array $session,array $execution,array $recipient,int $now): array {
        $base=['transport_allowed'=>false,'external_actions_allowed'=>false,'queue_allowed'=>false];
        $stop=static fn($reason)=>$base+['result'=>'blocked','reason'=>$reason];
        if (($session['mode']??null)!=='dry-run' || ($execution['mode']??null)!=='dry-run')return $stop('dry_run_only');
        if (($session['enabled']??false)!==true || ($session['revoked']??true)!==false)return $stop('session_disabled');
        if (!in_array($session['site']??null,['https://puratekpeptides.com','http://email-test-puratek.local'],true) || ($execution['site']??null)!==$session['site'])return $stop('site_mismatch');
        foreach(['issued_at','expires_at','generation'] as$key)if(!is_int($session[$key]??null))return $stop('invalid_session');
        if($now<=0 || $session['issued_at']<=0 || $session['issued_at']>$now || $session['expires_at']<=$now
            || $session['expires_at']<=$session['issued_at'] || $session['expires_at']-$session['issued_at']>3600 || $session['generation']<1)return $stop('expired_or_invalid_session');
        if (!is_string($session['id']??null)||!preg_match('/^[a-f0-9]{32}$/D',$session['id'])
            || ($execution['session_id']??null)!==$session['id'] || ($execution['generation']??null)!==$session['generation'])return $stop('stale_session');
        if (($execution['profile']??null)!=='pfi-rehearsal-v1' || ($execution['owner']??null)!=='puratek-flow-importer')return $stop('foreign_execution');
        if (($execution['action']??null)!=='render-preview')return $stop('action_not_allowed');
        if (($execution['automation_status']??null)!==2)return $stop('automation_not_inactive');
        if (!is_int($execution['automation_id']??null)||$execution['automation_id']<1
            || !is_array($session['automation_ids']??null)||!in_array($execution['automation_id'],$session['automation_ids'],true))return $stop('automation_not_allowlisted');
        if (!is_string($execution['snapshot_hash']??null)||!preg_match('/^[a-f0-9]{64}$/D',$execution['snapshot_hash'])
            || ($session['snapshots'][$execution['automation_id']]??null)!==$execution['snapshot_hash'])return $stop('automation_changed');
        if (!is_int($recipient['id']??null)||$recipient['id']<1||($recipient['verified_test_account']??false)!==true)return $stop('test_identity_required');
        $email=$recipient['email']??null;
        if(!is_string($email)||!filter_var($email,FILTER_VALIDATE_EMAIL))return $stop('invalid_email');
        $email=strtolower($email);
        if(!is_array($session['recipients']??null)||($session['recipients'][$recipient['id']]??null)!==$email
            || !is_string($execution['recipient']??null)||strtolower($execution['recipient'])!==$email)return $stop('recipient_mismatch');
        // CC/BCC/attachments/remote callbacks never enter the rehearsal renderer.
        foreach(['cc','bcc','attachments','webhook_url']as$key)if(!empty($execution[$key]))return $stop('side_effect_fields');
        if(($recipient['consent']??false)!==true||($recipient['country']??null)!=='US'||($recipient['suppressed']??true)!==false)return $stop('consent_or_suppression');
        if(($recipient['subscription_status']??null)!=='subscribed'||!is_int($recipient['subscription_checked_at']??null)
            || $recipient['subscription_checked_at']>$now||$recipient['subscription_checked_at']<$session['issued_at']
            || $now-$recipient['subscription_checked_at']>300)return $stop('subscription_unknown_or_stale');
        return $base+['result'=>'preview_only','reason'=>'eligible_for_local_simulation'];
    }
}
