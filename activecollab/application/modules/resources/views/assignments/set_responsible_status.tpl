{title id=$ticket->getTicketId() name=$ticket->getName()}Ticket #:id: :name{/title}
{add_bread_crumb}Manage "Responsible" Status{/add_bread_crumb}
<table style="font-size:11px;">
	<tr>
		<td colspan="2" style="padding:10px;">
			Modify "Responsible" Status: 
		</td>
	</tr>
	{foreach from=$users item=user}
	<tr>
		<td style="padding:5px;"><input type="radio" name="is_responsible" value="{$user->getId()}" onclick="location.href='{$ticket_url}&is_responsible={$user->getId()}';" style="width:20px;" {if $owner && $owner->getId()==$user->getId()}checked{/if}  /></td>
		<td width="100%" style="padding:5px;">{$user->getName()}</td>
	</tr>
	{/foreach}
	<tr><td colspan="2" style="padding:10px;">Back to <a href="{$ticket_url}">{$ticket->getName()}</a></td></tr>
</table>