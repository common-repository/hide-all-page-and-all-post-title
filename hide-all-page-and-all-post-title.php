<?php
/*
Plugin Name: Hide All Page And All Post Title
Plugin URI: https://profiles.wordpress.org/sureshkldh#content-plugins/
Description: Allows the authors hide the title on single pages and single posts via the edit admin post screen.
Version: 1.0
Author: Suresh Kumar
Author URI: https://profiles.wordpress.org/sureshkldh
*/

/**
 * Load the plugin in domain
 */
function wphp_LoadPluginHideTitle() {
	load_plugin_textdomain(
		'hide-all-page-and-all-post-title', false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}

add_action( 'plugins_loaded', 'wphp_LoadPluginHideTitle' );

/**
 * Filter the title and return empty string if necessary.
 *
 * @param $title string The old title
 * @param int $post_id The post ID
 *
 * @return string Old title or empty string.
 */



function wphpSuppressTitle( $title, $post_id = 0 ) {
	if ( ! $post_id ) {
		return $title;
	}

	$hide_title = get_post_meta( $post_id, 'wphp_hideTitle', true );
	if ($hide_title==1 ) {
		$title = '';
	}
	return $title;
}

add_filter( 'the_title', 'wphpSuppressTitle', 10, 2 );

/*--------------------------------------------------
	MetaBox
----------------------------------------------------*/

add_action( 'load-post.php', 'wphpPostMetaBoxesSetup' );
add_action( 'load-post-new.php', 'wphpPostMetaBoxesSetup' );

function wphpPostMetaBoxesSetup() {
	/* Add meta boxes on the 'add_meta_boxes' hook. */
	add_action( 'add_meta_boxes', 'wphpAddPostMetaBoxes' );

	/* Save post meta on the 'save_post' hook. */
	add_action( 'save_post', 'wphpSaveMeta', 10, 2 );
}

function wphpAddPostMetaBoxes() {
	add_meta_box(
		'wphp-hide-all-page-and-all-post-title',
		esc_html__( 'Hide All Page And All Post Title?', 'hide-all-page-and-all-post-title' ),
		'wphpRenderMetabox',
		null,
		'side',
		'core'
	);
}

function wphpRenderMetabox( $post ) {
	$curr_value = get_post_meta( $post->ID, 'wphp_hideTitle', true );
	wp_nonce_field( basename( __FILE__ ), 'wphp_meta_nonce' );
	?>
	<input type="hidden" name="wphp_hide_title" value="0"/>
	<input type="checkbox" name="wphp_hide_title" id="wphp_hide_title"
	       value="1" <?php checked( $curr_value, '1' ); ?> />
	<label for="wphp_hide_title"><?php esc_html_e( 'Hide the title for this post', 'hide-all-page-and-all-post-title' ); ?></label>
	<?php
}

function wphpSaveMeta( $post_id, $post ) {

	/* Verify the nonce before proceeding. */
	if ( ! isset( $_POST['wphp_meta_nonce'] ) || ! wp_verify_nonce( $_POST['wphp_meta_nonce'], basename( __FILE__ ) ) ) {
		return;
	}

	/* Get the post type object. */
	$post_type = get_post_type_object( $post->post_type );

	/* Check if the current user has permission to edit the post. */
	if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
		return;
	}

	/* Get the posted data and sanitize it for use as an HTML class. */
	$form_data = sanitize_text_field( $_POST['wphp_hide_title'] );
	update_post_meta( $post->ID, 'wphp_hideTitle', $form_data );
}
