<?php
/**
 * Enhancement: add prefetch link (key: "prefetch").
 */

defined( 'ABSPATH' ) || die();

/**
 * Class: EnhanceAssets_PrefetchEnhancement
 */
class EnhanceAssets_PrefetchEnhancement extends EnhanceAssets_Enhancement {

	const KEY = 'prefetch';

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
	 * Add prefetch link.
	 *
	 * @uses EnhanceAssets::get_assets()
	 * @uses $this->get_asset_url()
	 */
	function action__wp_head() {
		$asset = EnhanceAssets::get_asset( $this->handle );

		# Confirm enhancement is still set.
		if ( !isset( $asset->extra['enhancements']['prefetch'] ) )
			return;

		printf( '<link rel="prefetch" href="%s" />', $this->get_asset_url() );
	}

	/**
	 * Add attribute to loader tag.
	 *
	 * @param string $tag
	 * @return string $tag
	 */
	protected function _enhance( string $tag ) {
		return str_replace( array(
			'<script ',
			'<link ',
		), array(
			'<script prefetched ',
			'<link prefetched ',
		), $tag );
	}

}

EnhanceAssets_Enhancements::add( EnhanceAssets_PrefetchEnhancement::KEY, EnhanceAssets_PrefetchEnhancement::class );

?>