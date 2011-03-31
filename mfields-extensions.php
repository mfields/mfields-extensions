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

Mfields_Extension_Controller::init();

class Mfields_Extension {
	/**
	 * Initiate.
	 *
	 * Hook into WordPress.
	 *
	 * @return     void
	 * @since      2011-02-20
	 */
	 function init() {
		add_action( 'init', array( __class__, 'register' ), 0 );
		add_action( 'admin_menu', array( __class__, 'meta_boxen' ) );
		add_action( 'save_post', array( __class__, 'meta_save' ), 10, 2 );
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
	function register() {
		if ( isset( $_REQUEST['action'] ) && 'deactivate' == $_REQUEST['action'] ) {
			return;
		}
		register_post_type( 'mfields_extension', array(
			'public'        => true,
			'can_export'    => true,
			'has_archive'   => 'extensions',
			'rewrite'       => array( 'slug' => 'extensions', 'with_front' => false ),
			'menu_position' => 3,
			'supports' => array(
				'title',
				'editor',
				'excerpt',
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
	 * Register Metaboxen.
	 *
	 * @uses       Mfields_Extension::meta_box()
	 * @since      2011-03-12
	 */
	function meta_boxen() {
		add_meta_box( 'mfields_extension_meta', 'Extension Data', array( __class__, 'meta_box' ), 'mfields_extension', 'side', 'high' );
	}
	/**
	 * Meta Box.
	 *
	 * @since      2011-03-12
	 */
	function meta_box() {
		/* WordPress URL. */
		$key = '_mfields_extension_wordpress_url';
		$url = get_post_meta( get_the_ID(), $key, true );
		print "\n\t" . '<p><label for="' . esc_attr( $key ) . '">WordPress URL</label>';
		print "\n\t" . '<input id="' . esc_attr( $key ) . '" type="text" class="widefat" name="' . esc_attr( $key ) . '" value="' . esc_url( $url ) . '" /></p>';

		/* WordPress URL. */
		$key = '_mfields_extension_github_dev_url';
		$url = get_post_meta( get_the_ID(), $key, true );
		print "\n\t" . '<p><label for="' . esc_attr( $key ) . '">Github Dev URL</label>';
		print "\n\t" . '<input id="' . esc_attr( $key ) . '" type="text" class="widefat" name="' . esc_attr( $key ) . '" value="' . esc_url( $url ) . '" /></p>';

		/* WordPress URL. */
		$key = '_mfields_extension_github_release_url';
		$url = get_post_meta( get_the_ID(), $key, true );
		print "\n\t" . '<p><label for="' . esc_attr( $key ) . '">Github Release URL</label>';
		print "\n\t" . '<input id="' . esc_attr( $key ) . '" type="text" class="widefat" name="' . esc_attr( $key ) . '" value="' . esc_url( $url ) . '" /></p>';

		/* Nonce field. */
		print "\n" . '<input type="hidden" name="mfields_extension_meta_nonce" value="' . esc_attr( wp_create_nonce( 'update-mfields_extension-meta-for-' . get_the_ID() ) ) . '" />';
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
		if ( ! isset( $_POST['_mfields_extension_wordpress_url'] ) ) {
			return;
		}

		/* This function only applies to the following post_types. */
		if ( ! in_array( $post_type, array( 'mfields_extension' ) ) ) {
			return;
		}

		/* Terminate script if accessed from outside the administration panels. */
		check_admin_referer( 'update-mfields_extension-meta-for-' . $ID, 'mfields_extension_meta_nonce' );

		/* Find correct capability from post_type arguments. */
		if ( isset( $post_type_object->cap->edit_posts ) ) {
			$capability = $post_type_object->cap->edit_posts;
		}

		/* Return if current user cannot edit this post. */
		if ( ! current_user_can( $capability ) ) {
			return;
		}

		/* Save post meta. */
		update_post_meta( $ID, '_mfields_extension_wordpress_url', esc_url_raw( $_POST['_mfields_extension_wordpress_url'] ) );
		update_post_meta( $ID, '_mfields_extension_github_dev_url', esc_url_raw( $_POST['_mfields_extension_github_dev_url'] ) );
		update_post_meta( $ID, '_mfields_extension_github_release_url', esc_url_raw( $_POST['_mfields_extension_github_release_url'] ) );
	}
}

class Mfields_Extension_Author {
	/**
	 * Initiate.
	 *
	 * Hook into WordPress.
	 *
	 * @return     void
	 * @since      2011-05-25
	 */
	function init() {
		add_action( 'init', array( __class__, 'register' ), 0 );
		add_action( 'init', array( __class__, 'customize_wpdb' ) );
		add_filter( 'mfields_open_graph_meta_tags_term_mfields_extension_author', array( __class__, 'open_graph_data' ), 10, 2 );
		add_action ( 'mfields_extension_author_edit_form_fields', array( __class__, 'meta_controls' ) );
	}
	/**
	 * Open Graph Data.
	 *
	 * Hook into the "Mfields Open Graph Meta" Plugin.
	 *
	 * @return     void
	 * @since      2011-05-25
	 */
	function open_graph_data( $data, $term ) {
		$data['type'] = 'author';
		return $data;
	}
	function create_meta_table() {

		global $wpdb;

		$tablename = mysql_real_escape_string( $wpdb->prefix . 'mfields_extension_authormeta' );

		/* Return early if table already exists. */
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tablename ) ) == $tablename ) {
			return;
		}

		/*
		 * This file defines the dbDelta() function which will
		 * be used to create the custom table.
		 */
		$file = ABSPATH . 'wp-admin/includes/upgrade.php';

		/* I fear hardcoded paths. */
		if ( ! file_exists( $file ) ) {
			$old_error_handler = set_error_handler( create_function( '$errno, $errstr, $errfile, $errline', "exit( '<p>' . __( 'Author meta table could not be created because upgrade.php could not be found in the wp-admin/includes/ directory.', 'mfields_extension' ) . '</p>' );" ) );
			trigger_error( '', E_USER_ERROR );
		}

		require_once( $file );

		/* Create the custom table for author meta. */
		dbDelta( "CREATE TABLE `{$tablename}` (
			`meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`author_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			`meta_key` varchar(255) DEFAULT NULL,
			`meta_value` longtext,
			PRIMARY KEY (`meta_id`),
			KEY `comment_id` (`author_id`),
			KEY `meta_key` (`meta_key`)
		);" );

		/* Table was not created. Do not install. */
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tablename ) ) != $tablename ) {
			$old_error_handler = set_error_handler( create_function( '$errno, $errstr, $errfile, $errline', "exit( __( 'Author meta table could not be created.', 'mfields_extension'); );" ) );
			trigger_error( '', E_USER_ERROR );
		}
	}
	/**
	 * Customize $wpdb.
	 *
	 * Register the author taxonomy's metadata table with the $wpdb object.
	 *
	 * @since      2011-02-20
	 */
	function customize_wpdb() {
		global $wpdb;
		$wpdb->mfields_extension_author = $wpdb->prefix . 'mfields_extension_author';
	}
	function meta_controls( $term ) {

		/* Term must possess the following properties. */
		if ( ! isset( $term->term_id ) ) {
			return;
		}
		if ( ! isset( $term->taxonomy ) ) {
			return;
		}

		/* Website Url. */
		$id = $term->taxonomy . '_url_website';
		print "\n" . '<tr class="form-field">';
		print "\n" . '<th scope="row" valign="top"><label for="' . esc_attr( $id ) . '">' . esc_html__( 'Website', 'mfields-extensions' ) . '</label></th>';
		print "\n" . '<td>';
		print "\n" . '<input type="text" name="' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '" value="' . esc_url( get_metadata( $term->taxonomy, $term->term_id, $id, TRUE ) ) . '" />';
		print "\n" . '<p class="description">' . esc_html( "Full url to the author's website.", 'mfields-extensions' ) . '</p>';
		print "\n" . '</td>';
		print "\n" . '</tr>';

		/* WordPress profile url. */
		$id = $term->taxonomy . '_url_wordpress';
		print "\n" . '<tr class="form-field">';
		print "\n" . '<th scope="row" valign="top"><label for="' . esc_attr( $id ) . '">' . esc_html__( 'WordPress Profile', 'mfields-extensions' ) . '</label></th>';
		print "\n" . '<td>';
		print "\n" . '<input type="text" name="' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '" value="' . esc_url( get_metadata( $term->taxonomy, $term->term_id, $id, TRUE ) ) . '" />';
		print "\n" . '<p class="description">' . esc_html( "Full url to the author's profile on wordpress.org.", 'mfields-extensions' ) . '</p>';
		print "\n" . '</td>';
		print "\n" . '</tr>';

		/* Twitter url. */
		$id = $term->taxonomy . '_url_twitter';
		print "\n" . '<tr class="form-field">';
		print "\n" . '<th scope="row" valign="top"><label for="' . esc_attr( $id ) . '">' . esc_html__( 'Twitter Profile', 'mfields-extensions' ) . '</label></th>';
		print "\n" . '<td>';
		print "\n" . '<input type="text" name="' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '" value="' . esc_url( get_metadata( $term->taxonomy, $term->term_id, $id, TRUE ) ) . '" />';
		print "\n" . '<p class="description">' . esc_html( "Full url to the author's profile on twitter.com.", 'mfields-extensions' ) . '</p>';
		print "\n" . '</td>';
		print "\n" . '</tr>';
		
		/* Nonce field. */
		print "\n" . '<input type="hidden" name="' . esc_attr( $term->taxonomy . '_nonce' ) . '" value="' . esc_attr( wp_create_nonce( 'update_' . $term->taxonomy . '_for_' . $term->term_id ) ) . '" />';
	}
	/**
	 * Register taxonomies.
	 *
	 * Register author taxonomy for extensions post_type.
	 *
	 * @return     void
	 * @since      2011-02-20
	 */
	function register() {
		if ( isset( $_REQUEST['action'] ) && 'deactivate' == $_REQUEST['action'] ) {
			return;
		}
		register_taxonomy( 'mfields_extension_author', 'mfields_extension', array(
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
}

class Mfields_Extension_Controller {
	function init() {
		register_activation_hook( __FILE__, array( __class__, 'activate' ) );
		register_deactivation_hook( __FILE__, array( __class__, 'deactivate' ) );
		
		Mfields_Extension::init();
		Mfields_Extension_Author::init();
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
		Mfields_Extension::register();
		Mfields_Extension_Author::register();
		Mfields_Extension_Author::create_meta_table();
		flush_rewrite_rules();
	}
	/**
	 * Deactivation.
	 *
	 * When a user chooses to deactivate extensions, it is
	 * important to remove all custom object rewrites from
	 * the database.
	 *
	 * @return     void
	 * @since      2011-02-20
	 */
	function deactivate() {
		flush_rewrite_rules();
	}
}