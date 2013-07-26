<?php

  // We need MobileAccessProjectRepositoriesController
  use_controller('mobile_access_project', MOBILE_ACCESS_MODULE);

  /**
   * Mobile Access Project repositories controller
   *
   * @package activeCollab.modules.source
   * @subpackage controllers
   */
  class MobileAccessProjectRepositoriesController extends MobileAccessProjectController {
    /**
     * Controller name
     *
     * @var string
     */
    var $controller_name = 'mobile_access_project_repositories';
    
    /**
     * Active repository (if exists)
     *
     * @var Repository
     */
    var $active_repository;
    
    /**
     * Constructor
     *
     * @param Request $request
     * @return MobileAccessProjectRepositoriesController extends ApplicationController 
     */
    function __construct($request) {      
      parent::__construct($request);
      
      if($this->logged_user->getProjectPermission('repository', $this->active_project) < PROJECT_PERMISSION_ACCESS) {
        $this->httpError(HTTP_ERR_FORBIDDEN);
      } // if
      
      $this->controller_description_name = lang('Repositories');
      $this->active_project_section = 'repositories';
      
      $repository_id = $this->request->getId('object_id');
      if($repository_id) {
        $this->active_repository = Repositories::findById($repository_id);
      } // if
      
      if(!instance_of($this->active_repository, 'Repository')) {
        $this->active_repository = new Repository();
      } // if
      
      $this->smarty->assign(array(
        "active_repository" => $this->active_repository,
        "active_project_section" => $this->active_project_section
      ));
      
      $this->addBreadcrumb($this->controller_description_name, assemble_url('mobile_access_view_repositories',array('project_id' => $this->active_project->getId())));
    } // __construct
    
    /**
     * List of repositories
     *
     */
    function index() {
      $this->addBreadcrumb(lang('List'));
      
      $repositories = Repositories::findByProjectId($this->active_project->getId());
      
      $this->smarty->assign(array(
        'repositories'          => $repositories,
        'page_back_url' => assemble_url('mobile_access_view_project', array('project_id' => $this->active_project->getId())),
      ));
    } // index
    
    /**
     * View the repository
     *
     */
    function view() {
      if($this->active_repository->isNew()) {
        $this->httpError(HTTP_ERR_NOT_FOUND);
      } // if
      
      if(!$this->active_repository->canView($this->logged_user)) {
      	$this->httpError(HTTP_ERR_FORBIDDEN);
      } // if
      
      $this->addBreadcrumb(str_excerpt(clean($this->active_repository->getName()),10),mobile_access_module_get_view_url($this->active_repository));
      $this->addBreadcrumb(lang('View'));
      
      $per_page = 20;
      $page = intval(array_var($_GET, 'page')) > 0 ? array_var($_GET, 'page') : 1;
            
      list($commits, $pagination) = Commits::paginateByRepository($this->active_repository, $page, $per_page);
      $commits = group_by_date($commits);
      
      $this->smarty->assign(array(
        'pagination' => $pagination,
        'commits' => $commits,
        'pagination_url' => assemble_url('mobile_access_view_repository', array('object_id' => $this->active_repository->getId(), 'project_id' => $this->active_project->getId())),
        'page_back_url' => assemble_url('mobile_access_view_project', array('project_id' => $this->active_project->getId())),
      ));
    } // view
    
  } // MobileAccessProjectRepositoriesController
?>