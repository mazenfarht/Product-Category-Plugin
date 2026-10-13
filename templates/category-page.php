<?php
/**
 * Category page (Image 1).
 *
 * Variables: $container, $term, $top_id, $crumbs, $strip_terms, $children, $query, $pagination.
 *
 * @package CustomProductCatalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div <?php echo cpc_wrap_attrs( 'cpc-category', isset( $container ) ? $container : 'auto' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>

	<nav class="cpc-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'custom-product-catalog' ); ?>">
		<?php echo cpc_breadcrumb_html( $crumbs ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</nav>

	<div class="cpc-strip">
		<ul class="cpc-strip__list">
			<?php foreach ( $strip_terms as $strip_term ) : ?>
				<?php
				$icon_id    = absint( get_term_meta( $strip_term->term_id, 'cpc_category_icon_id', true ) );
				$is_current = ( (int) $strip_term->term_id === (int) $top_id );
				?>
				<li class="cpc-strip__item<?php echo $is_current ? ' is-current' : ''; ?>">
					<a href="<?php echo esc_url( cpc_term_url( $strip_term ) ); ?>"<?php echo $is_current ? ' aria-current="true"' : ''; ?>>
						<span class="cpc-strip__circle">
							<?php
							if ( $icon_id ) {
								echo wp_get_attachment_image( $icon_id, 'thumbnail', false, array( 'class' => 'cpc-strip__icon', 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
							}
							?>
						</span>
						<span class="cpc-strip__label"><?php echo esc_html( $strip_term->name ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<div class="cpc-strip__line">
			<span class="cpc-emblem"><?php echo cpc_icon( 'emblem' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		</div>
	</div>

	<div class="cpc-catalog">
		<h2 class="cpc-title"><?php echo esc_html( $term->name ); ?></h2>
		<div class="cpc-catalog__body">
			<?php if ( ! empty( $children ) ) : ?>
				<ul class="cpc-subcats">
					<?php foreach ( $children as $child ) : ?>
						<li class="cpc-subcats__item">
							<a href="<?php echo esc_url( cpc_term_url( $child ) ); ?>"><?php echo esc_html( $child->name ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<div class="cpc-results" id="cpc-products">
				<?php if ( $query->have_posts() ) : ?>
					<div class="cpc-grid">
						<?php
						while ( $query->have_posts() ) {
							$query->the_post();
							echo cpc_render( 'product-card', array( 'pid' => get_the_ID() ) ); // phpcs:ignore WordPress.Security.EscapeOutput
						}
						wp_reset_postdata();
						?>
					</div>
					<?php echo $pagination; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php else : ?>
					<p class="cpc-empty"><?php esc_html_e( 'No products found in this category.', 'custom-product-catalog' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>

</div>
