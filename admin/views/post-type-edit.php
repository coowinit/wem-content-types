<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_edit = ! empty( $slug );
$all_supports = [
	'title' => [
		'label'   => __( '标题', 'wem-content-types' ),
		'example' => __( '示例：产品名称。', 'wem-content-types' ),
	],
	'editor' => [
		'label'   => __( '编辑器', 'wem-content-types' ),
		'example' => __( '示例：产品概述或长篇正文内容。', 'wem-content-types' ),
	],
	'thumbnail' => [
		'label'   => __( '特色图片', 'wem-content-types' ),
		'example' => __( '示例：产品主图。', 'wem-content-types' ),
	],
	'excerpt' => [
		'label'   => __( '摘要', 'wem-content-types' ),
		'example' => __( '示例：用于卡片或列表中的产品简短摘要。', 'wem-content-types' ),
	],
	'revisions' => [
		'label'   => __( '修订版本', 'wem-content-types' ),
		'example' => __( '示例：内容更新时保留历史版本。', 'wem-content-types' ),
	],
	'author' => [
		'label'   => __( '作者', 'wem-content-types' ),
		'example' => __( '示例：记录由哪个 WordPress 用户创建该内容。', 'wem-content-types' ),
	],
	'comments' => [
		'label'   => __( '评论', 'wem-content-types' ),
		'example' => __( '示例：允许访客评论；产品内容通常不需要开启。', 'wem-content-types' ),
	],
	'page-attributes' => [
		'label'   => __( '页面属性', 'wem-content-types' ),
		'example' => __( '示例：需要时提供排序等页面属性。', 'wem-content-types' ),
	],
	'custom-fields' => [
		'label'   => __( '自定义字段', 'wem-content-types' ),
		'example' => __( '示例：启用 WordPress 原生自定义字段支持。通常保持关闭，直到字段模块需要时再开启。', 'wem-content-types' ),
	],
];

$icon_choices = [
	'dashicons-admin-post'    => __( '文章', 'wem-content-types' ),
	'dashicons-products'      => __( '产品', 'wem-content-types' ),
	'dashicons-portfolio'     => __( '作品集', 'wem-content-types' ),
	'dashicons-media-document'=> __( '文档', 'wem-content-types' ),
	'dashicons-format-gallery'=> __( '图库', 'wem-content-types' ),
	'dashicons-groups'        => __( '群组', 'wem-content-types' ),
	'dashicons-businessperson'=> __( '企业', 'wem-content-types' ),
	'dashicons-location'      => __( '位置', 'wem-content-types' ),
	'dashicons-category'      => __( '分类', 'wem-content-types' ),
	'dashicons-feedback'      => __( '反馈', 'wem-content-types' ),
	'dashicons-book'          => __( '书籍', 'wem-content-types' ),
	'dashicons-lightbulb'     => __( '创意', 'wem-content-types' ),
];
?>
<div class="wrap wem-ct-wrap">
	<div class="wem-ct-header">
		<div>
			<h1><?php echo esc_html( $is_edit ? __( '编辑内容类型', 'wem-content-types' ) : __( '新建内容类型', 'wem-content-types' ) ); ?></h1>
			<p><?php esc_html_e( '先定义内容结构即可；高级设置为可选项，通常保持默认值。', 'wem-content-types' ); ?></p>
		</div>
		<a class="page-title-action" href="<?php echo esc_url( add_query_arg( [ 'page' => 'wem-content-types' ], admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( '返回列表', 'wem-content-types' ); ?></a>
	</div>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wem-ct-form">
		<input type="hidden" name="action" value="wem_ct_save_post_type">
		<input type="hidden" name="editing_slug" value="<?php echo esc_attr( $is_edit ? $slug : '' ); ?>">
		<?php wp_nonce_field( 'wem_ct_save_post_type' ); ?>

		<div class="wem-ct-card">
			<h2><?php esc_html_e( '基本设置', 'wem-content-types' ); ?></h2>
			<div class="wem-ct-grid">
				<div class="wem-ct-field">
					<label for="wem-ct-slug"><?php esc_html_e( '内容类型标识（Slug）', 'wem-content-types' ); ?></label>
					<input id="wem-ct-slug" name="wem_ct[slug]" type="text" maxlength="20" value="<?php echo esc_attr( $config['slug'] ); ?>" <?php echo $is_edit ? 'readonly' : ''; ?> required pattern="[a-z0-9_-]+">
					<p><?php esc_html_e( 'WordPress 内部使用的小写标识，最多 20 个字符；创建后将锁定，不能修改。', 'wem-content-types' ); ?></p>
					<p class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <code>product</code>, <code>solution</code>, <code>case</code>, <code>faq</code>, <code>download</code>. <?php esc_html_e( '建议使用单数形式的内部标识。', 'wem-content-types' ); ?></p>
				</div>

				<div class="wem-ct-field">
					<label for="wem-ct-singular"><?php esc_html_e( '单数名称', 'wem-content-types' ); ?></label>
					<input id="wem-ct-singular" name="wem_ct[singular_label]" type="text" value="<?php echo esc_attr( $config['singular_label'] ); ?>" required placeholder="Product">
					<p><?php esc_html_e( 'WordPress 表示单个内容时使用的名称。', 'wem-content-types' ); ?></p>
					<p class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <code>Product</code></p>
				</div>

				<div class="wem-ct-field">
					<label for="wem-ct-plural"><?php esc_html_e( '复数名称', 'wem-content-types' ); ?></label>
					<input id="wem-ct-plural" name="wem_ct[plural_label]" type="text" value="<?php echo esc_attr( $config['plural_label'] ); ?>" required placeholder="Products">
					<p><?php esc_html_e( '后台菜单以及多条内容列表中使用的名称。', 'wem-content-types' ); ?></p>
					<p class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <code>Products</code></p>
				</div>

				<div class="wem-ct-field wem-ct-icon-field">
					<label for="wem-ct-icon"><?php esc_html_e( '菜单图标', 'wem-content-types' ); ?></label>
					<div class="wem-ct-icon-input-row">
						<span class="wem-ct-icon-preview" aria-hidden="true"><span class="dashicons <?php echo esc_attr( $config['menu_icon'] ); ?>"></span></span>
						<input id="wem-ct-icon" data-wem-icon-input name="wem_ct[menu_icon]" type="text" value="<?php echo esc_attr( $config['menu_icon'] ); ?>" placeholder="dashicons-products">
					</div>
					<p><?php esc_html_e( '可以从下方选择常用 Dashicon，也可以手动输入其他 Dashicons 类名。', 'wem-content-types' ); ?></p>
					<div class="wem-ct-icon-picker" role="group" aria-label="<?php esc_attr_e( '常用菜单图标', 'wem-content-types' ); ?>">
						<?php foreach ( $icon_choices as $icon_class => $icon_label ) : ?>
							<button type="button" class="wem-ct-icon-choice<?php echo $config['menu_icon'] === $icon_class ? ' is-selected' : ''; ?>" data-icon="<?php echo esc_attr( $icon_class ); ?>" aria-pressed="<?php echo $config['menu_icon'] === $icon_class ? 'true' : 'false'; ?>" title="<?php echo esc_attr( $icon_label ); ?>">
								<span class="dashicons <?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></span>
								<span class="screen-reader-text"><?php echo esc_html( $icon_label ); ?></span>
							</button>
						<?php endforeach; ?>
					</div>
					<p class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <code>dashicons-products</code></p>
				</div>
			</div>

			<div class="wem-ct-info">
				<strong><?php esc_html_e( '系统会自动生成常用名称。', 'wem-content-types' ); ?></strong>
				<?php esc_html_e( 'WEM 会根据单数名称和复数名称自动生成“新建产品”“编辑产品”“搜索产品”“所有产品”等常用后台文字；如有需要，后续仍可通过 Label Filters 单独调整。', 'wem-content-types' ); ?>
			</div>

			<div class="wem-ct-field wem-ct-field-full">
				<label for="wem-ct-description"><?php esc_html_e( '说明', 'wem-content-types' ); ?></label>
				<textarea id="wem-ct-description" name="wem_ct[description]" rows="3"><?php echo esc_textarea( $config['description'] ); ?></textarea>
				<p><?php esc_html_e( '可选，用于简要说明该内容类型存储什么内容。', 'wem-content-types' ); ?></p>
				<p class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <?php esc_html_e( '产品及其结构化产品信息。', 'wem-content-types' ); ?></p>
			</div>
		</div>

		<div class="wem-ct-card wem-ct-status-card">
			<h2><?php esc_html_e( '状态', 'wem-content-types' ); ?></h2>
			<div class="wem-ct-checks wem-ct-checks-two">
				<label>
					<input type="checkbox" name="wem_ct[enabled]" value="1" <?php checked( ! empty( $config['enabled'] ) ); ?>>
					<span>
						<strong><?php esc_html_e( '已启用', 'wem-content-types' ); ?></strong>
						<small><?php esc_html_e( '在 WordPress 中注册此内容类型。关闭后只会暂时停用，不会删除配置或已有内容。', 'wem-content-types' ); ?></small>
						<small class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <?php esc_html_e( '例如网站改版期间暂时禁用未使用的 Case 内容类型，之后可再次启用。', 'wem-content-types' ); ?></small>
					</span>
				</label>
			</div>
		</div>

		<div class="wem-ct-card">
			<h2><?php esc_html_e( '基本行为', 'wem-content-types' ); ?></h2>
			<div class="wem-ct-checks">
				<label>
					<input type="checkbox" name="wem_ct[public]" value="1" <?php checked( ! empty( $config['public'] ) ); ?>>
					<span>
						<strong><?php esc_html_e( '公开', 'wem-content-types' ); ?></strong>
						<small><?php esc_html_e( '将此内容类型作为面向访客的公开内容。', 'wem-content-types' ); ?></small>
						<small class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <?php esc_html_e( 'Product、Solution、Case 等公开内容通常保持开启。', 'wem-content-types' ); ?></small>
					</span>
				</label>
				<label>
					<input type="checkbox" name="wem_ct[has_archive]" value="1" <?php checked( ! empty( $config['has_archive'] ) ); ?>>
					<span>
						<strong><?php esc_html_e( '归档页', 'wem-content-types' ); ?></strong>
						<small><?php esc_html_e( '启用该内容类型的归档页 URL。', 'wem-content-types' ); ?></small>
						<small class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <?php esc_html_e( '需要产品列表页或归档页时开启。', 'wem-content-types' ); ?></small>
					</span>
				</label>
				<label>
					<input type="checkbox" name="wem_ct[show_in_rest]" value="1" <?php checked( ! empty( $config['show_in_rest'] ) ); ?>>
					<span>
						<strong><?php esc_html_e( 'REST API / 区块编辑器', 'wem-content-types' ); ?></strong>
						<small><?php esc_html_e( '现代 WordPress 网站建议开启，也便于后续字段和 API 集成。', 'wem-content-types' ); ?></small>
						<small class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <?php esc_html_e( '使用 Gutenberg、REST API，以及后续字段或 AI 集成时建议保持开启。', 'wem-content-types' ); ?></small>
					</span>
				</label>
			</div>
		</div>

		<div class="wem-ct-card">
			<h2><?php esc_html_e( '编辑功能（Supports）', 'wem-content-types' ); ?></h2>
			<p class="wem-ct-section-help"><?php esc_html_e( '选择此内容类型需要启用的 WordPress 标准编辑功能。', 'wem-content-types' ); ?></p>
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
				<span><?php esc_html_e( '高级设置', 'wem-content-types' ); ?></span>
				<small><?php esc_html_e( '除非内容类型有特殊的可见性需求，否则通常保持默认值。', 'wem-content-types' ); ?></small>
			</summary>
			<div class="wem-ct-advanced-body">
				<div class="wem-ct-checks wem-ct-checks-two">
					<label>
						<input type="checkbox" name="wem_ct[publicly_queryable]" value="1" <?php checked( ! empty( $config['publicly_queryable'] ) ); ?>>
						<span><strong><?php esc_html_e( '允许前台查询', 'wem-content-types' ); ?></strong><small><?php esc_html_e( '允许通过前台 URL 和查询访问单条内容。', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( '公开的 Product 或 Solution 通常保持开启。', 'wem-content-types' ); ?></small></span>
					</label>
					<label>
						<input type="checkbox" name="wem_ct[exclude_from_search]" value="1" <?php checked( ! empty( $config['exclude_from_search'] ) ); ?>>
						<span><strong><?php esc_html_e( '从搜索中排除', 'wem-content-types' ); ?></strong><small><?php esc_html_e( '从 WordPress 前台普通搜索结果中隐藏此内容类型。', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( '适用于仍有独立 URL、但不希望出现在站内搜索中的内部参考内容。', 'wem-content-types' ); ?></small></span>
					</label>
					<label>
						<input type="checkbox" name="wem_ct[show_ui]" value="1" <?php checked( ! empty( $config['show_ui'] ) ); ?>>
						<span><strong><?php esc_html_e( '显示后台管理界面', 'wem-content-types' ); ?></strong><small><?php esc_html_e( '显示此内容类型的 WordPress 后台管理页面。', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( '需要在后台编辑内容时请保持开启。', 'wem-content-types' ); ?></small></span>
					</label>
					<label>
						<input type="checkbox" name="wem_ct[show_in_menu]" value="1" <?php checked( ! empty( $config['show_in_menu'] ) ); ?>>
						<span><strong><?php esc_html_e( '显示在后台菜单', 'wem-content-types' ); ?></strong><small><?php esc_html_e( '在 WordPress 后台菜单中显示此内容类型。', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( '通常与“显示后台管理界面”一起开启。', 'wem-content-types' ); ?></small></span>
					</label>
					<label>
						<input type="checkbox" name="wem_ct[show_in_nav_menus]" value="1" <?php checked( ! empty( $config['show_in_nav_menus'] ) ); ?>>
						<span><strong><?php esc_html_e( '允许加入导航菜单', 'wem-content-types' ); ?></strong><small><?php esc_html_e( '允许将此内容类型的项目添加到 WordPress 导航菜单。', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( '当产品或解决方案需要直接从导航菜单链接时很有用。', 'wem-content-types' ); ?></small></span>
					</label>
					<label>
						<input type="checkbox" name="wem_ct[show_in_admin_bar]" value="1" <?php checked( ! empty( $config['show_in_admin_bar'] ) ); ?>>
						<span><strong><?php esc_html_e( '显示在管理工具栏', 'wem-content-types' ); ?></strong><small><?php esc_html_e( '允许从 WordPress 顶部管理工具栏快速访问。', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( '通常可以保持开启。', 'wem-content-types' ); ?></small></span>
					</label>
				</div>

				<div class="wem-ct-field wem-ct-compact-field">
					<label for="wem-ct-menu-position"><?php esc_html_e( '菜单位置', 'wem-content-types' ); ?></label>
					<input id="wem-ct-menu-position" name="wem_ct[menu_position]" type="number" min="2" max="100" step="1" value="<?php echo esc_attr( $config['menu_position'] ); ?>" placeholder="25">
					<p><?php esc_html_e( '可选。留空时由 WordPress 自动安排位置；也可以填写 2 到 100 之间的整数。', 'wem-content-types' ); ?></p>
					<p class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <code>25</code></p>
				</div>

				<div class="wem-ct-subsection">
					<h3><?php esc_html_e( '固定链接重写（Rewrite）', 'wem-content-types' ); ?></h3>
					<p><?php esc_html_e( '在不修改内部内容类型标识的情况下控制前台 URL 基础路径。', 'wem-content-types' ); ?></p>

					<div class="wem-ct-checks wem-ct-checks-two">
						<label>
							<input type="checkbox" name="wem_ct[rewrite_enabled]" value="1" <?php checked( ! empty( $config['rewrite_enabled'] ) ); ?>>
							<span><strong><?php esc_html_e( '启用 URL 重写', 'wem-content-types' ); ?></strong><small><?php esc_html_e( '为该内容类型生成正常的友好固定链接规则。', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( '面向访客的公开内容通常保持开启。', 'wem-content-types' ); ?></small></span>
						</label>
						<label>
							<input type="checkbox" name="wem_ct[rewrite_with_front]" value="1" <?php checked( ! empty( $config['rewrite_with_front'] ) ); ?>>
							<span><strong><?php esc_html_e( '使用固定链接前缀', 'wem-content-types' ); ?></strong><small><?php esc_html_e( '包含网站固定链接结构中的 front 前缀。', 'wem-content-types' ); ?></small><small class="wem-ct-example"><?php esc_html_e( '如果希望 Products 保持为 /products/，而不是继承类似 /blog/ 的前缀，通常保持关闭。', 'wem-content-types' ); ?></small></span>
						</label>
					</div>

					<div class="wem-ct-field wem-ct-rewrite-field">
						<label for="wem-ct-rewrite-slug"><?php esc_html_e( 'Rewrite 标识', 'wem-content-types' ); ?></label>
						<input id="wem-ct-rewrite-slug" name="wem_ct[rewrite_slug]" type="text" value="<?php echo esc_attr( $config['rewrite_slug'] ); ?>" placeholder="products">
						<p><?php esc_html_e( '可选的 URL 基础路径；留空时使用内部内容类型标识。', 'wem-content-types' ); ?></p>
						<p class="wem-ct-example"><strong><?php esc_html_e( '示例：', 'wem-content-types' ); ?></strong> <?php esc_html_e( '内部标识', 'wem-content-types' ); ?> <code>product</code> → <?php esc_html_e( 'Rewrite 标识', 'wem-content-types' ); ?> <code>products</code> → <code>/products/sample-product/</code></p>
					</div>
				</div>
			</div>
		</details>

		<p class="submit">
			<button type="submit" class="button button-primary button-large"><?php echo esc_html( $is_edit ? __( '保存修改', 'wem-content-types' ) : __( '创建内容类型', 'wem-content-types' ) ); ?></button>
		</p>
	</form>
</div>
