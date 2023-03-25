jQuery(document).ready(function(e) {
    jQuery(document.body).on('added_to_cart',function() {
		open_cart_sidebar();
	});
	
	
	if (jQuery('#billing_wooccm11').length) {
		jQuery('#billing_wooccm11').datepicker({
	
			changeMonth: true,
	
			changeYear: true,
			
			dateFormat : "dd/mm/yy",
	
			yearRange: "-100:+0",
	
		}).prop('readonly', 'readonly');
	}
	
	jQuery('#side_filter_toggle').on('click',function() {
		jQuery(this).closest('#side_filters').toggleClass('active');
		jQuery(this).closest('#side_filter_con').toggleClass('active');
	});
	
	jQuery('#side_filters_cover').on('click',function() {
		jQuery('#side_filter_toggle').click();
	});
	
	jQuery('#text_filter_button button').on('click',function() {
		setTimeout(function() {
			jQuery('#side_filter_toggle').click();
		},1000);
	});
	
	jQuery('.jet-checkboxes-list input[type=checkbox].jet-checkboxes-list__input').on('click',function(e) {

		var value = this.value;
		
		console.log(jQuery('input[type=checkbox][value="'+value+'"].jet-checkboxes-list__input'));
		
		jQuery('input[type=checkbox][value="'+value+'"].jet-checkboxes-list__input').not(this).prop('checked',jQuery(this).prop('checked')).trigger('change');
		
		console.log(jQuery('body').find('.jet-checkboxes-list__input:checked').length);
		
		if (!jQuery('body').find('.jet-checkboxes-list__input:checked').length) {
			window.location.href = '/shop';
		}
		

	});
	
    jQuery( document ).on( 'jet-filter-content-rendered', function() {
								
		var pagination = jQuery('.woocommerce-pagination');
		
		if (pagination.length) {
			var links = pagination.find('a');
			
			for (var i=0;i<links.length;i++) {
				var lnk = jQuery(links[i]);
				
				
				//lnk.attr('href',lnk.attr('href').replace('&jsf_ajax=1',''));
				
				
				var href = lnk.attr('href').split("/");
				var page = '';
				
				for (var j=0;j<href.length;j++) {
					if (href[j] == 'page') {
						page = href[j+1];
						break;
					}
				}
				
				console.log(page);
				
				href = '/shop/page/'+page+'/'+window.location.search;
								
				console.log(href);
				
				lnk.attr('href',href);
			}
			
		}
	});
	
});