<?php
/*
Plugin Name:       Mfields Extensions
Description:       Enables a custom post_type for downloads on ghostbird.mfields.org.
Version:           0.1
Author:            Michael Fields
Author URI:        http://wordpress.mfields.org/
License:           GPLv2 or later

Copyright 2011     Michael Fields  michael@mfields.org

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 or later
as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

Mfields_Extensions_Post_Type::init();
class Mfields_Extensions_Post_Type {
	/**
	 * Initiate.
	 *
	 * Hook into WordPress.
	 *
	 * @return     void
	 * @since      2011-02-20
	 */
	 function init() {
		register_activation_hook( __FILE__, array( __class__, 'activate' ) );
		register_deactivation_hook( __FILE__, array( __class__, 'deactivate' ) );
		add_action( 'init', array( __class__, 'register_post_type' ), 0 );
		add_action( 'init', array( __class__, 'register_taxonomies' ), 0 );
		add_action( 'admin_menu', array( __class__, 'register_meta_boxen' ) );
		add_action( 'save_post', array( __class__, 'meta_save' ), 10, 2 );
	}
	/**
	 * Activation.
	 *
	 * When a user activates this plugin the public pages
	 * for both custom taxonomies and post_types will need
	 * to be immediately available. To ensure that this happens
	 * both post_types and taxonomies need to be registered at
	 * activation so that their rewrite rules will be present
	 * when new rules are added to the database during flush.
	 *
	 * @return     void
	 * @since      2011-02-20
	 */
	function activate() {
		call_user_func( array( __class__, 'register_post_type' ) );
		call_user_func( array( __class__, 'register_taxonomies' ) );
		flush_rewrite_rules();
	}
	/**
	 * Deactivation.
	 *
	 * When a user chooses to deactivate extensionss it is
	 * important to remove all custom object rewrites from
	 * the database.
	 *
	 * @return     void
	 * @since      2011-02-20
	 */
	function deactivate() {
		flush_rewrite_rules();
	}
	/**
	 * Register post_type.
	 *
	 * Registers custom post_type 'mfields_extensions' with
	 * WordPress.
	 *
	 * This method is hooked into the init action and should
	 * fire everywhere save the deactivation hook. When this
	 * plugin is deactivated this method will return early.
	 * This makes it easy for the deactivation() method to do
	 * its job.
	 *
	 * @return     void
	 * @since      2011-02-20
	 */
	function register_post_type() {
		if ( isset( $_REQUEST['action'] ) && 'deactivate' == $_REQUEST['action'] ) {
			return;
		}
		register_post_type( 'mfields_extensions', array(
			'public'        => true,
			'can_export'    => true,
			'has_archive'   => 'extras',
			'rewrite'       => array( 'slug' => 'extra', 'with_front' => false ),
			'menu_position' => 3,
			'supports' => array(
				'title',
				'editor',
				'comments',
				'thumbnail',
				'trackbacks',
				'custom-fields',
				),
			'labels' => array(
				'name'               => 'Extensions',
				'singular_name'      => 'Extension',
				'add_new'            => 'Add New',
				'add_new_item'       => 'Add New Extension',
				'edit_item'          => 'Edit Extension',
				'new_item'           => 'New Extension',
				'view_item'          => 'View Extension',
				'search_items'       => 'Search Extensions',
				'not_found'          => 'No Extensions found',
				'not_found_in_trash' => 'No Extensions found in Trash'
				),
			'decription' => ''
			)
		);
	}
	/**
	 * Register taxonomies.
	 *
	 * Register author taxonomy for extensions post_type.
	 *
	 * @return     void
	 * @since      2011-02-20
	 */
	function register_taxonomies() {
		if ( isset( $_REQUEST['action'] ) && 'deactivate' == $_REQUEST['action'] ) {
			return;
		}
		register_taxonomy( 'mfields_extension_author', 'mfields_extensions', array(
			'hierarchical'          => true,
			'query_var'             => 'extension_author',
			'rewrite'               => array( 'slug' => 'extension-author' ),
			'show_tagcloud'         => false,
			'update_count_callback' => '_update_post_term_count',
			'labels' => array(
				'name'              => 'Authors',
				'singular_name'     => 'Author',
				'search_items'      => 'Search Authors',
				'all_items'         => 'All Authors',
				'parent_item'       => 'Parent Author',
				'parent_item_colon' => 'Parent Author:',
				'edit_item'         => 'Edit Author',
				'update_item'       => 'Update Author',
				'add_new_item'      => 'Add a New Author',
				'new_item_name'     => 'New Author Name'
				)
			) );
	}
	/**
	 * Register Metaboxen.
	 *
	 * @uses       Mfields_Extensions_Post_Type::meta_box()
	 * @since      2011-03-12
	 */
	function register_meta_boxen() {
		add_meta_box( 'mfields_extensions_meta', 'Extension Data', array( __class__, 'meta_box' ), 'mfields_extensions', 'side', 'high' );
	}
	/**
	 * Meta Box.
	 *
	 * @since      2011-03-12
	 */
	function meta_box() {
		/* URL. */
		$key = '_mfields_extensions_url';
		$url = get_post_meta( get_the_ID(), $key, true );
		print "\n\t" . '<p><label for="' . esc_attr( $key ) . '">Plugin URL</label>';
		print "\n\t" . '<input id="' . esc_attr( $key ) . '" type="text" class="widefat" name="' . esc_attr( $key ) . '" value="' . esc_url( $url ) . '" /></p>';

		/* Nonce field. */
		print "\n" . '<input type="hidden" name="mfields_extensions_meta_nonce" value="' . esc_attr( wp_create_nonce( 'update-mfields_extensions-meta-for-' . get_the_ID() ) ) . '" />';
	}
	/**
	 * Save Meta Data.
	 *
	 * @since      2011-03-12
	 */
	function meta_save( $ID, $post ) {
		
		/* Local variables. */
		$ID               = absint( $ID );
		$post_type        = get_post_type();
		$post_type_object = get_post_type_object( $post_type );
		$capability       = '';

		/* Do nothing on auto save. */
		if ( defined( 'DOING_AUTOSAVE' ) && true === DOING_AUTOSAVE ) {
			return;
		}

		/* Return early if custom value is not present in POST request. */
		if ( ! isset( $_POST['_mfields_extensions_url'] ) ) {
			return;
		}

		/* This function only applies to the following post_types. */
		if ( ! in_array( $post_type, array( 'mfields_extensions' ) ) ) {
			return;
		}

		/* Terminate script if accessed from outside the administration panels. */
		check_admin_referer( 'update-mfields_extensions-meta-for-' . $ID, 'mfields_extensions_meta_nonce' );

		/* Find correct capability from post_type arguments. */
		if ( isset( $post_type_object->cap->edit_posts ) ) {
			$capability = $post_type_object->cap->edit_posts;
		}

		/* Return if current user cannot edit this post. */
		if ( ! current_user_can( $capability ) ) {
			return;
		}

		/* Save post meta. */
		update_post_meta( $ID, '_mfields_extensions_url', esc_url_raw( $_POST['_mfields_extensions_url'] ) );

	}
}