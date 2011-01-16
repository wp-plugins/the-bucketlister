jQuery(document).ready( function($) {
	$('#bucketlister-instructions').hide();
	$(' <span class="bucketlister-show-instructions"> <a href="#bucket-show">Show me!</a></span>')
		.css( 'font-size', '12px')
		.appendTo('.bucketlister-instructions-title');
	var showInstructions = false;
	$('.bucketlister-show-instructions').click( function() {
		if ( showInstructions ) {
			$('#bucketlister-instructions').fadeOut();
			$('.bucketlister-show-instructions a').text('Show me!');
			showInstructions = false;
		} else {
			$('#bucketlister-instructions').fadeIn();
			$('.bucketlister-show-instructions a').text('Hide me!');
			showInstructions = true;
		}
	});
	
	$('#bucketlister-new-item').keyup( function() {
		$('.bucketlister-new-item-container').removeClass('bucketlister-error');
	});

	var adminAjax = function( theItem, method ) {
		$.ajax({
			type: "POST",
			url: ajaxurl,
			dataType: 'json',
			data: { 
				action: 'bucketlister_admin_ajax',
				method: method,
				stuff: theItem
			},
			beforeSend: function() {
			}, 
			complete: function() { 

			},
			success: function(stuff){ 
				
				if ( stuff.error == false ) {
					if ( method == 'delete' ) {
						$('tr#item-' + theItem.id)
							.stop(true)
							.css('background', '#FFEBE8').fadeOut('slow');	
					} else if ( method == 'add' ) {
						var category = '';
						if ( theItem.category != 'none' ) {
							category = $("#bucketlister-category option[value=" + theItem.category + "]").text();
						}
						
						$("<tr id='item-" + stuff.id + "'><td class='bucketlister-id'>" + stuff.id + "</td><td class='bucketlister-item'>" + theItem.name + "</td><td  class='bucketlister-category'>" + category + "</td><td  class='bucketlister-target'>" + stuff.targetDate + "</td><td  class='bucketlister-completed'>" + stuff.dateCompleted + "</td><td  class='bucketlister-buttons'><input type='submit' class='button-secondary bucketlister-edit' id='edit-" + stuff.id + "' name='bucketlister-edit[" + stuff.id + "]' value='Edit'><input type='submit' class='button-secondary bucketlister-delete' id='bucketlister-delete-" + stuff.id + "' name='bucketlister-delete[" + stuff.id + "]' value='Delete'></td></tr>")
							.css('background-color', '#FFEBE8')
							.appendTo('.bucketlister-table tbody')
							.animate( { 'background-color' : '#ffffff' }, 2000 );
						
						$('#bucketlister-new-item').val('');
					} else if ( method == 'update' ) {
						$("tr#item-" + theItem.itemId )
							.stop()
							.css('background-color', '#FFEBE8')
							.animate( { 'background-color' : '#ffffff' }, 2000 );
						
					} else if ( method == 'addcategory' ) {
						$('#bucketlister-add-category')
							.stop()
							.val('')
							.css('background-color', '#FFEBE8')
							.animate( { 'background-color' : '#ffffff' }, 2000 );						
						$('#bucketlister-categories, #bucketlister-category, #bucketlister-edit-category')
							.append('<option value="' + stuff.id + '">' + theItem.category + '</option>')
							.css('background-color', '#FFEBE8')
							.animate( { 'background-color' : '#ffffff' }, 2000 );
					} else if ( method == 'deletecategory' ) {
						$('#bucketlister-categories option[value="' + theItem.id + '"], #bucketlister-category option[value="' + theItem.id + '"], #bucketlister-edit-category option[value="' + theItem.id + '"]')
							.remove();
						$('#bucketlister-categories, #bucketlister-category, #bucketlister-edit-category')
							.css('background-color', '#FFEBE8')
							.animate( { 'background-color' : '#ffffff' }, 2000 );
							
					}
				}
			}
		});
		
	}
	var editOpen = false;
	var currentId;
	var currentItem = {
		itemId: '',
		name: '',
		category: '',
		categoryName: '',
		itemButtons: '',
		target: {},
		completed: {},
		closeEditor: function() {
			$('tr#item-' + currentId + ' .bucketlister-id').text(currentId);
			$('tr#item-' + currentId + ' .bucketlister-item').text(currentItem.name);
			$('tr#item-' + currentId + ' .bucketlister-category').text(currentItem.categoryName);
			$('tr#item-' + currentId + ' .bucketlister-target').text(currentItem.target.day + ' ' + currentItem.target.fullmonth + ' ' + currentItem.target.year);
						
			if ( currentItem.completed.day != '' && currentItem.completed.day != 'none') {
				$('tr#item-' + currentId + ' .bucketlister-completed').text(currentItem.completed.day + ' ' + currentItem.completed.fullmonth + ' ' + currentItem.completed.year);
			} else {
				$('tr#item-' + currentId + ' .bucketlister-completed').text('');
			}
				
			
			$('tr#item-' + currentId + ' .bucketlister-buttons').html(currentItem.itemButtons);

		}
	};
	
	$('.bucketlister-edit').live('click', function() {
		if ( editOpen ) {
			console.log('edit is open');
			currentItem.closeEditor();
		} 
		currentId =  $(this).attr('id').substr(5);
		currentItem.itemId = currentId;
		currentItem.name = $('tr#item-' + currentId + ' .bucketlister-item').text();
		currentItem.category = '';
		currentItem.categoryName = $('tr#item-' + currentId + ' .bucketlister-category').text();
		currentItem.itemButtons = $('tr#item-' + currentId + ' .bucketlister-buttons').html();
		targetDay = $('tr#item-' + currentId + ' .bucketlister-target').text() + ' 00:00:00';
		var targetDate = new Date( targetDay);
		currentItem.target = {
			day: targetDate.getDate(),
			month: targetDate.getMonth(),
			fullmonth: $('#bucketlister-target-month option[value="' + ( targetDate.getMonth() + 1 ) + '"]').text(),
			year: targetDate.getFullYear()
		}
		currentItem.completed = {
			day: 'none',
			month: 'none',
			fullmonth: '',
			year: 'none'
		}
		if ( $('tr#item-' + currentId + ' .bucketlister-completed').text() != '' ) {
			completedDay = $('tr#item-' + currentId + ' .bucketlister-completed').text();
			var completedDate = new Date( completedDay );
			currentItem.completed = {
				day: completedDate.getDate(),
				month: completedDate.getMonth(),
				year: completedDate.getFullYear()
			}
		}
		$('tr#item-' + currentId + ' td').each(function() {
			$(this).text('');	
		});	
		$('#bucketlister-new-item')
			.clone()
			.attr('id', 'bucketlister-edit-item')
			.val(currentItem.name)
			.appendTo('tr#item-' + currentId + ' .bucketlister-item');
		
		$('#bucketlister-category')
			.clone()
			.attr('id', 'bucketlister-edit-category')
			.appendTo('tr#item-' + currentId + ' .bucketlister-category');
		$('#bucketlister-edit-category option').each( function() {
			if ( $(this).text() == currentItem.categoryName ) {
				currentItem.category = $(this).val();
				$(this).attr('selected', 'selected');		
			}
		});
		
		$('#bucketlister-target-day')
			.clone()
			.attr('id', 'bucketlister-edit-target-day')
			.appendTo('tr#item-' + currentId + ' .bucketlister-target');
		$('#bucketlister-edit-target-day option[value="' + currentItem.target.day + '"]').attr('selected', 'selected');
		$('#bucketlister-target-month')
			.clone()
			.attr('id', 'bucketlister-edit-target-month')
			.appendTo('tr#item-' + currentId + ' .bucketlister-target');
		$('#bucketlister-edit-target-month option[value="' + ( currentItem.target.month + 1 ) + '"]').attr('selected', 'selected');
		$('#bucketlister-target-year')
			.clone()
			.attr('id', 'bucketlister-edit-target-year')
			.appendTo('tr#item-' + currentId + ' .bucketlister-target');
		$('#bucketlister-edit-target-year option[value="' + currentItem.target.year + '"]').attr('selected', 'selected');

		$('#bucketlister-completed-day')
			.clone()
			.attr('id', 'bucketlister-edit-completed-day')
			.appendTo('tr#item-' + currentId + ' .bucketlister-completed');
		$('#bucketlister-edit-completed-day option[value="' + currentItem.completed.day + '"]').attr('selected', 'selected');
		$('#bucketlister-completed-month')
			.clone()
			.attr('id', 'bucketlister-edit-completed-month')
			.appendTo('tr#item-' + currentId + ' .bucketlister-completed');
		$('#bucketlister-edit-completed-month option[value="' + ( currentItem.completed.month + 1 ) + '"]').attr('selected', 'selected');
		$('#bucketlister-completed-year')
			.clone()
			.attr('id', 'bucketlister-edit-completed-year')
			.appendTo('tr#item-' + currentId + ' .bucketlister-completed');
		$('#bucketlister-edit-completed-year option[value="' + currentItem.completed.year + '"]').attr('selected', 'selected');
		
		$('tr#item-'+ currentId + ' .bucketlister-buttons').append('<input type="submit" id="bucketlister-update" value="Update" class="button-secondary" /><input type="submit" id="bucketlister-cancel" value="Cancel" class="button-secondary" />');
		
		editOpen = true;
		return false;
	});
	
	$('#bucketlister-update').live('click', function() {
		var errorMessage = '';
		currentItem.name = $('#bucketlister-edit-item').val();
		if ( $('#bucketlister-edit-category :selected').val() != 'none' ) { 
			currentItem.categoryName = $('#bucketlister-edit-category :selected').text();
			currentItem.category = $('#bucketlister-edit-category :selected').val();			
		} else {
			currentItem.category = 'none';
			currentItem.categoryName = '';
		}
		currentItem.target.day = $('#bucketlister-edit-target-day :selected').val();
		currentItem.target.month = $('#bucketlister-edit-target-month :selected').val();		
		currentItem.target.fullmonth = $('#bucketlister-edit-target-month :selected').text();		
		currentItem.target.year = $('#bucketlister-edit-target-year :selected').val();		
		currentItem.completed.day = $('#bucketlister-edit-completed-day :selected').val();
		currentItem.completed.month = $('#bucketlister-edit-completed-month :selected').val();		
		currentItem.completed.fullmonth = $('#bucketlister-edit-completed-month :selected').text();		
		currentItem.completed.year = $('#bucketlister-edit-completed-year :selected').val();		
		if ( currentItem.name == '' ) {
			$('#item-' + currentId + ' .bucketlister-item').addClass('bucketlister-error');
			errorMessage = '<p>You need to enter an item</p>';
		}
		if ( currentItem.completed.day != 'none' ) {
			if ( currentItem.completed.month == 'none' || currentItem.completed.year == 'none' ) {

				$('#item-' + currentId + ' .bucketlister-completed').addClass('bucketlister-error');
				errorMessage += '<p>If you wish to insert a Completed date, You need to specify the Day, Month and Year</p>';	
			}
		}
		if ( currentItem.target.day == 'none' || currentItem.target.month == 'none' ||currentItem.target.year == 'none'  ) {
			
			$('#item-' + currentId + ' .bucketlister-target').addClass('bucketlister-error');
			errorMessage += '<p>You need to specify a full Target date - Day, Month and Year</p>';	
		}
		errorMessage += checkDateErrors( 'Target',currentItem.target, '#item-' + currentId + ' .bucketlister-target' );
		errorMessage += checkDateErrors( 'Completed', currentItem.completed, '#item-' + currentId + ' .bucketlister-completed');
		
		
		if ( errorMessage != '' ) {
			$('input,select').removeClass('bucketlister-error');
			
			$('.error').remove();
			$('#bucketlister-container .bucketlister-table').after('<div class="error below-h2">' + errorMessage + '</div>');
			$('.error').stop().delay(5000).fadeOut(1000).queue(function() {
				$('input,select').removeClass('bucketlister-error');
				$('.error').remove();
			});

		} else {
		currentItem.closeEditor();
			adminAjax(currentItem, 'update');
			editOpen = false;		
		}
		return false;
	});

	$('#bucketlister-cancel').live('click', function() {
		currentItem.closeEditor();
		editOpen = false;
		return false;
	});

	$('.bucketlister-delete').live( 'click',  function() {
		var id = $(this).attr('id').substr(20);
		var deleteItem = {
			id: id	
		}
		adminAjax(deleteItem, 'delete');
		return false;
	});
	
	$('#bucketlister-go').click( function() {
		var pause = false;
		var itemName = $('#bucketlister-new-item').val();
		var targetDay = $('#bucketlister-target-day').val();
		var targetMonth = $('#bucketlister-target-month').val();
		var targetYear = $('#bucketlister-target-year').val();
		var completedDay = $('#bucketlister-completed-day').val();
		var completedMonth = $('#bucketlister-completed-month').val();
		var completedYear = $('#bucketlister-completed-year').val();
		var category = $('#bucketlister-category').val();		
		var newItem = {
			name: itemName,
			target: '',
			completed: '',
			category: category
		}
		newItem.target = {
			day: targetDay,
			month: targetMonth,
			year: targetYear	
		}
		newItem.completed = {
			day: completedDay,
			month: completedMonth,
			year: completedYear	
		}
		
		error = '';
		
		if ( newItem.completed.day != 'none' ) {
			if ( newItem.completed.month == 'none' || newItem.completed.year == 'none' ) {

				$('.bucketlister-completed-date-container').addClass('bucketlister-error');
				error += '<p>If you wish to insert a Completed date, You need to specify the Day, Month and Year</p>';	
			}
		}
		
		if ( newItem.target.day == 'none' || newItem.target.month == 'none' ||newItem.target.year == 'none'  ) {
			$('.bucketlister-target-date-container').addClass('bucketlister-error');
			error += '<p>You need to specify a full Target date - Day, Month and Year</p>';	
		}
		
		if ( newItem.name == '' ) {
			$('.bucketlister-new-item-container').addClass('bucketlister-error');
			error += '<p>You need to specify an item</p>';	
		}
		error += checkDateErrors( 'Target',newItem.target, '.bucketlister-target-date-container' );
		error += checkDateErrors( 'Completed', newItem.completed, '.bucketlister-completed-date-container');
		
		if ( error != '' ) {
			
			$('.error').remove();
			$('#bucketlister-container .bucketlister-table').after('<div class="error below-h2">' + error + '</div>');
			$('.error').stop().delay(5000).fadeOut(1000).queue(function() {
				$('input,select').removeClass('bucketlister-error');
				$('.error').remove();
			});
		} else {
			adminAjax(newItem, 'add');	
			$('tfoot td').each(function() {
				$(this).removeClass('bucketlister-error');
			});
		}
		error = '';
		return false;
	});	
	
	var bucketlistercategory = {
		name: '', 
		id:	''
	}
	
	$('#bucketlister-delete-category').click( function(e) {
		bucketlistercategory.id = $('#bucketlister-categories').val();
		adminAjax(bucketlistercategory, 'deletecategory')
		e.preventDefault();
	});
	
	$('#inline-form-submit-4').click( function(e) {
		if ( $('#bucketlister-add-category').val() == '' ) {
			$('#bucketlister-add-category')
				.closest('td')
				.addClass('bucketlister-error')
				.delay(2000)
				.append('<p class="bucketlister-msg">Gots to add a category there, friend</p>')
				.queue( function() {
					$(this).closest('td').removeClass('bucketlister-error');
					$('.bucketlister-msg').remove();
				});
				return false;
		}
		bucketlistercategory.category = $('#bucketlister-add-category').val();
		bucketlistercategory.id = $('#bucketlister-categories').val();
		
		adminAjax(bucketlistercategory, 'addcategory')
		e.preventDefault();
	});
	
	
	var checkDateErrors = function(theItem, date, errorLocation) {
		var thisError = '';
		if ( date.day > '28' && date.month == '2') {
			if ( ( date.year == '2012' || date.year == '2016'
				|| date.year == '2020' || date.year == '2024' || date.year == '2028' || date.year == '2032' || date.year == '2036' || date.year == '2040' || date.year == '2044' || date.year == '2048' || date.year == '2052' || date.year == '2056' || date.year == '2060' || date.year == '2064' || date.year == '2068' || date.year == '2072') && date.day == '29' ) {
					
			} else {
				$(errorLocation).addClass('bucketlister-error');
				thisError = "<p>Check the " + theItem + " date - This day is not possible in February for the chosen year.</p>";	
			}
		} else if ( date.day == '31' && ( date.month == '4' || date.month  == '6' || date.month  == '9' || date.month  == '11' ) ) {
			$(errorLocation).addClass('bucketlister-error');
			thisError = "<p>Check the " + theItem + " date - This day is not possible for the current month.</p>";	
		}
		return thisError;
	}
	
});