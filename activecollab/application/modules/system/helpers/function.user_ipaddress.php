<?php

  /**
   * user_ipaddress helper
   *
   * @package activeCollab.modules.system
   * @subpackage helpers
   */
  
  /**
   * Display user ip address if logged in
   * 
   * - user - User
   * 
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_user_ipaddress($params, &$smarty) {
    $user = array_var($params, 'user');
    $resp = array();
    // User instance
    if(instance_of($user, 'User')) {
		$reference = new DateTimeValue("-30 minutes");
		
        $link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
        mysql_select_db(DB_NAME);
		$query = "SELECT a.user_ip from " . TABLE_PREFIX . "user_sessions a inner join " . TABLE_PREFIX .  "users b on a.user_id=b.id WHERE b.id='" . $user->getId() . "' and b.last_activity_on>'" . $reference . "'";
		$result = mysql_query($query, $link);
		if (mysql_num_rows($result)){
			$info = mysql_fetch_assoc($result);
			return $info['user_ip'];
		} else {
			return '--';
		}
		mysql_close($link);
    } // if
	return '--';
  } // smarty_function_user_ipaddress

?>