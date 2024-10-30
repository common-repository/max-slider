<?php

/**
 * Max Slider Importer Class
 */
class Max_Slider_Importer
{

    /**
     * Plugin version
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * Plugin directory
     *
     * @var string
     */
    private $dir;

    /**
     * Plugin URI
     *
     * @var string
     */
    private $uri;

    /**
     * Plugin basename
     *
     * @var string
     */
    private $basename;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize the plugin
     */
    private function init()
    {
        $this->set_path();
        $this->define_constants();
        $this->enqueue_scripts();
    }

    /**
     * Set the plugin path
     */
    private function set_path()
    {
        $dirname        = wp_normalize_path(dirname(__FILE__));
        $plugin_dir     = wp_normalize_path(WP_PLUGIN_DIR);
        $located_plugin = (preg_match('#' . $plugin_dir . '#', $dirname)) ? true : false;
        $directory      = ($located_plugin) ? $plugin_dir : get_template_directory();
        $directory_uri  = ($located_plugin) ? WP_PLUGIN_URL : get_template_directory_uri();
        $basename       = str_replace(wp_normalize_path($directory), '', $dirname);
        $this->dir      = $directory . $basename;
        $this->uri      = $directory_uri . $basename;
        $this->basename = wp_normalize_path($basename);
    }

    /**
     * Define plugin constants
     */
    private function define_constants()
    {
        define('MAX_SLIDER_IMPORTER_VER', self::VERSION);
        define('MAX_SLIDER_IMPORTER_DIR', $this->dir);
        define('MAX_SLIDER_IMPORTER_URI', $this->uri);
        define('MAX_SLIDER_IMPORTER_CONTENT_DIR', MAX_SLIDER_IMPORTER_DIR . '/demos/');
        define('MAX_SLIDER_IMPORTER_CONTENT_URI', MAX_SLIDER_IMPORTER_URI . '/demos/');
    }

    /**
     * Enqueue scripts and styles for admin
     */
    private function enqueue_scripts()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts()
    {
        wp_enqueue_style('max-slider-importer-css', MAX_SLIDER_IMPORTER_URI . '/assets/css/max-slider-importer.css', null, MAX_SLIDER_IMPORTER_VER);
    }
}

/**
 * Instantiate the plugin
 */
$max_slider_importer = new Max_Slider_Importer();


/**
 * Load Importer ad
 */
/**
 *
 * Framework Class
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class Max_Slider_Demo_Importer
{
    /**
     *
     * option database/data name
     * @access public
     * @var string
     *
     */
    public $opt_id = '_max_slider_importer';
    public $settings = '';
    /**
     *
     * demo items
     * @access public
     * @var array
     *
     */
    public $items = array();
    /**
     *
     * instance
     * @access private
     * @var class
     *
     */
    private static $instance = null;
    // run framework construct
    public function __construct($settings, $items)
    {
        $this->settings = apply_filters('max_slider_importer_settings', $settings);
        $this->items    = apply_filters('max_slider_importer_items', $items);
        if (!empty($this->items)) {

            add_action('admin_menu', array($this, 'admin_menu'), 30, 1);
        }
    }
    // instance
    public static function instance($settings = array(), $items = array())
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($settings, $items);
        }
        return self::$instance;
    }

    // adding option page
    public function admin_menu()
    {
        $defaults_menu_args = array(
            'menu_parent'     => '',
            'menu_title'      => '',
            'menu_type'       => '',
            'menu_slug'       => '',
            'menu_icon'       => '',
            'menu_capability' => 'manage_options',
            'menu_position'   => null,
        );
        $args = wp_parse_args($this->settings, $defaults_menu_args);
        if ($args['menu_type'] == 'add_submenu_page') {
            call_user_func($args['menu_type'], $args['menu_parent'], $args['menu_title'], $args['menu_title'], $args['menu_capability'], $args['menu_slug'], array(&$this, 'admin_page'));
        } else {
            call_user_func($args['menu_type'], $args['menu_title'], $args['menu_title'], $args['menu_capability'], $args['menu_slug'], array(&$this, 'admin_page'), $args['menu_icon'], $args['menu_position']);
        }
    }
    // output demo items
    public function admin_page()
    {
        $nonce = wp_create_nonce('max_slider_importer');
?>
        <div class="wrap max-slider-importer">
            <h2><?php _e('Max Slider Demo Importer', 'max-slider'); ?></h2>
            <div class="max-slider-demo-browser">
                <div class="max-slider-pro-banner">
                    <div class="max-slider-pro-banner-content">
                        <h3>Max Slider Template Library</h3>
                        <p>25+ Templates</p>
                    </div>
                    <a href="https://www.templatemonster.com/wordpress-plugins/max-slider-pro-build-sliders-using-elementor-341319.html?_gl=1*1sl7ddp*_ga*MTY2NjM1NzI0NC4xNjg3MDgxNTkx*_ga_FTPYEGT5LY*MTY5OTAyNTgyNS43OS4xLjE2OTkwMjU4NjcuMTguMC4w" target="_blank">Buy Max Slider Pro Version</a>
                </div>
                <div class="max-slider-demo-item-container">
                    <?php
                    foreach ($this->items as $item => $value) :
                        $opt = get_option($this->opt_id);

                        $imported_class = '';
                        $btn_text = '';
                        $status = '';
                        if (!empty($opt[$item])) {
                            $imported_class = 'imported';
                            $btn_text .= __('Re-Import', 'max-slider');
                            $status .= __('Imported', 'max-slider');
                        } else {
                            $btn_text .= __('Import', 'max-slider');
                            $status .= __('Not Imported', 'max-slider');
                        }
                    ?>
                        <div class="max-slider-demo-item <?php echo esc_attr($imported_class); ?>" data-max-slider-importer>
                            <div class="max-slider-demo-screenshot">
                                <?php
                                $image_url = $value['preview_image'];
                                ?>
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($value['title']); ?>">
                            </div>
                            <div class="max-slider-importer-demo-info">
                                <h2 class="max-slider-demo-name"><?php echo esc_attr($value['title']); ?></h2>
                                <div class="max-slider-demo-actions">
                                    <a class="button button-primary" target="_blank" href="<?php echo esc_url($value['preview_url']); ?>"><?php _e('Preview', 'max-slider'); ?></a>
                                </div>
                            </div>

                            <div class="max-slider-importer-response"><span class="dismiss" title="Dismis this messages.">X</span></div>
                        </div><!-- /.max-slider-demo-item -->
                    <?php endforeach; ?>
                </div>
                <div class="clear"></div>
            </div><!-- /.max-slider-demo-browser -->
        </div><!-- /.wrap -->
<?php
    }
}
