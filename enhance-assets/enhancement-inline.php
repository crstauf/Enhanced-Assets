<?php
/**
 * Enhancement: inline the asset's contents.
 */

defined( 'ABSPATH' ) || die();

/**
 * Class: EnhanceAssets_InlineEnhancement
 */
class EnhanceAssets_InlineEnhancement extends EnhanceAssets_Enhancement {

	const KEY = 'inline';

	/**
	 * @var null|string Path of asset's file.
	 */
	protected $path;

	/**
	 * Determine path of asset from URL.
	 *
	 * @uses EnhanceAssets::get_asset()
	 */
	protected function determine_path() {
		$asset = EnhanceAssets::get_asset( $this->handle, $this->is_script );
		$path = untrailingslashit( ABSPATH ) . str_replace( site_url(), '', $asset->src );

		if ( !file_exists( $path ) ) {
			trigger_error( sprintf( 'Unable to find asset file at <code>%s</code>.', $path ) );
			return;
		}

		$this->path = $path;
	}

	/**
	 * Replace tag with inlined file contents.
	 *
	 * @param string $tag
	 * @uses $this->determine_path()
	 * @uses $this->get_contents()
	 * @return string
	 */
	function _enhance( string $tag ) {
		$this->determine_path();

		if ( empty( $this->path ) )
			return $tag;

		$contents = $this->get_contents();

		if ( empty( $contents ) ) {
			trigger_error( sprintf( 'Unable to get contents of <code>%s</code>.', $this->path ) );
			return $tag;
		}

		$tag = sprintf( '<%s type="%s" inlined="%s">%s</%s>',
			$this->is_script ? 'script' : 'style',
			esc_attr( $this->is_script ? 'text/javascript' : 'text/css' ),
			esc_attr( $this->handle ),
			"\n" . trim( $contents ) . "\n",
			$this->is_script ? 'script' : 'style'
		);

		return $tag;
	}

	/**
	 * Get file's contents.
	 *
	 * @return string
	 */
	protected function get_contents() {
		return file_get_contents( $this->path );
	}

}

EnhanceAssets_Enhancements::add( EnhanceAssets_InlineEnhancement::KEY, EnhanceAssets_InlineEnhancement::class );

?>