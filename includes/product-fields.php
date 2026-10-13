<?php
/**
 * Custom product fields (meta box on the WooCommerce product edit screen).
 *
 * @package CustomProductCatalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', 'cpc_add_product_meta_box' );
add_action( 'save_post_product', 'cpc_save_product_fields', 10, 2 );
add_action( 'admin_enqueue_scripts', 'cpc_admin_assets' );

/**
 * Register the meta box.
 */
function cpc_add_product_meta_box() {
	add_meta_box(
		'cpc_product_fields',
		__( 'Product Catalog Fields', 'custom-product-catalog' ),
		'cpc_render_product_meta_box',
		'product',
		'normal',
		'high'
	);
}

/**
 * Admin CSS/JS for the product and product category screens.
 *
 * @param string $hook Current admin page hook.
 */
function cpc_admin_assets( $hook ) {
	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}
	$is_product = ( 'product' === $screen->post_type && in_array( $hook, array( 'post.php', 'post-new.php' ), true ) );
	$is_cat     = ( 'product_cat' === $screen->taxonomy );
	if ( ! $is_product && ! $is_cat ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'cpc-admin', CPC_URL . 'assets/css/admin.css', array(), CPC_VERSION );
	wp_enqueue_script( 'cpc-admin', CPC_URL . 'assets/js/admin.js', array( 'jquery' ), CPC_VERSION, true );
}

/**
 * Image picker (stores an attachment ID).
 *
 * @param string $name          Input name.
 * @param int    $attachment_id Current attachment ID.
 */
function cpc_image_picker( $name, $attachment_id ) {
	$attachment_id = absint( $attachment_id );
	$thumb         = $attachment_id ? wp_get_attachment_image( $attachment_id, 'thumbnail' ) : '';
	?>
	<div class="cpc-image-picker">
		<span class="cpc-image-picker__preview"><?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<input type="hidden" class="cpc-image-picker__input" name="<?php echo esc_attr( $name ); ?>" value="<?php echo $attachment_id ? esc_attr( $attachment_id ) : ''; ?>">
		<button type="button" class="button cpc-image-select"><?php esc_html_e( 'Select image', 'custom-product-catalog' ); ?></button>
		<button type="button" class="button-link cpc-image-remove"<?php echo $attachment_id ? '' : ' style="display:none"'; ?>><?php esc_html_e( 'Remove', 'custom-product-catalog' ); ?></button>
	</div>
	<?php
}

/**
 * One Specifications row.
 *
 * @param string|int $i   Row index (or the __INDEX__ placeholder).
 * @param array      $row Row data.
 */
function cpc_spec_row( $i, $row = array() ) {
	$label = isset( $row['label'] ) ? $row['label'] : '';
	$value = isset( $row['value'] ) ? $row['value'] : '';
	?>
	<div class="cpc-row">
		<input type="text" class="cpc-row__label" name="cpc_specs[<?php echo esc_attr( $i ); ?>][label]" value="<?php echo esc_attr( $label ); ?>" placeholder="<?php esc_attr_e( 'Label (e.g. Outlet Type)', 'custom-product-catalog' ); ?>">
		<input type="text" class="cpc-row__value" name="cpc_specs[<?php echo esc_attr( $i ); ?>][value]" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php esc_attr_e( 'Value (e.g. Bottom Outlet - Hub)', 'custom-product-catalog' ); ?>">
		<button type="button" class="button-link cpc-remove-row"><?php esc_html_e( 'Remove', 'custom-product-catalog' ); ?></button>
	</div>
	<?php
}

/**
 * One Support Document row.
 *
 * @param string|int $i   Row index (or the __INDEX__ placeholder).
 * @param array      $row Row data.
 */
function cpc_doc_row( $i, $row = array() ) {
	$label  = isset( $row['label'] ) ? $row['label'] : '';
	$url    = isset( $row['url'] ) ? $row['url'] : '';
	$locked = ! empty( $row['locked'] );
	?>
	<div class="cpc-row">
		<input type="text" class="cpc-row__label" name="cpc_docs[<?php echo esc_attr( $i ); ?>][label]" value="<?php echo esc_attr( $label ); ?>" placeholder="<?php esc_attr_e( 'Label (e.g. FD-7010 Data Sheet)', 'custom-product-catalog' ); ?>">
		<input type="text" class="cpc-row__value cpc-url-input" name="cpc_docs[<?php echo esc_attr( $i ); ?>][url]" value="<?php echo esc_attr( $url ); ?>" placeholder="<?php esc_attr_e( 'File / URL', 'custom-product-catalog' ); ?>">
		<button type="button" class="button cpc-url-select"><?php esc_html_e( 'Select file', 'custom-product-catalog' ); ?></button>
		<label class="cpc-row__locked"><input type="checkbox" name="cpc_docs[<?php echo esc_attr( $i ); ?>][locked]" value="1"<?php checked( $locked ); ?>> <?php esc_html_e( 'Locked icon', 'custom-product-catalog' ); ?></label>
		<button type="button" class="button-link cpc-remove-row"><?php esc_html_e( 'Remove', 'custom-product-catalog' ); ?></button>
	</div>
	<?php
}

/**
 * Meta box output.
 *
 * @param WP_Post $post Product post.
 */
function cpc_render_product_meta_box( $post ) {
	$d = cpc_get_product_data( $post->ID );
	wp_nonce_field( 'cpc_save_product', 'cpc_product_nonce' );
	?>
	<div class="cpc-fields">
		<p>
			<label for="cpc_code"><strong><?php esc_html_e( 'Product Code', 'custom-product-catalog' ); ?></strong></label>
			<input type="text" id="cpc_code" name="cpc_code" class="widefat" value="<?php echo esc_attr( $d['code'] ); ?>" placeholder="FD-7010">
		</p>
		<p>
			<label for="cpc_subtitle"><strong><?php esc_html_e( 'Subtitle', 'custom-product-catalog' ); ?></strong></label>
			<input type="text" id="cpc_subtitle" name="cpc_subtitle" class="widefat" value="<?php echo esc_attr( $d['subtitle'] ); ?>" placeholder="Finished Floor Strainer">
		</p>
		<p>
			<label for="cpc_finishes"><strong><?php esc_html_e( 'Finish Options', 'custom-product-catalog' ); ?></strong></label>
			<input type="text" id="cpc_finishes" name="cpc_finishes" class="widefat" value="<?php echo esc_attr( implode( ', ', $d['finishes'] ) ); ?>" placeholder="SS, B, CPB">
			<span class="description"><?php esc_html_e( 'Separate values with commas, in display order.', 'custom-product-catalog' ); ?></span>
		</p>

		<div class="cpc-images">
			<div><strong><?php esc_html_e( 'Normal Image', 'custom-product-catalog' ); ?></strong><?php cpc_image_picker( 'cpc_img_normal', $d['img_normal'] ); ?></div>
			<div><strong><?php esc_html_e( 'Exploded Image', 'custom-product-catalog' ); ?></strong><?php cpc_image_picker( 'cpc_img_exploded', $d['img_exploded'] ); ?></div>
			<div><strong><?php esc_html_e( 'Dimensions Image', 'custom-product-catalog' ); ?></strong><?php cpc_image_picker( 'cpc_img_dimensions', $d['img_dimensions'] ); ?></div>
		</div>

		<p>
			<label for="cpc_engineering"><strong><?php esc_html_e( 'Engineering Specifications', 'custom-product-catalog' ); ?></strong></label>
			<textarea id="cpc_engineering" name="cpc_engineering" class="widefat" rows="4"><?php echo esc_textarea( $d['engineering'] ); ?></textarea>
			<span class="description"><?php esc_html_e( 'Links can be added with standard HTML (<a href="...">text</a>).', 'custom-product-catalog' ); ?></span>
		</p>

		<div class="cpc-repeater" data-next="<?php echo esc_attr( count( $d['specs'] ) ); ?>">
			<strong><?php esc_html_e( 'Specifications', 'custom-product-catalog' ); ?></strong>
			<div class="cpc-rows">
				<?php foreach ( array_values( $d['specs'] ) as $i => $row ) { cpc_spec_row( $i, $row ); } ?>
			</div>
			<script type="text/template" class="cpc-row-template"><?php cpc_spec_row( '__INDEX__' ); ?></script>
			<button type="button" class="button cpc-add-row"><?php esc_html_e( 'Add specification row', 'custom-product-catalog' ); ?></button>
		</div>

		<p>
			<label for="cpc_area"><strong><?php esc_html_e( 'Area of Application', 'custom-product-catalog' ); ?></strong></label>
			<textarea id="cpc_area" name="cpc_area" class="widefat" rows="4"><?php echo esc_textarea( $d['area'] ); ?></textarea>
		</p>

		<div class="cpc-repeater" data-next="<?php echo esc_attr( count( $d['docs'] ) ); ?>">
			<strong><?php esc_html_e( 'Product Support Documents', 'custom-product-catalog' ); ?></strong>
			<div class="cpc-rows">
				<?php foreach ( array_values( $d['docs'] ) as $i => $row ) { cpc_doc_row( $i, $row ); } ?>
			</div>
			<script type="text/template" class="cpc-row-template"><?php cpc_doc_row( '__INDEX__' ); ?></script>
			<button type="button" class="button cpc-add-row"><?php esc_html_e( 'Add document', 'custom-product-catalog' ); ?></button>
		</div>
	</div>
	<?php
}

/**
 * Save or delete a meta value.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @param mixed  $value   Value (empty values delete the meta).
 */
function cpc_save_meta( $post_id, $key, $value ) {
	if ( '' === $value || array() === $value || 0 === $value ) {
		delete_post_meta( $post_id, $key );
	} else {
		update_post_meta( $post_id, $key, $value );
	}
}

/**
 * Plain-text value from a posted scalar.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function cpc_clean_text( $value ) {
	return is_scalar( $value ) ? sanitize_text_field( wp_unslash( (string) $value ) ) : '';
}

/**
 * Save the product fields.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 */
function cpc_save_product_fields( $post_id, $post ) {
	if ( ! isset( $_POST['cpc_product_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpc_product_nonce'] ) ), 'cpc_save_product' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	cpc_save_meta( $post_id, '_cpc_code', isset( $_POST['cpc_code'] ) ? cpc_clean_text( $_POST['cpc_code'] ) : '' );
	cpc_save_meta( $post_id, '_cpc_subtitle', isset( $_POST['cpc_subtitle'] ) ? cpc_clean_text( $_POST['cpc_subtitle'] ) : '' );
	cpc_save_meta( $post_id, '_cpc_finishes', isset( $_POST['cpc_finishes'] ) ? cpc_clean_text( $_POST['cpc_finishes'] ) : '' );

	foreach ( array( 'normal', 'exploded', 'dimensions' ) as $view ) {
		$field = 'cpc_img_' . $view;
		cpc_save_meta( $post_id, '_' . $field, isset( $_POST[ $field ] ) ? absint( $_POST[ $field ] ) : 0 );
	}

	cpc_save_meta( $post_id, '_cpc_engineering', isset( $_POST['cpc_engineering'] ) ? wp_kses_post( wp_unslash( $_POST['cpc_engineering'] ) ) : '' );
	cpc_save_meta( $post_id, '_cpc_area', isset( $_POST['cpc_area'] ) ? wp_kses_post( wp_unslash( $_POST['cpc_area'] ) ) : '' );

	$specs = array();
	if ( isset( $_POST['cpc_specs'] ) && is_array( $_POST['cpc_specs'] ) ) {
		foreach ( $_POST['cpc_specs'] as $row ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( ! is_array( $row ) ) {
				continue;
			}
			$label = isset( $row['label'] ) ? cpc_clean_text( $row['label'] ) : '';
			$value = isset( $row['value'] ) ? cpc_clean_text( $row['value'] ) : '';
			if ( '' === $label && '' === $value ) {
				continue;
			}
			$specs[] = array( 'label' => $label, 'value' => $value );
		}
	}
	cpc_save_meta( $post_id, '_cpc_specs', $specs );

	$docs = array();
	if ( isset( $_POST['cpc_docs'] ) && is_array( $_POST['cpc_docs'] ) ) {
		foreach ( $_POST['cpc_docs'] as $row ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( ! is_array( $row ) ) {
				continue;
			}
			$label = isset( $row['label'] ) ? cpc_clean_text( $row['label'] ) : '';
			$url   = ( isset( $row['url'] ) && is_scalar( $row['url'] ) ) ? esc_url_raw( trim( wp_unslash( (string) $row['url'] ) ) ) : '';
			if ( '' === $label && '' === $url ) {
				continue;
			}
			$docs[] = array(
				'label'  => $label,
				'url'    => $url,
				'locked' => ! empty( $row['locked'] ) ? 1 : 0,
			);
		}
	}
	cpc_save_meta( $post_id, '_cpc_docs', $docs );
}
