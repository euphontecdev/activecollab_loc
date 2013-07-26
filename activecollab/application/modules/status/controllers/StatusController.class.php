<?php

  // We need projects controller
  use_controller('application', SYSTEM_MODULE);

  /**
   * Status controller
   *
   * @package activeCollab.modules.status
   * @subpackage controllers
   */
  class StatusController extends ApplicationController {
    
    /**
     * Active module
     *
     * @var string
     */
    var $active_module = STATUS_MODULE;
    
    /**
     * Controller name
     *
     * @var string
     */
    var $controller_name = 'status';
    
    /**
     * Array of available API actions
     *
     * @var array
     */
    var $api_actions = array('index', 'add');
    
    /**
     * Selected status update
     *
     * @var StatusUpdate
     */
    var $active_status_update;
    
    /**
     * Selected menu item
     *
     * @var string
     */
    var $current_menu_item = 'status';
    
    /**
     * Constructor method
     *
     * @param string $request
     * @return StatusController
     */
    function __construct($request) {
      parent::__construct($request);
      
      if(!$this->logged_user->isAdministrator() && !$this->logged_user->getSystemPermission('can_use_status_updates')) {
        if($this->request->getAction() == 'count_new_messages') {
          die('0');
        } else {
          $this->httpError(HTTP_ERR_FORBIDDEN);
        } // if
      } // if
      
      $this->wireframe->addBreadCrumb(lang('Status'), assemble_url('status_updates'));
      
      $status_update_id = (integer) $this->request->get('status_update_id');
      if($status_update_id) {
        $this->active_status_update = StatusUpdates::findById($status_update_id);
      } // if
      
      if(instance_of($this->active_status_update, 'StatusUpdate')) {
        $this->wireframe->addBreadCrumb(lang('Status Update #:id', array('id' => $this->active_status_update->getId())), $this->active_status_update->getViewUrl());
      } else {
        $this->active_status_update = new StatusUpdate();
      } // if
      
      $this->smarty->assign(array(
        'active_status_update' => $this->active_status_update,
        'add_status_message_url' => assemble_url('status_updates_add'),
      ));
    } // __construct
    
    /**
     * Index page action
     * 
     * @param void
     * @return void
     */
    function index() {
      UserConfigOptions::setValue('status_update_last_visited', new DateTimeValue(), $this->logged_user);
      
      // Popup
      if($this->request->isAsyncCall()) {
        $this->skip_layout = true;
        
        $this->setTemplate(array(
          'template' => 'popup',
          'controller' => 'status',
          'module' => STATUS_MODULE,
        ));
        
        $last_visit = UserConfigOptions::getValue('status_update_last_visited', $this->logged_user);
        $new_messages_count = StatusUpdates::countNewMessagesForUser($this->logged_user, $last_visit);
        
        $limit = $new_messages_count > 10 ? $new_messages_count : 10;
        
        $latest_status_updates = StatusUpdates::findVisibleForUser($this->logged_user, $limit);
        $this->smarty->assign(array(
          'status_updates' => $latest_status_updates,
          "rss_url" => assemble_url('status_updates_rss', array(
            'token' => $this->logged_user->getToken(true),
           )),
        ));
        
      // Archive
      } else {
        $this->setTemplate(array(
          'template' => 'messages',
          'controller' => 'status',
          'module' => STATUS_MODULE,
        ));
      
        $visible_users = $this->logged_user->visibleUserIds(); // We'll need them in several places
        
        $selected_user_id = $this->request->getId('user_id');
        if($selected_user_id) {
          if(!in_array($selected_user_id, $visible_users)) {
            $this->httpError(HTTP_ERR_FORBIDDEN);
          } // if
          
          $selected_user = Users::findById($selected_user_id);
          if(!instance_of($selected_user, 'User')) {
            $this->httpError(HTTP_ERR_NOT_FOUND);
          } // if
        } else {
          $selected_user = null;
        } // if
        
        if($this->request->isApiCall()) {
          if($selected_user) {
            $this->serveData(StatusUpdates::findByUser($selected_user), 'messages');
          } else {
            $this->serveData(StatusUpdates::findVisibleForUser($this->logged_user, 50), 'messages');
          } // if
        } else {
          $per_page = 15; // Messages per page
          $page = $this->request->getPage();
          
          if($selected_user) {
            $rss_url = assemble_url('status_updates_rss', array(
              'user_id' => $selected_user_id,
              'token' => $this->logged_user->getToken(true),
            ));
            $rss_title = clean($selected_user->getDisplayName()). ': '.lang('Status Updates');
            list($status_updates, $pagination) = StatusUpdates::paginateByUser($selected_user, $page, $per_page);
            $this->smarty->assign(array(
              'selected_user' => $selected_user,
              'rss_url' => $rss_url
            ));
          } else {
            $rss_url = assemble_url('status_updates_rss', array('token' => $this->logged_user->getToken(true)));
            $rss_title = lang('Status Updates');
            list($status_updates, $pagination) = StatusUpdates::paginateByUserIds($visible_users, $page, $per_page);
            $this->smarty->assign(array(
              'rss_url' => $rss_url
            ));
          } // if
          
          $this->wireframe->addRssFeed($rss_title, $rss_url, FEED_RSS);
          
          $this->smarty->assign(array(
            'visible_users' => Users::findUsersDetails($visible_users),
            'status_updates' => $status_updates,
            'pagination' => $pagination
          ));
        } // if
      } // if
    } // index
    
    /**
     * Display status update details
     * 
     * @param null
     * @return null
     */
    function view() {
      if($this->active_status_update->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      if(!$this->active_status_update->canView($this->logged_user)) {
        $this->httpError(HTTP_ERR_FORBIDDEN);
      } // if
      
      $this->smarty->assign('active_status_update_author', $this->active_status_update->getCreatedBy());
      
      if($this->request->isSubmitted()) {
        db_begin_work();
        
        $reply_data = $this->request->post('reply');
        
        $reply = new StatusUpdate();
        $reply->setAttributes(array(
          'message' => array_var($reply_data, 'message'),
          'parent_id' => $this->active_status_update->getId(),
        ));
        $reply->setCreatedBy($this->logged_user);
        
        $save = $reply->save();
        if($save && !is_error($save)) {
          UserConfigOptions::setValue('status_update_last_visited', new DateTimeValue(), $this->logged_user);
          
          db_commit();
          
          if($this->request->isAsyncCall()) {
            $this->smarty->assign('status_update_reply', $reply);
            print $this->smarty->fetch(get_template_path('_status_reply_row', 'status', STATUS_MODULE));
            die();
          } else {
            flash_success('Reply has been successfully posted');
          } // if
        } else {
          db_rollback();
          
          if($this->request->isAsyncCall()) {
            $this->serveData($save);
          } else {
            flash_error('Failed to post reply to selected status message');
          } // if
        } // if
        
        $this->redirectToUrl($this->active_status_update->getViewUrl());
      } // if
    } // view
    
    /**
     * Add status message
     * 
     * @param void
     * @return void
     */
    function add() {
      if(!$this->request->isApiCall() && !$this->request->isAsyncCall()) {
        $this->httpError(HTTP_ERR_BAD_REQUEST);
      } // if
      
      $this->wireframe->print_button = false;
      
      if($this->request->isSubmitted()) {
        $status_data = $this->request->post('status');
        
        $status_message = array_var($status_data, 'message');
        $status_message_parent = null;
        
        $status_message_parent_id = (integer) array_var($status_data, 'parent_id');
        if($status_message_parent_id) {
          $status_message_parent = StatusUpdates::findById($status_message_parent_id);
        } // if
        
        $this->active_status_update = new StatusUpdate();
        
        $this->active_status_update->setAttributes($status_data);
        $this->active_status_update->setCreatedById($this->logged_user->getId());
        $this->active_status_update->setCreatedByName($this->logged_user->getName());
        $this->active_status_update->setCreatedByEmail($this->logged_user->getEmail());
        
        $save = $this->active_status_update->save();
        if(!$save || is_error($save)) {
          if($this->request->isApiCall()) {
            $this->serveData($save);
          } else {
            $this->httpError(HTTP_ERR_OPERATION_FAILED);
          } // if
        } // if
        
        if($this->request->isApiCall()) {
          $this->serveData($this->active_status_update, 'message');
        } else {
          UserConfigOptions::setValue('status_update_last_visited', new DateTimeValue(), $this->logged_user);
          
          if(instance_of($status_message_parent, 'StatusUpdate')) {
            $this->smarty->assign('status_update', $status_message_parent);
          } else {
            $this->smarty->assign('status_update', $this->active_status_update);
          } // if
          
          print $this->smarty->fetch(get_template_path('_status_row', $this->controller_name, STATUS_MODULE));
          
          die();
        } // if
      } else {
        $this->httpError(HTTP_ERR_BAD_REQUEST);
      } // if
    } // add
    
    /**
     * Drop selected status update
     *
     * @param void
     * @return null
     */
    function delete() {
      if($this->active_status_update->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      if(!$this->active_status_update->canDelete($this->logged_user)) {
        $this->httpError(HTTP_ERR_FORBIDDEN);
      } // if
      
      if($this->request->isSubmitted()) {
        $delete = $this->active_status_update->delete();
        if($delete && !is_error($delete)) {
          if($this->request->isApiCall()) {
            $this->httpOk();
          } else {
            flash_success('Selected status update has been successfully deleted');
          } // if
        } else {
          if($this->request->isApiCall()) {
            $this->serveData($delete);
          } else {
            flash_success('Failed to delete selected status update');
          } // if
        } // if
        
        $this->redirectToReferer(assemble_url('status_updates'));
      } else {
        $this->httpError(HTTP_ERR_BAD_REQUEST);
      } // if
    } // delete
    
    /**
     * Provide ajax functionality for menu badge
     * 
     * @param void
     * @return void
     */
    function count_new_messages() {
      $last_visit = UserConfigOptions::getValue('status_update_last_visited', $this->logged_user);
      echo StatusUpdates::countNewMessagesForUser($this->logged_user, $last_visit);
      die();
    } // count_new_messages
    
    /**
     * Rss for status updates
     * 
     * @param void
     * @return void
     */
    function rss() {
      require_once ANGIE_PATH . '/classes/feed/init.php';
      
      $archive_url = assemble_url('status_updates');
    	
    	$selected_user = $this->request->get('user_id');
    	if ($selected_user) {
        if (!in_array($selected_user, $this->logged_user->visibleUserIds())) {
          $this->httpError(HTTP_ERR_FORBIDDEN);
        } // if
    	  
    	  $user = Users::findById($selected_user);
    	  if (!instance_of($user, 'User')) {
    	    $this->httpError(HTTP_ERR_NOT_FOUND);
    	  } // if
    	  
    	  $archive_url = assemble_url('status_updates', array('user_id' => $user->getId()));
    	  $latest_status_updates = StatusUpdates::findByUser($user, 20);
    	  $feed = new Feed(lang(":display_name's Status Updates", array('display_name' => $user->getDisplayName())), $archive_url);
    	} else {
      	$latest_status_updates = StatusUpdates::findVisibleForUser($this->logged_user, 20);
      	$feed = new Feed(lang('Status Updates'), $archive_url);
    	} // if
    	
    	if(is_foreachable($latest_status_updates)) {
    	  foreach($latest_status_updates as $status_update) {
    	    $this->smarty->assign(array(
    	      'status_update' => $status_update,
    	    ));
    	    
    	    $item = new FeedItem(str_excerpt($status_update->getMessage(), 50), $status_update->getViewUrl(), $this->smarty->fetch(get_template_path('feed_item', 'status', STATUS_MODULE)), $status_update->getLastUpdateOn());
    	    $item->setId($status_update->getId());
    	    $feed->addItem($item);
    	  } // foreach
    	} // if
    	
      print render_rss_feed($feed);
      die();
    } // rss
    
  }
  
?>