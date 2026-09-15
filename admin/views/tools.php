<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wem-ct-wrap">
	<div class="wem-ct-header">
		<div>
			<h1><?php esc_html_e( 'Tools', 'wem-content-types' ); ?></h1>
			<p><?php esc_html_e( 'Move WEM content structure between WordPress sites without moving posts, terms, media, or custom-field data.', 'wem-content-types' ); ?></p>
		</div>
	</div>

	<div class="wem-ct-tools-grid">
		<div class="wem-ct-card wem-ct-tool-card">
			<h2><?php esc_html_e( 'Export Structure', 'wem-content-types' ); ?></h2>
			<p><?php esc_html_e( 'Download one portable JSON file containing all WEM Post Type and Taxonomy definitions.', 'wem-content-types' ); ?></p>

			<div class="wem-ct-tool-stats">
				<span><strong><?php echo esc_html( (string) $post_type_count ); ?></strong> <?php esc_html_e( 'Post Types', 'wem-content-types' ); ?></span>
				<span><strong><?php echo esc_html( (string) $taxonomy_count ); ?></strong> <?php esc_html_e( 'Taxonomies', 'wem-content-types' ); ?></span>
			</div>

			<ul class="wem-ct-tool-list">
				<li><?php esc_html_e( 'Includes labels, status, Supports, visibility, Rewrite settings, and Taxonomy relationships.', 'wem-content-types' ); ?></li>
				<li><?php esc_html_e( 'Does not include posts, terms, media files, post meta, or custom-field values.', 'wem-content-types' ); ?></li>
			</ul>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="wem_ct_export_config">
				<?php wp_nonce_field( 'wem_ct_export_config' ); ?>
				<button type="submit" class="button button-primary button-large" <?php disabled( 0 === $post_type_count && 0 === $taxonomy_count ); ?>><?php esc_html_e( 'Download JSON', 'wem-content-types' ); ?></button>
			</form>
		</div>

		<div class="wem-ct-card wem-ct-tool-card">
			<h2><?php esc_html_e( 'Import Structure', 'wem-content-types' ); ?></h2>
			<p><?php esc_html_e( 'Upload a JSON file previously exported by WEM Content Types.', 'wem-content-types' ); ?></p>

			<div class="wem-ct-info wem-ct-tool-info">
				<strong><?php esc_html_e( 'Safe merge:', 'wem-content-types' ); ?></strong>
				<?php esc_html_e( 'new slugs are added, matching WEM slugs are updated, and unrelated existing WEM definitions are kept.', 'wem-content-types' ); ?>
			</div>

			<ul class="wem-ct-tool-list">
				<li><?php esc_html_e( 'The entire file is validated before any configuration is written.', 'wem-content-types' ); ?></li>
				<li><?php esc_html_e( 'Conflicts with WordPress or another plugin are rejected instead of silently overwritten.', 'wem-content-types' ); ?></li>
				<li><?php esc_html_e( 'Import never deletes posts, terms, media, or unrelated WEM definitions.', 'wem-content-types' ); ?></li>
			</ul>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="wem-ct-import-form">
				<input type="hidden" name="action" value="wem_ct_import_config">
				<?php wp_nonce_field( 'wem_ct_import_config' ); ?>
				<label for="wem-ct-import-file"><strong><?php esc_html_e( 'WEM JSON File', 'wem-content-types' ); ?></strong></label>
				<input id="wem-ct-import-file" type="file" name="wem_ct_import_file" accept=".json,application/json" required>
				<p class="description"><?php esc_html_e( 'Maximum file size: 1 MB.', 'wem-content-types' ); ?></p>
				<button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Import JSON', 'wem-content-types' ); ?></button>
			</form>
		</div>
	</div>
</div>
