<?php

class setting{
	private $key;
	private $value;
	private $name;
	private $modified;
	
	public function __construct($key,$value,$name,$modified){
		$this->key =			$key;
		$this->value =			$value;
		$this->name =			$name;
		$this->modified =		$modified;
	}
	public function __destruct(){}
	public function get_key(){return $this->key;}
	public function get_value(){return $this->value;}
	public function get_name(){return $this->name;}
	public function get_modified(){return $this->modified;}
}


class settings {
	
	private $db;
	private $settings;
	
	public function __construct($db){
		$this->db = $db;
		$this->settings = array();
		$this->load_settings();
	}
	
	public function get_all_settings(){
		return $this->settings;
	}
	
	public function get_setting($key){
		if(isset($this->settings[$key])){
			return $this->settings[$key]->get_value();
		}
		return "";
	}
	
	public function set_setting($key,$value){
		$sql = "update `settings_values` set `current`=0 where `key`=:key";
		$this->db->non_select_query($sql,array(':key'=>$key));
		$sql = "insert into `settings_values` (`key`,`value`,`current`) values (:key,:value,1)";
		$args = array(':key'=>$key,':value'=>$value);
		$result = $this->db->non_select_query($sql,$args);
		
		$this->load_settings();
		
		return $result;
	}

	public function load_settings(){
		$settings = $this->db->query("select s.key, s.name, v.value, v.modified from settings s join settings_values v on s.key=v.key where v.current=1");
		foreach ($settings as $key=>$setting){
			$this->settings[$setting['key']] = new setting($setting['key'],$setting['value'],$setting['name'],$setting['modified']);
		}
	}

	public static function get_server_name() {
        $server_name = substr($_SERVER['SERVER_NAME'],0,strpos($_SERVER['SERVER_NAME'],"."));
        return $server_name;
	}
	
	public static function get_root_data_dirs(){
		$data_dirs = explode(" ",ROOT_DATA_DIRS);
		return $data_dirs;
	}
}

?>
