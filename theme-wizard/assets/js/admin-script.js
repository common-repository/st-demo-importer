jQuery(document).ready(function($) {
    $('.install-button').on('click', function(e) {
        e.preventDefault();

        var button = $(this);
        var themeSlug = button.data('theme');
        var loader = button.find('.stdi-install-loader');
        var text = button.find('.stdi-install-text');

        loader.show();
        text.hide();

        $.ajax({
            url: stdi_admin_params.ajaxurl,
            type: 'POST',
            data: {
                action: 'install_free_theme',
                theme: themeSlug,
                _wpnonce: stdi_admin_params.wpnonce
            },
            success: function(response) {
                location.reload();
            },
            error: function() {
                alert('An error occurred. Please try again.');
                loader.hide();
                text.show();
            }
        });
    });
});