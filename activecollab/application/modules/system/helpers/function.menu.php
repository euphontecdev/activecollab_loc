<?php

  /**
   * menu helper
   *
   * @package activeCollab.modules.system
   * @subpackage helpers
   */
  
  /**
   * Render main menu
   *
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_menu($params, &$smarty) {
    require SYSTEM_MODULE_PATH . '/models/menu/Menu.class.php';
    require SYSTEM_MODULE_PATH . '/models/menu/MenuGroup.class.php';
    require SYSTEM_MODULE_PATH . '/models/menu/MenuItem.class.php';
    
    $logged_user = $smarty->get_template_vars('logged_user');
    //BOF:task_1260
    $active_project = $smarty->get_template_vars('active_project');
    //EOF:task_1260
    
    $menu = new Menu();
    //BOF:task_1260
    /*
    //EOF:task_1260
    event_trigger('on_build_menu', array(&$menu, &$logged_user));
    //BOF:task_1260
    */
    event_trigger('on_build_menu', array(&$menu, &$logged_user, &$active_project));
    //EOF:task_1260
    $smarty->assign('_menu', $menu);
    
    return $smarty->fetch(get_template_path('_menu', null, SYSTEM_MODULE));
  } // smarty_function_menu

?>