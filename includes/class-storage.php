<?php
namespace WEM\Content_Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Storage {
	public const OPTION_POST_TYPES = 'wem_ct_post_types';
	public const OPTION_TAXONOMIES = 'wem_ct_taxonomies';

	/**
	 * Return all saved post type configurations.
	 *
	 * Kept as all() for backward compatibility with v0.1.x.
	 */
	public function all(): array {
		$items = get_option( self::OPTION_POST_TYPES, [] );
		return is_array( $items ) ? $items : [];
	}

	public function get( string $slug ): ?array {
		$items = $this->all();
		return isset( $items[ $slug ] ) && is_array( $items[ $slug ] ) ? $items[ $slug ] : null;
	}

	public function exists( string $slug ): bool {
		$items = $this->all();
		return isset( $items[ $slug ] );
	}

	public function save( array $config ): bool {
		$slug = $config['slug'] ?? '';
		if ( '' === $slug ) {
			return false;
		}

		$items          = $this->all();
		$items[ $slug ] = $config;
		ksort( $items );

		return update_option( self::OPTION_POST_TYPES, $items, false );
	}

	public function delete( string $slug ): bool {
		$items = $this->all();
		if ( ! isset( $items[ $slug ] ) ) {
			return false;
		}

		unset( $items[ $slug ] );
		return update_option( self::OPTION_POST_TYPES, $items, false );
	}

	/**
	 * Return all saved taxonomy configurations.
	 */
	public function all_taxonomies(): array {
		$items = get_option( self::OPTION_TAXONOMIES, [] );
		return is_array( $items ) ? $items : [];
	}

	public function get_taxonomy( string $slug ): ?array {
		$items = $this->all_taxonomies();
		return isset( $items[ $slug ] ) && is_array( $items[ $slug ] ) ? $items[ $slug ] : null;
	}

	public function taxonomy_exists( string $slug ): bool {
		$items = $this->all_taxonomies();
		return isset( $items[ $slug ] );
	}

	public function save_taxonomy( array $config ): bool {
		$slug = $config['slug'] ?? '';
		if ( '' === $slug ) {
			return false;
		}

		$items          = $this->all_taxonomies();
		$items[ $slug ] = $config;
		ksort( $items );

		return update_option( self::OPTION_TAXONOMIES, $items, false );
	}

	public function delete_taxonomy( string $slug ): bool {
		$items = $this->all_taxonomies();
		if ( ! isset( $items[ $slug ] ) ) {
			return false;
		}

		unset( $items[ $slug ] );
		return update_option( self::OPTION_TAXONOMIES, $items, false );
	}
}
