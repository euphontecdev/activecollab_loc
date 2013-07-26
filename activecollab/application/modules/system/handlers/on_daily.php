<?php

  /**
   * System handle daily tasks
   *
   * @package activeCollab.modules.system
   * @subpackage handlers
   */
  
  /**
   * Do daily taks
   *
   * @param void
   * @return null
   */
  function system_handle_on_daily() {
    ProjectObjectViews::cleanUp();

	$priorities_images = array(
		PRIORITY_URGENT 	=> 'assets/images/icons/priority/urgent.png',
		PRIORITY_HIGHEST 	=> 'assets/images/icons/priority/highest.gif',
		PRIORITY_HIGH    	=> 'assets/images/icons/priority/high.gif',
		PRIORITY_NORMAL  	=> 'assets/images/icons/priority/normal.gif',
		PRIORITY_LOW     	=> 'assets/images/icons/priority/low.gif',
		PRIORITY_LOWEST  	=> 'assets/images/icons/priority/lowest.gif',
		PRIORITY_HOLD  		=> 'assets/images/icons/priority/hold.png',
		'-99'  				=> 'assets/images/icons/priority/unknown.png',);

	$pages = array();
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
	mysql_select_db(DB_NAME);
	$sql = "select id, name from healingcrystals_project_objects where project_id='" . TASK_LIST_PROJECT_ID . "' and type='Page'";
	$result = mysql_query($sql, $link);
	while ($entry = mysql_fetch_assoc($result)){
		list($name, ) = explode('-', $entry['name']);
		$name = trim($name);
		$pages[$name] = $entry['id'];
	}
	
	$current_time = time();

	$users = Users::findAll();
	foreach($users as $user){
		$flag = 1;
		$message = '';
		$name = $user->getName();
		if (array_key_exists($name, $pages)){
			$page = new Page($pages[$name]);
			if ($page){
				$sql = "select id from healingcrystals_project_objects where parent_id='" . $pages[$name] . "' and parent_type='Page' and type='Task' and completed_on is null and priority is null and created_on>='" . date('Y-m-d H:i:s', $current_time - (1*24*60*60) ) . "' order by created_on";
				$result = mysql_query($sql, $link);
				if (mysql_num_rows($result)){
					$show_task_list = true;
				} else {
					$show_task_list = false;
				}
			
				if (date('N')=='1' || $show_task_list){
					$message .= 
	'<style>
		.odd {background-color:#ffffff;}
		.even{background-color:#eeeeee;}
	</style>
	<table>
		<tr>
			<td colspan="3">Task List: ' . $name . '</td>
		</tr>
		<tr>
			<td align="center">Priority</td>
			<td>Task</td>
			<td>&nbsp;</td>
		</tr>';
					$tasks = Tasks::findOpenByObject($page);
					foreach($tasks as $task){
						$message .= '
		<tr class="' . ($flag%2===1 ? 'odd' : 'even' ) . '">
			<td valign="top" align="center"><img  src="http://projects.ffbh.org/public/' . $priorities_images[$task->getPriority()] . '"/></td>
			<td valign="top">' . $task->getName() . '</td>
			<td valign="top"><a href="' . $task->getViewUrl() . '">View</a></td>
		</tr>';
						$flag++;
					}
					$message .= '
	</table>';
					$subject = 'projects: healingcrystals.com Task list';
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
					$headers .= 'From: FFBH Reminder <auto@ffbh.org>' . "\r\n";
					mail($user->getEmail(), $subject, $message, $headers);
				}
			}
		}
		
	}

	$sql = "select po.id, cast(if( pom.recurring_period_type='D', DATE_ADD(po.due_on, interval pom.recurring_period day), if(pom.recurring_period_type='W', DATE_ADD(po.due_on, interval pom.recurring_period week), if(pom.recurring_period_type='M', DATE_ADD(po.due_on, interval pom.recurring_period month), null ) ) ) as Date) as next_due_date, cast(DATE_ADD(now(), interval 0 day) as Date) as cur_date, cast(if(isnull(pom.email_reminder_unit), null, if( pom.email_reminder_unit='D', DATE_ADD(po.due_on, interval pom.email_reminder_period day), if(pom.email_reminder_unit='W', DATE_ADD(po.due_on, interval pom.email_reminder_period week), if(pom.email_reminder_unit='M', DATE_ADD(po.due_on, interval pom.email_reminder_period month), null ) ) )	) as Date) as reminder_date from healingcrystals_project_objects po inner join  healingcrystals_project_object_misc pom on po.id=pom.object_id where po.type='Task' and po.due_on is not null and po.due_on<=now() and po.completed_on is null and pom.recurring_period_condition='after_due_date' and if(pom.recurring_end_date is not null and pom.recurring_end_date!='0000-00-00', if(pom.recurring_end_date>=now(), 1, 0), 1)=1 having next_due_date=cur_date"; 
	$result = mysql_query($sql);
	while ($entry = mysql_fetch_assoc($result)){
		$task = new Task($entry['id']);
		$action = $task->complete(new AnonymousUser('auto', 'auto@projects.ffbh.org'));
		if (!empty($entry['reminder_date']) && $entry['cur_date']==$entry['reminder_date']){
			$sql02 = "select id from " . TABLE_PREFIX . "project_objects where type='Task' and project_id='" . $task->getProjectId() . "' and milestone_id='" . $task->getMilestoneId() . "' and parent_id='" . $task->getParentId() . "' order by id desc limit 0, 1";
			$result02 = mysql_query($sql02);
			if (mysql_num_rows($result02)){
				$info = mysql_fetch_assoc($result02);
				$recurring_task = new Task($info['id']);
				$parent = $recurring_task->getParent();
				$project = $recurring_task->getProject();
				$assignees = $recurring_task->getAssignees();
				
				$priorities = array(
					PRIORITY_HIGHEST 	=> lang('Highest'),
					PRIORITY_HIGH    	=> lang('High'),
					PRIORITY_NORMAL  	=> lang('Normal'),
					PRIORITY_LOW     	=> lang('Low'),
					PRIORITY_LOWEST  	=> lang('Lowest'),
					PRIORITY_ONGOING  	=> lang('Ongoing'),
					PRIORITY_HOLD  		=> lang('Hold'),
				);
				
				$due_date = $task->getDueOn();
				$due_date = date('m/d/Y', strtotime($due_date));
				$reminder_date = date('m/d/Y', strtotime($entry['reminder_date']));
				foreach($assignees as $assignee) {
					$assignees_string .= $assignee->getDisplayName() . ', ';
				}
				if (!empty($assignees_string))
					$assignees_string = substr($assignees_string, 0, -2);
				else
					$assignees_string = '--';
					
				$reminders_sent = array();
				foreach($assignees as $user) {
					//if ($user->getEmail()=='anuj@focusindia.com'){
						$reminder = new Reminder();

						$reminder->setAttributes(array(
							'user_id'   => $user->getId(),
							'object_id' => $recurring_task->getId(),
							'comment'   => $comment,
						));

						$save = $reminder->save();
						if($save && !is_error($save)) {
							$reminders_sent[] = $user->getDisplayName();
							ApplicationMailer::send($user, 'system/reminder', array(
								'reminded_by_name'  				=> 'AutoReminder',
								'reminded_by_url'   				=> '',
								'object_name'       				=> $recurring_task->getName(),
								'object_url'        				=> $recurring_task->getViewUrl(),
								'object_type'       				=> strtolower($recurring_task->getType()),
								'comment_body'      				=> $comment,
								'project_name'      				=> $project->getName(),
								'project_url'       				=> $project->getOverviewUrl(),
								'ticket_name'       				=> $parent->getName(),
								'ticket_url'       					=> $parent->getViewUrl(),
								'object_priority' 					=> $priorities[(string)$recurring_task->getPriority()], 
								'object_due_date' 					=> $due_date, 
								'object_reminder_date_n_time' 		=> $reminder_date, 
								'object_assignees' 					=> $assignees_string, 
								'task_mark_complete_url' 			=> $recurring_task->getCompleteUrl() . '&auto=1', 
								'display_status_for_complete_url'	=> ($recurring_task->is_action_request_task() ? '' : 'none'), 
							), $recurring_task);
						}
					//}
				}
			}
		}
	}
	
	mysql_close($link);
  } // system_handle_on_daily
?>