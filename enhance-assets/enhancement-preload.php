<?php
/**
 * Enhancement: Preload.
 */

defined( 'ABSPATH' ) || die();

/**
 * Class: EnhanceAssets_PushEnhancement
 */
class EnhanceAssets_PreloadEnhancement extends EnhanceAssets_Enhancement {

	const KEY = 'preload';

	protected $default_args = array(
		'header' => false,
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

		if (
			$this->args['link']
			&&   !did_action( 'wp_head' )
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
	 * Maybe add enhancement.
	 *
	 * wp_resource_hints() does not yet support "preload",
	 * so add link tag manually.
	 *
	 * @uses EnhanceAssets::get_asset()
	 * @uses $this->is_asset_enqueued()
	 * @uses $this->get_asset_url()
	 */
	function action__wp_head() {
		if ( $this->preloaded )
			return;

		# Confirm enhancement is still set.
		if ( !isset( EnhanceAssets::get_asset( $this->handle )->extra['enhancements'][static::KEY] ) )
			return;

		if (
			   !$this->args['always']
			&& !$this->is_asset_enqueued()
		)
			return;

		printf( '<link rel="preload" href="%s" />', esc_attr( esc_url( $this->get_asset_url() ) ) );
		$this->preloaded = true;
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

		$tag = str_replace( array(
			'<script ',
			'<link ',
		), array(
			'<script preloaded ',
			'<link preloaded ',
		), $tag );
		return $tag;
	}

}

EnhanceAssets_Enhancements::add( EnhanceAssets_PreloadEnhancement::KEY, EnhanceAssets_PreloadEnhancement::class );

?>