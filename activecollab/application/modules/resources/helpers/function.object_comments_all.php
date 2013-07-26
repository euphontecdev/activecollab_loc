<?php

  /**
   * object_comments helper
   *
   * @package activeCollab.modules.resources
   * @subpackage helpers
   */
  
  /**
   * List object comments
   * 
   * Parameters:
   * 
   * - object - Parent object. It needs to be an instance of ProjectObject class
   * - comments - List of comments. It is optional. If it is missing comments 
   *   will be loaded by calling getCommetns() method of parent object
   *
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_object_comments_all($params, &$smarty) {
    $object = array_var($params, 'object');
    if(!instance_of($object, 'ProjectObject')) {
      return new InvalidParamError('object', $object, '$object is expected to be an instance of ProjectObject class', true);
    } // if
    
    $logged_user = $smarty->get_template_vars('logged_user');
    if(!instance_of($logged_user, 'User')) {
      return '';
    } // if
    $visiblity = $logged_user->getVisibility();
    //    if ($object->canView($logged_user) && $object->getVisibility() == VISIBILITY_PRIVATE) {
    if ($object->canView($logged_user)) {
    	$visiblity = VISIBILITY_PRIVATE;
    }
    $comments = $object->getComments($visiblity);
    if(is_foreachable($comments)) {
      foreach($comments as $comment) {
        ProjectObjectViews::log($comment, $logged_user);
        //BOF:task_1260
        $comment->set_action_request_n_fyi_flag($logged_user);
        //EOF:task_1260
      } // foreach
    } // if    
    $count_from = 0;      
    $smarty->assign(array(
      '_object_comments_object' 	=> $object,
      '_object_comments_count_from' => $count_from,
      '_object_comments_comments' 	=> $comments,
        //BOF-20120228SA
        '_total_comments' 	=> sizeof($comments),     
        
    ));
    
    return $smarty->fetch(get_template_path('_object_comments_all', 'comments', RESOURCES_MODULE));
  } // smarty_function_object_comments

?>