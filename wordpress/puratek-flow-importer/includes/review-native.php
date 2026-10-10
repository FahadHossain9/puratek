<?php
if (!defined('ABSPATH')) { exit; }

/** Intentionally has no customer hooks, enrollment method or transport. */
final class PFI_Review_Event extends BWFAN_Event {
    private static $instance;
    public static function get_instance() { return self::$instance ??= new self(); }
    public function __construct() {
        $this->event_name='Puratek review only — no enrollment';
        $this->event_desc='Content review draft. Production triggers are not connected.';
        $this->v2=true; $this->support_v1=false;
    }
    public function get_slug() { return 'pfi_review_only'; }
    public function validate_v2_event_settings($automation_data) { return false; }
    public function validate_v2_before_start($automation_contact_row) { return false; }
    public function handle_automation_run_v2($automation_id,$automation_data) { return false; }
}

final class PFI_Review_Email extends BWFAN_Action {
    private static $instance;
    public static function get_instance() { return self::$instance ??= new self(); }
    public function __construct() {
        $this->action_name='Puratek review email — sending disabled';
        $this->action_desc='Inert content and variant storage. No email delivery implementation.';
        $this->support_v2=true; $this->support_v1=false;
    }
    public function get_slug() { return 'pfi_review_email'; }
    public function get_fields_schema() { return BWFAN_Wp_Sendemail::get_instance()->get_fields_schema(); }
    public function make_v2_data($automation,$step) { return []; }
    public function process_v2() { return ['status'=>self::$RESPONSE_FAILED,'message'=>'Review-only draft: sending is disabled in this release.']; }
}
BWFAN_Load_Sources::register_events(PFI_Review_Event::get_instance());
BWFAN_Load_Integrations::register_actions(PFI_Review_Email::get_instance());
