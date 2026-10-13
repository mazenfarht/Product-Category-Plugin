<?php
/**
 * Category Icon field on WooCommerce product categories (used by the category icon strip).
 *
 * @package CustomProductCatalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'product_cat_add_form_fields', 'cpc_category_add_field' );
add_action( 'product_cat_edit_form_fields', 'cpc_category_edit_field', 10, 1 );
add_action( 'created_product_cat', 'cpc_category_save' );
add_action( 'edited_product_cat', 'cpc_category_save' );

/**
 * Field on the "Add new category" form.
 */
function cpc_category_add_field() {
	wp_nonce_field( 'cpc_save_category', 'cpc_category_nonce' );
	?>
	<div class="form-field">
		<label><?php esc_html_e( 'Category Icon', 'custom-product-catalog' ); ?></label>
		<?php cpc_image_picker( 'cpc_category_icon_id', 0 ); ?>
		<p class="description"><?php esc_html_e( 'Icon shown inside the circle in the category strip.', 'custom-product-catalog' ); ?></p>
	</div>
	<?php
}

/**
 * Field on the "Edit category" form.
 *
 * @param WP_Term $term Term being edited.
 */
function cpc_category_edit_field( $term ) {
	$icon_id = absint( get_term_meta( $term->term_id, 'cpc_category_icon_id', true ) );
	wp_nonce_field( 'cpc_save_category', 'cpc_category_nonce' );
	?>
	<tr class="form-field">
		<th scope="row"><label><?php esc_html_e( 'Category Icon', 'custom-product-catalog' ); ?></label></th>
		<td>
			<?php cpc_image_picker( 'cpc_category_icon_id', $icon_id ); ?>
			<p class="description"><?php esc_html_e( 'Icon shown inside the circle in the category strip.', 'custom-product-catalog' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Save the Category Icon.
 *
 * @param int $term_id Term ID.
 */
function cpc_category_save( $term_id ) {
	if ( ! isset( $_POST['cpc_category_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpc_category_nonce'] ) ), 'cpc_save_category' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_term', $term_id ) ) {
		return;
	}
	$icon_id = isset( $_POST['cpc_category_icon_id'] ) ? absint( $_POST['cpc_category_icon_id'] ) : 0;
	if ( $icon_id ) {
		update_term_meta( $term_id, 'cpc_category_icon_id', $icon_id );
	} else {
		delete_term_meta( $term_id, 'cpc_category_icon_id' );
	}
}
