<?php

if (!defined('ABSPATH'))
    exit;

class ACOVSW_Backend
{

    /**
     * @var    object
     * @access  private
     * @since    1.0.0
     */
    private static $_instance = null;

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
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $file;

    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $dir;

    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_dir;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $script_suffix;

    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_url;
    public $hook_suffix = array();
    public $plugin_slug;

    /**
     * Constructor function.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function __construct($file = '', $version = '1.0.0')
    {
        $this->_version = $version;
        $this->_token = ACOVSW_TOKEN;
        $this->file = $file;
        $this->dir = dirname($this->file);
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));

        $this->plugin_slug = 'abc';

        $this->script_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        if ( $this->acoplw_check_woocommerce_active() ) {

            register_activation_hook($this->file, array($this, 'install'));
            // register_deactivation_hook($this->file, array($this, 'deactivation'));
            add_action('save_post', array($this, 'delete_transient'), 1);
            add_action('edited_term', array($this, 'delete_transient'));
            add_action('delete_term', array($this, 'delete_transient'));
            add_action('created_term', array($this, 'delete_transient'));

            add_action('admin_menu', array($this, 'register_root_page'));
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 10, 1);
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_styles'), 10, 1);

            $plugin = plugin_basename($this->file);
            add_filter("plugin_action_links_$plugin", array($this, 'add_settings_link'));

            // Custom Attributes
            add_filter( 'woocommerce_product_data_tabs', array ( $this,'acovsw_custom_attributes' ) );
            add_action( 'woocommerce_product_data_panels', array ( $this,'acovsw_custom_attributes_settings' ) );

            add_action('admin_footer', array($this, 'vsw_deactivation_form'));

        }
        
    }

    /**
     *
     *
     * Ensures only one instance of WCPA is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see WordPress_Plugin_Template()
     * @return Main WCPA instance
     */
    public static function instance($file = '', $version = '1.0.0')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    }

    public function register_root_page()
    {
        $this->hook_suffix[] = add_menu_page(
            __('Variation Swatches', 'aco-variation-swatches-for-woocommerce'), __('Variation Swatches', 'aco-variation-swatches-for-woocommerce'), 'edit_products', 'acovsw-attributes', array($this, 'admin_ui'), esc_url($this->assets_url) . '/images/icon.png', 25);
        $this->hook_suffix[] = add_submenu_page(
            'acovsw-attributes', __('Settings', 'aco-variation-swatches-for-woocommerce'), __('Settings', 'aco-variation-swatches-for-woocommerce'), 'edit_products', 'acovsw-settings', array($this, 'admin_ui_settings'));
    }

    public function add_settings_link($links)
    {
        $settings = '<a href="' . admin_url('admin.php?page=acovsw-attributes') . '">' . __('Variation Swatches','aco-variation-swatches-for-woocommerce') . '</a>';
        array_push($links, $settings);
        return $links;
    }

    /**
     *    Create post type forms
     */

     static function view($view, $data = array())
    {
        extract($data);
        include(plugin_dir_path(__FILE__) . 'views/' . $view . '.php');
    }

    // End admin_enqueue_styles ()

    public function admin_ui()
    {
        ACOVSW_Backend::view('admin-attributes', []);
    }

    public function admin_ui_settings()
    {
        ACOVSW_Backend::view('admin-settings', []);
    }

    /**
     * Load admin CSS.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function admin_enqueue_styles($hook = '')
    {
                
        $currentScreen = get_current_screen();
        $screenID = $currentScreen->id; //
        if ( strpos($screenID, 'acovsw-') !== false || $screenID == 'product' ) {

            wp_register_style($this->_token . '-admin', esc_url($this->assets_url) . 'css/backend.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-admin');
            
        }
    }

    /**
     * Load admin Javascript.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function admin_enqueue_scripts($hook = '')
    {

        $currentScreen = get_current_screen();
        $screenID = $currentScreen->id; 
        
        if ( strpos($screenID, 'acovsw-') !== false || $screenID == 'product' ) { 

            if (!isset($this->hook_suffix) || empty($this->hook_suffix)) { 
                return;
            }

            // All Categories
            $attributes = wc_get_attribute_taxonomies();
            $attrs = [];
            if ( $attributes ) {
                foreach ( $attributes as $attribute ) {
                    $attr_id = $attribute->attribute_id;
                    $attr_label = $attribute->attribute_label;
                    $attr_name = $attribute->attribute_name;
                    // $attr_tax = 'pa_'.$attr_name;
                    // $options = array ('hide_empty' => false );
                    // $attr = get_terms ( $attr_tax, $options );
                    $attrs[] = array ( 'attr_id' => $attr_id, 'attr_label' => $attr_label, 'attr_name' => $attr_name );
                }
            }

            $screen = get_current_screen();

            wp_enqueue_script('jquery');
            wp_enqueue_media(); // wp.media not a function error fix

            // Preview Box
            $previewImage = plugin_dir_url(__FILE__). '../assets/images/no-image.svg';
            $productID      = '';

            if ( $screenID == 'product' ) {
                global $post;
                $productID = $post->ID;
            }

            if ( in_array($screen->id, $this->hook_suffix) || $screenID == 'product' ) { 

                if (!wp_script_is('wp-i18n', 'registered')) {
                    wp_register_script('wp-i18n', esc_url($this->assets_url) . 'js/i18n.min.js', array('jquery'), $this->_version, true);
                }

                wp_enqueue_script($this->_token . '-backend-script', esc_url($this->assets_url) . 'js/backend.js', array('jquery', 'wp-i18n'), $this->_version, true);
                wp_localize_script($this->_token . '-backend-script', 'acovsw_object', array(
                        'api_nonce' => wp_create_nonce('wp_rest'),
                        'root' => rest_url('acovsw/v1/'),
                        'previewImage' => $previewImage,
                        'attributes' => (array)$attrs,
                        'productID' => $productID
                    )
                );

                $plugin_rel_path = (dirname($this->file)) . '/../../languages'; /* Relative to WP_PLUGIN_DIR */
                if ( ACOVSW_Wordpress_Version >= 5 ) {
                    wp_set_script_translations(ACOVSW_TOKEN . '-backend-script', 'aco-variation-swatches-for-woocommerce', $plugin_rel_path);
                }

            }

        }

        // Deactivation JS
        if ( $screenID == 'plugins' ) {
            wp_enqueue_script('acovsw-deactivation-message', esc_url($this->assets_url).'js/message.js', array());
        }

    }

    // Deactivation Form
    public function vsw_deactivation_form() {
        $currentScreen = get_current_screen();
        $screenID = $currentScreen->id;
        if ( $screenID == 'plugins' ) {
            $view = '<div id="vsw-survey-form-wrap"><div id="vsw-survey-form">
            <p>If you have a moment, please let us know why you are deactivating this plugin. All submissions are anonymous and we only use this feedback for improving our plugin.</p>
            <form method="POST">
                <input name="Plugin" type="hidden" placeholder="Plugin" value="'.ACOVSW_TOKEN.'" required>
                <input name="Version" type="hidden" placeholder="Version" value="'.ACOVSW_VERSION.'" required>
                <input name="Date" type="hidden" placeholder="Date" value="'.date("m/d/Y").'" required>
                <input name="Website" type="hidden" placeholder="Website" value="'.get_site_url().'" required>
                <input name="Title" type="hidden" placeholder="Title" value="'.get_bloginfo( 'name' ).'" required>
                <input type="radio" id="vsw_temporarily" name="Reason" value="I\'m only deactivating temporarily">
                <label for="vsw_temporarily">I\'m only deactivating temporarily</label><br>
                <input type="radio" id="vsw_notneeded" name="Reason" value="I no longer need the plugin">
                <label for="vsw_notneeded">I no longer need the plugin</label><br>
                <input type="radio" id="vsw_short" name="Reason" value="I only needed the plugin for a short period">
                <label for="vsw_short">I only needed the plugin for a short period</label><br>
                <input type="radio" id="vsw_better" name="Reason" value="I found a better plugin">
                <label for="vsw_better">I found a better plugin</label><br>
                <input type="radio" id="vsw_upgrade" name="Reason" value="Upgrading to PRO version">
                <label for="vsw_upgrade">Upgrading to PRO version</label><br>
                <input type="radio" id="vsw_requirement" name="Reason" value="Plugin doesn\'t meets my requirement">
                <label for="vsw_requirement">Plugin doesn\'t meets my requirement</label><br>
                <input type="radio" id="vsw_broke" name="Reason" value="Plugin broke my site">
                <label for="vsw_broke">Plugin broke my site</label><br>
                <input type="radio" id="vsw_stopped" name="Reason" value="Plugin suddenly stopped working">
                <label for="vsw_stopped">Plugin suddenly stopped working</label><br>
                <input type="radio" id="vsw_bug" name="Reason" value="I found a bug">
                <label for="vsw_bug">I found a bug</label><br>
                <input type="radio" id="vsw_other" name="Reason" value="Other">
                <label for="vsw_other">Other</label><br>
                <p id="vsw-error"></p>
                <div class="vsw-comments" style="display:none;">
                    <textarea type="text" name="Comments" placeholder="Please specify" rows="2"></textarea>
                    <p>For support queries <a href="https://support.acowebs.com/portal/en/newticket?departmentId=361181000000006907&layoutId=361181000000074011" target="_blank">Submit Ticket</a></p>
                </div>
                <button type="submit" class="vsw_button" id="vsw_deactivate">Submit & Deactivate</button>
                <a href="#" class="vsw_button" id="vsw_cancel">Cancel</a>
                <a href="#" class="vsw_button" id="vsw_skip">Skip & Deactivate</a>
            </form></div></div>';
            echo $view;
        } ?>
        <style>
            #vsw-survey-form-wrap{ display: none;position: absolute;top: 0px;bottom: 0px;left: 0px;right: 0px;z-index: 10000;background: rgb(0 0 0 / 63%); } #vsw-survey-form{ display:none;margin-top: 15px;position: fixed;text-align: left;width: 40%;max-width: 600px;z-index: 100;top: 50%;left: 50%;transform: translate(-50%, -50%);background: rgba(255,255,255,1);padding: 35px;border-radius: 6px;border: 2px solid #fff;font-size: 14px;line-height: 24px;outline: none;}#vsw-survey-form p{font-size: 14px;line-height: 24px;padding-bottom:20px;margin: 0;} #vsw-survey-form .vsw_button { margin: 25px 5px 10px 0px; height: 42px;border-radius: 6px;background-color: #1eb5ff;border: none;padding: 0 36px;color: #fff;outline: none;cursor: pointer;font-size: 15px;font-weight: 600;letter-spacing: 0.1px;color: #ffffff;margin-left: 0 !important;position: relative;display: inline-block;text-decoration: none;line-height: 42px;} #vsw-survey-form .vsw_button#vsw_deactivate{background: #fff;border: solid 1px rgba(88,115,149,0.5);color: #a3b2c5;} .vsw_button#vsw_deactivate:disabled{opacity: .5; cursor: not-allowed;} #vsw-survey-form .vsw_button#vsw_skip{background: #fff;border: none;color: #a3b2c5;padding: 0px 15px;float:right;}#vsw-survey-form .vsw-comments{position: relative;}#vsw-survey-form .vsw-comments p{ position: absolute; top: -24px; right: 0px; font-size: 14px; padding: 0px; margin: 0px;} #vsw-survey-form .vsw-comments p a{text-decoration:none;}#vsw-survey-form .vsw-comments textarea{background: #fff;border: solid 1px rgba(88,115,149,0.5);width: 100%;line-height: 30px;resize:none;margin: 10px 0 0 0;} #vsw-survey-form p#vsw-error{margin-top: 10px;padding: 0px;font-size: 13px;color: #ea6464;}
        </style>
    <?php }


    /* 
    * Custom Attributes
    * @since 3.0.0
    */
    public function acovsw_custom_attributes ( $tabs ) { 
        
        $tabs['acovsw-custom-swatches-settings'] = array(
            'label'    => __( 'Swatches', 'aco-variation-swatches-for-woocommerce' ),
            'target'   => 'acovsw-custom-attributes-settings',
            'class'    => array( 'variations_tab', 'show_if_variable', ),
            'priority' => 65,
        );
        return $tabs;

    }

    public function acovsw_custom_attributes_settings () { ?>

        <div id="acovsw-custom-attributes-settings" class="panel wc-metaboxes-wrapper hidden">
            <?php ACOVSW_Backend::view('admin-product', []); ?>
        </div> 
        
    <?php }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

    /**
     * Installation. Runs on activation.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function install()
    {
        $this->_log_version_number();

    }

    /**
     * Log the plugin version number.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    private function _log_version_number()
    {
        update_option($this->_token . '_version', $this->_version);
    }

    public function delete_transient($arg = false)
    {
         delete_transient(ACOVSW_PRODUCTS_TRANSIENT_KEY);
    }

    /**
     * Check if woocommerce plugin is active
     */
    public function acoplw_check_woocommerce_active()
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
