<?php
/*
Plugin Name: Datafiles
Plugin URI:
Description: Allows sites to serve and track changes of root level XML, JSON, and other data files without directly uploading files to the server
Version: 1.0
License: GPL3 or later
*/

/*  Datafiles
 *
 * Allows sites to serve and track changes of root level XML, JSON,
 * and other data files without directly uploading files to the server
 *
 * The code contained within this plugin consitites a work of the 
 * United States Government and is not subject to domestic copyright 
 * protection under 17 USC ¤ 105. The plugin, as a derivative work of 
 * a project distribued under the terms of the GNU General Public License 
 * is itself licensed under GPL v3 or later.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright 2012
 * @license GPL v3 or later
 * @version 1.0
 * @package Datafiles
 */

class WP_Datafiles {

	public $post_type = 'datafile';
	public $taxonomy = 'extension';
	public $initial_terms = array( 'json', 'xml' );
	public $initial_posts = array( 'digital-strategy' );
	public $version = '1.0';

	/**
	 * Hook into WordPress Plugin API
	 */
	function __construct() {

		add_action( 'template_redirect', array( &$this, 'template_filter' ) );
		add_action( 'add_meta_boxes', array( &$this, 'register_metabox' ) );
		add_action( 'save_post', array( &$this, 'metabox_save' ), 25 );
		add_action( 'init', array( &$this, 'register_cpt' ) );
		add_action( 'init', array( &$this, 'register_ct' ) );
		add_action( 'wp_ajax_add_datafile_extension', array( &$this, 'ajax_add') );
		add_action( 'post_type_link', array(&$this, 'permalink'), 10, 4 );
		add_action( 'post_link', array(&$this, 'permalink'), 10, 4 );
		add_filter( 'rewrite_rules_array' , array( &$this, 'rewrite' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'disable_rich_editor' ) );
		add_action( 'admin_init', array( &$this, 'propegate' ) );
		add_filter( 'redirect_canonical', array( &$this, 'redirect_canonical_filter' ), 10, 2 );
		add_filter( 'get_sample_permalink_html', array(&$this, 'sample_permalink_html_filter'), 10, 4);
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue' ) );
		add_filter( 'the_content', array( &$this, 'jsonp_callback_filter' ) );
		add_filter( 'query_vars', array(&$this, 'add_query_var') );
		register_activation_hook( __FILE__, 'flush_rewrite_rules' );

	}


	/**
	 * Register Custom Post Type
	 */
	function register_cpt() {

		$labels = array(
			'name' => _x( 'Datafiles', 'datafile' ),
			'singular_name' => _x( 'Datafile', 'datafile' ),
			'add_new' => _x( 'Add New', 'datafile' ),
			'add_new_item' => _x( 'Add New Datafile', 'datafile' ),
			'edit_item' => _x( 'Edit Datafile', 'datafile' ),
			'new_item' => _x( 'New Datafile', 'datafile' ),
			'view_item' => _x( 'View Datafile', 'datafile' ),
			'search_items' => _x( 'Search Datafiles', 'datafile' ),
			'not_found' => _x( 'No datafiles found', 'datafile' ),
			'not_found_in_trash' => _x( 'No datafiles found in Trash', 'datafile' ),
			'parent_item_colon' => _x( 'Parent Datafile:', 'datafile' ),
			'menu_name' => _x( 'Datafiles', 'datafile' ),
		);

		$args = array(
			'labels' => $labels,
			'hierarchical' => false,
			'supports' => array( 'title', 'editor', 'revisions' ),
			'taxonomies' => array( 'extension' ),
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'has_archive' => false,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => true,
			'capability_type' => 'post'
		);

		register_post_type( $this->post_type, $args );

	}


	/**
	 * Register Custom Taxonomy
	 */
	function register_ct() {

		$labels = array(
			'name' => _x( 'Extensions', 'extension' ),
			'singular_name' => _x( 'Extension', 'extension' ),
			'search_items' => _x( 'Search Extensions', 'extension' ),
			'popular_items' => _x( 'Popular Extensions', 'extension' ),
			'all_items' => _x( 'All Extensions', 'extension' ),
			'parent_item' => _x( 'Parent Extension', 'extension' ),
			'parent_item_colon' => _x( 'Parent Extension:', 'extension' ),
			'edit_item' => _x( 'Edit Extension', 'extension' ),
			'update_item' => _x( 'Update Extension', 'extension' ),
			'add_new_item' => _x( 'Add New Extension', 'extension' ),
			'new_item_name' => _x( 'New Extension', 'extension' ),
			'separate_items_with_commas' => _x( 'Separate extensions with commas', 'extension' ),
			'add_or_remove_items' => _x( 'Add or remove Extensions', 'extension' ),
			'choose_from_most_used' => _x( 'Choose from most used Extensions', 'extension' ),
			'menu_name' => _x( 'Extensions', 'extension' ),
		);

		$args = array(
			'labels' => $labels,
			'public' => false,
			'show_in_nav_menus' => true,
			'show_ui' => true,
			'show_tagcloud' => false,
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => true
		);

		register_taxonomy( $this->taxonomy, array( $this->post_type ), $args );

	}


	/**
	 * Hook to check for post_type call template filter
	 */
	function template_filter() {

		//get current post ID
		global $wp_query;

		if ( !isset( $wp_query->post ) )
			return;

		$post_id = $wp_query->post->ID;

		//if not a page or single post, kick
		if ( get_post_type( $post_id ) != $this->post_type )
			return;

		remove_filter( 'the_content', 'wptexturize'        );
		remove_filter( 'the_content', 'convert_smilies'    );
		remove_filter( 'the_content', 'convert_chars'      );
		remove_filter( 'the_content', 'wpautop'            );
		remove_filter( 'the_content', 'shortcode_unautop'  );
		remove_filter( 'the_content', 'prepend_attachment' );
		remove_filter( 'the_content', 'wpautop' );
		add_filter('template_include', array( &$this, 'template_callback' ), 100);

	}


	/**
	 * Callback to replace the current template with our blank template
	 * @return string the path to the plugin's template.php
	 */
	function template_callback( $template ) {

		$this->content_type_header();
		return dirname(__FILE__) . '/templates/template.php';

	}


	/**
	 * Registers plugin's toggle metabox with metabox API
	 */
	function register_metabox() {

		//pull out the standard post meta box , we don't need it
		remove_meta_box( 'tagsdiv-extension', 'datafile', 'side' );

		add_meta_box( 'datafile_extension', 'Extension', array( &$this, 'metabox_callback' ), 'datafile', 'side', 'low' );

	}


	/**
	 * Callback to display toggle metabox
	 */
	function metabox_callback( $post ) {

		wp_nonce_field( 'datafiles', '_datafile_nonce' );

		//get the taxonomies details
		$taxonomy = get_taxonomy( $this->taxonomy );

		//grab an array of all terms within our custom taxonomy, including empty terms
		$terms = get_terms(  $this->taxonomy, array( 'hide_empty' => false ) );

		//garb the current selected term where applicable so we can select it
		$current = wp_get_object_terms( $post->ID, $this->taxonomy );

		//grab the template
		include dirname( __FILE__ ) . '/templates/taxonomy-box.php';

	}


	/**
	 * Saves metabox toggle when page is saved
	 */
	function metabox_save( $post_id ) {

		global $wpdb;

		if ( !$_POST )
			return;

		if ( $_POST['post_type'] != $this->post_type )
			return;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( wp_is_post_revision( $post_id ) )
			return;

		if ( !wp_verify_nonce( $_POST['_datafile_nonce'], 'datafiles' ) )
			return;

		if ( !current_user_can( 'edit_page', $post_id ) )
			return;

		$slug = $this->get_public_slug( $post_id );

		wp_set_object_terms( $post_id, (int) $_POST['datafile_extension'], $this->taxonomy );

		$name = "$slug-" . $this->get_extension( $post_id );
		$wpdb->update( $wpdb->posts, array( 'post_name' => $name ), array( 'ID' => $post_id ) );

	}


	/**
	 * Processes AJAX request to add new terms
	 */
	function ajax_add() {

		//check the nonce
		check_ajax_referer( 'add_datafile_extension' , '_datafile_extension_nonce' );

		//pull up the taxonomy details
		$taxonomy = get_taxonomy( $this->taxonomy );

		//check user capabilities
		if ( !current_user_can( $taxonomy->cap->edit_terms ) )
			die('-1');

		//insert term
		$term = wp_insert_term( $_POST[ 'new_datafile_extension' ], $this->taxonomy );

		//catch errors
		if ( is_wp_error( $term ) ) {
			$this->metabox_callback( $post );
			exit();
		}

		//associate position with new term
		wp_set_object_terms( $_POST['post_ID'], $term['term_id'], $this->taxonomy );

		//get updated post to send to taxonomy box
		$post = get_post( $_POST['post_ID'] );

		//return the HTML of the updated metabox back to the user so they can use the new term
		$this->metabox_callback( $post );

		exit();

	}


	/**
	 * Builds post type permalink
	 * @param string $link original permalink
	 * @param object $post post object
	 * @param unknown $leavename
	 * @param unknown $sample (optional)
	 * @return string the real permalink
	 */
	function permalink( $link, $post, $leavename, $sample = '' ) {

		global $wp_rewrite;

		//if this isn't our post type, kick
		if ( get_post_type( $post ) != $this->post_type )
			return $link;

		$extension_terms = wp_get_object_terms( $post->ID, $this->taxonomy );

		//if no permastruct
		if ( $wp_rewrite->permalink_structure == '' || in_array( $post->post_status, array( 'pending', 'draft' ) ) || empty( $extension_terms ) ) {
			$link = site_url( '?post_type=datafile&p=' . $post->ID );
			return apply_filters( 'datafile_permalink', $link, $post );
		}

		$extension = reset( $extension_terms )->name;

		$slug = $this->get_public_slug( $post );

		$link = home_url() .'/';
		$link .= ( $leavename ) ? '%datafile%' : $slug;
		$link .= '.' . $extension;

		$link = apply_filters( 'datafile_permalink', $link, $post );

		return $link;

	}


	/**
	 * Return a file's extension
	 */
	function get_extension( $post ) {

		$post = get_post( $post );
		$extension_terms = wp_get_object_terms( $post->ID, $this->taxonomy );

		if ( empty( $extension_terms ) )
			return $post->post_name;

		$extension = reset( $extension_terms )->name;

		return $extension;

	}


	/**
	 * Return the public slug without the extension appended
	 */
	function get_public_slug( $post ) {

		$post = get_post( $post );
		$extension = $this->get_extension( $post );

		return preg_replace( "#([a-z0-9-]+)-$extension$#", "$1", $post->post_name );

	}


	/**
	 * Adds document rewrite rules to the rewrite array
	 * @param $rules array rewrite rules
	 * @return array rewrite rules
	 */
	function rewrite( $rules ) {

		$datafile_rule = array(
			'([^./]+)\.([A-Za-z0-9]{3,4})' => 'index.php?datafile=$matches[1]-$matches[2]&post_type=datafile',
		);

		$datafile_rule = apply_filters( 'datafile_rewrite_rule', $datafile_rule, $rules );

		return $datafile_rule + $rules;

	}


	/**
	 * Propegate initial posts and taxonomy terms
	 */
	function propegate() {

		$terms = get_terms( $this->taxonomy, array( 'hide_empty' => false ) );

		if ( empty( $terms ) )
			foreach ( $this->initial_terms as $term )
				wp_insert_term( $term, $this->taxonomy );

			$posts = get_posts( array( 'post_type' => $this->post_type, 'post_status' => array( 'draft', 'publish' ) ) );
		
		//not a .gov, don't load posts
		if ( stripos( get_bloginfo( 'home' ), '.gov' ) === false ) 
			return;
			
		if ( empty( $posts ) ) {
			foreach ( $this->initial_terms as $term ) {
				foreach ( $this->initial_posts as $post ) {
					$data = array(
						'post_type' => $this->post_type,
						'post_status' => 'draft',
						'post_title' => $post . " ($term)",
						'post_name' => $post,
					);
					$ID = wp_insert_post( $data );
					wp_set_post_terms( $ID, array( $term ), $this->taxonomy );
				}
			}
		}

	}


	/**
	 * Default to HTML editor on file edit screens
	 */
	function disable_rich_editor( ) {
		global $post_type;

		if ( $post_type != $this->post_type )
			return;

		add_filter( 'wp_default_editor', array( &$this, 'return_html' ) );

	}


	function return_html() {

		return 'html';

	}


	/**
	 * Removes auto-appended trailing slash from datafile requests prior to serving
	 * WordPress SEO rules properly dictate that all post requests should be 301 redirected with a trailing slash
	 * Because documents end with a phaux file extension, we don't want that
	 * Removes trailing slash from documents, while allowing all other SEO goodies to continue working
	 * @param unknown $redirect
	 * @param unknown $request
	 * @return unknown
	 */
	function redirect_canonical_filter( $redirect, $request ) {
		global $post;

		if ( !$post || get_post_type( $post ) != $this->post_type )
			return $redirect;

		return untrailingslashit( $redirect );

	}


	/**
	 * Filters permalink displayed on edit screen
	 * @rerurns string modified HTML
	 * @param string $html original HTML
	 * @param int $id Post ID
	 * @return unknown
	 */
	function sample_permalink_html_filter( $html, $id ) {

		$post = get_post( $id );

		//verify post type
		if ( $post->post_type != $this->post_type )
			return $html;

		$html = preg_replace( "#{$post->post_name}#", $this->get_public_slug( $post ), $html );
		$html = preg_replace( "#\.({$this->get_extension()})</span>#", ".<span class=\"extension\">$1</span></span>", $html );

		//otherwise return html unfiltered
		return $html;
	}


	/**
	 * Return mime types filtered
	 * This way we do not allow additional mimetypes elsewhere
	 */
	function get_mimes() {

		add_filter( 'upload_mimes', array( &$this, 'mime_filter' ) );
		$mimes = get_allowed_mime_types();
		remove_filter( 'upload_mimes', array( &$this, 'mime_filter' ) );
		return $mimes;

	}


	/**
	 * Add our mimetypes
	 */
	function mime_filter( $mimes ) {
		return $mimes + array(
			'json' => 'application/json',
			'xml' => 'text/xml',
		);

	}


	/**
	 * Send proper mimetype header on download
	 */
	function content_type_header( ) {
		global $post;
		$extension = $this->get_extension( $post );
		$mimes = $this->get_mimes();

		if ( !isset( $mimes[ $extension ] ) || headers_sent() )
			return;

		header( 'Content-Type: ' . $mimes[ $extension ] );

	}


	/**
	 * Enqueue backend javscript
	 */
	function enqueue() {

		if ( get_current_screen()->post_type != $this->post_type )
			return;

		$suffix = ( WP_DEBUG ) ? '.dev' : '';
		wp_enqueue_script( 'datafiles', plugins_url( "/js/datafiles{$suffix}.js", __FILE__ ), array( 'jquery' ), $this->version, true );

		$l10n = array( 'missingExtensionMsg' => __( 'Please select an extension', 'datafiles' ) );
		wp_localize_script( 'datafiles', 'datafiles', $l10n );

	}
	
	function jsonp_callback_filter( $content ) {
		global $post;
		
		if ( get_post_type( $post ) != $this->post_type )
			return $content; 
	
		//check for callback and sanitize
		if ( !$callback = get_query_var( 'callback' ) )
			return $content;

		//http://stackoverflow.com/a/10900911/1082542	
		if ( preg_match( '/[^0-9a-zA-Z\$_]|^(abstract|boolean|break|byte|case|catch|char|class|const|continue|debugger|default|delete|do|double|else|enum|export|extends|false|final|finally|float|for|function|goto|if|implements|import|in|instanceof|int|interface|long|native|new|null|package|private|protected|public|return|short|static|super|switch|synchronized|this|throw|throws|transient|true|try|typeof|var|volatile|void|while|with|NaN|Infinity|undefined)$/', $callback) )
			return $content;
		
		return "{$callback}($content);";
		
	
	}

	/**
	 * Tell's WP to recognize the jsonp callback query var
	 * @param array $vars the query vars
	 * @return array the modified query vars
	 */
	function add_query_var( $vars ) {
		$vars[] = "callback";
		return $vars;
	}

}


$datafiles = new WP_Datafiles;