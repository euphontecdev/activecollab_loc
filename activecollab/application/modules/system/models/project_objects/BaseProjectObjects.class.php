<?php

  /**
   * BaseProjectObjects class
   */
  class BaseProjectObjects extends DataManager {
  
    /**
     * Do a SELECT query over database with specified arguments
     * 
     * This function can return single instance or array of instances that match 
     * requirements provided in $arguments associative array
     *
     * @param array $arguments Array of query arguments. Fields:
     * 
     *  - one        - select first row
     *  - conditions - additional conditions
     *  - order      - order by string
     *  - offset     - limit offset, valid only if limit is present
     *  - limit      - number of rows that need to be returned
     * 
     * @return mixed
     * @throws DBQueryError
     */
    function find($arguments = null) {
      return DataManager::find($arguments, TABLE_PREFIX . 'project_objects', 'ProjectObject');
    } // find
    
    /**
     * Return array of objects that match specific SQL
     *
     * @param string $sql
     * @param array $arguments
     * @param boolean $one
     * @return mixed
     */
    function findBySQL($sql, $arguments = null, $one = false) {
      return DataManager::findBySQL($sql, $arguments, $one, TABLE_PREFIX . 'project_objects', 'ProjectObject');
    } // findBySQL
    
    /**
     * Return object by ID
     *
     * @param mixed $id
     * @return ProjectObject
     */
    function findById($id) {
      return DataManager::findById($id, TABLE_PREFIX . 'project_objects', 'ProjectObject');
    } // findById
    
    /**
     * Return number of rows in this table
     *
     * @param string $conditions Query conditions
     * @return integer
     * @throws DBQueryError
     */
    function count($conditions = null) {
      return DataManager::count($conditions, TABLE_PREFIX . 'project_objects');
    } // count
    
    /**
     * Update table
     * 
     * $updates is associative array where key is field name and value is new 
     * value
     *
     * @param array $updates
     * @param string $conditions
     * @return boolean
     * @throws DBQueryError
     */
    function update($updates, $conditions = null) {
      return DataManager::update($updates, $conditions, TABLE_PREFIX . 'project_objects');
    } // update
    
    /**
     * Delete all rows that match given conditions
     *
     * @param string $conditions Query conditions
     * @param string $table_name
     * @return boolean
     * @throws DBQueryError
     */
    function delete($conditions = null) {
      return DataManager::delete($conditions, TABLE_PREFIX . 'project_objects');
    } // delete
    
    /**
     * Return paginated result
     * 
     * This function will return paginated result as array. First element of 
     * returned array is array of items that match the request. Second parameter 
     * is Pager class instance that holds pagination data (total pages, current 
     * and next page and so on)
     *
     * @param array $arguments
     * @param integer $page
     * @param integer $per_page
     * @return array
     * @throws DBQueryError
     */
    function paginate($arguments = null, $page = 1, $per_page = 10) {
      return DataManager::paginate($arguments, $page, $per_page, TABLE_PREFIX . 'project_objects', 'ProjectObject');
    } // paginate
    
    //BOF: mod
    function getItemsByDepartment($project_id, $project_object){
    	$resp = array();
    	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
    	mysql_select_db(DB_NAME, $link);
    	$query = "select id, category_name as text from healingcrystals_project_milestone_categories where project_id='" . $project_id . "' order by category_name";
    	$result = mysql_query($query, $link);
    	while($info = mysql_fetch_assoc($result)){
    		$resp[] = array('id' => $info['id'], 'text' => $info['text'], 'items' => array());
    		if (instance_of($project_object, 'Checklist')){
    			$query_1 = "select a.id from healingcrystals_project_objects a inner join healingcrystals_project_object_categories b on a.id=b.object_id where a.type='Checklist' and a.project_id='" . $project_id . "' and a.visibility='1' and a.completed_on is null and b.category_id='" . $info['id'] . "' order by a.name";
    			$result_1 = mysql_query($query_1, $link);
    			while($info_1 = mysql_fetch_assoc($result_1)){
    				$item = new Checklist($info_1['id']);;
    				$resp[count($resp)-1]['items'][] = $item;
    			}
    		} elseif (instance_of($project_object, 'Discussion')){
    			$query_1 = "select a.id from healingcrystals_project_objects a inner join healingcrystals_project_object_categories b on a.id=b.object_id where a.type='Discussion' and a.project_id='" . $project_id . "' and a.visibility='1' and a.completed_on is null and b.category_id='" . $info['id'] . "' order by a.name";
    			$result_1 = mysql_query($query_1, $link);
    			while($info_1 = mysql_fetch_assoc($result_1)){
    				$item = new Discussion($info_1['id']);;
    				$resp[count($resp)-1]['items'][] = $item;
    			}
    		} elseif (instance_of($project_object, 'File')){
    			$query_1 = "select a.id from healingcrystals_project_objects a inner join healingcrystals_project_object_categories b on a.id=b.object_id where a.type='File' and a.project_id='" . $project_id . "' and a.visibility='1' and a.completed_on is null and b.category_id='" . $info['id'] . "' order by a.created_on desc";
    			$result_1 = mysql_query($query_1, $link);
    			while($info_1 = mysql_fetch_assoc($result_1)){
    				$item = new File($info_1['id']);;
    				$resp[count($resp)-1]['items'][] = $item;
    			}
    		}
    	}
    	$resp[] = array('id' => '0', 'text' => 'Uncategorized', 'items' => array());
    	if (instance_of($project_object, 'Checklist')){
    		$query_1 = "select a.id from healingcrystals_project_objects a where a.type='Checklist' and a.project_id='" . $project_id . "' and a.visibility='1' and a.completed_on is null and not exists (select * from healingcrystals_project_object_categories b where b.object_id=a.id) order by a.name";
			$result_1 = mysql_query($query_1, $link);
			while($info_1 = mysql_fetch_assoc($result_1)){
				$item = new Checklist($info_1['id']);;
				$resp[count($resp)-1]['items'][] = $item;
			}
    	} elseif (instance_of($project_object, 'Discussion')){
    		$query_1 = "select a.id from healingcrystals_project_objects a where a.type='Discussion' and a.project_id='" . $project_id . "' and a.visibility='1' and a.completed_on is null and not exists (select * from healingcrystals_project_object_categories b where b.object_id=a.id) order by a.name";
			$result_1 = mysql_query($query_1, $link);
			while($info_1 = mysql_fetch_assoc($result_1)){
				$item = new Discussion($info_1['id']);;
				$resp[count($resp)-1]['items'][] = $item;
			}
    	} elseif (instance_of($project_object, 'File')){
    		$query_1 = "select a.id from healingcrystals_project_objects a where a.type='File' and a.project_id='" . $project_id . "' and a.visibility='1' and a.completed_on is null and not exists (select * from healingcrystals_project_object_categories b where b.object_id=a.id) order by a.created_on desc";
			$result_1 = mysql_query($query_1, $link);
			while($info_1 = mysql_fetch_assoc($result_1)){
				$item = new File($info_1['id']);;
				$resp[count($resp)-1]['items'][] = $item;
			}
    	}
		mysql_close($link);
    	return $resp;
    }
    
    function getItemsWithPagination($project_id, $project_object, $department_id, $page, $per_page){
    	$resp = array();
    	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
    	mysql_select_db(DB_NAME, $link);
    	if (instance_of($project_object, 'File')){
    		if (empty($department_id)){
    			$query_1 = "select a.id from healingcrystals_project_objects a where a.type='File' and a.project_id='" . $project_id . "' and a.visibility='1' and a.completed_on is null and not exists (select * from healingcrystals_project_object_categories b where b.object_id=a.id) order by a.created_on desc";
    		} else {
    			$query_1 = "select a.id from healingcrystals_project_objects a inner join healingcrystals_project_object_categories b on a.id=b.object_id where a.type='File' and a.project_id='" . $project_id . "' and a.visibility='1' and a.completed_on is null and b.category_id='" . $department_id . "' order by a.created_on desc";
    		}
    		if (!empty($query_1)){
    			$query_1 .= " limit " . (($page - 1) * $per_page) . ", " . $page;
	    		$result_1 = mysql_query($query_1, $link);
	    		while($info_1 = mysql_fetch_assoc($result_1)){
	    			$item = new File($info_1['id']);;
	    			$resp[count($resp)-1]['items'][] = $item;
	    		}
    		}
    	}
    	return $resp;
    }
    
    //EOF: mod
  
  }

?>