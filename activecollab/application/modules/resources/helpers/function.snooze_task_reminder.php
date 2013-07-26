<?php

  /**
   * snooze_task_reminder helper definition
   * 
   * Reason why this helper needs to be here is because it is used across entire 
   * system and other modules may required it without check if timetracking 
   * module is installed
   *
   * @package activeCollab.modules.resources
   * @subpackage helpers
   */

  /**
   * Render snooze task reminder widget
   *
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
function smarty_function_snooze_task_reminder($params, &$smarty) {
	$object = array_var($params, 'object');
	if(!instance_of($object, 'ProjectObject')) {
		return new InvalidParamError('$object', $object, '$object is expected to be a valid instance of ProjectObject class');
	} // if

	return '<span class="option"><a href="' . assemble_url('project_task_snoozereminder', array('project_id' => $object->getProjectId(), 'task_id' => $object->getId() )) . '" onclick="javascript:return false;"><img id="snooze_' . $object->getId() . '" src="' . get_image_url('gray-clock-small.gif') . '" alt="Snooze Reminder" title="Snooze Reminder" /></a></span>';
} // smarty_function_snooze_task_reminder