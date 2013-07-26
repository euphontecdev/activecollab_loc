<?php

  // Use project controller
  use_controller('project', SYSTEM_MODULE);

  /**
   * Project people controller
   * 
   * This controller implements project people and permission relateed pages and 
   * actions
   *
   * @package activeCollab.modules.system
   * @subpackage controllers
   */
  class ProjectPeopleController extends ProjectController {
    
    /**
     * Controller name
     * 
     * @var string
     */
    var $controller_name = 'project_people';
    
    /**
     * Actions available as API methods
     *
     * @var array
     */
    var $api_actions = array('index', 'add_people', 'user_permissions', 'remove_user');
    //BOF: task 01 | AD
    var $order_by = 'due_on';
    var $sort_order = 'asc';
  	//EOF: task 01 | AD
    /**
     * Construct project_people controller
     *
     * @param Request $request
     * @return ProjectPeopleController
     */
    function __construct($request) {
      parent::__construct($request);
      
      $this->wireframe->addBreadCrumb(lang('People'), $this->active_project->getPeopleUrl());
      
      if($this->active_project->canEdit($this->logged_user)) {
        $this->wireframe->addPageAction(lang('Add People'), $this->active_project->getAddPeopleUrl());
      } // if
      //$this->wireframe->addPageAction(lang('Milestones'), assemble_url('project_user_assigned_milestones', array('project_id' => $this->active_project->getId(), 'user_id' => $this->logged_user->getId())));
      //BOF: task 01 | AD
      $order_by_val = $_GET['order_by'];
      if (!empty($order_by_val)){
      	$this->order_by = $order_by_val;
      }
      $sort_order_val = $_GET['sort_order'];
      if (!empty($sort_order_val)){
      	$this->sort_order = $sort_order_val;
      }
      //EOF: task 01 | AD
      $this->smarty->assign('page_tab', 'people');
    } // __construct
    
    /**
     * Show people page
     *
     * @param void
     * @return null
     */
    function index() {
      if($this->active_project->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      $users = $this->active_project->getUsers();
      
      // API
      if($this->request->isApiCall()) {
        $project_users_data = array();
        
        if(is_foreachable($users)) {
          foreach($users as $user) {
            $user_data = array(
              'user_id' => $user->getId(),
              'role' => null,
              'permissions' => array(),
              'permalink' => $user->getViewUrl(), 
            );
            
            $permissions = array_keys(Permissions::findProject());
            if($user->isAdministrator()) {
              $user_data['role'] = 'administrator';
            } elseif($user->isProjectManager()) {
              $user_data['role'] = 'project-manager';
            } elseif($user->isProjectLeader($this->active_project)) {
              $user_data['role'] = 'project-leader';
            } // if
            
            if($user_data['role'] === null) {
              $project_role = $user->getProjectRole($this->active_project);
              if(instance_of($project_role, 'Role')) {
                $user_data['role'] = $project_role->getId();
              } else {
                $user_data['role'] = 'custom';
              } // if
              
              foreach($permissions as $permission) {
                $user_data['permissions'][$permission] = (integer) $user->getProjectPermission($permission, $this->active_project);
              } // foreach
            } else {
              foreach($permissions as $permission) {
                $user_data['permissions'][$permission] = PROJECT_PERMISSION_MANAGE;
              } // foreach
            } // if
            
            $project_users_data[] = $user_data;
          } // foreach
        } // if
        
        $this->serveData($project_users_data, 'project_users');
        
      // Regular interface
      } else {
        if(is_foreachable($users)) {
          $people = array();
          $grouped_users = array();
          
          //BOF:mod 20110712 ticketid237
          $company_by_custom_sort = array();
          foreach($users as $user) {
              $company_id = $user->getCompanyId();
              if (!in_array($company_id, $company_by_custom_sort)){
                  $company_by_custom_sort[] = $company_id;
              }
          }
          asort($company_by_custom_sort);
          foreach($company_by_custom_sort as $company_id){
              $people[$company_id] = array('users' => null, 'company' => null,);
          }
          //EOF:mod 20110712 ticketid237
          
          foreach($users as $user) {
            $company_id = $user->getCompanyId();
            if(!isset($people[$company_id])) {
              $people[$company_id] = array(
                'users' => null,
                'company' => null,
              );
            } // if
            $people[$company_id]['users'][] = $user;
          } // foreach
          
          $companies = Companies::findByIds(array_keys($people));
          foreach($companies as $company) {
            $people[$company->getId()]['company'] = $company;
          } // foreach
          
          $this->smarty->assign('people', $people);
        } else {
          $this->smarty->assign('people', null);
        } // if
      } // if
      
    } // index
    
    /**
     * Add people to the project
     *
     * @param void
     * @return null
     */
    function add_people() {
      if(!$this->active_project->canEdit($this->logged_user)) {
        $this->httpError(HTTP_ERR_FORBIDDEN);
      } // if
      
      $project_users = $this->active_project->getUsers();
      if(is_foreachable($project_users)) {
        $exclude_users = objects_array_extract($project_users, 'getId');
      } else {
        $exclude_users = null;
      } // if
      
      $this->smarty->assign(array(
        'exclude_users' => $exclude_users,
      ));
      
      if($this->request->isSubmitted()) {
        $user_ids = $this->request->post('users');
        if(!is_foreachable($user_ids)) {
          flash_error('No users selected');
          $this->redirectToUrl($this->active_project->getViewUrl());
        } // if
        
        $users = Users::findByIds($user_ids);
        
        $project_permissions = $this->request->post('project_permissions');
        
        $role = null;
        $role_id = (integer) array_var($project_permissions, 'role_id');
        
        if($role_id) {
          $role = Roles::findById($role_id);
        } // if
        
        if(instance_of($role, 'Role') && $role->getType() == ROLE_TYPE_PROJECT) {
          $permissions = null;
        } else {
          $permissions = array_var($project_permissions, 'permissions');
          if(!is_array($permissions)) {
            $permissions = null;
          } // if
        } // if
        
        if(is_foreachable($users)) {
          db_begin_work();
          
          $added = array();
          foreach($users as $user) {
            $add = $this->active_project->addUser($user, $role, $permissions);
            if($add && !is_error($add)) {
              $added[] = $user->getDisplayName();
            } else {
              db_rollback();
              
              flash_error('Failed to add ":user" to ":project" project', array('user' => $user->getDisplayName(), 'project' => $this->active_project->getName()));
              $this->redirectToUrl($this->active_project->getAddPeopleUrl());
            } // if
          } // foreach
          
          db_commit();
          
          if($this->request->isApiCall()) {
            $this->httpOk();
          } else {
            require_once SMARTY_PATH . '/plugins/function.join.php';
            
            flash_success(':users added to :project project', array('users' => smarty_function_join(array('items' => $added)), 'project' => $this->active_project->getName()));
            $this->redirectToUrl($this->active_project->getPeopleUrl());
          } // if
        } // if
      } else {
        if($this->request->isApiCall()) {
          $this->httpError(HTTP_ERR_BAD_REQUEST);
        } // if
      } // if
    } // add_people
    
    /**
     * Show and process user permissions page
     *
     * @param void
     * @return null
     */
    function user_permissions() {
      if(!$this->active_project->canEdit($this->logged_user)) {
        $this->httpError(HTTP_ERR_FORBIDDEN);
      } // if
      
      $user = Users::findById($this->request->getId('user_id'));
      if(!instance_of($user, 'User')) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      if($user->isProjectManager() || $user->isProjectLeader($this->active_project)) {
        flash_error(':user has all permissions in this project', array('user' => $user->getDisplayName()));
        $this->redirectToReferer($this->active_project->getPeopleUrl());
      } // if
      
      $project_user = ProjectUsers::findById(array(
        'user_id'    => $user->getId(),
        'project_id' => $this->active_project->getId(),
      ));
      
      if(!instance_of($project_user, 'ProjectUser')) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      if(!$this->logged_user->canChangeProjectPermissions($user, $this->active_project)) {
        $this->httpError(HTTP_ERR_FORBIDDEN);
      } // if
      
      $this->smarty->assign(array(
        'active_user' => $user,
        'project_user' => $project_user,
      ));
      
      if($this->request->isSubmitted()) {
        $project_permissions = $this->request->post('project_permissions');
        
        $role = null;
        $role_id = (integer) array_var($project_permissions, 'role_id');
        if($role_id) {
          $role = Roles::findById($role_id);
        } // if
        
        if(instance_of($role, 'Role') && $role->getType() == ROLE_TYPE_PROJECT) {
          $permissions = null;
        } else {
          $role = null;
          $permissions = array_var($project_permissions, 'permissions');
          if(!is_array($permissions)) {
            $permissions = null;
          } // if
        } // if
        
        $update = $this->active_project->updateUserPermissions($user, $role, $permissions);
        if($update && !is_error($update)) {
          if($this->request->isApiCall()) {
            $this->httpOk();
          } else {
            flash_success('Permissions have been updated successfully');
          } // if
        } else {
          if($this->request->isApiCall()) {
            $this->serveData($update);
          } else {
            flash_error('Failed to update permissions');
          } // if
        } // if
        
        $this->redirectToUrl($this->active_project->getPeopleUrl());
      } else {
        if($this->request->isApiCall()) {
          $this->httpError(HTTP_ERR_BAD_REQUEST);
        } // if
      } // if
    } // user_permission
    
    /**
     * Remove user from this project
     *
     * @param void
     * @return null
     */
    function remove_user() {
      if($this->request->isSubmitted()) {
        $user = Users::findById($this->request->getId('user_id'));
        if(!instance_of($user, 'User')) {
          $this->httpError(HTTP_ERR_NOT_FOUND);
        } // if
        
        if(!$this->logged_user->canRemoveFromProject($user, $this->active_project)) {
          $this->httpError(HTTP_ERR_FORBIDDEN);
        } // if
        
        $remove = $this->active_project->removeUser($user);
        if($remove && !is_error($remove)) {
          if($this->request->isApiCall()) {
            $this->httpOk();
          } else {
            flash_success(':user has been removed from :project project', array('user' => $user->getDisplayName(), 'project' => $this->active_project->getName()));
            $this->redirectToReferer($this->active_project->getPeopleUrl());
          } // if
        } else {
          if($this->request->isApiCall()) {
            $this->serveData($remove);
          } else {
            flash_error('Failed to remove :user from :project project', array('user' => $user->getDisplayName(), 'project' => $this->active_project->getName()));
            $this->redirectToReferer($this->active_project->getPeopleUrl());
          } // if
        } // if
      } else {
        $this->httpError(HTTP_ERR_BAD_REQUEST);
      } // if
    } // remove_user
    
    //BOF: task 01 | AD
    function view_user_allocations(){
    	$selected_category_id = $_GET['category_id'];
    	$selected_category_name = 'All';
    	$page_view = $_GET['page_view'];// values: all, priority, ownership
    	if (empty($page_view)){ 
    		$page_view = 'all';
    	}
		$user = Users::findById($this->request->getId('user_id'));
      	$project_user = ProjectUsers::findById(array(
        	'user_id'    => $user->getId(),
        	'project_id' => $this->active_project->getId(),
      	));
      	$project_id = $this->active_project->getId();
      	$user_id = $user->getId();
      	
      	$allocations = array();
      	
      	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
      	mysql_select_db(DB_NAME, $link);
      	$query = "select distinct d.category_id as `id`, c.category_name as `name`  
      			 from healingcrystals_assignments a 
      			 inner join healingcrystals_project_objects b on (a.object_id=b.id and b.type='Milestone')
				 inner join healingcrystals_project_object_categories d on b.id=d.object_id 
      			 inner join healingcrystals_project_milestone_categories c on d.category_id=c.id 
      			 where a.user_id='" . $user_id . "' and 
				 b.project_id='" . $project_id . "' and 
				 b.completed_on is null " . 
				 ($page_view=='ownership' ? " and a.is_owner='1' " : "") . 
				 " group by b.category_id, c.category_name order by c.category_name";
		$result = mysql_query($query);
		while ($category = mysql_fetch_assoc($result)){
			$allocations[] = array('category_name' => $category['name'], 
									'category_id' => $category['id'], 
									'category_url' => assemble_url('project_user_view_allocations', array('project_id' => $project_id, 'user_id'=>$user_id, 'category_id' => $category['id'])));
			if (!empty($selected_category_id) && $category['id']!=$selected_category_id){
				continue;
			}
			$selected_category_name = (!empty($selected_category_name) && $selected_category_id==$category['id'] ? $category['name'] : $selected_category_name);

			$query_1 = "select b.*, a.is_owner, d.category_name 
		  		 		from healingcrystals_assignments a 
				 		inner join healingcrystals_project_objects b on (a.object_id=b.id and b.type='Milestone') 
				 		inner join healingcrystals_project_object_categories c on b.id=c.object_id 
				 		inner join healingcrystals_project_milestone_categories d on c.category_id=d.id 
				 		where a.user_id='" . $user_id . "' and 
						b.project_id='" . $project_id . "' and 
						c.category_id='" . $category['id'] . "' and 
						b.completed_on is null  ";
			if ($page_view=='all'){
				$query_1 .= " order by b." . $this->order_by . " " . $this->sort_order;
			} elseif($page_view=='priority'){
				$query_1 .= " order by b.priority desc, b.name";
			}  elseif($page_view=='ownership'){
				$query_1 .= " and a.is_owner='1' order by d.category_name, b.name";
			}
			
			$result_1 = mysql_query($query_1);
			while ($milestone = mysql_fetch_assoc($result_1)){
          		$item_class = array_var($milestone, 'type');
          		$item = new $item_class();
          		$item->loadFromRow($milestone);
          		//$allocations[count($allocations)-1]['milestones'][] = $item;
          		$allocations[count($allocations)-1]['milestones'][] = array('obj' => $item, 
																			'logged_user_is_responsible' => (!empty($milestone['is_owner']) ? true : false), 
																			'department' => $milestone['category_name']);
          		unset($item);
          		
				
				$query_2 = "select b.*, a.is_owner, d.category_name 
			  		 		from healingcrystals_assignments a 
					 		inner join healingcrystals_project_objects b on (a.object_id=b.id and b.type='Ticket') 
				 			left join healingcrystals_project_object_categories c on b.id=c.object_id 
				 			inner join healingcrystals_project_milestone_categories d on c.category_id=d.id 
					 		where a.user_id='" . $user_id . "' and 
							b.project_id='" . $project_id . "' and 
							b.milestone_id = '" . $milestone['id'] . "' and 
							b.completed_on is null";
				if ($page_view=='all'){
					$query_2 .= " order by b." . $this->order_by . " " . $this->sort_order;
				} elseif($page_view=='priority'){
					$query_2 .= " order by b.priority desc, b.name";
				}  elseif($page_view=='ownership'){
					$query_2 .= " and a.is_owner='1' order by d.category_name, b.name";
				}
				$result_2 = mysql_query($query_2);
				if (mysql_num_rows($result_2)){
					$index = count($allocations[count($allocations)-1]['milestones']) - 1;
					$ticket_index = -1;
					$temp = array();
					while ($ticket = mysql_fetch_assoc($result_2)){
						if (!array_key_exists((string)$ticket['id'], $temp)){
			          		$ticket_index++;
			          		$temp[(string)$ticket['id']] = $ticket_index;
			          		
			          		$item_class = array_var($ticket, 'type');
			          		$item = new $item_class();
			          		$item->loadFromRow($ticket);
			          		$allocations[count($allocations)-1]['milestones'][$index]['tickets'][$ticket_index] = array('obj' => $item, 
							  																			   'logged_user_is_responsible' => $ticket['is_owner'], 
																										   'department' => (empty($ticket['category_name']) ? '--' : $ticket['category_name'])
																										   );
			          		unset($item);
						} else {
			          		$allocations[count($allocations)-1]['milestones'][$index]['tickets'][$ticket_index]['department'] .= ' / ' . (empty($ticket['category_name']) ? '--' : $ticket['category_name']);
			          		continue;
						}
		          		
		          		$query_3 = "select b.*  
			  		 				from healingcrystals_assignments a 
					 				inner join healingcrystals_project_objects b on (a.object_id=b.id and b.type='Task') 
					 				where a.user_id='" . $user_id . "' and 
									b.project_id='" . $project_id . "' and 
									b.milestone_id = '" . $milestone['id'] . "' and 
									b.parent_id='" . $ticket['id'] . "' and 
									b.parent_type='Ticket' and   
									b.completed_on is null";
						if ($page_view=='all'){
							$query_3 .= " order by b." . ($this->order_by=='name' ? 'body' : 'name') . " " . $this->sort_order;
						} elseif($page_view=='priority'){
							$query_3 .= " order by b.priority desc, b.body";
						}  elseif($page_view=='ownership'){
							$query_3 .= " and a.is_owner='1' order by b.body";
						}
					 	$result_3 = mysql_query($query_3);
					 	if (mysql_num_rows($result_3)){
					 		$index_1 = count($allocations[count($allocations)-1]['milestones'][$index]['tickets']) - 1;
					 		while($task = mysql_fetch_assoc($result_3)){
				          		$item_class = array_var($task, 'type');
				          		$item = new $item_class();
				          		$item->loadFromRow($task);
				          		$allocations[count($allocations)-1]['milestones'][$index]['tickets'][$index_1]['tasks'][] = array('obj' => $item);
				          		unset($item);
					 		}
					 	}
					}
				}
			}
		}
		$query_1 = "select b.*, a.is_owner 
	  		 		from healingcrystals_assignments a 
			 		inner join healingcrystals_project_objects b on (a.object_id=b.id and b.type='Milestone') 
			 		where (not exists (select c.* from healingcrystals_project_object_categories c where b.id=c.object_id and c.category_id>0)) and   
					a.user_id='" . $user_id . "' and 
					b.project_id='" . $project_id . "' 
					b.completed_on is null";
		if ($page_view=='all'){
			$query_1 .= " order by b." . $this->order_by . " " . $this->sort_order;
		} elseif($page_view=='priority'){
			$query_1 .= " and a.is_owner='1' order by b.priority desc, b.name";
		}  elseif($page_view=='ownership'){
			$query_1 .= " order by b.name";
		}
		$result_1 = mysql_query($query_1);
		if (mysql_num_rows($result_1)){
			$allocations[] = array('category_name' => 'Uncategorized Milestones', 
									'category_id'=> '-1',
									'category_url' => assemble_url('project_user_view_allocations', array('project_id' => $project_id, 'user_id'=>$user_id, 'category_id' => '-1'))
									);
			if (empty($selected_category_id) || $selected_category_id==-1){
				$selected_category_name = ($selected_category_id==-1 ? 'Uncategorized' : $selected_category_name);
				while ($milestone = mysql_fetch_assoc($result_1)){
	          		$item_class = array_var($milestone, 'type');
	          		$item = new $item_class();
	          		$item->loadFromRow($milestone);
					//$allocations[count($allocations)-1]['milestones'][] = $item;
					$allocations[count($allocations)-1]['milestones'][] = array('obj' => $item, 
																				'logged_user_is_responsible' => (!empty($milestone['is_owner']) ? true : false), 
																				'department' => '--');
					unset($item);
					
					$query_2 = "select b.*, a.is_owner, d.category_name   
				  		 		from healingcrystals_assignments a 
						 		inner join healingcrystals_project_objects b on (a.object_id=b.id and b.type='Ticket') 
					 			left join healingcrystals_project_object_categories c on b.id=c.object_id 
					 			inner join healingcrystals_project_milestone_categories d on c.category_id=d.id 
						 		where a.user_id='" . $user_id . "' and 
								b.project_id='" . $project_id . "' and 
								b.milestone_id = '" . $milestone['id'] . "' and 
								b.completed_on is null";
					if ($page_view=='all'){
						$query_1 .= " order by b." . $this->order_by . " " . $this->sort_order;
					} elseif($page_view=='priority'){
						$query_1 .= " order by b.priority desc, b.name";
					}  elseif($page_view=='ownership'){
						$query_1 .= " and a.is_owner='1' order by b.name";
					}
					$result_2 = mysql_query($query_2);
					if (mysql_num_rows($result_2)){
						$index = count($allocations[count($allocations)-1]['milestones']) - 1;
						$ticket_index = -1;
						$temp = array();
						while ($ticket = mysql_fetch_assoc($result_2)){
							if (!array_key_exists((string)$ticket['id'], $temp)){
				          		$ticket_index++;
				          		$temp[(string)$ticket['id']] = $ticket_index;
				          		
				          		$item_class = array_var($ticket, 'type');
				          		$item = new $item_class();
				          		$item->loadFromRow($ticket);
				          		$allocations[count($allocations)-1]['milestones'][$index]['tickets'][] = array('obj' => $item, 
							  																			   'logged_user_is_responsible' => $ticket['is_owner'], 
																										   'department' => (empty($ticket['category_name']) ? '--' : $ticket['category_name'])
								  );
				          		unset($item);
							} else {
				          		$allocations[count($allocations)-1]['milestones'][$index]['tickets'][$ticket_index]['department'] .= ' / ' . (empty($ticket['category_name']) ? '--' : $ticket['category_name']);
				          		continue;
							}
			          		
			          		$query_3 = "select b.*  
				  		 				from healingcrystals_assignments a 
						 				inner join healingcrystals_project_objects b on (a.object_id=b.id and b.type='Task') 
						 				where a.user_id='" . $user_id . "' and 
										b.project_id='" . $project_id . "' and 
										b.milestone_id = '" . $milestone['id'] . "' and 
										b.parent_id='" . $ticket['id'] . "' and 
										b.parent_type='Ticket' and   
										b.completed_on is null";
							if ($page_view=='all'){
								$query_1 .= " order by b." . ($this->order_by=='name' ? 'body' : 'name') . " " . $this->sort_order;
							} elseif($page_view=='priority'){
								$query_1 .= " order by b.priority desc, b.body";
							}  elseif($page_view=='ownership'){
								$query_1 .= " and a.is_owner='1' order by b.body";
							}
						 	$result_3 = mysql_query($query_3);
						 	if (mysql_num_rows($result_3)){
						 		$index_1 = count($allocations[count($allocations)-1]['milestones'][$index]['tickets']) - 1;
						 		while($task = mysql_fetch_assoc($result_3)){
					          		$item_class = array_var($task, 'type');
					          		$item = new $item_class();
					          		$item->loadFromRow($task);
					          		$allocations[count($allocations)-1]['milestones'][$index]['tickets'][$index_1]['tasks'][] = array('obj' => $item);
					          		unset($item);
						 		}
						 	}
						}
					}
				}
			}
		}

      	mysql_close($link);
		        	
      	$this->smarty->assign(array(
        	'active_user' => $user,
        	'project_user' => $project_user,
        	'allocations' => $allocations,
        	'manage_milestone_cat_url' => $this->active_project->getManageMilestoneCategoriesUrl(), 
        	'all_milestones_url' => assemble_url('project_user_view_allocations', array('project_id' => $project_id, 'user_id'=>$user_id)), 
        	'selected_category_id' => $selected_category_id, 
        	'selected_category_name' => 'Milestone: ' . $selected_category_name, 
        	'page_view' => $page_view
      	));
    }
    //EOF: task 01 | AD
    
    function view_assigned_milestones(){
    	$page_view = $_GET['page_view'];
    	$order_by = $_GET['order_by'];
    	$sort_order = $_GET['sort_order'];
		$user = Users::findById($this->request->getId('user_id'));
      	$project_user = ProjectUsers::findById(array(
        	'user_id'    => $user->getId(),
        	'project_id' => $this->active_project->getId(),
      	));
      	$project_id = $this->active_project->getId();
      	$user_id = $user->getId();
      	
      	
      	$milestones = array();
      	$temp = array();
      	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
      	mysql_select_db(DB_NAME, $link);
      	
		$query = "select b.*, a.is_owner, d.category_name 
	  		 	 from healingcrystals_assignments a 
			 	 inner join healingcrystals_project_objects b on (a.object_id=b.id and b.type='Milestone') 
			 	 left outer join healingcrystals_project_object_categories c on b.id=c.object_id 
			 	 left outer join healingcrystals_project_milestone_categories d on c.category_id=d.id 
			 	 where a.user_id='" . $user_id . "' and " .
				 //b.project_id='" . $project_id . "' and " .
				 ($page_view ? "" : " a.is_owner='1' and ") . 
				 " b.project_id='" . $this->active_project->getId() . "' and 
				  b.completed_on is null and b.state='3' and b.visibility='1'";
		if (!empty($order_by)){
			switch ($order_by){
				case 'priority':
					$query .= " order by b.priority $sort_order ";
					break;
				case 'name':
					$query .= " order by b.name $sort_order ";
					break;
				case 'department':
					$query .= " order by d.category_name $sort_order ";
					break;
				default:
					$query .= " order by b.priority desc, b.name";
			}
		} else {
			$query .= " order by b.priority desc, b.name";
		}
		$result = mysql_query($query);
		while($milestone = mysql_fetch_assoc($result)){
			$item_class = array_var($milestone, 'type');
			$item = new $item_class();
			$item->loadFromRow($milestone);
			
			if (!in_array($milestone['id'], $temp)){
				$milestones[] = array('obj' => $item, 'id' => $milestone['id'], 'logged_user_is_responsible' => $milestone['is_owner'], 'department' => array($milestone['category_name']));
				$temp[] = $milestone['id'];
			} else {
				$milestones[array_search($milestone['id'], $temp)]['department'][] = $milestone['category_name'];
			}
			
			
		}
      	
      	mysql_close($link);
      	
      	$this->smarty->assign(array(
        	'active_user' => $user,
        	'project_user' => $project_user,
        	'milestones' => $milestones, 
        	'page_view' => $page_view
      	));
    }
    
    function view_today_page(){
        if($this->request->get('async')) {
            $object_id = $this->request->post('object_id');
            $priority = $this->request->post('priority');
            $link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
            mysql_select_db(DB_NAME);
            mysql_query("update healingcrystals_project_objects set priority='" . $priority . "' where id='" . $object_id . "'");
            mysql_close($link);
            exit();
        }
    	$selected_project   = $_GET['selected_project'];
    	$order_by           = $_GET['order_by'];
    	$sort_order         = $_GET['sort_order'];
    	$tab                = empty($_GET['tab']) ? 'tab01' : $_GET['tab'];
        
        $user = Users::findById($this->request->getId('user_id'));
      	$project_user = ProjectUsers::findById(array(
        	'user_id'    => $user->getId(),
        	'project_id' => $this->active_project->getId(),
      	));
        
        if ($tab == 'tab01'){
            $entries = $this->logged_user->getOwnedTickets($user->getId(), $selected_project, $order_by, $sort_order);
            $this->smarty->assign(array(
	        	'active_user'       => $user,
	        	'project_user'      => $project_user,
	        	'entries'           => $entries,
                        'user_projects'     => $user->getActiveProjects(), 
			'selected_project'  => $selected_project, 
	        	'tab'               => $tab, ));
        }elseif ($tab == 'tab03'){
            $content = $this->logged_user->getHomeTabContent($user_id);
            $this->smarty->assign(array(
                                    'active_user'       => $user,
                                    'project_user'      => $project_user,
                                    'page_view'         => $page_view, 
                                    'tab'               => $tab, 
                                    'home_tab_content'  => $content));
        }elseif ($tab == 'tab04'){
            $entries = $this->logged_user->getSubscribedTickets($user->getId(), $selected_project, $order_by, $sort_order);
            $this->smarty->assign(array(
	        	'active_user'       => $user,
	        	'project_user'      => $project_user,
	        	'entries'           => $entries,
                        'user_projects'     => $user->getActiveProjects(), 
			'selected_project'  => $selected_project, 
	        	'tab'               => $tab, ));
        }elseif ($tab=='tab02'){
            $mark_completed     = $_GET['mark_completed'];
            $completed_count    = '';
            $project_id = $this->active_project->getId();
            $user_id = $user->getId();
            $fyi_query = array();
            if (!empty($mark_completed)){
                $ids = explode(',', $mark_completed);
                $tickets = Tickets::findByIds($ids, STATE_VISIBLE, $this->logged_user->getVisibility());    		
                foreach($tickets as $ticket) {
                    if($ticket->isOpen() && $ticket->canChangeCompleteStatus($this->logged_user)) {
                        $complete = $ticket->complete($this->logged_user);
                        if($complete && !is_error($complete)) {
                            $updated++;
                            $fyi_query[] = "update healingcrystals_assignments_action_request a , healingcrystals_project_objects b set a.is_fyi='-1', last_modified=now() where a.comment_id=b.id and b.type='Comment' and b.project_id='" . $ticket->getProjectId() . "' and b.parent_id='" . $ticket->getId() . "' and a.user_id='" . $this->logged_user->getId() . "' and a.is_fyi='1'";
                        } // if
                    } // if
                } // foreach
            }
            $link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
            mysql_select_db(DB_NAME, $link);
            foreach($fyi_query as $query){
                mysql_query($query, $link);
            }
        
            $entries = array();
	    $temp = array();

            $query = "select b.*, a.is_owner, d.category_name, f.object_id as temp
                        from healingcrystals_assignments a 
			inner join healingcrystals_project_objects b on (a.object_id=b.id and (b.type='Ticket' or (b.type='Task' and b.parent_type='Ticket'))) 
			left outer join healingcrystals_project_objects e on b.milestone_id=e.id   
			left outer join healingcrystals_project_object_categories c on b.id=c.object_id 
			left outer join healingcrystals_project_milestone_categories d on c.category_id=d.id 
			left outer join healingcrystals_starred_objects f on (b.id=f.object_id and f.user_id='" . $user_id . "') 
			inner join healingcrystals_assignments_action_request g on (g.user_id='" . $user_id . "' and g.is_fyi='1' and exists(select * from healingcrystals_project_objects h where h.id=g.comment_id and h.parent_id=b.id))  
			where a.user_id='" . $user_id . "' and 
                        (a.is_owner='1' or g.user_id='" . $user_id . "') and 
                        b.project_id='" . $this->active_project->getId() . "' and 
			b.completed_on is null and b.state='3' and b.visibility='1' 
                        order by b.due_on desc";

            $result = mysql_query($query);
            while($entry = mysql_fetch_assoc($result)){
                $item_class = array_var($entry, 'type');
		$item = new $item_class();
		$item->loadFromRow($entry);
				
		if (!in_array($entry['id'], $temp)){
                    $milestone_id = $item->getMilestoneId();
                    if (!empty($milestone_id)){
                        $query_1 = "select * from healingcrystals_project_objects where id='" . $milestone_id . "'";
			$result_1 = mysql_query($query_1);
			if (mysql_num_rows($result_1)){
                            $item_1 = new Milestone($milestone_id);
			} 
                    }
                    $entries[] = array('obj' => $item, 
                                       'id' => $entry['id'], 
                                       'logged_user_is_responsible' => $entry['is_owner'], 
                                       'department' => array($entry['category_name']), 
				       'milestone_obj' => $item_1, 
                                       'team_name' => $entry['team_name'], 
                                        );
                    $temp[] = $entry['id'];
                    if (!empty($milestone_id)){
                        unset($item_1);
                    }
                } else {
                    $entries[array_search($entry['id'], $temp)]['department'][] = $entry['category_name'];
		}
		unset($item);
            }
	
            $action_request_comments = array();
            $fyi_comments = array();
            $fyi_read_comments = array();
            $query = "select b.id 
                        from healingcrystals_assignments_action_request a 
			inner join healingcrystals_project_objects b on a.comment_id=b.id 
			inner join healingcrystals_projects c on b.project_id=c.id 
			where a.is_action_request='1' and a.user_id='" . $user_id . "' 
			and b.project_id='" . $this->active_project->getId() . "' 
			order by c.name, a.date_added desc";
            $result = mysql_query($query);
            while($entry = mysql_fetch_assoc($result)){
                $action_request_comments[] = new Comment($entry['id']);
            }
				
            $query = "select b.id 
                        from healingcrystals_assignments_action_request a 
			inner join healingcrystals_project_objects b on a.comment_id=b.id 
			inner join healingcrystals_projects c on b.project_id=c.id 
			where a.is_fyi='1' and a.user_id='" . $user_id . "' 
			and b.project_id='" . $this->active_project->getId() . "' 
			order by c.name, a.date_added desc";
            $result = mysql_query($query);
            while($entry = mysql_fetch_assoc($result)){
                $fyi_comments[] = new Comment($entry['id']);
            }
				
            $query = "select b.id 
                        from healingcrystals_assignments_action_request a 
			inner join healingcrystals_project_objects b on a.comment_id=b.id 
			inner join healingcrystals_projects c on b.project_id=c.id 
			where a.is_fyi='R' and a.user_id='" . $user_id . "' 
			and b.project_id='" . $this->active_project->getId() . "' 
			order by a.fyi_marked_read_on, a.last_modified desc, c.name";
            $result = mysql_query($query);
            while($entry = mysql_fetch_assoc($result)){
                $fyi_read_comments[] = new Comment($entry['id']);
            }

            mysql_close($link);
            
	    $this->smarty->assign(array('active_user' => $user,
                                        'project_user' => $project_user,
                                        'entries' => $entries,
                                        'user_projects' => $user->getActiveProjects(), 
                                        'selected_project' => $selected_project, 
                                        'tab' => $tab, 
                                        'action_request_comments' => $action_request_comments, 
                                        'fyi_comments' => $fyi_comments, 
                                        'fyi_read_comments' => $fyi_read_comments, 
                                        'home_tab_content' => $home_tab_content, 
                                    ));
        }
    }
    
    function star_user_projects_page(){
    	$starred_user_id = $_GET['starred_user_id'];
    	$starred_page_type = 'projects';
		if($this->request->isAsyncCall()) {
        	if($this->request->isSubmitted()) {
			  	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
			  	mysql_select_db(DB_NAME, $link);
			  	$query = "insert into healingcrystals_starred_user_pages (starred_by_user_id, starred_user_id, starred_page_type) values ('" . $this->logged_user->getId() . "', '" . $starred_user_id . "', '" . $starred_page_type . "')";
			  	//mysql_query("insert into healingcrystals_testing (query, fired_at) values ('" . $query . "', now())");
			  	mysql_query($query);
			  	mysql_close($link);
        	} // if
        	require_once SYSTEM_MODULE_PATH . '/helpers/function.object_user_star.php';
        
        	print smarty_function_object_user_star(array(
          		'starred_user_id' => $starred_user_id,
				'starred_page_type' => $starred_page_type, 
				'starred_by_user_id' => $this->logged_user->getId(), 
				'project_id' => $this->active_project->getId()
        	), $this->smarty);
        	die();
      }
	}
    
    function unstar_user_projects_page(){
    	$starred_user_id = $_GET['starred_user_id'];
    	$starred_page_type = 'projects';
		if($this->request->isAsyncCall()) {
        	if($this->request->isSubmitted()) {
			  	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
			  	mysql_select_db(DB_NAME, $link);
			  	$query = "delete from healingcrystals_starred_user_pages where starred_by_user_id='" . $this->logged_user->getId() . "' and starred_user_id='" . $starred_user_id . "' and starred_page_type='" . $starred_page_type . "'";
			  	//mysql_query("insert into healingcrystals_testing (query, fired_at) values ('" . $query . "', now())");
			  	mysql_query($query);
			  	mysql_close($link);
        	} // if
        
        	require_once SYSTEM_MODULE_PATH . '/helpers/function.object_user_star.php';
        
        	print smarty_function_object_user_star(array(
          		'starred_user_id' => $starred_user_id,
				'starred_page_type' => $starred_page_type, 
				'starred_by_user_id' => $this->logged_user->getId(), 
				'project_id' => $this->active_project->getId()
        	), $this->smarty);
        	die();
      	}
	}
	
    function star_user_tickets_page(){
    	$starred_user_id = $_GET['starred_user_id'];
    	$starred_page_type = 'tickets';
		if($this->request->isAsyncCall()) {
        	if($this->request->isSubmitted()) {
			  	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
			  	mysql_select_db(DB_NAME, $link);
			  	$query = "insert into healingcrystals_starred_user_pages (starred_by_user_id, starred_user_id, starred_page_type) values ('" . $this->logged_user->getId() . "', '" . $starred_user_id . "', '" . $starred_page_type . "')";
			  	//mysql_query("insert into healingcrystals_testing (query, fired_at) values ('" . $query . "', now())");
			  	mysql_query($query);
			  	mysql_close($link);
        	} // if
        	require_once SYSTEM_MODULE_PATH . '/helpers/function.object_user_star.php';
        
        	print smarty_function_object_user_star(array(
          		'starred_user_id' => $starred_user_id,
				'starred_page_type' => $starred_page_type, 
				'starred_by_user_id' => $this->logged_user->getId(), 
				'project_id' => $this->active_project->getId()
        	), $this->smarty);
        	die();
      }
	}
    
    function unstar_user_tickets_page(){
    	$starred_user_id = $_GET['starred_user_id'];
    	$starred_page_type = 'tickets';
		if($this->request->isAsyncCall()) {
        	if($this->request->isSubmitted()) {
			  	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
			  	mysql_select_db(DB_NAME, $link);
			  	$query = "delete from healingcrystals_starred_user_pages where starred_by_user_id='" . $this->logged_user->getId() . "' and starred_user_id='" . $starred_user_id . "' and starred_page_type='" . $starred_page_type . "'";
			  	//mysql_query("insert into healingcrystals_testing (query, fired_at) values ('" . $query . "', now())");
			  	mysql_query($query);
			  	mysql_close($link);
        	} // if
        
        	require_once SYSTEM_MODULE_PATH . '/helpers/function.object_user_star.php';
        
        	print smarty_function_object_user_star(array(
          		'starred_user_id' => $starred_user_id,
				'starred_page_type' => $starred_page_type, 
				'starred_by_user_id' => $this->logged_user->getId(), 
				'project_id' => $this->active_project->getId()
        	), $this->smarty);
        	die();
      	}
	}
    
  }

?>