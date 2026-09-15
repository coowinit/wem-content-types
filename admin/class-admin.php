<?php
namespace WEM\Content_Types\Admin;

use WEM\Content_Types\Storage;
use WEM\Content_Types\Validator;
use WEM\Content_Types\Transfer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {
	private Storage $storage;
	private Validator $validator;
	private Transfer $transfer;

	public function __construct( Storage $storage, Validator $validator, Transfer $transfer ) {
		$this->storage   = $storage;
		$this->validator = $validator;
		$this->transfer  = $transfer;
	}

	public function hooks(): void {
		add_action( 'admin_menu', [ $this, 'menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'assets' ] );
		add_action( 'admin_post_wem_ct_save_post_type', [ $this, 'save_post_type' ] );
		add_action( 'admin_post_wem_ct_delete_post_type', [ $this, 'delete_post_type' ] );
		add_action( 'admin_post_wem_ct_toggle_post_type', [ $this, 'toggle_post_type' ] );
		add_action( 'admin_post_wem_ct_save_taxonomy', [ $this, 'save_taxonomy' ] );
		add_action( 'admin_post_wem_ct_delete_taxonomy', [ $this, 'delete_taxonomy' ] );
		add_action( 'admin_post_wem_ct_toggle_taxonomy', [ $this, 'toggle_taxonomy' ] );
		add_action( 'admin_post_wem_ct_export_config', [ $this, 'export_config' ] );
		add_action( 'admin_post_wem_ct_import_config', [ $this, 'import_config' ] );
	}

	private function capability(): string {
		return (string) apply_filters( 'wem_ct_manage_capability', 'manage_options' );
	}

	public function menu(): void {
		add_menu_page(
			__( 'WEM Content Types', 'wem-content-types' ),
			__( 'WEM Content Types', 'wem-content-types' ),
			$this->capability(),
			'wem-content-types',
			[ $this, 'render_post_types_page' ],
			'dashicons-screenoptions',
			58
		);

		add_submenu_page(
			'wem-content-types',
			__( 'Post Types', 'wem-content-types' ),
			__( 'Post Types', 'wem-content-types' ),
			$this->capability(),
			'wem-content-types',
			[ $this, 'render_post_types_page' ]
		);

		add_submenu_page(
			'wem-content-types',
			__( 'Taxonomies', 'wem-content-types' ),
			__( 'Taxonomies', 'wem-content-types' ),
			$this->capability(),
			'wem-content-types-taxonomies',
			[ $this, 'render_taxonomies_page' ]
		);

		add_submenu_page(
			'wem-content-types',
			__( 'Tools', 'wem-content-types' ),
			__( 'Tools', 'wem-content-types' ),
			$this->capability(),
			'wem-content-types-tools',
			[ $this, 'render_tools_page' ]
		);
	}

	public function assets( string $hook ): void {
		if ( false === strpos( $hook, 'wem-content-types' ) ) {
			return;
		}

		wp_enqueue_style( 'dashicons' );

		wp_enqueue_style(
			'wem-content-types-admin',
			WEM_CT_URL . 'assets/css/admin.css',
			[ 'dashicons' ],
			WEM_CT_VERSION
		);

		wp_enqueue_script(
			'wem-content-types-admin',
			WEM_CT_URL . 'assets/js/admin.js',
			[],
			WEM_CT_VERSION,
			true
		);
	}

	public function render_post_types_page(): void {
		$this->guard();

		$action = sanitize_key( wp_unslash( $_GET['action'] ?? '' ) );
		$slug   = sanitize_key( wp_unslash( $_GET['post_type'] ?? '' ) );

		$this->render_notice();

		if ( in_array( $action, [ 'add', 'edit' ], true ) ) {
			$this->render_post_type_form( 'edit' === $action ? $slug : null );
			return;
		}

		$items = $this->storage->all();
		include WEM_CT_PATH . 'admin/views/post-types.php';
	}

	public function render_taxonomies_page(): void {
		$this->guard();

		$action = sanitize_key( wp_unslash( $_GET['action'] ?? '' ) );
		$slug   = sanitize_key( wp_unslash( $_GET['taxonomy'] ?? '' ) );

		$this->render_notice();

		if ( in_array( $action, [ 'add', 'edit' ], true ) ) {
			$this->render_taxonomy_form( 'edit' === $action ? $slug : null );
			return;
		}

		$items = $this->storage->all_taxonomies();
		include WEM_CT_PATH . 'admin/views/taxonomies.php';
	}

	public function render_tools_page(): void {
		$this->guard();
		$this->render_notice();

		$post_type_count = count( $this->storage->all() );
		$taxonomy_count  = count( $this->storage->all_taxonomies() );

		include WEM_CT_PATH . 'admin/views/tools.php';
	}

	private function render_post_type_form( ?string $slug ): void {
		$config = $slug ? $this->storage->get( $slug ) : null;
		if ( $slug && ! $config ) {
			$this->redirect( 'post_type_not_found', [], 'wem-content-types' );
		}

		$is_new         = null === $config;
		$public_default = $is_new ? true : ! empty( $config['public'] );

		$defaults = [
			'slug'                => '',
			'enabled'             => true,
			'singular_label'      => '',
			'plural_label'        => '',
			'description'         => '',
			'menu_icon'           => 'dashicons-admin-post',
			'public'              => true,
			'has_archive'         => true,
			'show_in_rest'        => true,
			'supports'            => [ 'title', 'editor', 'thumbnail' ],
			'publicly_queryable'  => $public_default,
			'exclude_from_search' => ! $public_default,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => $public_default,
			'show_in_admin_bar'   => true,
			'menu_position'       => '',
			'rewrite_enabled'     => true,
			'rewrite_slug'        => $is_new ? '' : ( $slug ?: '' ),
			'rewrite_with_front'  => $is_new ? false : true,
		];
		$config = wp_parse_args( $config ?: [], $defaults );

		include WEM_CT_PATH . 'admin/views/post-type-edit.php';
	}

	private function render_taxonomy_form( ?string $slug ): void {
		$config = $slug ? $this->storage->get_taxonomy( $slug ) : null;
		if ( $slug && ! $config ) {
			$this->redirect( 'taxonomy_not_found', [], 'wem-content-types-taxonomies' );
		}

		$is_new         = null === $config;
		$public_default = $is_new ? true : ! empty( $config['public'] );

		$defaults = [
			'slug'               => '',
			'enabled'            => true,
			'singular_label'     => '',
			'plural_label'       => '',
			'description'        => '',
			'object_types'       => [],
			'public'             => true,
			'hierarchical'       => true,
			'show_admin_column'  => true,
			'show_in_rest'       => true,
			'publicly_queryable' => $public_default,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => $public_default,
			'rewrite_enabled'    => $public_default,
			'rewrite_slug'       => $is_new ? '' : ( $slug ?: '' ),
			'rewrite_with_front' => $is_new ? false : true,
		];
		$config = wp_parse_args( $config ?: [], $defaults );

		$available_post_types = $this->available_post_types( $config['object_types'] ?? [] );
		include WEM_CT_PATH . 'admin/views/taxonomy-edit.php';
	}

	private function available_post_types( array $preserve_slugs = [] ): array {
		$objects     = get_post_types( [ 'show_ui' => true ], 'objects' );
		$wem_configs = $this->storage->all();

		$wem_types      = [];
		$builtin_types  = [];
		$external_types  = [];
		$preserved_types = [];

		// WEM-managed post types are the primary targets for WEM taxonomies.
		// Disabled WEM types remain selectable so relationships are preserved
		// and become active again automatically if the type is re-enabled.
		foreach ( $wem_configs as $slug => $config ) {
			$label = isset( $objects[ $slug ] )
				? ( $objects[ $slug ]->labels->name ?? $slug )
				: ( $config['plural_label'] ?? $slug );

			$enabled = ! array_key_exists( 'enabled', $config ) || ! empty( $config['enabled'] );
			if ( ! $enabled ) {
				$label .= ' — ' . __( 'Disabled', 'wem-content-types' );
			}

			$wem_types[ $slug ] = $label;
		}

		// Only expose the useful built-in editorial post types by default.
		foreach ( [ 'post', 'page' ] as $slug ) {
			if ( isset( $objects[ $slug ] ) ) {
				$builtin_types[ $slug ] = $objects[ $slug ]->labels->name ?? $slug;
			}
		}

		// Keep compatibility with public CPTs registered by other plugins/themes,
		// while hiding WordPress internal UI post types such as wp_block and
		// wp_navigation. Special cases can still be added through the filter.
		foreach ( $objects as $slug => $object ) {
			if ( isset( $wem_types[ $slug ] ) || isset( $builtin_types[ $slug ] ) ) {
				continue;
			}

			if ( ! empty( $object->_builtin ) || empty( $object->public ) ) {
				continue;
			}

			$external_types[ $slug ] = $object->labels->name ?? $slug;
		}

		// Preserve relationships to third-party post types that are temporarily
		// unavailable. This prevents an edit from silently dropping a valid
		// relationship merely because the providing plugin is deactivated.
		foreach ( array_unique( array_map( 'sanitize_key', $preserve_slugs ) ) as $slug ) {
			if ( '' === $slug || isset( $wem_types[ $slug ] ) || isset( $builtin_types[ $slug ] ) || isset( $external_types[ $slug ] ) ) {
				continue;
			}

			$preserved_types[ $slug ] = sprintf(
				/* translators: %s: post type slug. */
				__( '%s — Unavailable', 'wem-content-types' ),
				$slug
			);
		}

		asort( $wem_types, SORT_NATURAL | SORT_FLAG_CASE );
		asort( $builtin_types, SORT_NATURAL | SORT_FLAG_CASE );
		asort( $external_types, SORT_NATURAL | SORT_FLAG_CASE );
		asort( $preserved_types, SORT_NATURAL | SORT_FLAG_CASE );

		// Preserve a predictable order: WEM first, WordPress editorial types next,
		// public third-party CPTs, then temporarily unavailable saved relationships.
		$items = $wem_types + $builtin_types + $external_types + $preserved_types;

		/**
		 * Filters post types shown in the Taxonomy "Attach To" selector.
		 *
		 * By default this list contains WEM-managed CPTs, WordPress Posts/Pages,
		 * and public CPTs registered by other plugins or themes. Internal
		 * WordPress post types are intentionally hidden. Existing relationships
		 * to temporarily unavailable third-party post types remain visible.
		 *
		 * @param array $items Key/value pairs of post type slug => display label.
		 */
		return (array) apply_filters( 'wem_ct_taxonomy_available_post_types', $items );
	}

	public function save_post_type(): void {
		$this->guard_action();
		check_admin_referer( 'wem_ct_save_post_type' );

		$editing_slug = sanitize_key( wp_unslash( $_POST['editing_slug'] ?? '' ) );
		$raw          = isset( $_POST['wem_ct'] ) && is_array( $_POST['wem_ct'] )
			? wp_unslash( $_POST['wem_ct'] )
			: [];

		$result = $this->validator->validate_post_type( $raw, $editing_slug ?: null );
		if ( is_wp_error( $result ) ) {
			$this->redirect(
				'validation_error',
				[
					'action'    => $editing_slug ? 'edit' : 'add',
					'post_type' => $editing_slug ?: sanitize_key( $raw['slug'] ?? '' ),
					'error'     => implode( ' ', $result->get_error_messages() ),
				],
				'wem-content-types'
			);
		}

		$this->storage->save( $result );
		$this->mark_rewrite_flush();
		$this->redirect( $editing_slug ? 'post_type_updated' : 'post_type_created', [], 'wem-content-types' );
	}

	public function delete_post_type(): void {
		$this->guard_action();

		$slug = sanitize_key( wp_unslash( $_GET['post_type'] ?? '' ) );
		check_admin_referer( 'wem_ct_delete_' . $slug );

		if ( $slug ) {
			$dependent_taxonomies = $this->taxonomies_attached_to_post_type( $slug );
			if ( $dependent_taxonomies ) {
				$this->redirect(
					'post_type_in_use',
					[
						'error' => sprintf(
							/* translators: %s: comma-separated taxonomy labels. */
							__( 'This post type is still attached to these WEM taxonomies: %s. Edit those taxonomies and remove the relationship before deleting the post type. If you only want to pause it, use Disable instead.', 'wem-content-types' ),
							implode( ', ', $dependent_taxonomies )
						),
					],
					'wem-content-types'
				);
			}

			$this->storage->delete( $slug );
			$this->mark_rewrite_flush();
		}

		$this->redirect( 'post_type_deleted', [], 'wem-content-types' );
	}

	public function toggle_post_type(): void {
		$this->guard_action();

		$slug = sanitize_key( wp_unslash( $_GET['post_type'] ?? '' ) );
		check_admin_referer( 'wem_ct_toggle_' . $slug );

		$config = $slug ? $this->storage->get( $slug ) : null;
		if ( $config ) {
			$current           = ! array_key_exists( 'enabled', $config ) || ! empty( $config['enabled'] );
			$config['enabled'] = ! $current;
			$this->storage->save( $config );
			$this->mark_rewrite_flush();
			$this->redirect( $config['enabled'] ? 'post_type_enabled' : 'post_type_disabled', [], 'wem-content-types' );
		}

		$this->redirect( 'post_type_not_found', [], 'wem-content-types' );
	}

	public function save_taxonomy(): void {
		$this->guard_action();
		check_admin_referer( 'wem_ct_save_taxonomy' );

		$editing_slug = sanitize_key( wp_unslash( $_POST['editing_slug'] ?? '' ) );
		$raw          = isset( $_POST['wem_ct'] ) && is_array( $_POST['wem_ct'] )
			? wp_unslash( $_POST['wem_ct'] )
			: [];

		$result = $this->validator->validate_taxonomy( $raw, $editing_slug ?: null );
		if ( is_wp_error( $result ) ) {
			$this->redirect(
				'validation_error',
				[
					'action'   => $editing_slug ? 'edit' : 'add',
					'taxonomy' => $editing_slug ?: sanitize_key( $raw['slug'] ?? '' ),
					'error'    => implode( ' ', $result->get_error_messages() ),
				],
				'wem-content-types-taxonomies'
			);
		}

		$this->storage->save_taxonomy( $result );
		$this->mark_rewrite_flush();
		$this->redirect( $editing_slug ? 'taxonomy_updated' : 'taxonomy_created', [], 'wem-content-types-taxonomies' );
	}

	public function delete_taxonomy(): void {
		$this->guard_action();

		$slug = sanitize_key( wp_unslash( $_GET['taxonomy'] ?? '' ) );
		check_admin_referer( 'wem_ct_delete_taxonomy_' . $slug );

		if ( $slug ) {
			$this->storage->delete_taxonomy( $slug );
			$this->mark_rewrite_flush();
		}

		$this->redirect( 'taxonomy_deleted', [], 'wem-content-types-taxonomies' );
	}

	public function toggle_taxonomy(): void {
		$this->guard_action();

		$slug = sanitize_key( wp_unslash( $_GET['taxonomy'] ?? '' ) );
		check_admin_referer( 'wem_ct_toggle_taxonomy_' . $slug );

		$config = $slug ? $this->storage->get_taxonomy( $slug ) : null;
		if ( $config ) {
			$current           = ! array_key_exists( 'enabled', $config ) || ! empty( $config['enabled'] );
			$config['enabled'] = ! $current;
			$this->storage->save_taxonomy( $config );
			$this->mark_rewrite_flush();
			$this->redirect( $config['enabled'] ? 'taxonomy_enabled' : 'taxonomy_disabled', [], 'wem-content-types-taxonomies' );
		}

		$this->redirect( 'taxonomy_not_found', [], 'wem-content-types-taxonomies' );
	}

	public function export_config(): void {
		$this->guard_action();
		check_admin_referer( 'wem_ct_export_config' );

		$json = $this->transfer->export_json();
		if ( '' === $json ) {
			$this->redirect(
				'export_error',
				[ 'error' => __( 'The configuration could not be encoded as JSON.', 'wem-content-types' ) ],
				'wem-content-types-tools'
			);
		}

		$filename = 'wem-content-types-' . gmdate( 'Y-m-d' ) . '.json';

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'X-Content-Type-Options: nosniff' );

		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Downloaded JSON is generated by wp_json_encode().
		exit;
	}

	public function import_config(): void {
		$this->guard_action();
		check_admin_referer( 'wem_ct_import_config' );

		$file = isset( $_FILES['wem_ct_import_file'] ) && is_array( $_FILES['wem_ct_import_file'] )
			? $_FILES['wem_ct_import_file']
			: [];

		$error = $this->validate_import_file( $file );
		if ( is_wp_error( $error ) ) {
			$this->redirect(
				'import_error',
				[ 'error' => implode( ' ', $error->get_error_messages() ) ],
				'wem-content-types-tools'
			);
		}

		$json = file_get_contents( $file['tmp_name'] );
		if ( false === $json ) {
			$this->redirect(
				'import_error',
				[ 'error' => __( 'The uploaded JSON file could not be read.', 'wem-content-types' ) ],
				'wem-content-types-tools'
			);
		}

		$result = $this->transfer->import_json( $json );
		if ( is_wp_error( $result ) ) {
			$messages = array_slice( $result->get_error_messages(), 0, 8 );
			if ( count( $result->get_error_messages() ) > 8 ) {
				$messages[] = __( 'Additional validation errors were omitted. Fix the JSON structure and try again.', 'wem-content-types' );
			}

			$this->redirect(
				'import_error',
				[ 'error' => implode( ' ', $messages ) ],
				'wem-content-types-tools'
			);
		}

		$this->mark_rewrite_flush();
		$this->redirect(
			'import_success',
			[
				'pt_added'    => (int) $result['post_types']['added'],
				'pt_updated'  => (int) $result['post_types']['updated'],
				'tax_added'   => (int) $result['taxonomies']['added'],
				'tax_updated' => (int) $result['taxonomies']['updated'],
			],
			'wem-content-types-tools'
		);
	}

	private function validate_import_file( array $file ) {
		$errors = new \WP_Error();
		$upload_error = isset( $file['error'] ) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;

		if ( UPLOAD_ERR_OK !== $upload_error ) {
			$errors->add( 'upload_error', $this->upload_error_message( $upload_error ) );
			return $errors;
		}

		$size = isset( $file['size'] ) ? (int) $file['size'] : 0;
		if ( $size <= 0 ) {
			$errors->add( 'empty_file', __( 'Choose a non-empty WEM JSON file to import.', 'wem-content-types' ) );
		}
		if ( $size > Transfer::MAX_IMPORT_BYTES ) {
			$errors->add( 'file_too_large', __( 'The import file is too large. WEM configuration files must be 1 MB or smaller.', 'wem-content-types' ) );
		}

		$name = sanitize_file_name( $file['name'] ?? '' );
		if ( 'json' !== strtolower( pathinfo( $name, PATHINFO_EXTENSION ) ) ) {
			$errors->add( 'file_type', __( 'Choose a .json file exported by WEM Content Types.', 'wem-content-types' ) );
		}

		$tmp_name = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';
		if ( '' === $tmp_name || ! is_uploaded_file( $tmp_name ) ) {
			$errors->add( 'invalid_upload', __( 'WordPress could not verify the uploaded file.', 'wem-content-types' ) );
		}

		return $errors->has_errors() ? $errors : true;
	}

	private function upload_error_message( int $error ): string {
		$messages = [
			UPLOAD_ERR_INI_SIZE   => __( 'The uploaded file exceeds the server upload limit.', 'wem-content-types' ),
			UPLOAD_ERR_FORM_SIZE  => __( 'The uploaded file exceeds the allowed form size.', 'wem-content-types' ),
			UPLOAD_ERR_PARTIAL    => __( 'The JSON file was only partially uploaded. Please try again.', 'wem-content-types' ),
			UPLOAD_ERR_NO_FILE    => __( 'Choose a WEM JSON file to import.', 'wem-content-types' ),
			UPLOAD_ERR_NO_TMP_DIR => __( 'The server is missing a temporary upload directory.', 'wem-content-types' ),
			UPLOAD_ERR_CANT_WRITE => __( 'The server could not write the uploaded file.', 'wem-content-types' ),
			UPLOAD_ERR_EXTENSION  => __( 'A server extension stopped the file upload.', 'wem-content-types' ),
		];

		return $messages[ $error ] ?? __( 'The JSON file could not be uploaded.', 'wem-content-types' );
	}

	/**
	 * Return WEM taxonomy labels that still reference a post type.
	 *
	 * Keeping this integrity check in the admin layer prevents deleting a post
	 * type definition while saved taxonomy configuration still depends on it.
	 */
	private function taxonomies_attached_to_post_type( string $post_type ): array {
		$labels = [];

		foreach ( $this->storage->all_taxonomies() as $slug => $config ) {
			if ( ! is_array( $config ) ) {
				continue;
			}

			$object_types = isset( $config['object_types'] ) && is_array( $config['object_types'] )
				? array_map( 'sanitize_key', $config['object_types'] )
				: [];

			if ( ! in_array( $post_type, $object_types, true ) ) {
				continue;
			}

			$label = sanitize_text_field( $config['plural_label'] ?? $slug );
			$labels[] = $label . ' (' . sanitize_key( $slug ) . ')';
		}

		return $labels;
	}

	private function guard(): void {
		if ( ! current_user_can( $this->capability() ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wem-content-types' ) );
		}
	}

	private function guard_action(): void {
		if ( ! current_user_can( $this->capability() ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'wem-content-types' ) );
		}
	}

	private function mark_rewrite_flush(): void {
		update_option( 'wem_ct_needs_flush', 1, false );
	}

	private function render_notice(): void {
		$message = sanitize_key( wp_unslash( $_GET['wem_ct_message'] ?? '' ) );
		$error   = sanitize_text_field( wp_unslash( $_GET['error'] ?? '' ) );
		$pt_added    = absint( $_GET['pt_added'] ?? 0 );
		$pt_updated  = absint( $_GET['pt_updated'] ?? 0 );
		$tax_added   = absint( $_GET['tax_added'] ?? 0 );
		$tax_updated = absint( $_GET['tax_updated'] ?? 0 );

		$messages = [
			'post_type_created'   => __( 'Post type created successfully.', 'wem-content-types' ),
			'post_type_updated'   => __( 'Post type updated successfully.', 'wem-content-types' ),
			'post_type_deleted'   => __( 'Post type configuration deleted. Existing content in the database has not been deleted.', 'wem-content-types' ),
			'post_type_enabled'   => __( 'Post type enabled. WordPress will register it again on the next request.', 'wem-content-types' ),
			'post_type_disabled'  => __( 'Post type disabled. Its configuration and existing content remain stored.', 'wem-content-types' ),
			'post_type_not_found' => __( 'The requested post type configuration was not found.', 'wem-content-types' ),
			'post_type_in_use'     => $error,
			'taxonomy_created'    => __( 'Taxonomy created successfully.', 'wem-content-types' ),
			'taxonomy_updated'    => __( 'Taxonomy updated successfully.', 'wem-content-types' ),
			'taxonomy_deleted'    => __( 'Taxonomy configuration deleted. Existing term relationships in the database have not been deleted.', 'wem-content-types' ),
			'taxonomy_enabled'    => __( 'Taxonomy enabled. WordPress will register it again on the next request.', 'wem-content-types' ),
			'taxonomy_disabled'   => __( 'Taxonomy disabled. Its configuration, terms, and relationships remain stored.', 'wem-content-types' ),
			'taxonomy_not_found'  => __( 'The requested taxonomy configuration was not found.', 'wem-content-types' ),
			'import_success'      => sprintf(
				__( 'Import completed. Post Types: %1$d added, %2$d updated. Taxonomies: %3$d added, %4$d updated.', 'wem-content-types' ),
				$pt_added,
				$pt_updated,
				$tax_added,
				$tax_updated
			),
			'import_error'        => $error,
			'export_error'        => $error,
			'validation_error'    => $error,
		];

		if ( ! $message || empty( $messages[ $message ] ) ) {
			return;
		}

		$class = in_array( $message, [ 'validation_error', 'import_error', 'export_error', 'post_type_in_use' ], true ) || false !== strpos( $message, 'not_found' )
			? 'notice notice-error'
			: 'notice notice-success is-dismissible';

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $messages[ $message ] ) );
	}

	private function redirect( string $message, array $extra = [], string $page = 'wem-content-types' ): void {
		$args = array_merge(
			[
				'page'           => $page,
				'wem_ct_message' => $message,
			],
			array_filter( $extra, static fn( $value ) => '' !== $value && null !== $value )
		);

		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}
}
