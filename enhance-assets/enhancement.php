<?php
/**
 * Abstract class for enhancements.
 */

defined( 'ABSPATH' ) || die();

/**
 * Abstract class: EnhanceAssets_Enhancement
 */
abstract class EnhanceAssets_Enhancement {

	/**
	 * @var string $handle
	 * @var bool $is_script
	 * @var array $args
	 */
	protected $handle;
	protected $is_script;
	public $args;

	/**
	 * Construct.
	 *
	 * @param string $handle
	 * @param bool $is_script
	 * @param array $args
	 */
	function __construct( string $handle, bool $is_script, array $args = array() ) {
		$this->handle = $handle;
		$this->is_script = $is_script;
		$this->args = $args;
	}

	/**
	 * Add enhancement to tag.
	 *
	 * @param mixed $tag
	 * @uses $this->_enhance()
	 * @return mixed
	 */
	function enhance( $tag ) {
		if ( empty( $tag ) )
			return $tag;

		return $this->_enhance( $tag ) . "\n";
	}

	/**
	 * Add enhancement to loader tag.
	 *
	 * @param string $tag
	 * @return string $tag
	 */
	abstract protected function _enhance( string $tag );

}

?>