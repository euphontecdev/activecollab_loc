{title not_lang=yes}{lang name=$active_user->getDisplayName()}:name's Milestones{/lang}{/title}
{add_bread_crumb}{lang}Assigned milestones{/lang}{/add_bread_crumb}
<span style="font-size:11px;"><input name="page_view" type="checkbox" style="width:30px;" onclick="javascript:view_milestones_oncheckboxclick(this);" {if $page_view=='1'}checked{/if} />{lang}Display All Milestones{/lang}</span>
<div style="font-size:11px;">
	<table cellpadding="2" cellspacing="2" border="0">
		<tr>
			<td><b>Star</b>&nbsp;</td>
			<td onclick="sort_page('priority');" style="cursor:pointer;"><b>Priority</b>&nbsp;</td>
			<td onclick="sort_page('name');" style="cursor:pointer;"><b>Name</b></td>
			<td onclick="sort_page('department');" style="cursor:pointer;"><b>Department</b>&nbsp;</td>
		</tr>
		{foreach from=$milestones item=milestone}
		<tr>
			<td>{object_star object=$milestone.obj user=$active_user}</td>
			<td>{object_priority object=$milestone.obj}</td>
			<td><a href="{$milestone.obj->getViewUrl()}">{if $milestone.logged_user_is_responsible}<b>{/if}{$milestone.obj->getName()|clean}{if $milestone.logged_user_is_responsible}</b>{/if}</a></td>
			<td>
			{foreach from=$milestone.department item=department}
				{$department}<br />
			{/foreach}
			</td>
		</tr>
		{/foreach}
	</table>
</div>
