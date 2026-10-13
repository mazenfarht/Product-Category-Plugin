<?php
/**
 * Single product wrapper (theme header + [pc_single_product] + theme footer).
 *
 * @package CustomProductCatalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
while ( have_posts() ) {
	the_post();
	echo do_shortcode( '[pc_single_product]' ); // phpcs:ignore WordPress.Security.EscapeOutput
}
get_footer();
