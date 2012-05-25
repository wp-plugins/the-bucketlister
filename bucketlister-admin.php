<?php
add_action('admin_menu', 'bucketlister_add_menu');
if ( !function_exists( 'bucketlister_add_menu' ) ) {
	function bucketlister_add_menu() {
		//create new top-level menu
		add_options_page( 'Bucketlister Options', 'Bucketlister Options', 'manage_options', 'bucketlister-options', 'bucketlister_options');
	}
}

if ( !function_exists( 'bucketlister_options' ) ) {
	function bucketlister_options() {
		if ( !current_user_can('manage_options') ) {
			wp_die('You do not have sufficient privileges to edit this page');	
		}
		$edit = false;
		$target['day'] = '';
		$target['month'] = '';
		$target['year'] = '';
		$completed['day'] = '';
		$completed['month'] = '';
		$completed['year'] = '';
		$item = '';
		
		$message = '';
		if ( isset($_POST['bucketlister-form-submission'] ) ) {
			$result['error'] = false;
			check_admin_referer('bucketlister-form-check');
			if ( isset($_POST['bucketlister-delete']) ) {
				$id = key( $_POST['bucketlister-delete'] );
				
				$result = bucketlister_delete_item($id);
			}	
			if ( isset( $_POST['bucketlister-edit'] ) ) {
				$edit = key($_POST['bucketlister-edit']);
			}
			
			if ( isset($_POST['inline-form-submit-4'])) {
				$items['category'] = $_POST['bucketlister-add-category'];		
				$result = bucketlister_add_category($items);
			}
			if ( isset($_POST['bucketlister-delete-category']) ) {
				$result = bucketlister_delete_category($_POST['bucketlister-categories']);	
			}
			if ( isset( $_POST['bucketlister-update'] ) ) {
				$submission = array(
					'name' => $_POST['bucketlister-new-item-up'],
					'target' => array(
						'day' => (int) $_POST['bucketlister-target-day-up'],
						'month' => (int) $_POST['bucketlister-target-month-up'],
						'year' => (int) $_POST['bucketlister-target-year-up']
					),
					'completed' => array(
						'day' => (int) $_POST['bucketlister-completed-day-up'],
						'month' => (int) $_POST['bucketlister-completed-month-up'],
						'year' => (int) $_POST['bucketlister-completed-year-up']
					),
					'category' => $_POST['bucketlister-category-up']
				);
				$result = bucketlister_insert_item( $submission, key( $_POST['bucketlister-update'] ) );
			}
			
			if ( isset( $_POST['bucketlister-go'] ) ) {
				$submission = array(
					'name' => $_POST['bucketlister-new-item'],
					'target' => array(
						'day' => (int) $_POST['bucketlister-target-day'],
						'month' => (int) $_POST['bucketlister-target-month'],
						'year' => (int) $_POST['bucketlister-target-year']
					),
					'completed' => array(
						'day' => (int) $_POST['bucketlister-completed-day'],
						'month' => (int) $_POST['bucketlister-completed-month'],
						'year' => (int) $_POST['bucketlister-completed-year']
					),
					'category' => $_POST['bucketlister-category']
					
				);
				
				$result = bucketlister_insert_item($submission);	
			}
			
			if ( $result['error'] ) {
				if ( is_int($result['error']) ) { 
					$affix = '-up';
					$edit = $result['error'];
				} else {
					$affix = '';
				}
				$message = "<div class='error below-h2'><p>{$result['message']}</p></div>";
				$target['day'] = $_POST['bucketlister-target-day' . $affix];
				$target['month'] = $_POST['bucketlister-target-month'. $affix];
				$target['year'] = $_POST['bucketlister-target-year'. $affix];
				$completed['day'] = $_POST['bucketlister-completed-day'. $affix];
				$completed['month'] = $_POST['bucketlister-completed-month'. $affix];
				$completed['year'] = $_POST['bucketlister-completed-year'. $affix];
			} elseif ( !$edit && !isset($_POST['bucketlister-cancel'] )) {
				$message = "<div class='updated below-h2'><p>{$result['message']}</p></div>";	
			}

		}
				
		echo '<div class="wrap" id="bucketlister-container">';
		echo '<div id="bucketlister-nev-logo"><a href="http://www.neverendingvoyage.com" title="Come visit us! You won\'t regret it! (You probably won\'t regret it - results not guaranteed)"><span>Never Ending Voyage</span></a></div> <p class="padme">Presents</p>';
		echo '<h2>The Bucketlister</h2>';
		echo $message;
		
		if ( !class_exists('DateTime') ) {
?>
			<div class='error'>
				<h3>Awww, pants. A Totally Fatal Error.</h3>

				<p>Unfortunately, the DateTime class needed to run this plugin isn't available on your server. You should email your hosting provider and tell them to get their ass into the 21st century, post haste.</p>

				<p>Until they get all futuristic, though, you won't be able to use this incredibly awesome plugin. Sorry about that.</p>
				<p><strong>Geeky Information</strong></p>
				<p>This plugin assumes that you're going to live until at least 2080, but the current UNIX timestamps only go up to 2038 (it's to do with 32bit integers and the number of seconds since the 1st of January 1970). In order to get round this, I decided to get all modern and use the new PHP DateTime class which has support for dates billions of years in the future (let's hope we all live until THEN!).</p>
				<p>However, in order to use this class, your version of PHP needs to have it installed. If it doesn't, this plugin ceases to function and, in your case my friend, it does not. Sucks, I know.</p>
				<h3>Plugin Deactivated. <small>(sorry)</small></h3>

			</div>
<?php
			deactivate_plugins( dirname(__FILE__) . '/bucketlister.php' );
			return false;
		} 
		echo "<form action='" . $_SERVER['PHP_SELF'] . "?page=bucketlister-options' id='bucketlister-form' method='post'>"; 
		if ( function_exists('wp_nonce_field') )
			wp_nonce_field('bucketlister-form-check');		
		echo "<h3>The Bucket List</h3>";
		echo "<table class='widefat fixed bucketlister-table'>";
		echo "<thead>";
		echo "<tr class='thead'>";
		echo "<th class='column-posts'>ID</th>";
		echo "<th class='manage-column column-title'>Item</th>";
		echo "<th class='manage-column column-title'>Category</th>";
		
		echo "<th class='manage-column column-title'>Target Date</th>";
		echo "<th class='manage-column column-title'>Date Completed</th>";
		echo "<th class='column-posts'>Edit</th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		echo bucketlister_get_items('id ASC', $edit);
		echo "</tbody>";
		echo "<tfoot>";
		echo "<tr>";
		if ( !$edit ) {
		
			echo "<td>NEW:</td><td class='bucketlister-new-item-container'><input type='text' id='bucketlister-new-item' name='bucketlister-new-item' value=''></td><td>" .  bucketlister_categories('bucketlister-category')  . "</td><td class='bucketlister-target-date-container'>" . bucketlister_dates('bucketlister-target-day', 'day', $target['day']) . bucketlister_dates('bucketlister-target-month', 'month', $target['month']) . bucketlister_dates('bucketlister-target-year','year', $target['year']) . "</td><td class='bucketlister-completed-date-container'>" . bucketlister_dates('bucketlister-completed-day', 'day', $completed['day']) . bucketlister_dates('bucketlister-completed-month', 'month', $completed['month']) . bucketlister_dates('bucketlister-completed-year','year', $completed['year']) . "</td><td><input type='submit' class='button-secondary' id='bucketlister-go' name='bucketlister-go' value='Add'></td>";
		}
		echo "</tr>";
		echo "</tfoot>";
		
		echo "</table>";
		echo "<fieldset>";
		echo "<legend>Bucket List Categories</legend>";
		echo "<table class='form-table'>";
		echo "<tbody>";
		echo "<tr><th scope='row'></th><td>" . bucketlister_categories('bucketlister-categories') . "<input type='submit' class='button-secondary' id='bucketlister-delete-category' name='bucketlister-delete-category' value='Delete this category'></td></tr>";
		echo "<tr><th scope='row'>Add Category</th><td><input type='text' id='bucketlister-add-category' name='bucketlister-add-category' value='' /><input type='submit' class='button-primary' id='inline-form-submit-4' name='inline-form-submit-4' value='Add This Category' /><input type='hidden' id='bucketlister-form-submission' name='bucketlister-form-submission' value='Form submitted' /></td></tr>";
		echo "</tbody>";
		echo "</table>";
		
		echo "</fieldset>";		
		echo "</form>";
		echo "<h3 class='bucketlister-instructions-title'>Instructions</h3>";
		echo "<div id='bucketlister-instructions'>";
		echo "<p>So you've created an awesome list full of all the cool stuff you're going to do. Now what? Share it with the world, of course! Here's how:</p>";
		echo "<p><strong>Add the list to a post or page with the following shortcode:</strong></p>";
		echo "<p><code>[bucketlister]</code></p>";
		echo "<p><strong>Want more? OK, let's get awesome. Only want to see a specific category? No problem:</strong></p>";
		echo "<p><code>[bucketlister category='your category name']</code></p>";		
		echo "<p><strong>Want to set the default order (users can reorder by heading if they have JavaScript switched on)</strong></p>";
		echo "<p><code>[bucketlister order='item']</code> - order by item ascending</p>";		
		echo "<p><code>[bucketlister order='item desc']</code> - order by item descending</p>";				
		echo "<p><code>[bucketlister order='target']</code> - order by your target date ascending</p>";		
		echo "<p><code>[bucketlister order='target desc']</code> - order by item descending</p>";				
		echo "<p><code>[bucketlister order='category']</code> - order by category ascending</p>";		
		echo "<p><code>[bucketlister order='category desc']</code> - order by category descending</p>";				
		echo "<p><code>[bucketlister order='completed']</code> - order by date completed ascending</p>";		
		echo "<p><code>[bucketlister order='completed desc']</code> - order by date completed descending</p>";		
		echo "<p><strong>Finally, show a single item:</strong></p>";
		echo "<p><code>[bucketlister id='item-id']</code> - item-id should be the number of the id (listed above)</p>";		
		echo "</div>";
				
		echo "<p class='description'>Thanks for checking out our Bucketlister plugin - come see out our adventures at <a href='http://www.neverendingvoyage.com/'>Never Ending Voyage</a>. If you're interested in getting a website designed and built or want a custom plugin just as awesome as this one (but CUSTOM!), then head over to <a href='http://line-in.co.uk'>Line In Web Design</a> and let me work my magic!</p>";
		echo '</div>';
		
	}
}

if ( !function_exists('bucketlister_delete_category') ) {
	function bucketlister_delete_category($id) {
		global $wpdb;
		$return['error'] = false;
		$return['message'] = "Category deleted";			
		
		if ($id != 'none' ) {
			$wpdb->query( "DELETE FROM " . $wpdb->prefix . "nevcats WHERE id='$id'" );
		} else {
			$return['error'] = true;
			$return['message'] = "No category selected";			
		}
		
		return $return;
		
	}
}

if ( !function_exists('bucketlister_delete_item') ) {
	function bucketlister_delete_item($id) {
		global $wpdb;
		$return['error'] = false;
		$return['message'] = "Item deleted";			
		$wpdb->query( "DELETE FROM " . $wpdb->prefix . "bucketlister WHERE id='$id'" );
		
		return $return;
	}
}

if ( !function_exists('bucketlister_get_items') ) {
	function bucketlister_get_items($order = 'id ASC', $selected = false) {
		$items = '';
		$results = bucketlister_get_items_db($order);	
		foreach ( $results as $result ) {
			if ( $selected == $result->id ) {
				$completed_day = '';
				$completed_month = '';
				$completed_year = '';		
				$completed_date = new DateTime($result->datecompleted);
				if ( $result->datecompleted != '' ) {
					$completed_day = $completed_date->format('j');	
					$completed_month = $completed_date->format('n');	
					$completed_year = $completed_date->format('Y');	
				}
				$target_date = new DateTime( $result->datemodified);
				$target_day = $target_date->format('j');	
				$target_month = $target_date->format('n');	
				$target_year = $target_date->format('Y');	
				
				$items .= "<tr id='item-{$result->id}'><td>{$result->id}</td>";
				$items .= "<td><input type='text' id='bucketlister-new-item-up' name='bucketlister-new-item-up' value='" . esc_attr__($result->item) . "'></td>";
				$items .= "<td>" . bucketlister_categories('bucketlister-category-up') . "</td>";
				$items .= "<td>" .  bucketlister_dates('bucketlister-target-day-up', 'day', $target_day) . bucketlister_dates('bucketlister-target-month-up', 'month', $target_month) . bucketlister_dates('bucketlister-target-year-up','year', $target_year) . "</td>";

				$items .= "<td>" .  bucketlister_dates('bucketlister-completed-day-up', 'day', $completed_day) . bucketlister_dates('bucketlister-completed-month-up', 'month', $completed_month) . bucketlister_dates('bucketlister-completed-year-up','year', $completed_year) . "</td>";
				$items .= "<td><input type='submit' class='button-secondary' id='update-{$result->id}' name='bucketlister-update[{$result->id}]' value='Update' /><input type='submit' class='button-secondary' id='bucketlister-cancel' name='bucketlister-cancel' value='Cancel' /></td></tr>";
			} else {
				$completed = '';
				$target_date = new DateTime($result->datemodified);
				$completed_date = new DateTime($result->datecompleted );
				if ( $result->datecompleted != '' ) 
					$completed = $completed_date->format('j F Y');
				
				
				$items .= "<tr id='item-{$result->id}'><td class='bucketlister-id'>{$result->id}</td><td class='bucketlister-item'>" . stripslashes($result->item) . "</td><td class='bucketlister-category'>" . stripslashes($result->category) . "</td><td class='bucketlister-target'>" . $target_date->format('j F Y') . "</td><td class='bucketlister-completed'>$completed</td>";
				$items .= "<td class='bucketlister-buttons'><input type='submit' id='edit-{$result->id}' name='bucketlister-edit[{$result->id}]' value='Edit' class='button-secondary bucketlister-edit' /><input type='submit' id='bucketlister-delete-{$result->id}' name='bucketlister-delete[{$result->id}]' value='Delete' class='button-secondary bucketlister-delete' /></td>";
				
				$items .= "</tr>";
			}
		}
		return $items;		
	}
}


if ( !function_exists('bucketlister_insert_item') ) {
	function bucketlister_insert_item($items, $update = false) {
		global $wpdb;

		$return['error'] = false;
		$return['message'] = "Item added";			
		$added = time();
		if ( $items['completed']['day'] != 'none' ) {
			if ( !checkdate( $items['completed']['month'], $items['completed']['day'], $items['completed']['year'] ) ) {
				$return['error'] = true;
				$return['message'] = "Completed date invalid";			
			} else {
				$completed_date = new DateTime(	$items['completed']['year'] . '-' . $items['completed']['month'] . '-' 	. $items['completed']['day'] );	
				$sql_array['datecompleted'] = $completed_date->format('Y-m-d H:i:s');
				$sql_type[] = '%s';
				
			}
		}
		
		if ( !checkdate( $items['target']['month'], $items['target']['day'], $items['target']['year'] ) ) {
			$return['error'] = true;
			$return['message'] = "Target date invalid";			
		}
		if ( $items['name'] == '' )  {
			$return['error'] = true;
			$return['message'] = "You need to specify an item!";			
		}
		if ( $items['category'] != '' ) {
			$sql_array['cat_id'] = $items['category'];
			$sql_type[] = '%d';
		}
		$target_date = new DateTime( $items['target']['year'] . '-' . $items['target']['month'] . '-' 	. $items['target']['day'] );	
		$sql_array['datemodified'] = $target_date->format('Y-m-d H:i:s');
		$sql_type[] = '%s';
		$sql_array['item'] = $items['name'];
		$sql_type[] = '%s';
		$now = new DateTime('now');
		$sql_array['datecreated'] = $now->format('Y-m-d H:i:s');
		$sql_type[] = '%s';
		
		if ( $return['error'] ) {
			if ( $update ) {
				$return['error'] = $update;	
			}
		} else {
		
			if ( !$update ) {
				$wpdb->insert( $wpdb->prefix . 'bucketlister', $sql_array, $sql_type );
				$return['id'] = $wpdb->insert_id;
				$return['targetDate'] = $target_date->format('j F Y');
				if ( $items['completed']['day'] != 'none' ) {
					$return['dateCompleted'] = $completed_date->format('j F Y');
				} else {
					$return['dateCompleted'] = '';	
				}
				
			} else {
				$wpdb->update( $wpdb->prefix . 'bucketlister', $sql_array, array( 'id' => $update ), $sql_type, array( '%d' ) );
				$return['message'] = "Item updated";
			}
		}
			
		return $return;
		
	}
}



if ( !function_exists('bucketlister_add_category') ) {
	function bucketlister_add_category($items) {
		global $wpdb;
		$return['error'] = false;
		if ( $items['category'] == '' ) {
			$return['error'] = true;
			$return['message'] = "You need to enter a category!";
		} else {
			$wpdb->insert( $wpdb->prefix . 'nevcats', array( 'category' => $items['category'], 'plugin' => 'bucketlister' ), array( '%s') );
			$return['message'] = 'New category added';
			$return['id'] = $wpdb->insert_id;
		}
		return $return;
		
	}
}


if ( !function_exists('bucketlister_categories') ) {
	function bucketlister_categories($id) {
		global $wpdb;
		$db_cats = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'nevcats WHERE plugin="bucketlister"', OBJECT);		
		
		foreach ( $db_cats as $cat ) {
			$categories[ $cat->id ] = stripslashes($cat->category);	
		}
		$category_dropdown = "<select id='$id' name='$id'>";
		$category_dropdown .= "<option value='none'>-- Select A Category --</option>";
		foreach ( $db_cats as $cat ) {
			$category_dropdown .= "<option value='{$cat->id}'>" . stripslashes( $cat->category ) . "</option>";	
		}
		$category_dropdown .= "</select>";
		return $category_dropdown;
	}
}

if ( !function_exists('bucketlister_dates') ) {
	function bucketlister_dates($id, $type, $selected = 0) {
		$dropdown = '';
		$default = '';		
		if ( $type == 'day' ) {
			$options = array();
			for ( $i = 1; $i <= 31; $i++ ) {
				$options[$i] = $i;	
			}
			$default = '-Day-';
			// return $dropdown->get_select($id, '-Day-', $options, $selected );
		} else if ( $type == 'month' ) {
			$options = array(
				'1' => 'January',
				'2' => 'February',
				'3' => 'March',
				'4' => 'April',
				'5' => 'May',
				'6' => 'June',
				'7' => 'July',
				'8' => 'August',
				'9' => 'September',
				'10' => 'October',
				'11' => 'November',
				'12' => 'December'
			);
			$default = '-Month-';
			// return $dropdown->get_select($id, '-Month-', $options , $selected );
		} else if ( $type == 'year' ) {
			$options = array();
			$this_year = 1960;
			for ( $i = 0; $i < 160; $i++ ) {
				$options[$this_year + $i] = $this_year + $i;
			}
			$default = '-Year-';
			// return $dropdown->get_select($id, '-Year-', $options , $selected );
		}
		$dropdown = "<select id='$id' name='$id'>";
		$dropdown .= "<option value='none'>$default</option>";
		foreach ( $options as $value => $text ) {
			$dropdown .= "<option value='$value'";
			if ( $value == $selected ) {
				$dropdown .= " selected='selected'";
			}
			$dropdown .= ">$text</option>";	
		}
		
		$dropdown .= "</select>";
		return $dropdown;
	}
	
}


?>