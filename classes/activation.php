<?php

class StActivation {

    private $whizzie_instance;
    private $steps_instance;

    public function __construct($whizzie_instance) {
        $this->whizzie_instance = $whizzie_instance;
        $this->steps_instance = new StSteps($whizzie_instance);
    }

    public function st_demo_importer_pro_mostrar_guide() {

        $st_demo_importer_pro_license_key = st_demo_importer_ThemeWhizzie::get_the_theme_key();

        $api_params = array(
            'slm_action' => 'slm_check',
            'secret_key' => STDI_SECRET_KEY,
            'license_key' => $st_demo_importer_pro_license_key,
        );

        $response = wp_remote_get(
            add_query_arg(
                $api_params,
                STDI_THEMES_HOME_URL
            ),
            array(
                'timeout' => 20,
                'sslverify' => false
            )
        );

        if (is_wp_error($response)) {
            st_demo_importer_ThemeWhizzie::set_the_validation_status('false');
        } else {
            $response_body = wp_remote_retrieve_body($response);
            $response_body = json_decode($response_body);

            $theme_textdomain = wp_get_theme()->get('TextDomain');

            if (isset($response_body->product_ref) && ($response_body->product_ref == $theme_textdomain) ) {

                if (isset($response_body->status) && $response_body->status == 'active') {
                    st_demo_importer_ThemeWhizzie::set_the_validation_status('true');
                } else {
                    st_demo_importer_ThemeWhizzie::set_the_validation_status('false');
                }
            } else {
                st_demo_importer_ThemeWhizzie::set_the_validation_status('false');
            }
            
        }
        // Check the validation END
        $theme_validation_status = st_demo_importer_ThemeWhizzie::get_the_validation_status(); ?>
        <div class="wrapper-info get-stared-page-wrap">
            <div class='admin-requirements'>
                <p><?php esc_html_e('To successfully import a demo content pack, you need to fulfill at least the following PHP configuration parameters:'); ?></p>
                <div class='partitions'>
                    <div class='partition-1'>
                        <h3><?php esc_html_e('Directive'); ?></h3>
                        <ul style="list-style-type:none;">
                            <li><?php esc_html_e('memory_limit'); ?></li>
                            <li><?php esc_html_e('max_execution_time'); ?></li>
                            <li><?php esc_html_e('upload_max_filesize'); ?></li>
                            <li><?php esc_html_e('max_input_time'); ?></li>
                            <li><?php esc_html_e('post_max_size'); ?></li>
                        </ul>
                    </div>
                    <div class='partition-2'>
                        <h3><?php esc_html_e('Priority'); ?></h3>
                        <ul style="list-style-type:none;">
                            <li><?php esc_html_e('High'); ?></li>
                            <li><?php esc_html_e('High'); ?></li>
                            <li><?php esc_html_e('High'); ?></li>
                            <li><?php esc_html_e('High'); ?></li>
                            <li><?php esc_html_e('High'); ?></li>
                        </ul>
                    </div>
                    <div class='partition-3'>
                        <h3><?php esc_html_e('Least Suggested Value'); ?></h3>
                        <ul style="list-style-type:none;">
                            <li><?php esc_html_e('128M'); ?></li>
                            <li><?php esc_html_e('1000'); ?></li>
                            <li><?php esc_html_e('128M'); ?></li>
                            <li><?php esc_html_e('1000'); ?></li>
                            <li><?php esc_html_e('128M'); ?></li>
                        </ul>
                    </div>
                    <div class='partition-4'>
                        <h3><?php esc_html_e('Current Value'); ?></h3>
                        <ul style="list-style-type:none;">
                            <li class='memory_limit'><?php
                                $memory_limit = ini_get("memory_limit"); 
                                    echo esc_html($memory_limit);
                                if($memory_limit >= 128){?>
                                <style>
                                .memory_limit {
                                    color: green;
                                }
                                </style> <?php
                                } else {?>
                                <style>
                                .memory_limit {
                                    color: red;
                                }
                                </style><?php 
                                } ?>
                            </li>
                            <li class='max_execution_time'><?php
                                $max_execution_time = ini_get("max_execution_time");
                                echo esc_html($max_execution_time);
                                if ($max_execution_time >= "1000") {?>
                                    <style>
                                    .max_execution_time {
                                        color: green;
                                    }
                                    </style> <?php
                                } else {?>
                                    <style>
                                    .max_execution_time {
                                        color: red;
                                    }
                                    </style> <?php
                                }  ?>
                            </li>
                            <li class='upload_max_filesize'> <?php
                                $upload_max_filesize = ini_get("upload_max_filesize");
                                $upload_max_filesize_no = str_replace("M","",$upload_max_filesize);
                                echo esc_html($upload_max_filesize);
                                if ($upload_max_filesize_no >= "128") {?>
                                    <style>
                                    .upload_max_filesize {
                                        color: green;
                                    }
                                    </style><?php
                                }else {?>
                                    <style>
                                    .upload_max_filesize {
                                        color: red;
                                    }
                                    </style> <?php
                                }?>
                            </li>
                            <li class='max_input_time'><?php
                                $max_input_time = ini_get("max_input_time");
                                echo esc_html($max_input_time);
                                if ($max_input_time >= "1000") { ?>
                                    <style>
                                    .max_input_time {
                                        color: green;
                                    }
                                    </style> <?php
                                } else { ?>
                                    <style>
                                    .max_input_time {
                                    color: red;
                                    }
                                    </style> <?php
                                }?>
                            </li>
                            <li class='post_max_size'><?php
                                $post_max_size = ini_get("post_max_size");
                                $post_max_size_no = str_replace("M","",$post_max_size);
                                echo esc_html($post_max_size);
                                if ($post_max_size_no >= "128") {?>
                                    <style>
                                    .post_max_size {
                                        color: green;
                                    }
                                    </style> <?php
                                } else { ?>
                                    <style>
                                    .post_max_size {
                                        color: red;
                                    }
                                    </style> <?php
                                }?>
                            </li>
                        </ul>
                    </div>
                </div>
                <p><?php esc_html_e('If you need to change PHP directives you need to modify'); ?> <strong><?php esc_html_e('php.ini'); ?></strong><?php esc_html_e(' file, for more information '); ?><a target="_blank" href="https://docs.presscustomizr.com/article/171-fixing-maximum-upload-and-php-memory-limit-issues"><?php esc_html_e('please read this article'); ?></a><?php esc_html_e(' or contact your hosting provider.'); ?></p>
                <p class='requirement-note'><?php esc_html_e('Note: Even if your current value of "max execution time" is lower than recomemended, demo content will be imported in most cases.'); ?></p>                                    
            </div>
            <div class="d-flex align-items-start parent-import-container">
                <div class="main-div-left d-flex align-items-start">
                <div class="nav flex-column nav-pills me-3" id="stdi-tab-pills-tab" role="tablist" aria-orientation="vertical">
                    <button class="nav-link active" id="stdi-tab-pills-import-tab" data-bs-toggle="pill" data-bs-target="#stdi-tab-pills-import" type="button" role="tab" aria-controls="stdi-tab-pills-import" aria-selected="true"><img title="Import" src="<?php echo esc_url(STDI_URL . 'theme-wizard/assets/images/import-demo.png'); ?>"></button>
                    <button class="nav-link" id="stdi-tab-pills-documentation-tab" data-bs-toggle="pill" data-bs-target="#stdi-tab-pills-documentation" type="button" role="tab" aria-controls="stdi-tab-pills-support" aria-selected="false"><img title="Documentation" src="<?php echo esc_url(STDI_URL . 'theme-wizard/assets/images/document.png'); ?>"></button>
                    <button class="nav-link" id="stdi-tab-pills-support-tab" data-bs-toggle="pill" data-bs-target="#stdi-tab-pills-support" type="button" role="tab" aria-controls="stdi-tab-pills-support" aria-selected="false"><img title="Support" src="<?php echo esc_url(STDI_URL . 'theme-wizard/assets/images/support.png'); ?>"></button>
                    <button class="nav-link" id="stdi-tab-pills-premium-tab" data-bs-toggle="pill" data-bs-target="#stdi-tab-pills-premium" type="button" role="tab" aria-controls="stdi-tab-pills-premium" aria-selected="false"><img title="Premium" src="<?php echo esc_url(STDI_URL . 'theme-wizard/assets/images/premium.png'); ?>"></button>
                </div>
                <div class="tab-content" id="stdi-tab-pills-tabContent">

                    <div class="tab-pane fade show active" id="stdi-tab-pills-import" role="tabpanel" aria-labelledby="stdi-tab-pills-import-tab" tabindex="0">
                        <div class="wee-tab-sec wee-theme-option-tab">
                            <div class="wee-tab">
                                <?php if(defined('IS_ST_PREMIUM')){ ?>
                                    <div class="tab">
                                      <!--   <button class="tablinks active" onclick="openCity(event, 'wee_theme_activation')" data-tab="wee_theme_activation"><?php esc_html_e('Key Activation', 'st-demo-importer'); ?></button> -->
                                    </div>
                                <?php }?>
                            </div>
                            <!-- Tab content -->
                            <div id="wee_theme_activation" class="wee-tabcontent  <?php echo defined('IS_ST_PREMIUM') ? 'open' : '' ?>">
                                <?php if(defined('IS_ST_PREMIUM')){ ?>
                                    <div class="wee_theme_activation-wrapper">
                                        <div class="wee_theme_activation_spinner">
                                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin:auto;background:#fff;display:block;" width="200px" height="200px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                                                <g transform="translate(50,50)">
                                                    <g transform="scale(0.7)">
                                                        <circle cx="0" cy="0" r="50" fill="#0f81d0"></circle>
                                                        <circle cx="0" cy="-28" r="15" fill="#cfd7dd">
                                                            <animateTransform attributeName="transform" type="rotate" dur="1s" repeatCount="indefinite" keyTimes="0;1" values="0 0 0;360 0 0"></animateTransform>
                                                        </circle>
                                                    </g>
                                                </g>
                                            </svg>
                                        </div>
                                        <div class="wee-theme-wizard-key-status">
                                            <?php if ($theme_validation_status === 'false') {
                                                esc_html_e('Theme License Key is not activated!', 'st-demo-importer');
                                            } else {
                                                esc_html_e('Theme License is Activated!', 'st-demo-importer');
                                            } ?>
                                        </div>
                                        <?php $this->activation_page(); ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div id="wee_demo_offer" class="wee-tabcontent <?php echo !defined('IS_ST_PREMIUM') ? 'open' : '' ?>">
                                <?php $this->steps_instance->wizard_page(); ?>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="stdi-tab-pills-documentation" role="tabpanel" aria-labelledby="stdi-tab-pills-documentation-tab" tabindex="0">
                        <div class="tab-content" id="stdi-tab-pills-tabContent">
                            <div class="tab-pane fade active show" id="stdi-tab-pills-import" role="tabpanel" aria-labelledby="stdi-tab-pills-import-tab" tabindex="0">
                                <div class="wee-tab-sec wee-theme-option-tab">
                                    <div class="text-center">
                                        <h2 class="text-center mb-4"><?php echo esc_html('Theme Documentation'); ?></h2>
                                        <p class="text-center w-75 m-auto m-0"><?php echo esc_html('Dive into our theme documentation, tailored for seamless customization. Unlock the power to shape your website according to your vision and needs. Explore various tools and techniques to personalize every aspect of your online presence.'); ?></p>
                                        <?php
                                            $theme_info = wp_get_theme();
                                            $theme_author = $theme_info->get( 'Author' );
                                            $theme_TextDomain = $theme_info->get( 'TextDomain' );
                                            if($theme_author == 'SpectraThemes'){?>
                                                <a class="button button-primary doc-btn text-center" target="_blank" href="https://spectrathemes.com/documentation/<?php echo esc_attr( $theme_TextDomain ); ?>"><?php echo esc_html('Theme Documentation'); ?></a>
                                            <?php }else{ ?>
                                                <a class="button button-primary doc-btn text-center" target="_blank" href="https://spectrathemes.com/documentation/st-dent-care"><?php echo esc_html('Theme Documentation'); ?></a>
                                            <?php }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>  
                    </div>

                    <div class="tab-pane fade" id="stdi-tab-pills-support" role="tabpanel" aria-labelledby="stdi-tab-pills-support-tab" tabindex="0">
                        <div class="tab-content" id="stdi-tab-pills-tabContent">
                            <div class="tab-pane fade active show" id="stdi-tab-pills-import" role="tabpanel" aria-labelledby="stdi-tab-pills-import-tab" tabindex="0">
                                <div class="wee-tab-sec wee-theme-option-tab">
                                    <div class="text-center">
                                        <h2 class="text-center mb-4"><?php echo esc_html('Spectra Support'); ?></h2>
                                        <p class="text-center w-75 m-auto m-0"><?php echo esc_html("Need Support? Click the button below to access Spectra Themes' dedicated support portal. Our team is here to assist you with any questions, troubleshooting, or customization needs you may have. Let's ensure your website shines with our reliable support services."); ?></p>
                                        <a class="button button-primary support-btn text-center" href="https://spectrathemes.com/support" target="_blank"><?php echo esc_html('Support'); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>  
                    </div>

                    <div class="tab-pane fade" id="stdi-tab-pills-premium" role="tabpanel" aria-labelledby="stdi-tab-pills-premium-tab" tabindex="0">
                        <div class="tab-content" id="stdi-tab-pills-tabContent">
                            <div class="tab-pane fade active show" id="stdi-tab-pills-import" role="tabpanel" aria-labelledby="stdi-tab-pills-import-tab" tabindex="0">
                                <div class="wee-tab-sec wee-theme-option-tab">
                                    <div class="text-center">
                                        <h2 class="text-center mb-4"><?php echo esc_html('Coming Soon'); ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>                        
                    </div>

                </div>
            </div>
        </div>
    </div>
        <?php
    }

    public function activation_page() {
        
        if(defined('IS_ST_PREMIUM')){
            $theme_key = st_demo_importer_ThemeWhizzie::get_the_theme_key();
            $validation_status = st_demo_importer_ThemeWhizzie::get_the_validation_status(); ?>
                <div class="wee-wrap">
                    <label><?php esc_html_e('Enter Your Theme License Key:', 'st-demo-importer'); ?></label>
                    <form id="st_demo_importer_pro_license_form">
                        <input type="text" name="st_demo_importer_pro_license_key" value="<?php echo esc_attr($theme_key); ?>" <?php if ($validation_status === 'true') { echo 'disabled'; } ?> required placeholder="<?php esc_attr_e('License Key', 'st-demo-importer'); ?>" />
                        <div class="licence-key-button-wrap">
                            <button class="button" type="submit" name="button" <?php if ($validation_status === 'true') { echo 'disabled'; } ?>>
                                <?php if ($validation_status === 'true') { ?>
                                    <?php esc_html_e('Activated', 'st-demo-importer'); ?>
                                <?php } else { ?>
                                    <?php esc_html_e('Activate', 'st-demo-importer'); ?>
                                <?php } ?>
                            </button>
                            <?php if ($validation_status === 'true') { ?>
                                <button id="change--key" class="button" type="button" name="button"><?php esc_html_e('Change Key', 'st-demo-importer'); ?></button>
                                <div class="next-button">
                                    <button id="start-now-next" class="button" type="button" name="button" onclick="openCity(event, 'wee_demo_offer')"><?php esc_html_e('Next', 'st-demo-importer'); ?></button>
                                </div>
                            <?php } ?>
                        </div>
                    </form>
                </div>
        <?php }
    }

    public function slm_check_premium_theme_text_domain($st_demo_importer_pro_license_key) {

        $api_params = array(
            'slm_action' => 'slm_check',
            'secret_key' => STDI_SECRET_KEY,
            'license_key' => $st_demo_importer_pro_license_key,
        );

        $response = wp_remote_get(
            add_query_arg(
                $api_params,
                STDI_THEMES_HOME_URL
            ),
            array(
                'timeout' => 20,
                'sslverify' => false
            )
        );

        if (is_wp_error($response)) {
            st_demo_importer_ThemeWhizzie::remove_the_theme_key();
            st_demo_importer_ThemeWhizzie::set_the_validation_status('false');
            st_demo_importer_ThemeWhizzie::set_the_theme_key('');
            
            $response = array('status' => false, 'msg' => 'Something Went Wrong!');
            wp_send_json($response);
            exit;
        } else {
            $response_body = wp_remote_retrieve_body($response);
            $response_body = json_decode($response_body);

            $theme_textdomain = wp_get_theme()->get('TextDomain');

            if ($response_body->result == 'error') {
                
                st_demo_importer_ThemeWhizzie::remove_the_theme_key();
                st_demo_importer_ThemeWhizzie::set_the_validation_status('false');
                st_demo_importer_ThemeWhizzie::set_the_theme_key('');

                $response = array('status' => false, 'msg' => $response_body->message);
                wp_send_json($response);
                exit;
            }

            if ($response_body->result == 'success') {

                if ( $response_body->status == 'active' ) {

                    if (isset($response_body->product_ref) && ($response_body->product_ref != $theme_textdomain)) {

                        st_demo_importer_ThemeWhizzie::remove_the_theme_key();
                        st_demo_importer_ThemeWhizzie::set_the_validation_status('false');
                        
                        $response = array('status' => false, 'msg' => 'The key for this theme is incorrect!');
                        wp_send_json($response);
                        exit;
                    } else {
                        
                        $current_site_url = site_url();

                        $site_exists = false;
                        foreach ($response_body->registered_domains as $registered_domain) {
                            if ($current_site_url === $registered_domain->registered_domain) {
                                $site_exists = true;
                                break;
                            }
                        }

                        if ($site_exists) {
                            
                            st_demo_importer_ThemeWhizzie::set_the_validation_status('true');
                            st_demo_importer_ThemeWhizzie::set_the_theme_key($st_demo_importer_pro_license_key);

                            $response = array('status' => true, 'msg' => 'License Key Activated');
                            wp_send_json($response);
                            exit;

                        } else {

                            return 'true';
                        }
                    }

                } else {

                    return 'true';
                }
            }
        }
    }

    public function wz_activate_st_demo_importer_pro() {

        if(defined('IS_ST_PREMIUM')){
            $st_demo_importer_pro_license_key = sanitize_text_field($_POST['st_demo_importer_pro_license_key']);
            $is_current_theme = $this->slm_check_premium_theme_text_domain($st_demo_importer_pro_license_key);

            if ($is_current_theme == 'true') {
                $api_params = array(
                    'slm_action' => 'slm_activate',
                    'secret_key' => STDI_SECRET_KEY,
                    'license_key' => $st_demo_importer_pro_license_key,
                    'registered_domain' => site_url()
                );
        
                $response = wp_remote_get(
                    add_query_arg(
                        $api_params,
                        STDI_THEMES_HOME_URL
                    ),
                    array(
                        'timeout' => 20,
                        'sslverify' => false
                    )
                );
    
                if (is_wp_error($response)) {
                    st_demo_importer_ThemeWhizzie::remove_the_theme_key();
                    st_demo_importer_ThemeWhizzie::set_the_validation_status('false');
                    st_demo_importer_ThemeWhizzie::set_the_theme_key('');
                    
                    $response = array('status' => false, 'msg' => 'Something Went Wrong!');
                    wp_send_json($response);
                    exit;
                } else {
                    $response_body = wp_remote_retrieve_body($response);
                    $response_body = json_decode($response_body);
    
                    if ($response_body->result == 'error') {
                        
                        st_demo_importer_ThemeWhizzie::remove_the_theme_key();
                        st_demo_importer_ThemeWhizzie::set_the_validation_status('false');
    
                        $response = array('status' => false, 'msg' => $response_body->message);
                        wp_send_json($response);
                        exit;
                    } elseif ($response_body->result == 'success') {
    
                        st_demo_importer_ThemeWhizzie::set_the_validation_status('true');
                        st_demo_importer_ThemeWhizzie::set_the_theme_key($st_demo_importer_pro_license_key);
    
                        $response = array('status' => true, 'msg' => $response_body->message);
                        wp_send_json($response);
                        exit;
                    } else {
    
                        st_demo_importer_ThemeWhizzie::remove_the_theme_key();
                        st_demo_importer_ThemeWhizzie::set_the_validation_status('false');
    
                        $response = array('status' => false, 'msg' => 'Something Went Wrong!');
                        wp_send_json($response);
                        exit;
                    }
                }
            }
        }
    }
}