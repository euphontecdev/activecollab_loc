<?php

  /**
   * System module on_project_object_quick_options event handler
   *
   * @package activeCollab.modules.system
   * @subpackage handlers
   * @param NamedList $options
   * @param ProjectObject $object
   * @param User $user
   * @return null
   */
  function system_handle_on_project_object_quick_options(&$options, $object, $user) {
    
    /**
     * Add a quick option which links to the list of commits related to the object
     */
    if(instance_of($object, 'ProjectObject') && $object->getState() == STATE_DELETED && ($user->isAdministrator() || $user->getSystemPermission('manage_trash'))) {
      $options->add('project_object_delete', array(
        'text' => lang('Permanently delete'),
        'url' => assemble_url('project_object_delete', array('project_id' => $object->getProjectId(), 'object_id' => $object->getId())),
        'method' => 'post',
        'confirm' => lang('Are you sure that you wish to permanently remove this :type?', array('type' => $object->getVerboseType(true)))
      ));
    } // if
    
  } // system_handle_on_project_object_quick_options

?>