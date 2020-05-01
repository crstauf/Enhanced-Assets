<?php
/**
 * Enhancement: add "defer" attribute.
 */

defined( 'ABSPATH' ) || die();

/**
 * Class: EnhanceAssets_DeferEnhancement
 */
class EnhanceAssets_DeferEnhancement extends EnhanceAssets_Enhancement {

	const KEY = 'defer';

	/**
	 * Add "defer" attribute to tag.
	 *
	 * @param string $tag
	 * @return string
	 */
	protected function _enhance( string $tag ) {
		if ( !$this->is_script )
			return $tag;

		return str_replace( '<script ', '<script defer ', $tag );
	}

}

EnhanceAssets_Enhancements::add( EnhanceAssets_DeferEnhancement::KEY, EnhanceAssets_DeferEnhancement::class );

?>