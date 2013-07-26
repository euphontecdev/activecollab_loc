<?php

  /**
   * StatusUpdate class
   * 
   * @package activeCollab.modules.status
   * @subpackage models
   */
  class StatusUpdate extends BaseStatusUpdate {
    
    /**
     * Cached array of replies
     *
     * @var array
     */
    var $replies = false;
    
    /**
     * Return array of status message replies
     *
     * @param void
     * @return array
     */
    function getReplies() {
      if($this->replies === false) {
        $this->replies = StatusUpdates::findByParent($this);
      } // if
      return $this->replies;
    } // getReplies
    
    /**
     * Returns true if this message has replies
     *
     * @param boolean $preload
     * @return boolean
     */
    function hasReplies($preload = false) {
      if($preload) {
        $this->getReplies();
        return is_foreachable($this->replies);
      } else {
        return (boolean) StatusUpdates::countByParent($this);
      } // if
    } // hasReplies
    
    /**
     * Parent message
     *
     * @var StatusUpdate
     */
    var $parent = false;
    
    /**
     * Return parent update instance
     *
     * @param void
     * @return StatusUpdate
     */
    function getParent() {
      if($this->parent === false) {
        $this->parent = $this->getParentId() ? StatusUpdates::findById($this->getParentId()) : null;
      } // if
      return $this->parent;
    } // getParent
    
    /**
     * Describe this object
     *
     * @param User $user
     * @param array $additional
     * @return array
     */
    function describe($user, $additional = null) {
      $result = array(
        'message' => $this->getMessage(),
        'created_on' => $this->getCreatedOn(),
      );
      
      $created_by = $this->getCreatedBy();
      if(instance_of($created_by, 'User')) {
        $result['created_by'] = array(
          'id'         => $created_by->getId(),
          'name'       => $created_by->getDisplayName(),
          'avatar_url' => $created_by->getAvatarUrl(true)
        );
      } // if
      
      return $result;
    } // describe
    
    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------
    
    /**
     * Check if $user can view this message
     *
     * @param User $user
     * @return boolean
     */
    function canView($user) {
      return $this->getCreatedById() == $user->getId() || in_array($this->getCreatedById(), $user->visibleUserIds());
    } // canView
    
    /**
     * Check if $user can delete this message
     *
     * @param User $user
     * @return boolean
     */
    function canDelete($user) {
      if($user->isAdministrator()) {
        return true;
      } // if
      
      if($this->getCreatedById() == $user->getId()) {
        $created_on = $this->getCreatedOn();
        return $created_on->getTimestamp() + 1800 < time(); // Available for delete for 30 minutes after the post
      } // if
      
      return false;
    } // canDelete
    
    // ---------------------------------------------------
    //  URL-s
    // ---------------------------------------------------
    
    /**
     * View status update url
     * 
     * @param null
     * @return string
     */
    function getViewUrl() {
      return assemble_url('status_update', array(
        'status_update_id' => $this->getParentId() ? $this->getParentId() : $this->getId()
      ));
    } // getViewUrl
    
    /**
     * Get reply to status update URL
     *
     * @param void
     * @return string
     */
    function getReplyUrl() {
      return assemble_url('status_update_reply', array(
        'status_update_id' => $this->getParentId() ? $this->getParentId() : $this->getId()
      ));
    } // getReplyUrl
    
    // ---------------------------------------------------
    //  System
    // ---------------------------------------------------
    
    /**
     * Validate before save
     *
     * @param ValidationErrors $errors
     * @return null
     */
    function validate(&$errors) {
      if(!$this->validatePresenceOf('message')) {
        $errors->addError(lang('Status message is required'), 'message');
      } // if
    } // validate
    
    /**
     * Save this status message into database
     *
     * @param void
     * @return boolean
     */
    function save() {
      $now = new DateTimeValue();
      if($this->isNew()) {
        $this->setLastUpdateOn($now);
      } // if
      
      db_begin_work();
      
      $save = parent::save();
      if($save && !is_error($save)) {
        $parent = $this->getParent();
        if(instance_of($parent, 'StatusUpdate')) {
          $parent->setLastUpdateOn($now);
          $parent->save();
        } // if
        
        db_commit();
        return true;
      } else {
        db_rollback();
        return $save;
      } // if
    } // save
    
    /**
     * Drop this record from database
     *
     * @param void
     * @return boolean
     */
    function delete() {
      db_begin_work();
      
      $delete = parent::delete();
      if($delete && !is_error($delete)) {
        StatusUpdates::dropByParent($this);
        
        db_commit();
        return true;
      } else {
        db_rollback();
        return $delete;
      } // if
    } // delete

  }

?>