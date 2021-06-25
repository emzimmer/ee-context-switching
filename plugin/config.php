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
 * Get Context Switching Data
 * 
 * Get all the data for templates, reusables, and pages that are required for CSwitching
 * 
 * @return array $templates An array of arrays, with titls, id, nonce, and bool for inner content
 * 							for each template, reusable, or page
 */
function get_context_switching_data() {

	$templates = [
		'templates'      => [],
		'reusable_parts' => [],
		'pages'          => []
	];

	$context_switching_query = get_posts([
		'post_type'   => 'ct_template',
		'order'       => 'ASC',
		'orderby'     => 'title',
		'numberposts' => -1
	]);

	if ( ! empty( $context_switching_query ) ) {

		foreach ( $context_switching_query as $index => $template ) {

			$template_type = get_post_meta( $template->ID, 'ct_template_type', true ) == 'reusable_part'
							? 'reusable_parts'
							: 'templates';

			// Check if inner content
			$ct_parent_template = get_post_meta( $template->ID, 'ct_parent_template', true );

			$shortcodes = '';

			if ( $ct_parent_template && $ct_parent_template > 0 ) {
				$shortcodes = get_post_meta( $ct_parent_template, 'ct_builder_shortcodes', true );
			}

			$isInner = $shortcodes && strpos( $shortcodes, '[ct_inner_content' );

			// Set the template data
			$template_data = [
				'title'   => $template->post_title,
				'id'      => $template->ID,
				'nonce'   => wp_create_nonce( 'oxygen-nonce-' . $template->ID ),
				'isInner' => $isInner
			];

			$templates[$template_type][$template->post_name] = $template_data;
		}

		wp_reset_postdata();
	}

	// Now, pages?
	$context_switching_query = get_posts([
		'post_type'   => 'page',
		'order'       => 'ASC',
		'orderby'     => 'title',
		'numberposts' => -1
	]);

	if ( ! empty( $context_switching_query ) ) {

		foreach ( $context_switching_query as $index => $page ) {

			// Get outer template ID
			$page_other_template_id = get_post_meta( $page->ID, 'ct_other_template', true );
			
			// boolean: true if template id exists and is greater than 0
			$page_uses_other_template = $page_other_template_id > 0;

			// Assume that the page is inner content if other template > 0
			$isInner = $page_uses_other_template;

			// It says page uses a template. However, if the template has been deleted, then the page
			// will still resolve as inner content (since the post meta for the page itself does not
			// update just because the template was deleted). To remedy this, verify that the template
			// still exists.

			// To take it one step further, also verify that the template is published, and not reverted
			// back to a draft or otherwise.

			// If other template id is greater than 0
			if ( $page_other_template_id ) {

				// get the post status of the other template to verify if the page should render as inner
				$isInner = get_post_status ( $page_other_template_id ) == 'publish';
			}

			// Otherwise...
			// Set the template data
			$page_data = [
				'title'   => $page->post_title,
				'id'      => $page->ID,
				'nonce'   => wp_create_nonce( 'oxygen-nonce-' . $page->ID ),
				'isInner' => $isInner
			];

			$templates['pages'][$page->post_name] = $page_data;
		}

		wp_reset_postdata();
	}
	
	// Set setting
	$templates = ! empty( $templates ) ? $templates : false;

	return $templates;
}


/**
 * AJAX for context refresh
 */
add_action( 'wp_ajax_ee_refresh_contexts', 'ee_refresh_contexts' );

function ee_refresh_contexts() {

	// Set metaId and postId values to nice names.
	// $post_id = intval( $_POST['postId'] );
	// $meta_id = intval( $_POST['metaId'] );

	$templates = [
		'templates' => [],
		'reusable_parts' => [],
		'pages' => []
	];

	$csq = get_posts([
		'post_type' => 'ct_template',
		'order' => 'ASC',
		'orderby' => 'title',
		'numberposts' => -1
	]);

	if ( ! empty( $csq ) ) :

		foreach ( $csq as $index => $template ) :

			$template_type = get_post_meta( $template->ID, 'ct_template_type', true ) == 'reusable_part'
							? 'reusable_parts'
							: 'templates';

			// Check if inner content
			$ct_parent_template = get_post_meta( $template->ID, 'ct_parent_template', true );

			$shortcodes = '';

			if ( $ct_parent_template && $ct_parent_template > 0 )
				$shortcodes = get_post_meta( $ct_parent_template, 'ct_builder_shortcodes', true );	

			$isInner = $shortcodes && strpos( $shortcodes, '[ct_inner_content' );

			// Set the template data
			$template_data = [
				'title' => $template->post_title,
				'id' => $template->ID,
				'nonce' => wp_create_nonce( 'oxygen-nonce-' . $template->ID ),
				'isInner' => $isInner
			];

			$templates[$template_type][$template->post_name] = $template_data;
			
		endforeach;

		wp_reset_postdata();
	endif;

	// Now, pages?
	$csq = get_posts([
		'post_type' => 'page',
		'order' => 'ASC',
		'orderby' => 'title',
		'numberposts' => -1
	]);

	if ( ! empty( $csq ) ) :

		foreach ( $csq as $index => $page ) :

			// Get outer template ID
			$page_other_template_id = get_post_meta( $page->ID, 'ct_other_template', true );
			
			// boolean: true if template id exists and is greater than 0
			$page_uses_other_template = $page_other_template_id > 0;

			// Assume that the page is inner content if other template > 0
			$isInner = $page_uses_other_template;

			// It says page uses a template. However, if the template has been deleted, then the page
			// will still resolve as inner content (since the post meta for the page itself does not
			// update just because the template was deleted). To remedy this, verify that the template
			// still exists.

			// To take it one step further, also verify that the template is published, and not reverted
			// back to a draft or otherwise.

			// If other template id is greater than 0
			if ( $page_other_template_id ) :

				// get the post status of the other template to verify if the page should render as inner
				$isInner = get_post_status ( $page_other_template_id ) == 'publish';
			endif;

			// Otherwise...
			// Set the template data
			$page_data = [
				'title' => $page->post_title,
				'id' => $page->ID,
				'nonce' => wp_create_nonce( 'oxygen-nonce-' . $page->ID ),
				'isInner' => $isInner
			];

			$templates['pages'][$page->post_name] = $page_data;
			
		endforeach;

		wp_reset_postdata();
	endif;

	
	// Set setting
	$templates = ! empty( $templates ) ? json_encode( $templates ) : false;
	// $builder_settings->set( 'context_templates', $templates );

	// Respond with the value of the meta ID so the JS can finish on its
	// end, then die.
	echo $templates;
	wp_die();
}


/**
 * Enqueue styles and scripts with cached version control
 */
function enqueue_context_switching() {

	global $EE_ContextSwitching, $post;

	$localize = [
		'home_url'          => get_home_url(),
		'admin_url'         => admin_url(),
		'oxy_icons'         => get_home_url() . '/wp-content/plugins/oxygen/component-framework/toolbar/UI/oxygen-icons/',
		'ajaxurl'           => admin_url( 'admin-ajax.php' ),
		'post_id'           => $post->ID,
		'post_name'         => get_the_title( $post->ID ),
		'context_switching' => true,
		'context_templates' => get_context_switching_data()
	];

	$url = EECS_DIST_URL . 'context-switching.js';
	$version = $EE_ContextSwitching->get_product_info( 'version' );

	wp_enqueue_script( 'context-switching', $url, [], $version, true );
	wp_localize_script( 'context-switching', 'eeContextSwitchingSettings', $localize );
}

// Run builder stuff
if ( isset( $_GET['ct_builder'] ) && true == $_GET['ct_builder'] && ! isset( $_GET['oxygen_iframe'] ) ) {
	add_action( 'wp_enqueue_scripts', 'enqueue_context_switching' );
}