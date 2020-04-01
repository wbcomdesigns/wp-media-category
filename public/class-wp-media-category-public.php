<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Wp_Media_Category
 * @subpackage Wp_Media_Category/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Media_Category
 * @subpackage Wp_Media_Category/public
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Wp_Media_Category_Public {

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
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function wpmc_enqueue_styles() {
		global $post;
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-media-category-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'wpmc-lightbox-css', plugin_dir_url( __FILE__ ) . 'css/jquery.littlelightbox.css' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function wpmc_enqueue_scripts() {

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-media-category-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'wpmc-lightbox-js', plugin_dir_url( __FILE__ ) . 'js/jquery.littlelightbox.js', array( 'jquery' ) );
	}

	/**
	 * Shortcode for media category.
	 */
	public function wpmc_media_category_shortcode( $attrs = array(), $content = null, $tag = '' ) {

		$atts = array_change_key_case( (array) $attrs, CASE_LOWER );
		$atts = shortcode_atts(
			array(
				'category' => '',
			),
			$attrs,
			$tag
		);
		ob_start();
		echo '<h3>' . esc_html__( ucfirst( $atts['category'] ), 'media-category' ) . '</h3>';
		if ( ! term_exists( $atts['category'], 'media-category' ) ) {
			echo '<p class="wpmc-media-error">' . __( 'No such category exists!', 'media-category' ) . '</p>';
		} else {
			if ( ! empty( $atts['category'] ) ) {
				$wp_media = get_posts(
					array(
						'post_type'   => 'attachment',
						'numberposts' => -1,
						'tax_query'   => array(
							array(
								'taxonomy'         => 'media_category',
								'field'            => 'name',
								'terms'            => $atts['category'],
								'include_children' => true,
							),
						),
					)
				);

				if ( ! empty( $wp_media ) ) {
					echo '<div class="wpmc-media-display">';
					foreach ( $wp_media as $key => $media ) {
						$media_url = wp_get_attachment_url( $media->ID );
						if ( strpos( $media->post_mime_type, 'image' ) !== false ) {
							echo '<div class="wpmc-single-media">';
							echo '<a title="' . $media->post_title . '" href="' . $media->guid . '" class="wpmc-media-lightbox" data-littlelightbox-group="gallery">';
							echo '<img src=' . $media->guid . ' alt="' . $media->post_title . '" />';
							echo '</a>';
							echo '</div>';
						} elseif ( strpos( $media->post_mime_type, 'video' ) !== false ) {
							echo '<div class="wpmc-single-media-video">';
							echo '<video controls="controls">';
							echo '<source src="' . $media_url . '">';
							echo '</video>';
							echo '</div>';
						} elseif ( strpos( $media->post_mime_type, 'audio' ) !== false ) {
							echo '<div class="wpmc-single-media-audio">';
							echo '<audio controls="controls">';
							echo '<source src="' . $media_url . '">';
							echo '</audio>';
							echo '</div>';
						}
					} //end loop for printing media
					echo '</div>';
				} else {
					echo '<p class="wpmc-media-error">' . __( 'Sorry no media found in this category.', 'media-category' ) . '</p>';
				}
			}
		}
		return ob_get_clean();
	}
}
