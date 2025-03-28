<?php
if ( ! defined( 'ABSPATH' ) ) { exit;}
if( !class_exists( 'Ni_Sales_Agent_Report' ) ) {
	include_once("ni-user-role-report-function.php");
	class Ni_Sales_Agent_Report extends Ni_User_Role_Report_Function{
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
					<div class="ni-report-title">Sales Agent Report</div>
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
									
									<td><input type="submit" value="Search" id="btnSearch" name="btnSearch"  class="ni-btn-search"/></td>
								</tr>
							</table>
							<input type="<?php echo $type; ?>" name="sub_action" value="ni_sales_agent_report" />
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
			$columns = $this->get_sales_agent_report_columns();
			
			
			if (count($data)>0){
			$select_order = isset($_REQUEST["select_order"])?$_REQUEST["select_order"]:'today';	
			?>
             <div>
            	<?php do_action("ni_before_table_sales_agent_report"); ?>
            </div>
			<div style="overflow-x:auto;">
				<table class="ni-sale-role-data-table" cellspacing="0" cellpadding="0">
					<tr>
						<?php foreach($columns  as $key=>$value): ?>
							<th><?php echo $value; ?></th>
						<?php endforeach; ?>
						
					</tr>
					<?php foreach($data as $key=>$value): ?>
						<tr>
							<?php foreach($columns as $k=>$v): ?>
								<?php switch($k) : case "a": break;?>
									<?php case "order_total": ?>
											<td><?php echo  wc_price($value->$k); ?></td>
									 <?php break; ?>
                                     <?php case "user_email": ?>
											<td><a href="mailto:<?php echo  $value->$k; ?>"><?php echo  $value->$k; ?></a></td>
									 <?php break; ?>
									<?php case "button": ?>
											<td> <input type="button" class="ni-btn-search _ni_agent_report_ajax_button" value="<?php echo $v; ?>" data-user_email="<?php echo $value->user_email; ?>" data-first_name="<?php echo $value->first_name; ?>" data-last_name="<?php echo $value->last_name; ?>" data-order_count="<?php echo $value->order_count; ?>" data-order_total="<?php echo $value->order_total; ?>" data-ic_sales_agent_user_id ="<?php echo $value->ic_sales_agent_user_id; ?>"  > </td>
									 <?php break; ?>		
										<?php default; ?> 
											 <td><?php echo $value->$k; ?></td>  
								 <?php endswitch;?>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
					
				</table>
			</div>
			<?php	
			}else{
			?>
				<div class="ni-no-record-found">No record found</div>
			<?php	
			}
		}
		function get_sales_agent_report_columns(){
			$columns  = array();
			$columns["first_name"] = "First Name";
			$columns["last_name"] = "Last Name";
			$columns["user_email"] = "User email";
			$columns["order_count"] = "Order Count";
			$columns["order_total"] = "Order Total";
			return apply_filters('ni_sales_agent_report_columns', $columns );
		}
		
		function ni_query_data($type="DEFAULT"){
			//echo json_encode($_REQUEST);
			$select_order = isset($_REQUEST["select_order"])?$_REQUEST["select_order"]:'today';
			$today = date_i18n("Y-m-d");
			//$select_order = $this->get_request("select_order","today");
			global $wpdb;	
			$query = "";
			$query .= " SELECT  ";
			
			if ($this->is_hpos_enable){
				$query .= " SUM(ROUND(posts.total_amount,2)) as order_total ";
			}else{
				$query .= " SUM(ROUND(order_total.meta_value,2)) as order_total ";
			}
			
			$query .= ", ic_sales_agent_user_id.meta_value as ic_sales_agent_user_id ";
			
			$query .= ", first_name.meta_value as first_name "; 
			$query .= ", last_name.meta_value as last_name "; 
			$query .= ", COUNT(*) as order_count ";
			$query .= ", user_email as user_email ";

			if ($this->is_hpos_enable){
				$query .= " FROM {$wpdb->prefix}wc_orders as posts	";
			}else{
				$query .= " FROM {$wpdb->prefix}posts as posts	";
			}
			
			if ($this->is_hpos_enable){

			}else{
				$query .= " LEFT JOIN  {$wpdb->prefix}postmeta as order_total ON order_total.post_id=posts.ID  ";
			}
			
			$query .= " LEFT JOIN  {$wpdb->prefix}postmeta as ic_sales_agent_user_id ON ic_sales_agent_user_id.post_id=posts.ID  ";
			
			$query .= " LEFT JOIN  {$wpdb->prefix}usermeta as first_name ON first_name.user_id=ic_sales_agent_user_id.meta_value  ";
			
			$query .= " LEFT JOIN  {$wpdb->prefix}usermeta as last_name ON last_name.user_id=ic_sales_agent_user_id.meta_value  ";
			
			$query .= " LEFT JOIN  {$wpdb->prefix}users as user_email ON user_email.ID=ic_sales_agent_user_id.meta_value  ";
			
			
			$query .= " WHERE 1 = 1 ";
			if ($this->is_hpos_enable){
				$query .= "	AND	posts.type ='shop_order' ";
			}else{
				$query .= "	AND	posts.post_type ='shop_order' ";
			}
			if ($this->is_hpos_enable){
				
			}else{
				$query .= "	AND	order_total.meta_key ='_order_total' ";
			}
			
			$query .= "	AND	ic_sales_agent_user_id.meta_key ='_ic_sales_agent_user_id' ";
			
			$query .= "	AND	first_name.meta_key ='first_name' ";
			$query .= "	AND	last_name.meta_key ='last_name' ";
			if ($this->is_hpos_enable){
			$query .= "	AND posts.status IN ('wc-pending','wc-processing','wc-on-hold', 'wc-completed' ,'wc-cancelled' ,  'wc-refunded' ,'wc-failed')";
			}	else{
			$query .= "	AND posts.post_status IN ('wc-pending','wc-processing','wc-on-hold', 'wc-completed' ,'wc-cancelled' ,  'wc-refunded' ,'wc-failed')";
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
			
			$query .= "	 GROUP BY  ic_sales_agent_user_id.meta_value  ";		 
			if ($this->is_hpos_enable){
				$query .= "order by posts.date_created_gmt DESC";	
			 }else{
				$query .= "order by posts.post_date DESC";	
			 }
			
			$data = $wpdb->get_results( $query);	
			if ( $wpdb->last_error!=''){
				echo $wpdb->last_error;
			}
		//	echo  $query;
			//$this->print_data($wpdb);
			//echo mysql_error();
			return $data;	
		}
		function get_sales_order_data(){
			$order_data =$this->ni_query_data();
			
			return $order_data;
		}
	}
}
?>