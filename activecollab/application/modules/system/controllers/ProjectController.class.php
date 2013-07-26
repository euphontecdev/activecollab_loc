<?php

  // Inherit projects controller
  use_controller('projects', SYSTEM_MODULE);

  /**
   * Single project controller
   *
   * @package activeCollab.modules.system
   * @subpackage controllers
   */
  class ProjectController extends ProjectsController {
    
    /**
     * Active project
     *
     * @var Project
     */
    var $active_project;
    
    /**
     * Turn categories support on or off
     *
     * @var boolean
     */
    var $enable_categories = false;
    
    /**
     * Selected category, if categories are enabled
     *
     * @var Category
     */
    var $active_category;
    
    /**
     * Actions exposed through API
     *
     * @var array
     */
    var $api_actions = array('index', 'add', 'edit', 'edit_status', 'delete', 'user_tasks');
    
    /**
     * Construct project controller
     *
     * @param Request $request
     * @return ProjectController
     */
    function __construct($request) {
      parent::__construct($request);
      $this->wireframe->page_actions = array(); // Reset page actions
      
      $project_id = $this->request->get('project_id');      
      if($project_id) {
        $this->active_project = Projects::findById($project_id);
      } // if
      
      
      if(instance_of($this->active_project, 'Project')) {
        if(!$this->logged_user->isProjectMember($this->active_project)) {
          $this->httpError(HTTP_ERR_FORBIDDEN);
        } // if
        
        if($this->active_project->getType() == PROJECT_TYPE_SYSTEM) {
          $this->httpError(HTTP_ERR_NOT_FOUND);
        } // if
        
        if($this->active_project->isCompleted()) {
          $this->wireframe->addBreadCrumb(lang('Archive'), assemble_url('projects_archive'));
        } // if
        
        $this->wireframe->addBreadCrumb($this->active_project->getName(), $this->active_project->getOverviewUrl());
        
        $tabs = new NamedList();
        $tabs->add('overview', array(
          'text' => str_excerpt($this->active_project->getName(), 25),
          'url' => $this->active_project->getOverviewUrl()
        ));
        
        event_trigger('on_project_tabs', array(&$tabs, &$this->logged_user, &$this->active_project));
        
        $tabs->add('people', array(
          'text' => lang('People'),
          'url' => $this->active_project->getPeopleUrl(),
        ));
        
        $tabs->add('recent_pages', array(
          'text' => lang('Recent Pages'),
          'url' => assemble_url('recent_pages') . '&project_id=' . $this->active_project->getId(),
        ));
        /*$tabs->add('recent_pages_1', array(
          'text' => lang('Recent Pages'),
          'url' => assemble_url('project_recent_pages_1'),
        ));*/
        //BOF:mod
        /*$tabs->add('attachments', array(
          'text' => lang('Attachments'),
          'url' => assemble_url('attachments_list', array('project_id' => $this->active_project->getId())) ,
        ));*/
        //EOF:mod
        $tabs->add('reminders', array(
          'text' => lang('Notifications'),
          'url' => assemble_url('reminders_list', array('project_id' => $this->active_project->getId())) ,
        ));
	    $tabs->add('calendar', array(
	      'text' => lang('Calendar'),
	      'url' => Calendar::getProjectCalendarUrl($this->active_project),
	    ));
        js_assign('image_picker_url', assemble_url('image_picker', array('project_id' => $this->active_project->getId())));
        js_assign('active_project_id', $this->active_project->getId());

        $this->smarty->assign('page_tabs', $tabs);
        
        // ---------------------------------------------------
        //  Set page company and page project
        // ---------------------------------------------------
        
        $page_company = $this->active_project->getCompany();
        if(instance_of($page_company, 'Company')) {
          $this->wireframe->page_company = $page_company;
        } // if
        $this->wireframe->page_project = $this->active_project;
        
      // New project
      } else {
        if($this->controller_name == 'project') {
          $this->active_project = new Project();
        } else {
          $this->httpError(HTTP_ERR_NOT_FOUND);
        } // if
      } // if
      
      $this->smarty->assign(array(
        'active_project' => $this->active_project,
        'page_tab' => 'overview',
      ));
      
      // -----------------------------------------------------------------------
      //  Do category related voodoo if categories are enabled. Categories are 
      //  not initialized if we don't have a loaded project (no project ID)
      // -----------------------------------------------------------------------
      
      if($this->active_project->isLoaded() && $this->enable_categories) {
        $category_id = $this->request->get('category_id');
        if($category_id) {
          $this->active_category = Categories::findById($category_id);
        } // if
        
        if(instance_of($this->active_category, 'Category')) {
          if($this->active_category->getProjectId() != $this->active_project->getId()) {
            $this->active_category = new Category(); // this category is not part of selected project
          } // if
        } else {
          $this->active_category = new Category();
        } // if
        
        $this->smarty->assign(array(
          'active_category'  => $this->active_category,
          'categories_url'   => Category::getSectionUrl($this->active_project, $this->getControllerName(), $this->active_module),
          'add_category_url' => Category::getAddUrl($this->active_project, $this->getControllerName(), $this->active_module),
        ));
      } // if
    } // __construct
    
    /**
     * Show project overview page
     *
     * @param void
     * @return null
     */
    function index() {
      if($this->active_project->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      if($this->request->isApiCall()) {
        $this->serveData($this->active_project, 'project', array(
          'describe_leader' => true, 
          'describe_company' => true, 
          'describe_group' => true,
          'describe_permissions' => true,
          'describe_icon' => true,
        ));
      } else {
//        $options = $this->active_project->getOptions($this->logged_user);
//        if(is_foreachable($options)) {
//          $this->wireframe->addPageAction(lang('Options'), $this->active_project->getOverviewUrl(), $options, null, 255);
//        } // if
        
        $this->wireframe->addRssFeed(
          lang(':project project', array('project' => $this->active_project->getName())) . ' - ' . lang('Recent activities'),
          assemble_url('project_rss', array('project_id' => $this->active_project->getId(), 'token' => $this->logged_user->getToken(true))),
          FEED_RSS          
        );
        
        $task_types = get_completable_project_object_types();
        $day_types = get_day_project_object_types();
        
        $home_sidebars = array();
        event_trigger('on_project_overview_sidebars', array(&$home_sidebars, &$this->active_project, &$this->logged_user));
        
        $this->smarty->assign(array(
          'project_group' => $this->active_project->getGroup(),
          'project_company' => $this->active_project->getCompany(),
          'late_and_today' => ProjectObjects::findLateAndToday($this->logged_user, $this->active_project, $day_types),
          'upcoming_objects' => ProjectObjects::findUpcoming($this->logged_user, $this->active_project, $day_types),
          'grouped_activities' => group_by_date(ActivityLogs::findProjectActivitiesByUser($this->active_project, $this->logged_user, 20), $this->logged_user),
          'home_sidebars'      => $home_sidebars,
        ));
        
        $link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
        mysql_select_db(DB_NAME);
        
        $query = "select count(*) as count from healingcrystals_project_objects 
				 where project_id='" . $this->active_project->getId() .  "' and type='Milestone' and completed_on is null";
        $result = mysql_query($query);
        $info = mysql_fetch_assoc($result);
        $milestones_count = $info['count'];
        
        $query = "select count(*) as count from healingcrystals_project_objects 
				 where project_id='" . $this->active_project->getId() .  "' and type='Ticket' and completed_on is null";
        $result = mysql_query($query);
        $info = mysql_fetch_assoc($result);
        $tickets_count = $info['count'];
        
        $query = "select count(*) as count from healingcrystals_project_objects 
				 where project_id='" . $this->active_project->getId() .  "' and type='Page' and completed_on is null";
        $result = mysql_query($query);
        $info = mysql_fetch_assoc($result);
        $pages_count = $info['count'];
        
        $query = "select count(*) as count from healingcrystals_project_objects 
				 where project_id='" . $this->active_project->getId() .  "' and type='Task' and completed_on is null";
        $result = mysql_query($query);
        $info = mysql_fetch_assoc($result);
        $tasks_count = $info['count'];
        
        $query = "select count(*) as count from healingcrystals_project_objects 
				 where project_id='" . $this->active_project->getId() .  "' and type='File' and completed_on is null";
        $result = mysql_query($query);
        $info = mysql_fetch_assoc($result);
        $files_count = $info['count'];
        
        $query = "select count(*) as count from healingcrystals_project_objects 
				 where project_id='" . $this->active_project->getId() .  "' and type='Discussion' and completed_on is null";
        $result = mysql_query($query);
        $info = mysql_fetch_assoc($result);
        $discussions_count = $info['count'];
        
        $query = "select count(*) as count from healingcrystals_project_objects 
				 where project_id='" . $this->active_project->getId() .  "' and type='Checklist' and completed_on is null";
        $result = mysql_query($query);
        $info = mysql_fetch_assoc($result);
        $checklists_count = $info['count'];
        
        mysql_close($link);
        $this->smarty->assign(array('milestones_count' => $milestones_count, 
									'tickets_count' => $tickets_count, 
									'pages_count' => $pages_count, 
									'tasks_count' => $tasks_count, 
									'files_count' => $files_count, 
									'discussions_count' => $discussions_count, 
									'checklists_count' => $checklists_count));
        
      } // if
    } // index
    
    /**
     * Show tasks for a given user
     *
     * @param void
     * @return null
     */
    function user_tasks() {
      $filter = new AssignmentFilter();
      
      $filter->setUserFilter(USER_FILTER_LOGGED_USER);
      $filter->setProjectFilter(PROJECT_FILTER_SELECTED);
      $filter->setProjectFilterData(array($this->active_project->getId()));
      $filter->setStatusFilter(STATUS_FILTER_ACTIVE);
      
      $filter->setOrderBy('priority DESC');
      $filter->setObjectsPerPage(30);
      
      if($this->request->isApiCall()) {
        $this->serveData(AssignmentFilters::executeFilter($this->logged_user, $filter, false), 'assignments');
      } else {
        list($assignments, $pagination) = AssignmentFilters::executeFilter($this->logged_user, $filter, null, (integer) $this->request->get('page'));
      
        $this->smarty->assign(array(
          'assignments' => $assignments,
          'pagination' => $pagination,
        ));
      } // if
    } // user_tasks
    
    /**
     * Process add project form
     *
     * @param void
     * @return null
     */
    function add() {
      $this->wireframe->print_button = false;
      
      if($this->request->isApiCall() && !$this->request->isSubmitted()) {
        $this->httpError(HTTP_ERR_BAD_REQUEST);
      } // if
      
      if(!Project::canAdd($this->logged_user)) {
        $this->httpError(HTTP_ERR_FORBIDDEN);
      } // if
      
      $project_data = $this->request->post('project');
      if(!is_array($project_data)) {
        $project_data = array(
          'leader_id' => $this->logged_user->getId(),
          'default_visibility' => VISIBILITY_PRIVATE,
        );
      } // if
      $this->smarty->assign('project_data', $project_data);
      
      if($this->request->isSubmitted()) {
        db_begin_work();
        
        $this->active_project = new Project(); // just in case
        
        $this->active_project->setAttributes($project_data);
        $this->active_project->setType(PROJECT_TYPE_NORMAL);
        $this->active_project->setStatus(PROJECT_STATUS_ACTIVE);
        
        $leader = null;
        if($this->active_project->getLeaderId()) {
          $leader = Users::findById($this->active_project->getLeaderId());
          if(instance_of($leader, 'User')) {
            $this->active_project->setLeader($leader);
          } // if
        } // if
        
        $project_template = null;
        $project_template_id = array_var($project_data, 'project_template_id');
        if($project_template_id) {
          $project_template = Projects::findById($project_template_id);
        } // if
        
        $save = $this->active_project->save($project_template);
        if($save && !is_error($save)) {
          
          // Add user who created a project and leader
          $this->active_project->addUser($this->logged_user, null, null);
          if(instance_of($leader, 'User')) {
            $this->active_project->addUser($leader, null, null);
          } // if
          
          // Clone project template
          if(instance_of($project_template, 'Project')) {
            $project_template->copyItems($this->active_project);
          } else {
            
            // Auto assign users...
            $users = Users::findAutoAssignUsers();
            if(is_foreachable($users)) {
              foreach($users as $user) {
                $this->active_project->addUser($user, $user->getAutoAssignRole(), $user->getAutoAssignPermissions());
              } // foreach
            } // if
            
            // Create default categories
            $category_definitions = array();
            event_trigger('on_master_categories', array(&$category_definitions));
            
            if(is_foreachable($category_definitions)) {
              foreach($category_definitions as $category_definition) {
                $default_categories = $category_definition['value'];
                if(!is_foreachable($default_categories)) {
                  $default_categories = array('General');
                } // if
                
                foreach($default_categories as $category_name) {
                  if(trim($category_name) != '') {
                    $category = new Category();
                    
                    $category->log_activities = false; // don't log stuff in DB
                    
                    $category->setName($category_name);
                    $category->setProjectId($this->active_project->getId());
                    $category->setCreatedBy($this->logged_user);
                    $category->setState(STATE_VISIBLE);
                    $category->setVisibility(VISIBILITY_NORMAL);
                    $category->setModule($category_definition['module']);
                    $category->setController($category_definition['controller']);
                    
                    $category->save();
                  } // if
                } // foreach
              } // foreach
            } // if
          } // if
          
          db_commit();
          
          if($this->request->isApiCall()) {
            $this->serveData($this->active_project, 'project');
          } else {
            flash_success('Project ":name" has been created. Use this page to add more people to the project...', array('name' => $this->active_project->getName()));
            $this->redirectToUrl($this->active_project->getPeopleUrl());
          } // if
        } else {
          db_rollback();
          
          if($this->request->isApiCall()) {
            $this->serveData($save);
          } else {
            $this->smarty->assign('errors', $save);
          } // if
        } // if
      } // if
    } // add
    
    /**
     * Update project
     *
     * @param void
     * @return null
     */
    function edit() {
      $this->wireframe->print_button = false;
      
      if($this->request->isApiCall() && !$this->request->isSubmitted()) {
        $this->httpError(HTTP_ERR_BAD_REQUEST, null, true, true);
      } // if
      
      if($this->active_project->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      if(!$this->active_project->canEdit($this->logged_user)) {
        $this->httpError(HTTP_ERR_FORBIDDEN);
      } // if
      
      $project_data = $this->request->post('project');
      if(!is_array($project_data)) {
        $project_data = array(
          'name' => $this->active_project->getName(),
          'overview' => $this->active_project->getOverview(),
          'default_visibility' => $this->active_project->getDefaultVisibility(),
          'leader_id' => $this->active_project->getLeaderId(),
          'group_id' => $this->active_project->getGroupId(),
          'company_id' => $this->active_project->getCompanyId(),
          'default_visibility' => $this->active_project->getDefaultVisibility(),
          'starts_on' => $this->active_project->getStartsOn()
        );
      } // if
      
      $this->smarty->assign('project_data', $project_data);
      
      if($this->request->isSubmitted()) {
        db_begin_work();
        
        $old_name = $this->active_project->getName();
        $this->active_project->setAttributes($project_data);
        
        if($this->active_project->isModified('leader_id') && $this->active_project->getLeaderId()) {
          $leader = Users::findById($this->active_project->getLeaderId());
          if(instance_of($leader, 'User')) {
            $this->active_project->setLeader($leader);
          } // if
        } // if
        
        if($this->active_project->isModified('company_id')) {
          cache_remove('project_icons');
        } // if
        
        $save = $this->active_project->save();
        
        if($save && !is_error($save)) {
          db_commit();
          
          if($this->request->isApiCall()) {
            $this->serveData($this->active_project, 'project');
          } else {
            flash_success('Project :name has been updated', array('name' => $old_name));
            $this->redirectToUrl($this->active_project->getOverviewUrl());
          } // if
        } else {
          db_rollback();
          
          if($this->request->isApiCall()) {
            $this->serveData($save);
          } else {
            $this->smarty->assign('errors', $save);
          } // if
        } // if
      } // if
    } // edit
    
    /**
     * Show and process edit status form
     *
     * @param void
     * @return null
     */
    function edit_status() {
      if($this->active_project->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND, null, true, $this->request->isApiCall());
      } // if
      
      if($this->request->isApiCall() && !$this->request->isSubmitted()) {
        $this->httpError(HTTP_ERR_BAD_REQUEST, null, true, true);
      } // if
      
      if(!$this->active_project->canEdit($this->logged_user)) {
        $this->httpError(HTTP_ERR_FORBIDDEN, null, true, $this->request->isApiCall());
      } // if
      
      $project_data = $this->request->post('project');
      if(!is_array($project_data)) {
        $project_data = array(
          'status' => $this->active_project->getStatus()
        );
      } // if
      $this->smarty->assign('project_data', $project_data);
      
      if($this->request->isSubmitted()) {
        db_begin_work();
        
        switch(array_var($project_data, 'status')) {
          case PROJECT_STATUS_ACTIVE:
            $save = $this->active_project->reopen();
            break;
          case PROJECT_STATUS_PAUSED:
            $save = $this->active_project->reopen(true);
            break;
          case PROJECT_STATUS_COMPLETED:
            $save = $this->active_project->complete($this->logged_user);
            break;
          case PROJECT_STATUS_CANCELED:
            $save = $this->active_project->complete($this->logged_user, true);
            break;
          default:
            $this->httpError(HTTP_ERR_BAD_REQUEST);
        } // switch
        
        if($save && !is_error($save)) {
          db_commit();
          
          if($this->request->isApiCall()) {
            $this->serveData($this->active_project, 'project');
          } else {
            flash_success("Status of ':name' project has been updated", array('name' => $this->active_project->getName()));
            $this->redirectToUrl($this->active_project->getOverviewUrl());
          } // if
        } else {
          db_rollback();
          
          if($this->request->isApiCall()) {
            $this->serveData($save);
          } else {
            $this->smarty->assign('errors', $save);
          } // if
        } // if
      } // if
    } // edit_status
    
    /**
     * Edit Project Icon
     *
     * @param void
     * @return null
     */
    function edit_icon() {
      if($this->active_project->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      if(!$this->active_project->canEdit($this->logged_user)) {
        $this->httpError(HTTP_ERR_FORBIDDEN);
      } // if
      
      if(!extension_loaded('gd')) {
        $message = lang('<b>GD not Installed</b> - GD extension is not installed on your system. You will not be able to upload project icons, company logos and avatars!');
        if ($this->request->isAsyncCall()) {
          echo "<p>$message</p>";
          die();
        } else {
          $this->wireframe->addPageMessage($message, PAGE_MESSAGE_ERROR);  
        } // if
      } // if

    	if($this->request->isSubmitted()) {
    	  if(!isset($_FILES['icon']) || !is_uploaded_file($_FILES['icon']['tmp_name'])) {
    	    $message = lang('Please select an image');
    	    if ($this->request->isAsyncCall()) {
    	      $this->httpError(HTTP_ERR_OPERATION_FAILED, $message);
    	    } else {
            flash_error($message);
            $this->redirectToUrl($this->active_project->getEditIconUrl());
    	    } // if
    	  } // if

    		if(can_resize_images()) {
    		  $errors = new ValidationErrors();
    		  do {
    		    $from = WORK_PATH.'/'.make_password(10).'_'.$_FILES['icon']['name'];
    		  } while (is_file($from));
    		  
    		  if (!move_uploaded_file($_FILES['icon']['tmp_name'], $from)) {
            $errors->addError(lang("Can't copy image to work path"), 'icon');
    		  } else {
            if (FIX_UPLOAD_PERMISSION !== false) {
              @chmod($from, FIX_UPLOAD_PERMISSION);
            } // if
      		  // small avatar
      		  $to = $this->active_project->getIconPath();
      		  $small = scale_image($from, $to, 16, 16, IMAGETYPE_GIF);
      		  // large avatar
      		  $to = $this->active_project->getIconPath(true);
      		  $large = scale_image($from, $to, 40, 40, IMAGETYPE_GIF);
      		          	  
        	  @unlink($from);
    		  } // if
    		  
    		  if(empty($from)) {
    		  	$errors->addError('Select icon', 'icon');
    		  } // if
    		  
    		  if($errors->hasErrors()) {
      	    $this->smarty->assign('errors', $errors);
      	    $this->render();
      	  } // if
      	  
      	  cache_remove('project_icons');
    		} // if
    	} // if
    } // edit_icon
    
    /**
     * Delete Project Icon
     *
     * @param void
     * @return null
     */
    function delete_icon() {
      if($this->active_project->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      if(!$this->active_project->canEdit($this->logged_user)) {
        $this->httpError(HTTP_ERR_FORBIDDEN);
      } // if
      
    	if($this->request->isSubmitted()) {
    	  unlink($this->active_project->getIconPath());
    	  unlink($this->active_project->getIconPath(true));
    	  
    	  cache_remove('project_icons');
    	  if ($this->request->isAsyncCall()) {
    	    $this->serveData(array(
    	     'message' => lang('Icon successfully removed'),
           'icon' => $this->active_project->getIconUrl(true)
    	    ), 'delete', null, FORMAT_JSON);
    	  } else {
    	     $this->redirectToUrl($this->active_project->getEditIconUrl()); 
    	  }
    	} else {
    	  $this->httpError(HTTP_ERR_BAD_REQUEST);
    	} // if
    } // delete-avatar
    
    /**
     * Delete project
     *
     * @param void
     * @return null
     */
    function delete() {
      if($this->request->isApiCall() && !$this->request->isSubmitted()) {
        $this->httpError(HTTP_ERR_BAD_REQUEST, null, true, $this->request->isApiCall());
      } // if
      
      if($this->active_project->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND, null, true, $this->request->isApiCall());
      } // if
      
      if(!$this->active_project->canDelete($this->logged_user)) {
        $this->httpError(HTTP_ERR_FORBIDDEN, null, true, $this->request->isApiCall());
      } // if
      
      if($this->request->isSubmitted()) {
        $delete = $this->active_project->delete();
        if($delete && !is_error($delete)) {
          if($this->request->isApiCall()) {
            $this->httpOk();
          } else {
            flash_success("Project ':name' has been deleted", array('name' => $this->active_project->getName()));
            $this->redirectTo('projects');
          } // if
        } else {
          if($this->request->isApiCall()) {
            $this->serveData($delete);
          } else {
            flash_error("Failed to delete ':name' project", array('name' => $this->active_project->getName()));
            $this->redirectTo('projects');
          } // if
        } // if
      } else {
        $this->httpError(HTTP_ERR_BAD_REQUEST, null, true, $this->request->isApiCall());
      } // if
    } // delete
    
    /**
     * Pin project
     *
     * @param void
     * @return null
     */
    function pin() {
      if($this->active_project->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      if($this->request->isSubmitted()) {
        $pin = PinnedProjects::pinProject($this->active_project, $this->logged_user);
        if($pin && !is_error($pin)) {
        	if($this->request->isAsyncCall()) {
          	die($this->active_project->getUnpinUrl());
        	} else {
        		flash_success("Project ':name' has been marked as favorite", array('name' => $this->active_project->getName()));
        	} //if
        } else {
        	if($this->request->isAsyncCall()) {
          	$this->httpError(HTTP_ERR_OPERATION_FAILED);
        	} else {
        		flash_error("Failed to mark ':name' project as favorite", array('name' => $this->active_project->getName()));
        	} // if
        } // if
        $this->redirectToReferer(assemble_url('dashboard'));
      } else {
        $this->httpError(HTTP_ERR_BAD_REQUEST);
      } // if
    } // pin
    
    /**
     * Unpin project
     *
     * @param void
     * @return null
     */
    function unpin() {
      if($this->active_project->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      if($this->request->isSubmitted()) {
        $unpin = PinnedProjects::unpinProject($this->active_project, $this->logged_user);
        if($unpin && !is_error($unpin)) {
        	if($this->request->isAsyncCall()) {
          	die($this->active_project->getPinUrl());
        	} else {
        		flash_success("Project ':name' has been removed from list of favorite projects", array('name' => $this->active_project->getName()));
        	} // if
        } else {
        	if($this->request->isAsyncCall()) {
          	$this->httpError(HTTP_ERR_OPERATION_FAILED);
        	} else {
        		flash_error("Failed to remove ':name' project from list of favorite projects", array('name' => $this->active_project->getName()));
        	} // if
        } // if
        $this->redirectToReferer(assemble_url('dashboard'));
      } else {
        $this->httpError(HTTP_ERR_BAD_REQUEST);
      } // if
    } // unpin
    
    /**
     * Render recent activities feed
     *
     * @param void
     * @return null
     */
    function rss() {
      if($this->active_project->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      require_once ANGIE_PATH . '/classes/feed/init.php';
      
      $feed = new Feed(lang(':project project', array('project' => $this->active_project->getName())) . ' - ' . lang('Recent activities'), $this->active_project->getOverviewUrl());
      $feed->setDescription(lang('Recent ":project" activities', array('project' => $this->active_project->getName())));
      
      $activities = ActivityLogs::findProjectActivitiesByUser($this->active_project, $this->logged_user, 50);
      if(is_foreachable($activities)) {
        foreach($activities as $activity) {
          $object = $activity->getObject();
          
          $activity_title = $activity_body = $activity->renderHead(null, true);
          $activity_title = strip_tags($activity_title);
          
          if ($activity->has_body && ($body = trim($activity->renderBody()))) {
            $activity_body.=$body;
          } // if
          
          $item = new FeedItem($activity_title, $object->getViewUrl(), $activity_body, $activity->getCreatedOn());
          $item->setId(extend_url($object->getViewUrl(), array('guid' => $activity->getId())));
          $feed->addItem($item);
        } // foreach
      } // if
      
      print render_rss_feed($feed);
      die();
    } // rss
    
    /**
     * Serve iCal data
     *
     * @param void
     * @return null
     */
    function ical() {
      if($this->active_project->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      $filter = ProjectUsers::getVisibleTypesFilterByProject($this->logged_user, $this->active_project, get_completable_project_object_types());
      if($filter) {
        $objects = ProjectObjects::find(array(
    		  'conditions' => array($filter . ' AND completed_on IS NULL AND state >= ? AND visibility >= ?', STATE_VISIBLE, $this->logged_user->getVisibility()),
    		  'order'      => 'priority DESC',
    		));
    		
    		render_icalendar($this->active_project->getName() . ' ' . lang('calendar'), $objects);
    		die();
      } else {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
    } // ical
    
    /**
     * Show iCal subscribe page
     *
     * @param void
     * @return null
     */
    function ical_subscribe() {
      $this->wireframe->print_button = false;
      
      if($this->active_project->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      $ical_url = assemble_url('project_ical', array(
        'project_id' => $this->active_project->getId(),
        'token' => $this->logged_user->getToken(true),
      ));
      
      $ical_subscribe_url = str_replace(array('http://', 'https://'), array('webcal://', 'webcal://'), $ical_url);
      
      $this->smarty->assign(array(
        'ical_url' => $ical_url,
        'ical_subscribe_url' => $ical_subscribe_url
      ));
    } // ical_subscribe
    
    /**
     * Exports basic project info
     * 
     * @param void
     * @return void
     */
    function export() {
      if($this->active_project->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      $exportable_modules = explode(',', array_var($_GET,'modules', null));
      if (!is_foreachable($exportable_modules)) {
        $exportable_modules = null;
      } // if
      require_once(PROJECT_EXPORTER_MODULE_PATH.'/models/ProjectExporterOutputBuilder.class.php');
      $output_builder = new ProjectExporterOutputBuilder($this->active_project, $this->smarty, false, $exportable_modules);
     
      if (!$output_builder->createOutputFolder()) {
        $this->serveData($output_builder->execution_log, 'execution_log', null, FORMAT_JSON);
      } // if
      
      $output_builder->createAttachmentsFolder();
      $output_builder->setFileTemplate(SYSTEM_MODULE, 'project', 'index');
      $output_builder->outputToFile('index');
      $output_builder->outputProjectIcon();
      $output_builder->outputStyle();
      
      $this->serveData($output_builder->execution_log, 'execution_log', null, FORMAT_JSON);
    } // export
    
    // ---------------------------------------------------
    //  Categories implementation
    // ---------------------------------------------------
    
    /**
     * Show categories in this section
     *
     * @param void
     * @return null
     */
    function categories() {
      $categories = Categories::findByModuleSection($this->active_project, $this->active_module, $this->getControllerName());
      
      if($this->request->isApiCall()) {
        $this->serveData($categories, 'categories');
      } else {
        $this->setTemplate(array(
          'module' => RESOURCES_MODULE, 
          'controller' => 'categories', 
          'template' => 'list'
        ));
        $this->smarty->assign(array(
          'categories' => $categories,
          'can_add_category' => Category::canAdd($this->logged_user, $this->active_project)
        ));	
      } // if
    } // index
    
    /**
     * View single category
     *
     * @param void
     * @return null
     */
    function view_category() {
      if($this->active_category->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      $this->setTemplate(array(
        'module' => RESOURCES_MODULE, 
        'controller' => 'categories', 
        'template' => 'view'
      ));
      
      $per_page = 30; // discussions per page
      $page = (integer) $this->request->get('page');
      if($page < 1) {
        $page = 1;
      } // if
      
      list($category_objects, $pagination) = $this->active_category->paginateObjects($page, $per_page, STATE_VISIBLE, $this->logged_user->getVisibility());
      $this->smarty->assign(array(
        'category_objects' => $category_objects,
        'pagination' => $pagination,
      ));
    } // view
    
    /**
     * Show and process add category page
     *
     * @param void
     * @return null
     */
    function add_category() {
      $this->setTemplate(array(
        'module' => RESOURCES_MODULE, 
        'controller' => 'categories', 
        'template' => 'add'
      ));
      
      $category_data = $this->request->post('category');
      $this->smarty->assign('category_data', $category_data);
      
      if($this->request->isSubmitted()) {
        $this->active_category = new Category();
        
        $this->active_category->setAttributes($category_data);
        $this->active_category->setModule($this->active_module);
        $this->active_category->setController($this);
        $this->active_category->setProjectId($this->active_project->getId());
        $this->active_category->setCreatedBy($this->logged_user);
        $this->active_category->setState(STATE_VISIBLE);
        $this->active_category->setVisibility(VISIBILITY_NORMAL);
        
        $save = $this->active_category->save();
        if($save && !is_error($save)) {
          if($this->request->isApiCall()) {
            $this->serveData($this->active_category, 'category');
          } elseif($this->request->isAsyncCall()) {
            $this->smarty->assign('category', $this->active_category);
            print $this->smarty->fetch(get_template_path('_category_row', 'categories', RESOURCES_MODULE));
            die();
          } else {
            flash_success('Category :category_name has been created', array('category_name' => $this->active_category->getName()));
            $this->redirectToUrl($this->smarty->get_template_vars('categories_url'));
          } // if
        } else {
          if($this->request->isApiCall() || $this->request->isAsyncCall()) {
            $this->serveData($save);
          } else {
            $this->smarty->assign('errors', $save);
          } // if
        } // if
      } // if
    } // add
    
    /**
     * Quick add project category
     *
     * @param void
     * @return null
     */
    function quick_add_category() {
      if($this->request->isSubmitted() && $this->request->isAsyncCall()) {
        $this->active_category = new Category();
        
        $this->active_category->setAttributes($this->request->post('category'));
        $this->active_category->setModule($this->active_module);
        $this->active_category->setController($this);
        $this->active_category->setProjectId($this->active_project->getId());
        $this->active_category->setCreatedBy($this->logged_user);
        $this->active_category->setState(STATE_VISIBLE);
        $this->active_category->setVisibility(VISIBILITY_NORMAL);
        
        $save = $this->active_category->save();
        if($save && !is_error($save)) {
          print $this->active_category->getId();
          die();
        } else {
          $this->serveData($save);
        } // if
      } else {
        $this->httpError(HTTP_ERR_BAD_REQUEST);
      } // if
    } // quick_add_category
    
    /**
     * Show and process edit category page
     *
     * @param void
     * @return null
     */
    function edit_category() {
      if($this->active_category->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      $this->setTemplate(array('module' => RESOURCES_MODULE, 'controller' => 'categories', 'template' => 'edit'));
      
      $category_data = $this->request->post('category');
      if(!is_array($category_data)) {
        $category_data = array(
          'name' => $this->active_category->getName(),
        );
      } // if
      
      $this->smarty->assign('category_data', $category_data);
      
      if($this->request->isSubmitted()) {
        $old_name = $this->active_category->getName();
        
        $this->active_category->setAttributes($category_data);
        $save = $this->active_category->save();
        
        if($save && !is_error($save)) {
          if($this->request->isApiCall()) {
            $this->serveData($this->active_category, 'category');
          } elseif($this->request->isAsyncCall()) {
            print $this->active_category->getName();
            die();
          } else {
            flash_success('Category :category_name has been updated', array('category_name' => $old_name));
            $this->redirectToUrl($this->smarty->get_template_vars('categories_url'));
          } // if
        } else {
          if($this->request->isApiCall() || $this->request->isAsyncCall()) {
            $this->serveData($save);
          } else {
            $this->smarty->assign('errors', $save);
          } // if
        } // if
      } // if
    } // edit
    
    /**
     * Delete category
     *
     * @param void
     * @return null
     */
    function delete_category() {
      if($this->active_category->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      if($this->request->isSubmitted()) {
        $delete = $this->active_category->delete();
        if($delete && !is_error($delete)) {
          if($this->request->getFormat() == FORMAT_HTML) {
            flash_success('Category :category_name has been deleted', array('category_name' => $this->active_category->getName()));
            $this->redirectToUrl($this->smarty->get_template_vars('categories_url'));
          } else {
            $this->serveData($this->active_category, 'category');
          } // if
        } else {
          if($this->request->getFormat() == FORMAT_HTML) {
            flash_error('Failed to delete :category_name', array('category_name' => $this->active_category->getName()));
            $this->redirectToUrl($this->smarty->get_template_vars('categories_url'));
          } else {
            $this->serveData($delete);
          } // if
        } // if
      } else {
        $this->httpError(HTTP_ERR_BAD_REQUEST);
      } // if
    } // delete_category
    
    //BOF: task 04 | AD
    function manage_milestone_categories() {
    	$project_id 			= $this->active_project->getId();
    	$selected_category_name = '';
		$message 				= '';
		$message_color 			= 'red';
    	$category_data 			= $this->request->post('milestone_category');
    	$action 				= $category_data['hdn_action'];
    	$selected_category_id 	= $category_data['hdn_id'];
   		$status 				= true;
   		$action_handled			= false;
    	
		$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
		mysql_select_db(DB_NAME, $link);
		
		if ($action=='editnextstep'){
			$category_name = $category_data['category_name'];
			$query = "select * from healingcrystals_project_milestone_categories where project_id='" . $project_id . "' and category_name='" . mysql_real_escape_string($category_name) . "' and id<>'" . $selected_category_id . "'";
			$result = mysql_query($query);
			if (mysql_num_rows($result)){
				$message .= "Modified milestones department name already exists.";
				$status = false;
			} else {
				$query = "update healingcrystals_project_milestone_categories set category_name='" . mysql_real_escape_string($category_name) . "', last_modified=now() where id='" . $selected_category_id . "' and project_id='" . $project_id . "'";
				if (mysql_query($query)){
					$message .= 'Milestones department, modified';
					$message_color = 'green';
					$action_handled = true;
				} else {
					$message .= 'Error encountered while modifying information';
					$status = false;
				}
			}
		}
		
		$categories = array();
		$query = "select id, category_name, date_added, last_modified from healingcrystals_project_milestone_categories where project_id='" . $project_id . "' order by category_name";
		$result = mysql_query($query);
		while($entry = mysql_fetch_assoc($result)){
			if (!empty($category_data['chk_edit'])){
				if ($category_data['chk_edit']==$entry['id']){
					$selected_category_name = $entry['category_name'];
				}
			} elseif (!empty($category_data['chk_delete'])){
				if ($category_data['chk_delete']==$entry['id']){
					$selected_category_name = $entry['category_name'];
				}
			} elseif (!empty($selected_category_id)){
				if ($selected_category_id==$entry['id']){
					$selected_category_name = $entry['category_name'];
					if ($action=='deletenextstep'){
						continue;
					}
				}
			}
			$categories[] = array('id' 		=> $entry['id'], 
								  'name' 	=> $entry['category_name'], 
								  'added'	=> date('m/d/Y', strtotime($entry['date_added'])), 
								  'modified'=> (!empty($entry['last_modified']) ? date('m/d/Y', strtotime($entry['last_modified'])) : '--'));		
	  	}
	  	if (!$status){
	  		$categories = array();
	  	}
	  	
		if($action=='deletenextstep') {
			$query = "update healingcrystals_project_objects set category_id=null where module='milestones' and type='Milestone' and project_id='" . $project_id . "' and category_id='" . $selected_category_id . "'";
			mysql_query($query);
			$query = "delete from healingcrystals_project_milestone_categories where id='" . $selected_category_id . "'";
			mysql_query($query);
			$action_handled = true;
			$message .= 'Milestones category for the project: ' . $selected_category_name . ', deleted';
			$message_color = 'green';
		}


		if (!empty($category_data['chk_edit'])){
			$action = 'edit';
			$selected_category_id = $category_data['chk_edit'];
			$label = 'Edit milestones department: "' . $selected_category_name . '"';
		} elseif (!empty($category_data['chk_delete'])){
			$action = 'delete';
			$selected_category_id = $category_data['chk_delete'];
			$query = "select count(*) as num from healingcrystals_project_objects where module='milestones' and type='Milestone' and project_id='" . $project_id . "' and category_id='" . $selected_category_id . "'";
			$result = mysql_query($query);
			$entries = mysql_fetch_assoc($result);
			$label = 'Delete milestones department: "' . $selected_category_name . '" ?' . (mysql_num_rows($result)  ? ' (Milestones associated with department: ' . $entries['num'] . ')' : '');
		} elseif (empty($category_data['hdn_action'])) {
			$action = 'add';
			$label = 'Create new milestone department';
		} elseif ($action=='editnextstep'){
			if ($action_handled){
				$action = 'add';
				$selected_category_id = '';
				$selected_category_name = '';
				$label = 'Create new milestone department';
			} else {
				$action = 'editnextstep';
				$label = 'Edit milestones department: "' . $selected_category_name . '"';
			}
		} elseif ($action=='deletenextstep'){
			if ($action_handled){
				$action = 'add';
				$selected_category_id = '';
				$selected_category_name = '';
				$label = 'Create new milestones department';
			}
		} 
		
		if($this->request->isSubmitted()) {
			if ($action=='add' && !$action_handled){
				$category_name = $category_data['category_name'];
				if (empty($category_name)){
					$message .= 'Category name missing.<br/>';
				} else {
					if (!empty($project_id)){
						$query = "select * from healingcrystals_project_milestone_categories where project_id='" . $project_id . "' and category_name='" . mysql_real_escape_string($category_name) . "'";
						$result = mysql_query($query);
						if (mysql_num_rows($result)){
							$message .= 'Department name already exists for this project.<br/>';
						} else {
							$query = "insert into healingcrystals_project_milestone_categories (project_id, category_name, date_added) values ('" . $project_id . "', '" . mysql_real_escape_string($category_name) . "', now())";
							if (mysql_query($query)){
								$categories[] = array('id' 			=> mysql_insert_id(), 
													  'name' 		=> $category_name, 
													  'added' 		=> 'NEW ENTRY', 
													  'modified' 	=> '--');
								$message .= 'New milestones department, created';
								$message_color = 'green';
							} else {
								$message .= 'Error encountered while saving information';
							}
						}
					}
				}
			} elseif ($action=='edit'){
				$categories = array();
				$action = 'editnextstep';
			} elseif ($action=='delete'){
				$categories = array();
				$action = 'deletenextstep';
			}
		}
		mysql_close($link);
		$this->smarty->assign('categories', $categories);
		$this->smarty->assign('categories_count', count($categories));
		$this->smarty->assign('cat_name_lable_content', $label);
		$this->smarty->assign('message', $message);
		$this->smarty->assign('message_color', $message_color);
		$this->smarty->assign('action', $action);
		$this->smarty->assign('selected_category_id', $selected_category_id);
		$this->smarty->assign('selected_category_name', $selected_category_name);
    } // manage_milestone_categories
    //EOF: task 04 | AD

    /*function get_project_name($project_id, &$link){
    	$resp = 'Unknown';
    	$query = "select name from healingcrystals_projects where id='" . $project_id . "'";
    	$result = mysql_query($query, $link);
    	if (mysql_num_rows($result)){
    		$info = mysql_fetch_assoc($result);
    		$resp = $info['name'];
    	}
    	return $resp;
    }
    
    function get_object_info($object_string, $project_id, $id, &$link, $is_integer_field_1 = false){
    	$resp = array('type' => 'Unknown', 'name' => 'Unknown');
    	$type = ucfirst(substr($object_string, 0, -1));

    	$query = "select type, name 
				  from healingcrystals_project_objects 
				  where project_id='" . $project_id . "' and type='" . $type . "' and " . ($is_integer_field_1 ? " integer_field_1='" : " id='") . 
				  $id . "'";
		$result = mysql_query($query, $link);
		if (mysql_num_rows($result)){
			$info = mysql_fetch_assoc($result);
			$resp['type'] = $info['type'];
			if ($is_integer_field_1){
				$resp['type'] .= '#' . $id;
			}
			$resp['name'] = $info['name'];
		}

		return $resp;
    }
    
    function recent_pages_1(){
    	$recent_pages = array();
    	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
    	mysql_select_db(DB_NAME);
    	$query = "select * from healingcrystals_user_visited_pages where user_id='" . $this->logged_user->getId() . "' order by access_time desc";
    	$result = mysql_query($query);
    	$count = 0;
    	while($info = mysql_fetch_assoc($result)){
    		$desc = $info['page_url'];
    		$pos = strpos($desc, 'path_info');
    		if ($pos!==false){
    			$desc = str_replace('path_info=', '', substr(str_replace('%2F', '/', $desc), $pos));
    			$pos = strpos($desc, 'projects');
    			if ($pos!==false and $pos===0){
    				$split = explode('/', $desc);
    				$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
    				mysql_select_db(DB_NAME);
    				switch(count($split)){
    					case 2:
    						$desc = 'Project: ' . $this->get_project_name($split[1], $link);
    						break;
    					case 3:
    						switch($split[2]){
    							case 'tickets':
    							case 'milestones':
    							case 'people':
    							case 'checklists':
    							case 'discussions':
    							case 'calendar':
    							case 'files':
    							case 'pages':
    								$desc = 'Project: ' . $this->get_project_name($split[1], $link) . ' | ' . ucfirst($split[2]); 
    								break;
    						}
    						break;
    					case 4:
    						$pos = strpos($split[3], '&');
    						if ($pos!==false){
    							
    						} else {
    							switch($split[2]){
	    							case 'tickets':
	    							case 'milestones':
	    							case 'checklists':
	    							case 'discussions':
	    							case 'pages':
	    								$resp = $this->get_object_info($split[2], $split[1], $split[3], $link, ($split[2]=='tickets' ? true : false));
	    								$desc = $resp['type'] . ': ' . $resp['name'];
	    								break;
    							}
    						}
    						break;
    				}
    				mysql_close($link);
    			} else {
    				$desc = $info['page_url'];
    			}
    		}
    		$recent_pages[] = array('url' => $info['page_url'],
							'access_time' => date('m-d-Y G:i:s', strtotime($info['access_time'])), 
							'count' => ++$count, 
							'description' => $desc);
    	}
    	mysql_close($link);
    	$this->smarty->assign('recent_pages', $recent_pages);
    	$this->smarty->assign('page_tab', 'recent_pages');
    } */ 
    
    
  }

?>