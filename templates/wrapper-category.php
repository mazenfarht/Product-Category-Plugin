<?php
/**
 * Product category archive wrapper (theme header + [pc_category_page] + theme footer).
 *
 * @package CustomProductCatalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
echo do_shortcode( '[pc_category_page]' ); // phpcs:ignore WordPress.Security.EscapeOutput
get_footer();
