{title}Email Notifications{/title}
{add_bread_crumb}Email Notifications{/add_bread_crumb}
<div id="divFlag"><input type="checkbox" style="width:50px;" onclick="javascript:view_reminders_oncheckboxclick(this);" {if $flag=='1'} checked {/if} />Reminders Only</div>
<div>
	<table id="file_list" class="common_table">
        <tr>
          <th>{lang}Date{/lang}</th>
          <th>{lang}Type{/lang}</th>
          <th>{lang}Name{/lang}</th>
          <th>{lang}Comment{/lang}</th>
          <th>{lang}Sent By{/lang}</th>
          <th>{lang}Sent To{/lang}</th>
        </tr>
        <tbody>
        {foreach from=$reminders item=reminder}
        <tr class="file {cycle values='odd,even'}">
			<td valign="top">{$reminder.sent_on}</td>
			<td valign="top">{$reminder.object->getType()}</td>
			<td valign="top"><a href="{$reminder.object->getViewUrl()}">{$reminder.object->getName()}</a></td>
			<td valign="top">{$reminder.comment}</td>
			<td valign="top"><a href="{$reminder.sent_by->getViewUrl()}">{$reminder.sent_by->getName()}</a></td>
			<td valign="top"><a href="{$reminder.sent_to->getViewUrl()}">{$reminder.sent_to->getName()}</a></td>
		</tr>		
        {/foreach}
        </tbody>
	</table>
</div>
{*}<script type="text/javascript">
	try{ldelim}
		var title_coords = $('.page_info_container').offset();
		$('#divFlag').css('top', title_coords.top);
		$('#divFlag').css('left', title_coords.left);
		var div_coords = $('#divFlag').offset();
		//$('#divFlag').offset( top: title_coords.top, left: title_coords.left );
		alert(title_coords.top + ' | ' + title_coords.left + '\n' + div_coords.top + ' | ' + div_coords.left);
		//var top = $('.page_info_container').css('top');
		//$('#chkFlag').css('top', '-200');
	{rdelim} catch(e){ldelim}
		alert(e);
	{rdelim}
		 
</script>{*}