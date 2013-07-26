<?php
  function smarty_function_object_project_selection($params, &$smarty) {
    $object = array_var($params, 'object');
    if(!instance_of($object, 'ProjectObject')) {
      return new InvalidParamError('object', $object, '$object is not valid instance of ProjectObject class', true);
    }
	$user = array_var($params, 'user');
	$select = '';
	//$active_milestones = Milestones::findByProject($object->getProject(), $user);
	$active_milestones = Milestones::findActiveByProject_custom($object->getProject());
	$select = '<select onchange="modify_project_association(this);">';
	$select .= '<option value="">-- Select Project --</option>';
	foreach($active_milestones as $active_milestone){
		$select .= '<option value="' . $active_milestone->getId() . '" ' . ($object->getMilestoneId()==$active_milestone->getId() ? ' selected ' : '') . ' >' . $active_milestone->getName() . '</option>';
	}
	$select .= '</select>';
    return $select;
  }
?>