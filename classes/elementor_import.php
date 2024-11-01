<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class StElementor_Import {

    private $whizzie_instance;

    public function __construct($whizzie_instance) {
        $this->whizzie_instance = $whizzie_instance;
    }
    
    public function st_demo_importer_setup_elementor() {

        $st_themes = $this->whizzie_instance->get_st_themes();
        $arrayJson = array();
        if( $st_themes['status'] == 200 && !empty($st_themes['data']) ) {
            
            $st_themes_data = $st_themes['data'];
            foreach ( $st_themes_data as $single_theme ) {
                $arrayJson[$single_theme->theme_text_domain] = array(
                    'title' => $single_theme->theme_page_title,
                    'url' => $single_theme->theme_json_url
                );
            }
        }

        $my_theme_txd = wp_get_theme();
        $get_textdomain = $my_theme_txd->get('TextDomain');

        $pages_arr = array();
        if (array_key_exists($get_textdomain, $arrayJson)) {
            $getpreth = $arrayJson[$get_textdomain];
            array_push($pages_arr, array(
                'title' => $getpreth['title'],
                'ishome' => 1,
                'type' => '',
                'post_type' => 'page',
                'url' => $getpreth['url'],
            ));
            
            
            if( defined('IS_ST_PREMIUM') || defined('IS_ST_FREEMIUM') ){
                    
                if (file_exists(get_template_directory() . '/inc/page.json')) {
                    $json_url = get_template_directory_uri() . '/inc/page.json';
                    $response = wp_remote_get($json_url);
                
                    if (!is_wp_error($response) && $response['response']['code'] == 200) {
                        $inner_page_json = wp_remote_retrieve_body($response);
                        $inner_page_json_decoded = json_decode($inner_page_json, true);
                
                        if ($inner_page_json_decoded !== null) {
                            foreach ($inner_page_json_decoded as $page) {
                                array_push($pages_arr, array(
                                    'type' => isset($page['type']) ? $page['type'] : '',
                                    'title' => $page['name'],
                                    'ishome' => 0,
                                    'post_type' => $page['posttype'],
                                    'url' => $page['source'],
                                ));
                            }
                        } 
                    }
                }                
            }
        } else {
            array_push($pages_arr, array(
                'title' => 'Spectra Business',
                'type' => '',
                'ishome' => 1,
                'post_type' => 'page',
                'url' => STDI_THEMES_HOME_URL . "/demo/all-json/spectra-business/spectra-business.json",
            ));
        }

        $this->create_all_existing_elementor_values();

        // call theme function start //
        $setup_widgets_function = str_replace( '-', '_', $get_textdomain ) . '_demo_import';
        if ( class_exists('ST_Theme_Whizzie') && method_exists( 'ST_Theme_Whizzie', $setup_widgets_function ) ) {
            ST_Theme_Whizzie::$setup_widgets_function();
        }
        // call theme function end //

        foreach ($pages_arr as $page) {
            $elementor_template_data = $page['url'];
            $elementor_template_data_title = $page['title'];
            $ishome = $page['ishome'];
            $post_type = $page['post_type'];
            $type = isset($page['type']) ? $page['type'] : '';
            $this->import_inner_pages_data($elementor_template_data, $elementor_template_data_title, $ishome,$post_type,$type);
        }

        wp_send_json(array(
            'permalink' => site_url(),
            'edit_post_link' => admin_url('post.php?post=' . $home_id . '&action=elementor')
        ));
    }

    public function random_string($length) {
        
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));
        for ($i = 0;$i < $length;$i++) {
            $key.= $keys[array_rand($keys) ];
        }
        return $key;
    }

    public function import_inner_pages_data($elementor_template_data, $elementor_template_data_title, $ishome,$post_type,$type){

        $response = wp_remote_get($elementor_template_data);

        if (is_wp_error($response)) {
            // Handle error
            return;
        }
        
        $elementor_template_data_json = wp_remote_retrieve_body($response);

        // Upload the file first
        $upload_dir = wp_upload_dir();
        $filename = $this->random_string(25) . '.json';
        $file = trailingslashit($upload_dir['path']) . $filename;

        // Initialize WP_Filesystem
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
        }
    
        WP_Filesystem();
    
        global $wp_filesystem;
    
        if ( ! $wp_filesystem ) {
            // Failed to initialize WP_Filesystem, handle error
            return;
        }

        $write_result = $wp_filesystem->put_contents($file, $elementor_template_data_json, FS_CHMOD_FILE);

        if ( ! $write_result ) {
            // Failed to write file, handle error
            return;
        }

        $json_path = $upload_dir['path'] . '/' . $filename;
        $json_url = $upload_dir['url'] . '/' . $filename;
        $elementor_home_data = $this->get_elementor_theme_data($json_url, $json_path);

        $page_title = $elementor_template_data_title;
        $home_page = array(
            'post_type' => $post_type, 
            'post_title' => $page_title, 
            'post_content' => $elementor_home_data['elementor_content'], 
            'post_status' => 'publish', 
            'post_author' => 1, 
            'meta_input' => $elementor_home_data['elementor_content_meta']
        );
        $home_id = wp_insert_post($home_page);
        
        $get_author = wp_get_theme();
        $theme_author = $get_author->display( 'Author', FALSE );
        if( $theme_author == 'SpectraThemes'){
            update_post_meta( $home_id, '_wp_page_template', 'frontpage.php' );
        }

        if($post_type == 'elementskit_template'){
            update_post_meta( $home_id, '_wp_page_template', 'elementor_canvas' );
            update_post_meta( $home_id, 'elementskit_template_activation', 'yes' );
            update_post_meta( $home_id, 'elementskit_template_type', $type );
            update_post_meta( $home_id, 'elementskit_template_condition_a', 'entire_site' );
        } else {
            if ($ishome !== 0) {
                update_option('page_on_front', $home_id);
                update_option('show_on_front', $post_type);

                $my_theme_txd = wp_get_theme();
                $get_textdomain = $my_theme_txd->get('TextDomain');
                $api_url = STDI_ADMIN_CUSTOM_ENDPOINT . 'get_theme_text_domain_data';
                $options = ['headers' => ['Content-Type' => 'application/json', ]];
                $response = wp_remote_get($api_url, $options);
                $json = json_decode( $response['body'] );

                $sedi_free_text_domain = array();

                if ($json->code == 200) {
                    foreach ($json->data as $value) {

                        $get_all_domains = $value->theme_text_domain;
                        array_push($sedi_free_text_domain, $get_all_domains);
                    }
                }
                
                if(in_array($get_textdomain,  $sedi_free_text_domain)) {
                    add_post_meta( $home_id, '_wp_page_template', 'home-page-template.php' );
                }
            }
        }
    }

    public function get_elementor_theme_data($json_url, $json_path) {
    
        // Mime a supported document type.
        $elementor_plugin = \Elementor\Plugin::$instance;
        $elementor_plugin->documents->register_document_type('not-supported', \Elementor\Modules\Library\Documents\Page::get_class_full_name());
        $template = $json_path;
        $name = '';
        $_FILES['file']['tmp_name'] = $template;
        $elementor = new \Elementor\TemplateLibrary\Source_Local;
        $elementor->import_template($name, $template);
        wp_delete_file($json_path);

        $args = array('post_type' => 'elementor_library','nopaging' => true,'posts_per_page' => '1','orderby' => 'date','order' => 'DESC');
        add_filter('posts_where', array($this, 'custom_posts_where'));
        $query = new \WP_Query($args);
        remove_filter('posts_where', array($this, 'custom_posts_where'));
    
        $last_template_added = $query->posts[0];
        //get template id
        $template_id = $last_template_added->ID;
        wp_reset_query();
        wp_reset_postdata();
        //page content
        $page_content = $last_template_added->post_content;
        //meta fields
        $elementor_data_meta = get_post_meta($template_id, '_elementor_data');
        $elementor_ver_meta = get_post_meta($template_id, '_elementor_version');
        $elementor_edit_mode_meta = get_post_meta($template_id, '_elementor_edit_mode');
        $elementor_css_meta = get_post_meta($template_id, '_elementor_css');
        $elementor_metas = array('_elementor_data' => !empty($elementor_data_meta[0]) ? wp_slash($elementor_data_meta[0]) : '', '_elementor_version' => !empty($elementor_ver_meta[0]) ? $elementor_ver_meta[0] : '', '_elementor_edit_mode' => !empty($elementor_edit_mode_meta[0]) ? $elementor_edit_mode_meta[0] : '', '_elementor_css' => $elementor_css_meta,);
        $elementor_json = array('elementor_content' => $page_content, 'elementor_content_meta' => $elementor_metas);
        return $elementor_json;
    }

    public function custom_posts_where($where) {
        return $where;
    }

    public function create_all_existing_elementor_values() {

        update_option('elementor_unfiltered_files_upload', '1');
        update_option('elementor_experiment-e_optimized_control_loading', 'active');
        
        // getting color from theme start //
        if (file_exists(get_template_directory() . '/inc/json/color.json')) {
            
            $color_json = get_template_directory_uri() . '/inc/json/color.json';
            $response = wp_remote_get($color_json);
            $color_arr = array();
            
            if (!is_wp_error($response) && $response['response']['code'] == 200) {
                $color_setting_json = wp_remote_retrieve_body($response);
                $color_setting_json_decoded = json_decode($color_setting_json, true);
                
                if ($color_setting_json_decoded !== null) {
                    foreach ($color_setting_json_decoded as $color) {
                            array_push($color_arr, array(
                                '_id' => isset($color['_id']) ? $color['_id'] : 'st_default',
                                'title' => isset($color['title']) ? $color['title'] : 'ST Default',
                                'color' => isset($color['color']) ? $color['color'] : '#ffff',
                            ));
                        }
                    } 
                }
            } else {
                $color_arr[] = array(
                    '_id' => 'st_default',
                    'title' => 'ST Default',
                    'color' => '#ffff'
                );
            }
        // getting color from theme end //

        // getting typography from theme start //
        if (file_exists(get_template_directory() . '/inc/json/typography.json')) {
            $typography_json = get_template_directory_uri() . '/inc/json/typography.json';
            $response = wp_remote_get($typography_json);
            $typography_arr = array();
            
            if (!is_wp_error($response) && $response['response']['code'] == 200) {
                $typography_setting_json = wp_remote_retrieve_body($response);
                $typography_setting_json_decoded = json_decode($typography_setting_json, true);
                
                if ($typography_setting_json_decoded !== null) {
                    foreach ($typography_setting_json_decoded as $typography) {
                            array_push($typography_arr, array(
                                '_id' => isset($typography['_id']) ? $typography['_id'] : 'st_default',
                                'title' => isset($typography['title']) ? $typography['title'] : 'ST Default',
                                'typography_typography' => isset($typography['typography_typography']) ? $typography['typography_typography'] : 'custom',
                                'typography_font_family' => isset($typography['typography_font_family']) ? $typography['typography_font_family'] : 'Montserrat',
                                'typography_font_weight' => isset($typography['typography_font_weight']) ? $typography['typography_font_weight'] : '500',
                            ));
                        }
                    } 
                }
            } else {
                $typography_arr[] = array(
                    '_id' => 'st_default',
                    'title' => 'ST Default',
                    'typography_typography' => 'custom',
                        'typography_font_family' => 'Montserrat',
                        'typography_font_weight' => '500',
                );
            }
        // getting typography from theme end //
      
        $elementor_kit_id = get_option('elementor_active_kit');
            
        if (!get_post_meta($elementor_kit_id, '_elementor_page_settings', true)) {
            // Define the entire array with the desired values
         
            $system_colors = array(
                'system_colors' => array(
                    array(
                        '_id' => 'primary',
                        'title' => 'Primary',
                        'color' => '#6EC1E4'
                    ),
                    array(
                        '_id' => 'secondary',
                        'title' => 'Secondary',
                        'color' => '#54595F'
                    ),
                    array(
                        '_id' => 'text',
                        'title' => 'Text',
                        'color' => '#7A7A7A'
                    ),
                    array(
                        '_id' => 'accent',
                        'title' => 'Accent',
                        'color' => '#61CE70'
                    )
                ),
                'custom_colors' => array(),
                'system_typography' => array(
                    array(
                        '_id' => 'primary',
                        'title' => 'Primary',
                        'typography_typography' => 'custom',
                        'typography_font_family' => 'Roboto',
                        'typography_font_weight' => 600
                    ),
                    array(
                        '_id' => 'secondary',
                        'title' => 'Secondary',
                        'typography_typography' => 'custom',
                        'typography_font_family' => 'Roboto Slab',
                        'typography_font_weight' => 400
                    ),
                    array(
                        '_id' => 'text',
                        'title' => 'Text',
                        'typography_typography' => 'custom',
                        'typography_font_family' => 'Roboto',
                        'typography_font_weight' => 400
                    ),
                    array(
                        '_id' => 'accent',
                        'title' => 'Accent',
                        'typography_typography' => 'custom',
                        'typography_font_family' => 'Roboto',
                        'typography_font_weight' => 500
                    )
                ),
                'custom_typography' => array(),
                'default_generic_fonts' => 'Sans-serif',
                'site_name' => 'wp1',
                'page_title_selector' => 'h1.entry-title',
                'active_breakpoints' => array(
                    'viewport_mobile',
                    'viewport_mobile_extra',
                    'viewport_tablet',
                    'viewport_tablet_extra',
                    'viewport_laptop',
                    'viewport_widescreen'
                ),
                'viewport_md' => 768,
                'viewport_lg' => 1025,
                'colors_enable_styleguide_preview' => 'yes',
                'viewport_lg' => 1
            );
           
            $system_colors['custom_typography'] = array_merge($system_colors['custom_typography'], $typography_arr);
            $system_colors['custom_colors'] = array_merge($system_colors['custom_colors'], $color_arr);

            // Save the entire array as post meta
            update_post_meta($elementor_kit_id, '_elementor_page_settings', $system_colors);
        } else {
            // add color start //
            $elementor_kit_id = get_option('elementor_active_kit');
            $get_all_existing_elementor_values = get_post_meta($elementor_kit_id, '_elementor_page_settings', true);
            
            $expected_custom_colors = $color_arr;
                function add_missing_custom_colors(&$existing_array, $expected_array, $key) {
                    if (!isset($existing_array[$key]) || !is_array($existing_array[$key])) {
                    $existing_array[$key] = $expected_array;
                } else {
                    $existing_values = $existing_array[$key];
                    $missing_items = array_udiff($expected_array, $existing_values, function($a, $b) {
                    return $a['_id'] <=> $b['_id'];
                });
                    $existing_array[$key] = array_merge($existing_values, $missing_items);
                }
        
            }
        
            add_missing_custom_colors($get_all_existing_elementor_values, $expected_custom_colors, 'custom_colors');
            update_post_meta($elementor_kit_id, '_elementor_page_settings', $get_all_existing_elementor_values);
            // add color end //
            
            // add typography start //
            $expected_custom_typography = $typography_arr;

            function add_missing_custom_typography(&$existing_array, $expected_array, $key) {
                if (!isset($existing_array[$key]) || !is_array($existing_array[$key])) {
                    $existing_array[$key] = $expected_array;
                } else {
                    $existing_values = $existing_array[$key];
                    $missing_items = array_udiff($expected_array, $existing_values, function($a, $b) {
                        return $a['_id'] <=> $b['_id'];
                    });
                    $existing_array[$key] = array_merge($existing_values, $missing_items);
                }
            }
            
            add_missing_custom_typography($get_all_existing_elementor_values, $expected_custom_typography, 'custom_typography');
            update_post_meta($elementor_kit_id, '_elementor_page_settings', $get_all_existing_elementor_values);
            // add typography end //
            
            // add breakpoints end //
            $expected_breakpoints = array(
                'viewport_mobile',
                'viewport_mobile_extra',
                'viewport_tablet',
                'viewport_tablet_extra',
                'viewport_laptop',
                'viewport_widescreen'
            );
            
            if (isset($get_all_existing_elementor_values['active_breakpoints']) && is_array($get_all_existing_elementor_values['active_breakpoints'])) {
                $active_breakpoints = $get_all_existing_elementor_values['active_breakpoints'];
                $missing_breakpoints = array_diff($expected_breakpoints, $active_breakpoints);
            
                if (!empty($missing_breakpoints)) {
                    $updated_breakpoints = array_merge($active_breakpoints, $missing_breakpoints);
                    $get_all_existing_elementor_values['active_breakpoints'] = $updated_breakpoints;
                    update_post_meta($elementor_kit_id, '_elementor_page_settings', $get_all_existing_elementor_values);
                }
            } else {
                $get_all_existing_elementor_values['active_breakpoints'] = $expected_breakpoints;
            
                update_post_meta($elementor_kit_id, '_elementor_page_settings', $get_all_existing_elementor_values);
            }
            // add breakpoints end //
        
        }
               
        // adding elementor kit settings end
    }
}