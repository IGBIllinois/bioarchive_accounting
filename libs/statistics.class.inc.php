<?php
	class statistics {
		private $db;
		
		public function __construct($db){
			$this->db = $db;
		}
		public function __destruct(){}
		
		// Gets the max usage during the time period for each user, and sums those values
		public function get_total_usage($start_date,$end_date,$format=0){
			$sql = "select sum(table1.`usage`)/1048576 as `usage` from (select max(directory_size) as `usage` from archive_usage where date(usage_time) between :start and :end group by directory_id) as table1";
			$args = array(':start'=>$start_date,':end'=>$end_date);
			$result = $this->db->query($sql,$args);
			$total_usage = $result[0]['usage'];
			if($total_usage==""){
				$total_usage="0.00";
			}
			if($format == true){
				$total_usage = number_format($total_usage,4);
			}
			return $total_usage;
		}
		
		// Gets the change in usage between the given dates for each user, and sums those values
		public function get_total_delta_usage($start_date,$end_date,$format=0){
			$sql = "select sum(table1.delta)/1048576 as delta from (select ( coalesce((select `directory_size` from archive_usage where date(usage_time)<=:enddate and directory_id=u.directory_id order by usage_time desc limit 1),0)-coalesce((select directory_size from archive_usage where date(usage_time)<=:startdate and directory_id=u.directory_id order by usage_time desc limit 1),0) ) as delta from archive_usage u group by u.directory_id) as table1"; // Trust me.
			$args = array(':startdate'=>$start_date, ':enddate'=>$end_date);
			$result = $this->db->query($sql,$args);
			$total_delta = $result[0]['delta'];
			if($format == true){
				$total_delta = number_format($total_delta,4);
			}
			return $total_delta;
		}
		
		// Gets the max number of 'small' files during the time period for each user, and sums those values
		public function get_total_smallfiles($start_date,$end_date,$format=0){
			$sql = "select sum(table1.total_smallfiles) as smallfiles from (select max(num_small_files) as total_smallfiles from archive_usage left join directories on directory_id=directories.id where date(usage_time) between :start and :end group by directory) as table1";
			$args = array(':start'=>$start_date,':end'=>$end_date);
			$result = $this->db->query($sql,$args);
			$total_smallfiles = $result[0]['smallfiles'];
			if($total_smallfiles==""){
				$total_smallfiles="0";
			}
			return $total_smallfiles;
		}
		
		// Sum of all costs incurred between the given dates
		public function get_total_cost($start_date,$end_date,$format=0){
			$sql = "select sum(cost) as cost from archive_usage join directories on directories.id=directory_id where date(usage_time) between :start and :end and is_enabled=1 and do_not_bill=0";
			$args = array(':start'=>$start_date,':end'=>$end_date);
			$result = $this->db->query($sql,$args);
			$total_cost = $result[0]['cost'];
			if($total_cost==""){
				$total_cost="0.00";
			}
			if($format == true){
				$total_cost = number_format($total_cost,2);
			}
			return $total_cost;
		}
		
		// Sum of all billed costs incurred between the given dates
		public function get_total_billed_cost($start_date,$end_date,$format=0){
			$sql = "select sum(billed_cost) as cost from archive_usage where date(usage_time) between :start and :end";
			$args = array(':start'=>$start_date,':end'=>$end_date);
			$result = $this->db->query($sql,$args);
			$total_cost = $result[0]['cost'];
			if($total_cost==""){
				$total_cost="0.00";
			}
			if($format == true){
				$total_cost = number_format($total_cost,2);
			}
			return $total_cost;
		}
		
		// Returns a list of the total usage per month, for each month in the given year, optionally for a specified user
		public function get_usage_per_month($year,$directory_id=0){
			$sql = "select month(usage_time) as month, round(SUM(directory_size)/1048576,4) as directory_size from archive_usage where year(usage_time)=:year";
			$args = array(":year"=>$year);
			if($directory_id!=0){
				$sql .= " and directory_id=:dirid";
				$args[':dirid']=$directory_id;
			}
			$sql .= " group by month(usage_time) order by month(usage_time) asc";
			
			$result = $this->db->query($sql,$args);
			
			return $this->get_month_array($result,"month","directory_size");
		}
		
		// Returns a list of the change in usage per month, for each month in the given year, optionally for a specified user
		public function get_delta_usage_per_month($year,$directory_id=0){
			$sql = "select month(u.usage_time) as month, round((SUM(u.directory_size) - coalesce((select SUM(u1.directory_size) from archive_usage u1 where year(u1.usage_time)=year(u.usage_time - interval 1 month ) and month(u1.usage_time)=month(u.usage_time - interval 1 month)";
			$args = array(":year"=>$year);
			if($directory_id!=0){
				$sql .= " and u1.directory_id=:dirid";
				$args[':dirid']=$directory_id;
			}
			$sql .= " order by u1.usage_time desc limit 1
),0) )/1048576,4) as delta from archive_usage u where year(u.usage_time)=:year";
			if($directory_id!=0){
				$sql .= " and u.directory_id=:dirid";
			}
			$sql .= " group by month(usage_time) order by month(usage_time) asc";
			$result = $this->db->query($sql,$args);
			return $this->get_month_array($result,"month","delta");
		}
		
		// Returns a list of the number of 'small' files per month, for each month in the given year, optionally for a specified user
		public function get_smallfiles_per_month($year,$directory_id=0){
			$sql = "select month(usage_time) as month, SUM(num_small_files) as num_small_files from archive_usage where year(usage_time)=:year";
			$args = array(":year"=>$year);
			if($directory_id!=0){
				$sql .= " and directory_id=:dirid";
				$args[':dirid'] = $directory_id;
			}
			$sql .= " group by month(usage_time) order by month(usage_time) asc";
			
			$result = $this->db->query($sql,$args);
			
			return $this->get_month_array($result,"month","num_small_files");
		}
		
		// Returns a list of the total cost incurred per month, for each month in the given year, optionally for a specified user
		public function get_cost_per_month($year,$directory_id=0){
			$sql = "select month(usage_time) as month, sum(cost) as cost from archive_usage where year(usage_time)=:year";
			$args = array(':year'=>$year);
			if($directory_id!=0){
				$sql .= " and directory_id=:dirid";
				$args[':dirid']=$directory_id;
			}
			$sql .= " group by month(usage_time)";
			
			$result = $this->db->query($sql,$args);
			
			return $this->get_month_array($result,"month","cost");
		}
		
		// Returns a list of the total balance per month, for each month in the given year, optionally for a specified user
		public function get_balance_per_month($year,$directory_id=0){
			$month_array = array();
			for ($month=1;$month<=12;$month++){
				// TODO using the prepared statement correctly here might give us a wee performance boost
				$sql = "select sum(amount) as balance from transactions where ((month(transaction_time)<=:month and year(transaction_time)=:year) or year(transaction_time)<:year)";
				$args = array(':month'=>$month,':year'=>$year);
				if($directory_id!=0){
					$sql .= " and directory_id=:dirid";
					$args[':dirid']=$directory_id;
				}
				$result = $this->db->query($sql,$args);
				array_push($month_array,array('month'=>$month,'balance'=>$result[0]['balance']));
			}
			
			return $this->get_month_array($month_array,"month","balance");
		}
		
		// Returns the top $top users by usage between the given dates
		public function get_top_usage_users($start_date,$end_date){
			$sql = "select round(max(directory_size)/1048576,4) as total_usage, concat(username,' - ',:basedir,directory) as username from archive_usage left join directories on directory_id=directories.id left join users on user_id=users.id where date(usage_time) between :start and :end group by directory order by total_usage desc";
			$args = array(":start"=>$start_date,":end"=>$end_date,':basedir'=>ARCHIVE_DIR);
			$allusage = $this->db->query($sql,$args);
			return $allusage;
		}
		
		// Returns the top $top users by change in usage between the given dates
		public function get_top_delta_usage_users($start_date,$end_date){
			$sql = "select ( coalesce((select `directory_size` from archive_usage where date(usage_time)<=:end and directory_id=u.directory_id order by usage_time desc limit 1),0)-coalesce((select directory_size from archive_usage where date(usage_time)<=:start and directory_id=u.directory_id order by usage_time desc limit 1),0) )/1048576 as total_delta, concat(a.username,' - ',:basedir,d.directory) as username from archive_usage u left join directories d on u.directory_id=d.id left join users a on a.id=d.user_id group by d.id order by total_delta desc";
			$args = array(":start"=>$start_date,":end"=>$end_date,':basedir'=>ARCHIVE_DIR);
			$alldelta = $this->db->query($sql,$args);
			return $alldelta;
		}
		
		// Returns the top $top users by cost incurred between the given dates
		public function get_top_cost_users($start_date,$end_date){
			$sql = "select sum(cost) as total_cost, concat(username,' - ',:basedir,directory) as username from archive_usage left join directories on directory_id=directories.id left join users on user_id=users.id where date(usage_time) between :start and :end group by directory order by total_cost desc";
			$args = array(":start"=>$start_date,":end"=>$end_date,':basedir'=>ARCHIVE_DIR);
			$allcost = $this->db->query($sql,$args);
			return $allcost;
		}
		
		// Returns the top $top users by number of 'small' files present between the given dates
		public function get_top_smallfiles_users($start_date,$end_date){
			$sql = "select max(num_small_files) as total_smallfiles, concat(username,' - ',:basedir,directory) as username from archive_usage left join directories on directory_id=directories.id left join users on user_id=users.id where date(usage_time) between :start and :end group by directory order by total_smallfiles desc";
			$args = array(":start"=>$start_date,":end"=>$end_date,':basedir'=>ARCHIVE_DIR);
			$allsmallfiles = $this->db->query($sql,$args);
			return $allsmallfiles;
		}
		
		// Takes a list of data and formats it for use with google charts, with month names and column headers
		public function get_month_array($data,$month_column,$data_column){
			$new_data = array();
			for($i=1;$i<=12;$i++){
				$exists = false;
				if (count($data) > 0){
					foreach($data as $row){
						$month = $row[$month_column];
						if($month == $i){
							$month_name = date('F',mktime(0,0,0,$month,1));
							array_push($new_data, array($month_name,$row[$data_column]));
							$exists = true;
							break(1);
						}
					}
				}
				if(!$exists){
					$month_name = date('F',mktime(0,0,0,$i,1));
					array_push($new_data,array($month_name,0));
				}
				$exists = false;
			}
			return $new_data;
		}
	}
