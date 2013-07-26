<?php

/**
 * object_comments helper
 *
 * @package activeCollab.modules.resources
 * @subpackage helpers
 * Ticket ID #362 - modify Private button (SA) 15March2012
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
function smarty_function_object_comments($params, &$smarty) {
    $object = array_var($params, 'object');
    if (!instance_of($object, 'ProjectObject')) {
        return new InvalidParamError('object', $object, '$object is expected to be an instance of ProjectObject class', true);
    } // if

    $logged_user = $smarty->get_template_vars('logged_user');
    if (!instance_of($logged_user, 'User')) {
        return '';
    } // if
    //Ticket ID #362 - modify Private button (SA) 15March2012 BOF
    $visiblity = $logged_user->getVisibility();
//    if ($object->canView($logged_user) && $object->getVisibility() == VISIBILITY_PRIVATE) {
    if ($object->canView($logged_user)) {
        $visiblity = VISIBILITY_PRIVATE;
    }
    //19 March2012 (SA) Ticket #760: fix broken permalinks BOF
    /*if ($_GET['show_all'] == '1') {
        $comments = $object->getComments($visiblity);
        if (is_foreachable($comments)) {
            foreach ($comments as $comment) {
                ProjectObjectViews::log($comment, $logged_user);
                $comment->set_action_request_n_fyi_flag($logged_user);
            } // foreach
        } // if    
        $count_from = 0;
        $smarty->assign(array(
            '_object_comments_object' => $object,
            '_object_comments_count_from' => $count_from,
            '_object_comments_comments' => $comments,
            '_total_comments' => sizeof($comments),
            '_object_comments_show_header' => array_var($params, 'show_header', true),
             '_object_comments_show_form' => array_var($params, 'show_form', true),
            '_counter' =>sizeof($comments)+1,
        ));
        return $smarty->fetch(get_template_path('_object_comments', 'comments', RESOURCES_MODULE));
    } else {*/
        $comments = isset($params['comments']) ? $params['comments'] : $object->getComments($visiblity);
        //Ticket ID #362 - modify Private button (SA) 15March2012 EOF
        if (is_foreachable($comments)) {
            foreach ($comments as $comment) {
                ProjectObjectViews::log($comment, $logged_user);
                //BOF:task_1260
                $comment->set_action_request_n_fyi_flag($logged_user);
                //EOF:task_1260
            } // foreach
        } // if
        $count_from = 0;
        if (isset($params['count_from'])) {
            $count_from = (integer) $params['count_from'];
        } // if   

        $object->refreshCommentsCount();
		$total_comments = $object->getCommentsCount();
		if (empty($total_comments)){
			$total_comments = Comments::countByObject($object);
		}
        $smarty->assign(array(
            '_object_comments_object' => $object,
            '_object_comments_count_from' => $count_from,
            '_object_comments_comments' => $comments,
            '_object_comments_show_header' => array_var($params, 'show_header', true),
            '_object_comments_show_form' => array_var($params, 'show_form', true),
            '_object_comments_next_page' => array_var($params, 'next_page', ''),
            //BOF-20120228SA
            //'_total_comments' => $object->getCommentsCount(),
			'_total_comments' => $total_comments,
            '_counter' =>'21',
			'current_page' => array_var($params, 'current_page', '1'), 
			'last_page' => array_var($params, 'last_page', ''), 
			'view_url' => array_var($params, 'view_url', ''), 
			'scroll_to_comment' => array_var($params, 'scroll_to_comment', ''), 
        ));
        return $smarty->fetch(get_template_path('_object_comments', 'comments', RESOURCES_MODULE));
    //}       
}

// smarty_function_object_comments
?>