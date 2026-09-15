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
			$errors->add( 'slug_format', __( 'Post type slug may contain only lowercase letters, numbers, underscores, and hyphens.', 'wem-content-types' ) );
		}

		if ( '' === $slug ) {
			$errors->add( 'slug_required', __( 'Post type slug is required.', 'wem-content-types' ) );
		} elseif ( strlen( $slug ) > 20 ) {
			$errors->add( 'slug_length', __( 'Post type slug must be 20 characters or fewer.', 'wem-content-types' ) );
		} elseif ( in_array( $slug, $this->reserved_post_types, true ) ) {
			$errors->add( 'slug_reserved', __( 'This slug is reserved by WordPress. Please choose another one.', 'wem-content-types' ) );
		}

		if ( $editing_slug && $slug !== $editing_slug ) {
			$errors->add( 'slug_immutable', __( 'The slug cannot be changed after a post type has been created.', 'wem-content-types' ) );
		}

		if ( ! $editing_slug && $this->storage->exists( $slug ) ) {
			$errors->add( 'slug_exists', __( 'A WEM post type with this slug already exists.', 'wem-content-types' ) );
		}

		if ( ! $editing_slug && $slug && post_type_exists( $slug ) ) {
			$errors->add( 'slug_conflict', __( 'A registered WordPress post type already uses this slug.', 'wem-content-types' ) );
		}

		$singular = sanitize_text_field( $input['singular_label'] ?? '' );
		$plural   = sanitize_text_field( $input['plural_label'] ?? '' );

		if ( '' === $singular ) {
			$errors->add( 'singular_required', __( 'Singular label is required.', 'wem-content-types' ) );
		}
		if ( '' === $plural ) {
			$errors->add( 'plural_required', __( 'Plural label is required.', 'wem-content-types' ) );
		}

		$menu_icon = sanitize_html_class( $input['menu_icon'] ?? 'dashicons-admin-post' );
		if ( '' === $menu_icon ) {
			$menu_icon = 'dashicons-admin-post';
		}
		if ( 0 !== strpos( $menu_icon, 'dashicons-' ) ) {
			$errors->add( 'menu_icon_invalid', __( 'Menu icon must be a Dashicons class such as dashicons-products.', 'wem-content-types' ) );
		}

		$menu_position_raw = trim( (string) ( $input['menu_position'] ?? '' ) );
		$menu_position     = null;
		if ( '' !== $menu_position_raw ) {
			if ( ! ctype_digit( $menu_position_raw ) || (int) $menu_position_raw < 2 || (int) $menu_position_raw > 100 ) {
				$errors->add( 'menu_position_invalid', __( 'Menu position must be a whole number between 2 and 100, or left empty.', 'wem-content-types' ) );
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
			$errors->add( 'slug_format', __( 'Taxonomy slug may contain only lowercase letters, numbers, underscores, and hyphens.', 'wem-content-types' ) );
		}

		if ( '' === $slug ) {
			$errors->add( 'slug_required', __( 'Taxonomy slug is required.', 'wem-content-types' ) );
		} elseif ( strlen( $slug ) > 32 ) {
			$errors->add( 'slug_length', __( 'Taxonomy slug must be 32 characters or fewer.', 'wem-content-types' ) );
		} elseif ( in_array( $slug, $this->reserved_taxonomies, true ) ) {
			$errors->add( 'slug_reserved', __( 'This taxonomy slug is reserved or may conflict with WordPress query variables. Please choose another one.', 'wem-content-types' ) );
		}

		if ( $editing_slug && $slug !== $editing_slug ) {
			$errors->add( 'slug_immutable', __( 'The taxonomy slug cannot be changed after creation.', 'wem-content-types' ) );
		}

		if ( ! $editing_slug && $this->storage->taxonomy_exists( $slug ) ) {
			$errors->add( 'slug_exists', __( 'A WEM taxonomy with this slug already exists.', 'wem-content-types' ) );
		}

		if ( ! $editing_slug && $slug && taxonomy_exists( $slug ) ) {
			$errors->add( 'slug_conflict', __( 'A registered WordPress taxonomy already uses this slug.', 'wem-content-types' ) );
		}

		$singular = sanitize_text_field( $input['singular_label'] ?? '' );
		$plural   = sanitize_text_field( $input['plural_label'] ?? '' );

		if ( '' === $singular ) {
			$errors->add( 'singular_required', __( 'Singular label is required.', 'wem-content-types' ) );
		}
		if ( '' === $plural ) {
			$errors->add( 'plural_required', __( 'Plural label is required.', 'wem-content-types' ) );
		}

		$object_types = isset( $input['object_types'] ) && is_array( $input['object_types'] )
			? array_values( array_unique( array_filter( array_map( 'sanitize_key', $input['object_types'] ) ) ) )
			: [];

		if ( empty( $object_types ) ) {
			$errors->add( 'object_type_required', __( 'Select at least one post type to attach this taxonomy to.', 'wem-content-types' ) );
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
					sprintf( __( 'The selected post type "%s" is not currently available.', 'wem-content-types' ), $object_type )
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
			$errors->add( 'rewrite_slug_invalid', __( 'Rewrite slug must contain letters or numbers.', 'wem-content-types' ) );
			return $fallback;
		}

		if ( strlen( $sanitized ) > 200 ) {
			$errors->add( 'rewrite_slug_length', __( 'Rewrite slug is too long.', 'wem-content-types' ) );
		}

		return $sanitized;
	}
}
