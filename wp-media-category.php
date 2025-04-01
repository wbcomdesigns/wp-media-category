<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wbcomdesigns.com
 * @since             1.0.0
 * @package           Wp_Media_Category
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Media Category
 * Plugin URI:        https://wbcomdesigns.com/downloads/wordpress-media-category/
 * Description:       Organize your media files with categories for better management and display.
 * Version:           1.5.0
 * Author:            Wbcom Designs
 * Author URI:        https://wbcomdesigns.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       media-category
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( ! defined( 'WPMC_VERSION' ) ) {
    define( 'WPMC_VERSION', '1.5.0' );
}

if ( ! defined( 'WPMC_TEXT_DOMAIN' ) ) {
    define( 'WPMC_TEXT_DOMAIN', 'media-category' );
}

/**
 * The code that runs during plugin activation.
 */
function activate_wp_media_category() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-media-category-activator.php';
    Wp_Media_Category_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wp_media_category() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-media-category-deactivator.php';
    Wp_Media_Category_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_media_category' );
register_deactivation_hook( __FILE__, 'deactivate_wp_media_category' );

/**
 * Define plugin constants
 */
// Plugin path
if ( ! defined( 'WPMC_PLUGIN_PATH' ) ) {
    define( 'WPMC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

// Plugin URL
if ( ! defined( 'WPMC_PLUGIN_URL' ) ) {
    define( 'WPMC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Plugin basename
if ( ! defined( 'WPMC_BASENAME' ) ) {
    define( 'WPMC_BASENAME', plugin_basename( __FILE__ ) );
}

/**
 * The core plugin class
 */
require WPMC_PLUGIN_PATH . 'includes/class-wp-media-category.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_wp_media_category() {
    $plugin = new Wp_Media_Category();
    $plugin->run();
}

run_wp_media_category();