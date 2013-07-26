{title}Manage Milestone Departments{/title}
{add_bread_crumb}Manage Milestone Departments{/add_bread_crumb}
{if $message!=''}<div style="color:{$message_color}">{$message}</div>{/if}

{form action=$active_project->getManageMilestoneCategoriesUrl() method=post}
	{if $categories_count>0}
		<table class="table_cat">
			<tr>
				<th align="left">Department Name</th>
				<th align="left">Added</th>
				<th align="left">Modified</th>
				<th align="left">Edit</th>
				<th align="left">Delete</th>
			</tr>
			{foreach from=$categories item=cat}
				<tr>
					<td>{$cat.name}</td>
					<td>{$cat.added}</td>
					<td>{$cat.modified}</td>
					<td><input type="radio" name="milestone_category[chk_edit]" value="{$cat.id}" onclick="this.form.submit();" /></td>
					<td><input type="radio" name="milestone_category[chk_delete]" value="{$cat.id}" onclick="this.form.submit();" /></td>
				</tr>
			{/foreach}
		</table>
	{/if}
  {if $action!='deletenextstep'}	
	  {wrap field=category_name}
	    {label for=categoryName required=0}{$cat_name_lable_content}{/label}
	    {text_field name='milestone_category[category_name]' value=$selected_category_name id=categoryName}
	  {/wrap}
  {else}
  	{label required=0}{$cat_name_lable_content}{/label}
  {/if}
  <input type="hidden" name="milestone_category[hdn_action]" value="{$action}" />
  <input type="hidden" name="milestone_category[hdn_id]" value="{$selected_category_id}" />
  {if $action=='editnextstep' || $action=='deletenextstep'}
	  <input type="button" class="btn_continue" value="Continue" onclick="this.form.submit();" />
	  &nbsp;
	  <input type="button" class="btn_cancel" value="Cancel" onclick="history.back():" />
  {else}
	  {wrap_buttons}
	    {submit}Submit{/submit}
	  {/wrap_buttons}
  {/if}
{/form}
