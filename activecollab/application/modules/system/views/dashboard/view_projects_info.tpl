{title}Dashboard{/title}
{add_bread_crumb}View{/add_bread_crumb}

<div id="div_info">
<table id="tab_info">
{foreach from=$details item=project}
	<tr>
		<td colspan="4" valign="top"><h2 class="section_name"><span class="section_name_span">Project: <a href="{$project.project_url}">{$project.project_name}</a></span></h2></td>
	</tr>
	{foreach from=$project.milestones item=milestone}
	<tr>
		<td><b>Milestone</b>&nbsp;</td>
		<td colspan="3"><a href="{$milestone.milestone_url}">{$milestone.milestone_name}</a></td>
	</tr>
		{foreach from=$milestone.tickets item=ticket}
		<tr>
			<td>&nbsp;&nbsp;</td>
			<td><b>Ticket</b>&nbsp;</td>
			<td colspan="2" valign="top"><a href="{$ticket.ticket_url}">{$ticket.ticket_name}</a></td>
		</tr>
			{foreach from=$ticket.tasks item=task}
			<tr>
				<td>&nbsp;&nbsp;</td>
				<td>&nbsp;&nbsp;</td>
				<td><b>Task</b>&nbsp;</td>
				<td>{$task.task_body}</td>
			</tr>
			{/foreach}
		{/foreach}
	{/foreach}
{/foreach}
</table>
</div>