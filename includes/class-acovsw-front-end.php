<?php

if (!defined('ABSPATH'))
    exit;

class ACOVSW_Front_End
{

    static $cart_error = array();
    /**
     * The single instance of WordPress_Plugin_Template_Settings.
     * @var    object
     * @access  private
     * @since    1.0.0
     */
    private static $_instance = null;
    public $products = false;
    /**
     * The version number.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_version;
    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_token;
    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_url;
    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $file;
    
    private $options;

    /**
     * Check if price has to be display in cart and checkout
     * @var type
     * @var boolean
     * @access private
     * @since 3.4.2
     */
    private $show_price = false;

    function __construct($options, $file = '', $version = '1.0.0')
    {

        $this->_version = $version;
        $this->_token = ACOVSW_TOKEN;
        $this->options = $options;
        // add_action('init', array($this, 'register_acovsw_post_types'));

        if ($this->acovsw_check_woocommerce_active()) {

            // Enqueue Scripts / Styles
            add_action('wp_enqueue_scripts', array ( $this, 'enqueue_styles' ), 99);
            add_action('wp_enqueue_scripts', array ( $this, 'enqueue_scripts'), 99);

            // Custom Styles
            add_action('wp_footer', array ( $this, 'customStyles' ) );   
            
            // Hooks
            add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( $this, 'acovsw_attributes_optionview' ), 1000, 2 );
            add_filter( 'woocommerce_ajax_variation_threshold',array($this,'acovsw_ajax_threshold'), 1000, 2 );
            
        }

    }

    /**
     * Load frontend CSS.
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function enqueue_styles()
    {

        wp_register_style('acovsw-style', plugin_dir_url( __FILE__ ) . '../assets/css/frontend.css', array(), $this->_version);
        wp_enqueue_style('acovsw-style');

    }

    /**
     * Load frontend Javascript.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function enqueue_scripts()
    {

        wp_register_script('acovsw-script', plugin_dir_url( __FILE__ ) . '../assets/js/frontend.js', array(), $this->_version);
        wp_enqueue_script('acovsw-script');

    }

    public function customStyles()
    {

        echo $this->options->customStyles();

    }

    /*

    */
    public function acovsw_attributes_optionview( $view, $args )
    {

        return $this->options->acovswAttributesOptionsView( $view, $args );

    }

    /*

    */
    public function acovsw_ajax_threshold( $value,$prod )
    {

        return $value;

    }

    /**
     * Check if woocommerce plugin is active
     */
    public function acovsw_check_woocommerce_active()
    {

        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            return true;
        }
        if (is_multisite()) {
            $plugins = get_site_option('active_sitewide_plugins');
            if (isset($plugins['woocommerce/woocommerce.php']))
                return true;
        }
        return false;

    }

}
