<?php
/**
 * Plugin Name: Linn Custom Code
 * Description: Custom functionality for the Linn County project, including taxonomy fields and BuddyBoss integrations.
 * Version: 1.0.0
 * Author: James Welbes
 * Author URI: https://apexbranding.design
 * License: GPL2+
 */

defined( 'ABSPATH' ) || exit;

// Load modular files if you decide to break things out later
require_once plugin_dir_path( __FILE__ ) . 'includes/health-topic-taxonomy.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcodes/display-health-topics.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcodes/groups.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcodes/events.php';
// require_once plugin_dir_path( __FILE__ ) . 'includes/group-meta-connections.php';

/**
 * Helper function to get plugin directory URL
 */
function linn_custom_code_url( $path = '' ) {
    return plugins_url( $path, __FILE__ );
}