<?php

/**
 * page_object helper
 *
 * @package activeCollab.modules.system
 * @subpackage helpers
 * Ticket ID #362 - modify Private button (SA) 14March2012
 * Ticket ID #362 - modify Private button (SA) 20 April 2012 
 * http://projects.ffbh.org/public/index.php?path_info=projects%2F59%2Fcomments%2F22278
 *  */

/**
 * Set page properties with following object
 *
 * Parameters:
 *
 * - object - Application object instance
 *
 * @param array $params
 * @param Smarty $smarty
 * @return null
 */
function smarty_function_page_object($params, &$smarty) {
	static $private_roles = false;

	$object = array_var($params, 'object');
	if(!instance_of($object, 'ApplicationObject')) {
		return new InvalidParamError('object', $object, '$object is expected to be an instance of ApplicationObject class', true);
	} // if

	require_once SMARTY_DIR . '/plugins/modifier.datetime.php';

	$wireframe =& Wireframe::instance();
	$logged_user =& get_logged_user();

	$construction =& PageConstruction::instance();
	if($construction->page_title == '') {
		$construction->setPageTitle($object->getName());
	} // if

	if(instance_of($object, 'ProjectObject') && $wireframe->details == '') {
		$in = $object->getParent();
		$created_on = $object->getCreatedOn();
		$created_by = $object->getCreatedBy();

		if(instance_of($created_by, 'User') && instance_of($in, 'ApplicationObject') && instance_of($created_on, 'DateValue')) {
		    //BOF:mod 20120913
			/*
			//EOF:mod 20120913
			$wireframe->details = lang('By <a href=":by_url">:by_name</a> in <a href=":in_url">:in_name</a> on <span>:on</span>', array(
		    //BOF:mod 20120913
			*/
			$wireframe->details = lang('Created on <span>:on</span>', array(
			//EOF:mod 20120913
					'by_url' => $created_by->getViewUrl(),
					'by_name' => $created_by->getDisplayName(),
					'in_url' => $in->getViewUrl(),
					'in_name' => $in->getName(),
					'on' => smarty_modifier_datetime($created_on),
			));
		} elseif(instance_of($created_by, 'User') && instance_of($created_on, 'DateValue')) {
		    //BOF:mod 20120913
			/*
			//EOF:mod 20120913
			$wireframe->details = lang('By <a href=":by_url">:by_name</a> on <span>:on</span>', array(
		    //BOF:mod 20120913
			*/
			$wireframe->details = lang('Created on <span>:on</span>', array(
			//EOF:mod 20120913
					'by_url' => $created_by->getViewUrl(),
					'by_name' => $created_by->getDisplayName(),
					'on' => smarty_modifier_datetime($created_on),
			));
		} elseif(instance_of($created_by, 'User')) {
		    //BOF:mod 20120913
			/*
			//EOF:mod 20120913
			$wireframe->details = lang('By <a href=":by_url">:by_name</a>', array(
		    //BOF:mod 20120913
			*/
			$wireframe->details = lang('Created on <span>:on</span>', array(
			//EOF:mod 20120913
					'by_url' => $created_by->getViewUrl(),
					'by_name' => $created_by->getDisplayName(),
					//BOF:mod 20120913
					'on' => smarty_modifier_datetime($created_on),
					//EOF:mod 20120913
			));
		} elseif(instance_of($created_by, 'AnonymousUser') && instance_of($created_on, 'DateValue')) {
		    //BOF:mod 20120913
			/*
			//EOF:mod 20120913
			$wireframe->details = lang('By <a href=":by_url">:by_name</a> on <span>:on</span>', array(
		    //BOF:mod 20120913
			*/
			$wireframe->details = lang('Created on <span>:on</span>', array(
			//EOF:mod 20120913
					'by_url' => 'mailto:' . $created_by->getEmail(),
					'by_name' => $created_by->getName(),
					'on' => smarty_modifier_datetime($created_on),
			));
		} elseif(instance_of($created_by, 'AnonymousUser')) {
		    //BOF:mod 20120913
			/*
			//EOF:mod 20120913
			$wireframe->details = lang('By <a href=":by_url">:by_name</a>', array(
		    //BOF:mod 20120913
			*/
			$wireframe->details = lang('Created on <span>:on</span>', array(
			//EOF:mod 20120913
					'by_url' => 'mailto:' . $created_by->getEmail(),
					'by_name' => $created_by->getName(),
					//BOF:mod 20120913
					'on' => smarty_modifier_datetime($created_on),
					//EOF:mod 20120913
			));
		} // if
	} // if

	$smarty->assign('page_object', $object);

	// Need to do a case sensitive + case insensitive search to have PHP4 covered
	$class_methods = get_class_methods($object);

	if(in_array('getOptions', $class_methods) || in_array('getoptions', $class_methods)) {
		$options = $object->getOptions($logged_user);
		if(instance_of($options, 'NamedList') && $options->count()) {
			$wireframe->addPageAction(lang('Options'), '#', $options->data, array('id' => 'project_object_options'), 1000);
		} // if

		if(instance_of($object, 'ProjectObject')) {
			if($object->getState() > STATE_DELETED) {
				if($object->getVisibility() <= VISIBILITY_PRIVATE) {
					//Ticket ID #362 - modify Private button (SA) 14March2012 BOF
					$users_table = TABLE_PREFIX . 'users';
					$assignments_table = TABLE_PREFIX . 'assignments';
					$subscription_table = TABLE_PREFIX . 'subscriptions';
//					$rows = db_execute_all("SELECT $assignments_table.is_owner AS is_assignment_owner, $users_table.id AS user_id, $users_table.company_id, $users_table.first_name, $users_table.last_name, $users_table.email FROM $users_table, $assignments_table WHERE $users_table.id = $assignments_table.user_id AND $assignments_table.object_id = ? ORDER BY $assignments_table.is_owner DESC", $object->getId());
					$rows = db_execute_all("SELECT $assignments_table.is_owner AS is_assignment_owner, $users_table.id AS user_id, $users_table.company_id, $users_table.first_name, $users_table.last_name, $users_table.email FROM $users_table, $assignments_table WHERE $users_table.id = $assignments_table.user_id AND $assignments_table.object_id = ".$object->getId()." UNION SELECT '0' AS is_assignment_owner, $users_table.id AS user_id, $users_table.company_id, $users_table.first_name, $users_table.last_name, $users_table.email FROM $users_table, $subscription_table WHERE $users_table.id = $subscription_table.user_id AND $subscription_table.parent_id = ".$object->getId());
					
					if(is_foreachable($rows)) {
						$owner = null;
						$other_assignees = array();
						$users_dropdown_for_tickets = '';
						foreach($rows as $row) {
							if(empty($row['first_name']) && empty($row['last_name'])) {
								$user_link =  clean($row['email']);
							} else {
								$user_link =  clean($row['first_name'] . ' ' . $row['last_name']);
							} // if
							if($row['is_assignment_owner']) {
								$owner = $user_link;
							} else {
								$other_assignees[] = $user_link;
							}
							if($owner) {
								if(instance_of($object, 'Ticket')) {
									if(count($other_assignees) > 0) {
										$users = $owner;
										if (!empty($users)){
											$users .= ', ';
										}
										$users .= implode(', ', $other_assignees);
									} else {
										$users = $owner;
									} // if
								} else {
									if(count($other_assignees) > 0) {
										$users = $owner . ' ' . lang('is responsible', null, true, $language) . '. ' . lang('Other assignees', null, true, $language) . ': ' . implode(', ', $other_assignees) . '.';
									} else {
										$users = $owner . ' ' . lang('is responsible', null, true, $language) . '.';
									} // if
								}
							}elseif(count($other_assignees) > 0){
								$users = implode(', ', $other_assignees);
							}
						}
					}
					$wireframe->addPageMessage(lang('<b>Private</b> - This Ticket has been marked as "Private" and is Visible by these Users:  :users', array('users' => $users)), PAGE_MESSAGE_PRIVATE);
					//Ticket ID #362 - modify Private button (SA) 14March2012 EOF
				} // if
			} else {
				$wireframe->addPageMessage(lang('<b>Trashed</b> - this :type is located in trash.', array('type' => $object->getVerboseType(true))), PAGE_MESSAGE_TRASHED);
			} // if
		} // if
	} // if

	return '';
} // smarty_function_page_object

?>