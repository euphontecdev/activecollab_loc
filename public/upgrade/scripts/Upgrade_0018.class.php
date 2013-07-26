<?php

  /**
   * Make sure that we have everyone with 2.2.2 on activeCollab 2.3
   *
   * @package activeCollab.upgrade
   * @subpackage scripts
   */
  class Upgrade_0018 extends UpgradeScript {
    
    /**
     * Initial system version
     *
     * @var string
     */
    var $from_version = '2.2.2';
    
    /**
     * Final system version
     *
     * @var string
     */
    var $to_version = '2.3';
    
    /**
     * Return script actions
     *
     * @param void
     * @return array
     */
    function getActions() {
    	return array(
    	  'emptyAction' => 'Skip 2.2.2 to 2.3 version logging step',
    	);
    } // getActions
    
    /**
     * Empty action
     *
     * @param void
     * @return null
     */
    function emptyAction() {
      return true;
    } // emptyAction
    
  }

?>