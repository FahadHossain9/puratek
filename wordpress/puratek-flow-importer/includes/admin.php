<?php
if (!defined('ABSPATH')) { exit; }

final class PFI_Admin {
    public static function boot(): void {
        add_action('admin_menu', static function () {
            add_management_page('Puratek Flow Importer', 'Puratek Flow Importer', 'manage_options', 'puratek-flow-importer', [self::class, 'page']);
        });
        add_action('admin_post_pfi_backup', [self::class, 'download']);
    }
    private static function pending_key(): string { return 'pfi_preview_' . get_current_user_id(); }
    public static function download(): void {
        if (!current_user_can('manage_options')) { wp_die('Administrator access required.', '', ['response' => 403]); }
        check_admin_referer('pfi_backup');
        $key = sanitize_text_field(wp_unslash($_GET['key'] ?? ''));
        $entry = PFI_Importer::registry()[$key] ?? null;
        if (!$entry) { wp_die('Revision not found.'); }
        nocache_headers(); header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="puratek-revision-backup.json"');
        echo wp_json_encode($entry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); exit;
    }
    public static function page(): void {
        if (!current_user_can('manage_options')) { return; }
        $message = ''; $error = ''; $result = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                check_admin_referer('pfi_manage');
                $action = sanitize_key($_POST['pfi_action'] ?? '');
                if ($action === 'builder_clone') {
                    PFI_Package::check(PFI_Welcome_Policy::local(), 'Builder conversion is restricted to the local test site.');
                    $flows = [];
                    foreach (PFI_Importer::registry() as $entry) {
                        $payload = $entry['payload'] ?? null;
                        if (($entry['state'] ?? '') !== 'imported' || !is_array($payload) || ($payload['meta']['pfi_profile'] ?? '') !== 'lifecycle-local-v1' || !empty($payload['meta']['pfi_builder'])) { continue; }
                        $id = $payload['meta']['pfi_flow'];
                        $flows[$id] = ['id'=>$id, 'version'=>'2.0.0-builder', 'payload'=>$payload];
                    }
                    PFI_Package::check(count($flows) === count(PFI_Lifecycle_Policy::FLOWS), 'All seven original local flow revisions are required.');
                    $package = PFI_Builder::package(['package_id'=>'puratek-builder-local', 'flows'=>$flows]);
                    $result = PFI_Importer::import($package, array_keys($flows));
                    $message = 'Native builder drafts created. Source HTML preserved; editor save and dynamic widget rendering still require licensed validation.';
                } elseif ($action === 'upload') {
                    delete_transient(self::pending_key());
                    $upload = $_FILES['package'] ?? [];
                    PFI_Package::check(($upload['error'] ?? -1) === UPLOAD_ERR_OK && is_uploaded_file($upload['tmp_name'] ?? ''), 'ZIP upload failed; check the server upload limit.');
                    PFI_Package::check(strtolower(pathinfo($upload['name'] ?? '', PATHINFO_EXTENSION)) === 'zip', 'Upload a ZIP file.');
                    $package = PFI_Package::read($upload['tmp_name']);
                    PFI_Package::check(strlen(wp_json_encode($package)) <= 8388608, 'Parsed package exceeds the 8 MB preview limit. Split it into smaller packages.');
                    set_transient(self::pending_key(), ['package' => $package, 'map' => [], 'token' => wp_generate_uuid4()], 1800);
                    $message = 'ZIP validated. Preview only; no automation created. Preview expires in 30 minutes.';
                } elseif ($action === 'map') {
                    $pending = get_transient(self::pending_key());
                    PFI_Package::check(is_array($pending), 'Preview expired; upload the ZIP again.');
                    PFI_Package::check(hash_equals($pending['token'], (string)($_POST['preview_token'] ?? '')), 'Preview changed in another tab. Refresh before continuing.');
                    $pending['map'] = PFI_Package::json(wp_unslash($_POST['mapping'] ?? '{}'));
                    $pending['token'] = wp_generate_uuid4();
                    set_transient(self::pending_key(), $pending, 1800);
                    $message = 'Preview refreshed. Check each selected flow before import.';
                } elseif ($action === 'import') {
                    $pending = get_transient(self::pending_key());
                    PFI_Package::check(is_array($pending), 'Preview expired; upload the ZIP again.');
                    PFI_Package::check(hash_equals($pending['token'], (string)($_POST['preview_token'] ?? '')), 'Preview changed in another tab. Refresh before continuing.');
                    $selected = isset($_POST['flows']) && is_array($_POST['flows']) ? array_map('sanitize_text_field', wp_unslash($_POST['flows'])) : [];
                    PFI_Package::check(($_POST['review_ack'] ?? '') === 'yes', 'Acknowledge review-only import.');
                    $package = $pending['package'];
                    PFI_Package::check(!array_diff($selected, array_keys($package['flows'])), 'Invalid selection.');
                    $package['flows'] = array_intersect_key($package['flows'], array_flip($selected));
                    $result = PFI_Importer::import(PFI_Package::mappings($package, $pending['map']), $selected);
                    $message = 'Selected flows processed. Newly created automations are inactive review drafts.';
                } elseif ($action === 'rollback') {
                    PFI_Importer::rollback(sanitize_text_field(wp_unslash($_POST['revision'] ?? '')));
                    $message = 'Untouched inactive draft removed. Its backup is retained; other automations were preserved.';
                } else { throw new RuntimeException('Unknown action.'); }
            }
        } catch (Throwable $e) { $error = $e->getMessage(); }
        echo '<div class="wrap"><h1>Puratek Flow Importer</h1><p>Versioned ZIP packages → preview → selected inactive review drafts.</p>';
        echo '<div class="notice notice-warning inline"><p><strong>Review import only — not a production email launch.</strong> Outside the named local test site only live-review-v1 packages are accepted. Their event cannot enroll contacts, their action cannot send, and the normal activation endpoint is blocked. Production mappings and cart synchronization remain unresolved. Do not replace the review trigger/action or use Send Test Email on live. Existing store emails are not disabled. License checks are unchanged.</p></div>';
        echo '<p><a href="' . esc_url(admin_url('tools.php?page=pfi-flow-rules')) . '">View flow categories, timing and exit rules</a></p>';
        if (PFI_Welcome_Policy::local()) {
            echo '<form method="post">'; wp_nonce_field('pfi_manage');
            echo '<input type="hidden" name="pfi_action" value="builder_clone"><button class="button" type="submit">Create all 7 native builder drafts</button><p>Creates inactive copies from the existing Puratek templates. Native blocks are experimental until licensed editor Edit/Save validation.</p></form>';
        }
        try { PFI_Importer::compatible(); echo '<p>FunnelKit adapter: compatible installed versions.</p>'; }
        catch (Throwable $e) { echo '<p><strong>Import unavailable:</strong> ' . esc_html($e->getMessage()) . '</p>'; }
        if ($message) { echo '<div class="notice notice-success inline"><p>' . esc_html($message) . '</p></div>'; }
        if ($error) { echo '<div class="notice notice-error inline"><p>' . esc_html($error) . '</p></div>'; }
        if ($result) { echo '<pre>' . esc_html(wp_json_encode($result, JSON_PRETTY_PRINT)) . '</pre>'; }
        echo '<h2>1. Upload package</h2><p>Use puratek-package.json and native FunnelKit v2 exports. React/TypeScript source ZIPs need conversion first. Limit: 64 MB ZIP, subject to the server upload limit.</p><form method="post" enctype="multipart/form-data">';
        wp_nonce_field('pfi_manage'); echo '<input type="hidden" name="pfi_action" value="upload"><input type="file" name="package" accept=".zip" required aria-label="Automation package ZIP">';
        submit_button('Validate ZIP and preview', 'secondary'); echo '</form>';
        $pending = get_transient(self::pending_key());
        if (is_array($pending)) {
            echo '<h2>2. Preview and mappings</h2><p>Package: <strong>' . esc_html($pending['package']['package_id']) . '</strong>. The adapter supports linear email/delay graphs. Lifecycle category branching is handled by the guarded action policy; arbitrary visual branch nodes are rejected.</p>';
            echo '<form method="post"><input type="hidden" name="preview_token" value="' . esc_attr($pending['token'] ?? '') . '">'; wp_nonce_field('pfi_manage');
            echo '<input type="hidden" name="pfi_action" value="map"><label for="pfi-mapping">Mappings JSON for [[PFI:key]] placeholders. Native FunnelKit merge tags remain unchanged and require site testing.</label><br><textarea id="pfi-mapping" name="mapping" rows="5" cols="90">' . esc_textarea(wp_json_encode($pending['map'] ?: new stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . '</textarea>';
            submit_button('Refresh preview', 'secondary'); echo '</form><form method="post"><input type="hidden" name="preview_token" value="' . esc_attr($pending['token'] ?? '') . '">'; wp_nonce_field('pfi_manage');
            echo '<input type="hidden" name="pfi_action" value="import"><table class="widefat striped"><thead><tr><th>Select</th><th>Flow / version</th><th>Native event</th><th>Steps</th><th>Preview</th></tr></thead><tbody>';
            foreach ($pending['package']['flows'] as $id => $flow) {
                $problem = ''; $label = 'New inactive review draft';
                try {
                    $one = ['package_id' => $pending['package']['package_id'], 'flows' => [$id => $flow]];
                    $mapped = PFI_Package::mappings($one, $pending['map']);
                    $entry = PFI_Importer::registry()[PFI_Importer::key($one['package_id'], $id, $flow['version'])] ?? null;
                    if ($entry) {
                        PFI_Package::check(hash_equals($entry['payload_hash'], PFI_Importer::hash($mapped['flows'][$id]['payload'])), 'Version conflict: use a new version.');
                        if ($entry['state'] === 'imported') { PFI_Importer::untouched($entry); $label = 'Unchanged — will skip'; }
                        else { $label = 'Rolled back — can reimport inactive'; }
                    }
                } catch (Throwable $e) { $problem = $e->getMessage(); }
                echo '<tr><td><input type="checkbox" name="flows[]" value="' . esc_attr($id) . '" aria-label="Select ' . esc_attr($id) . '" ' . ($problem ? 'disabled' : '') . '></td><td>' . esc_html($id . ' / ' . $flow['version']) . '</td><td>' . esc_html($flow['payload']['data']['event']) . '</td><td>' . count($flow['payload']['step_data']) . '</td><td>' . esc_html($problem ?: $label);
                echo '<details><summary>Review step content</summary><ol>';
                $review = !$problem ? $mapped['flows'][$id]['payload'] : $flow['payload'];
                $nodes = array_column($review['meta']['steps'], null, 'id');
                $links = array_column($review['meta']['links'], 'target', 'source');
                $at = $links['start'];
                while ($at !== 'end') {
                    $step = $review['step_data'][$nodes[$at]['stepId']]; $d = PFI_Package::json($step['data']);
                    $description = (int)$step['type'] === 2 ? 'Email: ' . ($d['sidebarData']['bwfan_email_data']['subject'] ?? '') : 'Wait configuration: ' . wp_json_encode($d['sidebarData'] ?? []);
                    if (!empty($d['sidebarData']['pfi_variants'])) { $description .= ' | Variants: ' . implode('; ', array_map(static fn($k,$v) => $k . ': ' . $v['subject'], array_keys($d['sidebarData']['pfi_variants']), array_values($d['sidebarData']['pfi_variants']))); }
                    echo '<li>' . esc_html($description) . '</li>'; $at = $links[$at];
                }
                $policy = ($review['meta']['pfi_profile'] ?? '') === 'lifecycle-local-v1' ? 'LOCAL LIFECYCLE PROFILE: shared consent, conditions, event timing, priority and 24-hour frequency policy. Production providers are not connected; execution is locked outside the named local test site.' : (($review['meta']['pfi_profile'] ?? '') === 'welcome-local-v1' ? 'LOCAL TEST PROFILE: guarded W1/W2/W3 actions schedule at +0/+48/+96h and recheck eligibility. Production enrollment is locked.' : 'Waits are native sequential delays. No consent, exit or frequency policy is added by the importer.');
                if (($review['meta']['pfi_profile']??'')==='live-review-v1') { $policy='LIVE REVIEW ONLY: inert event and non-sending actions. Timings and conditions are specifications shown under Tools → Puratek Flow Rules, not running schedules. Address, preferences, consent, provider events and delivery still require verification.'; }
                echo '</ol><p>' . esc_html($policy) . '</p></details></td></tr>';
            }
            echo '</tbody></table><p><label><input type="checkbox" name="review_ack" value="yes" required> I understand this is content review only. These live review drafts cannot launch customer emails.</label></p>';
            submit_button('Import selected as inactive'); echo '</form>';
        }
        echo '<h2>3. Revision history</h2><p>New versions create separate drafts. Rollback removes only an unchanged, inactive draft with no contact history. Download its backup first if needed. Reimporting the same package after rollback creates a fresh inactive draft.</p><table class="widefat striped"><thead><tr><th>Package / flow / version</th><th>Automation</th><th>State</th><th>Backup / rollback</th></tr></thead><tbody>';
        foreach (array_reverse(PFI_Importer::registry(), true) as $key => $entry) {
            $url = wp_nonce_url(admin_url('admin-post.php?action=pfi_backup&key=' . $key), 'pfi_backup');
            echo '<tr><td>' . esc_html($entry['package'] . ' / ' . $entry['flow'] . ' / ' . $entry['version']) . '</td><td>' . (int)$entry['id'] . '</td><td>' . esc_html($entry['state']) . '</td><td><a href="' . esc_url($url) . '">Download backup</a>';
            if ($entry['state'] === 'imported') {
                echo '<form method="post">'; wp_nonce_field('pfi_manage');
                echo '<input type="hidden" name="pfi_action" value="rollback"><input type="hidden" name="revision" value="' . esc_attr($key) . '">';
                submit_button('Rollback this inactive draft', 'secondary small', 'submit', false); echo '</form>';
            }
            echo '</td></tr>';
        }
        echo '</tbody></table></div>';
    }
}
