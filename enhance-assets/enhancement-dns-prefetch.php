<?php
/**
 * Enhancement: add dns-prefetch link (key: "dns-prefetch").
 */

defined( 'ABSPATH' ) || die();

/**
 * Class: EnhanceAssets_DNSPrefetchEnhancement
 */
class EnhanceAssets_DNSPrefetchEnhancement extends EnhanceAssets_Enhancement {

	protected $prefetched = false;

	/**
	 * Construct.
	 *
	 * @param string $handle
	 * @param bool $is_script
	 * @param array $args
	 */
	function __construct( string $handle, bool $is_script, array $args = array() ) {
		parent::__construct( $handle, $is_script, $args );

		if ( did_action( 'wp_head' ) ) {
			trigger_error( sprintf( 'Too late to apply <code>%s</code> enhancement to <code>%s</code> %s.', __CLASS__, $handle, $is_script ? 'script' : 'stylesheet' ) );
			return;
		}

		add_action( 'wp_head', array( $this, 'action__wp_head' ) );
	}

	/**
	 * Action: wp_head
	 *
	 * Add dns-prefetch link.
	 *
	 * @uses EnhanceAssets::get_assets()
	 * @uses $this->get_asset_url()
	 *
	 * @todo use parse_url()
	 */
	function action__wp_head() {
		$asset = EnhanceAssets::get_asset( $this->handle );

		# Confirm enhancement is still set.
		if ( !isset( $asset->extra['enhancements']['dns-prefetch'] ) )
			return;

		printf( '<link rel="dns-prefetch" href="%s" />', $this->get_asset_url() );
		$this->prefetched = true;
	}

	/**
	 * Add attribute to loader tag.
	 *
	 * @param string $tag
	 * @return string $tag
	 */
	protected function _enhance( string $tag ) {
		if ( !$this->prefetched )
			return $tag;

		return str_replace( array(
			'<script ',
			'<link ',
		), array(
			'<script dns-prefetched ',
			'<link dns-prefetched ',
		), $tag );
	}

}

?>