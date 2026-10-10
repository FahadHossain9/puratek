<?php
if (!defined('ABSPATH')) { exit; }

/** ZIP contents are read in memory. Nothing is extracted or executed. */
final class PFI_Package {
    const MAX_ZIP = 67108864;
    const MAX_EXPANDED = 134217728;
    const MAX_FILE = 8388608;

    public static function check($ok, string $message): void {
        if (!$ok) { throw new RuntimeException($message); }
    }
    public static function json(string $text): array {
        $data = json_decode($text, true, 64, JSON_THROW_ON_ERROR);
        self::check(is_array($data), 'Expected a JSON object or array.');
        return $data;
    }
    public static function slug($value): bool {
        return is_string($value) && (bool) preg_match('/\A[a-z0-9][a-z0-9._-]{0,79}\z/', $value);
    }
    public static function path(string $name): bool {
        return $name !== '' && strlen($name) <= 240 && !preg_match('~[\\\\:\x00-\x1f]|^/|(?:^|/)\.\.?(?:/|$)~', $name);
    }
    public static function read(string $path): array {
        self::check(class_exists('ZipArchive'), 'PHP ZipArchive is required.');
        self::check(is_file($path) && filesize($path) <= self::MAX_ZIP, 'ZIP is missing or larger than 64 MB.');
        $zip = new ZipArchive();
        self::check($zip->open($path) === true, 'Cannot open ZIP.');
        try {
            self::check($zip->numFiles > 0 && $zip->numFiles <= 1500, 'ZIP must contain 1–1500 entries.');
            $files = []; $names = []; $total = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $s = $zip->statIndex($i);
                self::check(is_array($s) && self::path($s['name']), 'Unsafe ZIP path.');
                $n = $s['name']; $lower = strtolower($n);
                self::check(!isset($names[$lower]), 'Duplicate ZIP path: ' . $n);
                $names[$lower] = true;
                $opsys = 0; $attr = 0;
                if ($zip->getExternalAttributesIndex($i, $opsys, $attr)) {
                    self::check((($attr >> 16) & 0170000) !== 0120000, 'ZIP symlinks are not accepted.');
                }
                self::check(empty($s['encryption_method']), 'Encrypted ZIP entries are not accepted.');
                $total += $s['size'];
                self::check($s['size'] <= self::MAX_FILE && $total <= self::MAX_EXPANDED, 'ZIP expansion limit exceeded.');
                if (!str_ends_with($n, '/')) { $files[$n] = $i; }
            }
            $manifests = array_values(array_filter(array_keys($files), static fn($n) => basename($n) === 'puratek-package.json'));
            if (!$manifests) {
                $source = array_values(array_filter(array_keys($files), static fn($n) => str_ends_with($n, '/src/flows.ts') || $n === 'src/flows.ts'));
                self::check(!$source, 'Source project detected (flows.ts). This ZIP contains design/specification source, not native FunnelKit workflows. Build the email HTML locally and package it with verified FunnelKit v2 workflow exports and puratek-package.json. No source code was executed and no automation was created.');
                throw new RuntimeException('Missing puratek-package.json. Use the documented package format; an arbitrary ZIP is not a FunnelKit workflow.');
            }
            self::check(count($manifests) === 1, 'Exactly one puratek-package.json is required.');
            $base = substr($manifests[0], 0, -strlen('puratek-package.json'));
            $read = static function (string $relative) use ($zip, $files, $base): string {
                self::check(self::path($relative) && isset($files[$base . $relative]), 'Missing or unsafe package file: ' . $relative);
                $text = $zip->getFromIndex($files[$base . $relative], self::MAX_FILE + 1);
                self::check(is_string($text) && strlen($text) <= self::MAX_FILE, 'Cannot read package file.');
                return $text;
            };
            $m = self::json($read('puratek-package.json'));
            self::check(($m['schema'] ?? null) === 1 && self::slug($m['package_id'] ?? null), 'Invalid schema or package_id.');
            self::check(is_array($m['flows'] ?? null) && count($m['flows']) >= 1 && count($m['flows']) <= 24, 'Package needs 1–24 flows.');
            $flows = [];
            foreach ($m['flows'] as $f) {
                self::check(is_array($f) && self::slug($f['id'] ?? null) && self::slug($f['version'] ?? null), 'Invalid flow ID or version.');
                self::check(!isset($flows[$f['id']]), 'Duplicate flow ID.');
                self::check(is_string($f['file'] ?? null), 'Workflow file is required.');
                $p = self::json($read($f['file']));
                self::check(array_is_list($p) && count($p) === 1 && is_array($p[0]), 'Each workflow file must be a native export array containing one automation.');
                $p = $p[0];
                self::validate_workflow($p);
                foreach (($f['emails'] ?? []) as $binding) {
                    self::check(is_array($binding), 'Invalid email binding.');
                    $sid = (string) ($binding['step_id'] ?? '');
                    self::check(isset($p['step_data'][$sid]) && (int)$p['step_data'][$sid]['type'] === 2, 'Email binding must target an email action step.');
                    self::check(is_string($binding['html'] ?? null) && is_string($binding['subject'] ?? null), 'Email binding needs html and subject.');
                    $html = $read($binding['html']);
                    self::check(strlen($html) <= 104448, 'Email HTML exceeds 102 KB.');
                    self::check(!preg_match('~<\s*(script|iframe|object|embed|form)\b|\bon[a-z]+\s*=|javascript\s*:|data\s*:\s*text/html~i', $html), 'Unsafe active content in email HTML.');
                    $d = self::json($p['step_data'][$sid]['data']);
                    $email = ['subject' => $binding['subject'], 'mode' => 3, 'data' => [], 'template' => $html];
                    if (($binding['editor'] ?? '') === 'builder') { $email = PFI_Builder::convert($email); }
                    PFI_Builder::email($email);
                    if (!empty($d['sidebarData']['pfi_variants'])) {
                        $variants = $d['sidebarData']['pfi_variants'];
                        $variant = $binding['variant'] ?? (count($variants) === 1 ? array_key_first($variants) : null);
                        self::check(is_string($variant) && isset($variants[$variant]), 'Multi-variant email binding needs a valid variant.');
                        $d['sidebarData']['pfi_variants'][$variant] = $email;
                        $d['sidebarData']['pfi_editor_variant'] = $variant;
                    }
                    $d['sidebarData']['bwfan_email_data'] = $email;
                    $p['step_data'][$sid]['data'] = wp_json_encode($d);
                }
                self::validate_workflow($p);
                $flows[$f['id']] = ['id' => $f['id'], 'version' => $f['version'], 'payload' => $p];
            }
            return ['package_id' => $m['package_id'], 'flows' => $flows];
        } finally { $zip->close(); }
    }

    /** v1 supports the verified linear wait/email subset; unknown graph types fail closed. */
    public static function validate_workflow(array $p): void {
        self::check((string)($p['data']['v'] ?? '') === '2', 'Only FunnelKit v2 exports are supported.');
        self::check(is_string($p['meta']['title'] ?? null) && strlen($p['meta']['title']) > 0 && strlen($p['meta']['title']) <= 160, 'Workflow needs a title (max 160 bytes).');
        foreach (['source', 'event'] as $field) { self::check(self::slug($p['data'][$field] ?? null), 'Missing source/event.'); }
        self::check(is_array($p['step_data'] ?? null) && count($p['step_data']) > 0 && count($p['step_data']) <= 100, 'Invalid workflow step count.');
        self::check(is_array($p['meta']['steps'] ?? null) && is_array($p['meta']['links'] ?? null), 'Workflow graph is missing.');
        $nodes = []; $used = [];
        foreach ($p['meta']['steps'] as $node) {
            $id = (string)($node['id'] ?? ''); $type = $node['type'] ?? '';
            self::check($id !== '' && !isset($nodes[$id]) && in_array($type, ['start','end','wait','action'], true), 'Unsupported or duplicate workflow node. v1 accepts linear wait/email flows only.');
            $nodes[$id] = $type;
            if (in_array($type, ['wait','action'], true)) {
                $sid = (string)($node['stepId'] ?? '');
                self::check(ctype_digit($sid) && isset($p['step_data'][$sid]) && !isset($used[$sid]), 'Invalid or repeated step reference.');
                $used[$sid] = true; $row = $p['step_data'][$sid];
                self::check((int)($row['type'] ?? 0) === ($type === 'wait' ? 1 : 2), 'Step type does not match graph.');
                self::check(is_string($row['data'] ?? null), 'Step data must be JSON.');
                $d = self::json($row['data']);
                self::check(!isset($d['sidebarData']['jump_to']) && !isset($d['sidebarData']['data']['skip_to_step']), 'Jump steps are not supported by this adapter.');
                if ($type === 'action') {
                    $a = self::json($row['action'] ?? '{}');
                    self::check(in_array($a['action'] ?? '', ['wp_sendemail','pfi_welcome_email','pfi_lifecycle_email','pfi_review_email'], true), 'Only email actions are supported by this adapter.');
                    if (in_array($a['action'] ?? '', ['pfi_lifecycle_email','pfi_review_email'], true)) {
                        $flow=$p['meta']['pfi_flow']??'';$step=$d['sidebarData']['pfi_step']??'';
                        $review=($a['action']??'')==='pfi_review_email';
                        self::check(in_array($flow,PFI_Lifecycle_Policy::FLOWS,true)&&($p['meta']['pfi_profile']??'')===($review?'live-review-v1':'lifecycle-local-v1')&&($p['data']['event']??'')===($review?'pfi_review_only':'pfi_lifecycle_'.str_replace('-','_',$flow)),'Invalid lifecycle profile/event.');
                        self::check(in_array($step,PFI_Lifecycle_Policy::STEPS[$flow],true),'Unknown lifecycle step.');
                        $variants=$d['sidebarData']['pfi_variants']??[];$expected=$step==='c2'?['c2a','c2b']:($step==='b'?['b1','b2','b3']:[$step]);
                        self::check(array_keys($variants)===$expected,'Lifecycle variant set does not match step.');
                        foreach($variants as$email) {
                            self::check(is_array($email), 'Invalid lifecycle email.');
                            PFI_Builder::email($email);
                        }
                    }
                    if (($a['action'] ?? '') === 'pfi_welcome_email') {
                        self::check(($p['data']['event'] ?? '') === 'pfi_welcome_opt_in' && ($p['meta']['pfi_profile'] ?? '') === 'welcome-local-v1', 'Guarded Welcome action requires its local profile and event.');
                        self::check(in_array($d['sidebarData']['pfi_template'] ?? '', ['w1','w2','w3'], true), 'Unknown Welcome template.');
                    }
                    self::check(is_array($d['sidebarData']['bwfan_email_data'] ?? null), 'Missing email content.');
                    PFI_Builder::email($d['sidebarData']['bwfan_email_data']);
                }
                $allowed = ['ID','aid','type','action','status','data','created_at','updated_at'];
                self::check(!array_diff(array_keys($row), $allowed), 'Unknown step database fields.');
            } else { self::check($id === $type && !isset($node['stepId']), 'Invalid start/end node.'); }
        }
        self::check(count($used) === count($p['step_data']) && isset($nodes['start'], $nodes['end']), 'Orphaned steps or missing start/end.');
        $edges = []; $incoming = [];
        foreach ($p['meta']['links'] as $link) {
            $s = (string)($link['source'] ?? ''); $t = (string)($link['target'] ?? '');
            self::check(isset($nodes[$s], $nodes[$t]) && !isset($edges[$s]) && !isset($incoming[$t]) && $s !== 'end' && $t !== 'start', 'Invalid links or branching graph.');
            $edges[$s] = $t; $incoming[$t] = $s;
        }
        $seen = []; $at = 'start';
        while ($at !== 'end') {
            self::check(!isset($seen[$at]) && isset($edges[$at]), 'Cycle or disconnected workflow.');
            $seen[$at] = true; $at = $edges[$at];
        }
        self::check(count($seen) + 1 === count($nodes), 'Unreachable workflow nodes.');
        if (in_array($p['meta']['pfi_profile']??'', ['lifecycle-local-v1','live-review-v1'], true)) {
            $expected_action=$p['meta']['pfi_profile']==='live-review-v1'?'pfi_review_email':'pfi_lifecycle_email';
            self::check($p['data']['source']==='wp','Coordinated profiles require the WordPress source.');
            $ordered=[];$at='start';
            while(($at=$edges[$at])!=='end') {
                $node=current(array_filter($p['meta']['steps'],static fn($n)=>(string)$n['id']===$at));
                $row=$p['step_data'][$node['stepId']];
                self::check((self::json($row['action']??'{}')['action']??'')===$expected_action,'Lifecycle accepts only coordinated actions.');
                $ordered[]=self::json($row['data'])['sidebarData']['pfi_step'];
            }
            self::check($ordered===(PFI_Lifecycle_Policy::STEPS[$p['meta']['pfi_flow']??'']??null),'Lifecycle steps must match the complete ordered flow.');
        }
    }

    public static function mappings(array $package, array $map): array {
        foreach ($map as $key => $value) {
            self::check(self::slug($key) && is_string($value) && strlen($value) <= 2048, 'Invalid mapping.');
        }
        $replace = static function ($v) use (&$replace, $map) {
            if (is_array($v)) { return array_map($replace, $v); }
            if (!is_string($v)) { return $v; }
            return preg_replace_callback('/\[\[PFI:([a-z0-9._-]+)\]\]/', static function ($m) use ($map) {
                self::check(isset($map[$m[1]]) && trim($map[$m[1]]) !== '', 'Missing mapping: ' . $m[1]);
                // Mappings are plain text/URLs or native merge tags, never executable HTML.
                self::check(!preg_match('/[<>"\x00-\x1f]/', $map[$m[1]]), 'Mappings cannot contain HTML, quotes or control characters.');
                self::check(!preg_match('/\A\s*(javascript|data|vbscript)\s*:/i', $map[$m[1]]), 'Unsafe mapping URL.');
                return htmlspecialchars($map[$m[1]], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }, $v);
        };
        foreach ($package['flows'] as &$f) {
            // Parse nested JSON before substitution, preventing quote/backslash corruption.
            foreach ($f['payload']['step_data'] as &$step) {
                $step['data'] = wp_json_encode($replace(self::json($step['data'])));
            } unset($step);
            $f['payload']['meta'] = $replace($f['payload']['meta']);
            self::check(!preg_match('/\[\[PFI:|%%[A-Z0-9_]+%%/', wp_json_encode($f['payload'])), 'Unresolved template placeholders.');
        } unset($f);
        return $package;
    }
}
