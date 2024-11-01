<?php

class StFree_Templates {

  private $whizzie_instance;

  public function __construct($whizzie_instance) {
      $this->whizzie_instance = $whizzie_instance;

      add_action('wp_ajax_install_free_theme', array($this, 'stdi_install_and_activate_free_theme_from_wporg'));
      add_action('admin_init', array($this, 'stdi_handle_theme_redirect'));
  }

  public function stdi_free_templates_api() {

      $paged = isset($_POST['paged']) ? $_POST['paged'] : 1;
      $category_id = isset($_POST['category_id']) ? $_POST['category_id'] : 0;
      $search = isset($_POST['search_val']) ? $_POST['search_val'] : '';

      $themes_array = array();

      $endpoint = STDI_ADMIN_CUSTOM_ENDPOINT . 'get_free_theme_templates_arr';
      $body = [ 'paged' => $paged, 'category_id' => $category_id, 'search' => $search ];
      $body = wp_json_encode($body);
      $options = ['body' => $body, 'headers' => ['Content-Type' => 'application/json'], 'timeout' => 60];
      $response = wp_remote_post($endpoint, $options);

      if (!is_wp_error($response)) {
        
        $response_body = wp_remote_retrieve_body($response);
        $response_body = json_decode($response_body);
        
        if ( isset($response_body->code) && $response_body->code == 200 ) {
          if ( isset($response_body->data) && !empty($response_body->data) ) {

            $themes_array['themes'] = $response_body->data;
            $themes_array['total_pages'] = $response_body->total_pages;
            $themes_array['total_products'] = $response_body->total_products;
          }            
        }
      }

      return array(
        'data' => isset($themes_array['themes']) ? $themes_array['themes'] : array(),
        'total_pages' => isset($themes_array['total_pages']) ? $themes_array['total_pages'] : 1,
        'total_products' => isset($themes_array['total_products']) ? $themes_array['total_products'] : 0
      );
  }

  public function st_demo_importer_add_free_submenu() {
      add_submenu_page(
          'stdi_main_welcome_page',
          esc_html('Free Themes'),
          esc_html('Free Themes'),
          'manage_options',
          'stdi_free_templates_submenu',
          array( $this, 'st_demo_importer_free_templates_callback' )
      );
  }

  public function st_demo_importer_free_templates_callback() {

    $templates_arr = $this->stdi_free_templates_api();
    ?>
    <div class="wrap">
      
      <div class="stdi-html-content">
        <h2><?php echo esc_html('Explore Our Free Themes'); ?></h2>
        <p><?php echo esc_html('Browse through our collection of professional WordPress themes designed for various niches.'); ?></p>
      </div>

      <div class="stdi-hover-image-container">
        <a href="https://spectrathemes.com/themes/wordpress-theme-bundle/" target="_blank">
          <img class="stdi-hover-image" src="<?php echo esc_url( STDI_URL . 'theme-wizard/assets/images/st-bundle-banner.png' ); ?>" alt="Special Offer">
        </a>
      </div>

      <div class="stdi-grid-card row stdi-theme-templates">
        <?php
        if ( isset($templates_arr['data']) && is_array($templates_arr['data']) ) {
          foreach ( $templates_arr['data'] as $key => $theme ) {

            $free_theme_text_domain = $theme->free_theme_text_domain;
            $thumbnail_url      = $theme->thumbnail_url;
            $get_the_title      = $theme->get_the_title;

            $install_url = admin_url('admin.php?action=install_free_theme&theme=' . esc_attr($free_theme_text_domain));
            ?>
            <div class="stdi-templates col-lg-4 col-md-6 col-12">
              <div class="stdi-templates-inner">
                <div class="stdi-templates-inner-image-head">
                  <img class="stdi-templates-inner-image" src="<?php echo esc_url($thumbnail_url); ?>" width="100" height="100" alt="<?php echo esc_url($get_the_title); ?>">
                </div>
                <div class="stdi-templates-inner-description">
                  <h3><?php echo esc_html($get_the_title); ?></h3>
                  <div class="stdi-templates-inner-button">
                    <a href="#" class="stdi-templates-inner-button-install install-button" data-theme="<?php echo esc_attr($free_theme_text_domain); ?>">
                      <span class="stdi-install-text"><?php echo esc_html('Install'); ?></span>
                      <span class="stdi-install-loader" style="display: none;">
                        <div class="stdi-spinner"></div>
                      </span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php }
        } ?>
      </div>
    </div>
  <?php }

  public function stdi_install_and_activate_free_theme_from_wporg() {
    check_ajax_referer('stdi_install_free_theme_nonce', '_wpnonce');

    // Check user permissions
    if (!current_user_can('install_themes') || !isset($_POST['theme'])) {
        wp_send_json_error(array('message' => 'You do not have sufficient permissions to install themes.'));
    }

    $theme_slug = sanitize_text_field($_POST['theme']);

    include_once ABSPATH . 'wp-admin/includes/theme.php';
    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

    // Check if the theme is already installed
    $installed_themes = wp_get_themes(array('errors' => true));
    if (array_key_exists($theme_slug, $installed_themes)) {
        // If theme is already installed, check if it's already active
        $current_theme = wp_get_theme();
        if ($current_theme->get('TextDomain') === $theme_slug) {
            // Set a transient or option to handle the redirect
            set_transient('stdi_theme_activation_redirect', true, 30);
            wp_send_json_success();
        }

        // If theme is not activated, activate it
        switch_theme($theme_slug);
        // Set a transient or option to handle the redirect
        set_transient('stdi_theme_activation_redirect', true, 30);
        wp_send_json_success();
    }

    // If theme is not installed, proceed with installation
    $api = themes_api('theme_information', array(
        'slug'   => $theme_slug,
        'fields' => array('sections' => false),
    ));

    if (is_wp_error($api)) {
        wp_send_json_error(array('message' => 'Theme not found.'));
    }

    $upgrader = new Theme_Upgrader();
    ob_start();
    $install_result = $upgrader->install($api->download_link);
    ob_end_clean();

    if (is_wp_error($install_result)) {
        wp_send_json_error(array('message' => 'Theme installation failed.'));
    }

    // Activate the theme
    switch_theme($theme_slug);

    // Set a transient or option to handle the redirect
    set_transient('stdi_theme_activation_redirect', true, 30);
    wp_send_json_success();
  }

  public function stdi_handle_theme_redirect() {
    if (get_transient('stdi_theme_activation_redirect')) {
        delete_transient('stdi_theme_activation_redirect');
        wp_redirect(admin_url('admin.php?page=stdemoimporter-wizard'));
        exit;
    }
  }
}