<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_error', 1); 
define('DEBUG', true);     
$json = file_get_contents('php://input');
$data=  json_decode($json);
try {
	require_once(getcwd().'/DB.php');

   $imei=@$_GET['imei'];
    if(empty($imei)){
		 $data = array('message'=>'imei is required.','status'=>0);echo json_encode($data);exit; 
	}
	 $db = new DB();
	if(@$imei){
		    $details=$db->getDetailsByImei(@$imei);
			if($details){
					 $data = array('status'=>1,'massage'=>'Records Exists','result' =>$details);   
					 echo json_encode($data);exit;
				}else{
					$data = array('status'=>0,'message'=>'No Records.','result'=>"");   
		            echo json_encode($data); exit;
				}
	}
			
	
}  catch (PrestaShopWebserviceException $e) {
      
        error_log("cartCount Api- ".$e->getMessage());
}

?>