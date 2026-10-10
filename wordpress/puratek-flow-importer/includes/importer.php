<?php
if (!defined('ABSPATH')) { exit; }

final class PFI_Importer {
    const LEDGER = 'pfi_registry_v1';
    public static function hash(array $value): string { return hash('sha256', wp_json_encode($value)); }
    public static function key(string $package, string $flow, string $version): string { return hash('sha256', "$package/$flow/$version"); }
    public static function registry(): array { return get_option(self::LEDGER, []); }
    public static function compatible(): void {
        PFI_Package::check(current_user_can('manage_options'), 'Administrator access required.');
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        foreach (['wp-marketing-automations/wp-marketing-automations.php' => '3.8.5.2', 'wp-marketing-automations-pro/wp-marketing-automations-pro.php' => '3.8.5'] as $plugin => $version) {
            PFI_Package::check(is_plugin_active($plugin), 'Required plugin is inactive: ' . $plugin);
            $data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin, false, false);
            PFI_Package::check($data['Version'] === $version, 'Untested FunnelKit version. This adapter requires ' . $plugin . ' ' . $version . '.');
        }
        PFI_Package::check(function_exists('BWFAN_Core'), 'FunnelKit is unavailable.');
    }
    public static function snapshot(int $id): array {
        global $wpdb;
        $a = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}bwfan_automations WHERE ID=%d", $id), ARRAY_A);
        $s = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}bwfan_automation_step WHERE aid=%d ORDER BY ID", $id), ARRAY_A);
        $m = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}bwfan_automationmeta WHERE bwfan_automation_id=%d ORDER BY ID", $id), ARRAY_A);
        PFI_Package::check($wpdb->last_error === '', 'Database snapshot failed.');
        return ['automation' => $a, 'steps' => $s, 'meta' => $m];
    }
    public static function untouched(array $entry): void {
        $state = self::snapshot((int)$entry['id']);
        PFI_Package::check((int)($state['automation']['status'] ?? 0) === 2, 'Tracked automation is missing or active. Review it in FunnelKit.');
        PFI_Package::check(hash_equals($entry['snapshot_hash'], self::hash($state)), 'Manual edit or state change detected. Existing automation was preserved.');
    }
    private static function transaction(callable $fn) {
        global $wpdb;
        self::compatible();
        // This connection-scoped lock serializes importer requests; crash/connection loss releases it.
        $name = 'pfi_' . substr(hash('sha256', DB_NAME . $wpdb->prefix), 0, 40);
        PFI_Package::check((string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $name)) === '1', 'Another import is running. Retry after it finishes.');
        try {
            foreach (['options','bwfan_automations','bwfan_automation_step','bwfan_automationmeta'] as $suffix) {
                $table = $wpdb->prefix . $suffix;
                $engine = $wpdb->get_var($wpdb->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=%s', $table));
                PFI_Package::check(strtoupper((string)$engine) === 'INNODB', 'Transactional InnoDB table required: ' . $suffix);
            }
            PFI_Package::check($wpdb->query('START TRANSACTION') !== false, 'Cannot start database transaction.');
            try {
                // Discard request/object-cache copies before loading the registry under the lock.
                wp_cache_delete(self::LEDGER, 'options');
                $result = $fn();
                PFI_Package::check($wpdb->query('COMMIT') !== false, 'Commit failed.');
                return $result;
            } catch (Throwable $e) {
                $wpdb->query('ROLLBACK');
                wp_cache_delete(self::LEDGER, 'options');
                wp_cache_delete('notoptions', 'options');
                throw $e;
            }
        } finally { $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $name)); }
    }
    private static function save(array $registry): void {
        PFI_Package::check(strlen(wp_json_encode($registry)) <= 33554432, 'Import history exceeds 32 MB. Export backups and review history before continuing.');
        PFI_Package::check(update_option(self::LEDGER, $registry, false), 'Could not save import history.');
    }
    public static function import(array $package, array $selected): array {
        PFI_Package::check(count($selected) > 0 && count($selected) === count(array_unique($selected)), 'Select at least one unique flow.');
        return self::transaction(static function () use ($package, $selected) {
            $registry = self::registry(); $plan = [];
            foreach ($selected as $id) {
                PFI_Package::check(is_string($id) && isset($package['flows'][$id]), 'Unknown selected flow.');
                $f = $package['flows'][$id]; $p = $f['payload'];
                PFI_Package::validate_workflow($p);
                PFI_Package::check(PFI_Welcome_Policy::local() || ($p['meta']['pfi_profile'] ?? '') === 'live-review-v1', 'Outside the named local test site only inert live-review-v1 packages are accepted.');
                if (in_array($p['meta']['pfi_profile'] ?? '', ['welcome-local-v1','lifecycle-local-v1'], true)) {
                    PFI_Package::check(PFI_Welcome_Policy::local(), 'This Welcome package is local-test-only; production mappings are unverified.');
                }
                PFI_Package::check(!preg_match('/\[\[PFI:|%%[A-Z0-9_]+%%/', wp_json_encode($p)), 'Resolve placeholders before import.');
                if ($p['data']['source'] === 'wc') { PFI_Package::check(class_exists('WooCommerce'), 'WooCommerce must be active.'); }
                PFI_Package::check((bool)BWFAN_Core()->sources->get_event($p['data']['event']), 'Workflow event is not available in the installed plugins.');
                $key = self::key($package['package_id'], $id, $f['version']);
                $hash = self::hash($p); $existing = $registry[$key] ?? null;
                if ($existing) {
                    PFI_Package::check(hash_equals($existing['payload_hash'], $hash), 'Version conflict: content changed under the same version. Increase that flow version.');
                    if ($existing['state'] === 'imported') { self::untouched($existing); }
                }
                // A new version must not silently fork a locally edited tracked predecessor.
                foreach ($registry as $older) {
                    if ($older['package'] === $package['package_id'] && $older['flow'] === $id && $older['state'] === 'imported') { self::untouched($older); }
                }
                $plan[] = compact('key','hash','f','existing');
            }
            $results = []; $changed = false;
            foreach ($plan as $item) {
                ['key' => $key, 'hash' => $hash, 'f' => $f, 'existing' => $existing] = $item;
                if ($existing && $existing['state'] === 'imported') { $results[] = ['flow' => $f['id'], 'id' => $existing['id'], 'result' => 'unchanged']; continue; }
                $title = substr($f['payload']['meta']['title'], 0, 120) . ' [Review ' . $f['version'] . ']';
                $id = BWFAN_Core()->automations->import([$f['payload']], $title, [], false, 2);
                PFI_Package::check(is_numeric($id) && (int)$id > 0, 'Native FunnelKit import failed.');
                $id = (int)$id; $state = self::snapshot($id);
                PFI_Package::check((int)($state['automation']['status'] ?? 0) === 2 && count($state['steps']) === count($f['payload']['step_data']), 'Imported draft failed inactive/step-count verification.');
                self::no_enrollments($id);
                // Verify the native step-ID remapping and full email/wait data readback.
                $meta = BWFAN_Model_Automationmeta::get_automation_meta($id, false);
                $rows = array_column($state['steps'], null, 'ID');
                foreach ($f['payload']['meta']['steps'] as $index => $node) {
                    if (!isset($node['stepId'])) { continue; }
                    $new = $meta['steps'][$index]['stepId'] ?? 0;
                    PFI_Package::check(isset($rows[$new]) && PFI_Package::json($rows[$new]['data']) === PFI_Package::json($f['payload']['step_data'][$node['stepId']]['data']), 'Imported step content or ID mapping failed verification.');
                }
                $registry[$key] = ['package' => $package['package_id'], 'flow' => $f['id'], 'version' => $f['version'], 'id' => $id, 'state' => 'imported', 'payload_hash' => $hash, 'snapshot_hash' => self::hash($state), 'backup' => $state, 'payload' => $f['payload'], 'created_utc' => gmdate('c')];
                $changed = true; $results[] = ['flow' => $f['id'], 'id' => $id, 'result' => 'created_inactive_review_draft'];
            }
            if ($changed) { self::save($registry); }
            return $results;
        });
    }
    public static function no_enrollments(int $id): void {
        global $wpdb;
        foreach (['bwfan_automation_contact','bwfan_automation_complete_contact','bwfan_automation_contact_trail'] as $table) {
            $count=$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}{$table} WHERE aid=%d",$id));
            PFI_Package::check($wpdb->last_error==='' && $count!==null && (int)$count===0, 'Enrollment/history exists or could not be checked.');
        }
    }
    public static function rollback(string $key): void {
        self::transaction(static function () use ($key) {
            global $wpdb;
            $registry = self::registry(); $entry = $registry[$key] ?? null;
            PFI_Package::check($entry && $entry['state'] === 'imported', 'Imported revision not found.');
            $id = (int)$entry['id'];
            // Lock automation row while checking/deleting. Only untouched inactive drafts qualify.
            $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}bwfan_automations WHERE ID=%d FOR UPDATE", $id));
            $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}bwfan_automation_step WHERE aid=%d FOR UPDATE", $id));
            $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}bwfan_automationmeta WHERE bwfan_automation_id=%d FOR UPDATE", $id));
            self::untouched($entry);
            foreach (['bwfan_automation_contact','bwfan_automation_complete_contact','bwfan_automation_contact_trail'] as $table) {
                $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}{$table} WHERE aid=%d", $id));
                PFI_Package::check($wpdb->last_error === '' && $count !== null && (int)$count === 0, 'Rollback blocked: enrollment/history exists or could not be checked.');
            }
            foreach (['bwfan_automation_step' => 'aid', 'bwfan_automationmeta' => 'bwfan_automation_id', 'bwfan_automations' => 'ID'] as $table => $column) {
                PFI_Package::check($wpdb->delete($wpdb->prefix . $table, [$column => $id], ['%d']) !== false, 'Rollback delete failed.');
            }
            $registry[$key]['state'] = 'rolled_back'; $registry[$key]['rolled_back_utc'] = gmdate('c');
            self::save($registry);
            if (class_exists('WooFunnels_Cache')) { WooFunnels_Cache::get_instance()->set_cache('bwfan_automations_meta_' . $id, [], 'autonami'); }
        });
    }
}
