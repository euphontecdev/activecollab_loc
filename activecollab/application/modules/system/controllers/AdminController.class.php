<?php

  /**
   * Base administration controller
   *
   * @package activeCollab.modules.system
   * @subpackage controllers
   */
  class AdminController extends ApplicationController {
    
    /**
     * Controller name
     *
     * @var string
     */
    var $controller_name = 'admin';
    
    /**
     * Construct admin controller
     *
     * @param Request $request
     * @return AdminController
     */
    function __construct($request) {
      parent::__construct($request);
      
      // Turn off print button in entire administration
      $this->wireframe->print_button = false;
      
      if(!$this->logged_user->isAdministrator()) {
        $this->httpError(HTTP_ERR_FORBIDDEN);
      } // if
      
      $this->wireframe->addBreadCrumb(lang('Administration'), assemble_url('admin'));
      $this->wireframe->current_menu_item = 'admin';
    } // __construct
    
    /**
     * Show administration index page
     *
     * @param void
     * @return null
     */
    function index() {
      if(isset($this->application->version)) {
        $version = $this->application->version;
      } else {
        $version = '1.0';
      } // if
      
      $admin_sections = array(
        ADMIN_SECTION_SYSTEM => null,
        ADMIN_SECTION_MAIL   => null,
        ADMIN_SECTION_TOOLS  => null,
        ADMIN_SECTION_OTHER  => null,
      );
      event_trigger('on_admin_sections', array(&$admin_sections));
      
      $original_licence_key = LICENSE_KEY;
      $license_id = '';
      $license_group_length = 6;
      for ($x =0; $x < ceil(strlen($original_licence_key) / $license_group_length); $x++) {
        $license_id.= substr($original_licence_key, $x*$license_group_length, $license_group_length) . '-';
      } // for
      $license_id.= LICENSE_UID;
      
      $support_url = 'https://www.activecollab.com/support/index.php?pg=request';
      $support_url.= '&amp;acinfo[ac_version]='.urlencode($version);
      $support_url.= '&amp;acinfo[ac_version]='.urlencode($version);
      $support_url.= '&amp;acinfo[php_version]='.urlencode(PHP_VERSION);
      $support_url.= '&amp;acinfo[mysql_version]='.urlencode(db_version());
      $support_url.= '&amp;acinfo[license_key]='.urlencode($original_licence_key);
      $support_url.= '&amp;acinfo[license_type]='.urlencode(LICENSE_PACKAGE);
      $support_url.= '&amp;acinfo[license_uid]='.urlencode(LICENSE_UID);
      $support_url.= '&amp;acinfo[license_branding_removed]='.urlencode(LICENSE_COPYRIGHT_REMOVED);
      $support_url.= '&amp;acinfo[license_url]='.urlencode(LICENSE_URL);
      $support_url.= '&amp;acinfo[encoded_license_id]='.urlencode($license_id);
      
      $user_id = LICENSE_UID;
      $license_key = LICENSE_KEY;
      
      $update_to_corporate_url = LICENSE_PACKAGE == 'corporate' ? false : "http://www.activecollab.com/user/$user_id/upgrade-to-corporate?license_key=$license_key";
      $branding_removal_url = LICENSE_COPYRIGHT_REMOVED === true ? false : "http://www.activecollab.com/user/$user_id/purchase-branding-removal?license_key=$license_key";
      $extend_support_url = "http://www.activecollab.com/user/$user_id/extend-support?license_key=$license_key";

      $this->smarty->assign(array(
        'ac_version' => $version,
        'admin_sections' => $admin_sections,
        'php_version' => PHP_VERSION,
        'mysql_version' => db_version(),
        'license_user_id' => LICENSE_UID,
        'licence_type' => LICENSE_PACKAGE,
        'licence_url' => LICENSE_URL,
        'licence_expires' => new DateValue(LICENSE_EXPIRES),
        'license_branding_removed' => LICENSE_COPYRIGHT_REMOVED,
        'license_id' => $license_id,
        'support_url' => $support_url,
        'upgrade_to_corporate_url' => LICENSE_PACKAGE === 'corporate' ? false : "http://www.activecollab.com/user/$user_id/upgrade-to-corporate?license_key=$license_key",
        'branding_removal_url' => LICENSE_COPYRIGHT_REMOVED === true ? false : "http://www.activecollab.com/user/$user_id/purchase-branding-removal?license_key=$license_key",
        'extend_support_url' => "http://www.activecollab.com/user/$user_id/extend-support?license_key=$license_key",
      ));
    } // index
    
  }

?>