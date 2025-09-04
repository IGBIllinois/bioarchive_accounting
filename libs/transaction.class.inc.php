<?php
	// Represents the database entry for a single transaction, either a cost incurred or an amount paid
	class transaction {
		private $db;
		private $id;
		private $directory_id;
		private $balance;
		private $amount;
		private $usage_id;
		private $transaction_time;
		
		public function __construct($db,$id=0){
			$this->db = $db;
			if($id != 0){
				$this->get_transaction($id);
			}
		}
		public function __destruct(){}
		
		// Inserts a transaction with the given values into the database, then loads that transaction into this object
		public function create($directory_id,$amount,$usage_id,$date=''){
			if($date == ''){
				$sql = "insert into transactions (directory_id,amount,usage_id) values (:dirid,:amount,:usageid)";
				$args = array(':dirid'=>$directory_id,':amount'=>$amount,':usageid'=>$usage_id);
			} else {
				$sql = "insert into transactions (directory_id,amount,usage_id,transaction_time) values (:dirid,:amount,:usageid,:time)";
				$args = array(':dirid'=>$directory_id,':amount'=>$amount,':usageid'=>$usage_id,':time'=>$date);
			}
			$id = $this->db->insert_query($sql,$args);
			$this->get_transaction($id);
		}
		public function update($amount){
			$sql = "update transactions set amount=:amount,transaction_time=NOW() where id=:id";
			$args = array(':amount'=>$amount,':id'=>$this->id);
			$this->db->non_select_query($sql,$args);
		}
		public function delete(){
			$sql = "delete from transactions where id=:id limit 1";
			$args = array(':id'=>$this->id);
			$this->db->non_select_query($sql,$args);
		}
		
		// Returns the latest transaction in the database for the given user
		public static function latestTransaction($db,$directory_id){
			$sql = "select id from transactions where directory_id=:dirid order by transaction_time desc limit 1";
			$args = array(":dirid"=>$directory_id);
			$transaction = $db->query($sql,$args);
			if(count($transaction)>0){
				return new transaction($db,$transaction[0]['id']);
			} else {
				return self::emptyTransaction($db);
			}
		}
		
		public static function emptyTransaction($db){
			$transaction = new transaction($db);
			$transaction->id = 0;
			$transaction->directory_id = 0;
			$transaction->balance = 0;
			$transaction->amount = 0;
			$transaction->usage_id = 0;
			$transaction->transaction_time = 0;
			return $transaction;
		}
		
		public function get_id(){
			return $this->id;
		}
		public function get_directory_id(){
			return $this->directory_id;
		}
		public function get_amount(){
			return $this->amount;
		}
		public function get_balance(){
			return $this->balance;
		}
		public function get_transaction_time(){
			return $this->transaction_time;
		}
		
		// Loads the transaction with the given id into this object
		public function get_transaction($id){
			$sql = "select t.*, (select sum(t1.amount) from transactions t1 where t1.transaction_time<=t.transaction_time and t1.directory_id=t.directory_id) as balance from transactions t where id=:id limit 1";
			$args = array(':id'=>$id);
			$transaction = $this->db->query($sql,$args);
			$this->id = $transaction[0]['id'];
			$this->directory_id = $transaction[0]['directory_id'];
			$this->balance = $transaction[0]['balance'];
			$this->amount = $transaction[0]['amount'];
			$this->usage_id = $transaction[0]['usage_id'];
			$this->transaction_time = $transaction[0]['transaction_time'];
		}
		public function get_transaction_with_usage_id($usage_id){
			$sql = "select t.id from transactions t where usage_id=:usageid limit 1";
			$args = array(':usageid'=>$usage_id);
			$transaction = $this->db->query($sql,$args);
			if(count($transaction)==1){
				$this->get_transaction($transaction[0]['id']);
			}
		}
	}
