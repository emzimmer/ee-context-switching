<?php

/**
 * Prevent direct access to the rest of the file.
 */
defined( 'ABSPATH' ) || exit( 'WP absolute path is not defined.' );


/**
 * Plugin Directories with Trailing Slashes
 */
global $EE_ContextSwitching;
define( 'EECS_PLUGIN_DIR', $EE_ContextSwitching->get_plugin_root() . 'plugin/' );
define( 'EECS_PLUGIN_URL', $EE_ContextSwitching->get_plugin_root_url() . 'plugin/' );

// dist
define( 'EECS_DIST', EECS_PLUGIN_DIR . 'dist/' );
define( 'EECS_DIST_URL', EECS_PLUGIN_URL . 'dist/' );


/**
 * Enqueue styles and scripts with cached version control
 */
function enqueue_context_switching() {

	global $EE_ContextSwitching;

	$url = EECS_DIST_URL . 'context-switching.js';
	$version = $EE_ContextSwitching->get_product_info( 'version' );

	wp_enqueue_script( 'context-switching', $url, [], $version, true );
}

// Run builder stuff
if ( isset( $_GET['ct_builder'] ) && true == $_GET['ct_builder'] && ! isset( $_GET['oxygen_iframe'] ) ) {
	add_action( 'wp_enqueue_scripts', 'enqueue_context_switching' );
}