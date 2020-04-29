<?php
/**
 * Manager for enhancements.
 *
 * @todo add "nonce" enhancement
 */

defined( 'ABSPATH' ) || die();

/**
 * Class: EnhanceAssets_Enhancements
 */
class EnhanceAssets_Enhancements {

	/**
	 * @var array Built-in enhancements.
	 * array( 'key' => 'class_name' )
	 */
	protected static $native_enhancements = array(
		  'push' => 'EnhanceAssets_PushEnhancement',
		 'async' => 'EnhanceAssets_AsyncEnhancement',
		 'defer' => 'EnhanceAssets_DeferEnhancement',
		'inline' => 'EnhanceAssets_InlineEnhancement',
	);

	/**
	 * Get enhancement class name(s).
	 *
	 * @param null|array|string $keys
	 * @uses static::_get()
	 * @return false|array|string
	 */
	static function get( $keys = null ) {
		static $enhancements = null;

		if ( is_null( $enhancements ) )
			$enhancements = static::_get();

		if ( is_null( $keys ) )
			return $enhancements;

		$_enhancements = array();
		foreach ( ( array ) $keys as $key )
			if ( isset( $enhancements[$key] ) )
				$_enhancements[$key] = $enhancements[$key];

		if ( empty( $_enhancements ) )
			return array();

		if ( is_string( $keys ) )
			return array_pop( $_enhancements );

		return $_enhancements;
	}

	/**
	 * Get enhancement class names.
	 *
	 * @return array
	 */
	protected static function _get() {
		$enhancements = ( array ) apply_filters( 'enhance_assets/enhancements', static::$native_enhancements );

		$enhancements = array_filter( $enhancements, function( string $enhancement ) {
			if ( !class_exists( $enhancement ) )
				return false;

			if ( is_subclass_of( $enhancement, 'EnhanceAssets_Enhancement' ) )
				return true;

			trigger_error( sprintf( '<code>%s</code> must extend <code>%s</code>.', $enhancement, 'EnhanceAssets_Enhancement' ) );
			return false;
		} );

		return $enhancements;
	}

}

?>