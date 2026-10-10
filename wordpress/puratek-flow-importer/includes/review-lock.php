<?php
if (!defined('ABSPATH')) { exit; }

/** Block the verified FunnelKit UI activation endpoint for review drafts only. */
add_filter('rest_pre_dispatch', static function ($result,$server,$request) {
    if ($result !== null || !in_array($request->get_method(),['POST','PUT','PATCH'],true) || !defined('BWFAN_API_NAMESPACE') || rtrim($request->get_route(),'/') !== '/'.trim(BWFAN_API_NAMESPACE,'/').'/automations/toggle-state' || absint($request->get_param('state')) !== 1) { return $result; }
    global $wpdb;
    $id=absint($request->get_param('automation_id'));
    if (!$id) { return $result; }
    $event=$wpdb->get_var($wpdb->prepare("SELECT event FROM {$wpdb->prefix}bwfan_automations WHERE ID=%d",$id));
    $owned=false;
    foreach (PFI_Importer::registry() as $entry) {
        if ((int)$entry['id']===$id && $entry['state']==='imported' && ($entry['payload']['meta']['pfi_profile']??'')==='live-review-v1') { $owned=true; break; }
    }
    if ($owned || $event==='pfi_review_only') {
        return new WP_Error('pfi_review_locked','Puratek review-only draft cannot be activated. Production event mappings and cart synchronization are not validated.',['status'=>409]);
    }
    return $result;
},10,3);
