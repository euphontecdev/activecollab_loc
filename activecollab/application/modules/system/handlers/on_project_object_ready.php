<?php

  /**
   * System module on_project_object_ready event handler
   *
   * @package activeCollab.modules.system
   * @subpackage handlers
   */
  
  /**
   * Handle even when entire object creation process is done
   *
   * @param ProjectObject $object
   * @return null
   */
  function system_handle_on_project_object_ready(&$object) {
    //BOF:mod 20111011 #449
    /*
    //EOF:mod 20111011 #449
    if(instance_of($object, 'ProjectObject') && $object->can_be_completed && $object->can_have_subscribers) {
      $created_by = $object->getCreatedBy();
      //BOF:mod 20110715 ticketid246
      if ($object->getType()!='Ticket' && $object->getType()!='Task'){
      //EOF:mod 20110715 ticketid246
      $object->sendToSubscribers('resources/task_assigned', array(
        'created_by_name' => $created_by->getDisplayName(),
        'created_by_url' => $created_by->getViewUrl(),
      ), $object->getCreatedById(), $object->getNotificationContext());
      //BOF:mod 20110715 ticketid246
      }
      //EOF:mod 20110715 ticketid246
    } // if
    //BOF:mod 20111011 #449
    */
    //EOF:mod 20111011 #449
  } // system_handle_on_project_object_ready

?>