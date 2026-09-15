<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_edit = ! empty( $slug );
$all_supports = [
	'title' => [
		'label'   => __( 'Title', 'wem-content-types' ),
		'example' => __( 'Example: Product name.', 'wem-content-types' ),
	],
	'editor' => [
		'label'   => __( 'Editor', 'wem-content-types' ),
		'example' => __( 'Example: Product overview or long-form content.', 'wem-content-types' ),
	],
	'thumbnail' => [
		'label'   => __( 'Featured Image', 'wem-content-types' ),
		'example' => __( 'Example: Main product image.', 'wem-content-types' ),
	],
	'excerpt' => [
		'label'   => __( 'Excerpt', 'wem-content-types' ),
		'example' => __( 'Example: Short product summary used in cards or lists.', 'wem-content-types' ),
	],
	'revisions' => [
		'label'   => __( 'Revisions', 'wem-content-types' ),
		'example' => __( 'Example: Keep previous versions when content is updated.', 'wem-content-types' ),
	],
	'author' => [
		'label'   => __( 'Author', 'wem-content-types' ),
		'example' => __( 'Example: Record which WordPress user created the item.', 'wem-content-types' ),
	],
	'comments' => [
		'label'   => __( 'Comments', 'wem-content-types' ),
		'example' => __( 'Example: Allow visitor comments. Usually disabled for product content.', 'wem-content-types' ),
	],
	'page-attributes' => [
		'label'   => __( 'Page Attributes', 'wem-content-types' ),
		'example' => __( 'Example: Provide ordering and page-style attributes when needed.', 'wem-content-types' ),
	],
	'custom-fields' => [
		'label'   => __( 'Custom Fields', 'wem-content-types' ),
		'example' => __( 'Example: Enable native WordPress Custom Fields support. Usually leave this off until a field module needs it.', 'wem-content-types' ),
	],
];

$icon_choices = [
	'dashicons-admin-post'    => __( 'Post', 'wem-content-types' ),
	'dashicons-products'      => __( 'Products', 'wem-content-types' ),
	'dashicons-portfolio'     => __( 'Portfolio', 'wem-content-types' ),
	'dashicons-media-document'=> __( 'Document', 'wem-content-types' ),
	'dashicons-format-gallery'=> __( 'Gallery', 'wem-content-types' ),
	'dashicons-groups'        => __( 'Groups', 'wem-content-types' ),
	'dashicons-businessperson'=> __( 'Business', 'wem-content-types' ),
	'dashicons-location'      => __( 'Location', 'wem-content-types' ),
	'dashicons-category'      => __( 'Category', 'wem-content-types' ),
	'dashicons-feedback'      => __( 'Feedback', 'wem-content-types' ),
	'dashicons-book'          => __( 'Book', 'wem-content-types' ),
	'dashicons-lightbulb'     => __( 'Idea', 'wem-content-types' ),
];
?>
<div class="wrap wem-ct-wrap">
	<div class="wem-ct-header">
		<div>
			<h1><?php echo esc_html( $is_edit ? __( 'Edit Post Type', 'wem-content-types' ) : __( 'Add Post Type', 'wem-content-types' ) ); ?></h1>
			<p><?php esc_html_e( 'Define the content structure first. Advanced settings are optional and can usually keep their defaults.', 'wem-content-types' ); ?></p>
		</div>
		<a class="page-title-action" href="<?php echo esc_url( add_query_arg( [ 'page' => 'wem-content-types' ], admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Back to List', 'wem-content-types' ); ?></a>
	</div>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wem-ct-form">
		<input type="hidden" name="action" value="wem_ct_save_post_type">
		<input type="hidden" name="editing_slug" value="<?php echo esc_attr( $is_edit ? $slug : '' ); ?>">
		<?php wp_nonce_field( 'wem_ct_save_post_type' ); ?>

		<div class="wem-ct-card">
			<h2><?php esc_html_e( 'Basic Settings', 'wem-content-types' ); ?></h2>
			<div class="wem-ct-grid">
				<div class="wem-ct-field">
					<label for="wem-ct-slug"><?php esc_html_e( 'Post Type Slug', 'wem-content-types' ); ?></label>
					<input id="wem-ct-slug" name="wem_ct[slug]" type="text" maxlength="20" value="<?php echo esc_attr( $config['slug'] ); ?>" <?php echo $is_edit ? 'readonly' : ''; ?> required pattern="[a-z0-9_-]+">
					<p><?php esc_html_e( 'Lowercase key used internally by WordPress. Maximum 20 characters. It is locked after creation.', 'wem-content-types' ); ?></p>
					<p class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <code>product</code>, <code>solution</code>, <code>case</code>, <code>faq</code>, <code>download</code>. <?php esc_html_e( 'A singular internal key is recommended.', 'wem-content-types' ); ?></p>
				</div>

				<div class="wem-ct-field">
					<label for="wem-ct-singular"><?php esc_html_e( 'Singular Label', 'wem-content-types' ); ?></label>
					<input id="wem-ct-singular" name="wem_ct[singular_label]" type="text" value="<?php echo esc_attr( $config['singular_label'] ); ?>" required placeholder="Product">
					<p><?php esc_html_e( 'Name used when WordPress refers to one item.', 'wem-content-types' ); ?></p>
					<p class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <code>Product</code></p>
				</div>

				<div class="wem-ct-field">
					<label for="wem-ct-plural"><?php esc_html_e( 'Plural Label', 'wem-content-types' ); ?></label>
					<input id="wem-ct-plural" name="wem_ct[plural_label]" type="text" value="<?php echo esc_attr( $config['plural_label'] ); ?>" required placeholder="Products">
					<p><?php esc_html_e( 'Name used for the admin menu and lists containing multiple items.', 'wem-content-types' ); ?></p>
					<p class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <code>Products</code></p>
				</div>

				<div class="wem-ct-field wem-ct-icon-field">
					<label for="wem-ct-icon"><?php esc_html_e( 'Menu Icon', 'wem-content-types' ); ?></label>
					<div class="wem-ct-icon-input-row">
						<span class="wem-ct-icon-preview" aria-hidden="true"><span class="dashicons <?php echo esc_attr( $config['menu_icon'] ); ?>"></span></span>
						<input id="wem-ct-icon" data-wem-icon-input name="wem_ct[menu_icon]" type="text" value="<?php echo esc_attr( $config['menu_icon'] ); ?>" placeholder="dashicons-products">
					</div>
					<p><?php esc_html_e( 'Choose a common Dashicon below or enter another Dashicons class manually.', 'wem-content-types' ); ?></p>
					<div class="wem-ct-icon-picker" role="group" aria-label="<?php esc_attr_e( 'Common menu icons', 'wem-content-types' ); ?>">
						<?php foreach ( $icon_choices as $icon_class => $icon_label ) : ?>
							<button type="button" class="wem-ct-icon-choice<?php echo $config['menu_icon'] === $icon_class ? ' is-selected' : ''; ?>" data-icon="<?php echo esc_attr( $icon_class ); ?>" aria-pressed="<?php echo $config['menu_icon'] === $icon_class ? 'true' : 'false'; ?>" title="<?php echo esc_attr( $icon_label ); ?>">
								<span class="dashicons <?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></span>
								<span class="screen-reader-text"><?php echo esc_html( $icon_label ); ?></span>
							</button>
						<?php endforeach; ?>
					</div>
					<p class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <code>dashicons-products</code></p>
				</div>
			</div>

			<div class="wem-ct-info">
				<strong><?php esc_html_e( 'Labels are generated automatically.', 'wem-content-types' ); ?></strong>
				<?php esc_html_e( 'From the singular and plural names, WEM creates labels such as Add New Product, Edit Product, Search Products, and All Products. Individual labels can still be adjusted later through the label filters.', 'wem-content-types' ); ?>
			</div>

			<div class="wem-ct-field wem-ct-field-full">
				<label for="wem-ct-description"><?php esc_html_e( 'Description', 'wem-content-types' ); ?></label>
				<textarea id="wem-ct-description" name="wem_ct[description]" rows="3"><?php echo esc_textarea( $config['description'] ); ?></textarea>
				<p><?php esc_html_e( 'Optional short description of what this content type stores.', 'wem-content-types' ); ?></p>
				<p class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <?php esc_html_e( 'Products and structured product information.', 'wem-content-types' ); ?></p>
			</div>
		</div>

		<div class="wem-ct-card wem-ct-status-card">
			<h2><?php esc_html_e( 'Status', 'wem-content-types' ); ?></h2>
			<div class="wem-ct-checks wem-ct-checks-two">
				<label>
					<input type="checkbox" name="wem_ct[enabled]" value="1" <?php checked( ! empty( $config['enabled'] ) ); ?>>
					<span>
						<strong><?php esc_html_e( 'Enabled', 'wem-content-types' ); ?></strong>
						<small><?php esc_html_e( 'Register this post type in WordPress. Disable it temporarily without deleting its configuration or existing posts.', 'wem-content-types' ); ?></small>
						<small class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <?php esc_html_e( 'Disable an unused Case type during a redesign, then enable it again later.', 'wem-content-types' ); ?></small>
					</span>
				</label>
			</div>
		</div>

		<div class="wem-ct-card">
			<h2><?php esc_html_e( 'Behavior', 'wem-content-types' ); ?></h2>
			<div class="wem-ct-checks">
				<label>
					<input type="checkbox" name="wem_ct[public]" value="1" <?php checked( ! empty( $config['public'] ) ); ?>>
					<span>
						<strong><?php esc_html_e( 'Public', 'wem-content-types' ); ?></strong>
						<small><?php esc_html_e( 'Make this a normal visitor-facing content type.', 'wem-content-types' ); ?></small>
						<small class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <?php esc_html_e( 'Keep enabled for Product, Solution, Case, and other public content.', 'wem-content-types' ); ?></small>
					</span>
				</label>
				<label>
					<input type="checkbox" name="wem_ct[has_archive]" value="1" <?php checked( ! empty( $config['has_archive'] ) ); ?>>
					<span>
						<strong><?php esc_html_e( 'Archive', 'wem-content-types' ); ?></strong>
						<small><?php esc_html_e( 'Enable the post type archive URL.', 'wem-content-types' ); ?></small>
						<small class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <?php esc_html_e( 'Enable when you need a product listing or archive page.', 'wem-content-types' ); ?></small>
					</span>
				</label>
				<label>
					<input type="checkbox" name="wem_ct[show_in_rest]" value="1" <?php checked( ! empty( $config['show_in_rest'] ) ); ?>>
					<span>
						<strong><?php esc_html_e( 'REST API / Block Editor', 'wem-content-types' ); ?></strong>
						<small><?php esc_html_e( 'Recommended for modern WordPress sites and future field/API integrations.', 'wem-content-types' ); ?></small>
						<small class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <?php esc_html_e( 'Keep enabled for Gutenberg, REST API, and future field or AI integrations.', 'wem-content-types' ); ?></small>
					</span>
				</label>
			</div>
		</div>

		<div class="wem-ct-card">
			<h2><?php esc_html_e( 'Supports', 'wem-content-types' ); ?></h2>
			<p class="wem-ct-section-help"><?php esc_html_e( 'Choose which standard WordPress editing features this post type should expose.', 'wem-content-types' ); ?></p>
			<div class="wem-ct-supports">
				<?php foreach ( $all_supports as $key => $item ) : ?>
					<label>
						<input type="checkbox" name="wem_ct[supports][]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $config['supports'], true ) ); ?>>
						<span>
							<strong><?php echo esc_html( $item['label'] ); ?></strong>
							<small><?php echo esc_html( $item['example'] ); ?></small>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
		</div>

		<details class="wem-ct-card wem-ct-advanced">
			<summary>
				<span><?php esc_html_e( 'Advanced Settings', 'wem-content-types' ); ?></span>
				<small><?php esc_html_e( 'Usually keep these defaults unless the content type has a special visibility requirement.', 'wem-content-types' ); ?></small>
			</summary>
			<div class="wem-ct-advanced-body">
				<div class="wem-ct-checks wem-ct-checks-two">
					<label>
						<input type="checkbox" name="wem_ct[publicly_queryable]" value="1" <?php checked( ! empty( $config['publicly_queryable'] ) ); ?>>
						<span><strong><?php esc_html_e( 'Publicly Queryable', 'wem-content-types' ); ?></strong><small><?php esc_html_e( 'Allow front-end URLs and queries for individual items.', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( 'Normally enabled for public Products or Solutions.', 'wem-content-types' ); ?></small></span>
					</label>
					<label>
						<input type="checkbox" name="wem_ct[exclude_from_search]" value="1" <?php checked( ! empty( $config['exclude_from_search'] ) ); ?>>
						<span><strong><?php esc_html_e( 'Exclude From Search', 'wem-content-types' ); ?></strong><small><?php esc_html_e( 'Hide this content type from the normal WordPress front-end search results.', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( 'Useful for internal reference content that still has direct URLs.', 'wem-content-types' ); ?></small></span>
					</label>
					<label>
						<input type="checkbox" name="wem_ct[show_ui]" value="1" <?php checked( ! empty( $config['show_ui'] ) ); ?>>
						<span><strong><?php esc_html_e( 'Show Admin UI', 'wem-content-types' ); ?></strong><small><?php esc_html_e( 'Show WordPress administration screens for this content type.', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( 'Keep enabled for content editors.', 'wem-content-types' ); ?></small></span>
					</label>
					<label>
						<input type="checkbox" name="wem_ct[show_in_menu]" value="1" <?php checked( ! empty( $config['show_in_menu'] ) ); ?>>
						<span><strong><?php esc_html_e( 'Show in Admin Menu', 'wem-content-types' ); ?></strong><small><?php esc_html_e( 'Show the content type as a normal item in the WordPress admin menu.', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( 'Usually enabled together with Show Admin UI.', 'wem-content-types' ); ?></small></span>
					</label>
					<label>
						<input type="checkbox" name="wem_ct[show_in_nav_menus]" value="1" <?php checked( ! empty( $config['show_in_nav_menus'] ) ); ?>>
						<span><strong><?php esc_html_e( 'Show in Navigation Menus', 'wem-content-types' ); ?></strong><small><?php esc_html_e( 'Allow items from this post type to be selected in WordPress navigation menus.', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( 'Useful when products or solutions may be linked directly from menus.', 'wem-content-types' ); ?></small></span>
					</label>
					<label>
						<input type="checkbox" name="wem_ct[show_in_admin_bar]" value="1" <?php checked( ! empty( $config['show_in_admin_bar'] ) ); ?>>
						<span><strong><?php esc_html_e( 'Show in Admin Bar', 'wem-content-types' ); ?></strong><small><?php esc_html_e( 'Allow quick access from the WordPress admin toolbar.', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( 'Usually safe to keep enabled.', 'wem-content-types' ); ?></small></span>
					</label>
				</div>

				<div class="wem-ct-field wem-ct-compact-field">
					<label for="wem-ct-menu-position"><?php esc_html_e( 'Menu Position', 'wem-content-types' ); ?></label>
					<input id="wem-ct-menu-position" name="wem_ct[menu_position]" type="number" min="2" max="100" step="1" value="<?php echo esc_attr( $config['menu_position'] ); ?>" placeholder="25">
					<p><?php esc_html_e( 'Optional. Leave empty to let WordPress choose the normal position. Use a whole number from 2 to 100.', 'wem-content-types' ); ?></p>
					<p class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <code>25</code></p>
				</div>

				<div class="wem-ct-subsection">
					<h3><?php esc_html_e( 'Rewrite / Permalinks', 'wem-content-types' ); ?></h3>
					<p><?php esc_html_e( 'Control the public URL base without changing the internal post type slug.', 'wem-content-types' ); ?></p>

					<div class="wem-ct-checks wem-ct-checks-two">
						<label>
							<input type="checkbox" name="wem_ct[rewrite_enabled]" value="1" <?php checked( ! empty( $config['rewrite_enabled'] ) ); ?>>
							<span><strong><?php esc_html_e( 'Rewrite URLs', 'wem-content-types' ); ?></strong><small><?php esc_html_e( 'Generate normal pretty permalink rules for this post type.', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( 'Normally keep enabled for visitor-facing content.', 'wem-content-types' ); ?></small></span>
						</label>
						<label>
							<input type="checkbox" name="wem_ct[rewrite_with_front]" value="1" <?php checked( ! empty( $config['rewrite_with_front'] ) ); ?>>
							<span><strong><?php esc_html_e( 'Use Permalink Front', 'wem-content-types' ); ?></strong><small><?php esc_html_e( 'Include the front prefix from the site permalink structure.', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( 'Usually leave disabled if Products should stay at /products/ instead of inheriting something like /blog/.', 'wem-content-types' ); ?></small></span>
						</label>
					</div>

					<div class="wem-ct-field wem-ct-rewrite-field">
						<label for="wem-ct-rewrite-slug"><?php esc_html_e( 'Rewrite Slug', 'wem-content-types' ); ?></label>
						<input id="wem-ct-rewrite-slug" name="wem_ct[rewrite_slug]" type="text" value="<?php echo esc_attr( $config['rewrite_slug'] ); ?>" placeholder="products">
						<p><?php esc_html_e( 'Optional URL base. Leave empty to use the internal post type slug.', 'wem-content-types' ); ?></p>
						<p class="wem-ct-example"><strong><?php esc_html_e( 'Example:', 'wem-content-types' ); ?></strong> <?php esc_html_e( 'Internal slug', 'wem-content-types' ); ?> <code>product</code> → <?php esc_html_e( 'rewrite slug', 'wem-content-types' ); ?> <code>products</code> → <code>/products/sample-product/</code></p>
					</div>
				</div>
			</div>
		</details>

		<p class="submit">
			<button type="submit" class="button button-primary button-large"><?php echo esc_html( $is_edit ? __( 'Save Changes', 'wem-content-types' ) : __( 'Create Post Type', 'wem-content-types' ) ); ?></button>
		</p>
	</form>
</div>
