<?php

class StSetup_Plugins {

    private $whizzie_instance;

    public function __construct($whizzie_instance) {
        $this->whizzie_instance = $whizzie_instance;
    }

    public function setup_plugins(){

        $tgmpa_url = $this->whizzie_instance->get_tgmpa_url();
        $tgmpa_menu_slug = $this->whizzie_instance->get_tgmpa_menu_slug();

        if (!check_ajax_referer('whizzie_nonce', 'wpnonce') || empty($_POST['slug'])) {
            wp_send_json_error(array('error' => 1, 'message' => esc_html__('No Slug Found', 'st-demo-importer')));
        }
        $json = array();
        // send back some json we use to hit up TGM
        $plugins = $this->get_plugins();

        // what are we doing with this plugin?
        foreach ($plugins['activate'] as $slug => $plugin) {
            if ($_POST['slug'] == $slug) {
                $json = array(
                    'url' => esc_url(admin_url($tgmpa_url)),
                    'plugin' => array($slug),
                    'tgmpa-page' => $tgmpa_menu_slug,
                    'plugin_status' => 'all',
                    '_wpnonce' => wp_create_nonce('bulk-plugins'),
                    'action' => 'tgmpa-bulk-activate',
                    'action2' => - 1,
                    'message' => esc_html__('Activating Plugin', 'st-demo-importer')
                );
                break;
            }
        }
        
        foreach ($plugins['update'] as $slug => $plugin) {
            if ($_POST['slug'] == $slug) {
                $json = array(
                    'url' => esc_url(admin_url($tgmpa_url)),
                    'plugin' => array($slug),
                    'tgmpa-page' => $tgmpa_menu_slug,
                    'plugin_status' => 'all',
                    '_wpnonce' => wp_create_nonce('bulk-plugins'),
                    'action' => 'st-demo-importer-tgmpa-bulk-update',
                    'action2' => - 1,
                    'message' => esc_html__('Updating Plugin', 'st-demo-importer')
                );
                break;
            }
        }
    
        foreach ($plugins['install'] as $slug => $plugin) {
            if ($_POST['slug'] == $slug) {
                $json = array(
                    'url' => esc_url(admin_url($tgmpa_url)),
                    'plugin' => array($slug),
                    'tgmpa-page' => $tgmpa_menu_slug,
                    'plugin_status' => 'all',
                    '_wpnonce' => wp_create_nonce('bulk-plugins'),
                    'action' => 'st-demo-importer-tgmpa-bulk-install',
                    'action2' => - 1,
                    'message' => esc_html__('Installing Plugin', 'st-demo-importer')
                );
                break;
            }
        }
        
        delete_transient('elementor_activation_redirect');
        if ($json) {
            $json['hash'] = md5(serialize($json)); // used for checking if duplicates happen, move to next plugin
            wp_send_json($json);
        } else {
            wp_send_json(array('done' => 1, 'message' => esc_html__('Success', 'st-demo-importer')));
        }
        exit;
    }

    /**
    * Get the plugins registered with TGMPA
    */
    public function get_plugins() {
        
        $instance = call_user_func(array(get_class($GLOBALS['st_demo_importer_tgmpa']), 'get_instance'));
        $new_instance_plugins = $instance->plugins;

        $plugins = array('all' => array(), 'install' => array(), 'update' => array(), 'activate' => array());
        foreach ($new_instance_plugins as $slug => $plugin) {
            if ($instance->is_plugin_active($slug) && false === $instance->does_plugin_have_update($slug)) {
                // Plugin is installed and up to date
                continue;
            } else {
                $plugins['all'][$slug] = $plugin;
                if (!$instance->is_plugin_installed($slug)) {
                    $plugins['install'][$slug] = $plugin;
                } else {
                    if (false !== $instance->does_plugin_have_update($slug)) {
                        $plugins['update'][$slug] = $plugin;
                    }
                    if ($instance->can_plugin_activate($slug)) {
                        $plugins['activate'][$slug] = $plugin;
                    }
                }
            }
        }
        return $plugins;
    }
}