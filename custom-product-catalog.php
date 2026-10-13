<?php
/**
 * Plugin Name:       Custom Product Catalog
 * Description:       Category page and single product page display for WooCommerce products. Shortcodes: [pc_category_page category="slug"] and [pc_single_product id="123"].
 * Version:           1.1.1
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * Text Domain:       custom-product-catalog
 *
 * @package CustomProductCatalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CPC_VERSION', '1.1.1' );
define( 'CPC_DIR', plugin_dir_path( __FILE__ ) );
define( 'CPC_URL', plugin_dir_url( __FILE__ ) );

require_once CPC_DIR . 'includes/helpers.php';
require_once CPC_DIR . 'includes/product-fields.php';
require_once CPC_DIR . 'includes/category-fields.php';
require_once CPC_DIR . 'includes/shortcodes.php';
require_once CPC_DIR . 'includes/woocommerce-hooks.php';
