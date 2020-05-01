<?php
/**
 * Enhancement: add "async" attribute to tag.
 */

defined( 'ABSPATH' ) || die();

/**
 * Class: EnhanceAssets_AsyncEnhancement
 */
class EnhanceAssets_AsyncEnhancement extends EnhanceAssets_Enhancement {

	const KEY = 'async';

	/**
	 * Add "async" attribute to tag.
	 *
	 * @param string $tag
	 * @return string
	 */
	protected function _enhance( string $tag ) {
		if ( !$this->is_script )
			return $tag;

		return str_replace( '<script ', '<script async ', $tag );
	}

}

EnhanceAssets_Enhancements::add( EnhanceAssets_AsyncEnhancement::KEY, EnhanceAssets_AsyncEnhancement::class );

?>