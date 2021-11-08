<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
function getAccess()
{
    $curl = curl_init();
    curl_setopt_array(
        $curl,
        array(
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_URL            => 'https://nouveauillinois.my.salesforce.com/' . "/services/oauth2/token",
            CURLOPT_POST           => TRUE,
            CURLOPT_POSTFIELDS     => http_build_query(
                array(
                    'grant_type'    => 'password',
                    'client_id'     => '3MVG9srSPoex34FUZoBofauiNM_Wwyo79A_PByq0P6QT7WH9o9h0nN.mt.ZjIOVW_j6Y6.ecfjGCnDm_9yEv8',
                    'client_secret' => 'EAE91EF3042B88FD2519E76D8210B0B02357EEE49F68C0A504917C9DBB4522BE',
                    'username'      => 'psperanza@nouveauillinois.com',
                    'password'      => '$imboy89' . 'MZt3cWcLmC8FCCjlgfv8j18w'
                )
            )
        )
    );

    $response = json_decode(curl_exec($curl));
    curl_close($curl);

    $access_token = (isset($response->access_token) && $response->access_token != "") ? $response->access_token : die("Error - access token missing from response!");
    $instance_url = (isset($response->instance_url) && $response->instance_url != "") ? $response->instance_url : die("Error - instance URL missing from response!");

    return array(
        "accessToken" => $access_token,
        "instanceUrl" => $instance_url
    );
}
function salesforce_exec($url)
   {
       $credentials = getAccess();

       $curl = curl_init($credentials['instanceUrl'].$url);
       curl_setopt($curl, CURLOPT_HEADER, false);
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: OAuth ".$credentials['accessToken']));

       $json_response = curl_exec($curl);
       curl_close($curl);

       return $json_response;
   }

//var_dump(getAccess());
$accounts = get_object_vars(json_decode(salesforce_exec('/services/data/v20.0/query/?q=SELECT+name+from+Account')))['records'];
foreach($accounts as $index=>$account){
  $a = get_object_vars($account);
  echo '<pre>' . print_r($a, true) . '</pre>';
}
//echo '<html><body><pre>' . print_r($accounts, true) . '</pre></body></html>';



//var_dump(exec('https://nouveauillinois.my.salesforce.com'));
?>
