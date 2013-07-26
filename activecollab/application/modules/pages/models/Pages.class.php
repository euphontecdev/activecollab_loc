<?php

  /**
   * Pages manager
   * 
   * @package activeCollab.modules.pages
   * @subpackage models
   */
  class Pages extends ProjectObjects {
    
    /**
     * Return pages that belong to a specific milestone
     *
     * @param Milestone $milestone
     * @param integer $min_state
     * @param integer $min_visibility
     * @return array
     */
    function findByMilestone($milestone, $min_state = STATE_VISIBLE, $min_visibility = VISIBILITY_NORMAL) {
      return ProjectObjects::find(array(
        'conditions' =>  array('milestone_id = ? AND type = ? AND state >= ? AND visibility >= ?', $milestone->getId(), 'Page', $min_state, $min_visibility),
        //'order' => 'updated_on DESC'
        //'order' => ($milestone->getId()=='676' ? 'name, updated_on DESC': 'updated_on DESC'), 
        'order' => 'name, updated_on DESC',
      ));
    } // findByMilestone
    
    function findUnarchivedByMilestone($milestone, $min_state = STATE_VISIBLE, $min_visibility = VISIBILITY_NORMAL) {
      return ProjectObjects::find(array(
        'conditions' =>  array("milestone_id = ? AND type = ? AND state >= ? AND visibility >= ? and (boolean_field_1 is null or boolean_field_1='0')", $milestone->getId(), 'Page', $min_state, $min_visibility),
        'order' => 'name, updated_on DESC',
      ));
    }
	
	function findByProjectWithoutMilestoneAssociation($project, $min_state = STATE_VISIBLE, $min_visibility = VISIBILITY_NORMAL){
      return ProjectObjects::find(array(
        'conditions' =>  array("project_id = ? and type = ? and state >= ? and visibility >= ? and (milestone_id='0' or milestone_id is null)", $project->getId(), 'Page', $min_state, $min_visibility),
        'order' => 'name, updated_on DESC',
      ));
	}
    
	function findUnarchivedByProjectWithoutMilestoneAssociation($project, $min_state = STATE_VISIBLE, $min_visibility = VISIBILITY_NORMAL){
      return ProjectObjects::find(array(
        'conditions' =>  array("project_id = ? and type = ? and state >= ? and visibility >= ? and (milestone_id='0' or milestone_id is null) and (boolean_field_1 is null or boolean_field_1='0')", $project->getId(), 'Page', $min_state, $min_visibility),
        'order' => 'name, updated_on DESC',
      ));
	}
    
    /**
     * Paginate pages by project
     *
     * @param Project $project
     * @param integer $page
     * @param integer $per_page
     * @param integer $min_state
     * @param integer $min_visibility
     * @return array
     */
    function paginateByProject($project, $page = 1, $per_page = 10, $min_state = STATE_VISIBLE, $min_visibility = VISIBILITY_NORMAL) {
      return ProjectObjects::paginate(array(
        'conditions' =>  array('project_id = ? AND type = ? AND state >= ? AND visibility >= ?', $project->getId(), 'Page', $min_state, $min_visibility),
        'order' => 'updated_on DESC'
      ), $page, $per_page);
    } // paginateByProject
    
    /**
     * Load pages by $category
     *
     * @param Category $category
     * @param integer $min_state
     * @param integer $min_visibility
     * @return array
     */
    function findByCategory($category, $min_state = STATE_VISIBLE, $min_visibility = VISIBILITY_NORMAL) {
      return ProjectObjects::find(array(
        'conditions' =>  array('parent_id = ? AND type = ? AND state >= ? AND visibility >= ?', $category->getId(), 'Page', $min_state, $min_visibility),
        'order' => 'ISNULL(position) ASC, position'
      ));
    } // findByCategory
    
    function findByCategories($categories, $min_state = STATE_VISIBLE, $min_visibility = VISIBILITY_NORMAL) {
		$categories_string = '';
		$project_id = 0;
		foreach($categories as $category){
			if (empty($project_id)) $project_id = $category->getProjectId();
			$categories_string .= $category->getId() . ', ';
		}
		if (!empty($categories_string)){
			$categories_string = substr($categories_string, 0, -2);
			if ($project_id==TASK_LIST_PROJECT_ID){
				$listing_new = array();
				$listing = ProjectObjects::find(array(
							//'conditions' =>  array('parent_id in (?) AND type = ? AND state >= ? AND visibility >= ?', $categories_string, 'Page', $min_state, $min_visibility),
							'conditions' =>  array('parent_id in (?) AND type = ? AND state >= ? AND visibility >= ? and completed_on is null', $categories_string, 'Page', $min_state, $min_visibility),
							'order' => 'name', 
						));
				$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
				mysql_select_db(DB_NAME);
				foreach($listing as $entry){
					$temp_name = $entry->getName();
					if (strpos($temp_name, '-')!==false){
						$temp_name = trim(substr($temp_name, 0, strpos($temp_name, '-')));
						@list($first_name, $last_name, ) = explode(' ', $temp_name);
						
						$query = mysql_query("select * from healingcrystals_users where first_name='" . mysql_real_escape_string($first_name) . "' and last_name='" . mysql_real_escape_string($last_name) . "'");
						if (mysql_num_rows($query)){
							$listing_new[] = $entry;
						}
					}
				}
				mysql_close($link);
				return $listing_new;
			} else {
				return ProjectObjects::find(array(
					//'conditions' =>  array('parent_id in (?) AND type = ? AND state >= ? AND visibility >= ?', $categories_string, 'Page', $min_state, $min_visibility),
					'conditions' =>  array('parent_id in (?) AND type = ? AND state >= ? AND visibility >= ? and completed_on is null', $categories_string, 'Page', $min_state, $min_visibility),
					'order' => 'name', 
				));
			}
		} else{
			return array();
		}
    }

	//BOF:mod 20121108
    function findByCategoryNew($project_id, $category_id, $min_state = STATE_VISIBLE, $min_visibility = VISIBILITY_NORMAL) {
		$resp = array();
		$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
		mysql_select_db(DB_NAME);
		
		if ( (int)$category_id>0 ){
			$query = "select a.id from healingcrystals_project_objects a inner join healingcrystals_project_object_categories b on a.id=b.object_id where a.project_id='" . $project_id . "' and a.type='Page' and a.state>='" . $min_state . "' and a.visibility>='" . $min_visibility . "' ";
			$query .= " and b.category_id='" . $category_id . "' ";
		} else {
			$query = "select a.id from healingcrystals_project_objects a left join healingcrystals_project_object_categories b on a.id=b.object_id where a.project_id='" . $project_id . "' and a.type='Page' and a.state>='" . $min_state . "' and a.visibility>='" . $min_visibility . "' ";
			$query .= " and (b.category_id is null or b.category_id='0') ";
		}
		$query .= " order by updated_on DESC";
		mysql_query("insert into testing (content, date_added) values ('" . mysql_real_escape_string($query) . "', now())");
		$result = mysql_query($query, $link);
		while ( $entry = mysql_fetch_assoc($result) ){
			$resp[] = new Page($entry['id']);
		}
		mysql_close($link);
		return $resp;
    } // findByCategory
	//EOF:mod 20121108
	
    /**
     * Return subpages
     *
     * @param Page $page
     * @param integer $min_state
     * @param integer $min_visibility
     * @return array
     */
    function findSubpages($page, $min_state = STATE_VISIBLE, $min_visibility = VISIBILITY_NORMAL) {
      return Pages::find(array(
        'conditions' => array('parent_id = ? AND type = ? AND state >= ? AND visibility >= ?', $page->getId(), 'Page', $min_state, $min_visibility, false),
        'order' => 'ISNULL(position) ASC, position'
      ));
    } // findSubpages
  
  } // Pages

?>