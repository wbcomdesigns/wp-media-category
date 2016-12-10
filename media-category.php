<?php 
/*
  Plugin Name: Media Category Option
  Plugin URI: http://www.wbcomdesigns.com/plugins/media-category-option
  Description: Adds taxonomy to categorize media
  Version: 1.0.0
  Author: WBCOM DESIGNS<admin@wbcomdesigns.com>
  Author URI: http://www.wbcomdesigns.com
  License: GPL2
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!class_exists('Mediacat')){
	/**
	 * Class to wrap up all the functions
	 */
	class Mediacat{
		/**
		 * constructor to load all the functionality
		 */
		public function __construct(){
			add_action( 'init', array( $this, 'create_media_taxonomy' ) );
			add_action('wp_ajax_list_terms',array($this,'list_terms'));
			add_action('admin_footer', array( $this,'bulk_change_term_action_media'));
			add_action('admin_notices', array( $this,'bulk_change_term_media_notices'));
			add_action('load-upload.php', array($this, 'bulk_change_term_action'));
			add_shortcode('wbmedia',array($this,'media_category_shortcode'));
		}

		// Function to create taxonomy for media media type
		public function create_media_taxonomy(){
			global $pagenow;
			$labels = array(
				'name'              => __( 'Media Categories', 'taxonomy general name', 'media-category' ),
				'singular_name'     => __( 'Media Category', 'taxonomy singular name', 'media-category' ),
				'search_items'      => __( 'Search Media Categories', 'media-category' ),
				'all_items'         => __( 'All Media Categories', 'media-category' ),
				'change_term_item'         => __( 'Change Media Category Media Categories', 'media-category' ),
				'update_item'       => __( 'Update Media Category', 'media-category' ),
				'add_new_item'      => __( 'Add New Media Category', 'media-category' ),
				'new_item_name'     => __( 'New Media Category Name', 'media-category' ),
				'menu_name'         => __( 'Media Category', 'media-category' ),
			);

			$args = array(
				'hierarchical'      => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'update_count_callback' =>'_update_generic_term_count',
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'media-category' ),
			);
			register_taxonomy( 'media-category', array( 'attachment' ), $args );
			if($pagenow=='upload.php'){
				wp_enqueue_script('media-script',plugin_dir_url(__FILE__).'/assets/js/media-cat.js');
				wp_localize_script( 'media-script', 'ajax_object',array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
				wp_localize_script( 'media-script', 'url',array( 'spinner_url' => plugin_dir_url(__FILE__).'/assets/spinner.gif' ) );
			}
			wp_enqueue_style('media-style',plugin_dir_url(__FILE__).'/assets/css/media-cat.css');

		}

		// Adding Custom Bulk Action in Media Panel		
		public function bulk_change_term_action_media() {
		  	global $pagenow;
		  	if($pagenow == 'upload.php') {
				wp_enqueue_script('change-term',plugin_dir_url(__FILE__).'/assets/js/change-term.js');
		  	}
		}

		//Bulk Change Media Category Media Notices
		public function bulk_change_term_media_notices(){
			global $media_type, $pagenow;
			if($pagenow == 'upload.php' && isset($_REQUEST['change_term']) && (int) $_REQUEST['change_term']) {
				$message = sprintf( _n( 'Attachment change_term.', '%s attachments category changed.', $_REQUEST['change_term'] ), number_format_i18n( $_REQUEST['change_term'] ) );
				echo "<div class=\"updated\"><p>{$message}</p></div>";
			}
		}

		// Perfrom Bulk Change Media Category Action 
		public function bulk_change_term_action() {
			if ( isset( $_REQUEST['detached'] ) ) {
						return;
			}
			$action_request_top = ( isset( $_REQUEST['action'] ) && ! empty( $_REQUEST['action'] ) ) ?
				$_REQUEST['action'] : '';
			$action_request_bottom = ( isset( $_REQUEST['action2'] ) && ! empty( $_REQUEST['action2'] ) ) ?
				$_REQUEST['action2'] : '';
			$action = ( ! empty( $action_request_top ) ) ? $action_request_top : $action_request_bottom;
			$allowed_actions = array( 'change_term' );
			if ( empty( $action ) || ! in_array( $action, $allowed_actions ) ) {
							return;
			}
			check_admin_referer('bulk-media');
			$query_args = array();
			if ( isset( $_REQUEST['post_mime_type'] ) ) {
							$query_args['post_mime_type'] = $_REQUEST['post_mime_type'];
			}
			if ( isset( $_REQUEST['paged'] ) ) {
							$query_args['paged'] = $_REQUEST['paged'];
			}
			switch ( $action ) {
				case 'change_term':
					$media = ( isset( $_REQUEST['media'] ) ) ?
						(array) $_REQUEST['media'] : array();
					if ( ! empty( $media ) ) {
						$post_ids = array_map( 'intval', $_REQUEST['media'] );
						if ( empty( $post_ids ) ) {
													return;
						}
					}
					$change_termed = 0;
					foreach ( $post_ids as $post_id ) {
						if ( ! $this->perform_change_term( $post_id ) ) {
													wp_die( __('Error in changing categories.','media-category') );
						}
						$change_termed++;
					}
					$query_args['change_term'] = $change_termed;
					$query_args['ids']      = join( ',', $post_ids );
					break;
				default:
					return;
					break;
			}
			$sendback = add_query_arg( $query_args, admin_url( 'upload.php' ) );
			wp_redirect( $sendback );
			exit();
		}

		// list terms available in media category 
		public function list_terms(){
			$terms = get_terms( array(
				'taxonomy' => 'media-category',
				'hide_empty' => false,
			) );
			
			echo '<select class="terms_form" name="terms" id="terms_cat">';
			
				foreach ( $terms as $term => $term_obj ) {
					echo "<option value='$term_obj->name'>$term_obj->name</option>\n";
				}
			
			echo'</select>';
			die;
		}
		//bulk action to change term
		public function perform_change_term($post_id) {
			if (isset($_GET['terms'])) {
				$terms = sanitize_text_field($_GET['terms'];
				$taxonomy = 'media-category';
				wp_set_object_terms($post_id, $terms, $taxonomy);
				return true;
			}
		}

		//shortcode to display media categorywise 
		public function media_category_shortcode($atts) {
			if ( ! empty($atts['category'])) {
				$pages = get_posts(array(
				  'post_type' => 'attachment',
				  'numberposts' => -1,
				  'tax_query' => array(
					array(
					  'taxonomy' => 'media-category',
					  'field' => 'name',
					  'terms' => $atts['category'], // Where term_id of Term 1 is "1".
					  'include_children' => true
					)
				  )
				));

				if ( ! empty($pages)):
					echo "<h3>".$atts['category']."</h3>";
					echo '<ul style=list-style:none;>';
					foreach ($pages as $key => $value) {
						echo '<li class=thumb_media><img class=media_img src='.$value->guid.'></li>';
					}
					echo "</ul>";
				else:
					echo "<h3>".$atts['category']."</h3>";
					_e('Sorry no media found in this category.', 'media-category');
				endif;
			}
		}
	}
	new Mediacat();
}
?>
