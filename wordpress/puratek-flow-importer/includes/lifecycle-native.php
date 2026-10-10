<?php
if (!defined('ABSPATH')) { exit; }

final class PFI_Lifecycle_Event extends BWFAN_Event {
    private static array $instances=[];
    private string $flow;
    public static function instance(string $flow): self { return self::$instances[$flow]??=new self($flow); }
    public function get_instance() { return $this; }
    public function __construct(string $flow) {
        $this->flow=$flow;$this->event_name='Puratek '.ucwords(str_replace('-',' ',$flow)).' — verified local event';
        $this->event_desc='Synthetic local integration only; production mapping is locked.';$this->v2=true;$this->support_v1=false;$this->event_merge_tag_groups=['bwf_contact'];
    }
    public function get_slug() { return 'pfi_lifecycle_'.str_replace('-','_',$this->flow); }
    public function enroll(int $uid,array $r): bool {
        $raw=(new BWFAN_Automation_Controller())->get_automation_data($r['aid']);$u=get_userdata($uid);
        BWFAN_Common::get_bwf_customer($u->user_email,$uid);
        $data=array_merge($raw,$raw['meta'],['id'=>$r['aid'],'version'=>2]);
        $this->global_data=['global'=>['user_id'=>$uid,'email'=>$u->user_email,'pfi_run_id'=>$r['id']]];
        $this->event_data=['event_slug'=>$this->get_slug(),'event_source'=>'wp'];
        return (bool)$this->handle_automation_run_v2($r['aid'],$data);
    }
    public function validate_v2_before_start($row) {
        $d=json_decode($row['data']??'{}',true);$uid=(int)($d['global']['user_id']??0);$id=$d['global']['pfi_run_id']??'';
        $r=PFI_Lifecycle::runs($uid)[$id]??null;
        return PFI_Lifecycle::allowed($uid)&&$r&&$r['status']==='active'&&PFI_Lifecycle::active_id($this->flow)===(int)$row['aid'];
    }
}

final class PFI_Lifecycle_Email extends BWFAN_Action {
    private static $instance;
    public static function get_instance() { return self::$instance??=new self(); }
    public function __construct() { $this->action_name='Puratek lifecycle email — shared policy';$this->action_desc='Condition, event timing, cross-flow priority, frequency and deduplication.';$this->support_v2=true;$this->support_v1=false; }
    public function get_slug() { return 'pfi_lifecycle_email'; }
    public function get_fields_schema() { return BWFAN_Wp_Sendemail::get_instance()->get_fields_schema(); }
    public function make_v2_data($automation,$step) {
        // Keep original input: branch/content selection happens under the recipient lock at execution.
        return ['user_id'=>(int)($automation['global']['user_id']??0),'automation_id'=>(int)($automation['aid']??$automation['id']??0),'pfi_run_id'=>$automation['global']['pfi_run_id']??'','pfi_step'=>$step,'pfi_automation'=>$automation];
    }
    public function process_v2() {
        $uid=(int)($this->data['user_id']??0);
        if(!PFI_Lifecycle::allowed($uid)||!BWFAN_Common::check_for_lks()) { return ['status'=>self::$RESPONSE_FAILED,'message'=>'Local profile/native license required']; }
        $settings=BWFAN_Common::get_global_settings();
        if(($settings['bwfan_email_service']??'wp')!=='wp' || apply_filters('pre_wp_mail',null,['to'=>'guard@example.invalid','subject'=>'','message'=>'','headers'=>[],'attachments'=>[]])!==false) {
            return ['status'=>self::$RESPONSE_FAILED,'message'=>'Local blocked-mail transport required; connector delivery is disabled'];
        }
        try { return PFI_Lifecycle::locked($uid,function()use($uid){
            $runs=PFI_Lifecycle::runs($uid);$id=$this->data['pfi_run_id']??'';$r=$runs[$id]??null;
            if(!$r||PFI_Lifecycle::active_id($r['flow'])!==$r['aid']) { return ['status'=>self::$RESPONSE_FAILED,'message'=>'Inactive or conflicting revision']; }
            $input=$this->data['pfi_step'];$step=$input['pfi_step']??'';
            $d=PFI_Lifecycle::decision($uid,$r,$step,$runs);$now=PFI_Lifecycle::now();
            if($d['result']==='defer') { return ['status'=>self::$RESPONSE_REATTEMPT,'message'=>$d['reason'],'e_time'=>$d['at']]; }
            if($d['result']==='stop') {
                $runs[$id]['status']='stopped';$runs[$id]['reason']=$d['reason'];PFI_Lifecycle::save($uid,$runs);
                if($d['reason']==='route_support') {
                    // Transfer the same cart at its original abandonment time; bypass re-entry only for this transfer.
                    $aid=PFI_Lifecycle::active_id('support');
                    if($aid) { $sid=substr(hash('sha256','support-transfer:'.$id),0,32);$nr=$r;$nr['id']=$sid;$nr['flow']='support';$nr['aid']=$aid;$nr['steps']=[];$nr['status']='active';$nr['context']=array_merge($r['context'],PFI_Lifecycle::state($uid));$runs[$sid]=$nr;PFI_Lifecycle::save($uid,$runs);if(!PFI_Lifecycle_Event::instance('support')->enroll($uid,$nr)){$runs[$sid]['status']='enrollment_review';PFI_Lifecycle::save($uid,$runs);} }
                }
                return ['status'=>self::$RESPONSE_FAILED,'message'=>$d['reason']];
            }
            if($d['result']==='skip') {
                if($d['reason']!=='already_finished'){$runs[$id]['steps'][$step]=['state'=>'skipped','reason'=>$d['reason'],'at'=>$now];}
                self::finish($uid,$runs,$id,$step);PFI_Lifecycle::save($uid,$runs);
                return ['status'=>self::$RESPONSE_SKIPPED,'message'=>$d['reason']];
            }
            $template=$d['template'];$email=PFI_Builder::selected($input,$template);
            if(!$email) { return ['status'=>self::$RESPONSE_FAILED,'message'=>'Missing approved template variant']; }
            if(in_array($r['flow'],['cart','support'],true)) { $r['context']=array_merge($r['context'],PFI_Lifecycle::state($uid)); }
            $input['bwfan_email_data']=PFI_Lifecycle::hydrate($email,$r,$template);
            if(($input['bwfan_email_to']??'')!=='{{contact_email}}') { return ['status'=>self::$RESPONSE_FAILED,'message'=>'Recipient override is not permitted']; }
            $native=BWFAN_Wp_Sendemail::get_instance();$data=$native->make_v2_data($this->data['pfi_automation'],$input);
            if(strtolower((string)($data['email']??''))!==strtolower(get_userdata($uid)->user_email)) { return ['status'=>self::$RESPONSE_FAILED,'message'=>'Resolved recipient differs from authorized test account']; }
            $runs[$id]['steps'][$step]=['state'=>'sending','template'=>$template,'at'=>$now];PFI_Lifecycle::save($uid,$runs);
            $native->reset_data();$native->automation_id=$r['aid'];$native->set_data($data);
            try{$result=$native->process_v2();}catch(Throwable $e){$result=['status'=>self::$RESPONSE_FAILED];}
            $success=($result['status']??0)===self::$RESPONSE_SUCCESS;
            $runs[$id]['steps'][$step]=['state'=>$success?'sent':'uncertain','template'=>$template,'at'=>$now]+($success?['sent_at'=>$now]:[]);
            if($success) { update_user_meta($uid,'_pfi_last_marketing_at',$now);self::finish($uid,$runs,$id,$step); }
            else { $runs[$id]['status']='delivery_review'; }
            PFI_Lifecycle::save($uid,$runs);
            return $success?$result:['status'=>self::$RESPONSE_FAILED,'message'=>'Uncertain delivery held; no automatic resend'];
        }); } catch(Throwable $e) { return ['status'=>self::$RESPONSE_FAILED,'message'=>'Lifecycle processing held: '.$e->getMessage()]; }
    }
    private static function finish(int $uid,array &$runs,string $id,string $step): void {
        $flow=$runs[$id]['flow'];$all=PFI_Lifecycle_Policy::STEPS[$flow];
        foreach($all as$key) { if(!in_array($runs[$id]['steps'][$key]['state']??'', ['sent','skipped'],true)) { return; } }
        $runs[$id]['status']='completed';
        if($flow==='winback') { update_user_meta($uid,'_pfi_sunset','1'); }
    }
}

foreach(PFI_Lifecycle_Policy::FLOWS as$flow) { BWFAN_Load_Sources::register_events(PFI_Lifecycle_Event::instance($flow)); }
BWFAN_Load_Integrations::register_actions(PFI_Lifecycle_Email::get_instance());
