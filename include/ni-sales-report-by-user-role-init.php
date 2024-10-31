<?php
if ( ! defined( 'ABSPATH' ) ) { exit;}
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
if( !class_exists( 'Ni_Sales_Report_By_User_Role_Init' ) ) {
	include_once("ni-user-role-report-function.php");
	class Ni_Sales_Report_By_User_Role_Init extends Ni_User_Role_Report_Function{
		var $ni_constant = array();  
		function __construct($ni_constant = array()) {
			
			$this->ni_constant = $ni_constant; 
			//$this->get_menu();
			add_action( 'add_meta_boxes',	array($this,'add_sales_agent_metaboxes'), 10, 2 );
			add_action('admin_init', 		array($this,'admin_init'));
			add_action('admin_menu', 		array($this,'admin_menu'));	
			add_action( 'admin_enqueue_scripts',  array(&$this,'admin_enqueue_scripts' ));
			add_action( 'wp_ajax_user_role_report',  array(&$this,'ajax_user_role_report' )); /*used in form field name="action" value="my_action"*/
			
			add_filter( 'admin_footer_text',  array(&$this,'admin_footer_text' ),101);
			$this->add_setting_page();
			//print_r($ni_constant);	
			//echo $this->ni_constant["menu"];	
			
			add_filter( 'manage_edit-shop_order_columns',array(&$this,'add_sales_agent_column' ));
			add_action( 'manage_shop_order_posts_custom_column',array(&$this,'add_sales_agent_column_content' ), 20, 2 );

			add_action('woocommerce_after_order_object_save',array(&$this,'niwoo_save_sales_agent'));
			
		}
		function save_meta_boxes_anzar(){
			error_log("save_meta_boxes_anzar");
		}
		function admin_menu(){
			add_menu_page( 'User Role Report', 'User Role Report', $this->ni_constant['manage_options'], $this->ni_constant['menu'], array( $this, 'add_page'), 'dashicons-groups', "42.636" );
			add_submenu_page($this->ni_constant["menu"],"Dashboard","Dashboard", $this->ni_constant['manage_options'],'ni-dashboard-user-role', array( $this, 'add_page'));
			add_submenu_page($this->ni_constant["menu"],"Sales Product Report","Sales Product Report",  $this->ni_constant['manage_options'],'ni-sales-order-by-user-role', array( $this, 'add_page'));
			
			add_submenu_page($this->ni_constant["menu"],"Agent Report","Agent Report",  $this->ni_constant['manage_options'],'ni-sales-agent-report', array( $this, 'add_page'));
			
			do_action("ni_sales_agent_report_menu",$this->ni_constant);
			
	
		}
		function add_setting_page(){
			include("ni-user-role-setting.php");
			$obj = new Ni_User_Role_Setting($this->ni_constant);
		}
		function settings_page(){
			//echo "dsa";
		}
		function add_page(){
			if (isset($_REQUEST["page"])){
				$page = $_REQUEST["page"];
				if ($page == "ni-dashboard-user-role"){
					include_once("ni-dashboard-user-role.php");
					$obj = new Ni_Dashboard_User_Role();
					$obj->init();
					
				}
				if ($page == "ni-sales-order-by-user-role"){
					include_once("ni-sales-order-report-by-user-role.php");
					$obj =  new Ni_Sales_Order_Report_By_User_Role();
					$obj->init();
					
				}
				if ($page =="ni-sales-agent-report"){
					include_once("ni-sales-agent-report.php");
					$obj =  new Ni_Sales_Agent_Report();
					$obj->init();
				}
			}	
		}
		function add_sales_agent_metaboxes() {
			$screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
        ? wc_get_page_screen_id( 'shop-order' )
        : 'shop_order';
			//add_meta_box('ni_sales_agent_metaboxes', 'Select Sales Agent',  array( $this,  'ni_display_meta_box'), 'shop_order', 'side', 'default');

			add_meta_box(
				'ni_sales_agent_metaboxes',
				'Select Sales Agent',
				array( $this,  'ni_display_meta_box'),
				$screen,
				'side',
				'high'
			);

		}
		function admin_init(){
			if (isset($_REQUEST["post_type"])){
				if ($_REQUEST["post_type"] == "shop_order"){
					if(isset($_REQUEST["post_ID"])){
						$post_id =  $_REQUEST["post_ID"];
						$user_id =  isset($_REQUEST["ic_user_role"])?$_REQUEST["ic_user_role"]:'-1';
						update_post_meta($post_id, '_ic_sales_agent_user_id', $user_id);
					}
				}
			
			}
			
		}
		function niwoo_save_sales_agent( $order){
			
			if ($order->get_type()=="shop_order"){
				if(isset($_REQUEST["ic_user_role"])){
					$post_id  = $order->get_id();
					$user_id = $_REQUEST["ic_user_role"];
					update_post_meta($post_id, '_ic_sales_agent_user_id', $user_id);
				}	

			}
		}
		function ni_display_meta_box(){
			

			$post_id  = isset($_REQUEST["id"])?$_REQUEST["id"]:0;

			global $wpdb;
			global $post;
			$sales_person_option = get_option('ic-sales-agent-option');
			
			// print "<pre>";
			// print_r($sales_person_option["ic-sales-person-option"]["user_role"]);
			// print "</pre>";
		
			$role = isset( $sales_person_option["ic-sales-agent-option"]["user_role"])? $sales_person_option["ic-sales-agent-option"]["user_role"]:'administrator';
			$query=  "";
			//$role = "ic_salesperson";
			
			$query = " SELECT ";
			$query .= " users.ID as user_id  ";
			$query .= " ,users.user_email as user_email  ";
			$query .= " ,first_name.meta_value as first_name  ";
			$query .= " ,last_name.meta_value as last_name  ";
			
			$query .= " FROM	{$wpdb->prefix}users as users  ";
			
			
			$query .= " LEFT JOIN {$wpdb->prefix}usermeta  role ON role.user_id=users.ID ";
			$query .= " LEFT JOIN {$wpdb->prefix}usermeta  first_name ON first_name.user_id=users.ID ";
			$query .= " LEFT JOIN {$wpdb->prefix}usermeta  last_name ON last_name.user_id=users.ID ";
			
			$query .= " WHERE 1 = 1 ";
			$query .= " AND   role.meta_key='{$wpdb->prefix}capabilities'";
			$query .= " AND  role.meta_value   LIKE '%\"{$role}\"%' ";
			
			$query .= " AND   first_name.meta_key='first_name'";
			$query .= " AND   last_name.meta_key='last_name'";
				
			
			
		
			
			$data = $wpdb->get_results($query);
			$user_id = get_post_meta($post_id, '_ic_sales_agent_user_id', true);
			?>
			<select  name="ic_user_role" id="ic_user_role">
		   <option selected="selected" value="-1">Select Sales Agent</option>
			<?php 
			foreach($data as $k=>$v){
				if ($user_id == $v->user_id){
					?>
					<option selected="selected" value="<?php echo $v->user_id ; ?>"> <?php echo  $v->first_name ." ". $v->last_name  ; ?></option>
					<?php	
				}else{
					?>
					<option value="<?php echo $v->user_id ; ?>"> <?php echo  $v->first_name ." ". $v->last_name  ; ?></option>
					<?php	
				}
			}
			?>
			</select>
			<?php
			/*
			global $wp_roles;
			$roles = $wp_roles->get_names();
			print "<pre>";
			print_r($roles );
			print "</pre>";
			//echo "dsadsa";
			foreach($roles as $k=>$v) {
				echo $v;
				echo $k;
				echo "<br>";		
			}
			*/
		}
		function get_menu(){
			$page = isset($_REQUEST["page"]) ? $_REQUEST["page"] : '';
			$menu = array();
			//$menu = 	array('ni-sales-order-by-user-role','ni-dashboard-user-role','ni-user-role-setting','ni-sales-agent-report');
			$menu[] ='ni-sales-order-by-user-role';
			$menu[] ='ni-dashboard-user-role';
			$menu[] ='ni-user-role-setting';
			$menu[] ='ni-sales-agent-report';
			
			$menu = apply_filters('ni_sales_agent_report_admin_enqueue_script_pages',$menu, $page);
			return $menu;
		}
		function admin_enqueue_scripts(){
			if (isset($_REQUEST["page"])){
				$page = $_REQUEST["page"];
				$menu = $this->get_menu();
				//print_r($menu);
				if (in_array($page, $menu)) {
					wp_enqueue_script( 'ni-ajax-script-user-role-report', plugins_url( '../assets/js/script.js', __FILE__ ), array('jquery') );
					wp_enqueue_script( 'ni-sales-order-by-user-role-script', plugins_url( '../assets/js/ni-sales-order-report-by-user-role.js', __FILE__ ) );
					wp_localize_script( 'ni-ajax-script-user-role-report','user_role_report_ajax_object',
						array('ni_sales_report_user_role_ajaxurl'=>admin_url('admin-ajax.php') ) );
					if($page  == "ni-dashboard-user-role"){	
						wp_register_style( 'ni-sales-report-summary-css', plugins_url( '../assets/css/ni-sales-report-summary.css', __FILE__ ));
						wp_enqueue_style( 'ni-sales-report-summary-css' );		
						
						wp_register_style( 'ni-font-awesome-css', plugins_url( '../assets/css/font-awesome.css', __FILE__ ));
						wp_enqueue_style( 'ni-font-awesome-css' );
					}
					//if(($page  == "ni-sales-order-by-user-role") || ($page  == "ni-sales-agent-report")){
						wp_register_style( 'ni-sales-order-report-by-user-role-css', plugins_url( '../assets/css/ni-sales-order-report-by-user-role.css', __FILE__ ));
						wp_enqueue_style( 'ni-sales-order-report-by-user-role-css' );
					//}
					
					do_action('ni_sales_agent_report_admin_enqueue_scripts',$page);	
				}
			}
		}
		function ajax_user_role_report(){
			if (isset($_REQUEST['sub_action'])){
				$sub_action = $_REQUEST['sub_action'];
				do_action("add_ni_ajax_user_role_report",$sub_action);
				if ($sub_action =="ni_user_role_sales_order"){
					//echo json_encode($_REQUEST);
					include_once("ni-sales-order-report-by-user-role.php");
					$obj = new Ni_Sales_Order_Report_By_User_Role();
					$obj->get_sales_order_list();
				
				}
				if ($sub_action =="ni_sales_agent_report"){
					//echo json_encode($_REQUEST);
					include_once("ni-sales-agent-report.php");
					$obj = new Ni_Sales_Agent_Report();
					$obj->get_sales_order_list();
				}
				die;
			}
			
		}
		function add_sales_agent_column($columns){
			$columns['sale_agent_column'] = 'Sales Agent';
    		return $columns;
		}
		function add_sales_agent_column_content( $column, $post_id  ){
			if ($column  == 'sale_agent_column'){
				$user_id = get_post_meta( $post_id, '_ic_sales_agent_user_id', true );
				if ($user_id > 0){
					$row = $this->get_user_list($user_id);
					foreach($row as $k=>$v){
						echo  $v->first_name ." ". $v->last_name ;
					}
				}
				
			}
		}
		function admin_footer_text($text){
		
			 if (isset($_REQUEST["page"])){
				 $page = $_REQUEST["page"]; 
					if ($page == "ni-dashboard-user-role" || $page  =="ni-sales-order-by-user-role" || $page =="ni-user-role-setting"){
					$text = sprintf( __( 'Thank you for using our plugins <a href="%s" target="_blank">naziinfotech</a>    Email at: <a href="%s" target="_top">support@naziinfotech.com</a>' ), 
					__( 'http://naziinfotech.com/' ) , __( 'mailto:support@naziinfotech.com' ));
					$text = "<span id=\"footer-thankyou\">". $text ."</span>"	 ;
				}
			 }
			return $text ; 
		}
	}
}
//$obj = new  ic_sales_report_by_salesperson();
?>