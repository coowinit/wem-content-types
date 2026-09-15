<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wem-ct-wrap">
	<div class="wem-ct-header">
		<div>
			<h1><?php esc_html_e( 'WEM Content Types', 'wem-content-types' ); ?></h1>
			<p><?php esc_html_e( 'Create and manage lightweight WordPress custom post types.', 'wem-content-types' ); ?></p>
		</div>
		<a class="page-title-action" href="<?php echo esc_url( add_query_arg( [ 'page' => 'wem-content-types', 'action' => 'add' ], admin_url( 'admin.php' ) ) ); ?>">
			<?php esc_html_e( 'Add Post Type', 'wem-content-types' ); ?>
		</a>
	</div>

	<div class="wem-ct-card">
		<table class="widefat fixed striped wem-ct-table">
			<thead>
			<tr>
				<th><?php esc_html_e( 'Name', 'wem-content-types' ); ?></th>
				<th><?php esc_html_e( 'Slug', 'wem-content-types' ); ?></th>
				<th><?php esc_html_e( 'Status', 'wem-content-types' ); ?></th>
				<th><?php esc_html_e( 'Public', 'wem-content-types' ); ?></th>
				<th><?php esc_html_e( 'Archive', 'wem-content-types' ); ?></th>
				<th><?php esc_html_e( 'REST', 'wem-content-types' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ( empty( $items ) ) : ?>
				<tr>
					<td colspan="6" class="wem-ct-empty">
						<strong><?php esc_html_e( 'No custom post types yet.', 'wem-content-types' ); ?></strong>
						<p><?php esc_html_e( 'Create the first content type and WEM will register it on the next WordPress init cycle.', 'wem-content-types' ); ?></p>
					</td>
				</tr>
			<?php else : ?>
				<?php foreach ( $items as $slug => $item ) : ?>
					<?php
					$edit_url = add_query_arg( [ 'page' => 'wem-content-types', 'action' => 'edit', 'post_type' => $slug ], admin_url( 'admin.php' ) );
					$delete_url = wp_nonce_url(
						add_query_arg( [ 'action' => 'wem_ct_delete_post_type', 'post_type' => $slug ], admin_url( 'admin-post.php' ) ),
						'wem_ct_delete_' . $slug
					);
					$enabled = ! array_key_exists( 'enabled', $item ) || ! empty( $item['enabled'] );
					$toggle_url = wp_nonce_url(
						add_query_arg( [ 'action' => 'wem_ct_toggle_post_type', 'post_type' => $slug ], admin_url( 'admin-post.php' ) ),
						'wem_ct_toggle_' . $slug
					);
					?>
					<tr>
						<td>
							<strong><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $item['plural_label'] ?? $slug ); ?></a></strong>
							<div class="row-actions">
								<span class="edit"><a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'wem-content-types' ); ?></a> | </span>
								<span class="wem-toggle"><a href="<?php echo esc_url( $toggle_url ); ?>"><?php echo esc_html( $enabled ? __( 'Disable', 'wem-content-types' ) : __( 'Enable', 'wem-content-types' ) ); ?></a> | </span>
								<span class="trash"><a href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this post type configuration? Existing posts will remain in the database. If a WEM taxonomy still uses this post type, deletion will be blocked until you detach it.', 'wem-content-types' ) ); ?>');"><?php esc_html_e( 'Delete', 'wem-content-types' ); ?></a></span>
							</div>
						</td>
						<td><code><?php echo esc_html( $slug ); ?></code></td>
						<td><span class="wem-ct-status <?php echo $enabled ? 'is-enabled' : 'is-disabled'; ?>"><?php echo esc_html( $enabled ? __( 'Enabled', 'wem-content-types' ) : __( 'Disabled', 'wem-content-types' ) ); ?></span></td>
						<td><?php echo ! empty( $item['public'] ) ? esc_html__( 'Yes', 'wem-content-types' ) : esc_html__( 'No', 'wem-content-types' ); ?></td>
						<td><?php echo ! empty( $item['has_archive'] ) ? esc_html__( 'Yes', 'wem-content-types' ) : esc_html__( 'No', 'wem-content-types' ); ?></td>
						<td><?php echo ! empty( $item['show_in_rest'] ) ? esc_html__( 'Yes', 'wem-content-types' ) : esc_html__( 'No', 'wem-content-types' ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
