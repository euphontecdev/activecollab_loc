<div class="form_left_col">
{wrap field=name}
  {label for=checklistName required=yes}Summary{/label}
  {text_field name='checklist[name]' value=$checklist_data.name id=checklistName class='title required validate_minlength 3'}
{/wrap}

 {wrap field=body}
  {label for=checklistBody}Full description{/label}
  {editor_field name='checklist[body]' id=checklistBody inline_attachments=$checklist_data.inline_attachments}{$checklist_data.body}{/editor_field}
{/wrap}

  {*}BOF:mod 20110615{*}
  {wrap field=notify_users}
    {label}Notify People{/label}
    {select_assignees_inline name=notify_users value=$notify_users project=$active_project object=$active_checklist}
    <div class="clear"></div>
  {/wrap}
  {*}EOF:mod 20110615{*}
</div>

<div class="form_right_col">
{if $logged_user->canSeeMilestones($active_project)}
  {wrap field=milestone_id}
    {label for=checklistMilestone required=yes}Milestone{/label}
    {select_milestone name='checklist[milestone_id]' class="required" value=$checklist_data.milestone_id id=checklistMilestone project=$active_project}
  {/wrap}
{/if}

  {*BOF: mod*}
  {wrap field=category}
    {label for=milestoneCategory}Department(s){/label}
	{select_departments name='checklist[departments][]' object=$active_checklist project=$active_project}
  {/wrap}
  {*EOF: mod*}
  
  {wrap field=tags}
    {label for=checklistTags}Tags{/label}
    {select_tags name='checklist[tags]' value=$checklist_data.tags project=$active_project id=checklistTags}
  {/wrap}
  
{if $logged_user->canSeePrivate()}
  {wrap field=visibility}
    {label for=checklistVisibility}Visibility{/label}
    {select_visibility name='checklist[visibility]' value=$checklist_data.visibility project=$active_project short_description=true}
  {/wrap}
{else}
  <input type="hidden" name="checklist[visibility]" value="1">
{/if}
</div>

<div class="clear"></div>