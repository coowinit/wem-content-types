<?php
namespace WEM\Content_Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Portable JSON import/export for WEM structure definitions.
 *
 * This service deliberately transfers configuration only. It does not export
 * posts, terms, media, post meta, or term relationships.
 */
class Transfer {
	public const FORMAT = 'wem-content-types';
	public const SCHEMA_VERSION = 1;
	public const MAX_IMPORT_BYTES = 1048576; // 1 MB is far beyond normal WEM config files.

	private Storage $storage;
	private Validator $validator;

	public function __construct( Storage $storage, Validator $validator ) {
		$this->storage   = $storage;
		$this->validator = $validator;
	}

	/**
	 * Build a portable, explicit representation of current WEM configuration.
	 *
	 * Older WEM definitions may not contain fields introduced by later plugin
	 * versions. Canonicalizing here preserves their effective runtime behavior
	 * instead of silently turning missing values into false during import.
	 */
	public function export_payload(): array {
		$post_types = [];
		foreach ( $this->storage->all() as $slug => $config ) {
			if ( ! is_array( $config ) ) {
				continue;
			}
			$post_types[ $slug ] = $this->canonical_post_type( $slug, $config );
		}

		$taxonomies = [];
		foreach ( $this->storage->all_taxonomies() as $slug => $config ) {
			if ( ! is_array( $config ) ) {
				continue;
			}
			$taxonomies[ $slug ] = $this->canonical_taxonomy( $slug, $config );
		}

		$payload = [
			'format'         => self::FORMAT,
			'schema_version' => self::SCHEMA_VERSION,
			'plugin_version' => defined( 'WEM_CT_VERSION' ) ? WEM_CT_VERSION : '',
			'exported_at'    => gmdate( 'c' ),
			'post_types'     => $post_types,
			'taxonomies'     => $taxonomies,
		];

		/**
		 * Filters the complete JSON export payload.
		 *
		 * Extensions may add top-level metadata. Core WEM configuration should
		 * remain under post_types and taxonomies so the standard importer can
		 * continue to validate it safely.
		 *
		 * @param array $payload Export payload.
		 */
		return (array) apply_filters( 'wem_ct_export_payload', $payload );
	}

	public function export_json(): string {
		$json = wp_json_encode(
			$this->export_payload(),
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		);

		return is_string( $json ) ? $json : '';
	}

	/**
	 * Validate and import a WEM JSON document.
	 *
	 * Import is intentionally a safe merge:
	 * - new WEM slugs are added
	 * - matching WEM slugs are updated
	 * - unrelated existing WEM definitions are never deleted
	 */
	public function import_json( string $json ) {
		$json = preg_replace( '/^\xEF\xBB\xBF/', '', $json );
		$data = json_decode( (string) $json, true );

		if ( JSON_ERROR_NONE !== json_last_error() ) {
			return new \WP_Error(
				'invalid_json',
				sprintf(
					/* translators: %s: JSON parser error. */
					__( 'The import file is not valid JSON: %s', 'wem-content-types' ),
					json_last_error_msg()
				)
			);
		}

		if ( ! is_array( $data ) ) {
			return new \WP_Error( 'invalid_payload', __( 'The import file does not contain a valid WEM configuration object.', 'wem-content-types' ) );
		}

		return $this->import_payload( $data );
	}

	public function import_payload( array $payload ) {
		$errors = new \WP_Error();

		if ( self::FORMAT !== ( $payload['format'] ?? '' ) ) {
			$errors->add( 'format_mismatch', __( 'This JSON file is not a WEM Content Types export.', 'wem-content-types' ) );
		}

		$schema_version = isset( $payload['schema_version'] ) ? (int) $payload['schema_version'] : 0;
		if ( self::SCHEMA_VERSION !== $schema_version ) {
			$errors->add(
				'schema_mismatch',
				sprintf(
					/* translators: 1: imported schema version, 2: supported schema version. */
					__( 'Unsupported import schema version %1$d. This plugin supports schema version %2$d.', 'wem-content-types' ),
					$schema_version,
					self::SCHEMA_VERSION
				)
			);
		}

		$post_types = $payload['post_types'] ?? [];
		$taxonomies = $payload['taxonomies'] ?? [];

		if ( ! is_array( $post_types ) || ! is_array( $taxonomies ) ) {
			$errors->add( 'invalid_sections', __( 'The import file must contain valid post_types and taxonomies sections.', 'wem-content-types' ) );
		}

		if ( $errors->has_errors() ) {
			return $errors;
		}

		if ( empty( $post_types ) && empty( $taxonomies ) ) {
			return new \WP_Error( 'empty_import', __( 'The import file contains no Post Type or Taxonomy definitions.', 'wem-content-types' ) );
		}

		$validated_post_types = [];
		foreach ( $post_types as $key => $config ) {
			if ( ! is_array( $config ) ) {
				$errors->add( 'invalid_post_type', sprintf( __( 'Post Type "%s" has an invalid configuration.', 'wem-content-types' ), (string) $key ) );
				continue;
			}

			$slug = $this->resolve_config_slug( $key, $config, 'post_type', $errors );
			if ( '' === $slug ) {
				continue;
			}

			$config  = $this->canonical_post_type( $slug, $config );
			$editing = $this->storage->exists( $slug ) ? $slug : null;
			$result  = $this->validator->validate_post_type( $config, $editing );

			if ( is_wp_error( $result ) ) {
				foreach ( $result->get_error_messages() as $message ) {
					$errors->add( 'post_type_' . $slug, sprintf( '[%1$s] %2$s', $slug, $message ) );
				}
				continue;
			}

			$validated_post_types[ $slug ] = $result;
		}

		$additional_known_post_types = array_keys( $validated_post_types );
		$validated_taxonomies        = [];

		foreach ( $taxonomies as $key => $config ) {
			if ( ! is_array( $config ) ) {
				$errors->add( 'invalid_taxonomy', sprintf( __( 'Taxonomy "%s" has an invalid configuration.', 'wem-content-types' ), (string) $key ) );
				continue;
			}

			$slug = $this->resolve_config_slug( $key, $config, 'taxonomy', $errors );
			if ( '' === $slug ) {
				continue;
			}

			$config  = $this->canonical_taxonomy( $slug, $config );
			$editing = $this->storage->taxonomy_exists( $slug ) ? $slug : null;
			$result  = $this->validator->validate_taxonomy( $config, $editing, $additional_known_post_types );

			if ( is_wp_error( $result ) ) {
				foreach ( $result->get_error_messages() as $message ) {
					$errors->add( 'taxonomy_' . $slug, sprintf( '[%1$s] %2$s', $slug, $message ) );
				}
				continue;
			}

			$validated_taxonomies[ $slug ] = $result;
		}

		// Validate the entire document before writing anything to wp_options.
		if ( $errors->has_errors() ) {
			return $errors;
		}

		$summary = [
			'post_types' => [ 'added' => 0, 'updated' => 0 ],
			'taxonomies' => [ 'added' => 0, 'updated' => 0 ],
		];

		do_action( 'wem_ct_before_import', $validated_post_types, $validated_taxonomies, $payload );

		foreach ( $validated_post_types as $slug => $config ) {
			$exists = $this->storage->exists( $slug );
			$this->storage->save( $config );
			$summary['post_types'][ $exists ? 'updated' : 'added' ]++;
		}

		foreach ( $validated_taxonomies as $slug => $config ) {
			$exists = $this->storage->taxonomy_exists( $slug );
			$this->storage->save_taxonomy( $config );
			$summary['taxonomies'][ $exists ? 'updated' : 'added' ]++;
		}

		do_action( 'wem_ct_after_import', $summary, $validated_post_types, $validated_taxonomies, $payload );

		return $summary;
	}

	private function resolve_config_slug( $key, array $config, string $type, \WP_Error $errors ): string {
		$raw_key_slug    = is_string( $key ) ? trim( $key ) : '';
		$raw_config_slug = trim( (string) ( $config['slug'] ?? '' ) );
		$key_slug        = sanitize_key( $raw_key_slug );
		$config_slug     = sanitize_key( $raw_config_slug );

		if ( '' !== $raw_key_slug && $raw_key_slug !== $key_slug ) {
			$errors->add( 'invalid_slug_key', __( 'An imported configuration uses an invalid slug key. Use only lowercase letters, numbers, underscores, and hyphens.', 'wem-content-types' ) );
			return '';
		}

		if ( '' !== $raw_config_slug && $raw_config_slug !== $config_slug ) {
			$errors->add( 'invalid_embedded_slug', __( 'An imported configuration contains an invalid embedded slug. Use only lowercase letters, numbers, underscores, and hyphens.', 'wem-content-types' ) );
			return '';
		}

		$slug = $config_slug ?: $key_slug;

		if ( '' === $slug ) {
			$errors->add( 'missing_slug', __( 'An imported configuration is missing its slug.', 'wem-content-types' ) );
			return '';
		}

		if ( '' !== $key_slug && '' !== $config_slug && $key_slug !== $config_slug ) {
			$errors->add(
				'slug_mismatch',
				sprintf(
					/* translators: 1: configuration type, 2: object key, 3: embedded slug. */
					__( 'Imported %1$s key "%2$s" does not match its embedded slug "%3$s".', 'wem-content-types' ),
					'post_type' === $type ? __( 'Post Type', 'wem-content-types' ) : __( 'Taxonomy', 'wem-content-types' ),
					$key_slug,
					$config_slug
				)
			);
			return '';
		}

		return $slug;
	}

	private function canonical_post_type( string $slug, array $config ): array {
		$is_public = ! empty( $config['public'] );

		return [
			'slug'                => sanitize_key( $config['slug'] ?? $slug ),
			'enabled'             => array_key_exists( 'enabled', $config ) ? ! empty( $config['enabled'] ) : true,
			'singular_label'      => (string) ( $config['singular_label'] ?? '' ),
			'plural_label'        => (string) ( $config['plural_label'] ?? '' ),
			'description'         => (string) ( $config['description'] ?? '' ),
			'menu_icon'           => (string) ( $config['menu_icon'] ?? 'dashicons-admin-post' ),
			'public'              => $is_public,
			'has_archive'         => ! empty( $config['has_archive'] ),
			'show_in_rest'        => ! empty( $config['show_in_rest'] ),
			'supports'            => isset( $config['supports'] ) && is_array( $config['supports'] ) ? array_values( $config['supports'] ) : [ 'title' ],
			'publicly_queryable'  => array_key_exists( 'publicly_queryable', $config ) ? ! empty( $config['publicly_queryable'] ) : $is_public,
			'exclude_from_search' => array_key_exists( 'exclude_from_search', $config ) ? ! empty( $config['exclude_from_search'] ) : ! $is_public,
			'show_ui'             => array_key_exists( 'show_ui', $config ) ? ! empty( $config['show_ui'] ) : true,
			'show_in_menu'        => array_key_exists( 'show_in_menu', $config ) ? ! empty( $config['show_in_menu'] ) : true,
			'show_in_nav_menus'   => array_key_exists( 'show_in_nav_menus', $config ) ? ! empty( $config['show_in_nav_menus'] ) : $is_public,
			'show_in_admin_bar'   => array_key_exists( 'show_in_admin_bar', $config ) ? ! empty( $config['show_in_admin_bar'] ) : true,
			'menu_position'       => isset( $config['menu_position'] ) && '' !== (string) $config['menu_position'] ? $config['menu_position'] : null,
			'rewrite_enabled'     => array_key_exists( 'rewrite_enabled', $config ) ? ! empty( $config['rewrite_enabled'] ) : true,
			'rewrite_slug'        => (string) ( $config['rewrite_slug'] ?? $slug ),
			'rewrite_with_front'  => array_key_exists( 'rewrite_with_front', $config ) ? ! empty( $config['rewrite_with_front'] ) : true,
		];
	}

	private function canonical_taxonomy( string $slug, array $config ): array {
		$is_public = ! empty( $config['public'] );

		return [
			'slug'               => sanitize_key( $config['slug'] ?? $slug ),
			'enabled'            => array_key_exists( 'enabled', $config ) ? ! empty( $config['enabled'] ) : true,
			'singular_label'     => (string) ( $config['singular_label'] ?? '' ),
			'plural_label'       => (string) ( $config['plural_label'] ?? '' ),
			'description'        => (string) ( $config['description'] ?? '' ),
			'object_types'       => isset( $config['object_types'] ) && is_array( $config['object_types'] ) ? array_values( $config['object_types'] ) : [],
			'public'             => $is_public,
			'hierarchical'       => ! empty( $config['hierarchical'] ),
			'show_admin_column'  => ! empty( $config['show_admin_column'] ),
			'show_in_rest'       => ! empty( $config['show_in_rest'] ),
			'publicly_queryable' => array_key_exists( 'publicly_queryable', $config ) ? ! empty( $config['publicly_queryable'] ) : $is_public,
			'show_ui'            => array_key_exists( 'show_ui', $config ) ? ! empty( $config['show_ui'] ) : true,
			'show_in_menu'       => array_key_exists( 'show_in_menu', $config ) ? ! empty( $config['show_in_menu'] ) : true,
			'show_in_nav_menus'  => array_key_exists( 'show_in_nav_menus', $config ) ? ! empty( $config['show_in_nav_menus'] ) : $is_public,
			'rewrite_enabled'    => array_key_exists( 'rewrite_enabled', $config ) ? ! empty( $config['rewrite_enabled'] ) : $is_public,
			'rewrite_slug'       => (string) ( $config['rewrite_slug'] ?? $slug ),
			'rewrite_with_front' => array_key_exists( 'rewrite_with_front', $config ) ? ! empty( $config['rewrite_with_front'] ) : true,
		];
	}
}
