<?php
/**
 * Plugin name: Enhance Assets
 * Plugin URI: https://github.com/crstauf/enhance-assets
 * Description: Collection of enhancements for WordPress assets.
 * Author: Caleb Stauffer
 * Author URI: https://develop.calebstauffer.com
 * Version: 1.0
 */

defined( 'ABSPATH' ) || die();

/**
 * Class: EnhanceAssets
 */
final class EnhanceAssets {

	/**
	 * Get instance.
	 *
	 * @return self
	 */
	static function instance() {
		static $instance = null;

		if ( is_null( $instance ) )
			$instance = new self;

		return $instance;
	}

	/**
	 * Get asset object.
	 *
	 * @param string $handle
	 * @param bool $is_script
	 * @uses wp_is_script()
	 * @uses wp_is_style()
	 * @uses wp_scripts()
	 * @uses wp_styles()
	 * @return _WP_Dependency
	 */
	static function get_asset( string $handle, bool $is_script = true ) {
		$function_name = $is_script ? 'wp_script_is' : 'wp_style_is';

		if ( !$function_name( $handle, 'registered' ) )
			return false;

		$object = $is_script ? wp_scripts() : wp_styles();

		if ( !isset( $object->registered[$handle] ) )
			return false;

		return $object->registered[$handle];
	}

	/**
	 * Get assets's enhancements.
	 *
	 * @param string $handle
	 * @param bool $is_script
	 * @uses static::get_asset()
	 * @return array
	 */
	static function get( string $handle, bool $is_script = true ) {
		$object = static::get_asset( $handle, $is_script );

		if ( empty( $object ) )
			return array();

		if ( !isset( $object->extra['enhancements'] ) )
			return array();

		return $object->extra['enhancements'];
	}

	/**
	 * Check if asset has enhancement(s).
	 *
	 * @param string $handle
	 * @param bool $is_script
	 * @uses static::get()
	 * @return bool
	 */
	static function has( string $handle, bool $is_script = true ) {
		return !empty( static::get( $handle, $is_script ) );
	}

	/**
	 * Add enhancement to asset.
	 *
	 * @param string $handle
	 * @param string $enhancement
	 * @param null|array $args
	 * @param bool $is_script
	 * @uses EnhanceAssets::get_asset()
	 * @uses EnhanceAssets_Enhancements::get()
	 * @uses EnhanceAssets_Enhancement::__construct()
	 */
	static function enhance( string $handle, string $enhancement, $args = array(), bool $is_script = true ) {
		$object = static::get_asset( $handle, $is_script );

		if ( empty( $object ) ) {
			trigger_error( sprintf( '<code>%s</code> is not a registered %s.', $handle, $is_script ? 'script' : 'stylesheet' ) );
			return;
		}

		if ( !isset( $object->extra['enhancements'] ) )
			$object->extra['enhancements'] = array();

		if ( has_action( 'enhance_assets/enhancement_' . $enhancement . '_pre' ) ) {
			do_action( 'enhance_assets/enhancement_' . $enhancement . '_pre', array( $handle, $is_script, $args ) );
			return;
		}

		$enhancement_class = EnhanceAssets_Enhancements::get( $enhancement );

		if ( empty( $enhancement_class ) ) {
			trigger_error( sprintf( 'Enhancement <code>%s</code> is not available.', $enhancement ) );
			return;
		}

		$object->extra['enhancements'][$enhancement] = new $enhancement_class( $handle, $is_script, $args );
	}

	/**
	 * Construct.
	 *
	 * @uses $this->includes()
	 */
	protected function __construct() {
		$this->includes();

		add_action( 'enhance_assets/enhancement_critical_pre', array( $this, 'action__self__enhancement_critical_pre' ) );

		add_filter( 'script_loader_tag', array( $this, 'filter__script_loader_tag' ), 10, 2 );
		add_filter(  'style_loader_tag', array( $this,  'filter__style_loader_tag' ), 10, 2 );
	}

	/**
	 * Include files.
	 */
	protected function includes() {
		require_once 'enhance-assets/enhancements.php';
		require_once 'enhance-assets/enhancement.php';
		require_once 'enhance-assets/enhancement-async.php';
		require_once 'enhance-assets/enhancement-defer.php';
		require_once 'enhance-assets/enhancement-inline.php';
		require_once 'enhance-assets/enhancement-push.php';
	}

	/**
	 * Action: enhance_assets/enhancement_critical_pre
	 *
	 * Magically handle 'critical' enhancement.
	 *
	 * @param array $params
	 * @uses EnhanceAssets_Enhancement::get()
	 * @uses static::get_asset()
	 */
	function action__self__enhancement_critical_pre( $params ) {
		list( $handle, $is_script, $args ) = $params;

		$enhancements = array();

		if ( !did_action( 'send_headers' ) )
			$enhancements[] = 'push';

		$enhancements[] = 'inline';

		$enhancements = ( array ) apply_filters( 'enhance_assets/critical_enhancements', $enhancements );
		$enhancements = EnhanceAssets_Enhancements::get( $enhancements );

		if ( empty( $enhancements ) ) {
			trigger_error( sprintf( 'No critical enhancements available for <code>%s</code>.', $handle ) );
			return;
		}

		$object = static::get_asset( $handle, $is_script );

		$enhancement_name = current( $enhancements );
		$object->extra['enhancements'][ key( $enhancements ) ] = new $enhancement_name( $handle, $is_script, $args );
	}

	/**
	 * Filter: script_loader_tag
	 *
	 * @param mixed $tag
	 * @param string $handle
	 * @uses static::has()
	 * @uses static::get()
	 * @uses EnhanceAssets_Enhancement::enhance()
	 * @return string
	 */
	function filter__script_loader_tag( $tag, $handle ) {
		if ( !static::has( $handle ) )
			return $tag;

		foreach ( static::get( $handle ) as $enhancement )
			$tag = $enhancement->enhance( $tag );

		return $tag;
	}

	/**
	 * Filter: style_loader_tag
	 *
	 * @param mixed $tag
	 * @param string $handle
	 * @uses static::has()
	 * @uses static::get()
	 * @uses EnhanceAssets_Enhancement::enhance()
	 * @return string
	 */
	function filter__style_loader_tag( $tag, $handle ) {
		if ( !static::has( $handle, false ) )
			return $tag;

		foreach ( static::get( $handle, false ) as $enhancement )
			$tag = $enhancement->enhance( $tag );

		return $tag;
	}

}

if ( !function_exists( 'enhance_asset' ) ) {

	/**
	 * Add enhancement to asset.
	 *
	 * @param string $handle
	 * @param string $enhancement
	 * @param null|array $args
	 * @param bool $is_script
	 * @uses EnhanceAssets::enhance()
	 */
	function enhance_asset( string $handle, string $enhancement, $args = array(), bool $is_script = true ) {
		EnhanceAssets::enhance( $handle, $enhancement, $args, $is_script );
	}

}

if ( !function_exists( 'enhance_script' ) ) {

	/**
	 * Add enhancement to script.
	 *
	 * @param string $handle
	 * @param string $enhancement
	 * @param null|array $args
	 * @uses enhance_asset()
	 */
	function enhance_script( string $handle, string $enhancement, array $args = array() ) {
		enhance_asset( $handle, $enhancement, $args );
	}

}

if ( !function_exists( 'enhance_style' ) ) {

	/**
	 * Add enhancement to stylesheet.
	 *
	 * @param string $handle
	 * @param string $enhancement
	 * @param null|array $args
	 * @uses enhance_asset()
	 */
	function enhance_style( string $handle, string $enhancement, array $args = array() ) {
		enhance_asset( $handle, $enhancement, $args, false );
	}

}

# Initialize.
EnhanceAssets::instance();

?>