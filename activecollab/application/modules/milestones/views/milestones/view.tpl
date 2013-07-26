<script type="text/javascript">
	if (location.hash.indexOf('#comment')!=-1 && location.href.indexOf('&comment_id=')==-1){ldelim}
		var comment_id = location.hash.replace(/#comment/, '');
		location.href = location.href.replace(location.hash, '') + '&comment_id=' + comment_id;
	{rdelim}
</script>
{page_object object=$active_milestone}
{add_bread_crumb}Details{/add_bread_crumb}

{object_quick_options object=$active_milestone user=$logged_user}
<div class="milestone main_object" id="milestone{$active_milestone->getId()}">
  <div class="body">
    <dl class="properties">
      <dt>{lang}Status{/lang}</dt>
    {if $active_milestone->isCompleted()}
      <dd>{action_on_by user=$active_milestone->getCompletedBy() datetime=$active_milestone->getCompletedOn() action='Completed'}</dd>
    {else}
      <dd>{lang}Active{/lang}</dd>
    {/if}
    
    {if $active_milestone->isDayMilestone()}
      <dt>{lang}Due On{/lang}</dt>
      <dd>{$active_milestone->getDueOn()|date:0}</dd>
    {else}
      <dt>{lang}From / To{/lang}</dt>
      {if $active_milestone->getStartOn()=='' && $active_milestone->getDueOn()==''}
      	<dd>&mdash;</dd>
      {else}
		<dd>{$active_milestone->getStartOn()|date:0} &mdash; {$active_milestone->getDueOn()|date:0}</dd>
      {/if}
    {/if}
      
      <dt>{lang}Priority{/lang}</dt>
      <dd>{$active_milestone->getFormattedPriority()|clean}</dd>
      
    {if $active_milestone->hasAssignees(true)}
      <dt>{lang}Assignees{/lang}</dt>
      <dd>{object_assignees object=$active_milestone}</dd>
    {/if}
      <dt>{lang}Department(s){/lang}</dt>
      <dd>
		<span id="department_link">{object_departments object=$active_milestone}&nbsp;<img id="department_selector" src="assets/images/icons/down_arrow.png" style="cursor:pointer;" /></span>
		<span id="department_list" style="display:none;">{object_department_selection object=$active_milestone}</span>
	  </dd>
    {if $active_milestone->hasTags()}
      <dt>{lang}Tags{/lang}</dt>
      <dd>{object_tags object=$active_milestone}</dd>
    {/if}
    
    {if $milestone_add_links_code}
      <dt>{lang}Add to Milestone{/lang}</dt>
      <dd>{$milestone_add_links_code}</dd>
    {/if}
    </dl>
  </div>
  
  {if $active_milestone->getBody()}
    <div class="body content">{$active_milestone->getFormattedBody()}</div>
  {else}
    <div class="body content details">{lang}No notes...{/lang}</div>
  {/if}
   
  <div class="resource">
	{*}BOF:mod 20120907{*}{object_tasks object=$active_milestone}{*}EOF:mod 20120907{*}
    {if $total_objects && is_foreachable($milestone_objects)}
      {foreach from=$milestone_objects key=section_name item=objects}
      {if is_foreachable($objects)}
        <div class="resource">
          <div class="head">
            <h2 class="section_name"><span class="section_name_span">{$section_name}
            {if $section_name=='Pages'}
				<span style="float:right;font-weight:normal;">{object_show_checkbox milestone=$active_milestone section_name=$section_name is_checked=$show_archived_pages}</span>
			{elseif $section_name=='Tickets'}
				<span style="float:right;font-weight:normal;">{object_show_checkbox milestone=$active_milestone section_name=$section_name is_checked=$show_completed_tkts}</span>
			{/if}
			</span></h2>
          </div>
          <div class="body">
            {if $section_name=='Pages'}
				{list_objects objects=$objects show_checkboxes=no show_header=no section_name=$section_name is_checked=$show_archived_pages}
			{elseif $section_name=='Tickets'}
				{list_objects objects=$objects show_checkboxes=no show_header=no section_name=$section_name is_checked=$show_completed_tkts}
			{else}
				{list_objects objects=$objects show_checkboxes=no show_header=no}
			{/if}
          </div>
        </div>
      {/if}
      {/foreach}
    {else}
      <div class="body content details">{lang}No tasks in this milestone...{/lang}</div>
    {/if}
    {*BOF:mod 20110708 ticketid202*}
    {object_attachments object=$active_milestone}
    {*EOF:mod 20110708 ticketid202*}
    {object_subscriptions object=$active_milestone}
    {*object_comments object=$active_milestone*}
	{if $show_all=='1'}
	{object_comments_all object=$active_milestone show_header=no}
	{else}
	{object_comments object=$active_milestone comments=$comments show_header=no count_from=$count_start view_url=$active_milestone->getViewUrl() current_page=$pagination->getCurrentPage() last_page=$pagination->getLastPage() scroll_to_comment=$scroll_to_comment}
	{/if}
  </div>
  <script type="text/javascript">
	$('img#department_selector').click(function(){ldelim}
		$('span#department_link').css('display', 'none');
		$('span#department_list').css('display', '');
	{rdelim})
  </script>  
</div>