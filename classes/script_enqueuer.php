<?php

class StScript_Enqueuer {

    private $whizzie_instance;

    public function __construct($whizzie_instance) {
        $this->whizzie_instance = $whizzie_instance;
    }

    public function enqueue_scripts($hook) {

        $page_slug = $this->whizzie_instance->get_page_slug();

        if ($hook == 'st-importer_page_stdi_premium_templates_submenu' || $hook == 'toplevel_page_stdi_main_welcome_page' || $hook == 'st-importer_page_stdi_free_templates_submenu') {
            wp_enqueue_style('templates-admin-style', STDI_URL . 'theme-wizard/assets/css/templates.css', array(), STDI_VER);
            wp_register_script('stdi-theme-installation-script', STDI_URL . 'theme-wizard/assets/js/admin-script.js', array('jquery'), time(), true);
            wp_localize_script('stdi-theme-installation-script', 'stdi_admin_params', array(
                'ajaxurl' => esc_url(admin_url('admin-ajax.php')), 
                'wpnonce' => wp_create_nonce('stdi_install_free_theme_nonce'),
                'verify_text' => esc_html('verifying', 'st-demo-importer')
            ));
            wp_enqueue_script('stdi-theme-installation-script');
        }

        if ($hook == 'toplevel_page_stdi_main_welcome_page' || $hook == 'st-importer_page_stdi_premium_templates_submenu' || $hook == 'st-importer_page_stdemoimporter-wizard' || $hook == 'st-importer_page_stdi_free_templates_submenu') {
            wp_enqueue_style('bootstrap-min-css', STDI_URL . 'theme-wizard/assets/css/bootstrap.min.css', array(), STDI_VER);

            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
        }    

        if ( $hook == 'st-importer_page_stdemoimporter-wizard' ) {
            wp_enqueue_style('theme-wizard-style', STDI_URL . 'theme-wizard/assets/css/theme-wizard-style.css', array(), STDI_VER);
            wp_register_script('theme-wizard-script', STDI_URL . 'theme-wizard/assets/js/theme-wizard-script.js', array('jquery'), time(), true);
            wp_localize_script('theme-wizard-script', 'st_demo_importer_pro_whizzie_params', array(
                'ajaxurl' => esc_url(admin_url('admin-ajax.php')), 
                'wpnonce' => wp_create_nonce('whizzie_nonce'),
                'verify_text' => esc_html('verifying', 'st-demo-importer')
            ));
            wp_enqueue_script('theme-wizard-script');
            wp_enqueue_script('tabs', STDI_URL . 'theme-wizard/assets/js/tab.js', array('jquery'), STDI_VER, true);
            wp_enqueue_script('wp-notify-popup', STDI_URL . 'theme-wizard/assets/js/notify.min.js', array('jquery'), STDI_VER, true);
            wp_enqueue_script('bootstrap-bundle-min-js', STDI_URL . 'theme-wizard/assets/js/bootstrap.bundle.min.js', array('jquery'), STDI_VER, true);
        }
        
        wp_enqueue_style('st-demo-importer-font', $this->st_demo_importer_pro_admin_font_url(), array(), STDI_VER);
        wp_enqueue_style('custom-admin-style', STDI_URL . 'theme-wizard/assets/css/getstart.css', array(), STDI_VER);
    }

    public function st_demo_importer_pro_admin_font_url() {
        
        $font_url = '';
        $font_family = array();
        $font_family[] = 'Muli:300,400,600,700,800,900';
        $query_args = array('family' => urlencode(implode('|', $font_family)),);
        $font_url = add_query_arg($query_args, '//fonts.googleapis.com/css');
        return $font_url;
    }
}
