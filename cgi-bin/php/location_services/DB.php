<?php
class DB{
	function connect_db(){
		$serverName = "172.16.12.45";
		$ConnectionOptions = array(
		    "Database" => "location_services",
		    "Uid" => "sa",
		    "PWD" => "SQLABC!23456",
		    'ReturnDatesAsStrings'=>true,
		    "CharacterSet" => "UTF-8"
		);
		return sqlsrv_connect($serverName, $ConnectionOptions);
	}
	function getDetailsByImei($imei){
		$results= $results1="";
		$sql="select mobile,imei from imei_mobile_numbers where isActive = 1 and isDeleted = 0 and imei = ?";
		$params = array($imei);
	  $conn=$this->connect_db();
		$query = sqlsrv_query($conn,$sql,$params);

		if($query){
			$results = sqlsrv_fetch_array($query);
			if($results){
				$sql1="select gps_update_interval,gps_fastest_interval,app_version_code,app_update_url from  configuration where isActive = 1 and isDeleted = 0";
        $query1 = sqlsrv_query($conn, $sql1);
			  $results1 = sqlsrv_fetch_array($query1);
				if($results1){
					$results1['imei']=$results['imei'];
					$results1['mobile']=$results['mobile'];
					return $results1;
				}
			     return $results1;
			}else{
				 return "";
			}

		  return $results;
		}else{
			return 0;
		}
	}
}

?>
