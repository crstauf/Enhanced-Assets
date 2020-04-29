<?php
/**
 * Enhancement: HTTP2 server push.
 */

defined( 'ABSPATH' ) || die();

/**
 * Class: EnhanceAssets_PushEnhancement
 */
class EnhanceAssets_PushEnhancement extends EnhanceAssets_Enhancement {

	/**
	 * @var bool $pushed Indicate if header was set.
	 */
	protected $pushed = false;

	/**
	 * Construct.
	 *
	 * @param string $handle
	 * @param bool $is_script
	 * @param null|array $args
	 * @uses EnhanceAssets_Enhancement::__construct()
	 */
	function __construct( string $handle, bool $is_script, array $args = array() ) {
		parent::__construct( $handle, $is_script, $args );

		if ( !did_action( 'send_headers' ) )
			add_action( 'send_headers', array( $this, 'action__send_headers' ) );
	}

	/**
	 * Action: send_headers
	 *
	 * Add the preload header.
	 *
	 * @param array $headers
	 * @uses EnhanceAssets::get_asset()
	 * @uses wp_scripts()
	 * @uses wp_styles()
	 * @return array
	 */
	function action__send_headers() {
		$asset = EnhanceAssets::get_asset( $this->handle, $this->is_script );

		# Confirm enhancement is still set.
		if ( !isset( $asset->extra['enhancements']['push'] ) )
			return;

		$src = $asset->src;
		$object = $this->is_script ? wp_scripts() : wp_styles();
		$ver = '';

		if ( null !== $asset->ver )
			$ver = $asset->ver ? $asset->ver : $object->default_version;

		if ( isset( $object->args[ $this->handle ] ) )
			$ver = $ver ? $ver . '&amp;' . $object->args[ $this->handle ] : $object->args[ $this->handle ];

		$src = add_query_arg( 'ver', $ver, $src );

		header( sprintf( 'Link: <%s>; rel=preload; as=%s', $src, $this->is_script ? 'script' : 'style' ), false );
		$this->pushed = true;
	}

	/**
	 * Add attribute to tag to indicate pushed.
	 *
	 * @param string $tag
	 * @return string
	 */
	protected function _enhance( $tag ) {
		if ( !$this->pushed )
			return $tag;

		$tag = str_replace( array( '<script ', '<link ' ), array( '<script pushed ', '<link pushed ' ), $tag );
		return $tag;
	}

}