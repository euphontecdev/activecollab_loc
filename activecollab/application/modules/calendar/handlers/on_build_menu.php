<?php

  /**
   * Calendar module on_build_menu event handler
   *
   * @package activeCollab.modules.calendar
   * @subpackage handlers
   */
  
  /**
   * Add options to main menu
   *
   * @param Menu $menu
   * @param User $user
   * @return null
   */
  function calendar_handle_on_build_menu(&$menu, &$user) {
  	//BOF:mod 20110610
  	/*
  	//EOF:mod 20110610
    $menu->addToGroup(array(
      new MenuItem('calendar', lang('Calendar'), Calendar::getDashboardCalendarUrl(), get_image_url('navigation/calendar.gif')),
    ), 'main');
  	//BOF:mod 20110610
  	*/
  	//EOF:mod 20110610
  } // calendar_handle_on_build_menu

?>