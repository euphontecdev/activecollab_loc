{title not_lang=yes}{lang name=$active_user->getDisplayName()}Tickets for :name{/lang}{/title}
{add_bread_crumb}{lang}Assigned milestones{/lang}{/add_bread_crumb}

<div align="right" style="margin:15px 0 15px 0;">
{lang}Select Project: {/lang}
<select name="selected_project_id" id="selected_project_id" onchange="javascript:today_page_onchange(this);">
<option value="">{lang}All Projects{/lang}</option>
{foreach from=$user_projects item=user_project}
<option value="{$user_project->getId()}" {if $selected_project==$user_project->getId()}selected{/if}>{$user_project->getName()}</option>
{/foreach}
</select>
</div>
<div style="font-size:11px;">
	<table cellpadding="2" cellspacing="5" border="0">
		<tr>
			<td onclick="sort_page('star');" style="cursor:pointer;" title="Sort by Star" class="sort_column">Star</td>
			<td onclick="sort_page('priority');" style="cursor:pointer;" title="Sort by Priority" class="sort_column">Priority</td>
			<td onclick="sort_page('team');" style="cursor:pointer;" title="Sort by Team" class="sort_column">{lang}Project{/lang}</td>
			<td onclick="sort_page('project');" style="cursor:pointer;" title="Sort by Project" class="sort_column">{lang}Milestone{/lang}</td>
			<td onclick="sort_page('name');" style="cursor:pointer;" title="Sort by Name" class="sort_column">Name</td>
			<td onclick="sort_page('department');" style="cursor:pointer;" title="Sort by Department" class="sort_column">Department</td>
			<td onclick="sort_page('duedate');" style="cursor:pointer;" title="Sort by Due Date" class="sort_column">Due Date</td>
		</tr>
		{foreach from=$entries item=entry}
		<tr>
			<td>{object_star object=$entry.obj user=$active_user}</td>
			<td>{object_priority object=$entry.obj}</td>
			<td>{$entry.team_name}</td>
			<td>{if ($entry.milestone_obj && $entry.milestone_obj->getViewUrl())}<a href="{$entry.milestone_obj->getViewUrl()}">{$entry.milestone_obj->getName()|clean}</a>{else}--{/if}</td>
			<td><a href="{$entry.obj->getViewUrl()}">{if $entry.logged_user_is_responsible}<b>{/if}{$entry.obj->getName()|strip_tags:true}{if $entry.logged_user_is_responsible}</b>{/if}</a></td>
			<td>
			{foreach from=$entry.department item=department}
				{$department}<br />
			{/foreach}
			</td>
			<td>{if $entry.obj->getDueOn()!=''}{date_set_format value=$entry.obj->getDueOn() format='mmddyyyy'}{/if}</td>
		</tr>
		{/foreach}
	</table>
</div>