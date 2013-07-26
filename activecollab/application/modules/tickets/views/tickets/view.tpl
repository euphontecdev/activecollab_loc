{* 20 April 2012 (SA) Ticket #794: add print button to description section on tickets *}
<script type="text/javascript">
	if (location.hash.indexOf('#comment')!=-1 && location.href.indexOf('&comment_id=')==-1){ldelim}
		var comment_id = location.hash.replace(/#comment/, '');
		location.href = location.href.replace(location.hash, '') + '&comment_id=' + comment_id;
	{rdelim}
</script>
{title id=$active_ticket->getTicketId() name=$active_ticket->getName()}Ticket #:id: :name{/title}
{page_object object=$active_ticket}
{add_bread_crumb}Details{/add_bread_crumb}

{object_quick_options object=$active_ticket user=$logged_user}
<div class="ticket main_object" id="ticket{$active_ticket->getId()}">
  <div class="body">
  	<style>
  		table.table_info td {ldelim}line-height:24px;color: #666;border-bottom: 1px solid #f1f1f1;background: #f9f9f9; font-size:11px;{rdelim}
  	</style>
  	<table class="table_info">
  		<tr>
  			<td>{lang}Status{/lang}</td>
  			<td>{if $active_ticket->isCompleted()}{action_on_by user=$active_ticket->getCompletedBy() datetime=$active_ticket->getCompletedOn() action=Completed}{else}{lang}Open{/lang}{/if}</td>
  			<td>&nbsp;&nbsp;</td>
  			<td>{lang}Priority{/lang}</td>
  			<td>{object_priority_selection object=$active_ticket}</td>
  		</tr>
  		<tr>
  			<td align="left">{lang}Due on{/lang}</td>
  			<td>
  				<table width="100%">
  					<tr>
  						<td>{$active_ticket->getDueOn()|date:0}</td>
  						<td>{lang}Reminder{/lang}</td>
  						<td>{object_reminder object=$active_ticket}</td>
  					</tr>
  				</table>
			</td>
  			<td>&nbsp;&nbsp;</td>
  			<td>{lang}Recurring Every{/lang}</td>
  			<td>{object_recurring_period object=$active_ticket}</td>
  		</tr>
  		<tr>
  			<td>{lang}Owner{/lang}</td>
  			<td>{object_owner_selector object=$active_ticket}</td>
  			<td>&nbsp;&nbsp;</td>
  			<td>{lang}Action Request{/lang}</td>
  			<td>{object_action_request object=$active_ticket user=$logged_user}</td>
  		</tr>
		{if $logged_user->canSeeMilestones($active_project) && $active_ticket->getMilestoneId()}
			{if $active_ticket->hasAssignees()}
		<tr>
			<td>{lang}Milestone{/lang}</td>
			{*}<td>{milestone_link object=$active_ticket}</td>{*}
			<td>
				<span id="project_link">{milestone_link object=$active_ticket}&nbsp;<img id="project_selector" src="assets/images/icons/down_arrow.png" style="cursor:pointer;" /></span>
				<span id="project_list" style="display:none;">{object_project_selection object=$active_ticket user=$logged_user}</span>
			</td>
			<td>&nbsp;&nbsp;</td>
			<td>{lang}Assignees{/lang}</td>
			<td>{object_assignees object=$active_ticket}</td>
		</tr>
			{else}
		<tr>
			<td>{lang}Milestone{/lang}</td>
			{*}<td colspan="4">{milestone_link object=$active_ticket}</td>{*}
						<td>
				<span id="project_link">{milestone_link object=$active_ticket}&nbsp;<img id="project_selector" src="assets/images/icons/down_arrow.png" style="cursor:pointer;" /></span>
				<span id="project_list" style="display:none;">{object_project_selection object=$active_ticket user=$logged_user}</span>
			</td>
		</tr>
			{/if}
		{else}
			{if $active_ticket->hasAssignees()}
		<tr>
			<td>{lang}Assignees{/lang}</td>
			<td colspan="4">{object_assignees object=$active_ticket}</td>
		</tr>
			{/if}
		{/if}
		{if module_loaded('timetracking') && $logged_user->getProjectPermission('timerecord', $active_project)}
		<tr>
			<td>{lang}Department(s){/lang}</td>
			{*}<td>{object_departments object=$active_ticket}</td>{*}
			<td>
				<span id="department_link">{object_departments object=$active_ticket}&nbsp;<img id="department_selector" src="assets/images/icons/down_arrow.png" style="cursor:pointer;" /></span>
				<span id="department_list" style="display:none;">{object_department_selection object=$active_ticket}</span>
			</td>
			<td>&nbsp;&nbsp;</td>
			<td>{lang}Time{/lang}</td>
			<td>{object_time object=$active_ticket}</td>
		</tr>
		{else}
		<tr>
			<td>{lang}Department(s){/lang}</td>
			<td colspan="4">
				<span id="department_link">{object_departments object=$active_ticket}&nbsp;<img id="department_selector" src="assets/images/icons/down_arrow.png" style="cursor:pointer;" /></span>
				<span id="department_list" style="display:none;">{object_department_selection object=$active_ticket}</span>
			</td>
		</tr>
		{/if}
		<tr>
			<td colspan="5" align="right">{link href=$active_ticket->getPrintDescriptionUrl() title='Print Tasks'}<img src="{image_url name=icons/print.gif}" alt="" />{/link}</td>
		</tr>
  	</table>
    {*}<dl class="properties">
      <dt>{lang}Status{/lang}</dt>
    {if $active_ticket->isCompleted()}
      <dd>{action_on_by user=$active_ticket->getCompletedBy() datetime=$active_ticket->getCompletedOn() action=Completed}</dd>
    {else}
      <dd>{lang}Open{/lang}</dd>
    {/if}
    
      <dt>{lang}Priority{/lang}</dt>
      <dd>{$active_ticket->getFormattedPriority()}</dd>
      <dd>{object_priority_selection object=$active_ticket}</dd>
      
    {if $active_ticket->getDueOn()}
      <dt>{lang}Due on{/lang}</dt>
      <dd>{$active_ticket->getDueOn()|date:0}</dd>
    {/if}

      <dt style="background-color:#f9f9f9;">{lang}Reminder{/lang}</dt>
      <dd style="background-color:#f9f9f9;">{object_reminder object=$active_ticket}</dd>
      <br><br>
      <dt>{lang}Recurring Every{/lang}</dt>
      <dd>{object_recurring_period object=$active_ticket}</dd>
      
      <dt>{lang}Owner{/lang}</dt>
      <dd>{object_owner_selector object=$active_ticket}</dd>
      
    {if $active_ticket->hasAssignees()}
      <dt>{lang}Assignees{/lang}</dt>
      <dd>{object_assignees object=$active_ticket}</dd>
    {/if}
    
    {if $logged_user->canSeeMilestones($active_project) && $active_ticket->getMilestoneId()}
      <dt>{lang}Milestone{/lang}</dt>
      <dd>{milestone_link object=$active_ticket}</dd>
    {/if}
      <dt>{lang}Department(s){/lang}</dt>
      <dd>{object_departments object=$active_ticket}</dd>
    {if module_loaded('timetracking') && $logged_user->getProjectPermission('timerecord', $active_project)}
      <dt>{lang}Time{/lang}</dt>
      <dd>{object_time object=$active_ticket}</dd>
    {/if}
    
    {if $active_ticket->hasTags()}
      <dt>{lang}Tags{/lang}</dt>
      <dd>{object_tags object=$active_ticket}</dd>
    {/if}
    </dl>{*}
  
  {if $active_ticket->getBody()}
    <div class="body content" id="ticket_body_{$active_ticket->getId()}">{$active_ticket->getFormattedBody()}</div>
    {if $active_ticket->getSource() == $smarty.const.OBJECT_SOURCE_EMAIL}
      <script type="text/javascript">
        App.EmailObject.init('ticket_body_{$active_ticket->getId()}');
      </script>
    {/if}
  {else}
    <div class="body content details">{lang}Full description for this ticket is not provided{/lang}</div>
  {/if}
  </div>
  
  <div class="resources">
    {object_attachments object=$active_ticket}
    {object_subscriptions object=$active_ticket}
    {object_tasks object=$active_ticket}
    
    <div class="resource object_comments" id="comments">
      <div class="body">
          {*//BOF-20120228SA*}
      {*if $pagination->getLastPage() > 1}
        <p class="pagination top"><span class="inner_pagination">{lang}Page{/lang}: {pagination pager=$pagination}{$active_ticket->getViewUrl('-PAGE-')}{/pagination}</span></p>
        <div class="clear"></div>
        {/if*}
        
        {*if $pagination->getLastPage() > $pagination->getCurrentPage()}
          {object_comments object=$active_ticket comments=$comments show_header=no count_from=$count_start next_page=$active_ticket->getViewUrl($pagination->getNextPage())}
        {else}
          {object_comments object=$active_ticket comments=$comments show_header=no count_from=$count_start}
        {/if*}
        {*//EOF-20120228SA*}
		{if $show_all=='1'}
		{object_comments_all object=$active_ticket show_header=no}
		{else}
		{object_comments object=$active_ticket comments=$comments show_header=no count_from=$count_start view_url=$active_ticket->getViewUrl() current_page=$pagination->getCurrentPage() last_page=$pagination->getLastPage() scroll_to_comment=$scroll_to_comment}
		{/if}
      </div>
    </div>
    
    {ticket_changes ticket=$active_ticket}
  </div>
  {*}<script type="text/javascript">
    App.resources.SetResponsibleStatus.init('is_responsible');
  </script>{*}
  <script type="text/javascript">
	$('img#project_selector').click(function(){ldelim}
		$('span#project_link').css('display', 'none');
		$('span#project_list').css('display', '');
	{rdelim})
	$('img#department_selector').click(function(){ldelim}
		$('span#department_link').css('display', 'none');
		$('span#department_list').css('display', '');
	{rdelim})
  </script>
</div>