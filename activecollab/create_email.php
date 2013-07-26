<?php
//http://docs.cpanel.net/twiki/bin/view/ApiDocs/WebHome
$cpanelusername = "ffbhpro";
$cpanelpassword = "crystals2010";

$domain = 'projects.ffbh.org';
$username = 'anuj';
$pwd = 'crystals2010';
$query = 'https://projects.ffbh.org:2083/xml-api/cpanel?user=' . $username . '&cpanel_xmlapi_module=Email&cpanel_xmlapi_func=addpop&cpanel_xmlapi_version=1&email=' . $username . '&password=' . $pwd . '&quota=10&domain=' . $domain;

$curl = curl_init();		
# Create Curl Object
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);	
# Allow self-signed certs
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0); 	
# Allow certs that do not match the hostname
curl_setopt($curl, CURLOPT_HEADER,0);			
# Do not include header in output
curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);	
# Return contents of transfer on curl_exec
$header[0] = "Authorization: Basic " . base64_encode($cpanelusername.":".$cpanelpassword) . "\n\r";
curl_setopt($curl, CURLOPT_HTTPHEADER, $header);  
# set the username and password
curl_setopt($curl, CURLOPT_URL, $query);			
# execute the query
$result = curl_exec($curl);
if ($result == false) {
	error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");	
# log error if curl exec fails
}
curl_close($curl);

print $result;

echo 'Done';



?>