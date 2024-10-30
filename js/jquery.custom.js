jQuery(document).ready(function($){
    var colorPickerOptions = {
		defaultColor: "#f5d76e",
		change: function(event, ui){},
		clear: function() {},
		hide: true,
		palettes: true
    };
    jQuery('.coinhiveWidget-color-picker').wpColorPicker(colorPickerOptions);
	jQuery('#element .wp-picker-clear').trigger('click');
});
