<?php
/*
 * Plugin Name: Variation Swatches for WooCommerce
 * Version: 1.2.6
 * Description: Variation Swatches for WooCommerce
 * Author: Acowebs
 * Author URI: http://acowebs.com
 * Requires at least: 4.9
 * Tested up to: 6.4
 * Text Domain: aco-variation-swatches-for-woocommerce
 * WC requires at least: 4.3
 * WC tested up to: 8.4
 */

define('ACOVSW_TOKEN', 'acovsw');
define('ACOVSW_VERSION', '1.2.6');
define('ACOVSW_FILE', __FILE__);
define('ACOVSW_PLUGIN_NAME', 'Variation Swatches for WooCommerce');
define('ACOVSW_PRODUCTS_TRANSIENT_KEY', 'acovsw_swatches_key');
define('ACOVSW_STORE_URL', 'https://api.acowebs.com');

define('ACOVSW_Wordpress_Version', get_bloginfo('version'));

if ( !function_exists('acovsw_init') ) {

    function acovsw_init()
    {
        $plugin_rel_path = basename(dirname(__FILE__)) . '/languages'; /* Relative to WP_PLUGIN_DIR */
        load_plugin_textdomain('aco-variation-swatches-for-woocommerce', false, $plugin_rel_path);
    }

}

if ( !function_exists('acovsw_autoloader') ) {

    function acovsw_autoloader($class_name)
    {
        if ( 0 === strpos($class_name, 'ACOVSW') ) {
            $classes_dir = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
            $class_file = 'class-' . str_replace('_', '-', strtolower($class_name)) . '.php';
            require_once $classes_dir . $class_file;
        }
    }

}

if ( !function_exists('ACOVSW') ) {

    function ACOVSW()
    {
        $instance = ACOVSW_Backend::instance(__FILE__, ACOVSW_VERSION);
        return $instance;
    }

}
add_action('plugins_loaded', 'acovsw_init');
spl_autoload_register('acovsw_autoloader');
if ( is_admin() ) {
    ACOVSW();
}
new ACOVSW_Api();

$options = new ACOVSW_Options();

new ACOVSW_Front_End($options, __FILE__, ACOVSW_VERSION);

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );