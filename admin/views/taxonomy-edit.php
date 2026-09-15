<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_edit = ! empty( $slug );
?>
<div class="wrap wem-ct-wrap">
	<div class="wem-ct-header">
		<div>
			<h1><?php echo esc_html( $is_edit ? __( 'Edit Taxonomy', 'wem-content-types' ) : __( 'Add Taxonomy', 'wem-content-types' ) ); ?></h1>
			<p><?php esc_html_e( 'A taxonomy groups related content. Keep the internal slug stable and attach it only to content types that actually need it.', 'wem-content-types' ); ?></p>
		</div>
		<a class="page-title-action" href="<?php echo esc_url( add_query_arg( [ 'page' => 'wem-content-types-taxonomies' ], admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Back to List', 'wem-content-types' ); ?></a>
	</div>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wem-ct-form">
		<input type="hidden" name="action" value="wem_ct_save_taxonomy">
		<input type="hidden" name="editing_slug" value="<?php echo esc_attr( $is_edit ? $slug : '' ); ?>">
		<?php wp_nonce_field( 'wem_ct_save_taxonomy' ); ?>

		<div class="wem-ct-card">
			<h2><?php esc_html_e( 'Basic Settings', 'wem-content-types' ); ?></h2>
			<div class="wem-ct-grid">
				<div class="wem-ct-field">
					<label for="wem-ct-tax-slug"><?php esc_html_e( 'Taxonomy Slug', 'wem-content-types' ); ?></label>
					<input id="wem-ct-tax-slug" name="wem_ct[slug]" type="text" maxlength="32" value="<?php echo esc_attr( $config['slug'] ); ?>" <?php echo $is_edit ? 'readonly' : ''; ?> required pattern="[a-z0-9_-]+">
					<p><?php esc_html_e( 'Lowercase internal key. Maximum 32 characters. It is locked after creation.', 'wem-content-types' ); ?></p>
					<p class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <code>product_category</code>, <code>industry</code>, <code>region</code>, <code>application</code>.</p>
				</div>

				<div class="wem-ct-field">
					<label for="wem-ct-tax-singular"><?php esc_html_e( 'Singular Label', 'wem-content-types' ); ?></label>
					<input id="wem-ct-tax-singular" name="wem_ct[singular_label]" type="text" value="<?php echo esc_attr( $config['singular_label'] ); ?>" required placeholder="Product Category">
					<p><?php esc_html_e( 'Name used when WordPress refers to one term.', 'wem-content-types' ); ?></p>
					<p class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <code>Product Category</code></p>
				</div>

				<div class="wem-ct-field">
					<label for="wem-ct-tax-plural"><?php esc_html_e( 'Plural Label', 'wem-content-types' ); ?></label>
					<input id="wem-ct-tax-plural" name="wem_ct[plural_label]" type="text" value="<?php echo esc_attr( $config['plural_label'] ); ?>" required placeholder="Product Categories">
					<p><?php esc_html_e( 'Name used for taxonomy menus and term lists.', 'wem-content-types' ); ?></p>
					<p class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <code>Product Categories</code></p>
				</div>
			</div>

			<div class="wem-ct-info">
				<strong><?php esc_html_e( 'Labels are generated automatically.', 'wem-content-types' ); ?></strong>
				<?php esc_html_e( 'WEM creates labels such as Add New Product Category, Edit Product Category, Search Product Categories, and All Product Categories from the two names above.', 'wem-content-types' ); ?>
			</div>

			<div class="wem-ct-field wem-ct-field-full">
				<label for="wem-ct-tax-description"><?php esc_html_e( 'Description', 'wem-content-types' ); ?></label>
				<textarea id="wem-ct-tax-description" name="wem_ct[description]" rows="3"><?php echo esc_textarea( $config['description'] ); ?></textarea>
				<p><?php esc_html_e( 'Optional explanation of what this taxonomy classifies.', 'wem-content-types' ); ?></p>
				<p class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <?php esc_html_e( 'Main categories used to organize products.', 'wem-content-types' ); ?></p>
			</div>
		</div>

		<div class="wem-ct-card wem-ct-status-card">
			<h2><?php esc_html_e( 'Status', 'wem-content-types' ); ?></h2>
			<div class="wem-ct-checks wem-ct-checks-two">
				<label>
					<input type="checkbox" name="wem_ct[enabled]" value="1" <?php checked( ! empty( $config['enabled'] ) ); ?>>
					<span>
						<strong><?php esc_html_e( 'Enabled', 'wem-content-types' ); ?></strong>
						<small><?php esc_html_e( 'Register this taxonomy in WordPress. Disable it temporarily without deleting its configuration, terms, or relationships.', 'wem-content-types' ); ?></small>
						<small class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <?php esc_html_e( 'Pause an unused Region taxonomy during restructuring and restore it later.', 'wem-content-types' ); ?></small>
					</span>
				</label>
			</div>
		</div>

		<div class="wem-ct-card">
			<h2><?php esc_html_e( 'Attach To', 'wem-content-types' ); ?></h2>
			<p class="wem-ct-section-help"><?php esc_html_e( 'Select one or more post types that should use this taxonomy. WEM post types are shown first; WordPress internal post types are hidden.', 'wem-content-types' ); ?></p>
			<div class="wem-ct-object-types">
				<?php foreach ( $available_post_types as $post_type_slug => $post_type_label ) : ?>
					<label>
						<input type="checkbox" name="wem_ct[object_types][]" value="<?php echo esc_attr( $post_type_slug ); ?>" <?php checked( in_array( $post_type_slug, $config['object_types'], true ) ); ?>>
						<span>
							<strong><?php echo esc_html( $post_type_label ); ?></strong>
							<small><code><?php echo esc_html( $post_type_slug ); ?></code></small>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
			<p class="wem-ct-footnote"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <?php esc_html_e( 'Product Category usually attaches only to Product. Industry or Region can later be shared by multiple content types if that reflects the real information structure.', 'wem-content-types' ); ?></p>
		</div>

		<div class="wem-ct-card">
			<h2><?php esc_html_e( 'Behavior', 'wem-content-types' ); ?></h2>
			<div class="wem-ct-checks wem-ct-checks-four">
				<label>
					<input type="checkbox" name="wem_ct[public]" value="1" <?php checked( ! empty( $config['public'] ) ); ?>>
					<span>
						<strong><?php esc_html_e( 'Public', 'wem-content-types' ); ?></strong>
						<small><?php esc_html_e( 'Allow normal front-end taxonomy archives and queries.', 'wem-content-types' ); ?></small>
						<small class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <?php esc_html_e( 'Enable for Product Category, Industry, Region, and similar visitor-facing classifications.', 'wem-content-types' ); ?></small>
					</span>
				</label>

				<label>
					<input type="checkbox" name="wem_ct[hierarchical]" value="1" <?php checked( ! empty( $config['hierarchical'] ) ); ?>>
					<span>
						<strong><?php esc_html_e( 'Hierarchical', 'wem-content-types' ); ?></strong>
						<small><?php esc_html_e( 'Use category-style parent/child terms instead of flat tags.', 'wem-content-types' ); ?></small>
						<small class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <?php esc_html_e( 'Enable for Product Category. Disable for flat attributes such as simple Tags.', 'wem-content-types' ); ?></small>
					</span>
				</label>

				<label>
					<input type="checkbox" name="wem_ct[show_admin_column]" value="1" <?php checked( ! empty( $config['show_admin_column'] ) ); ?>>
					<span>
						<strong><?php esc_html_e( 'Admin Column', 'wem-content-types' ); ?></strong>
						<small><?php esc_html_e( 'Show assigned terms as a column in the related post type list.', 'wem-content-types' ); ?></small>
						<small class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <?php esc_html_e( 'Useful for quickly seeing each product category from the Products list.', 'wem-content-types' ); ?></small>
					</span>
				</label>

				<label>
					<input type="checkbox" name="wem_ct[show_in_rest]" value="1" <?php checked( ! empty( $config['show_in_rest'] ) ); ?>>
					<span>
						<strong><?php esc_html_e( 'REST API / Block Editor', 'wem-content-types' ); ?></strong>
						<small><?php esc_html_e( 'Recommended for Gutenberg and future API/field/AI integrations.', 'wem-content-types' ); ?></small>
						<small class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <?php esc_html_e( 'Keep enabled for modern WordPress sites.', 'wem-content-types' ); ?></small>
					</span>
				</label>
			</div>
		</div>

		<details class="wem-ct-card wem-ct-advanced">
			<summary>
				<span><?php esc_html_e( 'Advanced Settings', 'wem-content-types' ); ?></span>
				<small><?php esc_html_e( 'Usually keep these defaults unless the taxonomy has a special visibility requirement.', 'wem-content-types' ); ?></small>
			</summary>
			<div class="wem-ct-advanced-body">
				<div class="wem-ct-checks wem-ct-checks-two">
					<label>
						<input type="checkbox" name="wem_ct[publicly_queryable]" value="1" <?php checked( ! empty( $config['publicly_queryable'] ) ); ?>>
						<span><strong><?php esc_html_e( 'Publicly Queryable', 'wem-content-types' ); ?></strong><small><?php esc_html_e( 'Allow visitors and front-end requests to query this taxonomy.', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( 'Normally enabled for Product Category, Industry, and Region.', 'wem-content-types' ); ?></small></span>
					</label>
					<label>
						<input type="checkbox" name="wem_ct[show_ui]" value="1" <?php checked( ! empty( $config['show_ui'] ) ); ?>>
						<span><strong><?php esc_html_e( 'Show Admin UI', 'wem-content-types' ); ?></strong><small><?php esc_html_e( 'Show term management screens in WordPress admin.', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( 'Keep enabled when editors need to manage terms.', 'wem-content-types' ); ?></small></span>
					</label>
					<label>
						<input type="checkbox" name="wem_ct[show_in_menu]" value="1" <?php checked( ! empty( $config['show_in_menu'] ) ); ?>>
						<span><strong><?php esc_html_e( 'Show in Admin Menu', 'wem-content-types' ); ?></strong><small><?php esc_html_e( 'Show the taxonomy management link below its attached post types.', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( 'Usually enabled together with Show Admin UI.', 'wem-content-types' ); ?></small></span>
					</label>
					<label>
						<input type="checkbox" name="wem_ct[show_in_nav_menus]" value="1" <?php checked( ! empty( $config['show_in_nav_menus'] ) ); ?>>
						<span><strong><?php esc_html_e( 'Show in Navigation Menus', 'wem-content-types' ); ?></strong><small><?php esc_html_e( 'Allow taxonomy terms to be selected in WordPress navigation menus.', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( 'Useful when category or region archive links belong in site navigation.', 'wem-content-types' ); ?></small></span>
					</label>
				</div>

				<div class="wem-ct-subsection">
					<h3><?php esc_html_e( 'Rewrite / Permalinks', 'wem-content-types' ); ?></h3>
					<p><?php esc_html_e( 'Control the public taxonomy URL base without changing its internal taxonomy slug.', 'wem-content-types' ); ?></p>

					<div class="wem-ct-checks wem-ct-checks-two">
						<label>
							<input type="checkbox" name="wem_ct[rewrite_enabled]" value="1" <?php checked( ! empty( $config['rewrite_enabled'] ) ); ?>>
							<span><strong><?php esc_html_e( 'Rewrite URLs', 'wem-content-types' ); ?></strong><small><?php esc_html_e( 'Generate normal pretty permalink rules for taxonomy archives.', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( 'Normally keep enabled for public Product Categories, Industries, or Regions.', 'wem-content-types' ); ?></small></span>
						</label>
						<label>
							<input type="checkbox" name="wem_ct[rewrite_with_front]" value="1" <?php checked( ! empty( $config['rewrite_with_front'] ) ); ?>>
							<span><strong><?php esc_html_e( 'Use Permalink Front', 'wem-content-types' ); ?></strong><small><?php esc_html_e( 'Include the front prefix from the site permalink structure.', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( 'Usually leave disabled for independent taxonomy URLs.', 'wem-content-types' ); ?></small></span>
						</label>
					</div>

					<div class="wem-ct-field wem-ct-rewrite-field">
						<label for="wem-ct-tax-rewrite-slug"><?php esc_html_e( 'Rewrite Slug', 'wem-content-types' ); ?></label>
						<input id="wem-ct-tax-rewrite-slug" name="wem_ct[rewrite_slug]" type="text" value="<?php echo esc_attr( $config['rewrite_slug'] ); ?>" placeholder="product-category">
						<p><?php esc_html_e( 'Optional URL base. Leave empty to use the internal taxonomy slug.', 'wem-content-types' ); ?></p>
						<p class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <?php esc_html_e( 'Internal slug', 'wem-content-types' ); ?> <code>product_category</code> → <?php esc_html_e( 'rewrite slug', 'wem-content-types' ); ?> <code>product-category</code>.</p>
					</div>
				</div>
			</div>
		</details>

		<p class="submit">
			<button type="submit" class="button button-primary button-large"><?php echo esc_html( $is_edit ? __( 'Save Changes', 'wem-content-types' ) : __( 'Create Taxonomy', 'wem-content-types' ) ); ?></button>
		</p>
	</form>
</div>
