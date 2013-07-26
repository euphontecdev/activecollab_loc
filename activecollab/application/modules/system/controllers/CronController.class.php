<?php

  /**
   * Cron controller
   * 
   * This controller is used to trigger daily and hourly tasks through web 
   * interface
   *
   * @package activeCollab.modules.system
   * @subpackage controllers
   */
  class CronController extends PageController {
    
    /**
     * Construct cron controller
     *
     * @param Request $request
     * @return null
     */
    function __construct($request) {
      parent::__construct($request);
      
      if(defined('PROTECT_SCHEDULED_TASKS') && PROTECT_SCHEDULED_TASKS) {
        $code = $this->request->get('code');
        if(empty($code) || strtoupper($code) != strtoupper(substr(LICENSE_KEY, 0, 5))) {
          $this->httpError(HTTP_ERR_FORBIDDEN);
        } // if
      } // if
    } // __construct
    
    /**
     * Trigger frequently event
     *
     * @param void
     * @return null
     */
    function frequently() {
      $this->renderText('Frequently event started on ' . strftime(FORMAT_DATETIME) . '.<br />' ,false, false);
      event_trigger('on_frequently');
      ConfigOptions::setValue('last_frequently_activity', time());
      $this->renderText('Frequently event finished on ' . strftime(FORMAT_DATETIME) . '.');
    } // frequently
    
    /**
     * Trigger hourly tasks
     *
     * @param void
     * @return null
     */
    function hourly() {
      $this->renderText('Hourly event started on ' . strftime(FORMAT_DATETIME) . '.<br />' ,false, false);
    	event_trigger('on_hourly');
    	ConfigOptions::setValue('last_hourly_activity', time());
    	$this->renderText('Hourly event finished on ' . strftime(FORMAT_DATETIME) . '.');
    } // hourly
    
    /**
     * Trigger daily tasks
     *
     * @param void
     * @return null
     */
    function daily() {
      $this->renderText('Daily event started on ' . strftime(FORMAT_DATETIME) . '.<br />' ,false, false);
    	event_trigger('on_daily');
    	ConfigOptions::setValue('last_daily_activity', time());
    	$this->renderText('Daily event finished on ' . strftime(FORMAT_DATETIME) . '.');
    } // daily
    
  }

?>