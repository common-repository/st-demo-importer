<?php

class StWelcome {

    private $whizzie_instance;

    public function __construct($whizzie_instance) {
        $this->whizzie_instance = $whizzie_instance;
    }

    public function render_stdi_main_welcome_page() { ?>

        <div class="wrap">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <div class="stdi-card text-center">
                            <h2>Welcome to the new and improved ST Demo Importer WordPress Plugin</h2>
                            <div class="welcome-image-container">
                                <img src="<?php echo esc_url( STDI_URL . 'theme-wizard/assets/images/welcome.png' ); ?>" alt="Envato Elements WordPress Plugin Video">
                            </div>
                            <div class="stdi-card-body">
                                <div class="links">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=stdemoimporter-wizard')); ?>" class="btn btn-primary stdi-btn-primary">Demo Importer</a>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=stdi_premium_templates_submenu')); ?>" class="btn btn-primary stdi-btn-primary">Premium Themes</a>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=stdi_free_templates_submenu')); ?>" class="btn btn-primary stdi-btn-primary">Free Themes</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php }
}