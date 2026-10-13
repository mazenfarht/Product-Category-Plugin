<?php
/**
 * Product card: image, WooCommerce product title, product code, subtitle. The whole card links to the product page.
 *
 * Variables: $pid.
 *
 * @package CustomProductCatalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$d        = cpc_get_product_data( $pid );
$title    = cpc_product_title( $pid );
$alt      = '' !== $title ? $title : trim( $d['code'] . ' ' . $d['subtitle'] );
$image_id = cpc_card_image_id( $pid, $d );
?>
<a class="cpc-card" href="<?php echo esc_url( get_permalink( $pid ) ); ?>">
	<span class="cpc-card__image">
		<?php echo $image_id ? wp_get_attachment_image( $image_id, 'medium', false, array( 'alt' => $alt ) ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</span>
	<span class="cpc-card__title"><?php echo esc_html( $title ); ?></span>
	<?php if ( '' !== $d['code'] && 0 !== strcasecmp( trim( $title ), $d['code'] ) ) : ?>
		<span class="cpc-card__code"><?php echo esc_html( $d['code'] ); ?></span>
	<?php endif; ?>
	<?php if ( '' !== $d['subtitle'] ) : ?>
		<span class="cpc-card__subtitle"><?php echo esc_html( $d['subtitle'] ); ?></span>
	<?php endif; ?>
</a>
