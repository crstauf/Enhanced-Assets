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

	protected $default_args = array(
		'always' => false,
	);

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

		if (
			     did_action( 'wp_head' )
			|| doing_action( 'wp_head' )
		) {
			trigger_error( sprintf( 'Too late to apply <code>%s</code> enhancement to <code>%s</code> %s.', __CLASS__, $handle, $is_script ? 'script' : 'stylesheet' ) );
			return;
		}

		add_action( 'wp_head', array( $this, 'action__wp_head' ), 0 );
	}

	/**
	 * Action: wp_head
	 *
	 * Check if asset is enhanced.
	 *
	 * @uses EnhanceAssets::get_assets()
	 * @uses $this->get_asset_url()
	 */
	function action__wp_head() {
		$asset = EnhanceAssets::get_asset( $this->handle );

		# Confirm enhancement is still set.
		if ( !isset( $asset->extra['enhancements'][static::KEY] ) )
			return;

		add_filter( 'wp_resource_hints', array( $this, 'filter__wp_resource_hints' ), 10, 2 );
	}

	/**
	 * Filter: wp_resource_hints
	 *
	 * Add enhancement.
	 *
	 * @see wp_resource_hints()
	 * @param string[] $urls
	 * @param string $type
	 * @uses $this->is_asset_enqueued()
	 * @uses $this->get_asset_url()
	 * @return string[]
	 */
	function filter__wp_resource_hints( $urls, $type ) {
		if ( 'prefetch' !== $type )
			return $urls;

		if (
			   !$this->args['always']
			&& !$this->is_asset_enqueued()
		)
			return $urls;

		$urls[] = $this->get_asset_url();
		$this->prefetched = true;

		return $urls;
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
			'<script prefetched ',
			'<link prefetched ',
		), $tag );
	}

}

EnhanceAssets_Enhancements::add( EnhanceAssets_PrefetchEnhancement::KEY, EnhanceAssets_PrefetchEnhancement::class );

?>