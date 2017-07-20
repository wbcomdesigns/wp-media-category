<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Wp_Media_Category
 * @subpackage Wp_Media_Category/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Media_Category
 * @subpackage Wp_Media_Category/admin
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Wp_Media_Category_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function wpmc_enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Media_Category_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Media_Category_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-media-category-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function wpmc_enqueue_scripts() {
		global $pagenow;
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Media_Category_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Media_Category_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ($pagenow == 'upload.php') {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-media-category-admin.js', array( 'jquery' ), $this->version, false );
			wp_localize_script(
				$this->plugin_name,
				'wpmc_admin_js',
				array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'spinner_url' => includes_url().'/images/spinner.gif',
				)
			);
		}
	}

	/**
	 *
	 */
	public function wpmc_create_media_taxonomy() {
		$labels = array(
			'name'              => __('Media Categories', 'taxonomy general name', 'media-category'),
			'singular_name'     => __('Media Category', 'taxonomy singular name', 'media-category'),
			'search_items'      => __('Search Media Categories', 'media-category'),
			'all_items'         => __('All Media Categories', 'media-category'),
			'change_term_item'         => __('Change Media Category Media Categories', 'media-category'),
			'update_item'       => __('Update Media Category', 'media-category'),
			'add_new_item'      => __('Add New Media Category', 'media-category'),
			'new_item_name'     => __('New Media Category Name', 'media-category'),
			'menu_name'         => __('Media Category', 'media-category'),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'update_count_callback' =>'_update_generic_term_count',
			'query_var'         => true,
			'rewrite'           => array('slug' => 'media-category'),
		);
		register_taxonomy('media-category', array('attachment'), $args);
	}

	/**
	 *
	 */
	public function wpmc_bulk_change_term_media_notices() {
		global $media_type, $pagenow;
		if ($pagenow == 'upload.php' && isset($_REQUEST['change_term']) && (int) $_REQUEST['change_term']) {
			$message = sprintf(_n('Attachment change_term.', '%s attachments category changed.', $_REQUEST['change_term']), number_format_i18n($_REQUEST['change_term']));
			echo "<div class=\"updated\"><p>{$message}</p></div>";
		}
	}

	/**
	 *
	 */
	public function wpmc_list_terms() {
		$terms = get_terms(array(
			'taxonomy' => 'media-category',
			'hide_empty' => false,
		));
		
		echo '<select class="terms_form" name="terms" id="terms_cat">';
		
			foreach ($terms as $term => $term_obj) {
				echo "<option value='$term_obj->name'>$term_obj->name</option>\n";
			}
		
		echo'</select>';
		die;
	}

	/**
	 *
	 */
	public function wpmc_bulk_change_term_action() {
		if (isset($_REQUEST['detached'])) {
					return;
		}
		$action_request_top = (isset($_REQUEST['action']) && ! empty($_REQUEST['action'])) ?
			$_REQUEST['action'] : '';
		$action_request_bottom = (isset($_REQUEST['action2']) && ! empty($_REQUEST['action2'])) ?
			$_REQUEST['action2'] : '';
		$action = ( ! empty($action_request_top)) ? $action_request_top : $action_request_bottom;
		$allowed_actions = array('change_term');
		if (empty($action) || ! in_array($action, $allowed_actions)) {
						return;
		}
		check_admin_referer('bulk-media');
		$query_args = array();
		if (isset($_REQUEST['post_mime_type'])) {
						$query_args['post_mime_type'] = $_REQUEST['post_mime_type'];
		}
		if (isset($_REQUEST['paged'])) {
						$query_args['paged'] = $_REQUEST['paged'];
		}
		switch ($action) {
			case 'change_term':
				$media = (isset($_REQUEST['media'])) ?
					(array) $_REQUEST['media'] : array();
				if ( ! empty($media)) {
					$post_ids = array_map('intval', $_REQUEST['media']);
					if (empty($post_ids)) {
												return;
					} else {
						$change_termed = 0;
				foreach ($post_ids as $post_id) {
					if ($this->wpmc_perform_change_term($post_id) === false) {
												wp_die(__('Error in changing categories.', 'media-category'));
					}
					$change_termed++;
				}
				$query_args['change_term'] = $change_termed;
				$query_args['ids'] = join(',', $post_ids);
					}
				}
				
				break;
			default:
				return;
		}
		$sendback = add_query_arg($query_args, admin_url('upload.php'));
		wp_redirect($sendback);
		exit();
	}

	/**
	 * Bulk action to change term
	 */
	public function wpmc_perform_change_term($post_id) {
		if (isset($_GET['terms'])) {
			$terms = sanitize_text_field($_GET['terms']);
			$taxonomy = 'media-category';
			wp_set_object_terms($post_id, $terms, $taxonomy);
			return true;
		} else {
		return false;
		}
	}

	/**
	 *
	 */
	public function wpmc_media_category_shortcode( $atts ) {
		if ( ! empty($atts['category'])) {
			$pages = get_posts(array(
				'post_type' => 'attachment',
				'numberposts' => -1,
				'tax_query' => array(
				array(
					'taxonomy' => 'media-category',
					'field' => 'name',
					'terms' => $atts['category'],
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
			_e('Sorry no media found in this category.', WPMC_TEXT_DOMAIN);
			endif;
		}
	}
}
