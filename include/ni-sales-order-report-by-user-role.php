<?php
if ( ! defined( 'ABSPATH' ) ) { exit;}
if( !class_exists( 'Ni_Sales_Order_Report_By_User_Role' ) ) {
	include_once("ni-user-role-report-function.php");
	class Ni_Sales_Order_Report_By_User_Role extends Ni_User_Role_Report_Function{
		var $is_hpos_enable = false;
		function __construct() {
			$this->is_hpos_enable = $this->is_hpos_enabled();
			

		}
		function init(){
			//$type ="text";
			$type ="hidden";
			?>
			<div class="ni-container">
				<div class="ni-content">
					<div class="ni-report-title">Product Wise Sales Order List </div>
					<div style="border-bottom:1px solid #880E4F;"></div>
					<div class="ni-form-table">
						<form name="frm_user_role_report" id="frm_user_role_report" class="frm-user-role-report">
							<?php $row = $this->get_user_list(); ?>
							<table class="table-search-form" cellpadding="0" cellspacing="0">
								<tr>
									<td class="ni-label">Order Period:</td>
									<td class="ni-value"><select name="select_order" id="select_order" style="width:200px">
							  <option value="today">Today</option>
							  <option value="yesterday">Yesterday</option>
							  <option value="last_7_days">Last 7 days</option>
							  <option value="last_10_days">Last 10 days</option>
                              <option value="last_15_days">Last 15 days</option>
							  <option value="last_30_days">Last 30 days</option>
							  <option value="this_year">This year</option>
                               <option value="last_year">Last year</option>
							</select></td>
									<td class="ni-label">Select Agent:</td>
									<td class="ni-value"><select  name="ic_user_role" id="ic_user_role" style="width:200px">
							   <option selected="selected" value="-1">Select Sales Agent</option>
								<?php 
								foreach($row as $k=>$v){
								?>
								<option value="<?php echo $v->user_id ; ?>"> <?php echo  $v->first_name ." ". $v->last_name  ; ?></option>
								<?php	
								}
								?>
							</select></td>
									<td><input type="submit" value="Search" id="btnSearch" name="btnSearch"  class="ni-btn-search"/></td>
								</tr>
                                
                               	<?php do_action("ni_after_form_field_agent_sales_order_report"); ?>
                                
							</table>
							<input type="<?php echo $type; ?>" name="sub_action" value="ni_user_role_sales_order" />
							<input type="<?php echo $type; ?>" name="action" value="user_role_report" />
						</form>
					</div>
					<div class="_ajax_user_role_report_content"></div>
				</div>
				
			</div>
			
			<?php
		}
		
		function get_sales_order_list(){
			//echo json_encode(rand() . "<br>");
			$this->ni_display_table();
		}
		function ni_display_table(){
			$data = $this->get_sales_order_data();
			$columns = $this->get_sales_product_report_columns();
			//print_r($data);
			//echo json_encode($data );
			/*
			print "<pre>";
			print_r($data);
			print "</pre>";
			*/
			if (count($data)>0){
			$user_id =  isset($_REQUEST["ic_user_role"])?$_REQUEST["ic_user_role"]:'-1';	 
			$select_order = isset($_REQUEST["select_order"])?$_REQUEST["select_order"]:'today';	
			?>
            <div class="action-agent-sales-order-report">
            	<?php do_action("ni_before_table_agent_sales_order_report"); ?>
            </div>
			<div style="overflow-x:auto;">
				
				
				<table class="ni-sale-role-data-table" cellspacing="0" cellpadding="0">
					<tr>
						<th>ID</th>
						<th>Order Date</th>
                        <th>First Name</th>
						<th>Last Name</th>
						<th>Email</th>
						<th>Phone</th>
						<th>Country</th>
						<th>Payment Method</th>
						<th>Status</th>
                        <th>Product</th>
						<th>Qty.</th>
						<th>Line Tax</th>
						<th>Line Total</th>
						<th>
						<?php
						echo $role  = $this->get_user_role("Y");
						?>
						</th>
					</tr>
					<?php foreach($data as $k=>$v) {?>
                    <?php $admin_url = admin_url("post.php")."?action=edit&post=". $v->order_id; ?>
					<tr>
						<td><a href="<?php echo $admin_url; ?>" target="_blank"> <?php echo $v->order_id;?></a> </td>
						<td><?php echo $v->order_date; ?></td>
                        <td><?php echo $v->billing_first_name; ?></td>
						<td><?php echo $v->billing_last_name; ?></td>
						<td> <a href="mailto:<?php echo $v->billing_email; ?>"> <?php echo $v->billing_email; ?></a></td>
						<td><?php echo $v->billing_phone; ?></td>
						<td> <?php echo $this->get_country_name($v->billing_country);?> </td>
						<td><?php echo $v->payment_method_title; ?></td>
						<td> <?php echo ucfirst ( str_replace("wc-","", $v->order_status));?> </td>
                        <td><?php echo $v->order_item_name; ?></td>
						<td><?php echo $v->qty; ?></td>
						<td><?php echo wc_price(isset($v->line_tax)?$v->line_tax:0); ?></td>
						<td><?php echo wc_price(isset($v->line_total)?$v->line_total:0); ?></td>
						<td>
						<?php
							$user_id = 	  isset($v->ic_sales_agent_user_id)?$v->ic_sales_agent_user_id :'0';
							$row = $this->get_user_list($user_id);
							foreach($row as $k=>$v){
								echo  $v->first_name ." ". $v->last_name ;
							}
						?>
						</td>
					</tr>
					<?php }?>
				</table>
			</div>
			<?php	
			}else{
			?>
				<div class="ni-no-record-found">No record found</div>
			<?php	
			}
		}
		function ni_query_data($type="DEFAULT"){
			//echo json_encode($_REQUEST);
			$user_id = $_REQUEST["ic_user_role"];
			$select_order = isset($_REQUEST["select_order"])?$_REQUEST["select_order"]:'today';
			$today = date_i18n("Y-m-d");
			
			global $wpdb;	
			$query = " SELECT ";
			$query .= "	posts.ID as order_id ";
			if ($this->is_hpos_enable){
				$query .= "	,posts.status as order_status ";
			}else{
				$query .= "	,posts.post_status as order_status ";
			}

			if ($this->is_hpos_enable){
				$query .= "	,order_billing_addresses.email as billing_email ";
				$query .= "	,order_billing_addresses.first_name as billing_first_name ";
				$query .= "	,order_billing_addresses.last_name as billing_last_name ";
				$query .= "	,order_billing_addresses.phone as billing_phone ";
				$query .= "	,order_billing_addresses.country as billing_country ";
			}

			if ($this->is_hpos_enable){
				$query .= "	,posts.payment_method_title as payment_method_title ";
			}
			
			$query .= "	,woocommerce_order_items.order_item_id as order_item_id";
			if ($this->is_hpos_enable){
				$query .= "	, date_format( posts.date_created_gmt, '%Y-%m-%d') as order_date ";
			}else{
				$query .= "	, date_format( posts.post_date, '%Y-%m-%d') as order_date ";
			}
		
			$query .= "	,woocommerce_order_items.order_item_name";
			if ($this->is_hpos_enable){
				$query .= "	FROM {$wpdb->prefix}wc_orders as posts	";
			}else{
				$query .= "	FROM {$wpdb->prefix}posts as posts	";
			}
			
							
			$query .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items ON woocommerce_order_items.order_id=posts.ID 
			";
			if ($user_id !="-1"){
				$query .= " LEFT JOIN  {$wpdb->prefix}postmeta as sales_agent_user ON sales_agent_user.post_id=posts.ID ";
			}
			if ($this->is_hpos_enable){
				$query .= " LEFT JOIN  {$wpdb->prefix}wc_order_addresses as order_billing_addresses ON order_billing_addresses.order_id=posts.ID ";
			}
			$query .= " WHERE 1=1 " ;
			if ($this->is_hpos_enable){
				$query .= "	AND	posts.type ='shop_order' ";
			}else{
				$query .= "	AND	posts.post_type ='shop_order' ";
			}
			
			$query .= "		AND woocommerce_order_items.order_item_type ='line_item'";
			if ($this->is_hpos_enable){
				$query .= "		AND posts.status IN ('wc-pending','wc-processing','wc-on-hold', 'wc-completed' ,'wc-cancelled' ,  'wc-refunded' ,'wc-failed')";
			}else{
				$query .= "		AND posts.post_status IN ('wc-pending','wc-processing','wc-on-hold', 'wc-completed' ,'wc-cancelled' ,  'wc-refunded' ,'wc-failed')";
			}

			


			

			if ($user_id !="-1"){
				$query .= " AND sales_agent_user.meta_key='_ic_sales_agent_user_id'";
				$query .= " AND sales_agent_user.meta_value='{$user_id }'";	
			}		
			
			if ($this->is_hpos_enable){
				$query .= " AND order_billing_addresses.address_type='billing'";
			}


			switch ($select_order) {
				case "today":
					if ($this->is_hpos_enable){
						$query .= " AND   date_format( posts.date_created_gmt, '%Y-%m-%d') BETWEEN '{$today}' AND '{$today}'";

					}else{
						$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') BETWEEN '{$today}' AND '{$today}'";
					}
					
					break;
				case "yesterday":
					if ($this->is_hpos_enable){
						$query .= " AND  date_format( posts.date_created_gmt, '%Y-%m-%d') = date_format( DATE_SUB(CURDATE(), INTERVAL 1 DAY), '%Y-%m-%d')";
					}else{
						$query .= " AND  date_format( posts.post_date, '%Y-%m-%d') = date_format( DATE_SUB(CURDATE(), INTERVAL 1 DAY), '%Y-%m-%d')";
					}
					
					break;
				case "last_7_days":
					if ($this->is_hpos_enable){
						$query .= " AND  date_format( posts.date_created_gmt, '%Y-%m-%d') BETWEEN date_format(DATE_SUB(CURDATE(), INTERVAL 7 DAY), '%Y-%m-%d') AND   '{$today}' ";
					}else{
						$query .= " AND  date_format( posts.post_date, '%Y-%m-%d') BETWEEN date_format(DATE_SUB(CURDATE(), INTERVAL 7 DAY), '%Y-%m-%d') AND   '{$today}' ";
					}
					
					break;
				case "last_10_days":
					if ($this->is_hpos_enable){
						$query .= " AND  date_format( posts.date_created_gmt, '%Y-%m-%d') BETWEEN date_format(DATE_SUB(CURDATE(), INTERVAL 10 DAY), '%Y-%m-%d') AND   '{$today}' ";
					}else{
						$query .= " AND  date_format( posts.post_date, '%Y-%m-%d') BETWEEN date_format(DATE_SUB(CURDATE(), INTERVAL 10 DAY), '%Y-%m-%d') AND   '{$today}' ";
					}
					
					break;
				case "last_15_days":
					if ($this->is_hpos_enable){
						$query .= " AND  date_format( posts.date_created_gmt, '%Y-%m-%d') BETWEEN date_format(DATE_SUB(CURDATE(), INTERVAL 15 DAY), '%Y-%m-%d') AND   '{$today}' ";
					}else{
						$query .= " AND  date_format( posts.post_date, '%Y-%m-%d') BETWEEN date_format(DATE_SUB(CURDATE(), INTERVAL 15 DAY), '%Y-%m-%d') AND   '{$today}' ";	
					}
					
					break;		
				case "last_30_days":
					if ($this->is_hpos_enable){
						$query .= " AND  date_format( posts.date_created_gmt, '%Y-%m-%d') BETWEEN date_format(DATE_SUB(CURDATE(), INTERVAL 30 DAY), '%Y-%m-%d') AND   '{$today}' ";
					}else{
						$query .= " AND  date_format( posts.post_date, '%Y-%m-%d') BETWEEN date_format(DATE_SUB(CURDATE(), INTERVAL 30 DAY), '%Y-%m-%d') AND   '{$today}' ";
					}
						
					break;	
				case "this_year":
					if ($this->is_hpos_enable){
						$query .= " AND  YEAR(date_format( posts.date_created_gmt, '%Y-%m-%d')) = YEAR(date_format(CURDATE(), '%Y-%m-%d'))";	
					}else{
						$query .= " AND  YEAR(date_format( posts.post_date, '%Y-%m-%d')) = YEAR(date_format(CURDATE(), '%Y-%m-%d'))";	
					}
						
					break;	
				case "last_year":
					if ($this->is_hpos_enable){
						$query .= " AND  YEAR( posts.date_created_gmt) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR)) ";	
					}else{
						$query .= " AND  YEAR( posts.post_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR)) ";	
					}
							
					break;
				case "none":
					$query .= " ";			
					break;				
				default:
				if ($this->is_hpos_enable){
					$query .= " AND   date_format( posts.date_created_gmt, '%Y-%m-%d') BETWEEN '{$today}' AND '{$today}'";
				}else{
					$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') BETWEEN '{$today}' AND '{$today}'";
				}
					
			}
			
					
					
			 $query = apply_filters('ni_sales_agent_sales_order_report_query', $query, $_REQUEST);		
			 if ($this->is_hpos_enable){
				$query .= "order by posts.date_created_gmt DESC";	
			 }else{
				$query .= "order by posts.post_date DESC";	
			 }
			
			$data = $wpdb->get_results( $query);	
			if ( $wpdb->last_error!=''){
				echo $wpdb->last_error;
			}
			//echo $query;
				//$this->print_data($wpdb);
			//echo mysql_error();
			return $data;	
		}
		function get_sales_order_data(){
			$order_data =$this->ni_query_data();
			if(count($order_data)> 0){
				foreach($order_data as $k => $v){
					
					/*Order Data*/
					$order_id =$v->order_id;
					$order_detail = $this->get_order_detail($order_id);
					foreach($order_detail as $dkey => $dvalue)
					{
							$order_data[$k]->$dkey =$dvalue;
						
					}
					/*Order Item Detail*/
					$order_item_id = $v->order_item_id;
					$order_item_detail= $this->get_order_item_detail($order_item_id );
					foreach ($order_item_detail as $mKey => $mValue){
							$new_mKey = $str= ltrim ($mValue->meta_key, '_');
							$order_data[$k]->$new_mKey = $mValue->meta_value;		
					}
				}
			}
			else
			{
				//echo "No Record Found";
				 $order_data = array();
			}
			//$this->print_data( $order_data);
			
			return apply_filters('nisabur_sales_product_report_data', $order_data );
			//return $order_data;
		}
		function get_sales_product_report_columns(){
			$columns = array();
			$columns["order_id"] = "ID";
			$columns["order_date"] = "Order Date";
			$columns["billing_first_name"] = "First Name";
			$columns["billing_last_name"] = "Last Name";
			$columns["billing_email"] = "Email";
			$columns["billing_phone"] = "Phone";
			$columns["billing_country"] = "Country";
			$columns["payment_method_title"] = "Payment Method";
			$columns["order_status"] = "Status";
			$columns["order_item_name"] = "Product";
			$columns["qty"] = "Qty.";
			$columns["line_tax"] = "Line Tax";
			$columns["line_total"] = "Line Total";
			$columns["ic_sales_agent_user_id"] = $this->get_user_role("Y");
			return apply_filters('nisabur_sales_product_report_columns', $columns );
		}
		function get_order_detail($order_id){
			$order_detail	= get_post_meta($order_id);
			$order_detail_array = array();
			foreach($order_detail as $k => $v)
			{
				$k =substr($k,1);
				$order_detail_array[$k] =$v[0];
			}
			return 	$order_detail_array;
		}
		function get_order_item_detail($order_item_id){
			global $wpdb;
			$sql = "SELECT
					* FROM {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta			
					WHERE order_item_id = {$order_item_id}
					";
					
			$results = $wpdb->get_results($sql);
			return $results;			
		}
		function render_my_meta_box(){
			global $wpdb;
			global $post;
			$sales_person_option = get_option('ic-sales-person-option');
			/*
			print "<pre>";
			print_r($sales_person_option["ic-sales-person-option"]["user_role"]);
			print "</pre>";
			*/
			$role =  $sales_person_option["ic-sales-person-option"]["user_role"];
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
			$query .= " AND   role.meta_key='wp_capabilities'";
			$query .= " AND  role.meta_value   LIKE '%\"{$role}\"%' ";
			
			$query .= " AND   first_name.meta_key='first_name'";
			$query .= " AND   last_name.meta_key='last_name'";
				
			
			
			
			
			$data = $wpdb->get_results($query);
			//$user_id = get_post_meta($post->ID, '_ic_sales_agent_user_id', true);
			$user_id  = 0;
			?>
			<select  name="ic_user_role" id="ic_user_role">
		   <option selected="selected" value="-1">Select Sales Person</option>
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
		} 
	}
}
?>