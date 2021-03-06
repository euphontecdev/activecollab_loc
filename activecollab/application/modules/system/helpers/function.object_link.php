<?php

  /**
   * object_link helper
   *
   * @package activeCollab.modules.system
   * @subpackage helpers
   */
  
  /**
   * Render default object link
   *
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_object_link($params, &$smarty) {
    static $cache = array(), $cache_deleted = array();
        
    $object = array_var($params, 'object');
    if(!instance_of($object, 'ProjectObject') && !instance_of($object, 'Attachment')) {
      return new InvalidParamError('object', $object, '$object is not valid instance of ProjectObject class', true);
    } // if
    
    $del_completed = true;
    if(array_key_exists('del_completed', $params)) {
      $del_completed = (boolean) $params['del_completed'];
    } // if
    
	$custom_mark_as_complete = false;
	if ($object->getType()=='Comment'){
		$parent = $object->getParent();
		if ($parent->getType()=='Page' && $parent->getIsArchived()==true){
			$custom_mark_as_complete = true;
		} elseif ($parent->isCompleted()){
			$custom_mark_as_complete = true;
		}
	}
    
    if($custom_mark_as_complete || ($del_completed && $object->can_be_completed && $object->isCompleted()) || (instance_of($object, 'Page') && $object->getIsArchived()=='1')) {
      if(!isset($cache_deleted[$object->getId()])) {
        //$cache_deleted[$object->getId()] = '<del class="completed"><a href="' . clean($object->getViewUrl()) . '" title="' . clean($object->getName()) . '">' . clean($object->getName()) . '</a></del>';
        $cache_deleted[$object->getId()] = '<del class="completed"><a href="' . clean($object->getViewUrl()) . '" title="' . strip_tags($object->getName()) . '">' . strip_tags($object->getName()) . '</a></del>';
      } // if
      
      return $cache_deleted[$object->getId()];
    } else {
      if(!isset($cache[$object->getId()])) {
        //$cache[$object->getId()] = '<a href="' . clean($object->getViewUrl()) . '">' . clean($object->getName()) . '</a>';
        $cache[$object->getId()] = '<a href="' . clean($object->getViewUrl()) . '">' . strip_tags($object->getName()) . '</a>';
      } // if
      
      return $cache[$object->getId()];
    } // if
  } // smarty_function_object_link

?>