<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_edit = ! empty( $slug );
?>
<div class="wrap wem-ct-wrap">
	<div class="wem-ct-header">
		<div>
			<h1><?php echo esc_html( $is_edit ? __( '编辑分类法', 'wem-content-types' ) : __( '新建分类法', 'wem-content-types' ) ); ?></h1>
			<p><?php esc_html_e( '分类法用于组织相关内容。内部标识应保持稳定，并只关联真正需要该分类法的内容类型。', 'wem-content-types' ); ?></p>
		</div>
		<a class="page-title-action" href="<?php echo esc_url( add_query_arg( [ 'page' => 'wem-content-types-taxonomies' ], admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( '返回列表', 'wem-content-types' ); ?></a>
	</div>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wem-ct-form">
		<input type="hidden" name="action" value="wem_ct_save_taxonomy">
		<input type="hidden" name="editing_slug" value="<?php echo esc_attr( $is_edit ? $slug : '' ); ?>">
		<?php wp_nonce_field( 'wem_ct_save_taxonomy' ); ?>

		<div class="wem-ct-card">
			<h2><?php esc_html_e( '基本设置', 'wem-content-types' ); ?></h2>
			<div class="wem-ct-grid">
				<div class="wem-ct-field">
					<label for="wem-ct-tax-slug"><?php esc_html_e( '分类法标识（Slug）', 'wem-content-types' ); ?></label>
					<input id="wem-ct-tax-slug" name="wem_ct[slug]" type="text" maxlength="32" value="<?php echo esc_attr( $config['slug'] ); ?>" <?php echo $is_edit ? 'readonly' : ''; ?> required pattern="[a-z0-9_-]+">
					<p><?php esc_html_e( '内部使用的小写标识，最多 32 个字符；创建后将锁定，不能修改。', 'wem-content-types' ); ?></p>
					<p class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <code>product_category</code>, <code>industry</code>, <code>region</code>, <code>application</code>.</p>
				</div>

				<div class="wem-ct-field">
					<label for="wem-ct-tax-singular"><?php esc_html_e( '单数名称', 'wem-content-types' ); ?></label>
					<input id="wem-ct-tax-singular" name="wem_ct[singular_label]" type="text" value="<?php echo esc_attr( $config['singular_label'] ); ?>" required placeholder="Product Category">
					<p><?php esc_html_e( 'WordPress 表示单个分类项时使用的名称。', 'wem-content-types' ); ?></p>
					<p class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <code>Product Category</code></p>
				</div>

				<div class="wem-ct-field">
					<label for="wem-ct-tax-plural"><?php esc_html_e( '复数名称', 'wem-content-types' ); ?></label>
					<input id="wem-ct-tax-plural" name="wem_ct[plural_label]" type="text" value="<?php echo esc_attr( $config['plural_label'] ); ?>" required placeholder="Product Categories">
					<p><?php esc_html_e( '分类法菜单及分类项列表中使用的名称。', 'wem-content-types' ); ?></p>
					<p class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <code>Product Categories</code></p>
				</div>
			</div>

			<div class="wem-ct-info">
				<strong><?php esc_html_e( '系统会自动生成常用名称。', 'wem-content-types' ); ?></strong>
				<?php esc_html_e( 'WEM 会根据上面的单数名称和复数名称，自动生成“新建产品分类”“编辑产品分类”“搜索产品分类”“所有产品分类”等常用后台文字。', 'wem-content-types' ); ?>
			</div>

			<div class="wem-ct-field wem-ct-field-full">
				<label for="wem-ct-tax-description"><?php esc_html_e( '说明', 'wem-content-types' ); ?></label>
				<textarea id="wem-ct-tax-description" name="wem_ct[description]" rows="3"><?php echo esc_textarea( $config['description'] ); ?></textarea>
				<p><?php esc_html_e( '可选，用于说明该分类法主要用于分类哪些内容。', 'wem-content-types' ); ?></p>
				<p class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <?php esc_html_e( '用于组织产品的主要分类。', 'wem-content-types' ); ?></p>
			</div>
		</div>

		<div class="wem-ct-card wem-ct-status-card">
			<h2><?php esc_html_e( '状态', 'wem-content-types' ); ?></h2>
			<div class="wem-ct-checks wem-ct-checks-two">
				<label>
					<input type="checkbox" name="wem_ct[enabled]" value="1" <?php checked( ! empty( $config['enabled'] ) ); ?>>
					<span>
						<strong><?php esc_html_e( '已启用', 'wem-content-types' ); ?></strong>
						<small><?php esc_html_e( '在 WordPress 中注册此分类法。关闭后只会暂时停用，不会删除配置、分类项或关联关系。', 'wem-content-types' ); ?></small>
						<small class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <?php esc_html_e( '例如网站结构调整期间暂时禁用未使用的 Region 分类法，之后可再次恢复。', 'wem-content-types' ); ?></small>
					</span>
				</label>
			</div>
		</div>

		<div class="wem-ct-card">
			<h2><?php esc_html_e( '关联到内容类型', 'wem-content-types' ); ?></h2>
			<p class="wem-ct-section-help"><?php esc_html_e( '选择一个或多个需要使用此分类法的内容类型。WEM 管理的内容类型优先显示，WordPress 内部内容类型会隐藏。', 'wem-content-types' ); ?></p>
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
			<p class="wem-ct-footnote"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <?php esc_html_e( 'Product Category 通常只关联 Product；如果符合实际信息结构，Industry 或 Region 后续也可以由多个内容类型共享。', 'wem-content-types' ); ?></p>
		</div>

		<div class="wem-ct-card">
			<h2><?php esc_html_e( '基本行为', 'wem-content-types' ); ?></h2>
			<div class="wem-ct-checks wem-ct-checks-four">
				<label>
					<input type="checkbox" name="wem_ct[public]" value="1" <?php checked( ! empty( $config['public'] ) ); ?>>
					<span>
						<strong><?php esc_html_e( '公开', 'wem-content-types' ); ?></strong>
						<small><?php esc_html_e( '允许正常访问前台分类归档页和查询。', 'wem-content-types' ); ?></small>
						<small class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <?php esc_html_e( 'Product Category、Industry、Region 等面向访客的分类通常保持开启。', 'wem-content-types' ); ?></small>
					</span>
				</label>

				<label>
					<input type="checkbox" name="wem_ct[hierarchical]" value="1" <?php checked( ! empty( $config['hierarchical'] ) ); ?>>
					<span>
						<strong><?php esc_html_e( '层级式', 'wem-content-types' ); ?></strong>
						<small><?php esc_html_e( '使用类似分类目录的父级/子级层级，而不是扁平标签。', 'wem-content-types' ); ?></small>
						<small class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <?php esc_html_e( 'Product Category 通常开启；简单 Tags 等扁平属性通常关闭。', 'wem-content-types' ); ?></small>
					</span>
				</label>

				<label>
					<input type="checkbox" name="wem_ct[show_admin_column]" value="1" <?php checked( ! empty( $config['show_admin_column'] ) ); ?>>
					<span>
						<strong><?php esc_html_e( '后台列表列', 'wem-content-types' ); ?></strong>
						<small><?php esc_html_e( '在关联内容类型的后台列表中显示已分配的分类项。', 'wem-content-types' ); ?></small>
						<small class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <?php esc_html_e( '方便在 Products 列表中快速查看每个产品所属分类。', 'wem-content-types' ); ?></small>
					</span>
				</label>

				<label>
					<input type="checkbox" name="wem_ct[show_in_rest]" value="1" <?php checked( ! empty( $config['show_in_rest'] ) ); ?>>
					<span>
						<strong><?php esc_html_e( 'REST API / 区块编辑器', 'wem-content-types' ); ?></strong>
						<small><?php esc_html_e( '建议为 Gutenberg 以及后续 API、字段和 AI 集成开启。', 'wem-content-types' ); ?></small>
						<small class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <?php esc_html_e( '现代 WordPress 网站建议保持开启。', 'wem-content-types' ); ?></small>
					</span>
				</label>
			</div>
		</div>

		<details class="wem-ct-card wem-ct-advanced">
			<summary>
				<span><?php esc_html_e( '高级设置', 'wem-content-types' ); ?></span>
				<small><?php esc_html_e( '除非分类法有特殊的可见性需求，否则通常保持默认值。', 'wem-content-types' ); ?></small>
			</summary>
			<div class="wem-ct-advanced-body">
				<div class="wem-ct-checks wem-ct-checks-two">
					<label>
						<input type="checkbox" name="wem_ct[publicly_queryable]" value="1" <?php checked( ! empty( $config['publicly_queryable'] ) ); ?>>
						<span><strong><?php esc_html_e( '允许前台查询', 'wem-content-types' ); ?></strong><small><?php esc_html_e( '允许访客和前台请求查询此分类法。', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( 'Product Category、Industry 和 Region 等通常保持开启。', 'wem-content-types' ); ?></small></span>
					</label>
					<label>
						<input type="checkbox" name="wem_ct[show_ui]" value="1" <?php checked( ! empty( $config['show_ui'] ) ); ?>>
						<span><strong><?php esc_html_e( '显示后台管理界面', 'wem-content-types' ); ?></strong><small><?php esc_html_e( '在 WordPress 后台显示分类项管理页面。', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( '需要后台管理分类项时请保持开启。', 'wem-content-types' ); ?></small></span>
					</label>
					<label>
						<input type="checkbox" name="wem_ct[show_in_menu]" value="1" <?php checked( ! empty( $config['show_in_menu'] ) ); ?>>
						<span><strong><?php esc_html_e( '显示在后台菜单', 'wem-content-types' ); ?></strong><small><?php esc_html_e( '在所关联内容类型的后台菜单下显示分类法管理链接。', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( '通常与“显示后台管理界面”一起开启。', 'wem-content-types' ); ?></small></span>
					</label>
					<label>
						<input type="checkbox" name="wem_ct[show_in_nav_menus]" value="1" <?php checked( ! empty( $config['show_in_nav_menus'] ) ); ?>>
						<span><strong><?php esc_html_e( '允许加入导航菜单', 'wem-content-types' ); ?></strong><small><?php esc_html_e( '允许将分类项添加到 WordPress 导航菜单。', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( '当分类或区域归档链接需要放入网站导航时很有用。', 'wem-content-types' ); ?></small></span>
					</label>
				</div>

				<div class="wem-ct-subsection">
					<h3><?php esc_html_e( '固定链接重写（Rewrite）', 'wem-content-types' ); ?></h3>
					<p><?php esc_html_e( '在不修改内部分类法标识的情况下控制前台分类 URL 基础路径。', 'wem-content-types' ); ?></p>

					<div class="wem-ct-checks wem-ct-checks-two">
						<label>
							<input type="checkbox" name="wem_ct[rewrite_enabled]" value="1" <?php checked( ! empty( $config['rewrite_enabled'] ) ); ?>>
							<span><strong><?php esc_html_e( '启用 URL 重写', 'wem-content-types' ); ?></strong><small><?php esc_html_e( '为分类归档页生成正常的友好固定链接规则。', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( '公开的 Product Category、Industry、Region 通常保持开启。', 'wem-content-types' ); ?></small></span>
						</label>
						<label>
							<input type="checkbox" name="wem_ct[rewrite_with_front]" value="1" <?php checked( ! empty( $config['rewrite_with_front'] ) ); ?>>
							<span><strong><?php esc_html_e( '使用固定链接前缀', 'wem-content-types' ); ?></strong><small><?php esc_html_e( '包含网站固定链接结构中的 front 前缀。', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( '希望分类法使用独立 URL 时通常保持关闭。', 'wem-content-types' ); ?></small></span>
						</label>
					</div>

					<div class="wem-ct-field wem-ct-rewrite-field">
						<label for="wem-ct-tax-rewrite-slug"><?php esc_html_e( 'Rewrite 标识', 'wem-content-types' ); ?></label>
						<input id="wem-ct-tax-rewrite-slug" name="wem_ct[rewrite_slug]" type="text" value="<?php echo esc_attr( $config['rewrite_slug'] ); ?>" placeholder="product-category">
						<p><?php esc_html_e( '可选的 URL 基础路径；留空时使用内部分类法标识。', 'wem-content-types' ); ?></p>
						<p class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <?php esc_html_e( '内部标识', 'wem-content-types' ); ?> <code>product_category</code> → <?php esc_html_e( 'Rewrite 标识', 'wem-content-types' ); ?> <code>product-category</code>.</p>
					</div>
				</div>
			</div>
		</details>

		<p class="submit">
			<button type="submit" class="button button-primary button-large"><?php echo esc_html( $is_edit ? __( '保存修改', 'wem-content-types' ) : __( '创建分类法', 'wem-content-types' ) ); ?></button>
		</p>
	</form>
</div>
