<?php

  /**
   * People controller
   *
   * @package activeCollab.modules.system
   * @subpackage controllers
   */
  class PeopleController extends ApplicationController {
    
    /**
     * Actions available through the API
     *
     * @var array
     */
    var $api_actions = array('index');
  
    /**
     * Constructor
     *
     * @param Request $request
     * @return PeopleController
     */
    function __construct($request) {
      parent::__construct($request);
      
      $this->wireframe->addBreadCrumb(lang('People'), assemble_url('people'));
      $this->wireframe->current_menu_item = 'people';
      
      if(Company::canAdd($this->logged_user)) {
        $this->wireframe->addPageAction(lang('New Company'), assemble_url('people_companies_add'));
      } // if
    } // __construct
    
    /**
     * Show companies index page
     *
     * @param void
     * @return null
     */
    function index() {
      if($this->request->isApiCall()) {
        $this->serveData(Companies::findByIds($this->logged_user->visibleCompanyIds()), 'companies');
      } else {
        $page = (integer) $this->request->get('page');
        if($page < 1) {
          $page = 1;
        } // if
        
        list($companies, $pagination) = Companies::paginateActive($this->logged_user, $page, 30);
        
        $this->smarty->assign(array(
          'companies' => $companies,
          'pagination' => $pagination,
        ));
      } // if
    } // index
    
    /**
     * Show archive page
     *
     * @param void
     * @return null
     */
    function archive() {
      if($this->logged_user->isPeopleManager()) {
        $this->wireframe->addBreadCrumb(lang('Archive'), assemble_url('people_archive'));
        
        $page = (integer) $this->request->get('page');
        if($page < 1) {
          $page = 1;
        } // if
        
        list($companies, $pagination) = Companies::paginateArchived($this->logged_user, $page, 30);
        
        $this->smarty->assign(array(
          'companies' => $companies,
          'pagination' => $pagination,
        ));
      } else {
        $this->httpError(HTTP_ERR_FORBIDDEN);
      } // if
    } // archive
    
    function view_assigned_milestones_all_projects(){
    	$page_view = $_GET['page_view'];
    	$order_by = $_GET['order_by'];
    	$sort_order = $_GET['sort_order'];
		$user = $this->logged_user;
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
				 ($page_view ? "" : " a.is_owner='1' and ") . 
				 "b.completed_on is null and b.state='3' and b.visibility='1'";
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
        	'milestones' => $milestones, 
        	'page_view' => $page_view
      	));
    }
    
    function view_today_page_all_projects(){
    	$page_view = $_GET['page_view'];
    	$order_by = $_GET['order_by'];
    	$sort_order = $_GET['sort_order'];
    	
   		$user = $this->logged_user;
      	$user_id = $user->getId();
      	
      	$entries = array();
      	$temp = array();
      	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
      	mysql_select_db(DB_NAME, $link);
      	
		$query = "select b.*, a.is_owner, d.category_name 
	  		 	 from healingcrystals_assignments a 
			 	 inner join healingcrystals_project_objects b on (a.object_id=b.id and (b.type='Ticket' or (b.type='Task' and b.parent_type='Ticket'))) 
			 	 left outer join healingcrystals_project_objects e on b.milestone_id=e.id 
				 left outer join healingcrystals_project_object_categories c on b.id=c.object_id 
			 	 left outer join healingcrystals_project_milestone_categories d on c.category_id=d.id 
			 	 where a.user_id='" . $user_id . "' and " . 
				 ($page_view ? "" : "a.is_owner='1' and ") .
				 "b.completed_on is null and b.state='3' and b.visibility='1' ";

		if (!empty($order_by)){
			switch ($order_by){
				case 'priority':
					$query .= " order by b.priority $sort_order ";
					break;
				case 'project':
					$query .= " order by e.name $sort_order ";
					break;
				case 'name':
					$query .= " order by b.name $sort_order ";
					break;
				case 'department':
					$query .= " order by d.category_name $sort_order ";
					break;
				default:
					$query .= " order by b.priority desc, e.name, b.name";
			}
		} else {
			$query .= " order by b.priority desc, e.name, b.name";
		}

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
								   'milestone_obj' => $item_1);
				$temp[] = $entry['id'];
				if (!empty($milestone_id)){
					unset($item_1);
				}
			} else {
				$entries[array_search($entry['id'], $temp)]['department'][] = $entry['category_name'];
			}
			unset($item);
			
		}
      	
      	mysql_close($link);
      	
      	$this->smarty->assign(array(
        	'active_user' => $user,
        	'entries' => $entries, 
        	'page_view' => $page_view
      	));
    }
  
  }

?>