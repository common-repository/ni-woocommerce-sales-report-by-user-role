<?php
/*
Plugin Name: Ni WooCommerce Sales Report By User Role
Description:Ni WooCommerce Sales Report by user role provide the option to link the sales order with your sales agent or salesperson.
Version: 2.1.2
Author: anzia
Author URI: http://naziinfotech.com/
Plugin URI: https://wordpress.org/plugins/ni-woocommerce-sales-report-by-user-role/
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/agpl-3.0.html
Requires at least: 4.7
Tested up to: 6.6.2
WC requires at least: 3.0.0
WC tested up to: 9.3.3
Last Updated Date:15-October-2024
Requires PHP: 7.0

*/
if ( ! defined( 'ABSPATH' ) ) { exit;}
if( !class_exists( 'Ni_WooCommerce_Sales_Report_By_User_Role' ) ) {
	class Ni_WooCommerce_Sales_Report_By_User_Role {
		var $ni_constant = array();  
		function __construct() {
				$this->ni_constant = array(
				 "prefix" 		  => "ni-",
				 "manage_options" => "manage_options",
				 "menu"   		  => "ni-dashboard-user-role",
				);
			register_activation_hook( __FILE__, array( $this,  'Add_Ni_Sales_Report_By_User_Role_Activation') );	
			include_once("include/ni-sales-report-by-user-role-init.php");
			$obj = new Ni_Sales_Report_By_User_Role_Init($this->ni_constant);
			
			add_action( 'before_woocommerce_init',  array(&$this,'before_woocommerce_init') );
			
		}
		
		function before_woocommerce_init(){
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}	 
		 }
		function Add_Ni_Sales_Report_By_User_Role_Activation(){
			$cap = array();
			
			remove_role( 'ni_sales_agent' );
			
			$result = add_role( 'ni_sales_agent', __('Ni Sales Agent' ),$cap); 
			$role = get_role( 'ni_sales_agent' );
			
			$role->add_cap("manage_woocommerce");
			$role->add_cap("edit_product");
			$role->add_cap("read_product");
			$role->add_cap("delete_product");
			$role->add_cap("edit_products");
			$role->add_cap("edit_others_products");
			$role->add_cap("publish_products");
			$role->add_cap("read_private_products");
			$role->add_cap("delete_products");
			$role->add_cap("delete_private_products");
			$role->add_cap("delete_published_products");
			$role->add_cap("delete_others_products");
			$role->add_cap("edit_private_products");
			$role->add_cap("edit_published_products");
			$role->add_cap("assign_product_terms");
			
			do_action('ni_user_agent_plugin_activated', $role);
		}
	}
$obj = new Ni_WooCommerce_Sales_Report_By_User_Role();
}