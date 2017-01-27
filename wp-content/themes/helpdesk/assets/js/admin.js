(function($){
	$(document).ready(function() {

	    var $page_template = $('#page_template');
	        $metabox = $('#redux-helpdesk-metabox-layout');

	    $page_template.change(function() {
	        if ($(this).val() === 'template-home.php') {
	            $metabox.hide();
	        } else {
	            $metabox.show();
	        }
	    }).change();

	});
})(jQuery);
