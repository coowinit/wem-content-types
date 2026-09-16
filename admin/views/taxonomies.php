<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wem-ct-wrap">
	<div class="wem-ct-header">
		<div>
			<h1><?php esc_html_e( '分类法', 'wem-content-types' ); ?></h1>
			<p><?php esc_html_e( '创建可复用的分类结构，并将其关联到一个或多个内容类型。', 'wem-content-types' ); ?></p>
		</div>
		<a class="page-title-action" href="<?php echo esc_url( add_query_arg( [ 'page' => 'wem-content-types-taxonomies', 'action' => 'add' ], admin_url( 'admin.php' ) ) ); ?>">
			<?php esc_html_e( '新建分类法', 'wem-content-types' ); ?>
		</a>
	</div>

	<div class="wem-ct-card wem-ct-card-table">
		<table class="widefat fixed striped wem-ct-table">
			<thead>
			<tr>
				<th><?php esc_html_e( '名称', 'wem-content-types' ); ?></th>
				<th><?php esc_html_e( '标识（Slug）', 'wem-content-types' ); ?></th>
				<th><?php esc_html_e( '状态', 'wem-content-types' ); ?></th>
				<th><?php esc_html_e( '关联内容类型', 'wem-content-types' ); ?></th>
				<th><?php esc_html_e( '类型', 'wem-content-types' ); ?></th>
				<th><?php esc_html_e( 'REST', 'wem-content-types' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ( empty( $items ) ) : ?>
				<tr>
					<td colspan="6" class="wem-ct-empty">
						<strong><?php esc_html_e( '还没有自定义分类法。', 'wem-content-types' ); ?></strong>
						<p><?php esc_html_e( '可以创建 Product Category、Industry、Region、Application 等分类法。', 'wem-content-types' ); ?></p>
					</td>
				</tr>
			<?php else : ?>
				<?php foreach ( $items as $slug => $item ) : ?>
					<?php
					$edit_url = add_query_arg(
						[ 'page' => 'wem-content-types-taxonomies', 'action' => 'edit', 'taxonomy' => $slug ],
						admin_url( 'admin.php' )
					);
					$delete_url = wp_nonce_url(
						add_query_arg( [ 'action' => 'wem_ct_delete_taxonomy', 'taxonomy' => $slug ], admin_url( 'admin-post.php' ) ),
						'wem_ct_delete_taxonomy_' . $slug
					);
					$enabled = ! array_key_exists( 'enabled', $item ) || ! empty( $item['enabled'] );
					$toggle_url = wp_nonce_url(
						add_query_arg( [ 'action' => 'wem_ct_toggle_taxonomy', 'taxonomy' => $slug ], admin_url( 'admin-post.php' ) ),
						'wem_ct_toggle_taxonomy_' . $slug
					);
					$object_types = isset( $item['object_types'] ) && is_array( $item['object_types'] ) ? $item['object_types'] : [];
					?>
					<tr>
						<td>
							<strong><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $item['plural_label'] ?? $slug ); ?></a></strong>
							<div class="row-actions">
								<span class="edit"><a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( '编辑', 'wem-content-types' ); ?></a> | </span>
								<span class="wem-toggle"><a href="<?php echo esc_url( $toggle_url ); ?>"><?php echo esc_html( $enabled ? __( '禁用', 'wem-content-types' ) : __( '启用', 'wem-content-types' ) ); ?></a> | </span>
								<span class="trash"><a href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php echo esc_js( __( '确定删除此分类法配置吗？已有分类项及其关联关系会继续保留。', 'wem-content-types' ) ); ?>');"><?php esc_html_e( '删除', 'wem-content-types' ); ?></a></span>
							</div>
						</td>
						<td><code><?php echo esc_html( $slug ); ?></code></td>
						<td><span class="wem-ct-status <?php echo $enabled ? 'is-enabled' : 'is-disabled'; ?>"><?php echo esc_html( $enabled ? __( '已启用', 'wem-content-types' ) : __( '已禁用', 'wem-content-types' ) ); ?></span></td>
						<td>
							<?php if ( $object_types ) : ?>
								<?php foreach ( $object_types as $object_type ) : ?>
									<code class="wem-ct-inline-code"><?php echo esc_html( $object_type ); ?></code>
								<?php endforeach; ?>
							<?php else : ?>
								—
							<?php endif; ?>
						</td>
						<td><?php echo ! empty( $item['hierarchical'] ) ? esc_html__( '层级式', 'wem-content-types' ) : esc_html__( '标签式', 'wem-content-types' ); ?></td>
						<td><?php echo ! empty( $item['show_in_rest'] ) ? esc_html__( '是', 'wem-content-types' ) : esc_html__( '否', 'wem-content-types' ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
