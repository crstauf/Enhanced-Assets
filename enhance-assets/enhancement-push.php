<?php
/**
 * Enhancement: HTTP2 server push (key: "push").
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

		if ( did_action( 'send_headers' ) ) {
			trigger_error( sprintf( 'Too late to apply <code>%s</code> enhancement to <code>%s</code> %s.', __CLASS__, $handle, $is_script ? 'script' : 'stylesheet' ) );
			return;
		}

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

		$src = $this->get_asset_src();

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