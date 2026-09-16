<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wem-ct-wrap">
	<div class="wem-ct-header">
		<div>
			<h1><?php esc_html_e( '工具', 'wem-content-types' ); ?></h1>
			<p><?php esc_html_e( '在不同 WordPress 网站之间迁移 WEM 内容结构，不迁移文章、分类项、媒体文件或自定义字段数据。', 'wem-content-types' ); ?></p>
		</div>
	</div>

	<div class="wem-ct-tools-grid">
		<div class="wem-ct-card wem-ct-tool-card">
			<h2><?php esc_html_e( '导出结构', 'wem-content-types' ); ?></h2>
			<p><?php esc_html_e( '下载一个可迁移的 JSON 文件，其中包含全部 WEM 内容类型和分类法定义。', 'wem-content-types' ); ?></p>

			<div class="wem-ct-tool-stats">
				<span><strong><?php echo esc_html( (string) $post_type_count ); ?></strong> <?php esc_html_e( '内容类型', 'wem-content-types' ); ?></span>
				<span><strong><?php echo esc_html( (string) $taxonomy_count ); ?></strong> <?php esc_html_e( '分类法', 'wem-content-types' ); ?></span>
			</div>

			<ul class="wem-ct-tool-list">
				<li><?php esc_html_e( '包含名称、状态、Supports、可见性、Rewrite 设置以及分类法关联关系。', 'wem-content-types' ); ?></li>
				<li><?php esc_html_e( '不包含文章、分类项、媒体文件、Post Meta 或自定义字段值。', 'wem-content-types' ); ?></li>
			</ul>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="wem_ct_export_config">
				<?php wp_nonce_field( 'wem_ct_export_config' ); ?>
				<button type="submit" class="button button-primary button-large" <?php disabled( 0 === $post_type_count && 0 === $taxonomy_count ); ?>><?php esc_html_e( '下载 JSON', 'wem-content-types' ); ?></button>
			</form>
		</div>

		<div class="wem-ct-card wem-ct-tool-card">
			<h2><?php esc_html_e( '导入结构', 'wem-content-types' ); ?></h2>
			<p><?php esc_html_e( '上传之前由 WEM Content Types 导出的 JSON 文件。', 'wem-content-types' ); ?></p>

			<div class="wem-ct-info wem-ct-tool-info">
				<strong><?php esc_html_e( '安全合并：', 'wem-content-types' ); ?></strong>
				<?php esc_html_e( '新的标识会被添加，相同的 WEM 标识会被更新，不相关的现有 WEM 定义会保留。', 'wem-content-types' ); ?>
			</div>

			<ul class="wem-ct-tool-list">
				<li><?php esc_html_e( '写入任何配置前，会先完整验证整个文件。', 'wem-content-types' ); ?></li>
				<li><?php esc_html_e( '如果与 WordPress 或其他插件发生冲突，将拒绝导入，不会静默覆盖。', 'wem-content-types' ); ?></li>
				<li><?php esc_html_e( '导入操作不会删除文章、分类项、媒体文件或不相关的 WEM 定义。', 'wem-content-types' ); ?></li>
			</ul>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="wem-ct-import-form">
				<input type="hidden" name="action" value="wem_ct_import_config">
				<?php wp_nonce_field( 'wem_ct_import_config' ); ?>
				<label for="wem-ct-import-file"><strong><?php esc_html_e( 'WEM JSON 文件', 'wem-content-types' ); ?></strong></label>
				<input id="wem-ct-import-file" type="file" name="wem_ct_import_file" accept=".json,application/json" required>
				<p class="description"><?php esc_html_e( '最大文件大小：1 MB。', 'wem-content-types' ); ?></p>
				<button type="submit" class="button button-primary button-large"><?php esc_html_e( '导入 JSON', 'wem-content-types' ); ?></button>
			</form>
		</div>
	</div>
</div>
