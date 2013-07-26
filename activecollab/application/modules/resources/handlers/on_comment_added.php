<?php

  /**
   * Resources module handle on_comment_added event
   *
   * @package activeCollab.modules.resources
   * @subpackage helpers
   */
  
  /**
   * Handle on_comment_added event (send email notifications)
   *
   * @param Comment $comment
   * @param ProjectObject $parent
   * @return null
   */
  function resources_handle_on_comment_added(&$comment, &$parent) {
    if(instance_of($parent, 'ProjectObject')) {
      $parent->refreshCommentsCount();
      
      //BOF:mod
      $subscribers_name = '';
      $all_subscribers = $parent->getSubscribers();
      foreach($all_subscribers as $reg_subscriber){
      	$subscribers_name .= $reg_subscriber->getName() . "<br/>";
      }
      if (!empty($subscribers_name)){
      	$subscribers_name = "<br/><br/>-- SET NOTIFICATIONS --<br/>" . $subscribers_name . "<br/><br/>";
      }
      //EOF:mod
      
      if($comment->send_notification) {
      	
      	$temp = explode("\n", strip_tags(htmlspecialchars($comment->getBody())));
      	$flag_line_located = false;
      	$flag_start_now = false;
      	$notify_to = array();
      	$expression = '/\W+\s?\W+/';
      	//$info = '#';
      	foreach($temp as $line){
      		if (!$flag_line_located){
      			$pos = strpos($line, 'SET NOTIFICATIONS');
      			if ($pos!==false){
      				$flag_line_located = true;
      			}
      		}
      		if ($flag_line_located){
      			if ($flag_start_now){
      				if ($line && $line!=''){
      					$split_vals = explode(' ', $line);
      					$first_name = '';
      					$last_name = '';
      					//$info .= '*' . $line . '|' . count($split_vals) .  '*';
      					for($i=count($split_vals)-1; $i>=0; $i--){
      						//$info .= '*' . $i . '|' . $split_vals[$i] . '*';
      						$val = $split_vals[$i];
      						preg_match_all($expression, $val, $matches);
      						//$info .= '*' . $val . ' | ' . count($matches[0]) . '*';
      						if (!count($matches[0])){
      							if ($last_name==''){
      								$last_name = $val;
      							} elseif ($first_name==''){
      								$first_name = $val;
      								break;
      							}
      						}
      					}
      					if (!empty($first_name) && !empty($last_name)){
							$notify_to[] = array('first_name' 	=> $first_name, 
    	  										 'last_name'	=> $last_name);
      						//$info .= '*' . $notify_to[count($notify_to)-1]['first_name'] . ' ' . $notify_to[count($notify_to)-1]['last_name'] . '*';
      					} else {
      						break;
      					}
      				} else {
      					break;
      				}
      			} else {
      				$flag_start_now = true;
      			}
      		}
      	}
      	//$info .= '#';
      	//$info = '';
      	$ticket_id = $comment->getParentId();
      	$exclude = array();
      	$exclude[] = $comment->getCreatedById();
      	$notified_to = '';
		$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
		mysql_select_db(DB_NAME);
		$query = "select a.user_id, b.first_name, b.last_name, b.email from healingcrystals_subscriptions a inner join healingcrystals_users b on a.user_id=b.id where a.parent_id='" . $ticket_id . "'";
		$result = mysql_query($query);
		while($entry = mysql_fetch_assoc($result)){
			$to_be_notified = false;
			foreach($notify_to as $user){
				if ($user['first_name']==$entry['first_name'] && $user['last_name']==$entry['last_name']){
					if ($comment->getCreatedById()!=$entry['user_id']){
						$to_be_notified = true;
						//$info .= $entry['email'] . '|';
						$notified_to .= $entry['first_name'] . ' ' . $entry['last_name'] . ', ';
					}
					break;
				}
			}
			if (!$to_be_notified){
				$exclude[] = $entry['user_id'];
			}
		}
		//mysql_query("insert into testing (entry) values ('" . str_replace("'", "''", $info . $query) . "')");
		mysql_close($link);
      	
        $created_by = $comment->getCreatedBy();
        $parent->sendToSubscribers('resources/new_comment', array(
          'comment_body' => $comment->getFormattedBody(),
          'comment_url' => $comment->getViewUrl(),
          'created_by_url' => $created_by->getViewUrl(),
          'created_by_name' => $created_by->getDisplayName(),
          //BOF:mod
          'subscribers_name' => $subscribers_name, 
          //EOF:mod
        //), $comment->getCreatedById(), $parent);
        ), $exclude, $parent);
      } // if
    } // if
  } // resources_handle_on_comment_added

?>