jQuery(document).ready( function($) {
	$('#instructions').hide();
	
	$('.bucketlister-table th').each( function() {
		$(this)
			.data('text', $(this).text())
			.text('')
			.html('<a href="#sort-desc" class="bucketlister-up"></a>');
	});
	$('.bucketlister-table th').each( function() {
		$(this)
			.find('a')
			.text($(this).data('text'));
	});
	var processing = false;
	$('.bucketlister-table th a').click( function() {
		if ( processing ) {
			return false;
		} 
		processing = true;
		var column = $(this).text();
		var order = $(this).attr('href').substr(1);
		var table = $(this).closest('table').attr('id');
		if ( order == 'sort-asc' )
			$(this)
				.attr('href', '#sort-desc')
				.removeClass('bucketlister-down')
				.addClass('bucketlister-up')				
		else		
			$(this)
				.attr('href', '#sort-asc')
				.removeClass('bucketlister-up')
				.addClass('bucketlister-down')				
				

		bucketlisterAjax(column, order, table)
		return false;
	});
	var result = false;
	var bucketlisterAjax = function(column, order, table) {
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: { 
				action: 'bucketlister_ajax',
				column: column,
				order: order
			},
			beforeSend: function() {
				$('<div id="loading"></div>')
					.insertAfter('#' + table);
			}, 
			complete: function() { 
				if ( !result )
					$('#' + table + ' tbody')
						.html('<tr><td><p>ERROR</p></td></tr>');
				processing = false;
				$('#loading').remove();
			},
			success: function(stuff){ 
				result = true;
				$('#' + table + ' tbody')
					.html(stuff);
			}
		});
		
	}
	
});