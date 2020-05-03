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
	
	protected $default_args = array(
		'header' => true,
		'link'   => true,
		'always' => false,
	);

	/**
	 * @var bool $pushed Indicate if asset was preloaded.
	 */
	protected $preloaded = false;

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

		if ( 
			$this->args['header']
			&& !did_action( 'send_headers' ) 
		) {
			add_action( 'send_headers', array( $this, 'action__send_headers' ) );
			return;
		}

		if ( !$this->args['link'] )
			return;

		if ( 
			     !did_action( 'wp_head' ) 
			&& !doing_action( 'wp_head' )
		) {
			add_action( 'wp_head', array( $this, 'action__wp_head' ), 0 );
			return;
		}

		trigger_error( sprintf( 'Too late to apply <code>%s</code> enhancement to <code>%s</code> %s.', static::KEY, $handle, $is_script ? 'script' : 'stylesheet' ) );
	}

	/**
	 * Action: send_headers
	 *
	 * Add the preload header.
	 *
	 * @uses $this->is_asset_enqueued()
	 * @uses EnhanceAssets::get_asset()
	 * @uses $this->get_asset_url()
	 * @return array
	 */
	function action__send_headers() {
		if ( !$this->args['push'] )
			return;
			
		if (
			   !$this->args['always']
			&& !$this->is_asset_enqueued()
		)
			return;
			
		$asset = EnhanceAssets::get_asset( $this->handle, $this->is_script );

		# Confirm enhancement is still set.
		if ( !isset( $asset->extra['enhancements'][static::KEY] ) )
			return;

		$src = $this->get_asset_url();

		header( sprintf( 'Link: <%s>; rel=preload; as=%s', $src, $this->is_script ? 'script' : 'style' ), false );
		$this->preloaded = true;
	}

	/**
	 * Action: wp_head
	 *
	 * Check if already preloaded and still enhanced.
	 */
	function action__wp_head() {
		if ( $this->preloaded )
			return;

		# Confirm enhancement is still set.
		if ( !isset( $asset->extra['enhancements'][static::KEY] ) )
			return;
		
		add_filter( 'wp_resource_hints', array( $this, 'filter__wp_resource_hints' ), 10, 2 );
	}
	
	/**
	 * Filter: wp_resource_hints
	 *
	 * Maybe add preload link.
	 *
	 * @see wp_resource_hints()
	 * @param string[] $urls
	 * @param string $type
	 * @uses $this->is_asset_enqueued()
	 * @uses $this->get_asset_url()
	 * @return string[]
	 */
	function filter__wp_resource_hints( $urls, $type ) {
		if ( 'preload' !== $type )
			return $urls;
			
		if (
			   !$this->args['always']
			&& !$this->is_asset_enqueued()
		)
			return;
		
		$urls[] = $this->get_asset_url();
		$this->preloaded = true;
		
		return $urls;
	}

	/**
	 * Add attribute to tag to indicate pushed.
	 *
	 * @param string $tag
	 * @return string
	 */
	protected function _enhance( string $tag ) {
		if ( !$this->preloaded )
			return $tag;

		$tag = str_replace( array( '<script ', '<link ' ), array( '<script pushed ', '<link pushed ' ), $tag );
		return $tag;
	}

}

EnhanceAssets_Enhancements::add( EnhanceAssets_PushEnhancement::KEY, EnhanceAssets_PushEnhancement::class );

?>