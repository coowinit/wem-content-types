<?php
/**
 * Plugin Name: WEM Content Types
 * Description: 一款轻量、独立的 WordPress 内容类型管理插件，用于管理自定义内容类型、分类法、Rewrite 与 JSON 导入导出。
 * Version: 1.0.1
 * Author: WEM
 * Text Domain: wem-content-types
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WEM_CT_VERSION', '1.0.1' );
define( 'WEM_CT_FILE', __FILE__ );
define( 'WEM_CT_PATH', plugin_dir_path( __FILE__ ) );
define( 'WEM_CT_URL', plugin_dir_url( __FILE__ ) );

require_once WEM_CT_PATH . 'includes/class-storage.php';
require_once WEM_CT_PATH . 'includes/class-validator.php';
require_once WEM_CT_PATH . 'includes/class-registry.php';
require_once WEM_CT_PATH . 'includes/class-transfer.php';
require_once WEM_CT_PATH . 'admin/class-admin.php';

register_activation_hook(
	__FILE__,
	static function () {
		update_option( 'wem_ct_needs_flush', 1, false );
	}
);

add_action(
	'plugins_loaded',
	static function () {
		$storage  = new WEM\Content_Types\Storage();
		$registry = new WEM\Content_Types\Registry( $storage );
		$registry->hooks();

		if ( is_admin() ) {
			$validator = new WEM\Content_Types\Validator( $storage );
			$transfer  = new WEM\Content_Types\Transfer( $storage, $validator );
			$admin     = new WEM\Content_Types\Admin\Admin( $storage, $validator, $transfer );
			$admin->hooks();
		}
	}
);
