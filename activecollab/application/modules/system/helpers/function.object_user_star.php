<?php

  /**
   * object_user_star helper
   *
   * @package activeCollab.modules.system
   * @subpackage helpers
   */
  
  /**
   * Render star for a given user page
   *
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_object_user_star($params, &$smarty) {
    static $ids = array();
    
    $starred_user_id = array_var($params, 'starred_user_id');
    $starred_page_type = array_var($params, 'starred_page_type');
    $starred_by_user_id = array_var($params, 'starred_by_user_id');
    $project_id = array_var($params, 'project_id');
    
    $id = array_var($params, 'id');
    if(empty($id)) {
      do {
        $id = 'object_star_' . make_string(40);
      } while(in_array($id, $ids));
    } // if
    
    $ids[] = $id;
    
  	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
  	mysql_select_db(DB_NAME, $link);
  	$query = "select * from healingcrystals_starred_user_pages where starred_by_user_id='" . $starred_by_user_id . "' and starred_user_id='" . $starred_user_id . "' and starred_page_type='" . $starred_page_type . "'";
  	$result = mysql_query($query);
  	$is_starred = false;
  	if (mysql_num_rows($result)){
  		$is_starred = true;
  	}
  	mysql_close($link);
    
      if($is_starred) {
        $params = array(
          'id'    => $id,
          'href'  => assemble_url('unstar_user_' . $starred_page_type . '_page', array('project_id'=>$project_id, 'user_id' => $starred_by_user_id )) . '&starred_user_id=' . $starred_user_id,
          'title' => lang('Unstar this object'),
          'class' => 'object_star'
        );
        
        $result = open_html_tag('a', $params) . '<img src="' . get_image_url('icons/star-small.gif') . '" alt="" /></a>';
      }  else {
        $params = array(
          'id'    => $id,
          'href'  => assemble_url('star_user_' . $starred_page_type . '_page', array('project_id'=>$project_id, 'user_id' => $starred_by_user_id)) . '&starred_user_id=' . $starred_user_id,
          'title' => lang('Star this object'),
          'class' => 'object_star',
        );
        
        $result = open_html_tag('a', $params) . '<img src="' . get_image_url('icons/unstar-small.gif') . '" alt="" /></a>';
      } // if
      
      return $result . "\n<script type=\"text/javascript\">App.layout.init_star_unstar_link('" . $id . "')</script>";

  } // smarty_function_object_star

?>