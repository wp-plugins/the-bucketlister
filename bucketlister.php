<?php
/*
Plugin Name: The Bucketlister
Plugin URI: http://line-in.co.uk/plugins/bucketlister
Description: A plugin to help you manage your Bucket List cause, you know, you're gonna die soon. If this thought is a bit too morbid, come see how much fun we're having at <a href='http://www.neverendingvoyage.com'>Never Ending Voyage</a>.
Version: 0.1.6
Author: Simon Fairbairn
Author URI: http://line-in.co.uk
*/

define('BUCKETLISTER_VERSION', "0.1.6");

register_activation_hook(__FILE__, array( 'LI_Bucketlister', 'install_plugin' ) );
register_deactivation_hook(__FILE__, array( 'LI_Bucketlister', 'uninstall_plugin') );

/**
 * 	instantiate the class
 *	@return	object	My_Test_Plugin object
 */
function LI_Bucketlister_call() {
    return LI_Bucketlister::init();
}
add_action( 'after_setup_theme', 'LI_Bucketlister_call' );

class LI_Table_Manager {
	static $prefix = false;
	private $name;
	private $fields;

	static function tableItemWithName( $name = false ) {
		$item = new self();

		if ( $name ) {
			$item->setName( $name );
		}
		return $item;
	}

	public function __construct() {
		if ( !self::$prefix ) {
			global $wpdb;
			self::$prefix = $wpdb->prefix;
		}
	}

	public function setName( $name ) {
		$this->name = self::$prefix . $name;
	}
	public function getName() {
		echo $this->name;
	}

}


class LI_Bucketlister {
	static $object = false;
	static $version = "0.1.6";
	static $bucketlisterTableName = "bucketlister";
	static $bucketlisterCategoryName = "nevcats";
	

	static function install_plugin() {


		global $wpdb;
		$bucketlisterTableName = $wpdb->prefix . 'bucketlister';
		$bucketlisterCategoryName = $wpdb->prefix . 'nevcats';

		// $sql = "ALTER TABLE " . $bucketlisterTableName . "  DROP `newitem`";
		// $wpdb->query($sql);
 
		if ( self::$version != get_option('bucketlister_version') ) {
			$sql = "CREATE TABLE " . $bucketlisterTableName . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				item varchar(255) NOT NULL,
				datecreated datetime,
				datecompleted datetime,
				datemodified datetime,
				cat_id mediumint(9)
			);";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);	
			update_option('bucketlister_version', self::$version);
		}
	}
	static function uninstall_plugin() {

	}
	static function deleteData() {
		global $wpdb;
		wp_die('deleting data');
	}

	public function init() {
		if ( !self::$object ) {
			self::$object = new self();
		}
		return self::$object;
	}


	private function __construct() {
		
	}

}





global $bucketlister_version;
global $nevcats_version;
$bucketlister_version = "0.1.6";
$nevcats_version = "0.1.0";

if ( is_admin() )
	require_once('bucketlister-admin.php');




if ( !function_exists('bucketlister_display') ) {
	function bucketlister_display($atts) {
		if ( !is_admin() ) {
			static $constant = 1;
			// Get shortcode attributes
			extract(shortcode_atts(array( 'order' => '', 'category' => '', 'id' => '', 'completed' => false), $atts));
			$where_clause = '';
			global $wpdb;
			if ( $order == '' ) 
				$order = 'item ASC';
			if ( strtolower( $order ) == 'item' )
				$order = 'item ASC';
			else if ( strtolower( $order ) == 'item desc' )
				$order = 'item DESC';
			else if ( strtolower($order) == 'target' ) 
				$order = 'datemodified ASC';
			else if ( strtolower($order) == 'target desc' ) 
				$order = 'datemodified DESC';
			else if ( strtolower($order) == 'category' ) 
				$order = 'category ASC';
			else if ( strtolower($order) == 'category desc' ) 
				$order = 'category DESC';
			else if ( strtolower($order) == 'completed' ) 
				$order = 'datecompleted ASC';
			else if ( strtolower($order) == 'completed desc' ) 
				$order = 'datecompleted DESC';
			else
				$order = 'item ASC';
		
			if ( $category != '' || $id != '' || $completed != '' ) {
				$where_clause = " WHERE ";
				$open = false;
				if ( $category != '' ) {
					$where_clause .= " category='$category'";
					$open = true;
				}
				if ( $id != '' ) {
					if ( $open )
						$where_clause .= " AND ";
					$where_clause .= $wpdb->prefix . "bucketlister.id='$id'";
					$open = true;	
				}
				if ( $completed == 'true' ) {
					if ( $open )
						$where_clause .= " AND ";
					$where_clause .= "datecompleted IS NOT NULL";
				}
			}

			
			$display = "<table id='bucketlister-table-$constant' class='bucketlister-table'>";
			$display .= "<thead>";
			$display .= "<tr class='thead'>";
			$display .= "<th class='bucketlister-item'>Item</th>";
			$display .= "<th class='bucketlister-category'>Category</th>";
			
			$display .= "<th class='bucketlister-target'>Target</th>";
			$display .= "<th class='bucketlister-days'>Days Left</th>";

			$display .= "<th class='bucketlister-completed'>Complete</th>";
			
			$display .= "</tr>";
			$display .= "</thead>";
			$display .= "<tbody>";
			$display .= bucketlister_prepare_body( $order, $where_clause );
			$display .= "</tbody>";
			$display .= "</table>";
			$constant++;
			return $display;
			
		}
	}
}
add_shortcode('bucketlister', 'bucketlister_display');


if ( !function_exists( 'bucketlister_prepare_body' ) ) {
	function bucketlister_prepare_body($order, $where_clause = '') {
		$even = false;
		$return = '';
		$items = bucketlister_get_items_db($order, $where_clause);
		
		foreach ( $items as $item ) {
			if ( $even ) {
				$return .= "<tr class='even'>";
				$even = false;
			} else { 
				$return .= "<tr>";
				$even = true;
			}
			$target_date = new DateTime($item->datemodified );
			$return .= "<td>" . stripslashes($item->item) . "</td>";
			$return .= "<td>" . stripslashes($item->category) . "</td>";
			$return .= "<td>" . $target_date->format('j F Y') . "</td>";
			$now = new DateTime('now');
			if ( $item->datecompleted != '' ) { 
				$completed_date = new DateTime( $item->datecompleted);
			
				$return .= "<td>0!</td>";
				$return .= "<td>" . $completed_date->format('j F Y') . "</td>";
			} else {
				
				$start_date = gregoriantojd( $now->format('m'), $now->format('d'), $now->format('Y') );
				$end_date = gregoriantojd( $target_date->format('m'), $target_date->format('d'), $target_date->format('Y') );
				$return .= "<td>" . round( $end_date - $start_date , 0 ). "</td>";
				
				$return .= "<td></td>";
				
			}
			$return .= "</tr>";
		}
		return $return;

	}
}



// Load up scripts
add_action('init', 'bucketlister_scripts');
if ( !function_exists( 'bucketlister_scripts' ) ) {
	function bucketlister_scripts() {
		global $octopus_framework;
		if ( !is_admin()  ) {
			if ( !is_object($octopus_framework ) ) {
				wp_enqueue_script('bucketlister-js', WP_PLUGIN_URL . '/the-bucketlister/js/bucketlister.js', array('jquery'), '0.1.1', true);
			}
		} else {
			wp_enqueue_script('bucketlister-js', WP_PLUGIN_URL . '/the-bucketlister/js/bucketlister-admin.js', array('jquery'), '0.1.1', true);
			wp_enqueue_style('bucketlister-css', WP_PLUGIN_URL . '/the-bucketlister/css/bucketlister.css');			
		}
		if ( !is_object($octopus_framework) ) {
			wp_enqueue_style('bucketlister-css', WP_PLUGIN_URL . '/the-bucketlister/css/bucketlister.css');
		}
	}
}

// AJAX functionality
add_action('wp_ajax_nopriv_bucketlister_ajax', 'bucketlister_do_ajax');
add_action('wp_ajax_bucketlister_ajax', 'bucketlister_do_ajax');
if (!function_exists('bucketlister_do_ajax') ) {
	function bucketlister_do_ajax() { 
		global $wpdb;
		$order = $_POST['order'];
		$column = $_POST['column'];
		if ( $order == 'sort-asc' )
			$order = 'ASC';
		else
			$order = 'DESC';
		
		if ( $column == 'Item' )
			$sort = "item $order";
		if ( $column == 'Category' )
			$sort = "category $order";
		if ( $column == 'Target' )
			$sort = "datemodified $order";
		if ( $column == 'Days Left' )
			$sort = "datemodified $order";
		if ( $column == 'Complete' )
			$sort = "datecompleted $order";
		echo bucketlister_prepare_body($sort);
		die;
	}
 }
 
add_action('wp_ajax_bucketlister_admin_ajax', 'bucketlister_do_admin_ajax');
if (!function_exists('bucketlister_do_admin_ajax') ) {
	function bucketlister_do_admin_ajax() { 
		global $wpdb;
		if ( $_POST['method'] == 'add' ) {
			$results = bucketlister_insert_item( $_POST['stuff'] );
		} else if ( $_POST['method'] == 'delete' ) {
			$results = bucketlister_delete_item($_POST['stuff']['id'] );
		} else if ( $_POST['method'] == 'update' ) {
			$results = bucketlister_insert_item( $_POST['stuff'], $_POST['stuff']['itemId'] );
		} else if ( $_POST['method'] == 'addcategory') {
			$results = bucketlister_add_category( $_POST['stuff'] );	
		} else if ( $_POST['method'] == 'deletecategory') {
			$results = bucketlister_delete_category( $_POST['stuff']['id'] );	
		}
		echo json_encode( $results );
		die;
	}
 }

 
 
add_action('wp_head', 'il_define_ajax');
if (!function_exists('il_define_ajax') ) {
	function il_define_ajax() { ?>
		<script type="text/javascript">
			var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
		</script>
<?php 
	}
}


// On activation
register_activation_hook( __FILE__, 'bucketlister_activate' );
if ( !function_exists('bucketlister_activate') ) {
	function bucketlister_activate() {
		global $wpdb;
		global $bucketlister_version;
		global $nevcats_version;
		$table_name = $wpdb->prefix . 'bucketlister';
		$category_name = $wpdb->prefix . 'nevcats';
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name && class_exists('DateTime') ) {
			$sql = "CREATE TABLE " . $table_name . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				item varchar(255) NOT NULL,
				datecreated datetime,
				datecompleted datetime,
				datemodified datetime,
				cat_id mediumint(9)
			);";
			add_option("bucketlister_version", $bucketlister_version);				
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);		
		}
		if ( $wpdb->get_var("SHOW TABLES LIKE '$category_name'") != $category_name && class_exists('DateTime') ) {
			$sql = "CREATE TABLE " . $category_name . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				plugin varchar(100),
				category varchar(255)
			);";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);	
			add_option("nevcats_version", $nevcats_version);				
			
		}
		
	}
}

if ( !function_exists('bucketlister_get_items_db') ) {
	function bucketlister_get_items_db($order = 'item ASC', $where_clause = '') {
		global $wpdb;
		
		$query = "SELECT " . $wpdb->prefix . "bucketlister.id,item,datemodified,datecompleted,category,cat_id FROM " . $wpdb->prefix . "bucketlister 
			LEFT JOIN " . $wpdb->prefix . "nevcats ON " .
			$wpdb->prefix . "bucketlister.cat_id = " .
			$wpdb->prefix . "nevcats.id" . 
			"$where_clause ORDER BY $order " ;		
				
		$results = $wpdb->get_results($query, OBJECT);
		return $results;
	}
}


?>