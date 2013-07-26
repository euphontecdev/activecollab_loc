{*}{title not_lang=yes}{lang name=$active_user->getDisplayName()}:name's Allocations{/lang}{/title}{*}
{title not_lang=yes}{lang name=$active_user->getDisplayName()}Tickets for :name{/lang}{/title}
{add_bread_crumb}{lang}Assigned milestones{/lang}{/add_bread_crumb}
<span style="font-size:11px;"><input name="page_view" type="checkbox" style="width:30px;" onclick="javascript:today_page_oncheckboxclick(this);" {if $page_view=='1'}checked{/if} />{lang}Display All Tickets{/lang}</span>

<div style="font-size:11px;">
	<table cellpadding="2" cellspacing="2" border="0">
		<tr>
			<td><b>Star</b>&nbsp;</td>
			<td onclick="sort_page('priority');" style="cursor:pointer;"><b>Priority</b>&nbsp;</td>
			{*}<td><b>Type</b>&nbsp;</td>{*}
			<td onclick="sort_page('project');" style="cursor:pointer;"><b>{lang}Milestone{/lang}</b>&nbsp;</td>
			<td onclick="sort_page('name');" style="cursor:pointer;"><b>Name</b></td>
			<td onclick="sort_page('department');" style="cursor:pointer;"><b>Department</b>&nbsp;</td>
			<td><b>Due in</b></td>
		</tr>
		{foreach from=$entries item=entry}
		<tr>
			<td>{object_star object=$entry.obj user=$active_user}</td>
			<td>{object_priority object=$entry.obj}</td>
			{*}<td>{if $entry.logged_user_is_responsible}<b>{/if}{lang}{$entry.obj->getType()}{/lang}{if $entry.logged_user_is_responsible}</b>{/if}&nbsp;</td>{*}
			<td>{if ($entry.milestone_obj && $entry.milestone_obj->getViewUrl())}<a href="{$entry.milestone_obj->getViewUrl()}">{$entry.milestone_obj->getName()|clean}</a>{else}--{/if}</td>
			<td><a href="{$entry.obj->getViewUrl()}">{if $entry.logged_user_is_responsible}<b>{/if}{$entry.obj->getName()|clean}{if $entry.logged_user_is_responsible}</b>{/if}</a></td>
			<td>
			{foreach from=$entry.department item=department}
				{$department}<br />
			{/foreach}
			</td>
			{capture name=get_due_on assign=due_on_text}{due object=$entry.obj}{/capture}
			<td>{if $due_on_text=='No Due Date'}--{else}{$due_on_text}{/if}</td>
		</tr>
		{/foreach}
	</table>
</div>
