<?php
namespace WEM\Content_Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Validator {
	private Storage $storage;

	private array $reserved_post_types = [
		'post', 'page', 'attachment', 'revision', 'nav_menu_item',
		'custom_css', 'customize_changeset', 'oembed_cache', 'user_request',
		'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles',
		'wp_navigation', 'wp_font_family', 'wp_font_face',
	];

	/**
	 * Common WordPress public query variables and built-in taxonomy names.
	 * Avoiding these reduces hard-to-debug rewrite/query collisions.
	 */
	private array $reserved_taxonomies = [
		'attachment', 'attachment_id', 'author', 'author_name', 'calendar', 'cat',
		'category', 'category_name', 'comments_per_page', 'comments_popup', 'cpage',
		'day', 'debug', 'error', 'exact', 'feed', 'fields', 'hour', 'link_category',
		'm', 'minute', 'monthnum', 'more', 'name', 'nav_menu', 'nonce', 'nopaging',
		'offset', 'order', 'orderby', 'p', 'page', 'page_id', 'paged', 'pagename',
		'pb', 'perm', 'post', 'post_format', 'post_mime_type', 'post_status',
		'post_tag', 'post_type', 'posts', 'posts_per_page', 'preview', 'robots', 's',
		'search', 'second', 'sentence', 'showposts', 'static', 'subpost', 'tag',
		'tag_id', 'taxonomy', 'tb', 'term', 'terms', 'theme', 'title', 'type',
		'w', 'withcomments', 'withoutcomments', 'year',
	];

	private array $support_whitelist = [
		'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author',
		'comments', 'page-attributes', 'custom-fields',
	];

	public function __construct( Storage $storage ) {
		$this->storage = $storage;
	}

	/**
	 * Validate a post type configuration.
	 *
	 * validate() is kept as a backward-compatible alias for post type validation.
	 */
	public function validate( array $input, ?string $editing_slug = null ) {
		return $this->validate_post_type( $input, $editing_slug );
	}

	public function validate_post_type( array $input, ?string $editing_slug = null ) {
		$errors   = new \WP_Error();
		$raw_slug = trim( (string) ( $input['slug'] ?? '' ) );
		$slug     = sanitize_key( $raw_slug );

		if ( '' !== $raw_slug && $raw_slug !== $slug ) {
			$errors->add( 'slug_format', __( '内容类型标识只能包含小写英文字母、数字、下划线和连字符。', 'wem-content-types' ) );
		}

		if ( '' === $slug ) {
			$errors->add( 'slug_required', __( '请填写内容类型标识。', 'wem-content-types' ) );
		} elseif ( strlen( $slug ) > 20 ) {
			$errors->add( 'slug_length', __( '内容类型标识不能超过 20 个字符。', 'wem-content-types' ) );
		} elseif ( in_array( $slug, $this->reserved_post_types, true ) ) {
			$errors->add( 'slug_reserved', __( '该标识已被 WordPress 保留，请使用其他标识。', 'wem-content-types' ) );
		}

		if ( $editing_slug && $slug !== $editing_slug ) {
			$errors->add( 'slug_immutable', __( '内容类型创建后，内部标识不可修改。', 'wem-content-types' ) );
		}

		if ( ! $editing_slug && $this->storage->exists( $slug ) ) {
			$errors->add( 'slug_exists', __( '已存在使用该标识的 WEM 内容类型。', 'wem-content-types' ) );
		}

		if ( ! $editing_slug && $slug && post_type_exists( $slug ) ) {
			$errors->add( 'slug_conflict', __( '已有 WordPress 内容类型使用该标识。', 'wem-content-types' ) );
		}

		$singular = sanitize_text_field( $input['singular_label'] ?? '' );
		$plural   = sanitize_text_field( $input['plural_label'] ?? '' );

		if ( '' === $singular ) {
			$errors->add( 'singular_required', __( '请填写单数名称。', 'wem-content-types' ) );
		}
		if ( '' === $plural ) {
			$errors->add( 'plural_required', __( '请填写复数名称。', 'wem-content-types' ) );
		}

		$menu_icon = sanitize_html_class( $input['menu_icon'] ?? 'dashicons-admin-post' );
		if ( '' === $menu_icon ) {
			$menu_icon = 'dashicons-admin-post';
		}
		if ( 0 !== strpos( $menu_icon, 'dashicons-' ) ) {
			$errors->add( 'menu_icon_invalid', __( '菜单图标必须使用 Dashicons 类名，例如 dashicons-products。', 'wem-content-types' ) );
		}

		$menu_position_raw = trim( (string) ( $input['menu_position'] ?? '' ) );
		$menu_position     = null;
		if ( '' !== $menu_position_raw ) {
			if ( ! ctype_digit( $menu_position_raw ) || (int) $menu_position_raw < 2 || (int) $menu_position_raw > 100 ) {
				$errors->add( 'menu_position_invalid', __( '菜单位置必须是 2 到 100 之间的整数，也可以留空。', 'wem-content-types' ) );
			} else {
				$menu_position = (int) $menu_position_raw;
			}
		}

		$rewrite_slug = $this->validate_rewrite_slug( $input['rewrite_slug'] ?? '', $slug, $errors );

		if ( $errors->has_errors() ) {
			return $errors;
		}

		$supports = isset( $input['supports'] ) && is_array( $input['supports'] )
			? array_values( array_intersect( array_map( 'sanitize_key', $input['supports'] ), $this->support_whitelist ) )
			: [];

		return [
			'slug'                => $slug,
			'enabled'             => ! empty( $input['enabled'] ),
			'singular_label'      => $singular,
			'plural_label'        => $plural,
			'description'         => sanitize_textarea_field( $input['description'] ?? '' ),
			'menu_icon'           => $menu_icon,
			'public'              => ! empty( $input['public'] ),
			'has_archive'         => ! empty( $input['has_archive'] ),
			'show_in_rest'        => ! empty( $input['show_in_rest'] ),
			'supports'            => $supports ?: [ 'title' ],
			'publicly_queryable'  => ! empty( $input['publicly_queryable'] ),
			'exclude_from_search' => ! empty( $input['exclude_from_search'] ),
			'show_ui'             => ! empty( $input['show_ui'] ),
			'show_in_menu'        => ! empty( $input['show_in_menu'] ),
			'show_in_nav_menus'   => ! empty( $input['show_in_nav_menus'] ),
			'show_in_admin_bar'   => ! empty( $input['show_in_admin_bar'] ),
			'menu_position'       => $menu_position,
			'rewrite_enabled'     => ! empty( $input['rewrite_enabled'] ),
			'rewrite_slug'        => $rewrite_slug,
			'rewrite_with_front'  => ! empty( $input['rewrite_with_front'] ),
		];
	}

	/**
	 * Validate a taxonomy configuration.
	 */
	public function validate_taxonomy( array $input, ?string $editing_slug = null, array $additional_known_post_types = [] ) {
		$errors   = new \WP_Error();
		$raw_slug = trim( (string) ( $input['slug'] ?? '' ) );
		$slug     = sanitize_key( $raw_slug );

		if ( '' !== $raw_slug && $raw_slug !== $slug ) {
			$errors->add( 'slug_format', __( '分类法标识只能包含小写英文字母、数字、下划线和连字符。', 'wem-content-types' ) );
		}

		if ( '' === $slug ) {
			$errors->add( 'slug_required', __( '请填写分类法标识。', 'wem-content-types' ) );
		} elseif ( strlen( $slug ) > 32 ) {
			$errors->add( 'slug_length', __( '分类法标识不能超过 32 个字符。', 'wem-content-types' ) );
		} elseif ( in_array( $slug, $this->reserved_taxonomies, true ) ) {
			$errors->add( 'slug_reserved', __( '该分类法标识已被保留，或可能与 WordPress 查询变量冲突，请使用其他标识。', 'wem-content-types' ) );
		}

		if ( $editing_slug && $slug !== $editing_slug ) {
			$errors->add( 'slug_immutable', __( '分类法创建后，内部标识不可修改。', 'wem-content-types' ) );
		}

		if ( ! $editing_slug && $this->storage->taxonomy_exists( $slug ) ) {
			$errors->add( 'slug_exists', __( '已存在使用该标识的 WEM 分类法。', 'wem-content-types' ) );
		}

		if ( ! $editing_slug && $slug && taxonomy_exists( $slug ) ) {
			$errors->add( 'slug_conflict', __( '已有 WordPress 分类法使用该标识。', 'wem-content-types' ) );
		}

		$singular = sanitize_text_field( $input['singular_label'] ?? '' );
		$plural   = sanitize_text_field( $input['plural_label'] ?? '' );

		if ( '' === $singular ) {
			$errors->add( 'singular_required', __( '请填写单数名称。', 'wem-content-types' ) );
		}
		if ( '' === $plural ) {
			$errors->add( 'plural_required', __( '请填写复数名称。', 'wem-content-types' ) );
		}

		$object_types = isset( $input['object_types'] ) && is_array( $input['object_types'] )
			? array_values( array_unique( array_filter( array_map( 'sanitize_key', $input['object_types'] ) ) ) )
			: [];

		if ( empty( $object_types ) ) {
			$errors->add( 'object_type_required', __( '请至少选择一个要关联此分类法的内容类型。', 'wem-content-types' ) );
		}

		$registered_post_types = get_post_types( [], 'names' );
		$known_wem_post_types   = array_keys( $this->storage->all() );
		$existing_object_types  = [];

		if ( $editing_slug ) {
			$existing_config = $this->storage->get_taxonomy( $editing_slug );
			if ( is_array( $existing_config ) && isset( $existing_config['object_types'] ) && is_array( $existing_config['object_types'] ) ) {
				$existing_object_types = array_map( 'sanitize_key', $existing_config['object_types'] );
			}
		}

		$known_post_types = array_values(
			array_unique(
				array_merge(
					$registered_post_types,
					$known_wem_post_types,
					$existing_object_types,
					array_map( 'sanitize_key', $additional_known_post_types )
				)
			)
		);
		foreach ( $object_types as $object_type ) {
			if ( ! in_array( $object_type, $known_post_types, true ) ) {
				$errors->add(
					'object_type_invalid',
					sprintf( __( '所选内容类型“%s”当前不可用。', 'wem-content-types' ), $object_type )
				);
			}
		}

		$rewrite_slug = $this->validate_rewrite_slug( $input['rewrite_slug'] ?? '', $slug, $errors );

		if ( $errors->has_errors() ) {
			return $errors;
		}

		return [
			'slug'               => $slug,
			'enabled'            => ! empty( $input['enabled'] ),
			'singular_label'     => $singular,
			'plural_label'       => $plural,
			'description'        => sanitize_textarea_field( $input['description'] ?? '' ),
			'object_types'       => $object_types,
			'public'             => ! empty( $input['public'] ),
			'hierarchical'       => ! empty( $input['hierarchical'] ),
			'show_admin_column'  => ! empty( $input['show_admin_column'] ),
			'show_in_rest'       => ! empty( $input['show_in_rest'] ),
			'publicly_queryable' => ! empty( $input['publicly_queryable'] ),
			'show_ui'            => ! empty( $input['show_ui'] ),
			'show_in_menu'       => ! empty( $input['show_in_menu'] ),
			'show_in_nav_menus'  => ! empty( $input['show_in_nav_menus'] ),
			'rewrite_enabled'    => ! empty( $input['rewrite_enabled'] ),
			'rewrite_slug'       => $rewrite_slug,
			'rewrite_with_front' => ! empty( $input['rewrite_with_front'] ),
		];
	}

	/**
	 * Validate a simple rewrite base. WEM intentionally supports one URL
	 * segment here; nested rewrite paths can be introduced later only if a
	 * real project requires them.
	 */
	private function validate_rewrite_slug( $value, string $fallback, \WP_Error $errors ): string {
		$raw = trim( (string) $value );
		if ( '' === $raw ) {
			return $fallback;
		}

		$sanitized = sanitize_title( $raw );
		if ( '' === $sanitized ) {
			$errors->add( 'rewrite_slug_invalid', __( 'Rewrite 标识必须至少包含字母或数字。', 'wem-content-types' ) );
			return $fallback;
		}

		if ( strlen( $sanitized ) > 200 ) {
			$errors->add( 'rewrite_slug_length', __( 'Rewrite 标识过长。', 'wem-content-types' ) );
		}

		return $sanitized;
	}
}
