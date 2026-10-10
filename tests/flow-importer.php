<?php
// Run with PHP 8.2+, DOM and ZipArchive; no WordPress installation required.
define('ABSPATH', __DIR__ . '/');
function wp_json_encode($value, $flags = 0) { return json_encode($value, $flags | JSON_THROW_ON_ERROR); }
$root = dirname(__DIR__);
$plugin = $root . '/wordpress/puratek-flow-importer';
require $plugin . '/includes/package.php';
require $plugin . '/includes/lifecycle-policy.php';
require $plugin . '/includes/builder.php';
function verify($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
$directory = $root . '/automation-packages/puratek-local';
$manifest = PFI_Package::json(file_get_contents($directory . '/puratek-package.json'));
$package = ['package_id'=>$manifest['package_id'], 'flows'=>[]];
$variants = 0;
foreach ($manifest['flows'] as $flow) {
    $payload = PFI_Package::json(file_get_contents($directory . '/' . $flow['file']))[0];
    PFI_Package::validate_workflow($payload);
    $package['flows'][$flow['id']] = ['id'=>$flow['id'], 'version'=>$flow['version'], 'payload'=>$payload];
    foreach ($payload['step_data'] as $step) {
        $data = PFI_Package::json($step['data']);
        foreach ($data['sidebarData']['pfi_variants'] ?? [] as $email) {
            verify($email['mode'] === 5, 'Expected native mode 5');
            verify(hash('sha256', $email['template']) === $email['data']['pfi_conversion']['source_hash'], 'Source HTML changed');
            preg_match_all('/<!-- wp:email-block\/[a-z-]+ (\{.*?\}) (?:\/)?-->/s', $email['data']['block']['body'], $matches);
            verify(count($matches[1]) > 0, 'Block document missing');
            foreach ($matches[1] as $json) { PFI_Package::json($json); }
            $variants++;
        }
    }
}
verify(count($package['flows']) === 7 && $variants === 21, 'Incomplete package');
verify(PFI_Builder::package($package) === $package, 'Conversion must be idempotent');
$sidebar = ['pfi_editor_variant'=>'c2a', 'bwfan_email_data'=>['subject'=>'edited'], 'pfi_variants'=>['c2a'=>['subject'=>'old'], 'c2b'=>['subject'=>'other']]];
verify(PFI_Builder::selected($sidebar, 'c2a')['subject'] === 'edited', 'Saved editor content ignored');
verify(PFI_Builder::selected($sidebar, 'c2b')['subject'] === 'other', 'Other variant overwritten');
$sampleEmail = null;
foreach ($package['flows']['welcome']['payload']['step_data'] as $step) {
    $data = PFI_Package::json($step['data']);
    if (isset($data['sidebarData']['bwfan_email_data'])) { $sampleEmail = $data['sidebarData']['bwfan_email_data']; break; }
}
verify(is_array($sampleEmail), 'Missing sample email');
foreach (["bad\r\nBcc: someone@example.invalid", "bad\0subject"] as $subject) {
    $badEmail = $sampleEmail; $badEmail['subject'] = $subject; $rejected = false;
    try { PFI_Builder::email($badEmail); } catch (RuntimeException $e) { $rejected = true; }
    verify($rejected, 'Unsafe subject accepted');
}
$badEmail = $sampleEmail;
$badEmail['data']['block']['body'] = '<!-- wp:email-block/row {} --><!-- wp:email-block/section {} --><!-- wp:email-block/column {} --><!-- wp:email-block/text {"content":"<img src=x onerror=alert(1)>"} /--><!-- /wp:email-block/column --><!-- /wp:email-block/section --><!-- /wp:email-block/row -->';
$rejected = false;
try { PFI_Builder::email($badEmail); } catch (RuntimeException $e) { $rejected = true; }
verify($rejected, 'Unsafe builder attributes accepted');
foreach (['<!-- wp:email-block/row {} -->', '<!-- wp:email-block/text {bad json} /-->'] as $body) {
    $rejected = false;
    try { PFI_Builder::document($body); } catch (Throwable $e) { $rejected = true; }
    verify($rejected, 'Broken builder document accepted');
}
$mappedPackage = $package;
foreach ($mappedPackage['flows']['welcome']['payload']['step_data'] as &$step) {
    $data = PFI_Package::json($step['data']);
    if (!isset($data['sidebarData']['pfi_variants'])) { continue; }
    foreach ($data['sidebarData']['pfi_variants'] as &$email) {
        $email['template'] = str_replace('</body>', '<p>[[PFI:label]]</p></body>', $email['template']);
        $email['data']['block']['template'] = $email['template'];
        $email['data']['block']['body'] = str_replace('wp:email-block/text {', 'wp:email-block/text {"pfi_test_label":"[[PFI:label]]",', $email['data']['block']['body']);
    } unset($email);
    $key = $data['sidebarData']['pfi_editor_variant'];
    $data['sidebarData']['bwfan_email_data'] = $data['sidebarData']['pfi_variants'][$key];
    $step['data'] = wp_json_encode($data);
} unset($step);
$mappedPackage = PFI_Package::mappings($mappedPackage, ['label'=>'C:\\Puratek\\draft & review']);
foreach ($mappedPackage['flows']['welcome']['payload']['step_data'] as $step) {
    $data = PFI_Package::json($step['data']);
    foreach ($data['sidebarData']['pfi_variants'] ?? [] as $email) {
        preg_match('/<!-- wp:email-block\/text (\{.*?\}) \/-->/s', $email['data']['block']['body'], $match);
        verify(PFI_Package::json($match[1])['pfi_test_label'] === 'C:\\Puratek\\draft &amp; review', 'Mapping changed backslashes or HTML escaping');
    }
}
$edited = $package;
foreach ($edited['flows']['cart']['payload']['step_data'] as &$step) {
    $data = PFI_Package::json($step['data']);
    if (($data['sidebarData']['pfi_step'] ?? '') !== 'c2') { continue; }
    $data['sidebarData']['pfi_editor_variant'] = 'c2b';
    $data['sidebarData']['bwfan_email_data'] = $data['sidebarData']['pfi_variants']['c2b'];
    $data['sidebarData']['bwfan_email_data']['subject'] = 'Saved returning customer subject';
    $step['data'] = wp_json_encode($data);
} unset($step);
$edited = PFI_Builder::package($edited);
foreach ($edited['flows']['cart']['payload']['step_data'] as $step) {
    $data = PFI_Package::json($step['data']);
    if (($data['sidebarData']['pfi_step'] ?? '') === 'c2') {
        verify($data['sidebarData']['pfi_editor_variant'] === 'c2b' && $data['sidebarData']['pfi_variants']['c2b']['subject'] === 'Saved returning customer subject', 'Conversion discarded saved editor variant');
    }
}
foreach (['<script>alert(1)</script>', '<img src="x" onerror="alert(1)">', '<a href="vbscript:x">x</a>', '<a href="java&#x73;cript:alert(1)">x</a>', '<a href="java&#10;script:alert(1)">x</a>'] as $html) {
    $rejected = false;
    try { PFI_Builder::html($html); } catch (RuntimeException $e) { $rejected = true; }
    verify($rejected, 'Active content accepted');
}
if (class_exists('ZipArchive')) {
    $path = __DIR__ . '/.pfi-test-' . bin2hex(random_bytes(8)) . '.zip';
    try {
        $zip = new ZipArchive();
        verify($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 'Cannot create test ZIP');
        foreach (glob($directory . '/*.json') as $file) { $zip->addFile($file, basename($file)); }
        verify($zip->close(), 'Cannot write test ZIP');
        verify(PFI_Package::read($path) === $package, 'ZIP parser changed payload');
    } finally { if (file_exists($path)) { unlink($path); } }
} else { throw new RuntimeException('ZipArchive is required to test the import package'); }
echo "PASS: 7 flows, 21 variants, preserved HTML, block structure/attributes, variant isolation, saved editor retention, encoded URL/header rejection, JSON-safe mappings, conversion idempotence and ZIP readback.\n";
