<?php

class StPremium_Templates {

    private $whizzie_instance;

    public function __construct($whizzie_instance) {
        $this->whizzie_instance = $whizzie_instance;
    }

    public function stdi_premium_templates_api() {

        $paged = isset($_POST['paged']) ? $_POST['paged'] : 1;
        $category_id = isset($_POST['category_id']) ? $_POST['category_id'] : 0;
        $search = isset($_POST['search_val']) ? $_POST['search_val'] : '';

        $themes_array = array();

        $endpoint = STDI_ADMIN_CUSTOM_ENDPOINT . 'get_premium_theme_templates_arr';
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

    public function st_demo_importer_add_premium_submenu() {
        add_submenu_page(
            'stdi_main_welcome_page',
            esc_html('Premium Themes'),
            esc_html('Premium Themes'),
            'manage_options',
            'stdi_premium_templates_submenu',
            array( $this, 'st_demo_importer_premium_templates_callback' )
        );
    }

    public function st_demo_importer_premium_templates_callback() {

      $templates_arr = $this->stdi_premium_templates_api();
      ?>
      <div class="wrap">
        
        <div class="stdi-html-content">
          <h2><?php echo esc_html('Explore Our Premium Themes'); ?></h2>
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

              $product_permalink  = $theme->product_permalink;
              $live_demo          = $theme->live_demo;
              $thumbnail_url      = $theme->thumbnail_url;
              $get_the_title      = $theme->get_the_title;
              ?>
              <div class="stdi-templates col-lg-4 col-md-6 col-12">
                <div class="stdi-templates-inner">
                  <div class="stdi-templates-inner-image-head">
                    <img class="stdi-templates-inner-image" src="<?php echo esc_url($thumbnail_url); ?>" width="100" height="100" alt="<?php echo esc_url($get_the_title); ?>">
                  </div>
                  <div class="stdi-templates-inner-description">
                    <h3><?php echo esc_html($get_the_title); ?></h3>
                    <div class="stdi-templates-inner-button">
                      <a target="_blank" href="<?php echo esc_url($product_permalink); ?>" class="stdi-templates-inner-button-buy"><?php echo esc_html('Buy Now'); ?></a>
                      <a target="_blank" href="<?php echo esc_url($live_demo); ?>" class="stdi-templates-inner-button-preview"><?php echo esc_html('Demo'); ?></a>
                    </div>
                  </div>
                </div>
              </div>
            <?php }
          } ?>
        </div>
      </div>
    <?php }
}