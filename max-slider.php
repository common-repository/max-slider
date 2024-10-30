<?php

/**
 * Plugin Name: Max Slider for Elementor
 * Description: Build Elementor sliders using the Max Slider and Elementor Builder. 25+ prebuild sliders are included in this plugin (pro). Many slider effects, Custom breakpoints, and many other variations like arrows and paginations.
 * Plugin URI:  https://wordpress.org/plugins/max-slider/
 * Version:     1.3.0
 * Author:      Maxech
 * Author URI:  https://maxech.com
 * License:     GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: max-slider
 * Domain Path: /languages
 * Elementor tested up to: 3.15
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Main Elementor Hello World Class
 *
 * The init class that runs the Hello World plugin.
 * Intended To make sure that the plugin's minimum requirements are met.
 *
 * You should only modify the constants to match your plugin's needs.
 *
 * Any custom code should go inside Plugin Class in the plugin.php file.
 * @since 1.2.0
 */
final class Max_Slider
{

	/**
	 * Plugin Version
	 *
	 * @since 1.2.1
	 * @var string The plugin version.
	 */
	const VERSION = '1.0.0';

	/**
	 * Minimum Elementor Version
	 *
	 * @since 1.2.0
	 * @var string Minimum Elementor version required to run the plugin.
	 */
	const MINIMUM_ELEMENTOR_VERSION = '3.0.0';

	/**
	 * Minimum PHP Version
	 *
	 * @since 1.2.0
	 * @var string Minimum PHP version required to run the plugin.
	 */
	const MINIMUM_PHP_VERSION = '7.0';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct()
	{

		// Init Plugin
		add_action('plugins_loaded', array($this, 'init'));
	}

	/**
	 * Initialize the plugin
	 *
	 * Validates that Elementor is already loaded.
	 * Checks for basic plugin requirements, if one check fail don't continue,
	 * if all check have passed include the plugin class.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function init()
	{

		// Check if Elementor installed and activated
		if (!did_action('elementor/loaded')) {
			add_action('admin_notices', array($this, 'admin_notice_missing_main_plugin'));
			return;
		}

		// Check for required Elementor version
		if (!version_compare(ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=')) {
			add_action('admin_notices', array($this, 'admin_notice_minimum_elementor_version'));
			return;
		}

		// Check for required PHP version
		if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
			add_action('admin_notices', array($this, 'admin_notice_minimum_php_version'));
			return;
		}

        include('inc/slides.php');
        include('inc/controls/Select2.php');
        include('inc/controls/helper.php');
        include('inc/max-slider-select2.php');
        include('inc/max-slider-importer.php');

		// Once we get here, We have passed all validation checks so we can safely include our plugin
		require_once('plugin.php');

        //add new category elementor
        add_action('elementor/init', function () {
            $elementsManager = Elementor\Plugin::instance()->elements_manager;
            $elementsManager->add_category(
                'max-slider-widgets',
                array(
                    'title' => 'Max Slider',
                    'icon'  => 'font',
                ),
                2
            );
        });
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor installed or activated.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_notice_missing_main_plugin()
	{
		if (isset($_GET['activate'])) {
			unset($_GET['activate']);
		}

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'max-slider'),
			'<strong>' . esc_html__('Elementor Hello World', 'max-slider') . '</strong>',
			'<strong>' . esc_html__('Elementor', 'max-slider') . '</strong>'
		);

		printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required Elementor version.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version()
	{
		if (isset($_GET['activate'])) {
			unset($_GET['activate']);
		}

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'max-slider'),
			'<strong>' . esc_html__('Elementor Hello World', 'max-slider') . '</strong>',
			'<strong>' . esc_html__('Elementor', 'max-slider') . '</strong>',
			self::MINIMUM_ELEMENTOR_VERSION
		);

		printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
	}

    /**
     * Activation hook
     *
     * @since 1.0.0
     * @access public
     */
    public static function activate() {
        $cpt_support = get_option('elementor_cpt_support', array()); // Get the current elementor_cpt_support items
        $cpt_support[] = 'max_slides'; // Add max_slides to the array
        update_option('elementor_cpt_support', $cpt_support); // Update the option
    }

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_notice_minimum_php_version()
	{
		if (isset($_GET['activate'])) {
			unset($_GET['activate']);
		}

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'max-slider'),
			'<strong>' . esc_html__('Elementor Hello World', 'max-slider') . '</strong>',
			'<strong>' . esc_html__('PHP', 'max-slider') . '</strong>',
			self::MINIMUM_PHP_VERSION
		);

		printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
	}
}
// Register the activation hook
register_activation_hook(__FILE__, array('Max_Slider', 'activate'));

// Instantiate Max_Slider.
new Max_Slider();
