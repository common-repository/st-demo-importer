function openCity(evt, cityName) {
    jQuery(".wee-tabcontent").hide();
    jQuery(".tablinks").removeClass("active");
    jQuery("#" + cityName).show();
    jQuery(evt.currentTarget).addClass("active");
}