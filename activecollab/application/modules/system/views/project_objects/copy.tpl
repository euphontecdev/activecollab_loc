{title}Copy to Project{/title}
{add_bread_crumb}Copy to Project{/add_bread_crumb}

<div id="copy_to_project">
  <p>{lang type=$active_object->getVerboseType(true) name=$active_object->getName() url=$active_object->getViewUrl() project=$active_project->getName() project_url=$active_project->getOverviewUrl()()}You are about to copy :type <a href=":url">:name</a> from <a href=":project_url">:project</a> project. Please select destination project:{/lang}</p>
  
  {form action=$active_object->getCopyUrl() method=post}
    {wrap field=project_id}
      {label id=move_to_project required=yes}Copy to Project{/label}
      {select_project name=copy_to_project_id user=$logged_user show_all=yes class=required}
    {/wrap}
    
    {if instance_of($active_object, 'Milestone')}
      {wrap field=copy_child_objects}
        {label id=copy_child_objects}Copy all related objects (tickets, checklists, discussions and files)?{/label}
        {yes_no name='copy_child_objects' value=$data.copy_child_objects}
      {/wrap}
    {/if}
    
    {wrap_buttons}
      {submit}Submit{/submit}
    {/wrap_buttons}
  {/form}
</div>