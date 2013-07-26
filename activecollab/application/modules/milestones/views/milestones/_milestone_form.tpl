<div class="form_left_col">
  {wrap field=name}
    {label for=milestoneName required=yes}Summary{/label}
    {text_field name='milestone[name]' value=$milestone_data.name id=milestoneName class='title required validate_minlength 3'}
  {/wrap}
  
  {if $active_milestone->isNew()}
    {wrap field=date_range}
      <div class="col">
      {wrap field=start_on}
        {label for=milestoneStartOn}Start on{/label}
        {select_date name='milestone[start_on]' value=$milestone_data.start_on id=milestoneStartOn}
      	{*}
        {label for=milestoneStartOn required=yes}Start on{/label}
        {select_date name='milestone[start_on]' value=$milestone_data.start_on id=milestoneStartOn  class=required}
        {*}
      {/wrap}
      </div>
      
      <div class="col">
      {wrap field=due_on}
        {label for=milestoneDueOn}Due on{/label}
        {select_date name='milestone[due_on]' value=$milestone_data.due_on id=milestoneDueOn}
      	{*}
        {label for=milestoneDueOn required=yes}Due on{/label}
        {select_date name='milestone[due_on]' value=$milestone_data.due_on id=milestoneDueOn class=required}
        {*}
      {/wrap}
      </div>
    {/wrap}
  {/if}
  
  {wrap field=body}
    {label for=milestoneBody}Notes{/label}
    {editor_field name='milestone[body]' id=milestoneBody inline_attachments=$milestone_data.inline_attachments style='height:300px;'}{$milestone_data.body}{/editor_field}
  {/wrap}
  
  {wrap field=assignees}
    {label for=milestoneAssignees}Assignees{/label}
    {select_assignees_inline name='milestone[assignees]' value=$milestone_data.assignees object=$active_milestone project=$active_project choose_responsible=true}
  {/wrap}
</div>

<div class="form_right_col">
  {wrap field=priority}
    {label for=milestonePriority}Priority{/label}
    {select_priority name='milestone[priority]' value=$milestone_data.priority id=milestonePriority}
  {/wrap}
  
  {wrap field=tags}
    {label for=milestoneTags}Tags{/label}
    {select_tags name='milestone[tags]' value=$milestone_data.tags project=$active_project id=milestoneTags}
  {/wrap}
  {*BOF: task 03 | AD*}
  {wrap field=category}
    {label for=milestoneCategory}Department(s){/label}
    {*}{select_milestone_category name='milestone[category_id]' value=$milestone_data.category_id project=$active_project id=milestoneCategory}{*}
	{select_departments name='milestone[departments][]' object=$active_milestone project=$active_project}
  {/wrap}
  {*EOF: task 03 | AD*}
  {*BOF: task 07 | AD*}
  {if $is_edit_mode=='1'}
    {wrap field=project_id}
      {label for=project_id}Project{/label}
      {select_project user=$logged_user name='milestone[project_id]' value=$milestone_data.project_id id=project_id show_all=true}
    {/wrap}
  {/if}
  {*EOF: task 07 | AD*}
  
  {if $logged_user->canSeePrivate()}
    {wrap field=visibility}
      {label for=milestoneVisibility}Visibility{/label}
      {select_visibility name=milestone[visibility] value=$milestone_data.visibility project=$active_project short_description=true}
    {/wrap}
  {else}
    <input type="hidden" name="milestone[visibility]" value="1" />
  {/if}
</div>
<div class="clear"></div>