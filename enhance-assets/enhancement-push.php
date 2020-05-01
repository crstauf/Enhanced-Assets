<?php
/**
 * Enhancement: HTTP2 server push (key: "push").
 */

defined( 'ABSPATH' ) || die();

/**
 * Class: EnhanceAssets_PushEnhancement
 */
class EnhanceAssets_PushEnhancement extends EnhanceAssets_Enhancement {

	const KEY = 'push';

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
			trigger_error( sprintf( 'Too late to apply <code>%s</code> enhancement to <code>%s</code> %s.', static::KEY, $handle, $is_script ? 'script' : 'stylesheet' ) );
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
		if ( !isset( $asset->extra['enhancements'][static::KEY] ) )
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
	protected function _enhance( string $tag ) {
		if ( !$this->pushed )
			return $tag;

		$tag = str_replace( array( '<script ', '<link ' ), array( '<script pushed ', '<link pushed ' ), $tag );
		return $tag;
	}

}

EnhanceAssets_Enhancements::add( EnhanceAssets_PushEnhancement::KEY, EnhanceAssets_PushEnhancement::class );

?>