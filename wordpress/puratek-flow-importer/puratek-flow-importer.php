<?php
/**
 * Plugin Name: Puratek Flow Importer
 * Description: Preview versioned ZIP packages and import selected FunnelKit workflows as inactive review drafts.
 * Version: 1.9.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Author: Puratek
 * License: GPL-2.0-or-later
 */
if (!defined('ABSPATH')) { exit; }
require_once __DIR__ . '/includes/package.php';
require_once __DIR__ . '/includes/builder.php';
require_once __DIR__ . '/includes/importer.php';
require_once __DIR__ . '/includes/admin.php';
PFI_Admin::boot();
require_once __DIR__ . '/includes/welcome-policy.php';
require_once __DIR__ . '/includes/lifecycle-policy.php';
require_once __DIR__ . '/includes/integration-map.php';
require_once __DIR__ . '/includes/cart-map.php';
require_once __DIR__ . '/includes/lifecycle-runtime.php';
require_once __DIR__ . '/includes/rehearsal-gate.php';
require_once __DIR__ . '/includes/rehearsal-session.php';
require_once __DIR__ . '/includes/consent-bridge.php';
require_once __DIR__ . '/includes/cart-observation.php';
require_once __DIR__ . '/includes/lifecycle-admin.php';
require_once __DIR__ . '/includes/review-lock.php';
add_action('init', static function () {
    if (class_exists('BWFAN_Event') && class_exists('BWFAN_Action') && BWFAN_Core()->integration->get_action('wp_sendemail')) {
        require_once __DIR__ . '/includes/welcome-native.php';
        require_once __DIR__ . '/includes/lifecycle-native.php';
        require_once __DIR__ . '/includes/cart-native.php';
        require_once __DIR__ . '/includes/review-native.php';
    }
}, 30);
