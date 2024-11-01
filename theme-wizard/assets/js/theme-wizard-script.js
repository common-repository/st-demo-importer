var Whizzie = (function($) {

  var t;
  var current_step = '';
  var step_pointer = '';

  // callbacks from form button clicks.
  var callbacks = {
    do_next_step: function(btn) {
      do_next_step(btn);
    },
    install_plugins: function(btn) {
      var plugins = new PluginManager();
      plugins.init(btn);
    },
    install_widgets: function(btn) {
      var widgets = new WidgetManager();
      widgets.init(btn);
    }
  };

  function window_loaded() {
    // Get all steps and find the biggest
    // Set all steps to same height
    var maxHeight = 0;

    $('.whizzie-menu li.step').each(function(index) {
      $(this).attr('data-height', $(this).innerHeight());
      if ($(this).innerHeight() > maxHeight) {
        maxHeight = $(this).innerHeight();
      }
    });

    $('.whizzie-menu li .detail').each(function(index) {
      $(this).attr('data-height', $(this).innerHeight());
      $(this).addClass('scale-down');
    });


    $('.whizzie-menu li.step').css('height', '100%');
    $('.whizzie-menu li.step:first-child').addClass('active-step');
    $('.wee-whizzie-nav li:first-child').addClass('active-step');

    $('.wee-whizzie-wrap').addClass('loaded');

    // init button clicks:
    $('.do-it').on('click', function(e) {
      e.preventDefault();
      step_pointer = $(this).data('step');
      current_step = $('.step-' + $(this).data('step'));
      $('.wee-whizzie-wrap').addClass('spinning');
      if ($(this).data('callback') && typeof callbacks[$(this).data('callback')] != 'undefined') {
        // we have to process a callback before continue with form submission
        callbacks[$(this).data('callback')](this);
        return false;
      } else {
        return true;
      }
    });
    $('.key-activation-tab-click').on('click', function() {
      document.querySelector(".tab button.tablinks[data-tab='wee_theme_activation']").click();
    });
  }

  function do_next_step(btn) {
    $('.nav-step-plugins').attr('data-enable', 1);
    current_step.removeClass('active-step');
    $('.nav-step-' + step_pointer).removeClass('active-step');
    current_step.addClass('done-step');
    $('.nav-step-' + step_pointer).addClass('done-step');
    current_step.fadeOut(500, function() {
      current_step = current_step.next();
      step_pointer = current_step.data('step');
      current_step.fadeIn();
      current_step.addClass('active-step');
      $('.nav-step-' + step_pointer).addClass('active-step');
      $('.wee-whizzie-wrap').removeClass('spinning');
    });
  }

  function PluginManager() {
    $('.step-loading').css('display', 'block');
    $('.nav-step-widgets').attr('data-enable', 1);
    var complete;
    var items_completed = 0;
    var current_item = '';
    var $current_node;
    var current_item_hash = '';

    function ajax_callback(response) {
      if (typeof response == 'object' && typeof response.message != 'undefined') {
        $current_node.find('.wizard-plugin-status').text(response.message);
        if (typeof response.url != 'undefined') {
          // we have an ajax url action to perform.

          if (response.hash == current_item_hash) {
            $current_node.find('.wizard-plugin-status').text("failed");
            find_next();
          } else {

            current_item_hash = response.hash;
            jQuery.post(response.url, response, function(response2) {
              process_current();
              $current_node.find('.wizard-plugin-status').text(response.message + st_demo_importer_pro_whizzie_params.verify_text);
            }).fail(ajax_callback);

          }

        } else if (typeof response.done != 'undefined') {
          // finished processing this plugin, move onto next
          find_next();
        } else {
          // error processing this plugin
          find_next();
        }
      } else {
        // error - try again with next plugin
        $current_node.find('.wizard-plugin-status').text("ajax error");
        find_next();
      }
    }

    function process_current() {
      if (current_item) {
        // query our ajax handler to get the ajax to send to TGM
        // if we don't get a reply we can assume everything worked and continue onto the next one.
        jQuery.post(st_demo_importer_pro_whizzie_params.ajaxurl, {
          action: 'setup_plugins',
          wpnonce: st_demo_importer_pro_whizzie_params.wpnonce,
          slug: current_item
        }, ajax_callback).fail(ajax_callback);
      }
    }

    function find_next() {
      var do_next = false;
      if ($current_node) {
        if (!$current_node.data('done_item')) {
          items_completed++;
          $current_node.data('done_item', 1);
        }
        $current_node.find('.spinner').css('visibility', 'hidden');
      }
      var $li = $('.whizzie-do-plugins li');
      $li.each(function() {
        if (current_item == '' || do_next) {
          current_item = $(this).data('slug');
          $current_node = $(this);
          process_current();
          do_next = false;
          jQuery(this).find('.spinner').css('display', 'inline-block');
        } else if ($(this).data('slug') == current_item) {
          do_next = true;
          jQuery(this).find('.spinner').css('display', 'none');
        }
      });
      if (items_completed >= $li.length) {
        // finished all plugins!
        complete();
        $('.wz-require-plugins').css('display', 'none');
        $('.step.step-plugins .button').text('');
        $('.step.step-plugins .button').text('Skip To Next Step');

        $('.step.step-plugins .summary p').text('');
        $('.step.step-plugins .summary p').text('All required plugins are already installed. click on the below button to go next step.');

      }
    }

    return {

      init: function(btn) {
        if (jQuery('.step.step-plugins .button').text() != "Skip To Next Step") {

          complete = function() {
            do_next_step();
          };
          find_next();
        } else {
          do_next_step();
        }
      }
    }
  }

  function WidgetManager() {
    $('.step-loading').css('display', 'block');

    jQuery('.wp-setup-finish .wz-btn-customizer').css('display', 'inline-block');
    jQuery('.wp-setup-finish .wz-btn-builder').css('display', 'none');

    function import_widgets() {
      jQuery.post(
        st_demo_importer_pro_whizzie_params.ajaxurl, {
          action: 'st_demo_importer_setup_elementor',
          wpnonce: st_demo_importer_pro_whizzie_params.wpnonce
        }, ajax_callback_customizer).fail(ajax_callback_customizer);
    }
    $('.nav-step-done').attr('data-enable', 1);

    return {
      init: function(btn) {
        ajax_callback = function(response) {
          var obj = JSON.parse(response);
          if (obj.home_page_url != "") {
            jQuery('.wz-btn-builder').attr('href', obj.home_page_url);
          }
          do_next_step();
        }
        ajax_callback_customizer = function(response) {
          console.log('response', response);
          jQuery('.wz-btn-customizer').attr('href', response.edit_post_link);
          do_next_step();
        }

        import_widgets();
      }
    }
  }

  return {
    init: function() {
      t = this;
      $(window_loaded);
    },
    callback: function(func) {}
  }
  // test end //
})(jQuery);

Whizzie.init();

jQuery(document).ready(function() {
  var current_icon_step = '';

  jQuery('.wp-setup-finish .wp-finish-btn a').click(function() {
    jQuery('.wee-tab-sec button.tablinks:nth-child(2)').addClass('active');
  });

  jQuery('.wizard-icon-nav li').click(function() {

    var tabenable = jQuery(this).attr('data-enable');
    if (tabenable == 1) {
      current_icon_step = jQuery(this).attr('wizard-steps');
      jQuery('.wp-wizard-menu-page li.step').removeClass('active-step');
      jQuery('.wp-wizard-menu-page li.step').css('display', 'none');
      jQuery('.wizard-icon-nav li').removeClass('active-step');
      jQuery('.wp-wizard-menu-page .' + current_icon_step).addClass('active-step');
      jQuery('.wp-wizard-menu-page .' + current_icon_step).css('display', 'block');
      jQuery(this).addClass('active-step');
    }
  });

  var plugin_count = "";
  plugin_count = jQuery('.wizard-plugin-count').text();
  if (plugin_count == 0) {
    jQuery('.step.step-plugins a.button').text('');
    jQuery('.step.step-plugins a.button').text('Skip To Next Step');
    jQuery('.wz-require-plugins').css('display', 'none');
    jQuery('.step.step-plugins .summary p').text('');
    jQuery('.step.step-plugins .summary p').text('All required plugins are already installed. click on the below button to go next step.');

  } else {
    jQuery('.step.step-plugins a.button').text('');
    jQuery('.step.step-plugins a.button').text('Install Plugins');
    jQuery('.wz-require-plugins').css('display', 'block');
  }

  jQuery('#wp-demo-setup-guid ul li a').click(function() {
    var doc_url = jQuery(this).attr('doc-video-url');
    jQuery('.get-stared-page-wrap .wz-video-model').css('display', 'block');
    jQuery('.get-stared-page-wrap .wz-video-model iframe').attr('src', doc_url)
  });
  jQuery('.wz-video-model .dashicons-no').click(function() {
    jQuery('.get-stared-page-wrap .wz-video-model').css('display', 'none');
    jQuery('.get-stared-page-wrap .wz-video-model iframe').attr('src', '')
  });


  jQuery('#st_demo_importer_pro_license_form button[id="change--key"]').on('click', function() {
    var $st_demo_importer_pro_license_form = jQuery('#st_demo_importer_pro_license_form');
    $st_demo_importer_pro_license_form.find('input[name="st_demo_importer_pro_license_key"]').val('');
    $st_demo_importer_pro_license_form.find('input[name="st_demo_importer_pro_license_key"]').attr('disabled', false);
    $st_demo_importer_pro_license_form.find('button[type="submit"]').val('');
    $st_demo_importer_pro_license_form.find('button[type="submit"]').attr('disabled', false);
    $st_demo_importer_pro_license_form.find('button[type="submit"]').text('Activate');
    jQuery('#start-now-next').hide();
    jQuery(this).remove();
  });
  jQuery('form#st_demo_importer_pro_license_form').on('submit', function(e) {
    jQuery('.wee_theme_activation_spinner').show();
    e.preventDefault();
    var key_to_send = jQuery('form#st_demo_importer_pro_license_form').serializeArray()[0].value;
    if (key_to_send == "") {
      alert('Please Enter the license key first!');
      return;
    } else {
      jQuery.post(
        st_demo_importer_pro_whizzie_params.ajaxurl, {
          action: 'wz_activate_st_demo_importer_pro',
          wpnonce: st_demo_importer_pro_whizzie_params.wpnonce,
          st_demo_importer_pro_license_key: key_to_send
        },

        function(data, status) {
          if (status == 'success') {
            if (data.status) {
              jQuery.notify(data.msg, {
                position: "right bottom",
                className: "success"
              });

              location.reload(true);

              jQuery('.wee_theme_activation_spinner').hide();
              jQuery('form#st_demo_importer_pro_license_form button[type="submit"]').css("background-color:#0a9d2c");
              jQuery('form#st_demo_importer_pro_license_form button[type="submit"]').text('Activated');
              jQuery('form#st_demo_importer_pro_license_form button[type="submit"]').attr('disabled', 'disabled');

            } else {
              jQuery.notify(data.msg, {
                position: "right bottom"
              });
              jQuery('.wee_theme_activation_spinner').hide();
            }
          } else {
            jQuery('.wee_theme_activation_spinner').hide();
          }
        },
        'json');
      }
    });
});
