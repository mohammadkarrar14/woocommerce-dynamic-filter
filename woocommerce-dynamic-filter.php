<?php
/**
 *
 * @link              https://muhammadkarrar.com
 * @since             1.0.0
 * @package           Woocommerce_Dynamic_Filter
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Dynamic Filter
 * Plugin URI:        https://muhammadkarrar.com/
 * Description:       The "WooCommerce Dynamic Filters" plugin enhances product filtering capabilities in WooCommerce, allowing users to refine their search by selecting main categories and subcategories.
 * Version:           1.0.0
 * Author:            Imagen Web Pro
 * Author URI:        https://muhammadkarrar.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-dynamic-filter
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOOCOMMERCE_DYNAMIC_FILTER_VERSION', '1.0.0' );
define( 'WOOCOMMERCE_DYNAMIC_FILTER_URL', plugin_dir_url( __FILE__ )) ;
define( 'WOOCOMMERCE_DYNAMIC_FILTER_PLUGIN_NAME', 'WooCommerce Dynamic Filter' );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocommerce-dynamic-filter-activator.php
 */
function activate_woocommerce_dynamic_filter() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-dynamic-filter-activator.php';
	Woocommerce_Dynamic_Filter_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocommerce-dynamic-filter-deactivator.php
 */
function deactivate_woocommerce_dynamic_filter() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-dynamic-filter-deactivator.php';
	Woocommerce_Dynamic_Filter_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woocommerce_dynamic_filter' );
register_deactivation_hook( __FILE__, 'deactivate_woocommerce_dynamic_filter' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-dynamic-filter.php';

function woocommerce_dynamic_filter_require_dependency() {
    
    // Check if WooCommerce is enabled
    if ( file_exists( ABSPATH . 'wp-admin/includes/plugin.php' ) ){
        include_once ( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    if ( ! class_exists( 'WooCommerce' ) ) {
        unset($_GET['activate']);  //unset this to hide default Plugin activated. notice
        deactivate_plugins( plugin_basename( __FILE__ ) , true );
        $class = 'notice is-dismissible error notice-rating';
        $message = __( 'WooCommerce Dynamic Filter Addon requires <a href="https://www.woocommerce.com">WooCommerce</a> plugin to be activated.', 'woocommerce-dynamic-filter' );
        printf( "<div id='message' class='%s'> <p>%s</p></div>", $class, $message );
    }
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woocommerce_dynamic_filter_addon() {

	$plugin = new Woocommerce_dynamic_filter();
	$plugin->run();
}

function woocommerce_dynamic_filter_load(){

    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'woocommerce_dynamic_filter_require_dependency' );
        return false;
    } else {
        run_woocommerce_dynamic_filter_addon();
    }
}

add_action( 'plugins_loaded', 'woocommerce_dynamic_filter_load' );

