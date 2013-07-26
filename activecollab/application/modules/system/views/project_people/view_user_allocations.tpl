{title not_lang=yes}{lang name=$active_user->getDisplayName()}:name's Allocations{/lang}{/title}
{add_bread_crumb}{$selected_category_name}{/add_bread_crumb}

<div class="list_view small_list_view">
  <div id="tickets" class="object_list">
<table cellpadding="2" cellspacing="2" border="0">
<tr>
	<td><b>Star</b>&nbsp;</td>
	<td><b><span style="cursor:pointer;" onclick="sort_page('priority');">Priority</span></b>&nbsp;</td>
	<td><b>Type</b>&nbsp;</td>
	<td><b>Department</b>&nbsp;</td>
	<td><b><span style="cursor:pointer;" onclick="sort_page('name');">Name</span></b></td>
	<td><b><span style="cursor:pointer;" onclick="sort_page('due_on');">Due Date</span></b></td>
	<td><b><span style="cursor:pointer;" onclick="sort_page('due_on');">Due in</span></b></td>
</tr>
{foreach from=$allocations item=category}
	{if $selected_category_id==''}
	{if $page_view=='all'}
	<tr>
		<td colspan="7"><h2 class="section_name"><span class="section_name_span">{$category.category_name}</span></h2></td>
	</tr>
	{/if}
	{/if}
	<tr>
	{foreach from=$category.milestones item=milestone}
	<tr>
		<td>{object_star object=$milestone.obj user=$active_user}</td>
		<td>{object_priority object=$milestone.obj}</td>
		<td>{if $milestone.logged_user_is_responsible}<b>{/if}{lang}{$milestone.obj->getType()}{/lang}{if $milestone.logged_user_is_responsible}</b>{/if}&nbsp;</td>
		<td>{$milestone.department}</td>
		<td><a href="{$milestone.obj->getViewUrl()}">{if $milestone.logged_user_is_responsible}<b>{/if}{$milestone.obj->getName()|clean}{if $milestone.logged_user_is_responsible}</b>{/if}</a></td>
		{*}<td>{$milestone.obj->getDueOn()|date:0}</td>{*}
		<td>{if $milestone.obj->getDueOn()==''}--{else}{$milestone.obj->getDueOn()|date:0}{/if}</td>
		{*}<td>{due object=$milestone.obj}</td>{*}
		{capture name=get_due_on assign=due_on_text}{due object=$milestone.obj}{/capture}
		<td>{if $due_on_text=='No Due Date'}--{else}{$due_on_text}{/if}</td>
	</tr>
		{foreach from=$milestone.tickets item=ticket}
		<tr>
			<td>{object_star object=$ticket.obj user=$active_user}</td>
			<td>{object_priority object=$ticket.obj}</td>
			<td>{if $ticket.logged_user_is_responsible}<b>{/if}{lang}{$ticket.obj->getType()}{/lang}{if $ticket.logged_user_is_responsible}</b>{/if}&nbsp;</td>
			<td>{$ticket.department}</td>
			<td><a href="{$ticket.obj->getViewUrl()}">{if $ticket.logged_user_is_responsible}<b>{/if}{$ticket.obj->getName()|clean}{if $ticket.logged_user_is_responsible}</b>{/if}</a></td>
			{*}<td>{$ticket.obj->getDueOn()|date:0}</td>{*}
			<td>{if $ticket.obj->getDueOn()==''}--{else}{$ticket.obj->getDueOn()|date:0}{/if}</td>
			{*}<td>{due object=$ticket.obj}</td>{*}
			{capture name=get_due_on}{due object=$ticket.obj}{/capture}
			<td>{if $smarty.capture.get_due_on=='No Due Date'}--{else}{$smarty.capture.get_due_on}{/if}</td>
		</tr>
			{foreach from=$ticket.tasks item=task}
			<tr>
				<td>{object_star object=$task.obj user=$active_user}</td>
				<td>{object_priority object=$task.obj}</td>
				<td>{if $task.logged_user_is_responsible}<b>{/if}{lang}{$task.obj->getType()}{/lang}{if $task.logged_user_is_responsible}</b>{/if}&nbsp;</td>
				<td>--</td>
				<td>{$task.obj->getBody()|clean|clickable}</td>
				{*}<td>{$task.obj->getDueOn()|date:0}</td>{*}
				<td>{if $task.obj->getDueOn()==''}--{else}{$task.obj->getDueOn()|date:0}{/if}</td>
				{*}<td>{due object=$task.obj}</td>{*}
				{capture name=get_due_on}{due object=$task.obj}{/capture}
				<td>{if $smarty.capture.get_due_on=='No Due Date'}--{else}{$smarty.capture.get_due_on}{/if}</td>
			</tr>
			{/foreach}
		{/foreach}
	{/foreach}
{/foreach}
	<tr>
		<td colspan="7" align="right">
			<select name="page_view" onchange="page_view_onchange(this);">
				<optgroup label="Page View">
					<option value="all" {if $page_view=='all'}selected{/if}>View All</option>
					<option value="priority" {if $page_view=='priority'}selected{/if}>View by Priority</option>
					<option value="ownership" {if $page_view=='ownership'}selected{/if}>View objects owned by me</option>
				</optgroup>
			</select>
		</td>
	</tr>
</table>
</div>

	<ul class="category_list">
		<li {if $selected_category_id==''}class="selected"{/if}><a href="{$all_milestones_url}"><span>{lang}All Milestones{/lang}</a></span></li>
  		{if is_foreachable($allocations)}
    		{foreach from=$allocations item=category}
    			<li {if $selected_category_id==$category.category_id} class="selected"{/if}><a href="{$category.category_url}"><span>{$category.category_name}</span></a></li>
    		{/foreach}
  		{/if}
	    <li id="manage_categories"><a onclick="location.href='{$manage_milestone_cat_url}';return false;"><span>{lang}Manage Milestone Categories{/lang}</span></a></li>
  	</ul>
</div>