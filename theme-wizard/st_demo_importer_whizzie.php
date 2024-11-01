<?php
/**
* Wizard
*
* @package Whizzie
* @author Catapult Themes
* @since 1.0.0
*/

class st_demo_importer_ThemeWhizzie {

    private $script_functions_instance;
    private $setup_plugins_instance;
    private $elementor_import_instance;
    private $activation_instance;
    private $steps_instance;
    private $premium_templates_instance;
    private $free_templates_instance;
    private $welcome_instance;
        
    public static $theme_key = '';
    protected $version = '1.1.0';
    
    /** @var string Current theme name, used as namespace in actions. */
    protected $plugin_name = '';
    protected $plugin_title = '';

    protected $plugin_path = '';
    protected $parent_slug  = '';
    
    /** @var string Wizard page slug and title. */
    protected $page_slug = '';
    protected $page_title = '';
    
    /**
    * Relative plugin url for this plugin folder
    * @since 1.0.0
    * @var string
    */
    protected $plugin_url = '';
    
    /**
    * TGMPA instance storage
    *
    * @var object
    */
    protected $tgmpa_instance;
    
    /**
    * TGMPA Menu slug
    *
    * @var string
    */
    protected $tgmpa_menu_slug = 'st-demo-importer-tgmpa-install-plugins';
    
    /**
    * TGMPA Menu url
    *
    * @var string
    */
    protected $tgmpa_url = 'admin.php?page=st-demo-importer-tgmpa-install-plugins';

    // Where to find the widget.wie file
    protected $widget_file_url = '';
    
    /**
    * Constructor
    *
    * @param $st_demo_importer_config Our config parameters
    */
    public function __construct() {
        $this->set_vars();
        $this->init();
    }

    public static function get_the_validation_status() {
        return get_option('st_demo_importer_pro_theme_validation_status', 'false');
    }

    public static function set_the_validation_status($is_valid) {
        update_option('st_demo_importer_pro_theme_validation_status', $is_valid);
    }

    public static function set_the_theme_key($the_key) {
        update_option('stdi_pro_theme_key', $the_key);
    }

    public static function remove_the_theme_key() {
        delete_option('stdi_pro_theme_key');
    }

    public static function get_the_theme_key() {
        return get_option('stdi_pro_theme_key');
    }

    public function get_page_slug() {
        return $this->page_slug;
    }

    public function get_tgmpa_url() {
        return $this->tgmpa_url;
    }

    public function get_tgmpa_menu_slug() {
        return $this->tgmpa_menu_slug;
    }

    /**
    * Set some settings
    * @since 1.0.0
    * @param $st_demo_importer_config Our config parameters
    */
    public function set_vars() {
        
        require_once trailingslashit(st_demo_importer_WHIZZIE_DIR) . 'tgm/tgm.php';

        $this->page_title = 'ST Importer';
        
        $this->plugin_path = trailingslashit(dirname(__FILE__));
        $relative_url = str_replace(get_template_directory(), '', $this->plugin_path);
        $this->plugin_url = trailingslashit(get_template_directory_uri() . $relative_url);
        $current_plugin = get_plugin_data(STDI_FILE);
        $this->plugin_title = $current_plugin['Name'];
        $this->plugin_name = strtolower(preg_replace('#[^a-zA-Z]#', '', $current_plugin['Name']));
        $this->page_slug = apply_filters($this->plugin_name . '_theme_setup_wizard_page_slug', $this->plugin_name . '-wizard');
        $this->parent_slug = apply_filters($this->plugin_name . '_theme_setup_wizard_parent_slug', '');
    }
    /**
    * Hooks and filters
    * @since 1.0.0
    */
    public function init() {

        $this->script_functions_instance = new StScript_Enqueuer($this);
        $this->setup_plugins_instance = new StSetup_Plugins($this);
        $this->elementor_import_instance = new StElementor_Import($this);
        $this->activation_instance = new StActivation($this);
        $this->steps_instance = new StSteps($this);
        $this->premium_templates_instance = new StPremium_Templates($this);
        $this->free_templates_instance = new StFree_Templates($this);
        $this->welcome_instance = new StWelcome($this);
        
        add_action('activated_plugin', array($this, 'redirect_to_wizard'), 100, 2);
        if (class_exists('st_demo_importer_TGM_Plugin_Activation') && isset($GLOBALS['st_demo_importer_tgmpa'])) {
            add_action('init', array($this, 'get_tgmpa_instance'), 30);
            add_action('init', array($this, 'set_tgmpa_url'), 40);
        }
        add_action('admin_enqueue_scripts', array($this->script_functions_instance, 'enqueue_scripts'));
        add_action('admin_menu', array($this, 'menu_page'));
        add_action('admin_init', array($this->setup_plugins_instance, 'get_plugins'), 30);
        add_filter('st_demo_importer_tgmpa_load', array($this, 'st_demo_importer_tgmpa_load'), 10, 1);
        add_action('wp_ajax_setup_plugins', array($this->setup_plugins_instance, 'setup_plugins'));
        add_action('wp_ajax_setup_widgets', array($this, 'setup_widgets'));
        add_action('wp_ajax_wz_activate_st_demo_importer_pro', array($this->activation_instance, 'wz_activate_st_demo_importer_pro'));
        add_action('wp_ajax_st_demo_importer_setup_elementor', array($this->elementor_import_instance, 'st_demo_importer_setup_elementor'));
        add_action('admin_menu', array($this->premium_templates_instance, 'st_demo_importer_add_premium_submenu'));
        add_action('admin_menu', array($this->free_templates_instance, 'st_demo_importer_add_free_submenu'));
    }
    
    public static function get_the_plugin_key() {
        return get_option('st_demo_importer_plugin_license_key');
    }
    
    public function redirect_to_wizard($plugin, $network_wide) {
        
        global $pagenow;
        if (is_admin() && ('plugins.php' == $pagenow) && current_user_can('manage_options') && (STDI_BASE == $plugin)) {
            wp_redirect(esc_url(admin_url('admin.php?page=' . esc_attr($this->page_slug))));
        }
    }
    
    public static function get_instance() {
        
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function st_demo_importer_tgmpa_load($status) {
        return is_admin() || current_user_can('install_themes');
    }
    /**
    * Get configured TGMPA instance
    *
    * @access public
    * @since 1.1.2
    */
    
    public function get_tgmpa_instance() {
        $this->tgmpa_instance = call_user_func(array(get_class($GLOBALS['st_demo_importer_tgmpa']), 'get_instance'));
    }

    /**
    * Update $tgmpa_menu_slug and $tgmpa_parent_slug from TGMPA instance
    *
    * @access public
    * @since 1.1.2
    */
    public function set_tgmpa_url() {
        
        $this->tgmpa_menu_slug = (property_exists($this->tgmpa_instance, 'menu')) ? $this->tgmpa_instance->menu : $this->tgmpa_menu_slug;
        $this->tgmpa_menu_slug = apply_filters($this->plugin_name . '_theme_setup_wizard_tgmpa_menu_slug', $this->tgmpa_menu_slug);
        $tgmpa_parent_slug = (property_exists($this->tgmpa_instance, 'parent_slug') && $this->tgmpa_instance->parent_slug !== 'plugin.php') ? 'admin.php' : 'plugin.php';
        $this->tgmpa_url = apply_filters($this->plugin_name . '_theme_setup_wizard_tgmpa_url', $tgmpa_parent_slug . '?page=' . $this->tgmpa_menu_slug);
    }
    
    /**
    * Make a modal screen for the wizard
    */
    public function menu_page() {
        
        add_menu_page(esc_html($this->page_title), esc_html($this->page_title), 'manage_options', 'stdi_main_welcome_page', array($this->welcome_instance, 'render_stdi_main_welcome_page'), 'dashicons-download', 40);

        add_submenu_page('stdi_main_welcome_page',esc_html('Welcome'),esc_html('Welcome'),'manage_options','stdi_main_welcome_page',array($this->welcome_instance, 'render_stdi_main_welcome_page'));

        add_submenu_page('stdi_main_welcome_page',esc_html('Import'),esc_html('Import'),'manage_options',$this->page_slug,array($this->activation_instance, 'st_demo_importer_pro_mostrar_guide'));
    }

    /**
    * Imports the Demo Content
    * @since 1.1.0
    */
    public function setup_widgets() {
    }

    public function get_st_themes() {
        
        $endpoint = STDI_ADMIN_CUSTOM_ENDPOINT . 'get_theme_data_from_database';
        $options = ['headers' => ['Content-Type' => 'application/json', ]];
        $response = wp_remote_get($endpoint, $options);
        if (is_wp_error($response)) {
            $response = array( 'status' => 100, 'msg' => 'Something Went Wrong!', 'data' => [] );
            return $response;
        } else {
            $response_body = wp_remote_retrieve_body($response);
            $response_body = json_decode($response_body);

            $response = array( 'status' => 200, 'msg' => 'Spectra themes list', 'data' => $response_body->data );
            return $response;
        }
    }
}
