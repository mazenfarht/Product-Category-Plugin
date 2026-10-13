<?php
/**
 * WooCommerce integration: dependency notice, HPOS declaration, template routing, asset loading.
 *
 * @package CustomProductCatalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_notices', 'cpc_woocommerce_missing_notice' );
add_action( 'before_woocommerce_init', 'cpc_declare_wc_compatibility' );
add_filter( 'template_include', 'cpc_template_include', 99 );
add_action( 'wp_enqueue_scripts', 'cpc_maybe_enqueue_front' );

/**
 * Warn when WooCommerce is not active.
 */
function cpc_woocommerce_missing_notice() {
	if ( class_exists( 'WooCommerce' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	echo '<div class="notice notice-warning"><p>' . esc_html__( 'Custom Product Catalog requires WooCommerce to be installed and active.', 'custom-product-catalog' ) . '</p></div>';
}

/**
 * The plugin stores nothing in orders; declare HPOS compatibility.
 */
function cpc_declare_wc_compatibility() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', CPC_DIR . 'custom-product-catalog.php', true );
	}
}

/**
 * Render WooCommerce product category archives and single product pages with the plugin
 * shortcodes, inside the active theme's header and footer.
 *
 * Return false from the `cpc_override_woocommerce_templates` filter to turn this off and
 * place the shortcodes manually instead.
 *
 * @param string $template Template chosen by WordPress.
 * @return string
 */
function cpc_template_include( $template ) {
	if ( ! class_exists( 'WooCommerce' ) || ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) ) {
		return $template;
	}
	if ( ! apply_filters( 'cpc_override_woocommerce_templates', true ) ) {
		return $template;
	}
	if ( is_tax( 'product_cat' ) ) {
		return CPC_DIR . 'templates/wrapper-category.php';
	}
	if ( is_singular( 'product' ) ) {
		return CPC_DIR . 'templates/wrapper-product.php';
	}
	return $template;
}

/**
 * Load the stylesheet on product pages, category archives, and singular content that uses a shortcode.
 */
function cpc_maybe_enqueue_front() {
	$load = is_tax( 'product_cat' ) || is_singular( 'product' );
	if ( ! $load && is_singular() ) {
		$post = get_post();
		if ( $post && ( has_shortcode( $post->post_content, 'pc_category_page' ) || has_shortcode( $post->post_content, 'pc_single_product' ) ) ) {
			$load = true;
		}
	}
	if ( $load ) {
		cpc_enqueue_front();
	}
}
