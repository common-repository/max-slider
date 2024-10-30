<?php
namespace MaxSliderAddon;

use MaxSliderAddon\PageSettings\Page_Settings;

define( 'VERSION', '2.0.4' );
define( 'MAX_SLIDER__FILE__', __FILE__ );
define( 'MAX_SLIDER_URL', plugins_url( '/', MAX_SLIDER__FILE__ ) );
define( 'MAX_SLIDER_PLUGIN_BASE', plugin_basename( MAX_SLIDER__FILE__ ) );
define( 'MAX_SLIDER_BADGE', '<span class="max-slider-badge"></span>');

/**
 * Class Plugin
 *
 * Main Plugin class
 * @since 1.2.0
 */
class Max_Slider_Plugin {

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 *
	 * @var Plugin The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Plugin An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * widget_scripts
	 *
	 * Load required plugin core files.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function widget_scripts() {
		
		add_action( 'elementor/frontend/after_register_scripts', function() {

			//swiper slider
			wp_register_script( 'max-slider-swiper', plugins_url( '/assets/js/swiper-bundle.min.js', __FILE__ ), [ 'jquery' ], false, true ); 
			
			//max slider
			wp_register_script( 'max-slider', plugins_url( '/assets/js/max-slider.js', __FILE__ ), [ 'jquery' ], false, true );
		
		});

		
		add_action( 'elementor/frontend/after_enqueue_styles', function() {

			//swiper slider
			wp_enqueue_style( 'max-slider-swiper-style', plugins_url( '/assets/css/swiper-bundle.min.css', __FILE__ ), array(), '', 'all');
		
			//max slider style
			wp_enqueue_style( 'max-slider-style', plugins_url( '/assets/css/style.css', __FILE__ ), array(), '', 'all');

		} );

		//max slide editor
		add_action( 'elementor/editor/after_enqueue_scripts', function() {
			wp_enqueue_script( 'max-slide-editor', plugins_url( '/assets/js/max-slide-editor.js', __FILE__ ), [ 'jquery' ], false, true );

			wp_add_inline_script( 'max-slide-editor', 'const maxSlider = ' . json_encode( array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'siteUrl' => get_option('siteurl'),
			) ), 'before' );
		});
		
		//max slider editor
		add_action( 'elementor/editor/after_enqueue_styles', function() {
			wp_enqueue_style( 'max-slider-editor-style', plugins_url( '/assets/css/editor-style.css', __FILE__ ), array(), '', 'all');
		});
	}

	/**
	 * Register Widgets
	 *
	 * Register new Elementor widgets.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets( $widgets_manager ) {
		// Its is now safe to include Widgets files
		require_once( __DIR__ . '/widgets/max-slider.php' );

		// Register Widgets
		$widgets_manager->register( new Widgets\Max_slider() );
	}

	/**
	 *  Plugin class constructor
	 *
	 * Register plugin action hooks and filters
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {

		// Register widget scripts
		$this->widget_scripts();

		// Register widgets
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

	}
}

// Instantiate Plugin Class
Max_Slider_Plugin::instance();
