<?php
function smarty_function_get_comment_custom_info($params, &$smarty) {
	$start_from_ts = strtotime('2013-05-07 00:00:00');
	$comment = array_var($params, 'comment');
	$created_on_ts = strtotime($comment->getCreatedOn());
	if ($created_on_ts>$start_from_ts){
		$custom_info = array();
		$ar_entries = array();
		$em_entries = array();

		$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
		mysql_select_db(DB_NAME);

		$sql = "select user_id, is_action_request, marked_for_email from healingcrystals_assignments_action_request where comment_id='" . $comment->getId() . "' order by id";
		$result = mysql_query($sql);
		if (mysql_num_rows($result)){
			while($entry = mysql_fetch_assoc($result)){
				if ($entry['is_action_request'] || $entry['marked_for_email']){
					$user = new User($entry['user_id']);
					if ($entry['is_action_request']){
						$ar_entries[] = ($entry['is_action_request']=='1' ? '<span style="color:red;">' : '') . $user->getName() . ($entry['is_action_request']=='1' ? '</span>' : '');
					}
					if ($entry['marked_for_email']){
						$em_entries[] = $user->getName();
					}
				}
			}
			if ($ar_entries){
				$custom_info[] = '<div style="width:175px;float:left;">Action Request Marked to</div><span>: ' . implode(', ', $ar_entries) . '</span>';
			}
			if ($em_entries){
				$custom_info[] = '<div style="width:175px;float:left;">Email Sent to</div><span>: ' . implode(', ', $em_entries) . '</span>';
			}
			return '<div>' . implode('<br/>', $custom_info) . '</div>';
		}
	}
	return '';
}
?>