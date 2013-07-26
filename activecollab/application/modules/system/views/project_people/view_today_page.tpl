{*}{title not_lang=yes}{lang name=$active_user->getDisplayName()}:name's Allocations{/lang}{/title}{*}
{title not_lang=yes}{lang name=$active_user->getDisplayName()}Tickets for :name{/lang}{/title}
{add_bread_crumb}{lang}Assigned milestones{/lang}{/add_bread_crumb}
<div id="tabs" style="background:none;">
	<div style="height: 26px;padding-top: 4px;border-left: 1px solid #D1D0D0;border-right: 1px solid #D1D0D0;border-bottom:none;width:100%;background:rgb(212,208,163);">
		<ul style="margin: 0px 0px 0px 12px;padding: 0px;list-style: none;">
			<li style="display: inline;" id="page_tab_03"><a href="{$active_project->getUserTodayPageUrl($active_user)}&tab=tab03" {if $tab=='tab03'}class="current"{/if}><span>Home</span></a></li>
 			<li style="display: inline;" id="page_tab_01"><a href="{$active_project->getUserTodayPageUrl($active_user)}&tab=tab01" {if $tab=='tab01'}class="current"{/if}><span>{lang name=$active_user->getDisplayName()}Tickets owned by :name{/lang}</span></a></li>
 			<li style="display: inline;" id="page_tab_04"><a href="{$active_project->getUserTodayPageUrl($active_user)}&tab=tab04" {if $tab=='tab04'}class="current"{/if}><span>{lang name=$active_user->getDisplayName()}Tickets subscribed for :name{/lang}</span></a></li>
      	    <li style="display: inline;" id="page_tab_02"><a href="{$active_project->getUserTodayPageUrl($active_user)}&tab=tab02" {if $tab=='tab02'}class="current"{/if}><span>Inbox</span></a></li>
		</ul>
	</div>
</div>
{if $tab=='tab02'}
<div style="font-size:11px;">
	<fieldset>
		<legend>FYI Tickets</legend>
	<form name="entries">
	<table cellpadding="2" cellspacing="5" border="0">
		{foreach from=$entries item=entry}
		<tr>
			{*}<td style="width:50px;">
				<input type="checkbox" name="chkSelected[]" value="{$entry.obj->getId()}" style="width:50px;" />
			</td>{*}
			<td align="left">	
				<a href="{$entry.obj->getViewUrl()}">{if $entry.logged_user_is_responsible}<b>{/if}{$entry.obj->getName()|strip_tags:true}{if $entry.logged_user_is_responsible}</b>{/if}</a>
			</td>
		</tr>
		{/foreach}
		<tr><td colspan="2">&nbsp;</td></tr>
		{*}<tr>
			<td align="left" colspan="2">
				<select name="drpAction" onchange="set_complete_status(this);">
					<option value="">-- Select Action --</option>
					<option value="complete">Mark as Completed</option>
				</select>
			</td>
		</tr>{*}
		<tr><td colspan="2">&nbsp;</td></tr>
	</table>
	</fieldset>
	</form>
	
	<fieldset>
	<legend>Action Request Comments</legend>
	<form name="action_request_comments">
	{foreach from=$action_request_comments item=action_request}
		<a href="{$action_request->getRealViewUrl(true)}">{$action_request->getBody()}</a><hr />
	{/foreach}
	</form>
	</fieldset>
	
	<fieldset>
	<legend>FYI Comments</legend>
	<form name="fyi_comments">
	{foreach from=$fyi_comments item=fyi}
		<div>
			{if $active_user->getId()==$logged_user->getId()}
			<div>
				<input type="checkbox" id="chk_fyi_user_cID_{$fyi->getId()}_userID_{$logged_user->getId()}" style="width:20px;" onclick="set_action_request_fyi_flag(this, true);" />Mark as Read<br/>
			</div>
			{/if}
			<a href="{$fyi->getRealViewUrl(true)}">{$fyi->getBody()}</a><hr />
		</div>
	{/foreach}
	</form>
	</fieldset>
	
	<fieldset>
	<legend>Read Notifications</legend>
	<form name="fyi_read_comments" id="fyi_read_comments">
	{foreach from=$fyi_read_comments item=fyi_read}
		<div><a href="{$fyi_read->getRealViewUrl(true)}">{$fyi_read->getBody()}</a><hr /></div>
	{/foreach}
	</form>
	</fieldset>
</div>
{* BOF:mod 20110704 ticketid134 *}
{*}
{elseif $tab=='tab01'} 
<span style="font-size:11px;"><input name="page_view" type="checkbox" style="width:30px;" onclick="javascript:today_page_oncheckboxclick(this);" {if $page_view=='1'}checked{/if} />{lang}Display All Tickets{/lang}</span>
{*}
{elseif $tab=='tab01' || $tab=='tab04'}
<div align="right" style="margin:15px 0 15px 0;">
{lang}Select Project: {/lang}
<select name="selected_project_id" id="selected_project_id" onchange="javascript:today_page_onchange(this);">
<option value="">{lang}All Projects{/lang}</option>
{foreach from=$user_projects item=user_project}
<option value="{$user_project->getId()}" {if $selected_project==$user_project->getId()}selected{/if}>{$user_project->getName()}</option>
{/foreach}
</select>
</div>
{* EOF:mod 20110704 ticketid134 *}
<div style="font-size:11px;">
	<table cellpadding="2" cellspacing="5" border="0">
		<tr>
			<td onclick="sort_page('star');" style="cursor:pointer;" title="Sort by Star" class="sort_column">Star</td>
			<td onclick="sort_page('priority');" style="cursor:pointer;" title="Sort by Priority" class="sort_column">Priority</td>
			{*}<td><b>Type</b>&nbsp;</td>{*}
			{* BOF:mod 20110704 ticketid134 *}
			<td onclick="sort_page('team');" style="cursor:pointer;" title="Sort by Team" class="sort_column">{lang}Project{/lang}</td>
			{* EOF:mod 20110704 ticketid134 *}
			<td onclick="sort_page('project');" style="cursor:pointer;" title="Sort by Project" class="sort_column">{lang}Milestone{/lang}</td>
			<td onclick="sort_page('name');" style="cursor:pointer;" title="Sort by Name" class="sort_column">Name</td>
			<td onclick="sort_page('department');" style="cursor:pointer;" title="Sort by Department" class="sort_column">Department</td>
			<td onclick="sort_page('duedate');" style="cursor:pointer;" title="Sort by Due Date" class="sort_column">Due Date</td>
		</tr>
		{foreach from=$entries item=entry}
		<tr>
			<td>{object_star object=$entry.obj user=$active_user}</td>
			<td class="priority obj_{$entry.obj->getId()}">{object_priority object=$entry.obj}</td>
			{*}<td>{if $entry.logged_user_is_responsible}<b>{/if}{lang}{$entry.obj->getType()}{/lang}{if $entry.logged_user_is_responsible}</b>{/if}&nbsp;</td>{*}
			{* BOF:mod 20110704 ticketid134 *}
			<td>{$entry.team_name}</td>
			{* EOF:mod 20110704 ticketid134 *}
			<td>{if ($entry.milestone_obj && $entry.milestone_obj->getViewUrl())}<a href="{$entry.milestone_obj->getViewUrl()}">{$entry.milestone_obj->getName()|clean}</a>{else}--{/if}</td>
			<td><a href="{$entry.obj->getViewUrl()}">{if $entry.logged_user_is_responsible}<b>{/if}{$entry.obj->getName()|strip_tags:true}{if $entry.logged_user_is_responsible}</b>{/if}</a></td>
			<td>
			{foreach from=$entry.department item=department}
				{$department}<br />
			{/foreach}
			</td>
			{*}{capture name=get_due_on assign=due_on_text}{due object=$entry.obj}{/capture}
			<td>{if $due_on_text=='No Due Date'}--{else}{$due_on_text}{/if}</td>{*}
			{*}<td>{$entry.obj->getDueOn()}</td>{*}
			<td>{if $entry.obj->getDueOn()!=''}{date_set_format value=$entry.obj->getDueOn() format='mmddyyyy'}{/if}</td>
		</tr>
		{/foreach}
	</table>
</div>
{elseif $tab=='tab03'}
<div>
	<div>
		{$home_tab_content}</td>
	</div>	
</div>
{/if}
<span id="priority_pulldown_container" style="display:none">
    <select id="priority_selector" style="width:80px;">
        <option value="2">Highest</option>
        <option value="1">High</option>
        <option value="0">Normal</option>
        <option value="-1">Low</option>
        <option value="-2">Lowest</option>
        <option value="-3">Ongoing</option>
        <option value="-4">On Hold</option>
    </select>
</span>
<script type="text/javascript">
    manage_priority();
</script>