<?php
namespace WEM\Content_Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Registry {
	private Storage $storage;

	public function __construct( Storage $storage ) {
		$this->storage = $storage;
	}

	public function hooks(): void {
		add_action( 'init', [ $this, 'register_post_types' ], 5 );
		add_action( 'init', [ $this, 'register_taxonomies' ], 6 );
		add_action( 'init', [ $this, 'maybe_flush_rewrite_rules' ], 99 );
	}

	public function register_post_types(): void {
		$configs = $this->storage->all();

		/**
		 * Fires before WEM Content Types starts registering post types.
		 *
		 * @param array $configs Saved post type configurations.
		 */
		do_action( 'wem_ct_before_register_post_types', $configs );

		foreach ( $configs as $slug => $config ) {
			if ( ! is_array( $config ) ) {
				continue;
			}

			// Disabled configurations remain stored but are not registered.
			if ( array_key_exists( 'enabled', $config ) && empty( $config['enabled'] ) ) {
				continue;
			}

			/**
			 * Filter whether a saved post type should be registered.
			 *
			 * @param bool   $should_register Default true.
			 * @param string $slug            Post type slug.
			 * @param array  $config          Saved configuration.
			 */
			$should_register = apply_filters( 'wem_ct_should_register_post_type', true, $slug, $config );
			if ( ! $should_register ) {
				continue;
			}

			$this->register_single_post_type( $slug, $config );
		}

		/**
		 * Fires after WEM Content Types finishes registering post types.
		 *
		 * @param array $configs Saved post type configurations.
		 */
		do_action( 'wem_ct_after_register_post_types', $configs );
	}

	public function register_taxonomies(): void {
		$configs = $this->storage->all_taxonomies();

		/**
		 * Fires before WEM Content Types starts registering taxonomies.
		 *
		 * @param array $configs Saved taxonomy configurations.
		 */
		do_action( 'wem_ct_before_register_taxonomies', $configs );

		foreach ( $configs as $slug => $config ) {
			if ( ! is_array( $config ) ) {
				continue;
			}

			// Disabled configurations remain stored but are not registered.
			if ( array_key_exists( 'enabled', $config ) && empty( $config['enabled'] ) ) {
				continue;
			}

			/**
			 * Filter whether a saved taxonomy should be registered.
			 *
			 * @param bool   $should_register Default true.
			 * @param string $slug            Taxonomy slug.
			 * @param array  $config          Saved configuration.
			 */
			$should_register = apply_filters( 'wem_ct_should_register_taxonomy', true, $slug, $config );
			if ( ! $should_register ) {
				continue;
			}

			$this->register_single_taxonomy( $slug, $config );
		}

		/**
		 * Fires after WEM Content Types finishes registering taxonomies.
		 *
		 * @param array $configs Saved taxonomy configurations.
		 */
		do_action( 'wem_ct_after_register_taxonomies', $configs );
	}

	public function maybe_flush_rewrite_rules(): void {
		if ( ! get_option( 'wem_ct_needs_flush' ) ) {
			return;
		}

		flush_rewrite_rules( false );
		delete_option( 'wem_ct_needs_flush' );
	}

	private function register_single_post_type( string $slug, array $config ): void {
		$singular = $config['singular_label'] ?? ucfirst( $slug );
		$plural   = $config['plural_label'] ?? $singular;

		$labels = [
			'name'                     => $plural,
			'singular_name'            => $singular,
			'menu_name'                => $plural,
			'name_admin_bar'           => $singular,
			'add_new'                  => __( 'Add New', 'wem-content-types' ),
			'add_new_item'             => sprintf( __( 'Add New %s', 'wem-content-types' ), $singular ),
			'edit_item'                => sprintf( __( 'Edit %s', 'wem-content-types' ), $singular ),
			'new_item'                 => sprintf( __( 'New %s', 'wem-content-types' ), $singular ),
			'view_item'                => sprintf( __( 'View %s', 'wem-content-types' ), $singular ),
			'view_items'               => sprintf( __( 'View %s', 'wem-content-types' ), $plural ),
			'search_items'             => sprintf( __( 'Search %s', 'wem-content-types' ), $plural ),
			'not_found'                => sprintf( __( 'No %s found.', 'wem-content-types' ), strtolower( $plural ) ),
			'not_found_in_trash'       => sprintf( __( 'No %s found in Trash.', 'wem-content-types' ), strtolower( $plural ) ),
			'all_items'                => sprintf( __( 'All %s', 'wem-content-types' ), $plural ),
			'archives'                 => sprintf( __( '%s Archives', 'wem-content-types' ), $singular ),
			'attributes'               => sprintf( __( '%s Attributes', 'wem-content-types' ), $singular ),
			'insert_into_item'         => sprintf( __( 'Insert into %s', 'wem-content-types' ), strtolower( $singular ) ),
			'uploaded_to_this_item'    => sprintf( __( 'Uploaded to this %s', 'wem-content-types' ), strtolower( $singular ) ),
			'featured_image'           => sprintf( __( '%s Featured Image', 'wem-content-types' ), $singular ),
			'set_featured_image'       => sprintf( __( 'Set %s featured image', 'wem-content-types' ), strtolower( $singular ) ),
			'remove_featured_image'    => sprintf( __( 'Remove %s featured image', 'wem-content-types' ), strtolower( $singular ) ),
			'use_featured_image'       => sprintf( __( 'Use as %s featured image', 'wem-content-types' ), strtolower( $singular ) ),
			'filter_items_list'        => sprintf( __( 'Filter %s list', 'wem-content-types' ), strtolower( $plural ) ),
			'items_list_navigation'    => sprintf( __( '%s list navigation', 'wem-content-types' ), $plural ),
			'items_list'               => sprintf( __( '%s list', 'wem-content-types' ), $plural ),
		];

		/**
		 * Filters automatically generated post type labels.
		 *
		 * @param array  $labels Generated labels.
		 * @param string $slug   Post type slug.
		 * @param array  $config Saved WEM configuration.
		 */
		$labels = (array) apply_filters( 'wem_ct_post_type_labels', $labels, $slug, $config );

		$is_public            = ! empty( $config['public'] );
		$publicly_queryable   = array_key_exists( 'publicly_queryable', $config ) ? ! empty( $config['publicly_queryable'] ) : $is_public;
		$exclude_from_search  = array_key_exists( 'exclude_from_search', $config ) ? ! empty( $config['exclude_from_search'] ) : ! $is_public;
		$show_ui              = array_key_exists( 'show_ui', $config ) ? ! empty( $config['show_ui'] ) : true;
		$show_in_menu         = array_key_exists( 'show_in_menu', $config ) ? ! empty( $config['show_in_menu'] ) : true;
		$show_in_nav_menus    = array_key_exists( 'show_in_nav_menus', $config ) ? ! empty( $config['show_in_nav_menus'] ) : $is_public;
		$show_in_admin_bar    = array_key_exists( 'show_in_admin_bar', $config ) ? ! empty( $config['show_in_admin_bar'] ) : true;
		$menu_position        = isset( $config['menu_position'] ) && '' !== $config['menu_position'] ? (int) $config['menu_position'] : null;
		$rewrite_enabled      = array_key_exists( 'rewrite_enabled', $config ) ? ! empty( $config['rewrite_enabled'] ) : true;
		$rewrite_slug         = ! empty( $config['rewrite_slug'] ) ? sanitize_title( $config['rewrite_slug'] ) : $slug;
		$rewrite_with_front   = array_key_exists( 'rewrite_with_front', $config ) ? ! empty( $config['rewrite_with_front'] ) : true;

		$args = [
			'labels'              => $labels,
			'description'         => $config['description'] ?? '',
			'public'              => $is_public,
			'publicly_queryable'  => $publicly_queryable,
			'exclude_from_search' => $exclude_from_search,
			'show_ui'             => $show_ui,
			'show_in_menu'        => $show_in_menu,
			'show_in_nav_menus'   => $show_in_nav_menus,
			'show_in_admin_bar'   => $show_in_admin_bar,
			'show_in_rest'        => ! empty( $config['show_in_rest'] ),
			'has_archive'         => ! empty( $config['has_archive'] ),
			'rewrite'             => $rewrite_enabled ? [
				'slug'       => $rewrite_slug,
				'with_front' => $rewrite_with_front,
			] : false,
			'query_var'            => true,
			'capability_type'      => 'post',
			'map_meta_cap'         => true,
			'hierarchical'         => false,
			'menu_icon'            => $config['menu_icon'] ?? 'dashicons-admin-post',
			'menu_position'        => $menu_position,
			'supports'             => ! empty( $config['supports'] ) ? $config['supports'] : [ 'title' ],
		];

		$args = apply_filters( 'wem_ct_post_type_args', $args, $slug, $config );

		do_action( 'wem_ct_before_register_post_type', $slug, $args, $config );

		$result = register_post_type( $slug, $args );

		do_action( 'wem_ct_post_type_registered', $slug, $result, $args, $config );
	}

	private function register_single_taxonomy( string $slug, array $config ): void {
		$singular = $config['singular_label'] ?? ucfirst( $slug );
		$plural   = $config['plural_label'] ?? $singular;

		$labels = [
			'name'                       => $plural,
			'singular_name'              => $singular,
			'menu_name'                  => $plural,
			'search_items'               => sprintf( __( 'Search %s', 'wem-content-types' ), $plural ),
			'popular_items'              => sprintf( __( 'Popular %s', 'wem-content-types' ), $plural ),
			'all_items'                  => sprintf( __( 'All %s', 'wem-content-types' ), $plural ),
			'parent_item'                => sprintf( __( 'Parent %s', 'wem-content-types' ), $singular ),
			'parent_item_colon'          => sprintf( __( 'Parent %s:', 'wem-content-types' ), $singular ),
			'edit_item'                  => sprintf( __( 'Edit %s', 'wem-content-types' ), $singular ),
			'view_item'                  => sprintf( __( 'View %s', 'wem-content-types' ), $singular ),
			'update_item'                => sprintf( __( 'Update %s', 'wem-content-types' ), $singular ),
			'add_new_item'               => sprintf( __( 'Add New %s', 'wem-content-types' ), $singular ),
			'new_item_name'              => sprintf( __( 'New %s Name', 'wem-content-types' ), $singular ),
			'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'wem-content-types' ), strtolower( $plural ) ),
			'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'wem-content-types' ), strtolower( $plural ) ),
			'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', 'wem-content-types' ), strtolower( $plural ) ),
			'not_found'                  => sprintf( __( 'No %s found.', 'wem-content-types' ), strtolower( $plural ) ),
			'no_terms'                   => sprintf( __( 'No %s', 'wem-content-types' ), strtolower( $plural ) ),
			'filter_by_item'             => sprintf( __( 'Filter by %s', 'wem-content-types' ), $singular ),
			'items_list_navigation'      => sprintf( __( '%s list navigation', 'wem-content-types' ), $plural ),
			'items_list'                 => sprintf( __( '%s list', 'wem-content-types' ), $plural ),
			'back_to_items'              => sprintf( __( '← Back to %s', 'wem-content-types' ), $plural ),
		];

		/**
		 * Filters automatically generated taxonomy labels.
		 *
		 * @param array  $labels Generated labels.
		 * @param string $slug   Taxonomy slug.
		 * @param array  $config Saved WEM configuration.
		 */
		$labels = (array) apply_filters( 'wem_ct_taxonomy_labels', $labels, $slug, $config );

		$object_types = isset( $config['object_types'] ) && is_array( $config['object_types'] )
			? array_values( array_filter( array_map( 'sanitize_key', $config['object_types'] ) ) )
			: [];

		/**
		 * Filters post types attached to a taxonomy.
		 *
		 * @param array  $object_types Registered post type slugs.
		 * @param string $slug         Taxonomy slug.
		 * @param array  $config       Saved WEM configuration.
		 */
		$object_types = apply_filters( 'wem_ct_taxonomy_object_types', $object_types, $slug, $config );

		if ( empty( $object_types ) ) {
			return;
		}

		$is_public           = ! empty( $config['public'] );
		$publicly_queryable  = array_key_exists( 'publicly_queryable', $config ) ? ! empty( $config['publicly_queryable'] ) : $is_public;
		$show_ui             = array_key_exists( 'show_ui', $config ) ? ! empty( $config['show_ui'] ) : true;
		$show_in_menu        = array_key_exists( 'show_in_menu', $config ) ? ! empty( $config['show_in_menu'] ) : true;
		$show_in_nav_menus   = array_key_exists( 'show_in_nav_menus', $config ) ? ! empty( $config['show_in_nav_menus'] ) : $is_public;
		$rewrite_enabled     = array_key_exists( 'rewrite_enabled', $config ) ? ! empty( $config['rewrite_enabled'] ) : $is_public;
		$rewrite_slug        = ! empty( $config['rewrite_slug'] ) ? sanitize_title( $config['rewrite_slug'] ) : $slug;
		$rewrite_with_front  = array_key_exists( 'rewrite_with_front', $config ) ? ! empty( $config['rewrite_with_front'] ) : true;

		$args = [
			'labels'             => $labels,
			'description'        => $config['description'] ?? '',
			'public'             => $is_public,
			'publicly_queryable' => $publicly_queryable,
			'hierarchical'       => ! empty( $config['hierarchical'] ),
			'show_ui'            => $show_ui,
			'show_in_menu'       => $show_in_menu,
			'show_in_nav_menus'  => $show_in_nav_menus,
			'show_in_rest'       => ! empty( $config['show_in_rest'] ),
			'show_admin_column'  => ! empty( $config['show_admin_column'] ),
			'query_var'          => $publicly_queryable,
			'rewrite'            => $rewrite_enabled ? [
				'slug'       => $rewrite_slug,
				'with_front' => $rewrite_with_front,
			] : false,
		];

		/**
		 * Filters the final register_taxonomy() arguments.
		 *
		 * @param array  $args         WordPress taxonomy arguments.
		 * @param string $slug         Taxonomy slug.
		 * @param array  $config       Saved WEM configuration.
		 * @param array  $object_types Attached post type slugs.
		 */
		$args = apply_filters( 'wem_ct_taxonomy_args', $args, $slug, $config, $object_types );

		do_action( 'wem_ct_before_register_taxonomy', $slug, $object_types, $args, $config );

		$result = register_taxonomy( $slug, $object_types, $args );

		do_action( 'wem_ct_taxonomy_registered', $slug, $result, $object_types, $args, $config );
	}
}
