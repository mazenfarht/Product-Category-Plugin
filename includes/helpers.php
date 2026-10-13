<?php
/**
 * Shared helpers: template loader, data getters, breadcrumb, inline SVG icons.
 *
 * @package CustomProductCatalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a plugin template and return the HTML.
 *
 * @param string $template Template file name without extension.
 * @param array  $vars     Variables made available to the template.
 * @return string
 */
function cpc_render( $template, array $vars = array() ) {
	$file = CPC_DIR . 'templates/' . $template . '.php';
	if ( ! file_exists( $file ) ) {
		return '';
	}
	ob_start();
	extract( $vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
	include $file;
	$html = ob_get_clean();

	// Remove line breaks and indentation between tags. Page builders such as Nicepage (and some
	// themes) can run wpautop() over shortcode output, which would otherwise inject <br> and <p>.
	return preg_replace( '/\s*\n\s*/', '', $html );
}

/**
 * All custom data stored for one product.
 *
 * @param int $product_id Product ID.
 * @return array
 */
function cpc_get_product_data( $product_id ) {
	$product_id = absint( $product_id );

	$specs = get_post_meta( $product_id, '_cpc_specs', true );
	$docs  = get_post_meta( $product_id, '_cpc_docs', true );
	$raw   = (string) get_post_meta( $product_id, '_cpc_finishes', true );

	return array(
		'code'           => (string) get_post_meta( $product_id, '_cpc_code', true ),
		'subtitle'       => (string) get_post_meta( $product_id, '_cpc_subtitle', true ),
		'finishes'       => array_values( array_filter( array_map( 'trim', explode( ',', $raw ) ), 'strlen' ) ),
		'img_normal'     => absint( get_post_meta( $product_id, '_cpc_img_normal', true ) ),
		'img_exploded'   => absint( get_post_meta( $product_id, '_cpc_img_exploded', true ) ),
		'img_dimensions' => absint( get_post_meta( $product_id, '_cpc_img_dimensions', true ) ),
		'engineering'    => (string) get_post_meta( $product_id, '_cpc_engineering', true ),
		'specs'          => is_array( $specs ) ? $specs : array(),
		'area'           => (string) get_post_meta( $product_id, '_cpc_area', true ),
		'docs'           => is_array( $docs ) ? $docs : array(),
	);
}

/**
 * Term archive URL (empty string on error).
 *
 * @param WP_Term $term Term.
 * @return string
 */
function cpc_term_url( $term ) {
	$url = get_term_link( $term );
	return is_wp_error( $url ) ? '' : $url;
}

/**
 * Chain of product_cat terms from the top-level ancestor down to $term (inclusive).
 *
 * @param WP_Term $term Term.
 * @return WP_Term[]
 */
function cpc_term_chain( $term ) {
	$chain = array( $term );
	$ids   = get_ancestors( $term->term_id, 'product_cat', 'taxonomy' ); // Nearest parent first.
	foreach ( $ids as $id ) {
		$parent = get_term( $id, 'product_cat' );
		if ( $parent && ! is_wp_error( $parent ) ) {
			array_unshift( $chain, $parent );
		}
	}
	return $chain;
}

/**
 * The deepest product_cat term assigned to a product (used for the breadcrumb).
 *
 * @param int $product_id Product ID.
 * @return WP_Term|null
 */
function cpc_product_primary_term( $product_id ) {
	$terms = get_the_terms( $product_id, 'product_cat' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return null;
	}
	$best  = null;
	$depth = -1;
	foreach ( $terms as $term ) {
		$d = count( get_ancestors( $term->term_id, 'product_cat', 'taxonomy' ) );
		if ( $d > $depth ) {
			$depth = $d;
			$best  = $term;
		}
	}
	return $best;
}

/**
 * Breadcrumb markup. The last item is the current page (bold, not a link).
 *
 * @param array $items Items: array( 'label' => string, 'url' => string ).
 * @return string
 */
function cpc_breadcrumb_html( array $items ) {
	$out = '<ol class="cpc-breadcrumb__list">';
	$n   = count( $items );
	foreach ( $items as $i => $item ) {
		$last = ( $i === $n - 1 );
		$out .= '<li class="cpc-breadcrumb__item' . ( $last ? ' is-current' : '' ) . '"' . ( $last ? ' aria-current="page"' : '' ) . '>';
		if ( ! $last && ! empty( $item['url'] ) ) {
			$out .= '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a>';
		} else {
			$out .= '<span>' . esc_html( $item['label'] ) . '</span>';
		}
		$out .= '</li>';
	}
	return $out . '</ol>';
}

/**
 * Inline SVG icons (static markup, safe to echo).
 *
 * @param string $name Icon name.
 * @return string
 */
function cpc_icon( $name ) {
	switch ( $name ) {
		case 'doc':
			return '<svg class="cpc-icon cpc-icon--doc" viewBox="0 0 14 16" width="14" height="16" aria-hidden="true" focusable="false"><rect x="1" y="1" width="12" height="14" rx="1.5" fill="#fff" stroke="#555" stroke-width="1.2"/><rect x="3" y="3.2" width="8" height="3.4" fill="#555"/><path d="M7 8.2v4.2M5.2 10.8L7 12.6l1.8-1.8" stroke="#555" stroke-width="1.2" fill="none"/></svg>';
		case 'lock':
			return '<svg class="cpc-icon cpc-icon--lock" viewBox="0 0 14 16" width="14" height="16" aria-hidden="true" focusable="false"><rect x="2.5" y="7" width="9" height="7.5" rx="1" fill="none" stroke="#be212f" stroke-width="1.3"/><path d="M4.6 7V5a2.4 2.4 0 0 1 4.8 0v2" fill="none" stroke="#be212f" stroke-width="1.3"/><circle cx="7" cy="10.6" r="1" fill="#be212f"/></svg>';
		case 'search':
			return '<svg class="cpc-icon cpc-icon--search" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><circle cx="10" cy="10" r="6.2" fill="none" stroke="#fff" stroke-width="2"/><path d="M14.8 14.8L21 21" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/></svg>';
		case 'facebook':
			return '<svg class="cpc-icon cpc-icon--facebook" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path fill="#626262" fill-rule="evenodd" d="M20 2H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h8.6v-7.7H10v-3h2.6V9.1c0-2.6 1.6-4 3.9-4 1.1 0 2 .1 2.3.1v2.7h-1.6c-1.3 0-1.5.6-1.5 1.5v2h3l-.4 3h-2.6V22H20a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/></svg>';
		case 'linkedin':
			return '<svg class="cpc-icon cpc-icon--linkedin" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path fill="#626262" fill-rule="evenodd" d="M20 2H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zM8.3 18.3H5.7V10h2.6zM7 8.9a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm11.3 9.4h-2.6v-4c0-1 0-2.2-1.4-2.2s-1.6 1-1.6 2.1v4.1h-2.6V10h2.5v1.1c.4-.7 1.2-1.3 2.5-1.3 2.7 0 3.2 1.8 3.2 4.1z"/></svg>';
		case 'emblem':
			return '<svg class="cpc-emblem__svg" viewBox="0 0 38 38" width="38" height="38" aria-hidden="true" focusable="false"><circle cx="19" cy="19" r="17.5" fill="#fff" stroke="#f04e29" stroke-width="2"/><circle cx="19" cy="19" r="11" fill="#1d1d1b"/><path d="M12 16h14M12 19.5h14M12 23h14" stroke="#fff" stroke-width="1.6"/><path d="M10 28L28 10" stroke="#f04e29" stroke-width="2.2"/></svg>';
	}
	return '';
}


/**
 * Clean a shortcode attribute value.
 *
 * Page builders and the visual editor can wrap attribute values in HTML entities or curly quotes
 * (category=&quot;x&quot; or category=”x”). This decodes those and trims quotes and whitespace.
 *
 * @param mixed $value Raw attribute value.
 * @return string
 */
function cpc_clean_attr( $value ) {
	if ( ! is_scalar( $value ) ) {
		return '';
	}
	$value = html_entity_decode( (string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$value = wp_strip_all_tags( $value );
	$chars = '[\s\p{Z}"\'`\x{2018}\x{2019}\x{201A}\x{201B}\x{201C}\x{201D}\x{201E}\x{2032}\x{2033}\x{00AB}\x{00BB}]+';
	$clean = preg_replace( '/^' . $chars . '|' . $chars . '$/u', '', $value );
	return is_string( $clean ) ? $clean : trim( $value );
}

/**
 * The WooCommerce product title (the real product name).
 *
 * @param int $product_id Product ID.
 * @return string
 */
function cpc_product_title( $product_id ) {
	return (string) get_the_title( absint( $product_id ) );
}

/**
 * Keep an attachment ID only when it is an existing image.
 *
 * @param int $attachment_id Attachment ID.
 * @return int
 */
function cpc_valid_image_id( $attachment_id ) {
	$attachment_id = absint( $attachment_id );
	return ( $attachment_id && wp_attachment_is_image( $attachment_id ) ) ? $attachment_id : 0;
}

/**
 * Image used on product cards: the featured image, else the saved Normal image.
 *
 * @param int   $product_id Product ID.
 * @param array $d          Product data from cpc_get_product_data().
 * @return int Attachment ID or 0.
 */
function cpc_card_image_id( $product_id, array $d ) {
	$id = cpc_valid_image_id( get_post_thumbnail_id( $product_id ) );
	return $id ? $id : cpc_valid_image_id( $d['img_normal'] );
}

/**
 * The three product views shown on the single product page, in tab order.
 * The Normal view uses the saved Normal image, else the featured image.
 *
 * @param int   $product_id Product ID.
 * @param array $d          Product data from cpc_get_product_data().
 * @return array key => array( 'label' => string, 'id' => int attachment ID or 0 )
 */
function cpc_product_views( $product_id, array $d ) {
	$normal = cpc_valid_image_id( $d['img_normal'] );
	if ( ! $normal ) {
		$normal = cpc_valid_image_id( get_post_thumbnail_id( $product_id ) );
	}
	return array(
		'normal'     => array(
			'label' => __( 'Normal', 'custom-product-catalog' ),
			'id'    => $normal,
		),
		'exploded'   => array(
			'label' => __( 'Exploded', 'custom-product-catalog' ),
			'id'    => cpc_valid_image_id( $d['img_exploded'] ),
		),
		'dimensions' => array(
			'label' => __( 'Dimensions (mm)', 'custom-product-catalog' ),
			'id'    => cpc_valid_image_id( $d['img_dimensions'] ),
		),
	);
}

/**
 * URL of the page that holds the shortcode, used as the base for pagination links.
 *
 * @return string
 */
function cpc_current_base_url() {
	$url = '';
	if ( is_singular() ) {
		$url = get_permalink( get_queried_object_id() );
	} elseif ( is_tax( 'product_cat' ) ) {
		$url = cpc_term_url( get_queried_object() );
	}
	return $url ? $url : home_url( '/' );
}

/**
 * Pagination markup (uses the cpc_page query argument so it works on pages, archives and with any permalink setting).
 *
 * @param int    $current  Current page number.
 * @param int    $total    Total number of pages.
 * @param string $base_url Page URL without the cpc_page argument.
 * @return string
 */
function cpc_pagination_html( $current, $total, $base_url ) {
	$current = max( 1, (int) $current );
	$total   = (int) $total;
	if ( $total < 2 ) {
		return '';
	}
	$big   = 999999999;
	$links = paginate_links(
		array(
			'base'         => str_replace( (string) $big, '%#%', esc_url_raw( add_query_arg( 'cpc_page', $big, $base_url ) ) ),
			'format'       => '',
			'current'      => $current,
			'total'        => $total,
			'type'         => 'list',
			'mid_size'     => 2,
			'end_size'     => 1,
			'prev_text'    => __( '&laquo; Prev', 'custom-product-catalog' ),
			'next_text'    => __( 'Next &raquo;', 'custom-product-catalog' ),
			'add_fragment' => '#cpc-products',
		)
	);
	if ( ! $links ) {
		return '';
	}
	return '<nav class="cpc-pagination" aria-label="' . esc_attr__( 'Product pages', 'custom-product-catalog' ) . '">' . $links . '</nav>';
}

/**
 * Short message shown only to logged-in editors when a shortcode cannot render. Visitors see nothing.
 *
 * @param string $message Plain-text message.
 * @return string
 */
function cpc_editor_notice( $message ) {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return '';
	}
	return '<div class="cpc cpc-notice"><p>' . esc_html( $message ) . '</p></div>';
}

/**
 * Attributes for the outer wrapper element of a catalog page.
 *
 * Product and category pages that the plugin renders itself (WooCommerce single product and
 * product category templates) are placed in a centered container that uses the same widths as the
 * Nicepage content sheet: 1140px (desktop), 940px (small desktop), 720px (tablet), 540px (large
 * phone) and 340px (phone). A shortcode placed inside a Nicepage/theme page already sits in that
 * page's own container, so there it simply fills its parent and is not constrained twice.
 *
 * Shortcode attribute container="auto|yes|no" (default auto) overrides the detection.
 * Filters: `cpc_use_container` (bool) and `cpc_container_widths` (array of widths in px).
 *
 * @param string $base_class Page class, for example "cpc-single".
 * @param string $mode       auto, yes or no.
 * @return string Ready-to-print class and style attributes.
 */
function cpc_wrap_attrs( $base_class, $mode = 'auto' ) {
	$mode = strtolower( trim( (string) $mode ) );
	if ( in_array( $mode, array( 'yes', 'true', '1', 'on' ), true ) ) {
		$on = true;
	} elseif ( in_array( $mode, array( 'no', 'false', '0', 'off' ), true ) ) {
		$on = false;
	} else {
		$on = is_tax( 'product_cat' ) || is_singular( 'product' );
	}
	/**
	 * Whether to place the catalog page in the centered, Nicepage-width container.
	 *
	 * @param bool   $on   Current decision.
	 * @param string $mode Value of the shortcode container attribute.
	 */
	$on = (bool) apply_filters( 'cpc_use_container', $on, $mode );

	$class = 'cpc ' . $base_class;
	$style = '';
	if ( $on ) {
		$class  .= ' cpc-contained';
		$widths  = apply_filters(
			'cpc_container_widths',
			array(
				'xl' => 1140,
				'lg' => 940,
				'md' => 720,
				'sm' => 540,
				'xs' => 340,
			)
		);
		foreach ( array( 'xl', 'lg', 'md', 'sm', 'xs' ) as $key ) {
			if ( ! empty( $widths[ $key ] ) ) {
				$style .= '--cpc-w-' . $key . ':' . absint( $widths[ $key ] ) . 'px;';
			}
		}
	}
	$out = 'class="' . esc_attr( $class ) . '"';
	if ( '' !== $style ) {
		$out .= ' style="' . esc_attr( $style ) . '"';
	}
	return $out;
}
