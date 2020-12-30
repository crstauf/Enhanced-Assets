<?php
/**
 * Enhancement: add preconnect link.
 *
 * @todo add sending via header (https://www.igvita.com/2015/08/17/eliminating-roundtrips-with-preconnect/)
 */

defined( 'ABSPATH' ) || die();

/**
 * Class: EnhanceAssets_PreconnectEnhancement
 */
class EnhanceAssets_PreconnectEnhancement extends EnhanceAssets_Enhancement {

	const KEY = 'preconnect';

	protected $default_args = array(
		'always' => false,
	);

	/**
	 * Construct.
	 *
	 * @param string $handle
	 * @param bool $is_script
	 * @param array $args
	 */
	function __construct( string $handle, bool $is_script, array $args = array() ) {
		parent::__construct( $handle, $is_script, $args );

		add_filter( 'wp_resource_hints', array( $this, 'filter__wp_resource_hints' ), 10, 2 );
	}

	/**
	 * Filter: wp_resource_hints
	 *
	 * Add preconnect link.
	 *
	 * @uses EnhanceAssets::get_asset()
	 * @uses $this->get_asset_url()
	 */
	function filter__wp_resource_hints( array $urls, string $type ) {
		if ( 'preconnect' !== $type )
			return $urls;

		$asset = EnhanceAssets::get_asset( $this->handle, $this->is_script );

		# Confirm enhancement is still set.
		if ( !isset( $asset->extra['enhancements'][static::KEY] ) )
			return $urls;

		if ( false !== stripos( $this->get_asset_url(), site_url() ) ) {
			trigger_error( sprintf( 'Cannot apply <code>%s</code> enhancement to asset <code>%s</code> on own domain.', static::KEY, $this->handle ) );
			return $urls;
		}

		$parsed_url = parse_url( $this->get_asset_url() );

		if ( empty( $parsed_url['scheme'] ) ) {
			trigger_error( sprintf( 'Cannot apply <code>%s</code> enhancement to asset <code>%s</code> on unknown domain.', static::KEY, $this->handle ) );
			return $urls;
		}

		$enqueued = $this->is_script
			? wp_script_is( $this->handle )
			: wp_style_is(  $this->handle );

		if (
			!$this->args['always']
			&& !$enqueued
		)
			return $urls;

		$urls[] = sprintf( '%s://%s', $parsed_url['scheme'], $parsed_url['host'] );

		return $urls;
	}

	/**
	 * Add attribute to loader tag.
	 *
	 * @param string $tag
	 * @return string $tag
	 */
	protected function _enhance( string $tag ) {
		return $tag;
	}

}

EnhanceAssets_Enhancements::add( EnhanceAssets_PreconnectEnhancement::KEY, EnhanceAssets_PreconnectEnhancement::class );

?>