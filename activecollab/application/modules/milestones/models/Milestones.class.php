<?php

  /**
   * Milestones manager class
   *
   * @package activeCollab.modules.milestones
   * @subpackage models
   */
  class Milestones extends ProjectObjects {
    
    /**
     * Return milestones by a given list of ID-s
     *
     * @param array $ids
     * @param integer $min_state
     * @param integer $min_visibility
     * @return array
     */
    function findByIds($ids, $min_state = STATE_VISIBLE, $min_visibility = VISIBILITY_NORMAL) {
      return ProjectObjects::find(array(
        'conditions' => array('id IN (?) AND type = ? AND state >= ? AND visibility >= ?', $ids, 'Milestone', $min_state, $min_visibility),
        //BOF: task 882 | AD
        /*
        //EOF: task 882 | AD
		//'order' => 'due_on',
		//BOF: task 882 | AD
		*/
		'order' => 'name',
		//EOF: task 882 | AD
      ));
    } // findByIds
    
    /**
     * Return all visible milestone by a project
     *
     * @param Project $project
     * @param integer $min_visibility
     * @return array
     */
    function findAllByProject($project, $min_visibility = VISIBILITY_NORMAL) {
    	return ProjectObjects::find(array(
        'conditions' => array('project_id = ? AND type = ? AND state >= ? AND visibility >= ?', $project->getId(), 'Milestone', STATE_VISIBLE, $min_visibility),
        //BOF: task 882 | AD
        /*
        //EOF: task 882 | AD
		//'order' => 'due_on',
		//BOF: task 882 | AD
		*/
		'order' => 'name',
		//EOF: task 882 | AD
      ));
    } // findAllByProject
  
    /**
     * Return all milestones for a given project
     *
     * @param Project $project
     * @param User $user
     * @return array
     */
    function findByProject($project, $user) {
      if($user->getProjectPermission('milestone', $project) >= PROJECT_PERMISSION_ACCESS) {
        return ProjectObjects::find(array(
          'conditions' => array('project_id = ? AND type = ? AND state >= ? AND visibility >= ?', $project->getId(), 'Milestone', STATE_VISIBLE, $user->getVisibility()),
        //BOF: task 882 | AD
        /*
        //EOF: task 882 | AD
		//'order' => 'due_on',
		//BOF: task 882 | AD
		*/
		'order' => 'name',
		//EOF: task 882 | AD
        ));
      } // if
      return null;
    } // findByProject
    
    function findActiveByProject_custom($project) {
        return ProjectObjects::find(array(
          'conditions' => array('project_id = ? AND type = ? AND state >= ? AND visibility >= ? and completed_on is null', $project->getId(), 'Milestone', STATE_VISIBLE, VISIBILITY_NORMAL),
		'order' => 'name',
        ));
      return null;
    }
    /**
     * Return all active milestones in a given project
     *
     * @param Project $project
     * @param integer $min_state
     * @param integer $min_visibility
     * @return array
     */
	//BOF:mod 20121108
	/*
	//EOF:mod 20121108
    function findActiveByProject($project, $min_state = STATE_VISIBLE, $min_visibility = VISIBILITY_NORMAL, $order_by = 'name', $sort_order = '', $milestone_category_id = '') {
      return ProjectObjects::find(array(
		'conditions' => (!empty($milestone_category_id) ? array('project_id = ? AND type = ? AND state >= ? AND visibility >= ? AND completed_on IS NULL AND category_id = ?', $project->getId(), 'Milestone', $min_state, $min_visibility, $milestone_category_id) : array('project_id = ? AND type = ? AND state >= ? AND visibility >= ? AND completed_on IS NULL AND (category_id is null or category_id=0)', $project->getId(), 'Milestone', $min_state, $min_visibility) ),
		'order'      => $order_by,
		'sort'		 => $sort_order
      ));
    } // findActiveByProject
	//BOF:mod 20121108
	*/
	function findActiveByProject($project, $min_state = STATE_VISIBLE, $min_visibility = VISIBILITY_NORMAL, $order_by = 'name', $sort_order = '', $milestone_category_id = '') {
	//EOF:mod 20121108
		$resp = array();
		$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
		mysql_select_db(DB_NAME);
		$query = "select a.id from healingcrystals_project_objects a left join healingcrystals_project_object_categories b on a.id=b.object_id where a.project_id='" . $project->getId() . "' and a.type='Milestone' and a.state>='" . $min_state . "' and a.visibility>='" . $min_visibility . "' and a.completed_on is null ";
		if ( !empty($milestone_category_id) ){
			if ($milestone_category_id>=0){
				$query .= " and b.category_id='" . $milestone_category_id . "' ";
			}
		} else {
			$query .= " and (b.category_id is null or b.category_id='0') ";
		}
		$query .= " order by " . $order_by . " " . $sort_order;
		$result = mysql_query($query, $link);
		while ( $entry = mysql_fetch_assoc($result) ){
			$resp[] = new Milestone($entry['id']);
		}
		mysql_close($link);
		return $resp;
    } // findActiveByProject
    //EOF:mod 20121108
	
    /**
     * Return completed milestones by project
     *
     * @param Project $project
     * @param integer $min_state
     * @param integer $min_visibility
     * @return array
     */
	//BOF:mod 20121108
	/*
	//EOF:mod 20121108
    function findCompletedByProject($project, $min_state = STATE_VISIBLE, $min_visibility = VISIBILITY_NORMAL, $order_by = 'name', $sort_order = '', $milestone_category_id = '') {
      return ProjectObjects::find(array(
    	'conditions' => (!empty($milestone_category_id) ? array('project_id = ? AND type = ? AND state >= ? AND visibility >= ? AND completed_on IS NOT NULL AND category_id = ?', $project->getId(), 'Milestone', $min_state, $min_visibility, $milestone_category_id) : array('project_id = ? AND type = ? AND state >= ? AND visibility >= ? AND completed_on IS NOT NULL and (category_id is null or category_id=0)', $project->getId(), 'Milestone', $min_state, $min_visibility) ),
		'order'      => $order_by,
		'sort'		 => $sort_order
		//EOF: task 03 | AD
      ));
    } // findCompletedByProject
	//BOF:mod 20121108
	*/
    function findCompletedByProject($project, $min_state = STATE_VISIBLE, $min_visibility = VISIBILITY_NORMAL, $order_by = 'name', $sort_order = '', $milestone_category_id = '') {
		$resp = array();
		$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
		mysql_select_db(DB_NAME);
		$query = "select a.id from healingcrystals_project_objects a left join healingcrystals_project_object_categories b on a.id=b.object_id where a.project_id='" . $project->getId() . "' and a.type='Milestone' and a.state>='" . $min_state . "' and a.visibility>='" . $min_visibility . "' and a.completed_on is not null ";
		if ( !empty($milestone_category_id) ){
			$query .= " and b.category_id='" . $milestone_category_id . "' ";
		} else {
			$query .= " and (b.category_id is null or b.category_id='0') ";
		}
		$query .= " order by " . $order_by . " " . $sort_order;
		
		$result = mysql_query($query, $link);
		while ( $entry = mysql_fetch_assoc($result) ){
			$resp[] = new Milestone($entry['id']);
		}
		mysql_close($link);
		return $resp;
    } // findCompletedByProject
	//EOF:mod 20121108
    
    /**
     * Find successive milestones by a given milestone
     *
     * @param Milestone $milestone
     * @param integer $min_state
     * @param integer $min_visibility
     * @return array
     */
    function findSuccessiveByMilestone($milestone, $min_state = STATE_VISIBLE, $min_visibility = VISIBILITY_NORMAL) {
      return Milestones::find(array(
        'conditions' => array('project_id = ? AND type = ? AND due_on >= ? AND state >= ? AND visibility >= ? AND id != ?', $milestone->getProjectId(), 'Milestone', $milestone->getDueOn(), $min_state, $min_visibility, $milestone->getId()),
        //BOF: task 882 | AD
        /*
        //EOF: task 882 | AD
		//'order' => 'due_on',
		//BOF: task 882 | AD
		*/
		'order' => 'name',
		//EOF: task 882 | AD
      ));
    } // findSuccessiveByMilestone
    
    // ---------------------------------------------------
    //  Portal methods
    // ---------------------------------------------------
    
    /**
     * Return all milestones for a given portal project
     *
     * @param Portal $portal
     * @param Project $project
     * @return array
     */
    function findByPortalProject($portal, $project) {
    	if($portal->getProjectPermissionValue('milestone') >= PROJECT_PERMISSION_ACCESS) {
    		return ProjectObjects::find(array(
    			'conditions' => array('project_id = ? AND type = ? AND state >= ? AND visibility >= ?', $project->getId(), 'Milestone', STATE_VISIBLE, VISIBILITY_NORMAL),
		        //BOF: task 882 | AD
		        /*
		        //EOF: task 882 | AD
				//'order' => 'due_on',
				//BOF: task 882 | AD
				*/
				'order' => 'name',
				//EOF: task 882 | AD
    		));
    	} // if
    	return null;
    } // findByPortalProject
  	
  } // Milestones

?>