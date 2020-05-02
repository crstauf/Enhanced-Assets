<?php
/**
 * Abstract class for enhancements.
 */

defined( 'ABSPATH' ) || die();

/**
 * Abstract class: EnhanceAssets_Enhancement
 */
abstract class EnhanceAssets_Enhancement {

	/**
	 * @var string $handle
	 * @var bool $is_script
	 * @var array $args
	 */
	protected $handle;
	protected $is_script;
	public $args;

	protected $default_args = array();

	/**
	 * Construct.
	 *
	 * @param string $handle
	 * @param bool $is_script
	 * @param array $args
	 */
	function __construct( string $handle, bool $is_script, array $args = array() ) {
		$this->handle = $handle;
		$this->is_script = $is_script;
		$this->args = wp_parse_args( $args, $this->default_args );
	}

	/**
	 * Check asset status.
	 *
	 * @param string $status
	 * @uses wp_script_is()
	 * @uses wp_style_is()
	 * @return bool
	 */
	protected is_asset_enqueued( string $status = 'enqueued' ) {
		return $this->is_script
			? wp_script_is( $this->handle, $status )
			: wp_style_is(  $this->handle, $status );
	}

	/**
	 * Get asset's source URL.
	 *
	 * @uses EnhanceAssets::get_asset()
	 * @uses wp_scripts()
	 * @uses wp_styles()
	 * @see WP_Dependencies::do_item()
	 * @return string
	 */
	protected function get_asset_url() {
		$asset = EnhanceAssets::get_asset( $this->handle, $this->is_script );

		$src = $asset->src;
		$object = $this->is_script ? wp_scripts() : wp_styles();
		$ver = '';

		if ( null !== $asset->ver )
			$ver = $asset->ver ? $asset->ver : $object->default_version;

		if ( isset( $object->args[ $this->handle ] ) )
			$ver = $ver ? $ver . '&amp;' . $object->args[ $this->handle ] : $object->args[ $this->handle ];

		return add_query_arg( 'ver', $ver, $src );
	}

	/**
	 * Alias for get_asset_url().
	 *
	 * @uses $this->get_asset_url()
	 */
	protected function get_asset_src() {
		return $this->get_asset_url();
	}

	/**
	 * Add enhancement to tag.
	 *
	 * @param mixed $tag
	 * @uses $this->_enhance()
	 * @return mixed
	 */
	function enhance( $tag ) {
		if ( empty( $tag ) )
			return $tag;

		return $this->_enhance( $tag ) . "\n";
	}

	/**
	 * Add enhancement to loader tag.
	 *
	 * @param string $tag
	 * @return string $tag
	 */
	abstract protected function _enhance( string $tag );

}

?>