<?php

  /**
   * Single comment
   *
   * @package activeCollab.modules.resources
   * @subpackage models
   * CHANGE LOG
   * 19 March2012 (SA) Ticket #760: fix broken permalinks
   */
  class Comment extends ProjectObject {
    
    /**
     * View comment route name
     *
     * @var string
     */
    var $view_route_name = 'project_comment';
    
    /**
     * Portal view route name
     *
     * @var string
     */
    var $portal_view_route_name = 'portal_comment';
    
    /**
     * Name of the route used for delete URL
     *
     * @var string
     */
    var $edit_route_name = 'project_comment_edit';
    //BOF:mod 20120217
    var $print_route_name = 'project_comment_print';
    //EOF:mod 20120217
    /**
     * Name of the object ID variable used alongside project_id when URL-s are 
     * generated (eg. comment_id, task_id, message_category_id etc)
     *
     * @var string
     */
    var $object_id_param_name = 'comment_id';
    
    /**
     * Define fields used by this project object
     *
     * @var array
     */
    var $fields = array(
      'id', 
      'type', 'source', 'module', 
      'project_id', 
      'parent_id', 'parent_type', 
      'body', 
      'state', 'visibility', 
      'created_on', 'created_by_id', 'created_by_name', 'created_by_email',
      'updated_on', 'updated_by_id',
      'version',
	  //BOF:mod 20121030
	  'integer_field_2', 
	  //EOF:mod 20121030
    );
    
    /**
     * Does this object has attachments
     *
     * @var boolean
     */
    var $can_have_attachments = true;
    
    /**
      * If true email notification on comment creation will be sent
      *
      * @var boolean
      */
    var $send_notification = true;
    
    /**
     * Construct comment object
     *
     * @param mixed $id
     * @return Comment
     */
    function __construct($id = null) {
      $this->setModule(RESOURCES_MODULE);
      $this->protect[] = 'name';
      
      parent::__construct($id);
    } // __construct
    
    /**
      * Return comment name
      *
      * @param void
      * @return string
      */
    function getName() {
      $parent = $this->getParent();
      return instance_of($parent, 'ProjectObject') ? lang('Comment on :name', array('name' => $parent->getName()), false) : lang('Comment');
    } // getName
    
    /**
     * Return comment subsribers (uses parents subscribers)
     *
     * @param void
     * @return array
     */
    function getSubscribers() {
      $parent = $this->getParent();
      if(instance_of($parent, 'ProjectObject') && $parent->can_have_subscribers) {
        return $parent->getSubscribers();
      } // if
      return array(); // nothing...
    } // getSubscribers
    
    /**
     * Return project tab based on parent object
     *
     * @param void
     * @return string
     */
    function getProjectTab() {
    	$parent = $this->getParent();
    	return instance_of($parent, 'ProjectObject') ? $parent->getProjectTab() : 'overview';
    } // getProjectTab
    
    /**
     * Prepare project section breadcrumb when this object is accessed directly 
     * and not through module controller
     *
     * @param Wireframe $wireframe
     * @return null
     */
    function prepareProjectSectionBreadcrumb(&$wireframe) {
      $parent = $this->getParent();
      if(instance_of($parent, 'ProjectObject')) {
        return $parent->prepareProjectSectionBreadcrumb($wireframe);
      } // if
    } // prepareProjectSectionBreadcrumb
    
    /**
     * Return context in which notifications are sent
     * 
     * Reply to notification will submit comment for context object, if context 
     * is commentable
     *
     * @param void
     * @return ProjectObject
     */
    function getNotificationContext() {
      $parent = $this->getParent();
      return instance_of($parent, 'ProjectObject') && $parent->can_have_comments ? $parent : null;
    } // getNotificationContext
    
    /**
     * Describe comment
     *
     * @param User $user
     * @return array
     */
    function describe($user, $additional = null) {
      if(is_array($additional)) {
        $additional['describe_attachments'] = true;
      } else {
        $additional = array('describe_attachments' => true);
      } // if
      
      return parent::describe($user, $additional);
    } // describe
    
    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------
    
    /**
     * Returns true if $user can access this attachment
     *
     * @param void
     * @return boolean
     */
    function canView($user) {
    	$parent = $this->getParent();
    	return instance_of($parent, 'ProjectObject') && $parent->canView($user);
    } // canView
    
    /**
     * Returns true if anonymous users can access this comment via portal
     *
     * @param Portal $portal
     * @return boolean
     */
    function canViewByPortal($portal) {
    	$parent = $this->getParent();
    	return instance_of($parent, 'ProjectObject') && $parent->canViewByPortal($portal);
    } // canViewByPortal
    
    // ---------------------------------------------------
    //  URL-s
    // ---------------------------------------------------
    
    /**
     * Cached value of real view URL
     *
     * @var string
     */
    var $real_view_url = false;
    
    /**
     * Return real view URL
     *
     * @param void
     * @return string
     */
    function getRealViewUrl($add_page = false) {
      if($this->real_view_url === false) {
        if($this->getState() == STATE_DELETED) {
          $this->real_view_url = assemble_url('trash');
        } // if
        
        $parent = $this->getParent();
        if(!instance_of($parent, 'ProjectObject')) {
          return new InvalidInstanceError('parent', $parent, 'ProjectObject', 'Parent is expected to be an instance of ProjectObject class');
        } // if
        
        if($parent->comments_per_page) {
          $logged_user = get_logged_user();
          
          $page = ceil(Comments::findCommentNum($this, $parent->getState(), $logged_user->getVisibility()) / $parent->comments_per_page);
          if ($add_page){
			$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
			mysql_select_db(DB_NAME);
			$query = "select * from healingcrystals_project_objects where type='Comment' and parent_id='" . $parent->getId() . "' and state='" . STATE_VISIBLE . "' and visibility='" . VISIBILITY_NORMAL . "' order by created_on desc";
			$result = mysql_query($query, $link);
			$count = 0;
			while($info = mysql_fetch_assoc($result)){
				$count++;
				if ($info['id']==$this->getId()){
					break;
				}
			}
			if (!empty($count)){
				$page = ceil($count / $parent->comments_per_page);
			}
			mysql_close($link);
                        //19 March2012 (SA) Ticket #760: fix broken permalinksBOF
          	$this->real_view_url = $parent->getViewUrl($page) . '&show_all=1#comment' . $this->getId();
          } else {
          	$this->real_view_url = $parent->getViewUrl() . '&show_all=1#comment' . $this->getId();
          }
        } else {
          $this->real_view_url = $parent->getViewUrl() . '&show_all=1#comment' . $this->getId();
          //19 March2012 (SA) Ticket #760: fix broken permalinks EOF
        } // if
      } // if
      return $this->real_view_url;
    } // getRealViewUrl
    
    /**
     * Cached value of real view URL
     *
     * @var string
     */
    var $portal_real_view_url = false;
    
    /**
     * Return portal real view URL
     *
     * @param Portal $portal
     * @return string
     */
    function getPortalRealViewUrl($portal) {
    	if($this->portal_real_view_url === false) {
    		$parent = $this->getParent();
    		if(!instance_of($parent, 'ProjectObject')) {
    			return new InvalidInstanceError('parent', $parent, 'ProjectObject', 'Parent is expected to be an instance of ProjectObject class');
    		} // if
    		//19 March2012 (SA) Ticket #760: fix broken permalinks BOF
    		if($parent->comments_per_page) {
    			$page = ceil(Comments::findCommentNum($this, STATE_VISIBLE, VISIBILITY_NORMAL) / $parent->comments_per_page);
    			$this->portal_real_view_url = $parent->getPortalViewUrl($portal, $page) . '&show_all=1#comment' . $this->getId();
    		} else {
    			$this->portal_real_view_url = $parent->getPortalViewUrl($portal) . '&show_all=1#comment' . $this->getId();
    		} // if
                //19 March2012 (SA) Ticket #760: fix broken permalinks EOF
    	} // if
    	return $this->portal_real_view_url;
    } // getPortalRealViewUrl
    
    // ---------------------------------------------------
    //  System
    // ---------------------------------------------------
    
    /**
     * Move object to trash
     * 
     * If $silent is set to true subobject will not add Moved to Trash info into 
     * activity log
     *
     * @param boolean $silent
     * @return boolean
     */
    function moveToTrash($silent = false) {
      $trash = parent::moveToTrash($silent);
      if($trash && !is_error($trash)) {
        $parent = $this->getParent();
        if(instance_of($parent, 'ProjectObject')) {
          $refresh = $parent->refreshCommentsCount();
        } // if
      } // if
      return $trash;
    } // moveToTrash
    
    /**
     * Restore object and subitems from trash
     *
     * @param boolean $check_parent_state
     * @return boolean
     */
    function restoreFromTrash($check_parent_state = true) {
      $restore = parent::restoreFromTrash($check_parent_state);
      if($restore && !is_error($restore)) {
        $parent = $this->getParent();
        if(instance_of($parent, 'ProjectObject')) {
          $parent->refreshCommentsCount();
        } // if
      } // if
      return $restore;
    } // restoreFromTrash
    
    /**
     * Validate before save
     *
     * @param ValidationErrors $errors
     * @return null
     */
    function validate(&$errors) {
    	//BOF:mod
    	/*
    	//EOF:mod
      if(strlen(trim(strip_tags($this->getBody()))) < 3) {
        $errors->addError(lang('Minimal content value is 3 characters'), 'body');
      } // if
      //BOF:mod
      */
      //EOF:mod
      
      parent::validate($errors, true);
    } // validate
    
    /**
     * Save comment into database
     *
     * @param void
     * @return boolean
     */
    function save($is_email_comment = '') {
      $is_new = $this->isNew();
      $save = parent::save();
      
      if($save && !is_error($save)) {
        $parent = $this->getParent();
        if($is_new) {
        	//BOF: mod
        	if ($is_email_comment){
        	//EOF: mod
        		event_trigger('on_comment_added', array(&$this, &$parent));
        	//BOF: mod
        	}
          	//EOF: mod
        } else {
          event_trigger('on_comment_updated', array(&$this, &$parent));
        } // if
      } // if
      
      return $save;
    } // save
    
    /**
     * Remove comment from database
     *
     * @param void
     * @return boolean
     */
    function delete() {
      $delete = parent::delete();
      
      if($delete && !is_error($delete)) {
        $parent = $this->getParent();
        event_trigger('on_comment_deleted', array(&$this, &$parent));
      } // if
      
      return $delete;
    } // delete
    
    //BOF:task_1260
    var $is_action_request_user = false;
    var $is_fyi_user 			= false;
    
    function set_action_request_n_fyi_flag($logged_user){
		$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
		mysql_select_db(DB_NAME);
		$query = "select * from healingcrystals_assignments_action_request where comment_id='" . $this->getId() . "' and user_id='" . $logged_user->getId() . "'";
		$result = mysql_query($query, $link);
		while ($entry = mysql_fetch_assoc($result)){
			if ($entry['is_action_request']=='1'){
				$this->is_action_request_user = true;
			}
			if ($entry['is_fyi']=='1'){
				$this->is_fyi_user = true;
			}
		}
		mysql_close($link);
    }
    //EOF:task_1260
    
  } // Comment

?>