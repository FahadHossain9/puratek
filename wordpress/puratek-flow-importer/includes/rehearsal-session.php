<?php
if(!defined('ABSPATH'))exit;

/** One local admin-owned session. No native executor, email, scheduler or network calls. */
final class PFI_Rehearsal_Session {
    const OPTION='pfi_rehearsal_lab_v1';
    private static function access(): void {
        if(!PFI_Welcome_Policy::local()||!current_user_can('manage_options'))throw new RuntimeException('local_admin_required');
    }
    private static function locked(callable $fn) {
        self::access();global $wpdb;$key='pfi_rehearsal_'.substr(hash('sha256',DB_NAME.$wpdb->prefix),0,32);
        if((string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,0)',$key))!=='1')throw new RuntimeException('session_busy');
        try{wp_cache_delete(self::OPTION,'options');return $fn();}finally{$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$key));}
    }
    public static function read(): array {
        self::access();$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];
    }
    private static function persist(array $v,string $event,string $reason): void {
        $s=$v['session']??[];
        $v['audit'][]=['at'=>time(),'event'=>$event,'reason'=>$reason,'admin'=>get_current_user_id(),'session'=>substr(hash('sha256',$s['id']??''),0,12),'automation'=>(int)($s['automation_ids'][0]??0)];
        $v['audit']=array_slice($v['audit'],-100);
        if(!update_option(self::OPTION,$v,false))throw new RuntimeException('session_persistence_failed');
    }
    private static function owned(int $aid): array {
        foreach(PFI_Importer::registry() as$entry){
            if((int)($entry['id']??0)===$aid&&($entry['state']??'')==='imported'){
                PFI_Importer::untouched($entry);$snapshot=PFI_Importer::snapshot($aid);
                if(!str_starts_with((string)($snapshot['automation']['event']??''),'pfi_lifecycle_'))break;
                return $snapshot;
            }
        }
        throw new RuntimeException('owned_inactive_draft_required');
    }
    public static function create(int $uid,int $aid,int $ttl,bool $syntheticSubscription): array {
        return self::locked(static function()use($uid,$aid,$ttl,$syntheticSubscription){
            if(!PFI_Test_Recipient::allowed($uid))throw new RuntimeException('authorized_fixture_required');
            if($ttl<60||$ttl>3600)throw new RuntimeException('invalid_session_duration');
            $snap=self::owned($aid);$v=self::read();$now=time();
            $v['generation']=(int)($v['generation']??0)+1;
            $v['session']=['id'=>bin2hex(random_bytes(16)),'owner'=>get_current_user_id(),'mode'=>'dry-run','enabled'=>true,'revoked'=>false,'site'=>'http://email-test-puratek.local','issued_at'=>$now,'expires_at'=>$now+$ttl,'generation'=>$v['generation'],'automation_ids'=>[$aid],'snapshots'=>[$aid=>PFI_Importer::hash($snap)],'recipients'=>[$uid=>strtolower(get_userdata($uid)->user_email)],'fixture_subscription'=>$syntheticSubscription?'subscribed':'unknown','fixture_checked_at'=>$now];
            self::persist($v,'create','synthetic_local_session');return $v['session'];
        });
    }
    private static function session(string $expected,array $v): array {
        $s=$v['session']??[];
        if(($s['owner']??0)!==get_current_user_id()||!hash_equals((string)($s['id']??''),$expected)||$expected==='')throw new RuntimeException('session_owner_or_id_mismatch');
        return $s;
    }
    public static function revoke(string $expected): void {
        self::locked(static function()use($expected){$v=self::read();$s=self::session($expected,$v);$s['revoked']=true;$s['enabled']=false;$v['session']=$s;self::persist($v,'revoke','admin_revoked');});
    }
    public static function preview(string $expected): array {
        return self::locked(static function()use($expected){
            $v=self::read();
            try{$s=self::session($expected,$v);}catch(Throwable $e){self::persist($v,'blocked','session_owner_or_id_mismatch');throw $e;}
            $aid=(int)$s['automation_ids'][0];$uid=(int)array_key_first($s['recipients']);
            if(time()>=$s['expires_at']){$v['session']['enabled']=false;$v['session']['revoked']=true;self::persist($v,'expire','session_expired');return ['result'=>'blocked','reason'=>'session_expired'];}
            try{$snap=self::owned($aid);}catch(Throwable $e){self::persist($v,'blocked','draft_changed_or_unavailable');return ['result'=>'blocked','reason'=>'draft_changed_or_unavailable'];}
            $u=get_userdata($uid);$state=PFI_Lifecycle::state($uid);
            $recipient=['id'=>$uid,'email'=>$u?$u->user_email:'','verified_test_account'=>PFI_Test_Recipient::allowed($uid),'consent'=>(bool)($state['consent']??false)&&!empty($state['known_source']),'country'=>$state['country']??'','suppressed'=>(bool)($state['suppressed']??true),'subscription_status'=>$s['fixture_subscription'],'subscription_checked_at'=>$s['fixture_checked_at']];
            $execution=['mode'=>'dry-run','site'=>'http://email-test-puratek.local','session_id'=>$s['id'],'generation'=>$s['generation'],'profile'=>'pfi-rehearsal-v1','owner'=>'puratek-flow-importer','action'=>'render-preview','automation_status'=>(int)$snap['automation']['status'],'automation_id'=>$aid,'snapshot_hash'=>PFI_Importer::hash($snap),'recipient'=>$s['recipients'][$uid]];
            $decision=PFI_Live_Rehearsal_Gate::evaluate($s,$execution,$recipient,time());$out=$decision;
            if($decision['result']==='preview_only'){
                $out['scope']='Stored template source only; merge tags and flow timing not evaluated. Subscription is a local synthetic fixture, not provider evidence.';$out['templates']=[];
                foreach($snap['steps']as$step){$data=json_decode($step['data']??'',true);$sidebar=$data['sidebarData']??[];
                    foreach(($sidebar['pfi_variants']??[])as$key=>$email){
                        if(!is_array($email)||!is_string($email['template']??null))continue;
                        $out['templates'][]=['variant'=>(string)$key,'subject'=>(string)($email['subject']??''),'source'=>$email['template']];
                    }
                }
                if(!$out['templates'])$out=['result'=>'blocked','reason'=>'no_reviewable_templates'];
            }
            self::persist($v,$out['result'],$out['reason']);return $out;
        });
    }
}

add_action('admin_menu',static function(){
 if(!PFI_Welcome_Policy::local())return;
 add_management_page('Puratek Rehearsal Lab','Puratek Rehearsal Lab','manage_options','pfi-rehearsal-lab',static function(){
  if(!current_user_can('manage_options')||!PFI_Welcome_Policy::local())return;
  $result=null;
  if(($_SERVER['REQUEST_METHOD']??'')==='POST'){
   check_admin_referer('pfi_rehearsal_lab');
   try{$op=sanitize_key($_POST['operation']??'');
    if($op==='create'){PFI_Rehearsal_Session::create(absint($_POST['uid']??0),absint($_POST['aid']??0),absint($_POST['ttl']??0),isset($_POST['synthetic_subscription']));$result=['result'=>'created'];}
    elseif($op==='preview')$result=PFI_Rehearsal_Session::preview(sanitize_text_field(wp_unslash($_POST['session_id']??'')));
    elseif($op==='revoke'){PFI_Rehearsal_Session::revoke(sanitize_text_field(wp_unslash($_POST['session_id']??'')));$result=['result'=>'revoked'];}
   }catch(Throwable $e){$result=['result'=>'blocked','reason'=>'Request rejected. Check fixture authorization, draft ownership and session state.'];}
  }
  echo '<div class="wrap"><h1>Puratek Rehearsal Lab</h1><p>Local only. No email, queues or external actions. Template source is displayed as inert text, without loading remote images or running merge tags. Each new session replaces the previous one.</p><form method="post">';wp_nonce_field('pfi_rehearsal_lab');
  echo '<input type="hidden" name="operation" value="create"><p><label>Authorized synthetic user ID <input name="uid" type="number" min="1" required></label> <label>Imported inactive automation ID <input name="aid" type="number" min="1" required></label></p><p><label>Session seconds <input name="ttl" type="number" min="60" max="3600" value="300" required></label></p><p><label><input type="checkbox" name="synthetic_subscription"> Use a synthetic subscribed observation (local fixture only, valid for at most five minutes)</label></p>';submit_button('Create local rehearsal session');echo '</form>';
  $v=PFI_Rehearsal_Session::read();$s=$v['session']??[];
  if(($s['owner']??0)===get_current_user_id()){
   echo '<h2>Session</h2><p>'.esc_html(($s['enabled']&&!$s['revoked']&&$s['expires_at']>time()?'Open':'Closed or expired').' · Expires '.gmdate('Y-m-d H:i:s',$s['expires_at']).' UTC').'</p><form method="post">';wp_nonce_field('pfi_rehearsal_lab');echo '<input type="hidden" name="session_id" value="'.esc_attr($s['id']).'"><button class="button button-primary" name="operation" value="preview">Preview without sending</button> <button class="button" name="operation" value="revoke">Revoke session</button></form>';
  }
  if($result){$templates=$result['templates']??[];unset($result['templates']);echo '<h2>Result</h2><pre>'.esc_html(wp_json_encode($result,JSON_PRETTY_PRINT)).'</pre>';foreach($templates as$t){echo '<details><summary>'.esc_html($t['variant'].' — '.$t['subject']).'</summary><textarea readonly rows="12" style="width:100%">'.esc_textarea($t['source']).'</textarea></details>';}}
  echo '<details><summary>Audit log — latest 100 events</summary><ul>';
  foreach(array_reverse($v['audit']??[])as$event){echo '<li>'.esc_html(gmdate('Y-m-d H:i:s',$event['at']).' UTC · '.$event['event'].' · '.$event['reason'].' · automation '.$event['automation'].' · session '.$event['session']).'</li>';}
  echo '</ul></details></div>';
 });
});
