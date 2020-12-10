<?php
/**
 * Enhancement: add asynchronous loading to tag.
 */

defined( 'ABSPATH' ) || die();

/**
 * Class: EnhanceAssets_AsyncEnhancement
 */
class EnhanceAssets_AsyncEnhancement extends EnhanceAssets_Enhancement {

	const KEY = 'async';

	/**
	 * Add asynchronous loading to tag.
	 *
	 * For scripts, add the "async" attribute.
	 * For styles, change "media" attribute and add "onload" event.
	 *
	 * @link https://www.filamentgroup.com/lab/load-css-simpler/
	 * @param string $tag
	 * @return string
	 */
	protected function _enhance( string $tag ) {
		if ( $this->is_script )
			return str_replace( '<script ', '<script async ', $tag );
			
		$enhanced = str_replace( 'media=\'all\'', 'media=\'print\' onload="this.media=\'all\'"', $tag );
		return $enhanced . "\n" . '<noscript>' . $tag . '</noscript>';
	}

}

EnhanceAssets_Enhancements::add( EnhanceAssets_AsyncEnhancement::KEY, EnhanceAssets_AsyncEnhancement::class );

?>
