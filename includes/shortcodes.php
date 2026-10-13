<?php
/**
 * Shortcodes: [pc_category_page] and [pc_single_product].
 *
 * @package CustomProductCatalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'pc_category_page', 'cpc_shortcode_category_page' );
add_shortcode( 'pc_single_product', 'cpc_shortcode_single_product' );

/**
 * Load the front-end stylesheet.
 */
function cpc_enqueue_front() {
	wp_enqueue_style( 'cpc-catalog', CPC_URL . 'assets/css/catalog.css', array(), CPC_VERSION );
	wp_enqueue_script( 'cpc-catalog', CPC_URL . 'assets/js/catalog.js', array(), CPC_VERSION, true );
}

/**
 * Product categories for the icon strip: the top-level categories in WooCommerce order.
 *
 * @return WP_Term[]
 */
function cpc_get_strip_terms() {
	$args = array(
		'taxonomy'   => 'product_cat',
		'parent'     => 0,
		'hide_empty' => false,
		'orderby'    => 'menu_order',
		'order'      => 'ASC',
	);
	$default = absint( get_option( 'default_product_cat' ) );
	if ( $default ) {
		$args['exclude'] = array( $default );
	}
	$terms = get_terms( $args );
	return ( is_wp_error( $terms ) || ! is_array( $terms ) ) ? array() : $terms;
}

/**
 * Subcategories of a category, in WooCommerce order.
 *
 * @param int $parent_id Parent term ID.
 * @return WP_Term[]
 */
function cpc_get_subcategories( $parent_id ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => absint( $parent_id ),
			'hide_empty' => false,
			'orderby'    => 'menu_order',
			'order'      => 'ASC',
		)
	);
	return ( is_wp_error( $terms ) || ! is_array( $terms ) ) ? array() : $terms;
}

/**
 * Find the product category a [pc_category_page] shortcode refers to.
 *
 * The category attribute accepts the category slug (recommended), the category ID or the category name.
 * Without the attribute the current product category archive is used.
 *
 * @param string $value Cleaned category attribute.
 * @return WP_Term|false
 */
function cpc_resolve_category( $value ) {
	if ( '' === $value ) {
		if ( is_tax( 'product_cat' ) ) {
			$term = get_queried_object();
			return ( $term instanceof WP_Term ) ? $term : false;
		}
		return false;
	}
	$term = get_term_by( 'slug', sanitize_title( $value ), 'product_cat' );
	if ( ! $term && ctype_digit( $value ) ) {
		$term = get_term( absint( $value ), 'product_cat' );
	}
	if ( ! $term ) {
		$term = get_term_by( 'name', $value, 'product_cat' );
	}
	return ( $term instanceof WP_Term ) ? $term : false;
}

/**
 * [pc_category_page category="slug" per_page="12"]
 *
 * Uses the current product category archive when no category attribute is given.
 * per_page is the number of products on each page of the listing (default 12).
 * container="auto|yes|no" controls the centered Nicepage-width container (auto = on plugin-rendered pages only).
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function cpc_shortcode_category_page( $atts ) {
	$atts = shortcode_atts(
		array(
			'category'  => '',
			'per_page'  => 12,
			'container' => 'auto',
		),
		$atts,
		'pc_category_page'
	);

	$requested = cpc_clean_attr( $atts['category'] );
	$term      = cpc_resolve_category( $requested );
	if ( ! $term || empty( $term->term_id ) ) {
		if ( '' === $requested ) {
			return cpc_editor_notice( __( 'Custom Product Catalog: add the category slug, for example [pc_category_page category="your-category-slug"].', 'custom-product-catalog' ) );
		}
		/* translators: %s: category value typed in the shortcode */
		return cpc_editor_notice( sprintf( __( 'Custom Product Catalog: product category "%s" was not found. Use the Slug from Products > Categories.', 'custom-product-catalog' ), $requested ) );
	}

	cpc_enqueue_front();

	$per_page = absint( cpc_clean_attr( $atts['per_page'] ) );
	$per_page = $per_page ? $per_page : 12;
	/**
	 * Filter the number of products per page on the category listing.
	 *
	 * @param int $per_page Products per page.
	 */
	$per_page = max( 1, min( 100, (int) apply_filters( 'cpc_products_per_page', $per_page ) ) );

	$chain  = cpc_term_chain( $term );
	$top    = $chain[0];
	$crumbs = array(
		array(
			'label' => __( 'Home', 'custom-product-catalog' ),
			'url'   => home_url( '/' ),
		),
	);
	foreach ( $chain as $t ) {
		$crumbs[] = array(
			'label' => $t->name,
			'url'   => cpc_term_url( $t ),
		);
	}

	$tax_query = array(
		array(
			'taxonomy'         => 'product_cat',
			'field'            => 'term_id',
			'terms'            => $term->term_id,
			'include_children' => true,
		),
	);
	if ( taxonomy_exists( 'product_visibility' ) ) {
		$tax_query[] = array(
			'taxonomy' => 'product_visibility',
			'field'    => 'name',
			'terms'    => 'exclude-from-catalog',
			'operator' => 'NOT IN',
		);
	}

	$paged = isset( $_GET['cpc_page'] ) ? absint( wp_unslash( $_GET['cpc_page'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$paged = max( 1, $paged );

	$query_args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'paged'          => $paged,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'tax_query'      => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery
	);
	$query = new WP_Query( $query_args );

	// A page number past the end (an old link) shows the last page instead of an empty list.
	// WP_Query reports 0 pages when the requested page is empty, so ask for page 1 to learn the real total.
	if ( $paged > 1 && ! $query->have_posts() ) {
		$query_args['paged'] = 1;
		$first               = new WP_Query( $query_args );
		$last                = (int) $first->max_num_pages;
		if ( $last > 1 ) {
			$paged               = $last;
			$query_args['paged'] = $last;
			$query               = new WP_Query( $query_args );
		} else {
			$paged = 1;
			$query = $first;
		}
	}

	return cpc_render(
		'category-page',
		array(
			'container'   => cpc_clean_attr( $atts['container'] ),
			'term'        => $term,
			'top_id'      => (int) $top->term_id,
			'crumbs'      => $crumbs,
			'strip_terms' => cpc_get_strip_terms(),
			'children'    => cpc_get_subcategories( $top->term_id ),
			'query'       => $query,
			'pagination'  => cpc_pagination_html( $paged, (int) $query->max_num_pages, cpc_current_base_url() ),
		)
	);
}

/**
 * Work out which product the single product shortcode should show.
 *
 * @param int $id Optional explicit product ID.
 * @return int
 */
function cpc_resolve_product_id( $id = 0 ) {
	$id = absint( $id );
	if ( $id ) {
		return $id;
	}
	if ( is_singular( 'product' ) ) {
		return (int) get_queried_object_id();
	}
	if ( 'product' === get_post_type() ) {
		return (int) get_the_ID();
	}
	global $product;
	if ( is_object( $product ) && method_exists( $product, 'get_id' ) ) {
		return (int) $product->get_id();
	}
	return 0;
}

/**
 * [pc_single_product id="123"]
 *
 * Shows the current WooCommerce product (optional id attribute for a specific product).
 * container="auto|yes|no" controls the centered Nicepage-width container.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function cpc_shortcode_single_product( $atts ) {
	$atts = shortcode_atts(
		array(
			'id'        => 0,
			'container' => 'auto',
		),
		$atts,
		'pc_single_product'
	);
	$pid  = cpc_resolve_product_id( cpc_clean_attr( $atts['id'] ) );
	if ( ! $pid || 'product' !== get_post_type( $pid ) ) {
		return cpc_editor_notice( __( 'Custom Product Catalog: product not found. Use the product ID, for example [pc_single_product id="123"].', 'custom-product-catalog' ) );
	}
	if ( 'publish' !== get_post_status( $pid ) && ! current_user_can( 'edit_post', $pid ) ) {
		return '';
	}

	cpc_enqueue_front();

	$d      = cpc_get_product_data( $pid );
	$title  = cpc_product_title( $pid );
	$crumbs = array(
		array(
			'label' => __( 'Home', 'custom-product-catalog' ),
			'url'   => home_url( '/' ),
		),
	);
	$term   = cpc_product_primary_term( $pid );
	if ( $term ) {
		foreach ( cpc_term_chain( $term ) as $t ) {
			$crumbs[] = array(
				'label' => $t->name,
				'url'   => cpc_term_url( $t ),
			);
		}
	}
	$crumbs[] = array(
		'label' => '' !== $title ? $title : $d['code'],
		'url'   => '',
	);

	return cpc_render(
		'single-product',
		array(
			'container' => cpc_clean_attr( $atts['container'] ),
			'pid'       => $pid,
			'd'         => $d,
			'title'     => $title,
			'crumbs'    => $crumbs,
		)
	);
}
