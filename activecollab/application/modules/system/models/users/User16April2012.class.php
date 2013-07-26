<?php

  /**
   * User class
   *
   * @package activeCollab.modules.system
   * @subpackage models
   */
  class User extends BaseUser {

    /**
     * List of protected fields (can't be set using setAttributes() method)
     *
     * @var array
     */
  	var $protect = array(
  	  'session_id',
  	  'token',
  	  'last_login_on',
  	  'last_visit_on',
  	  'last_activity_on',
  	  'auto_assign',
  	  'auto_assign_role_id',
  	  'auto_assign_permissions',
  	  'password_reset_key',
  	  'password_reset_on'
  	);

    /**
     * Return users display name
     *
     * @param boolean $short
     * @return string
     */
    function getName($short = false) {
      return $this->getDisplayName($short);
    } // getName

    /**
     * Return first name
     *
     * If $force_value is true and first name value is not present, system will
     * use email address part before @domain.tld
     *
     * @param boolean $force_value
     * @return string
     */
    function getFirstName($force_value = false) {
      $result = parent::getFirstName();
      if(empty($result) && $force_value) {
        $email = $this->getEmail();
        return substr_utf($email, 0, strpos_utf($email, '@'));
      } // if
      return $result;
    } // getFirstName

    /**
     * Parent company
     *
     * @var Company
     */
    var $company = false;

    /**
     * Return parent company
     *
     * @param void
     * @return Company
     */
    function getCompany() {
      if($this->company === false) {
        $this->company = Companies::findById($this->getCompanyId());
      } // if
      return $this->company;
    } // getCompany

    /**
     * Return company name
     *
     * @param void
     * @return string
     */
    function getCompanyName() {
    	$company = $this->getCompany();
    	return instance_of($company, 'Company') ? $company->getName() : lang('-- Unknown --');
    } // getCompanyName

    /**
     * Cached user role
     *
     * @var Role
     */
    var $role = false;

    /**
     * Return user role
     *
     * @param void
     * @return Role
     */
    function getRole() {
      if($this->role === false) {
        $this->role = $this->getRoleId() > 0 ? Roles::findById($this->getRoleId()) : null;
      } // if
      return $this->role;
    } // getRole

    /**
     * Return language for given user
     *
     * @var Language
     */
    var $language = false;

    /**
     * Return users language
     *
     * @param void
     * @return Language
     */
    function getLanguage() {
      if(!LOCALIZATION_ENABLED) {
        return null;
      } // if

      if($this->language === false) {
        $language_id = UserConfigOptions::getValue('language', $this);
        $this->language = $language_id ? Languages::findById($language_id) : null;
      } // if

      return $this->language;
    } // getLanguage

    /**
     * Cached array of all user projects
     *
     * @var array
     */
    var $projects = false;

    /**
     * Return all projects this user can access
     *
     * @param void
     * @return array
     */
    function getProjects() {
      if($this->projects === false) {
        if($this->isAdministrator() || $this->isProjectManager()) {
          $this->projects = Projects::findAll();
        } else {
          $this->projects = Projects::findByUser($this);
        } // if
      } // if
      return $this->projects;
    } // getProjects

    /**
     * Cached array of active projects
     *
     * @var array
     */
    var $active_projects = false;

    /**
     * Return all active project this user is involved in
     *
     * @param boolean $pinned_first
     * @return array
     */
    function getActiveProjects($pinned_first = false) {
      if($this->active_projects === false) {
        $this->active_projects = Projects::findByUser($this, PROJECT_STATUS_ACTIVE);
      } // if

      if($pinned_first) {
        if(is_foreachable($this->active_projects)) {
          $pinned = array();
          $not_pinned = array();

          foreach($this->active_projects as $active_project) {
            if(PinnedProjects::isPinned($active_project, $this)) {
              $pinned[] = $active_project;
            } else {
              $not_pinned[] = $active_project;
            } // if
          } // foreach

          if(count($pinned) && count($not_pinned)) {
            return array_merge($pinned, $not_pinned);
          } elseif(count($pinned)) {
            return $pinned;
          } elseif(count($not_pinned)) {
            return $not_pinned;
          } else {
            return null;
          } // if

        } else {
          return null;
        } // if
      } else {
        return $this->active_projects;
      } // if
    } // getActiveProjects

    /**
     * Cached display name
     *
     * @var string
     */
    var $display_name = false;

    /**
     * Return display name (first name and last name)
     *
     * @param boolean $short
     * @return string
     */
    function getDisplayName($short = false) {
      if($this->display_name === false) {
        if($this->getFirstName() && $this->getLastName()) {
          if($short) {
            return $this->getFirstName() . ' ' . substr_utf($this->getLastName(), 0, 1) . '.';
          } // if

          $this->display_name = $this->getFirstName() . ' ' . $this->getLastName();
        } elseif($this->getFirstName()) {
          $this->display_name = $this->getFirstName();
        } elseif($this->getLastName()) {
          $this->display_name = $this->getLastName();
        } else {
          $this->display_name = $this->getEmail();
        } // if
      } // if

      return $this->display_name;
    } // getDisplayName

    /**
     * Cached list of user options (indexed by user)
     *
     * @var array
     */
    var $options = array();

    /**
     * Return array of this $user can do to this user account
     *
     * @param User $user
     * @return array
     */
    function getOptions($user) {
      if(!isset($this->options[$user->getId()])) {
        $options = new NamedList();

        if($this->canChangeRole($user)) {
          $options->add('edit_company_and_role', array(
            'text' => lang('Company and Role'),
            'url'  => $this->getEditCompanyAndRoleUrl(),
          ));
        } // if

      	if($this->canEdit($user)) {
          $options->add('edit_profile', array(
            'text' => lang('Update Profile'),
            'url'  => $this->getEditProfileUrl(),
          ));

          $options->add('edit_settings', array(
            'text' => lang('Change Settings'),
            'url'  => $this->getEditSettingsUrl(),
          ));

          $options->add('edit_password', array(
            'text' => lang('Change Password'),
            'url'  => $this->getEditPasswordUrl(),
          ));

          $options->add('edit_avatar', array(
            'text' => lang('Change Avatar'),
            'url'  => $this->getEditAvatarUrl(),
          ));

          $options->add('api', array(
            'text' => lang('API Settings'),
            'url'  => $this->getApiSettingsUrl(),
          ));
        } // if

        if($this->canDelete($user)) {
          $options->add('delete', array(
            'text'    => lang('Delete'),
            'url'     => $this->getDeleteUrl(),
            'method'  => 'post',
            'confirm' => lang('Are you sure that you want to delete this user account? There is no undo!'),
          ));
        } // if

        if($user->isProjectManager()) {
          $options->add('add_to_projects', array(
            'text' => lang('Add to Projects'),
            'url'  => $this->getAddToProjectsUrl(),
          ));
        } // if

        if($this->canSendWelcomeMessage($user)) {
          $options->add('send_welcome_message', array(
            'text' => lang('Send Welcome Message'),
            'url'  => $this->getSendWelcomeMessageUrl(),
          ));
        } // if

        if($this->canViewActivities($user)) {
        	$options->add('recent_activities', array(
            'text' => lang('Recent Activities'),
            'url'  => $this->getRecentActivitiesUrl(),
          ));
        } // if

        event_trigger('on_user_options', array(&$this, &$options, &$user));
        $this->options[$user->getId()] = $options;
      } // if
      return $this->options[$user->getId()];
    } // getOptions

    /**
     * Cached quick options
     *
     * @var array
     */
    var $quick_options = array();

    /**
     * Return quick user options
     *
     * @param User $user
     * @return array
     */
    function getQuickOptions($user) {
      if(!isset($this->quick_options[$user->getId()])) {
        $options = new NamedList();

      	if($this->canEdit($user)) {
          $options->add('edit_profile', array(
            'text' => lang('Update Profile'),
            'url'  => $this->getEditProfileUrl(),
          ));

          $options->add('edit_settings', array(
            'text' => lang('Change Settings'),
            'url'  => $this->getEditSettingsUrl(),
          ));

          $options->add('edit_password', array(
            'text' => lang('Change Password'),
            'url'  => $this->getEditPasswordUrl(),
          ));

          $options->add('api', array(
            'text' => lang('API Settings'),
            'url'  => $this->getApiSettingsUrl(),
          ));
        } // if

        event_trigger('on_user_quick_options', array(&$this, &$options, &$user));
        $this->quick_options[$user->getId()] = $options;
      } // if
      return $this->quick_options[$user->getId()];
    } // getQuickOptions

    /**
     * Raw password value before it is encoded
     *
     * @var string
     */
    var $raw_password = false;

    /**
     * Set field value
     *
     * @param string $field
     * @param mixed $value
     * @return mixed
     */
    function setFieldValue($field, $value) {
      if($field == 'password' && !$this->is_loading) {
        $this->raw_password = $value;
        $this->resetToken();

        $value = sha1(LICENSE_KEY . $value);
      } // if
      return parent::setFieldValue($field, $value);
    } // setFieldValue

    /**
     * Returns true if we have a valid password
     *
     * @param string $password
     * @return boolean
     */
    function isCurrentPassword($password) {
      return $this->getPassword() === sha1(LICENSE_KEY . $password);
    } // isCurrentPassword

    /**
     * Generate new token for this user
     *
     * @param void
     * @return null
     */
    function resetToken() {
      $this->setToken(make_string(40));
    } // resetToken

    /**
     * Cached array of visible user ID-s
     *
     * @var array
     */
    var $visible_user_ids = false;

    /**
     * Returns an array of visible users
     *
     * @param void
     * @return array
     */
    function visibleUserIds() {
      if($this->visible_user_ids === false) {
        $this->visible_user_ids = Users::findVisibleUserIds($this);
      } // if
      return $this->visible_user_ids;
    } // visibleUserIds

    /**
     * Cached array of visible company ID-s
     *
     * @var array
     */
    var $visible_company_ids = false;

    /**
     * Returns array of companies this user can see
     *
     * @param void
     * @return array
     */
    function visibleCompanyIds() {
      if($this->visible_company_ids === false) {
        $this->visible_company_ids = Users::findVisibleCompanyIds($this);
      } // if
      return $this->visible_company_ids;
    } // visibleCompanyIds

    /**
     * Describe user
     *
     * @param User $user
     * @param array $additional
     * @return array
     */
    function describe($user, $additional = null) {
      $result = array(
        'id'                 => $this->getId(),
        'first_name'         => $this->getFirstName(),
        'last_name'          => $this->getLastName(),
        'email'              => $this->getEmail(),
        'last_visit_on'      => $this->getLastVisitOn(),
        'permalink'          => $this->getViewUrl(),
        'role_id'            => $this->getRoleId(),
        'is_administrator'   => $this->isAdministrator(),
        'is_project_manager' => $this->isProjectManager(),
        'is_people_manager'  => $this->isPeopleManager(),
      );

      if($user->isAdministrator() || $user->isPeopleManager()) {
        $result['token'] = $this->getToken();
      } // if

      if(array_var($additional, 'describe_company')) {
        $company = $this->getCompany();
        if(instance_of($company, 'Company')) {
          $result['company'] = $company->describe($user);
        } // if
      } // if

      if(!isset($result['company'])) {
        $result['company_id'] = $this->getCompanyId();
      } // if

      if(array_var($additional, 'describe_avatar')) {
        $result['avatar_url'] = $this->getAvatarUrl(true);
      } // if

      return $result;
    } // describe

    /**
     * Prefered locale
     *
     * @var string
     */
    var $locale = false;

    /**
     * Return prefered locale
     *
     * @param string $default
     * @return string
     */
    function getLocale($default = null) {
    	if($this->locale === false) {
    	  $language_id = UserConfigOptions::getValue('language', $this);
    	  if($language_id) {
    	    $language = Languages::findById($language_id);
    	    if(instance_of($language, 'Language')) {
    	      $this->locale = $language->getLocale();
    	    } // if
    	  } // if

    	  if($this->locale === false) {
    	    $this->locale = $default === null ? BUILT_IN_LOCALE : $default;
    	  } // if
    	} // if

    	return $this->locale;
    } // getLocale

    /**
     * Cached last visit on value
     *
     * @var DateTimeValue
     */
    var $last_visit_on = false;

    /**
     * Return users last visit
     *
     * @param void
     * @return DateTimeValue
     */
    function getLastVisitOn() {
      if($this->last_visit_on === false) {
      	$last_visit = parent::getLastVisitOn();
      	$this->last_visit_on = instance_of($last_visit, 'DateTimeValue') ? $last_visit : new DateTimeValue(filectime(ENVIRONMENT_PATH . '/config/config.php'));
      } // if
      return $this->last_visit_on;
    } // getLastVisitOn

    /**
     * Return token
     *
     * @param boolean $include_user_id
     * @return string
     */
    function getToken($include_user_id = false) {
      return $include_user_id ? $this->getId() . '-' . parent::getToken() : parent::getToken();
    } // getToken

    // ---------------------------------------------------
    //  Utils
    // ---------------------------------------------------

    /**
     * Returns true if this user have permissions to see private objects
     *
     * @param void
     * @return boolean
     */
    function canSeePrivate() {
    	return $this->isProjectManager() || (boolean) $this->getSystemPermission('can_see_private_objects');
    } // canSeePrivate

    /**
     * Cached values of can see milestones permissions
     *
     * @var array
     */
    var $can_see_milestones = array();

    /**
     * Returns true if user can see milestones in $project
     *
     * @param Project $project
     * @return boolean
     */
    function canSeeMilestones($project) {
      $project_id = $project->getId();
    	if(!isset($this->can_see_milestones[$project_id])) {
    	  $this->can_see_milestones[$project_id] = $this->getProjectPermission('milestone', $project) >= PROJECT_PERMISSION_ACCESS;
    	} // if
    	return $this->can_see_milestones[$project_id];
    } // canSeeMilestones

    /**
     * Is this user member of owner company
     *
     * @var boolean
     */
    var $is_owner = null;

    /**
     * Returns true if this user is member of owner company
     *
     * @param void
     * @return boolean
     */
    function isOwner() {
      if($this->is_owner === null) {
        $company = $this->getCompany();
        $this->is_owner = instance_of($company, 'Company') ? $company->getIsOwner() : false;
      } // if
      return $this->is_owner;
    } // isOwner

    /**
     * Does this user have administration permissions
     *
     * @var boolean
     */
    var $is_administrator = null;

    /**
     * Returns true only if this person has administration permissions
     *
     * @param void
     * @return boolean
     */
    function isAdministrator() {
      if($this->is_administrator === null) {
        $this->is_administrator = $this->getSystemPermission('admin_access');
      } // if
      return $this->is_administrator;
    } // isAdministrator

    /**
     * Check if this user is the only administrator
     *
     * @param void
     * @return boolean
     */
    function isOnlyAdministrator() {
      return $this->isAdministrator() && (Users::countAdministrators() == 1);
    } // isOnlyAdministrator

    /**
     * Cached is people manager permission value
     *
     * @var boolean
     */
    var $is_people_manager = null;

    /**
     * Returns true if this user has management permissions in People section
     *
     * @param void
     * @return boolean
     */
    function isPeopleManager() {
      if($this->is_people_manager === null) {
        if($this->isAdministrator()) {
          $this->is_people_manager = true;
        } else {
          $this->is_people_manager = $this->getSystemPermission('people_management');
        } // if
      } // if
      return $this->is_people_manager;
    } // isPeopleManager

    /**
     * Cached value of is project manager permissions
     *
     * @var boolean
     */
    var $is_project_manager = null;

    /**
     * Returns true if this user has global project management permissions
     *
     * @param void
     * @return boolean
     */
    function isProjectManager() {
      if($this->is_project_manager === null) {
        if($this->isAdministrator()) {
          $this->is_project_manager = true;
        } else {
          $this->is_project_manager = $this->getSystemPermission('project_management');
        } // if
      } // if
      return $this->is_project_manager;
    } // isProjectManager

    /**
     * Returns true if this user is part of a specific project
     *
     * @param Project $project
     * @param boolean $use_cache
     * @return boolean
     */
    function isProjectMember($project, $use_cache = true) {
      return ProjectUsers::isProjectMember($this, $project, $use_cache);
    } // isProjectMember

    /**
     * Check if this user is manager of a given company
     *
     * If $company is missing user will be checked agains his own company
     *
     * @param Company $company
     * @return boolean
     */
    function isCompanyManager($company) {
      if($this->isAdministrator() || $this->isPeopleManager()) {
        return true;
      } // if

      return $this->getCompanyId() == $company->getId() && $this->getSystemPermission('manage_company_details');
    } // isCompanyManager

    /**
     * Returns true if this person is leader of specified project
     *
     * @param Project $project
     * @return boolean
     */
    function isProjectLeader($project) {
      return $this->getId() == $project->getLeaderId();
    } // isProjectLeader

    /**
     * Cached visibility
     *
     * @var boolean
     */
    var $visibility = false;

    /**
     * Returns optimal visibility for this user
     *
     * If this user is member of owner company he will be able to see private
     * objects. If not he will be able to see only normal and public objects
     *
     * @param void
     * @return boolean
     */
    function getVisibility() {
      if($this->visibility === false) {
        $this->visibility = $this->canSeePrivate() ? VISIBILITY_PRIVATE : VISIBILITY_NORMAL;
      } // if
      return $this->visibility;
    } // getVisibility

    /**
     * Return system permission value
     *
     * @param string $name
     * @return boolean
     */
    function getSystemPermission($name) {
    	$role = $this->getRole();
      if(instance_of($role, 'Role')) {
        return (boolean) $role->getPermissionValue($name);
      } else {
        return false;
      } // if
    } // getSystemPermission

    // ---------------------------------------------------
    //  Project roles and permissions
    // ---------------------------------------------------

    /**
     * Cached project user instances
     *
     * @var array
     */
    var $project_users = array();

    /**
     * Return project user instance for this user and $project
     *
     * @param Project $project
     * @return ProjectUser
     */
    function getProjectUserInstance($project) {
      $project_id = $project->getId();
      if(!array_key_exists($project_id, $this->project_users)) {
        $this->project_users[$project->getId()] = ProjectUsers::findById(array(
      	  'user_id' => $this->getId(),
      	  'project_id' => $project->getId(),
      	));
      } // if
      return $this->project_users[$project->getId()];
    } // getProjectUserInstance

    /**
     * Return role this user has on a project
     *
     * If project is administrator, project manager or project leader NULL is
     * returned
     *
     * @param Project $project
     * @return Role
     */
    function getProjectRole($project) {
      $project_user = $this->getProjectUserInstance($project);
      return instance_of($project_user, 'ProjectUser') ? $project_user->getRole() : null;
    } // getProjectRole

    /**
     * Return verbose project role that this user have on $project
     *
     * @param Project $project
     * @return string
     */
    function getVerboseProjectRole($project) {
      if($this->isProjectLeader($project)) {
        return lang('Project Leader');
      } else if($this->isAdministrator()) {
        return lang('System Administrator');
      } elseif($this->isProjectManager()) {
        return lang('Project Manager');
      } else {
        $role = $this->getProjectRole($project);
        if(instance_of($role, 'Role')) {
          return $role->getname();
        } else {
          return lang('Custom');
        } // if
      } // if
    } // getVerboseProjectRole

    /**
     * Return project value
     *
     * @param string $name
     * @param Project $project
     * @return integer
     */
    function getProjectPermission($name, $project) {
      if($this->isAdministrator() || $this->isProjectManager() || $this->isProjectLeader($project)) {
        return PROJECT_PERMISSION_MANAGE;
      } // if

    	$project_user = $this->getProjectUserInstance($project);
    	return instance_of($project_user, 'ProjectUser') ? $project_user->getPermissionValue($name) : PROJECT_PERMISSION_NONE;
    } // getProjectPermission

    /**
     * Return config option value
     *
     * @param string $name
     * @return mixed
     */
    function getConfigValue($name) {
      return UserConfigOptions::getValue($name, $this);
    } // getConfigValue

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Check if $user can view recent activities of the selected user
     *
     * @param User $user
     * @return boolean
     */
    function canViewActivities($user) {
    	return $user->isAdministrator() || $user->isProjectManager();
    } // canViewActivities

    /**
     * Can a specific user create a new user account in given company
     *
     * @param User $user
     * @param Company $to_company
     * @return boolean
     */
    function canAdd($user, $to_company) {
      return $user->isAdministrator() || $user->isPeopleManager() || $user->isCompanyManager($to_company);
    } // canAdd

    /**
     * Check if $user can update this profile
     *
     * @param User $user
     * @return boolean
     */
    function canEdit($user) {
      if($user->getId() == $this->getId()) {
        return true; // user can change his own account
      } // if

      return $user->isCompanyManager($this->getCompany());
    } // canEdit

    /**
     * Returns true if $user can change this users role
     *
     * @param User $user
     * @return boolean
     */
    function canChangeRole($user) {
    	return $user->isAdministrator() || $user->isPeopleManager();
    } // canChangeRole

    /**
     * Check if $user can delete this profile
     *
     * @param User $user
     * @return boolean
     */
    function canDelete($user) {
      if($this->getId() == $user->getId()) {
        return false; // user cannot delete himself
      } // if

      if($this->isAdministrator() && !$user->isAdministrator()) {
        return false; // only administrators can delete administrators
      } // if

      return $user->isPeopleManager();
    } // canDelete

    /**
     * Returns true if $user can change this users permissions on a $project
     *
     * @param User $user
     * @param Project $project
     * @return boolean
     */
    function canChangeProjectPermissions($user, $project) {
      if($user->isProjectLeader($project) || $user->isProjectManager() || $user->isAdministrator()) {
        return false;
      } // if

      return $this->isProjectLeader($project) || $this->isPeopleManager() || $this->isAdministrator();
    } // canChangeProjectPermissions

    /**
     * Check if $user can remove this user from $project
     *
     * @param User $user
     * @param Project $project
     * @return boolean
     */
    function canRemoveFromProject($user, $project) {
      if($user->isProjectLeader($project)) {
        return false;
      } // if

      return $this->isProjectLeader($project) || $this->isPeopleManager() || $this->isAdministrator();
    } // canRemoveFromProject

    /**
     * Returns true if $user can (re)send welcome message
     *
     * @param User $user
     * @return boolean
     */
    function canSendWelcomeMessage($user) {
      return $user->isPeopleManager() || $user->isCompanyManager($this->getCompany());
    } // canSendWelcomeMessage

    // ---------------------------------------------------
    //  Avatars
    // ---------------------------------------------------

    /**
     * Get Avatar URL
     *
     * @param boolean $large
     * @return string
     */
    function getAvatarUrl($large = false) {
      $size = $large ? '40x40' : '16x16';
      $mtime = filemtime($this->getAvatarPath($size));

      if($mtime === false) {
        return ROOT_URL . "/avatars/default.$size.gif";
      } else {
        return ROOT_URL . '/avatars/' . $this->getId() . ".$size.jpg?updated_on=$mtime";
      } // if
    } // getAvatarUrl

    /**
     * Get Avatar Path
     *
     * @param boolean $large
     * @return string
     */
    function getAvatarPath($large = false) {
      $size = $large ? '40x40' : '16x16';
      return ENVIRONMENT_PATH . '/' . PUBLIC_FOLDER_NAME . '/avatars/' . $this->getId() . ".$size.jpg";
    } // getAvatarPath

    // ---------------------------------------------------
    //  URL-s
    // ---------------------------------------------------

    /**
     * Return View URL
     *
     * @param void
     * @return null
     */
    function getViewUrl() {
    	return assemble_url('people_company_user', array(
    	  'company_id' => $this->getCompanyId(),
    	  'user_id'    => $this->getId(),
    	));
    } // getViewUrl

    /**
     * Return Recent Activities URL
     *
     * @param void
     * @return null
     */
    function getRecentActivitiesUrl() {
    	return assemble_url('people_company_user_recent_activities', array(
    	  'company_id' => $this->getCompanyId(),
    	  'user_id'    => $this->getId(),
    	));
    } // getRecentActivitiesUrl

    /**
     * Get edit user profile URL
     *
     * @param void
     * @return string
     */
    function getEditProfileUrl() {
      return assemble_url('people_company_user_edit_profile', array(
        'company_id' => $this->getCompanyId(),
        'user_id'    => $this->getId(),
      ));
    } // getEditProfileUrl

    /**
     * Get edit user settings URL
     *
     * @param void
     * @return string
     */
    function getEditSettingsUrl() {
      return assemble_url('people_company_user_edit_settings', array(
        'company_id' => $this->getCompanyId(),
        'user_id'    => $this->getId(),
      ));
    } // getEditSettingsUrl

    /**
     * Return edit company and role URL
     *
     * @param void
     * @return string
     */
    function getEditCompanyAndRoleUrl() {
      return assemble_url('people_company_user_edit_company_and_role', array(
        'company_id' => $this->getCompanyId(),
        'user_id'    => $this->getId(),
      ));
    } // getEditCompanyAndRoleUrl

    /**
     * Get edit password URL
     *
     * @param void
     * @return string
     */
    function getEditPasswordUrl() {
      return assemble_url('people_company_user_edit_password', array(
        'company_id' => $this->getCompanyId(),
        'user_id'    => $this->getId(),
      ));
    } // getEditPasswordUrl

    /**
     * get edit user avatar URL
     *
     * @param void
     * @return string
     */
    function getEditAvatarUrl() {
      return assemble_url('people_company_user_edit_avatar', array(
        'company_id' => $this->getCompanyId(),
        'user_id'    => $this->getId(),
      ));
    } // getEditAvatarUrl

    /**
     * Get Delete Avatar URL
     *
     * @param void
     * @return string
     */
    function getDeleteAvatarUrl() {
    	return assemble_url('people_company_user_delete_avatar', array(
    	 'company_id' => $this->getCompanyId(),
    	 'user_id' => $this->getId(),
    	));
    } // getDeleteAvatarUrl

    /**
     * Return delete user URL
     *
     * @param void
     * @return string
     */
    function getDeleteUrl() {
      return assemble_url('people_company_user_delete', array(
        'company_id' => $this->getCompanyId(),
        'user_id'    => $this->getId(),
      ));
    } // getDeleteUrl

    /**
     * Return unsubscribe from object URL
     *
     * @param ProjectObject $object
     * @return string
     */
    function getUnsubscribeUrl($object) {
      return assemble_url('project_object_unsubscribe_user', array(
        'project_id' => $object->getProjectId(),
        'object_id' => $object->getId(),
        'user_id' => $this->getId(),
      ));
    } // getUnsubscribeUrl

    /**
     * Return API settings URL
     *
     * @param void
     * @return string
     */
    function getApiSettingsUrl() {
      return assemble_url('people_company_user_api', array(
        'company_id' => $this->getCompanyId(),
        'user_id'    => $this->getId(),
      ));
    } // getApiSettingsUrl

    /**
     * Return reset API key URL
     *
     * @param void
     * @return string
     */
    function getResetApiKeyUrl() {
      return assemble_url('people_company_user_api_reset_key', array(
        'company_id' => $this->getCompanyId(),
        'user_id'    => $this->getId(),
      ));
    } // getResetApiKeyUrl

    /**
     * Return reset password URL
     *
     * @param void
     * @return string
     */
    function getResetPasswordUrl() {
    	return assemble_url('reset_password', array(
    	  'user_id' => $this->getId(),
    	  'code' => $this->getPasswordResetKey(),
    	));
    } // getResetPasswordUrl

    /**
     * Return add to projects URL
     *
     * @param void
     * @return string
     */
    function getAddToProjectsUrl() {
      return assemble_url('people_company_user_add_to_projects', array(
        'company_id' => $this->getCompanyId(),
        'user_id'    => $this->getId(),
      ));
    } // getAddToProjectsUrl

    /**
     * Return send welcome message URL
     *
     * @param void
     * @return string
     */
    function getSendWelcomeMessageUrl() {
      return assemble_url('people_company_user_send_welcome_message', array(
        'company_id' => $this->getCompanyId(),
        'user_id'    => $this->getId(),
      ));
    } // getSendWelcomeMessageUrl

    // ---------------------------------------------------
    //  Getters and Setters
    // ---------------------------------------------------

    /**
     * Set auto-assign data
     *
     * @param boolean $enabled
     * @param integer $role_id
     * @param array $permissions
     * @return null
     */
    function setAutoAssignData($enabled, $role_id, $permissions) {
    	if($enabled) {
    	  $this->setAutoAssign(true);
  	    if($role_id) {
  	      $this->setAutoAssignRoleId($role_id);
  	      $this->setAutoAssignPermissions(null);
  	    } else {
  	      $this->setAutoAssignRoleId(0);
  	      $this->setAutoAssignPermissions($permissions);
  	    } // if
  	  } else {
  	    $this->setAutoAssign(false);
  	    $this->setAutoAssignRoleId(0);
  	    $this->setAutoAssignPermissions(null);
  	  } // if
    } // setAutoAssignData

    /**
     * Return auto assign role based on auto assign role ID
     *
     * @param void
     * @return Role
     */
    function getAutoAssignRole() {
    	$role_id = $this->getAutoAssignRoleId();
    	return $role_id ? Roles::findById($role_id) : null;
    } // getAutoAssignRole

    /**
     * Return auto assign permissions
     *
     * @param void
     * @return mixed
     */
    function getAutoAssignPermissions() {
    	$raw = parent::getAutoAssignPermissions();
    	return $raw ? unserialize($raw) : null;
    } // getAutoAssignPermissions

    /**
     * Set auto assign permissions
     *
     * @param mixed $value
     * @return mixed
     */
    function setAutoAssignPermissions($value) {
    	return parent::setAutoAssignPermissions(serialize($value));
    } // setAutoAssignPermissions

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
      if($this->validatePresenceOf('email', 5)) {
        if(is_valid_email($this->getEmail())) {
          if(!$this->validateUniquenessOf('email')) {
            $errors->addError(lang('Email address you provided is already in use'), 'email');
          } // if
        } else {
          $errors->addError(lang('Email value is not valid'), 'email');
        } // if
      } else {
        $errors->addError(lang('Email value is required'), 'email');
      } // if

      if($this->isNew()) {
        if(strlen(trim($this->raw_password)) < 3) {
          $errors->addError(lang('Minimal password length is 3 characters'), 'password');
        } // if
      } else {
        if($this->raw_password !== false && strlen(trim($this->raw_password)) < 3) {
          $errors->addError(lang('Minimal password length is 3 characters'), 'password');
        } // if
      } // if

      $company_id = $this->getCompanyId();
      if($company_id) {
        $company = Companies::findById($company_id);
        if(!instance_of($company, 'Company')) {
          $errors->addError(lang('Selected company does not exist'), 'company_id');
        } // if
      } else {
        $errors->addError(lang('Please select company'), 'company_id');
      } // if

      if(!$this->validatePresenceOf('role_id')) {
        $errors->addError(lang('Role is required'), 'role_id');
      } // if
    } // validate

    /**
     * Save user into the database
     *
     * @param void
     * @return boolean
     */
    function save() {
      $modified_fields = $this->modified_fields;
      $is_new = $this->isNew();

      if($is_new && ($this->getToken() == '')) {
        $this->resetToken();
      } // if

      $save = parent::save();
      if($save && !is_error($save)) {
        if($is_new || in_array('email', $modified_fields) || in_array('first_name', $modified_fields) || in_array('last_name', $modified_fields)) {
          $content = $this->getEmail();
          if($this->getFirstName() || $this->getLastName()) {
            $content .= "\n\n" . trim($this->getFirstName() . ' ' . $this->getLastName());
          } // if

          search_index_set($this->getId(), 'User', $content);
          cache_remove_by_pattern('object_assignments_*_rendered');
        } // if

        // Role changed?
        if(in_array('role_id', $modified_fields)) {
          clean_user_permissions_cache($this);
        } // if
      } // if

      return $save;
    } // save

    /**
     * Delete from database
     *
     * @param void
     * @return boolean
     */
    function delete() {
      db_begin_work();
      $delete = parent::delete();
      if($delete && !is_error($delete)) {
        unlink($this->getAvatarPath());
      	unlink($this->getAvatarPath(true));

        ProjectUsers::deleteByUser($this);
        Assignments::deleteByUser($this);
        Subscriptions::deleteByUser($this);
        StarredObjects::deleteByUser($this);
        PinnedProjects::deleteByUser($this);
        UserConfigOptions::deleteByUser($this);
        Reminders::deleteByUser($this);

        search_index_remove($this->getId(), 'User');

        $cleanup = array();
        event_trigger('on_user_cleanup', array(&$cleanup));

        if(is_foreachable($cleanup)) {
          foreach($cleanup as $table_name => $fields) {
            foreach($fields as $field) {
              $condition = '';
              if(is_array($field)) {
                $id_field    = array_var($field, 'id');
                $name_field  = array_var($field, 'name');
                $email_field = array_var($field, 'email');
                $condition   = array_var($field, 'condition');
              } else {
                $id_field    = $field . '_id';
                $name_field  = $field . '_name';
                $email_field = $field . '_email';
              } // if

              if($condition) {
                db_execute('UPDATE ' . TABLE_PREFIX . "$table_name SET $id_field = 0, $name_field = ?, $email_field = ? WHERE $id_field = ? AND $condition", $this->getName(), $this->getEmail(), $this->getId());
              } else {
                db_execute('UPDATE ' . TABLE_PREFIX . "$table_name SET $id_field = 0, $name_field = ?, $email_field = ? WHERE $id_field = ?", $this->getName(), $this->getEmail(), $this->getId());
              } // if
            } // foreach
          } // foreach
        } // if

        db_commit();
        return true;
      } else {
        db_rollback();
        return $delete;
      } // if
    } // delete

    //BOF:mod 20110715 ticketid246
    function getCustomTabsInfo(){
        $custom_tabs_info = array(array('name' => 'Tab1', 'url' => 'javascript://'),
                                  array('name' => 'Tab2', 'url' => 'javascript://'),
                                  array('name' => 'Tab3', 'url' => 'javascript://'), );
        $link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
        mysql_select_db(DB_NAME);
        $sql = "select * from healingcrystals_user_custom_tabs where user_id='" . $this->getId() . "'";
        $result = mysql_query($sql);
        if (mysql_num_rows($result)){
            while($entry = mysql_fetch_assoc($result)){
               $custom_tabs_info[$entry['tab_index']-1]['name'] = $entry['tab_description'];
               $custom_tabs_info[$entry['tab_index']-1]['url'] = $entry['tab_link'];
            }
        }
        mysql_close($link);
        return $custom_tabs_info;
    }
    //EOF:mod 20110715 ticketid246

    //BOF:mod 20110722
    function getHomeTabContent($user_id = '', $tickets_due_flag = ''){
        require_once SMARTY_PATH . '/plugins/modifier.html_excerpt.php';
        if (empty($user_id)){
            $user_id = $this->getId();
        }
      	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
      	mysql_select_db(DB_NAME, $link);

        $query = "select setting_value from healingcrystals_user_settings where user_id='" . $user_id . "' and setting_type='HOMETAB_LAYOUT'";
        $result = mysql_query($query);
        if (mysql_num_rows($result)){
            $info = mysql_fetch_assoc($result);
            $layout_type = $info['setting_value'];
        } else {
            $layout_type = 'summary';
        }

        //possible values for tickets_due_flag: 'due' or 'all'
        if (empty($tickets_due_flag)){
            $tickets_due_flag = 'due';
        }
        $query = '';
        if ($tickets_due_flag=='due'){
            $query = "SELECT distinct a.id, a.type, b.user_id, c.reminder_date, IF(c.reminder_date is null, a.due_on, IF(a.due_on<=c.reminder_date, a.due_on, c.reminder_date)) as old_date
                      FROM healingcrystals_project_objects a
                      inner join healingcrystals_assignments b on a.id=b.object_id
                      left outer join healingcrystals_project_object_misc c on (a.id=c.object_id)
                      where a.state='" . STATE_VISIBLE . "' and b.user_id='" . $user_id . "' and b.is_owner='1' and
                      (a.type='Task' or a.type='Ticket') and (a.completed_on is null or a.completed_on='') and
                      ((c.reminder_date is not null and c.reminder_date<>'0000-00-00' and c.reminder_date<=now()) or (a.due_on is not null and a.due_on<=now()) )
                       order by b.user_id, IFNULL(a.priority, '0') desc, old_date";
        } elseif ($tickets_due_flag=='all'){
            $query = "SELECT distinct a.id, a.type, b.user_id
                      FROM healingcrystals_project_objects a
                      inner join healingcrystals_assignments b on a.id=b.object_id
                      left outer join healingcrystals_project_object_misc c on (a.id=c.object_id)
                      where a.state='" . STATE_VISIBLE . "' and b.user_id='" . $user_id . "' and b.is_owner='1' and
                      (a.type='Task' or a.type='Ticket') and (a.completed_on is null or a.completed_on='') and
                      a.due_on is not null
                      order by b.user_id, IFNULL(a.priority, '0') desc";
        }
        if (!empty($query)){
            $result = mysql_query($query, $link);
            $tickets_due_info = array();
            if (mysql_num_rows($result)){
                while ($entry = mysql_fetch_assoc($result)){
                    $tickets_due_info[] = array('type' => $entry['type'], 'id' => $entry['id'], 'reminder' => $entry['reminder_date']);
                }
            }
        }

        //BOF:mod 20111103 #462
        /*
        //EOF:mod 20111103 #462
	$query = "(select b.id, d.id as parent_ref , a.date_added as date_value, e.priority as prio, c.name as project_name
		 from healingcrystals_assignments_action_request a
		 inner join healingcrystals_project_objects b on a.comment_id=b.id
		 inner join healingcrystals_project_objects d on b.parent_id=d.id
		 left outer join healingcrystals_project_objects e on d.milestone_id=e.id
		 inner join healingcrystals_projects c on b.project_id=c.id
		 where b.state='" . STATE_VISIBLE . "' and a.user_id='" . $user_id . "' and a.is_action_request='1'
                 and d.state='" . STATE_VISIBLE . "' and (d.completed_on is null or d.completed_on='') )
                 union
                 (select '' as id, a.object_id as parent_ref, b.created_on as date_value, e.priority as prio, c.name as project_name
                 from healingcrystals_assignments_flag_fyi_actionrequest a
                 inner join healingcrystals_project_objects b on a.object_id=b.id
                 left outer join healingcrystals_project_objects e on b.milestone_id=e.id
                 inner join healingcrystals_projects c on b.project_id=c.id
                 where a.user_id='" . $user_id . "' and flag_actionrequest='1' and b.state='" . STATE_VISIBLE . "'
                 and (b.completed_on is null or b.completed_on=''))
		 order by prio desc, project_name, date_value desc";
        //BOF:mod 20111103 #462
        */
	$query = "(select b.id, d.id as parent_ref , a.date_added as date_value, e.priority as prio, c.name as project_name
		 from healingcrystals_assignments_action_request a
		 inner join healingcrystals_project_objects b on a.comment_id=b.id
		 inner join healingcrystals_project_objects d on b.parent_id=d.id
		 left outer join healingcrystals_project_objects e on d.milestone_id=e.id
		 inner join healingcrystals_projects c on b.project_id=c.id
		 where b.state='" . STATE_VISIBLE . "' and a.user_id='" . $user_id . "' and a.is_action_request='1'
                 and d.state='" . STATE_VISIBLE . "'  )
                 union
                 (select '' as id, a.object_id as parent_ref, b.created_on as date_value, e.priority as prio, c.name as project_name
                 from healingcrystals_assignments_flag_fyi_actionrequest a
                 inner join healingcrystals_project_objects b on a.object_id=b.id
                 left outer join healingcrystals_project_objects e on b.milestone_id=e.id
                 inner join healingcrystals_projects c on b.project_id=c.id
                 where a.user_id='" . $user_id . "' and flag_actionrequest='1' and b.state='" . STATE_VISIBLE . "'
                  )
		 order by prio desc, project_name, date_value desc";
        //EOF:mod 20111103 #462
	$result = mysql_query($query, $link);
	$action_request_info = array();
	if (mysql_num_rows($result)){
            while ($entry = mysql_fetch_assoc($result)){
                if ($layout_type=='summary'){
                    if (!array_key_exists((string)$entry['parent_ref'], $action_request_info)){
                        $action_request_info[(string)$entry['parent_ref']] = array();
                    }
                    $action_request_info[(string)$entry['parent_ref']][] = $entry['id'];
                } else {
                    //BOF:mod 20111019 #448
                    if (empty($entry['id'])){
                      $action_request_info[] = $entry['parent_ref'];
                    } else {
                    //EOF:mod 20111019 #448
                      $action_request_info[] = $entry['id'];
                    //BOF:mod 20111019
                    }
                    //EOF:Mod 20111019
                }
            }
	}

        //BOF:mod 20111103 #462
        /*
        //EOF:mod 20111103 #462
	$query = "(select b.id, d.id as parent_ref , a.date_added as date_value, e.priority as prio, c.name as project_name
		 from healingcrystals_assignments_action_request a
		 inner join healingcrystals_project_objects b on a.comment_id=b.id
		 inner join healingcrystals_project_objects d on b.parent_id=d.id
		 left outer join healingcrystals_project_objects e on d.milestone_id=e.id
		 inner join healingcrystals_projects c on b.project_id=c.id
		 where b.state='" . STATE_VISIBLE . "' and a.user_id='" . $user_id . "' and a.is_fyi='1'
                 and d.state='" . STATE_VISIBLE . "' and (d.completed_on is null or d.completed_on='') )
                 union
                 (select '' as id, a.object_id as parent_ref, b.created_on as date_value, e.priority as prio, c.name as project_name
                 from healingcrystals_assignments_flag_fyi_actionrequest a
                 inner join healingcrystals_project_objects b on a.object_id=b.id
                 left outer join healingcrystals_project_objects e on b.milestone_id=e.id
                 inner join healingcrystals_projects c on b.project_id=c.id
                 where a.user_id='" . $user_id . "' and flag_fyi='1' and b.state='" . STATE_VISIBLE . "'
                 and (b.completed_on is null or b.completed_on=''))
		 order by prio desc, project_name, date_value desc";
        //BOF:mod 20111103 #462
        */
	$query = "(select b.id, d.id as parent_ref , a.date_added as date_value, e.priority as prio, c.name as project_name
		 from healingcrystals_assignments_action_request a
		 inner join healingcrystals_project_objects b on a.comment_id=b.id
		 inner join healingcrystals_project_objects d on b.parent_id=d.id
		 left outer join healingcrystals_project_objects e on d.milestone_id=e.id
		 inner join healingcrystals_projects c on b.project_id=c.id
		 where b.state='" . STATE_VISIBLE . "' and a.user_id='" . $user_id . "' and a.is_fyi='1'
                 and d.state='" . STATE_VISIBLE . "' )
                 union
                 (select '' as id, a.object_id as parent_ref, b.created_on as date_value, e.priority as prio, c.name as project_name
                 from healingcrystals_assignments_flag_fyi_actionrequest a
                 inner join healingcrystals_project_objects b on a.object_id=b.id
                 left outer join healingcrystals_project_objects e on b.milestone_id=e.id
                 inner join healingcrystals_projects c on b.project_id=c.id
                 where a.user_id='" . $user_id . "' and flag_fyi='1' and b.state='" . STATE_VISIBLE . "'
                  )
		 order by prio desc, project_name, date_value desc";
        //EOF:mod 20111103 #462
	$result = mysql_query($query, $link);
	$info = array();
	if (mysql_num_rows($result)){
            while ($entry = mysql_fetch_assoc($result)){
                if ($layout_type=='summary'){
                    if (!array_key_exists((string)$entry['parent_ref'], $info)){
                        $info[(string)$entry['parent_ref']] = array();
                    }
                    $info[(string)$entry['parent_ref']][] = $entry['id'];
                } else {
                    //BOF:mod 20111019 #448
                    if (empty($entry['id'])){
                      $info[] = $entry['parent_ref'];
                    } else {
                    //EOF:mod 20111019 #448
                        $info[] = $entry['id'];
                    //BOF:mod 20111019
                    }
                    //EOF:Mod 20111019
                }
            }
	}

	$search_from = time() - (24 * 60 * 60);
	$query = "SELECT a.id, b.user_id FROM healingcrystals_project_objects a
                 inner join healingcrystals_assignments b on a.id=b.object_id
		 where a.state='" . STATE_VISIBLE . "' and b.user_id='" . $user_id . "' and a.type='Ticket' and a.completed_on is not null and
		 a.completed_on >= '" . date('Y-m-d H:i', $search_from) . "' order by b.user_id, a.due_on";
	$result = mysql_query($query, $link);
	$completed_objects = array();
	if (mysql_num_rows($result)){
            while ($entry = mysql_fetch_assoc($result)){
                $query02 = "select max(id) as comment_id from healingcrystals_project_objects where parent_id='" . $entry['id'] . "' and parent_type='Ticket' and type='Comment'";
		$result02 = mysql_query($query02);
		if (mysql_num_rows($result02)){
                    $comment_info = mysql_fetch_assoc($result02);
		}
		$completed_objects[] = array('ticket_id' => $entry['id'], 'last_comment_id' => $comment_info['comment_id']);
            }
	}

	$fyi_updates = array();
	$query = "select id, fyi_user_id, object_id from healingcrystals_project_objects_to_fyi_users where fyi_user_id='" . $user_id . "' and user_intimated_on is null";
	$result = mysql_query($query);
	if (mysql_num_rows($result)){
            while ($entry = mysql_fetch_assoc($result)){
                $fyi_updates[] = $entry['object_id'];
            }
	}

        $fyi_table_start = '
                <a name="fyi"></a>
                <table style="border:1px solid black;">
                    <tr>
                        <td colspan="4">&nbsp;</td>
                    </tr>' .
                    (count($info) ? '
                    <tr>
                        <th colspan="4">FYI Comment(s)</th>
                    </tr>
                    <tr>
                        <td colspan="4">&nbsp;</td>
                    </tr>' : '');
        $fyi_table_end = '
                </table>';

        $action_request_table_start = '
                <a name="action_request"></a>
                <table style="border:1px solid black;">
                    <tr>
                        <td colspan="4">&nbsp;</td>
                    </tr>' .
                    (count($action_request_info) ? '
                    <tr>
                        <th colspan="4">Action Request Comment(s)</th>
                    </tr>
                    <tr>
                        <td colspan="4">&nbsp;</td>
                    </tr>' : '');
        $action_request_table_end = '
                </table>';

        $completed_objects_table_start = '
                <a name="closed_tickets"></a>
                <table style="border:1px solid black;">
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>' .
                    (count($completed_objects) ? '
                    <tr>
                        <th colspan="2">Recently Closed Tickets</th>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>' : '');
        $completed_objects_table_end = '
                </table>';

        $fyi_updates_table_start = '
                <table style="border:1px solid black;">
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>' .
                    (count($fyi_updates) ? '
                    <tr>
                        <th colspan="2">FYI Updates</th>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>' : '');
        $fyi_updates_table_end = '
                </table>';

        $tickets_due_table_start = '
                <a name="tickets_due"></a>
                <table style="border:1px solid black;">
                    <tr><td colspan="4">&nbsp;</td></tr>
                    <tr>
                        <th colspan="4">
                            Due Tickets & Tasks:
                            <div style="float:right;font-weight:normal;">
                                <table cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td valign="middle">
                                            <input type="radio" onclick="location.href=\'' . assemble_url('goto_home_tab') . '&due_flag=due\'" name="objects_due" value="due" style="width:20px;" ' . ($tickets_due_flag=='due' ? ' checked="true"' : '') . ' />
                                        </td>
                                        <td valign="middle">Show Due Dates/Reminders</td>
                                        <td valign="middle">&nbsp;&nbsp;&nbsp;</td>
                                        <td valign="middle">
                                            <input type="radio" onclick="location.href=\'' . assemble_url('goto_home_tab') . '&due_flag=all\'" name="objects_due" value="all" style="width:20px;" ' . ($tickets_due_flag=='all' ? ' checked="true"' : '') . ' />
                                        </td>
                                        <td valign="middle">Show All Tickets & Tasks</td>
                                    </tr>
                                </table>
                            </div>
                        </th>
                    </tr>
                    <tr><td colspan="4">&nbsp;</td></tr>
                    <tr>
                        <th align="left">Type</th>
                        <th align="left">Name</th>
                        <th align="left">Priority</th>
                        <th align="left">' . ($tickets_due_flag=='due' ? 'Due on / Reminder' : 'Due on') . '</th>
                    </tr>';
        $tickets_due_table_end = '
                </table>';

        $tickets_due_content = '';
        if ($tickets_due_info && is_array($tickets_due_info)){
            foreach ($tickets_due_info as $entry){
                $type = $entry['type'];
                $obj = new $type($entry['id']);

                $due_date_val = $obj->getDueOn();
                if (!empty($due_date_val)){
                    $due_date = date('F d, Y', strtotime($obj->getDueOn()));
                } else {
                    $due_date = '--';
                }

                if (!empty($entry['reminder']) && $entry['reminder']!='0000-00-00'){
                    $reminder_date = date('F d, Y', strtotime($entry['reminder']));
                } else {
                    $reminder_date = '--';
                }

                if ($tickets_due_flag=='due'){
                    $date_string = $due_date . ' / ' . $reminder_date;
                } else {
                    $date_string = $due_date;
                }

                $tickets_due_content .= '
                    <tr>
                        <td>' . $type . '</td>
                        <td>
                            <a target="_blank" href="' . $obj->getViewUrl() . '">
                                <span class="homepageobject">' . strip_tags($obj->getName()) . '</span>
                            </a>
                        </td>
                        <td>' . $obj->getFormattedPriority() .  '</td>
                        <td> ' . $date_string . '</td>
                    </tr>';
                unset($obj);
            }
        } else {
            $tickets_due_content .= '
                <tr>
                    <td colspan="4">No Records to Display</td>
                </tr>';
        }

        $fyi_comments_unvisited = 0;
        $content = '';
        if ($info && is_array($info)){
            if ($layout_type=='summary'){
                foreach ($info as $ticket_id => $comments){
                    //BOF:mod 20111019 #448
                    if (!empty($comments[0])){
                    //EOF:mod 20111019 #448
                        $temp_obj = new  Comment($comments[0]);
                        $parenttype = $temp_obj->getParentType();
                    //BOF:mod 20111019 #448
                    } else {
                      $temp_obj = new ProjectObject($ticket_id);
                      $parenttype = $temp_obj->getType();
                    }
                    //EOF:mod 20111019 #448

                    $parentobj = new $parenttype($ticket_id);
                    /*$projectobj = new Project($parentobj->getProjectId());

                    $milestone_id = $parentobj->getMilestoneId();
                    if (!empty($milestone_id)){
                        $milestoneobj = new Milestone($milestone_id);
                    }
                    $assigneesstring = '';
                    list($assignees, $owner_id) = $parentobj->getAssignmentData();
                    foreach($assignees as $assignee) {
                        $assigneeobj = new User($assignee);
                        $assigneesstring .= '<a target="_blank" href="' . $assigneeobj->getViewUrl() . '">' . $assigneeobj->getName() . '</a>, ';
                        unset($assigneeobj);
                    }
                    if (!empty($assigneesstring)){
                        $assigneesstring = substr($assigneesstring, 0, -2);
                    }
                    $dueon = date('F d, Y', strtotime($parentobj->getDueOn()));
                    if ($dueon=='January 01, 1970'){
                        $dueon = '--';
                    }
                    if ($milestoneobj){
                        $priority = $milestoneobj->getPriority();
                        if (!empty($priority) || $priority=='0'){
                            $priority = $milestoneobj->getFormattedPriority();
                        } else {
                            $priority = '--';
                        }
                    } else {
                        $priority = '--';
                    }*/

                    $comment_links = '';
                    //$comment_info = '';
                    $count = 0;
                    //$max_chars = 1000;
                    foreach($comments as $comment_id){
                        $count++;
                      //BOF:mod 20111019 #448
                      if (!empty($comment_id)){
                      //EOF:mod 20111019 #448
                            $temp_obj = new Comment($comment_id);

                            $is_unvisited = $this->link_unvisited($temp_obj->getId());
                            if ($is_unvisited){
                                $fyi_comments_unvisited++;
                            }
                            /*
                            $created_by_id = $temp_obj->getCreatedById();
                            $created_by_user = new User($created_by_id);
                            $created_on = strtotime($temp_obj->getCreatedOn());
                            $created_on = date('m-d-y', $created_on);

                            $temp = $temp_obj->getFormattedBody(true, true);
                            $comment_body = $temp;
                            */
                            //$comment_body = trim(str_excerpt(smarty_modifier_html_excerpt($temp), $max_chars));
                            //if (strlen($temp)>$max_chars){
                            //    $show_read_link = true;
                            //} else {
                            //    $show_read_link = false;
                            //}

                            $comment_links .= '<a target="_blank" href="' . $temp_obj->getViewUrl() . '" class="anc01' . (!$is_unvisited ? '_visited' : '') . '">#' . $count . '</a>&nbsp;&nbsp;&nbsp;';
                            /*$comment_info .= '
                                        <tr ' . ($count%2==1 ? ' style="background-color:#ffffff;" ' : ' style="background-color:#eeeeee;" ') . '>
                                            <td valign="top">
                                                Comment by<br/>' .
                                                (!empty($created_by_id) ? '<a target="_blank" href="' . $created_by_user->getViewUrl() . '">' . $created_by_user->getName() . '</a>' : $temp_obj->getCreatedByName()) .
                                                '<br/><br/><br/>
                                                <a target="_blank" href="' . $temp_obj->getViewUrl() . '" class="anc02' . (!$is_unvisited ? '_visited' : '') . '">[view comment]</a><br/>&nbsp;&nbsp;&nbsp;' .
                                                $created_on .
                                                '<br/><br/><br/>
                                                    <a class="mark_as_read" href="' . assemble_url('project_comment_fyi_read', array('project_id' => $temp_obj->getProjectId(), 'comment_id' => $temp_obj->getId())) . '">Mark this Notification<br/>as Read</a>
                                            </td>
                                            <td valign="top">
                                                <div style="overflow:auto;">' . $comment_body . '</div>' .
                                                ($show_read_link ? '<a target="_blank" href="' . $temp_obj->getViewUrl() . '">Click here to read the rest of this Comment</a>' : '') .
                                            '</td>
                                        </tr>
                                        <tr><td colspan="2" style="height:20px;">&nbsp;</td></tr>';*/
                      //BOF:mod 20111019 #448
                      } else {
                        $is_unvisited = $this->link_unvisited($temp_obj->getId());
                        if ($is_unvisited){
                            $fyi_comments_unvisited++;
                        }
                        /*
                        $created_by_id = $parentobj->getCreatedById();
                        $created_by_user = new User($created_by_id);
                        $created_on = strtotime($parentobj->getCreatedOn());
                        $created_on = date('m-d-y', $created_on);*/

                        $comment_links .= '<a target="_blank" href="' . $parentobj->getViewUrl() . '" class="anc01' . (!$is_unvisited ? '_visited' : '') . '">#' . $count . '</a>&nbsp;&nbsp;&nbsp;';
                        /*$comment_info .= '
                                    <tr ' . ($count%2==1 ? ' style="background-color:#ffffff;" ' : ' style="background-color:#eeeeee;" ') . '>
                                        <td valign="top">
                                            Created by<br/>' .
                                            (!empty($created_by_id) ? '<a target="_blank" href="' . $created_by_user->getViewUrl() . '">' . $created_by_user->getName() . '</a>' : $parentobj->getCreatedByName()) .
                                            '<br/><br/><br/>
                                            <a target="_blank" href="' . $parentobj->getViewUrl() . '" class="anc02' . (!$is_unvisited ? '_visited' : '') . '">[view object]</a><br/>&nbsp;&nbsp;&nbsp;' .
                                            $created_on .
                                            '<br/><br/><br/>
                                            <a class="mark_as_read" href="' . assemble_url('project_object_fyi_read', array('project_id' => $parentobj->getProjectId())) . '&object_id=' . $parentobj->getId() . '&project_id=' . $parentobj->getProjectId() . '">Mark this Notification<br/>as Read</a>
                                        </td>
                                        <td valign="top">
                                            <div style="overflow:auto;">' . $parentobj->getFormattedBody(true, true) . '</div>
                                        </td>
                                    </tr>
                                    <tr><td colspan="2" style="height:20px;">&nbsp;</td></tr>';*/

                      }
                      //EOF:mod 20111019 #448
                    }
                    $content .= '
                        <tr>
                            <td vlaign="top" class="comment_link" colspan="4">
                                &nbsp;&nbsp;&nbsp;
                                <a target="_blank" href="' . $parentobj->getViewUrl() . '">
                                   <span class="homepageobject">' . $parentobj->getName() . '</span>
                                </a>
                                &nbsp;&nbsp;&nbsp;' . $comment_links .
                                //'<img src="' . ROOT_URL . '/assets/images/icons/icon_plus.png" hspace="0" vspace="0" border="0" style="cursor:pointer;" onclick="toggle_details(this, \'fyi\', \'' . $parentobj->getId() . '\');" />
                                '<span id="fyi_' . $parentobj->getId() . '"><img id="icon_plus" src="' . ROOT_URL . '/assets/images/icons/icon_plus.png" hspace="0" vspace="0" border="0" style="cursor:pointer;" /></span>
                            </td>
                        </tr>';
                        /*<tr id="row_fyi_' . $parentobj->getId() . '" style="display:none;">
                            <td colspan="4">
                                <table width="100%">
                                    <tr><td colspan="2" style="height:20px;">&nbsp;</td></tr>
                                    <tr>
                                        <td style="width:250px;" valign="top">Ticket</td>
                                        <td valign="top">
                                            <a target="_blank" href="' . $parentobj->getViewUrl() . '"><span class="homepageobject">' . $parentobj->getName() . '</span></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td valign="top">Team &raquo; Project</td>
                                        <td valign="top">
                                            <a target="_blank" href="' . $projectobj->getOverviewUrl() . '"><span class="homepageobject">' . $projectobj->getName() . '</span></a> &raquo; ' .  ($milestoneobj ? '<a target="_blank" href="' . $milestoneobj->getViewUrl() . '"><span class="homepageobject">' . $milestoneobj->getName() . '</a></span>' : '--') .
                                        '</td>
                                    </tr>
                                    <tr>
                                        <td vlaign="top">Project Priority</td>
                                        <td valign="top">' .
                                            $priority .
                                        '</td>
                                    </tr>
                                    <tr>
                                        <td valign="top">Due on</td>
                                        <td valign="top">' .
                                            $dueon .
                                        '</td>
                                    </tr>
                                    <tr>
                                        <td valign="top">Assignees</td>
                                        <td valign="top">' .
                                            $assigneesstring .
                                        '</td>
                                    </tr>
                                    <tr><td colspan="2">&nbsp;</td></tr>' . $comment_info . '
                                </table>
                            </td>
                        </tr>';*/
                }
            } else {
                foreach ($info as $comment_id){
                    //BOF:mod 20111019 #448
                    $temp_obj = new ProjectObject($comment_id);
                    $temp_type = $temp_obj->getType();
                    if ($temp_type=='Comment'){
                    //EOF:mod 20111019 #448
                        $obj = new Comment($comment_id);
                    //BOF:mod 20111019 #448
                      $is_comment_obj = true;
                    } else {
                      $obj = new $temp_type($comment_id);
                      $is_comment_obj = false;
                    }
                    //EOF:mod 20111019 #448
                    $is_unvisited = $this->link_unvisited($obj->getId());
                    if ($is_unvisited){
                        $fyi_comments_unvisited++;
                    }

                    $created_by_id = $obj->getCreatedById();
                    $created_by_user = new User($created_by_id);
                    $created_on = strtotime($obj->getCreatedOn());
                    $created_on = date('m-d-y', $created_on);

                    $projectobj = new Project($obj->getProjectId());
                    $parenttype = $obj->getParentType();
                    //BOF:mod 20111019 #448
                    if ($is_comment_obj){
                    //EOF:mod 20111019 #448
                        $parentobj = new $parenttype($obj->getParentId());
                    //BOF:mod 20111019 #448
                    } else {
                      $parentobj = $obj;
                    }
                    //EOF:mod 20111019 #448
                    $milestone_id = $parentobj->getMilestoneId();
                    if (!empty($milestone_id)){
                        $milestoneobj = new Milestone($milestone_id);
                    }
                    $assigneesstring = '';
                    list($assignees, $owner_id) = $parentobj->getAssignmentData();
                    foreach($assignees as $assignee) {
                        $assigneeobj = new User($assignee);
                        $assigneesstring .= '<a target="_blank" href="' . $assigneeobj->getViewUrl() . '">' . $assigneeobj->getName() . '</a>, ';
                        unset($assigneeobj);
                    }
                    if (!empty($assigneesstring)){
                        $assigneesstring = substr($assigneesstring, 0, -2);
                    }
                    $dueon = date('F d, Y', strtotime($parentobj->getDueOn()));
                    if ($dueon=='January 01, 1970'){
                        $dueon = '--';
                    }
                    if ($milestoneobj){
                        $priority = $milestoneobj->getPriority();
                        if (!empty($priority) || $priority=='0'){
                            $priority = $milestoneobj->getFormattedPriority();
                        } else {
                            $priority = '--';
                        }
                    } else {
                        $priority = '--';
                    }

                    $max_chars = 1000;
                    $temp = $obj->getFormattedBody(true, true);
                    $comment_body = $temp;
                    /*$comment_body = trim(str_excerpt(smarty_modifier_html_excerpt($temp), $max_chars));
                    if (strlen($temp)>$max_chars){
                        $show_read_link = true;
                    } else {
                        $show_read_link = false;
                    }*/

                    $content .= '
                        <tr>
                            <td valign="top" width="150">Ticket</td>
                            <td valign="top">
                                <a target="_blank" href="' . $parentobj->getViewUrl() . '">' . $parentobj->getName() . '</a>
                            </td>
                        </tr>
                        <tr>
                            <td valign="top">Team &raquo; Project</td>
                            <td valign="top">
                                <a target="_blank" href="' . $projectobj->getOverviewUrl() . '">' . $projectobj->getName() . '</a> &raquo; ' .  ($milestoneobj ? '<a target="_blank" href="' . $milestoneobj->getViewUrl() . '">' . $milestoneobj->getName() . '</a>' : '--') .
                            '</td>
                        </tr>
                        <tr>
                            <td valign="top">Project Priority</td>
                            <td valign="top">' . $priority . '</td>
                        </tr>
                        <tr>
                            <td valign="top">Due on</td>
                            <td valign="top">' . $dueon . '</td>
                        </tr>' .
                        (!empty($assigneesstring) ?
                        '<tr>
                            <td valign="top">Assignees</td>
                            <td valign="top">' . $assigneesstring . '</td>
                        </tr>'
                        : '<tr><td colpan="2"></td></tr>') .
                        '<tr>
                            <td valign="top">' . ($is_comment_obj ? 'Comment' : 'Created') . ' by<br/>' . $created_by_user->getName() . '<br/><br/><br/><a target="_blank" href="' . $obj->getViewUrl() . '" class="anc02' . (!$is_unvisited ? '_visited' : '') . '">[view ' . ($is_comment_obj ? 'comment' : 'object') . ']</a><br/>' . $created_on . '<br/><br/><br/><a class="mark_as_read" href="' . ($is_comment_obj ? assemble_url('project_comment_fyi_read', array('project_id' => $obj->getProjectId(), 'comment_id' => $obj->getId())) : assemble_url('project_object_fyi_read', array('project_id' => $obj->getProjectId())) . '&object_id=' . $obj->getId() . '&project_id=' . $obj->getProjectId() ) . '">Mark this Notification<br/>as Read</a></td>
                            <td valign="top" style="max-width:500px;"><div style="overflow:auto;">' . $obj->getBody() . '</div></td>
                        </tr>
                        <tr><td colspan="2" style="border-bottom:1px dotted #000000;">&nbsp;</td></tr>
                        <tr><td colspan="2">&nbsp;</td></tr>';
                }
            }
            unset($obj);
            unset($projectobj);
            unset($parentobj);
            unset($milestone_id);
            unset($milestoneobj);
            unset($assignees);
            unset($owner_id);
        }

        $action_request_content = '';
        $action_request_comments_unvisited = 0;
        if ($action_request_info && is_array($action_request_info)){
            if ($layout_type=='summary'){
                foreach ($action_request_info as $ticket_id => $comments){
                    //BOF:mod 20111019 #448
                    if (!empty($comments[0])){
                    //EOF:mod 20111019 #448
                      $temp_obj = new  Comment($comments[0]);
                      $parenttype = $temp_obj->getParentType();
                    //BOF:mod 20111019 #448
                    } else {
                      $temp_obj = new ProjectObject($ticket_id);
                      $parenttype = $temp_obj->getType();
                    }
                    //EOF:mod 20111019 #448
                    $parentobj = new $parenttype($ticket_id);
                    /*$projectobj = new Project($parentobj->getProjectId());
                    $milestone_id = $parentobj->getMilestoneId();
                    if (!empty($milestone_id)){
                        $milestoneobj = new Milestone($milestone_id);
                    }

                    $assigneesstring = '';
                    list($assignees, $owner_id) = $parentobj->getAssignmentData();
                    foreach($assignees as $assignee) {
                        $assigneeobj = new User($assignee);
                        $assigneesstring .= '<a target="_blank" href="' . $assigneeobj->getViewUrl() . '">' . $assigneeobj->getName() . '</a>, ';
                        unset($assigneeobj);
                    }
                    if (!empty($assigneesstring)){
                        $assigneesstring = substr($assigneesstring, 0, -2);
                    }
                    $dueon = date('F d, Y', strtotime($parentobj->getDueOn()));
                    if ($dueon=='January 01, 1970'){
                        $dueon = '--';
                    }

                    if ($milestoneobj){
                        $priority = $milestoneobj->getPriority();
                        if (!empty($priority) || $priority=='0'){
                            $priority = $milestoneobj->getFormattedPriority();
                        } else {
                            $priority = '--';
                        }
                    } else {
                        $priority = '--';
                    }*/

                    $comment_links = '';
                    //$comment_info = '';
                    $count = 0;
                    //$max_chars = 1000;
                    foreach($comments as $comment_id){
                      $count++;
                      //BOF:mod 20111019 #448
                      if (!empty($comment_id)){
                      //EOF:mod 20111019 #448
                        $temp_obj = new Comment($comment_id);

                        $is_unvisited = $this->link_unvisited($temp_obj->getId());
                        if ($is_unvisited){
                            $action_request_comments_unvisited++;
                        }
                        /*
                        $created_by_id = $temp_obj->getCreatedById();
                        $created_by_user = new User($created_by_id);
                        $created_on = strtotime($temp_obj->getCreatedOn());
                        $created_on = date('m-d-y', $created_on);

                        $temp = $temp_obj->getFormattedBody(true, true);
                        $comment_body = $temp;
                        */
                        /*$comment_body = trim(str_excerpt(smarty_modifier_html_excerpt($temp), $max_chars));
                        if (strlen($temp)>$max_chars){
                            $show_read_link = true;
                        } else {
                            $show_read_link = false;
                        }*/

                        $comment_links .= '<a target="_blank" href="' . $temp_obj->getViewUrl() . '" class="anc01' . (!$is_unvisited ? '_visited' : '') . '">#' . $count . '</a>&nbsp;&nbsp;&nbsp;';
                        /*$comment_info .= '
                                    <tr ' . ($count%2==1 ? ' style="background-color:#ffffff;" ' : ' style="background-color:#eeeeee;" ') . '>
                                        <td valign="top">
                                            Comment by<br/>' .
                                            (!empty($created_by_id) ? '<a target="_blank" href="' . $created_by_user->getViewUrl() . '">' . $created_by_user->getName() . '</a>' : $temp_obj->getCreatedByName()) .
                                            '<br/><br/><br/>
                                            <a target="_blank" href="' . $temp_obj->getViewUrl() . '" class="anc02' . (!$is_unvisited ? '_visited' : '') . '">[view comment]</a><br/>&nbsp;&nbsp;&nbsp;' .
                                            $created_on .
                                            '<br/><br/><br/>
                                            <a class="mark_as_complete" href="' . assemble_url('project_comment_action_request_completed', array('project_id' => $temp_obj->getProjectId(), 'comment_id' => $temp_obj->getId())) . '">Mark Action Request Complete</a>
                                        </td>
                                        <td valign="top">
                                            <div style="overflow:auto;">' . $comment_body . '</div>
                                        </td>
                                    </tr>
                                    <tr><td colspan="2" style="height:20px;">&nbsp;</td></tr>';*/
                      //BOF:mod 20111019 #448
                      } else {
                        $is_unvisited = $this->link_unvisited($temp_obj->getId());
                        if ($is_unvisited){
                            $action_request_comments_unvisited++;
                        }

                        /*$created_by_id = $parentobj->getCreatedById();
                        $created_by_user = new User($created_by_id);
                        $created_on = strtotime($parentobj->getCreatedOn());
                        $created_on = date('m-d-y', $created_on);*/

                        $comment_links .= '<a target="_blank" href="' . $parentobj->getViewUrl() . '" class="anc01' . (!$is_unvisited ? '_visited' : '') . '">#' . $count . '</a>&nbsp;&nbsp;&nbsp;';
                        /*$comment_info .= '
                                    <tr ' . ($count%2==1 ? ' style="background-color:#ffffff;" ' : ' style="background-color:#eeeeee;" ') . '>
                                        <td valign="top">
                                            Created by<br/>' .
                                            (!empty($created_by_id) ? '<a target="_blank" href="' . $created_by_user->getViewUrl() . '">' . $created_by_user->getName() . '</a>' : $parentobj->getCreatedByName()) .
                                            '<br/><br/><br/>
                                            <a target="_blank" href="' . $parentobj->getViewUrl() . '" class="anc02' . (!$is_unvisited ? '_visited' : '') . '">[view object]</a><br/>&nbsp;&nbsp;&nbsp;' .
                                            $created_on .
                                            '<br/><br/><br/>
                                            <a class="mark_as_complete" href="' . assemble_url('project_object_action_request_completed', array('project_id' => $parentobj->getProjectId())) . '&object_id=' . $parentobj->getId() . '&project_id=' . $parentobj->getProjectId() . '">Mark Action Request Complete</a>
                                        </td>
                                        <td valign="top">
                                            <div style="overflow:auto;">' . $parentobj->getFormattedBody(true, true) . '</div>' .
                                            ($show_read_link ? '<a target="_blank" href="' . $temp_obj->getViewUrl() . '">Click here to read the rest of this Comment</a>' : '') .
                                        '</td>
                                    </tr>
                                    <tr><td colspan="2" style="height:20px;">&nbsp;</td></tr>';*/

                      }
                      //EOF:mod 20111019 #448
                    }

                    $action_request_content .= '
                        <tr>
                            <td vlaign="top" class="comment_link" colspan="4">
                                &nbsp;&nbsp;&nbsp;
                                <a target="_blank" href="' . $parentobj->getViewUrl() . '">
                                    <span class="homepageobject">' . $parentobj->getName() . '</span>
                                </a>
                                &nbsp;&nbsp;&nbsp;' . $comment_links .
                                //'<img src="' . ROOT_URL . '/assets/images/icons/icon_plus.png" hspace="0" vspace="0" border="0" style="cursor:pointer;" onclick="toggle_details(this, \'action\', \'' . $parentobj->getId() . '\');" />
                                '<span id="action_' . $parentobj->getId() . '"><img id="icon_plus" src="' . ROOT_URL . '/assets/images/icons/icon_plus.png" hspace="0" vspace="0" border="0" style="cursor:pointer;" /></span>
                            </td>
                        </tr>';
                        /*<tr id="row_action_' . $parentobj->getId() . '" style="display:none;">
                            <td colspan="4">
                                <table width="100%">
                                    <tr><td colspan="2" style="height:20px;">&nbsp;</td></tr>
                                    <tr>
                                        <td style="width:250px;" valign="top">Ticket</td>
                                        <td valign="top">
                                            <a target="_blank" href="' . $parentobj->getViewUrl() . '"><span class="homepageobject">' . $parentobj->getName() . '</span></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td valign="top">Team &raquo; Project</td>
                                        <td valign="top">
                                            <a target="_blank" href="' . $projectobj->getOverviewUrl() . '"><span class="homepageobject">' . $projectobj->getName() . '</span></a> &raquo; ' .  ($milestoneobj ? '<a target="_blank" href="' . $milestoneobj->getViewUrl() . '"><span class="homepageobject">' . $milestoneobj->getName() . '</a></span>' : '--') .
                                        '</td>
                                    </tr>
                                    <tr>
                                        <td vlaign="top">Project Priority</td>
                                        <td valign="top">' .
                                            $priority .
                                        '</td>
                                    </tr>
                                    <tr>
                                        <td valign="top">Due on</td>
                                        <td valign="top">' .
                                            $dueon .
                                        '</td>
                                    </tr>
                                    <tr>
                                        <td valign="top">Assignees</td>
                                        <td valign="top">' .
                                            $assigneesstring .
                                        '</td>
                                    </tr>
                                    <tr><td colspan="2">&nbsp;</td></tr>' . $comment_info . '
                                </table>
                            </td>
                        </tr>';*/
                }
            } else {
                foreach ($action_request_info as $comment){
                    //BOF:mod 20111019 #448
                    $temp_obj = new ProjectObject($comment);
                    $temp_type = $temp_obj->getType();
                    if ($temp_type=='Comment'){
                    //EOF:mod 20111019 #448
                      $obj = new Comment($comment);
                    //BOF:mod 20111019 #448
                      $is_comment_obj = true;
                    } else {
                      $obj = new $temp_type($comment);
                      $is_comment_obj = false;
                    }
                    //EOF:mod 20111019 #448

                    $is_unvisited = $this->link_unvisited($obj->getId());
                    if ($is_unvisited){
                        $action_request_comments_unvisited++;
                    }

                    $created_by_id = $obj->getCreatedById();
                    $created_by_user = new User($created_by_id);
                    $created_on = strtotime($obj->getCreatedOn());
                    $created_on = date('m-d-y', $created_on);
                    $projectobj = new Project($obj->getProjectId());
                    $parenttype = $obj->getParentType();
                    //BOF:mod 20111019 #448
                    if ($is_comment_obj){
                    //EOF:mod 20111019 #448
                      $parentobj = new $parenttype($obj->getParentId());
                    //BOF:mod 20111019 #448
                    } else {
                      $parentobj = $obj;
                    }
                    //EOF:mod 20111019 #448

                    $milestone_id = $parentobj->getMilestoneId();
                    if (!empty($milestone_id)){
                        $milestoneobj = new Milestone($milestone_id);
                    }
                    $assigneesstring = '';
                    list($assignees, $owner_id) = $parentobj->getAssignmentData();
                    foreach($assignees as $assignee) {
                        $assigneeobj = new User($assignee);
                        $assigneesstring .= '<a target="_blank" href="' . $assigneeobj->getViewUrl() . '">' . $assigneeobj->getName() . '</a>, ';
                        unset($assigneeobj);
                    }
                    if (!empty($assigneesstring)){
                        $assigneesstring = substr($assigneesstring, 0, -2);
                    }
                    $dueon = date('F d, Y', strtotime($parentobj->getDueOn()));
                    if ($dueon=='January 01, 1970'){
                        $dueon = '--';
                    }

                    if ($milestoneobj){
                        $priority = $milestoneobj->getPriority();
                        if (!empty($priority) || $priority=='0'){
                            $priority = $milestoneobj->getFormattedPriority();
                        } else {
                            $priority = '--';
                        }
                    } else {
                        $priority = '--';
                    }

                    $max_chars = 1000;
                    $temp = $obj->getFormattedBody(true, true);
                    $comment_body = $temp;
                    /*$comment_body = trim(str_excerpt(smarty_modifier_html_excerpt($temp), $max_chars));
                    if (strlen($temp)>$max_chars){
                        $show_read_link = true;
                    } else {
                        $show_read_link = false;
                    }*/

                    $action_request_content .= '
                        <tr>
                            <td valign="top" width="150">Ticket</td>
                            <td valign="top">
                                <a target="_blank" href="' . $parentobj->getViewUrl() . '">' . $parentobj->getName() . '</a>
                            </td>
                        </tr>
                        <tr>
                            <td valign="top">Team &raquo; Project</td>
                            <td valign="top"><a target="_blank" href="' . $projectobj->getOverviewUrl() . '">' . $projectobj->getName() . '</a> &raquo; ' .  ($milestoneobj ? '<a target="_blank" href="' . $milestoneobj->getViewUrl() . '">' . $milestoneobj->getName() . '</a>' : '--') . '</td>
                        </tr>
                        <tr>
                            <td valign="top">Project Priority</td>
                            <td valign="top">' . $priority  . '</td>
                        </tr>
                        <tr>
                            <td valign="top">Due on</td>
                            <td valign="top">' . $dueon . '</td>
                        </tr>' .
                        (!empty($assigneesstring) ?
                        '<tr>
                            <td valign="top">Assignees</td>
                            <td valign="top">' . $assigneesstring . '</td>
                        </tr>'
                        : '<tr><td colspan="2"></td></tr>') .
                        '<tr>
                            <td valign="top">' . ($is_comment_obj ? 'Comment' : 'Created') . ' by<br/>' . $created_by_user->getName() . '<br/><br/><br/><a target="_blank" href="' . $obj->getViewUrl() . '" class="anc02' . (!$is_unvisited ? '_visited' : '') . '">[view ' . ($is_comment_obj ? 'comment' : 'object') . ']</a><br/>' . $created_on . '<br/><br/><br/><a class="mark_as_complete" href="' . ($is_comment_obj ? assemble_url('project_comment_action_request_completed', array('project_id' => $obj->getProjectId(), 'comment_id' => $obj->getId())) : assemble_url('project_object_action_request_completed', array('project_id' => $obj->getProjectId())) . '&object_id=' . $obj->getId() . '&project_id=' . $obj->getProjectId()) . '">Mark Action Request Complete</a></td>
                            <td valign="top" style="max-width:500px;"><div style="overflow:auto;">' . $obj->getBody() . '</div></td>
                        </tr>
                        <tr><td colspan="2" style="border-bottom:1px dotted #000000;">&nbsp;</td></tr>
                        <tr><td colspan="2">&nbsp;</td></tr>';
                }
            }
            unset($obj);
            unset($projectobj);
            unset($parentobj);
            unset($milestone_id);
            unset($milestoneobj);
            unset($assignees);
            unset($owner_id);
        }

        $completed_objects_content = '';
        if ($completed_objects && is_array($completed_objects)){
            foreach ($completed_objects[(string)$userid] as $entry){
                $obj = new Ticket($entry['ticket_id']);
                $projectobj = new Project($obj->getProjectId());
                $milestone_id = $obj->getMilestoneId();
                if (!empty($milestone_id)){
                    $milestoneobj = new Milestone($milestone_id);
                }
                $assigneesstring = '';
                list($assignees, $owner_id) = $obj->getAssignmentData();
                foreach($assignees as $assignee) {
                    $assigneeobj = new User($assignee);
                    $assigneesstring .= '<a target="_blank" href="' . $assigneeobj->getViewUrl() . '">' . $assigneeobj->getName() . '</a>, ';
                    unset($assigneeobj);
                }
                if (!empty($assigneesstring)){
                    $assigneesstring = substr($assigneesstring, 0, -2);
                }
                $completedon = date('F d, Y', strtotime($obj->getCompletedOn()));

                if (!empty($entry['last_comment_id'])){
                    $commentobj = new Comment($entry['last_comment_id']);
                    $last_comment_body = '<br>' . $commentobj->getBody();
                    unset($commentobj);
                } else {
                    $last_comment_body = '<br>None';
                }

                $completed_objects_content .= '
                    <tr>
                        <td valign="top" width="150">' . $obj->getType() . '</td>
                        <td valign="top"><a target="_blank" href="' . $obj->getViewUrl() . '"><span class="homepageobject">' . $obj->getName() . '</span></a></td>
                    </tr>
                    <tr>
                        <td valign="top">Team &raquo; Project</td>
                        <td valign="top"><a target="_blank" href="' . $projectobj->getOverviewUrl() . '"><span class="homepageobject">' . $projectobj->getName() . '</span></a> &raquo; ' .  ($milestoneobj ? '<a target="_blank" href="' . $milestoneobj->getViewUrl() . '"><span class="homepageobject">' . $milestoneobj->getName() . '</span></a>' : '--') . '</td>
                    </tr>
                    <tr>
                        <td valign="top">Completed on</td>
                        <td valign="top">' . $completedon . '</td>
                    </tr>' .
                    (!empty($assigneesstring) ?
                    '<tr>
                        <td valign="top">Assignees</td>
                        <td valign="top">' . $assigneesstring . '</td>
                    </tr>'
                    : '') .
                    '<tr>
                        <td valign="top">&nbsp;</td>
                        <td valign="top" style="max-width:500px;"><div style="overflow:auto;">' . $obj->getBody() . '</div></td>
                    </tr>
                    <tr><td colspan="2">&nbsp;</td></tr>
                    <tr>
                        <td valign="top">&nbsp;</td>
                        <td valign="top"><b>Last comment associated with the ticket:</b><br>' . $last_comment_body . '</td>
                    </tr>
                    <tr><td colspan="2" style="border-bottom:1px dotted #000000;">&nbsp;</td></tr>
                    <tr><td colspan="2">&nbsp;</td></tr>';

                unset($obj);
                unset($projectobj);
                unset($milestone_id);
                unset($milestoneobj);
                unset($assignees);
                unset($owner_id);
            }
        }

        $fyi_updates_content = '';
        if ($fyi_updates && is_array($fyi_updates)){
            foreach ($fyi_updates[(string)$userid] as $object_id){
                $baseobj = new ProjectObject($object_id);
                $type = $baseobj->getType();
                switch($baseobj->getType()){
                    case 'Page':
                        $obj = new Page($object_id);
                        break;
                }
                if ($obj){
                    $projectobj = new Project($obj->getProjectId());
                    $milestone_id = $obj->getMilestoneId();
                    if (!empty($milestone_id)){
                        $milestoneobj = new Milestone($milestone_id);
                    }
                    $subscribers = $obj->getSubscribers();
                    foreach($subscribers as $subscriber) {
                        $subscriberstring .= '<a target="_blank" href="' . $subscriber->getViewUrl() . '">' . $subscriber->getName() . '</a>, ';
                    }
                    if (!empty($subscriberstring)){
                        $subscriberstring = substr($subscriberstring, 0, -2);
                    }

                    $fyi_updates_content .= '
                <tr>
                    <td valign="top" width="150">' . $obj->getType() . '</td>
                    <td valign="top"><a target="_blank" href="' . $obj->getViewUrl() . '"><span class="homepageobject">' . $obj->getName() . '</span></a></td>
                </tr>
                <tr>
                    <td valign="top">Team &raquo; Project</td>
                    <td valign="top"><a target="_blank" href="' . $projectobj->getOverviewUrl() . '"><span class="homepageobject">' . $projectobj->getName() . '</span></a> &raquo; ' .  ($milestoneobj ? '<a target="_blank" href="' . $milestoneobj->getViewUrl() . '"><span class="homepageobject">' . $milestoneobj->getName() . '</span></a>' : '--') . '</td>
                </tr>' .
                (!empty($subscriberstring) ?
                '<tr>
                    <td valign="top">Subscribers</td>
                    <td valign="top">' . $subscriberstring . '</td>
                </tr>'
                : '') .
                '<tr>
                    <td valign="top">&nbsp;</td>
                    <td valign="top" style="max-width:500px;"><div style="overflow:auto;">' . $obj->getBody() . '</div></td>
                </tr>
                <tr><td colspan="2" style="border-bottom:1px dotted #000000;">&nbsp;</td></tr>
                <tr><td colspan="2">&nbsp;</td></tr>';
                }

                unset($obj);
                unset($projectobj);
                unset($milestoneobj);
            }
        }

        $home_tab_content = '';
        if (!empty($tickets_due_content)){
            $home_tab_content .= $tickets_due_table_start . $tickets_due_content . $tickets_due_table_end . '<br/><br/>';
            $goto_links .= '<a href="#tickets_due">Go to Due Tickets & Tasks</a><br/>';
        }
        if (!empty($action_request_content)){
            $home_tab_content .= $action_request_table_start . $action_request_content . $action_request_table_end . '<br/><br/>';
            $goto_links .= '<a href="#action_request">Go to Action Request Comment(s)</a><br/>';
        }
        if (!empty($content)){
            $home_tab_content .= $fyi_table_start . $content . $fyi_table_end . '<br/><br/>';
            $goto_links .= '<a href="#fyi">Go to FYI Comment(s)</a><br/>';
        }
        if (!empty($fyi_updates_content)){
            $home_tab_content .= $fyi_updates_table_start . $fyi_updates_content . $fyi_updates_table_end . '<br/><br/>';
        }
        if (!empty($completed_objects_content)){
            $home_tab_content .= $completed_objects_table_start . $completed_objects_content . $completed_objects_table_end;
            $goto_links .= '<a href="#closed_tickets">Go to Closed Ticket(s)</a><br/>';
        }
        if (!empty($goto_links)){
            $goto_links .= '<br/><br/>';
        }

        $goto_links.= '<input type="hidden" id="unvisited_fyi_comments" value="' . $fyi_comments_unvisited . '" />
                       <input type="hidden" id="unvisited_action_request_comments" value="' . $action_request_comments_unvisited . '" />
                       <input type="hidden" id="user_id" value="' . $user_id . '" />';

        $css = '
                <style>' .
                //($layout_type=='summary' ?
                //    'td a.anc {background-image:url("assets/images/icons/icon_comment.gif"); background-repeat:no-repeat; background-position:0px 0px;padding-right:18px;}
                //    td a.anc:visited {background-position:0px -16px;}
                //    '
                //:
                //    'td a.anc:link{}
                //    td a.anc:visited {color:#FF00FF;}
                //    td a.anc:hover {}
                //    td a.anc:active {}'
                //) .
                    '/*td a.icon {padding:0px 6px 6px 0px;background-image:url("assets/images/icons/icon_comment.png"); background-repeat:no-repeat;background-position:0px 0px;}*/
                    /*td a.anc01:visited, td a.anc02:visited {color:#FF00FF;}*/
                    td a.anc01_visited, td a.anc02_visited {color:#FF00FF;}
                    body {}
                    span.homepageobject {}
                    table tr {line-height:1.5;}:
                </style>';
                /*<script type="text/javascript">
                    function toggle_details(imgref, type, id){
                        try{
                            //$("tr[id^=\'row_action_\']").css("display", "none");
                            //$("tr[id^=\'row_fyi_\']").css("display", "none");
                            var src =  $(imgref).attr("src");
                            if (src.indexOf("icon_plus.png")!=-1){
                                $("tr#row_" + type + "_" + id).css("display", "");
                                $(imgref).attr("src", src.replace(/icon_plus.png/, "icon_minus.png"));
                            } else {
                                $("tr#row_" + type + "_" + id).css("display", "none");
                                $(imgref).attr("src", src.replace(/icon_minus.png/, "icon_plus.png"));
                            }
                        } catch(e){
                            alert(e);
                        }
                    }
                </script>';*/

        $home_tab_content = $css .  $top_message . '<br/><br/>' . $goto_links . $home_tab_content;

      	mysql_close($link);
        return $home_tab_content;
    }

    function get_unvisited_links_count($user_id = ''){
        if (empty($user_id)){
            $user_id = $this->getId();
        }
      	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
      	mysql_select_db(DB_NAME, $link);
        //BOF:mod 20111019 #448
        /*
        //EOF:mod 20111019 #448
	$query = "select a.user_id, b.id, d.id as parent_ref
		 from healingcrystals_assignments_action_request a
		 inner join healingcrystals_project_objects b on a.comment_id=b.id
		 inner join healingcrystals_project_objects d on b.parent_id=d.id
		 left outer join healingcrystals_project_objects e on d.milestone_id=e.id
		 inner join healingcrystals_projects c on b.project_id=c.id
		 where b.state='" . STATE_VISIBLE . "' and a.user_id='" . $user_id . "' and a.is_action_request='1'
                 and d.state='" . STATE_VISIBLE . "' and (d.completed_on is null or d.completed_on='')
                 and a.link_is_visited='0'
		 order by a.user_id, e.priority desc, c.name, a.date_added desc";
        $result = mysql_query($query);
        $action_request_links = mysql_num_rows($result);
        //BOF:mod 20111019 #448
        */
        $action_request_links = 0;
        $query = "select count(*) as count
                 from healingcrystals_assignments_action_request a
                 inner join healingcrystals_project_objects b on a.comment_id=b.id
                 inner join healingcrystals_project_objects d on b.parent_id=d.id
                 left outer join healingcrystals_project_objects e on d.milestone_id=e.id
                 inner join healingcrystals_projects c on b.project_id=c.id
                 where b.state='" . STATE_VISIBLE . "' and a.user_id='" . $user_id . "' and a.is_action_request='1'
                 and d.state='" . STATE_VISIBLE . "' and (d.completed_on is null or d.completed_on='') and a.link_is_visited='0'";
        $result = mysql_query($query);
        $info = mysql_fetch_assoc($result);
        $action_request_links += $info['count'];

        $query = "select count(*) as count
                  from healingcrystals_assignments_flag_fyi_actionrequest a
                  inner join healingcrystals_project_objects b on a.object_id=b.id
                  left outer join healingcrystals_project_objects e on b.milestone_id=e.id
                  inner join healingcrystals_projects c on b.project_id=c.id
                  where a.user_id='" . $user_id . "' and flag_actionrequest='1' and b.state='" . STATE_VISIBLE . "'
                  and (b.completed_on is null or b.completed_on='') and a.link_is_visited='0'";
        $result = mysql_query($query);
        $info = mysql_fetch_assoc($result);
        $action_request_links += $info['count'];
        /*
        //EOF:mod 20111019 #448

	$query = "select a.user_id, b.id, d.id as parent_ref
                 from healingcrystals_assignments_action_request a
		 inner join healingcrystals_project_objects b on a.comment_id=b.id
		 inner join healingcrystals_project_objects d on b.parent_id=d.id
		 left outer join healingcrystals_project_objects e on d.milestone_id=e.id
		 inner join healingcrystals_projects c on b.project_id=c.id
		 where b.state='" . STATE_VISIBLE . "' and a.user_id='" . $user_id . "' and a.is_fyi='1'
                 and d.state='" . STATE_VISIBLE . "' and (d.completed_on is null or d.completed_on='')
                 and a.link_is_visited='0'
		 order by a.user_id, e.priority desc, c.name, a.date_added desc";
        $result = mysql_query($query);
        $fyi_links = mysql_num_rows($result);
        */
        $fyi_links = 0;
        $query = "select count(*) as count
                 from healingcrystals_assignments_action_request a
                 inner join healingcrystals_project_objects b on a.comment_id=b.id
                 inner join healingcrystals_project_objects d on b.parent_id=d.id
                 left outer join healingcrystals_project_objects e on d.milestone_id=e.id
                 inner join healingcrystals_projects c on b.project_id=c.id
                 where b.state='" . STATE_VISIBLE . "' and a.user_id='" . $user_id . "' and a.is_fyi='1'
                 and d.state='" . STATE_VISIBLE . "' and (d.completed_on is null or d.completed_on='') and a.link_is_visited='0'";
        $result = mysql_query($query);
        $info = mysql_fetch_assoc($result);
        $fyi_links += $info['count'];

        $query = "select count(*) as count
                  from healingcrystals_assignments_flag_fyi_actionrequest a
                  inner join healingcrystals_project_objects b on a.object_id=b.id
                  left outer join healingcrystals_project_objects e on b.milestone_id=e.id
                  inner join healingcrystals_projects c on b.project_id=c.id
                  where a.user_id='" . $user_id . "' and flag_fyi='1' and b.state='" . STATE_VISIBLE . "'
                  and (b.completed_on is null or b.completed_on='') and a.link_is_visited='0'";
        $result = mysql_query($query);
        $info = mysql_fetch_assoc($result);
        $fyi_links += $info['count'];
        //EOF:mod 20111019 #448

        mysql_close($link);

        return array('action_request' => $action_request_links, 'fyi' => $fyi_links);
    }

    function link_unvisited($comment_id){
        //BOF:mod 20111019 #448
        $temp_obj = new ProjectObject($comment_id);
        $object_type = $temp_obj->getType();
        //EOF:mod 20111019
        $is_unvisited = true;
        $link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
      	mysql_select_db(DB_NAME, $link);
        //BOF:mod 20111019 #448
        if ($object_type=='Comment'){
        //EOF:mod 20111019 #448
          $query = "select link_is_visited from healingcrystals_assignments_action_request where user_id='" . $this->getId() . "' and comment_id='" . (int)$comment_id . "'";
        //BOF:mod 20111019 #448
        } else {
          $query = "select link_is_visited from healingcrystals_assignments_flag_fyi_actionrequest where user_id='" . $this->getId() . "' and object_id='" . (int)$comment_id . "'";
        }
        //EOF:mod 20111019 #448
        $result = mysql_query($query);
        if (mysql_num_rows($result)){
            $info = mysql_fetch_assoc($result);
            if ($info['link_is_visited']){
                $is_unvisited = false;
            }
        }
        mysql_close($link);
        return $is_unvisited;
    }

    function getOwnedTickets($user_id = '', $selected_project = '', $order_by = '', $sort_order = ''){
        if (empty($user_id)){
            $user_id = $this->getId();
        }
      	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
      	mysql_select_db(DB_NAME, $link);

	$query = "select b.*, a.is_owner, d.category_name, f.object_id as temp , h.name as team_name
                  from healingcrystals_assignments a
                  inner join healingcrystals_project_objects b on (a.object_id=b.id and (b.type='Ticket' or (b.type='Task' and b.parent_type='Ticket')))
                  left outer join healingcrystals_project_objects e on b.milestone_id=e.id
                  left outer join healingcrystals_project_object_categories c on b.id=c.object_id
		  left outer join healingcrystals_project_milestone_categories d on c.category_id=d.id
		  left outer join healingcrystals_starred_objects f on (b.id=f.object_id and f.user_id='" . $user_id . "')
		  left outer join healingcrystals_assignments_action_request g on (g.user_id='" . $user_id . "' and g.is_action_request='1' and exists(select * from healingcrystals_project_objects h where h.id=g.comment_id and h.parent_id=b.id))
                  inner join healingcrystals_projects h on h.id=b.project_id
                  where a.user_id='" . $user_id . "' and " .
                  (empty($selected_project) ? "" : " b.project_id='" . $selected_project . "' and ") .
                  " a.is_owner='1' and b.completed_on is null and b.state='3' and b.visibility='1' ";

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
		case 'duedate':
                    $query .= " order by b.due_on $sort_order ";
                    break;
		case 'star' :
                    $query .= " order by temp $sort_order ";
                    break;
		case 'team' :
                    $query .= " order by team_name $sort_order, b.priority desc, e.name, b.name";
                    break;
		default:
                    $query .= " order by team_name, b.priority desc, e.name, b.name";
            }
	} else {
            $query .= " order by team_name, b.priority desc, e.name, b.name";
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
		$entries[] = array('obj'                        => $item,
                                   'id'                         => $entry['id'],
                                   'logged_user_is_responsible' => $entry['is_owner'],
                                   'department'                 => array($entry['category_name']),
                                   'milestone_obj'              => $item_1,
                                   'team_name'                  => $entry['team_name']);
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
        return $entries;
    }

    function getSubscribedTickets($user_id = '', $selected_project = '', $order_by = '', $sort_order = ''){
        if (empty($user_id)){
            $user_id = $this->getId();
        }
      	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
      	mysql_select_db(DB_NAME, $link);
        $query = "select b.*, a.is_owner, d.category_name, f.object_id as temp , h.name as team_name
                 from healingcrystals_assignments a
                 inner join healingcrystals_project_objects b on (a.object_id=b.id and (b.type='Ticket' or (b.type='Task' and b.parent_type='Ticket')))
		 left outer join healingcrystals_project_objects e on b.milestone_id=e.id
		 left outer join healingcrystals_project_object_categories c on b.id=c.object_id
		 left outer join healingcrystals_project_milestone_categories d on c.category_id=d.id
		 left outer join healingcrystals_starred_objects f on (b.id=f.object_id and f.user_id='" . $user_id . "')
		 left outer join healingcrystals_assignments_action_request g on (g.user_id='" . $user_id . "' and g.is_action_request='1' and exists(select * from healingcrystals_project_objects h where h.id=g.comment_id and h.parent_id=b.id))
		 inner join healingcrystals_projects h on h.id=b.project_id
                 where a.user_id='" . $user_id . "' and a.is_owner<>'1' and " .
		 (empty($selected_project) ? "" : " b.project_id='" . $selected_project . "' and ") .
		 " b.completed_on is null and b.state='3' and b.visibility='1' ";

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
		case 'duedate':
                    $query .= " order by b.due_on $sort_order ";
                    break;
		case 'star' :
                    $query .= " order by temp $sort_order ";
                    break;
		case 'team' :
                    $query .= " order by team_name $sort_order, b.priority desc, e.name, b.name";
                    break;
		default:
                    $query .= " order by team_name, b.priority desc, e.name, b.name";
            }
	} else {
            $query .= " order by team_name, b.priority desc, e.name, b.name";
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
		$entries[] = array('obj'                        => $item,
                                   'id'                         => $entry['id'],
                                   'logged_user_is_responsible' => $entry['is_owner'],
                                   'department'                 => array($entry['category_name']),
                                   'milestone_obj'              => $item_1,
                                   'team_name'                  => $entry['team_name']);
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
        return $entries;
    }
    //EOF:mod 20110722

  }

?>