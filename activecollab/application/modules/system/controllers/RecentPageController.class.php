<?php

  /**
   * People controller
   *
   * @package activeCollab.modules.system
   * @subpackage controllers
   */
  class RecentPageController extends ApplicationController {
    
    /**
     * Actions available through the API
     *
     * @var array
     */
    var $api_actions = array('recent_page');
  
    /**
     * Constructor
     *
     * @param Request $request
     * @return PeopleController
     */
    function __construct($request) {
      parent::__construct($request);
      
      $this->wireframe->addBreadCrumb(lang('Recent Pages'), assemble_url('recent_page'));
      $this->wireframe->current_menu_item = 'recent_page';
      
        /*$tabs = new NamedList();
        $tabs->add('overview', array(
          'text' => str_excerpt($this->active_project->getName(), 25),
          'url' => $this->active_project->getOverviewUrl()
        ));
        
        event_trigger('on_project_tabs', array(&$tabs, &$this->logged_user, &$this->active_project));
        
        $tabs->add('people', array(
          'text' => lang('People'),
          'url' => $this->active_project->getPeopleUrl(),
        ));
        $tabs->add('recent_page', array(
          'text' => lang('Recent Pages'),
          'url' => assemble_url('recent_page'),
        ));
         $this->smarty->assign('page_tabs', $tabs);*/
      
    } // __construct
    function get_project_name($project_id, &$link){
    	$resp = 'Unknown';
    	$query = "select name from healingcrystals_projects where id='" . $project_id . "'";
    	$result = mysql_query($query, $link);
    	if (mysql_num_rows($result)){
    		$info = mysql_fetch_assoc($result);
    		$resp = $info['name'];
    	}
    	return $resp;
    }
    
    function get_object_info($object_string, $project_id, $id, &$link, $is_integer_field_1 = false){
    	$resp = array('type' => 'Unknown', 'name' => 'Unknown');
    	$type = ucfirst(substr($object_string, 0, -1));

    	$query = "select type, name 
				  from healingcrystals_project_objects 
				  where project_id='" . $project_id . "' and type='" . $type . "' and " . ($is_integer_field_1 ? " integer_field_1='" : " id='") . 
				  $id . "'";
		$result = mysql_query($query, $link);
		if (mysql_num_rows($result)){
			$info = mysql_fetch_assoc($result);
			$resp['type'] = $info['type'];
			if ($is_integer_field_1){
				$resp['type'] .= '#' . $id;
			}
			$resp['name'] = $info['name'];
		}

		return $resp;
    }
    
    function recent_page(){
    	$recent_pages = array();
    	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
    	mysql_select_db(DB_NAME);
    	$query = "select * from healingcrystals_user_visited_pages where user_id='" . $this->logged_user->getId() . "' order by access_time desc";
    	$result = mysql_query($query);
    	$count = 0;
    	while($info = mysql_fetch_assoc($result)){
    		$desc = $info['page_url'];
    		$pos = strpos($desc, 'path_info');
    		if ($pos!==false){
    			$desc = str_replace('path_info=', '', substr(str_replace('%2F', '/', $desc), $pos));
    			$pos = strpos($desc, 'projects');
    			if ($pos!==false and $pos===0){
    				$split = explode('/', $desc);
    				$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
    				mysql_select_db(DB_NAME);
    				switch(count($split)){
    					case 2:
    						$desc = 'Project: ' . $this->get_project_name($split[1], $link);
    						break;
    					case 3:
    						switch($split[2]){
    							case 'tickets':
    							case 'milestones':
    							case 'people':
    							case 'checklists':
    							case 'discussions':
    							case 'calendar':
    							case 'files':
    							case 'pages':
    								$desc = 'Project: ' . $this->get_project_name($split[1], $link) . ' | ' . ucfirst($split[2]); 
    								break;
    						}
    						break;
    					case 4:
    						$pos = strpos($split[3], '&');
    						if ($pos!==false){
    							
    						} else {
    							switch($split[2]){
	    							case 'tickets':
	    							case 'milestones':
	    							case 'checklists':
	    							case 'discussions':
	    							case 'pages':
	    								$resp = $this->get_object_info($split[2], $split[1], $split[3], $link, ($split[2]=='tickets' ? true : false));
	    								$desc = $resp['type'] . ': ' . $resp['name'];
	    								break;
    							}
    						}
    						break;
    				}
    				mysql_close($link);
    			} else {
    				$desc = $info['page_url'];
    			}
    		}
    		$recent_pages[] = array('url' => $info['page_url'],
							'access_time' => date('m-d-Y G:i:s', strtotime($info['access_time'])), 
							'count' => ++$count, 
							'description' => $desc);
    	}
    	mysql_close($link);
    	$this->smarty->assign('recent_pages', $recent_pages);
    	//$this->smarty->assign('page_tab', 'recent_pages');
    }
  
  }

?>