<?php
/**
 * Single product page (Image 2).
 *
 * Variables: $container, $pid, $d (product data), $title (WooCommerce product title), $crumbs.
 *
 * @package CustomProductCatalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$alt   = '' !== $title ? $title : trim( $d['code'] . ' ' . $d['subtitle'] );
$views = cpc_product_views( $pid, $d );

// Views that have an image; the first one is shown when the page loads.
$available = array();
foreach ( $views as $key => $view ) {
	if ( $view['id'] ) {
		$available[] = $key;
	}
}
$first_view = $available ? $available[0] : '';
$show_tabs  = count( $available ) > 1;

// Product Description: the product's existing WooCommerce description, exactly as stored.
$description = (string) get_post_field( 'post_content', $pid );

// Product Gallery: the images already in the product's existing WooCommerce Product Gallery.
$gallery_ids = array();
$wc_product  = function_exists( 'wc_get_product' ) ? wc_get_product( $pid ) : null;
if ( $wc_product ) {
	foreach ( $wc_product->get_gallery_image_ids() as $gallery_id ) {
		$gallery_id = cpc_valid_image_id( $gallery_id );
		if ( $gallery_id ) {
			$gallery_ids[] = $gallery_id;
		}
	}
}
?>
<div <?php echo cpc_wrap_attrs( 'cpc-single', isset( $container ) ? $container : 'auto' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>

	<nav class="cpc-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'custom-product-catalog' ); ?>">
		<?php echo cpc_breadcrumb_html( $crumbs ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</nav>

	<div class="cpc-single__page">
		<div class="cpc-single__box">

			<div class="cpc-single__top">

				<div class="cpc-single__media" data-cpc-gallery data-active-view="<?php echo esc_attr( $first_view ); ?>">
					<div class="cpc-single__image">
						<?php foreach ( $views as $key => $view ) : ?>
							<?php
							if ( ! $view['id'] ) {
								continue;
							}
							$is_first = ( $key === $first_view );
							?>
							<div class="cpc-view<?php echo $is_first ? ' is-active' : ''; ?>" data-view="<?php echo esc_attr( $key ); ?>"<?php echo $is_first ? '' : ' hidden'; ?>>
								<?php
								echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput
									$view['id'],
									'large',
									false,
									array(
										'alt'       => ( 'normal' === $key ) ? $alt : $alt . ' - ' . $view['label'],
										'data-full' => (string) wp_get_attachment_image_url( $view['id'], 'full' ),
									)
								);
								?>
							</div>
						<?php endforeach; ?>
						<?php if ( ! empty( $d['finishes'] ) ) : ?>
							<ul class="cpc-finishes" role="group" aria-label="<?php esc_attr_e( 'Finish options', 'custom-product-catalog' ); ?>">
								<?php foreach ( $d['finishes'] as $i => $finish ) : ?>
									<li class="cpc-finishes__item<?php echo 0 === $i ? ' is-active' : ''; ?>" role="button" tabindex="0" aria-pressed="<?php echo 0 === $i ? 'true' : 'false'; ?>"><?php echo esc_html( $finish ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<?php if ( $first_view ) : ?>
							<span class="cpc-magnifier" role="button" tabindex="0" aria-label="<?php esc_attr_e( 'Enlarge image', 'custom-product-catalog' ); ?>"><?php echo cpc_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<?php endif; ?>
					</div>
					<?php if ( $show_tabs ) : ?>
						<ul class="cpc-viewtabs" role="tablist">
							<?php foreach ( $views as $key => $view ) : ?>
								<?php
								$has_image = (bool) $view['id'];
								$is_first  = ( $key === $first_view );
								$classes   = 'cpc-viewtabs__item' . ( $is_first ? ' is-active' : '' ) . ( $has_image ? '' : ' is-disabled' );
								?>
								<li class="<?php echo esc_attr( $classes ); ?>" role="tab" data-view="<?php echo esc_attr( $key ); ?>" aria-selected="<?php echo $is_first ? 'true' : 'false'; ?>" tabindex="<?php echo $is_first ? '0' : '-1'; ?>"<?php echo $has_image ? '' : ' aria-disabled="true"'; ?>><?php echo esc_html( $view['label'] ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>

				<div class="cpc-single__info">
					<h1 class="cpc-single__title"><?php echo esc_html( $title ); ?></h1>

					<?php if ( '' !== $d['code'] && 0 !== strcasecmp( trim( $title ), $d['code'] ) ) : ?>
						<p class="cpc-single__code"><?php echo esc_html( $d['code'] ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== $d['subtitle'] ) : ?>
						<p class="cpc-single__subtitle"><?php echo esc_html( $d['subtitle'] ); ?></p>
					<?php endif; ?>

					<?php if ( '' !== trim( $description ) ) : ?>
						<div class="cpc-single__description"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
					<?php endif; ?>

					<?php if ( '' !== trim( $d['engineering'] ) ) : ?>
						<div class="cpc-engineering">
							<h3 class="cpc-engineering__heading"><?php esc_html_e( 'Engineering Specifications', 'custom-product-catalog' ); ?></h3>
							<div class="cpc-engineering__text"><?php echo wp_kses_post( wpautop( $d['engineering'] ) ); ?></div>
						</div>
					<?php endif; ?>

					<div class="cpc-share">
						<h3 class="cpc-share__heading"><?php esc_html_e( 'Share This Product', 'custom-product-catalog' ); ?></h3>
						<ul class="cpc-share__icons">
							<li><?php echo cpc_icon( 'facebook' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
							<li><?php echo cpc_icon( 'linkedin' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
						</ul>
					</div>
				</div>

				<?php if ( ! empty( $d['docs'] ) ) : ?>
					<aside class="cpc-docs">
						<h3 class="cpc-docs__heading"><?php esc_html_e( 'Product Support Documents', 'custom-product-catalog' ); ?></h3>
						<ul class="cpc-docs__list">
							<?php foreach ( $d['docs'] as $doc ) : ?>
								<?php
								$label  = isset( $doc['label'] ) ? $doc['label'] : '';
								$url    = isset( $doc['url'] ) ? $doc['url'] : '';
								$locked = ! empty( $doc['locked'] );
								?>
								<li class="cpc-docs__item<?php echo $locked ? ' is-locked' : ''; ?>">
									<?php echo cpc_icon( $locked ? 'lock' : 'doc' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									<?php if ( '' !== $url ) : ?>
										<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
									<?php else : ?>
										<span><?php echo esc_html( $label ); ?></span>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</aside>
				<?php endif; ?>

			</div>

			<?php if ( ! empty( $d['specs'] ) || '' !== trim( $d['area'] ) ) : ?>
				<hr class="cpc-single__divider">
				<div class="cpc-single__bottom">
					<div class="cpc-single__specs">
						<?php if ( ! empty( $d['specs'] ) ) : ?>
							<h2 class="cpc-section-heading"><?php esc_html_e( 'Specifications', 'custom-product-catalog' ); ?></h2>
							<table class="cpc-spec-table">
								<tbody>
									<?php foreach ( $d['specs'] as $row ) : ?>
										<tr>
											<th scope="row"><?php echo esc_html( isset( $row['label'] ) ? $row['label'] : '' ); ?></th>
											<td><?php echo esc_html( isset( $row['value'] ) ? $row['value'] : '' ); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						<?php endif; ?>
					</div>
					<div class="cpc-single__area">
						<?php if ( '' !== trim( $d['area'] ) ) : ?>
							<h2 class="cpc-section-heading"><?php esc_html_e( 'Area of Application', 'custom-product-catalog' ); ?></h2>
							<div class="cpc-area__text"><?php echo wp_kses_post( wpautop( $d['area'] ) ); ?></div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $gallery_ids ) : ?>
				<hr class="cpc-single__divider">
				<div class="cpc-single__gallery">
					<?php foreach ( $gallery_ids as $gallery_id ) : ?>
						<div class="cpc-single__gallery-item"><?php echo wp_get_attachment_image( $gallery_id, 'large', false, array( 'alt' => $alt ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		</div>
	</div>

</div>
