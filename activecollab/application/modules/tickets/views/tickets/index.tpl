{title}Tickets{/title}
{if $current_department_id=='-99'}
    {add_bread_crumb}All Closed Tickets{/add_bread_crumb}
{else}
    {if $active_category->isLoaded()}
        {add_bread_crumb}Open Tickets{/add_bread_crumb}
    {else}
        {add_bread_crumb}All Open Tickets{/add_bread_crumb}
    {/if}
{/if}
{*}
{if $selected_milestone_id!=''}
  {add_bread_crumb}Open Tickets{/add_bread_crumb}
{else}
  {add_bread_crumb}All Open Tickets{/add_bread_crumb}
{/if}
<div class="list_view small_list_view">
	<div id="tickets" class="object_list">
		{if is_foreachable($tickets)}
		{form action=$mass_edit_tickets_url method=post}
			{foreach from=$tickets item=tickets_by_milestone}
				{*}{if is_foreachable($tickets_by_milestone.objects)}{*}
					{if $logged_user->canSeeMilestones($active_project)}
						{if instance_of($tickets_by_milestone.milestone, 'Milestone')}
							{if $can_add_ticket}
	                			<h2 class="section_name">
									<span class="section_name_span">
	                  					<span class="section_name_span_span">{object_link object=$tickets_by_milestone.milestone}</span>
	                  						<ul class="section_options">
	                    						{assign_var name=add_ticket_to_milestone_url}{assemble route=project_tickets_add project_id=$active_project->getId() milestone_id=$tickets_by_milestone.milestone->getId()}{/assign_var}
	                    						<li><a href="{$add_ticket_to_milestone_url}">{lang}Add Ticket{/lang}</a></li>
	                  						</ul>
	                  						<div class="clear"></div>
	                				</span>
								</h2>
							{/if}
						{else}
							<h2 class="section_name"><span class="section_name_span">{lang}Unknown Milestone{/lang}</span></h2>
						{/if}
					{/if}

					<div class="section_container visible_overflow">
	            		{if instance_of($tickets_by_milestone.milestone, 'Milestone')}
    	          			{assign_var name=reorder_tickets_url}{assemble route=project_tickets_reorder project_id=$active_project->getId() milestone_id=$tickets_by_milestone.milestone->getId() async=1}{/assign_var}
        	      			{assign_var name=milestone_name}{$tickets_by_milestone.milestone->getName()}{/assign_var}
            			{else}
              				{assign_var name=reorder_tickets_url}{assemble route=project_tickets_reorder project_id=$active_project->getId() milestone_id=0 async=1}{/assign_var}
              				{assign_var name=milestone_name}Unknown{/assign_var}
	            		{/if}
    	        		<ul class="tickets_list common_table_list" reorder_url='{$reorder_tickets_url}'>
        	    		{foreach from=$tickets_by_milestone.objects item=ticket}
            	  			<li class="ticket {cycle values='odd,even' name=$milestone_name} sort" id="ticket{$ticket->getId()}">
                				<span class="left_options">
                  					<span class="option star">{object_star object=$ticket user=$logged_user}</span>
                  					<span class="option">{object_priority object=$ticket}</span>
                  					<span class="option ticket_id">#{$ticket->getTicketId()}</span>
	                			</span>
    	            			<span class="right_options">
        	        				{if $logged_user->canSeePrivate()}
            	      					<span class="option">{object_visibility object=$ticket user=$logged_user}</span>
                					{/if}
                  					<span class="option"><input type="checkbox" name="tickets[]" value="{$ticket->getId()}" class="auto input_checkbox" /></span>
                				</span>
                				<span class="main_data">
                  					<a href="{$ticket->getViewUrl()}">{$ticket->getName()|clean}</a>
                  					<input type="hidden" name="reorder_ticket[{$ticket->getId()}]" />
                				</span>
              				</li>
              			{/foreach}
              				<li class="empty_row" style="{if is_foreachable($tickets_by_milestone.objects)}display: none;{/if}">{lang}There are no tickets in this milestone{/lang}</li>
            			</ul>
					</div>
				{*}{/if}{*}
			{/foreach}
	{if $selected_milestone_id=='' || ($selected_milestone_id!='' && $tickets_count>0)}
      <div id="mass_edit">
        <select name="with_selected" id="tickets_action" class="auto">
          <option value="">{lang}With Selected ...{/lang}</option>
          <option value=""></option>
          <option value="complete">{lang}Mark as Completed{/lang}</option>
          <option value=""></option>
          <option value="star">{lang}Star{/lang}</option>
          <option value="unstar">{lang}Unstar{/lang}</option>
          <option value=""></option>
          <option value="trash">{lang}Move to Trash{/lang}</option>
          <option value=""></option>

          <optgroup label="{lang}Visibility{/lang}">
            <option value="set_visibility_0">{lang}Private{/lang}</option>
            <option value="set_visibility_1">{lang}Normal{/lang}</option>
          </optgroup>
          <option value=""></option>

          <optgroup label="{lang}Change priority{/lang}">
            <option value="set_priority_2">{lang}Highest{/lang}</option>
            <option value="set_priority_1">{lang}High{/lang}</option>
            <option value="set_priority_0">{lang}Normal{/lang}</option>
            <option value="set_priority_-1">{lang}Low{/lang}</option>
            <option value="set_priority_-2">{lang}Lowest{/lang}</option>
            <option value="set_priority_-3">{lang}Ongoing{/lang}</option>
            <option value="set_priority_-4">{lang}On Hold{/lang}</option>
          </optgroup>

      {if $logged_user->canSeeMilestones($active_project) && is_foreachable($milestones)}
          <option value=""></option>
          <optgroup label="{lang}Move to milestone{/lang}">
            <option value="move_to_milestone">{lang}&lt;None&gt;{/lang}</option>
        {foreach from=$milestones item=milestone}
            <option value="move_to_milestone_{$milestone->getId()}">{$milestone->getName()|clean}</option>
        {/foreach}
          </optgroup>
      {/if}

          <option value=""></option>
          <optgroup label="{lang}Move to category{/lang}">
            <option value="move_to_category">{lang}&lt;None&gt;{/lang}</option>
        {foreach from=$categories item=category}
            <option value="move_to_category_{$category->getId()}">{$category->getName()|clean}</option>
        {/foreach}
          </optgroup>
        </select>
        {button id="tickets_submit" type="submit"}Go{/button}
      </div>
      {/if}
      <div class="clear"></div>
    {/form}
  		{else}
    		<p class="empty_page">{lang}No tickets here{/lang}. {if $add_ticket_url}{lang add_url=$add_ticket_url}Would you like to <a href=":add_url">create one</a>{/lang}?{/if}</p>
    		{empty_slate name=tickets module=tickets}
		{/if}
    <p class="archive_link">{link href=$tickets_archive_url}Archive{/link}</p>
  </div>

  	<ul class="category_list">
    	<li {if $selected_milestone_id==''}class="selected"{/if}><a href="{$tickets_url}"><span>{lang}All Open Tickets{/lang}</span></a></li>
  		{if is_foreachable($milestones)}
    		{foreach from=$milestones item=milestone}
    			<li {if $selected_milestone_id == $milestone->getId()}class="selected"{/if}><a href="{assemble route=project_tickets project_id=$active_project->getId() milestone_id=$milestone->getId()}"><span>{$milestone->getName()|clean}</span></a></li>
    		{/foreach}
  		{/if}
  		{if $can_manage_categories}
    		<li id="manage_categories"><a href="{$categories_url}"><span>{lang}Manage Categories{/lang}</span></a></li>
  		{/if}
  	</ul>
  	<script type="text/javascript">
    	App.resources.ManageCategories.init('manage_categories');
  	</script>
  	<div class="clear"></div>
</div>
{*}
<div class="list_view small_list_view">
	<div>
	<select onchange="set_milestone_page(this);">
		<option value="">{lang}Jump to Milestone{/lang}</option>
        {foreach from=$all_milestones item=milestone}
            <option value="{$milestone.id}" {if $milestone.id==$current_milestone}selected{/if}>{$milestone.name}</option>
        {/foreach}
	</select>
	&nbsp;
	Page#<select onchange="set_ticket_page(this);" style="width:50px;">
		{section name=page start=1 loop=$total_pages+1}
			<option value="{$smarty.section.page.index}" {if $smarty.section.page.index==$current_page}selected{/if}>{$smarty.section.page.index}</option>
		{/section}
	</select> of {$total_pages}
	</div>
  <div id="tickets" class="object_list">
  		{* BOF:mod 20110704 ticketid215 *}
  		<div class="section_container visible_overflow">
        <ul class="tickets_list common_table_list" reorder_url='{$reorder_tickets_url}'>
              <li class="ticket">
                <span class="left_options">
                	<span class="option"><b>Star</b></span>
                	<span class="option"><b>Priority</b></span>
				</span>
                <span class="right_options">
                	{* BOF:mod 20110706 *}
                	{*}
                	<span class="option"><b>Last Comment</b></span>
                	<span class="option"><b>Owner</b></span>
                	<span class="option"><b>Action</b></span>
                	{*}
                	<div class="option" style="float:left;width:90px;" align="left"><b>Last Comment</b></div>
                	<div class="option" style="float:left;width:50px;" align="left"><b>Owner</b></div>
                	<div class="option" style="float:left;width:20px;" align="left">&nbsp;</div>
                	{* EOF:mod 20110706 *}
				</span>
                <span class="main_data"><b>Ticket Name</b></span>
              </li>
		</ul>
		</div>
		{* EOF:mod 20110704 ticketid215 *}
  {if is_foreachable($groupped_tickets)}
    {form action=$mass_edit_tickets_url method=post}
     {if $sort_by=='category' || $sort_by=='milestone'}
      {foreach from=$groupped_tickets item=tickets_by_milestone}
        {if is_foreachable($tickets_by_milestone.objects)}

          <!-- Header -->
          {if $logged_user->canSeeMilestones($active_project)}
            {if instance_of($tickets_by_milestone.milestone, 'Milestone')}
              {if $can_add_ticket}
                <h2 class="section_name"><span class="section_name_span">
                  <span class="section_name_span_span">{object_link object=$tickets_by_milestone.milestone}</span>
                  <ul class="section_options">
                    {assign_var name=add_ticket_to_milestone_url}{assemble route=project_tickets_add project_id=$active_project->getId() milestone_id=$tickets_by_milestone.milestone->getId()}{/assign_var}
                    <li><a href="{$add_ticket_to_milestone_url}">{lang}Add Ticket{/lang}</a></li>
                  </ul>
                  <div class="clear"></div>
                </span></h2>
              {else}
                <h2 class="section_name"><span class="section_name_span">{object_link object=$tickets_by_milestone.milestone}</span></h2>
              {/if}
            {else}
              <h2 class="section_name"><span class="section_name_span">{lang}Unknown Milestone{/lang}</span></h2>
            {/if}
          {/if}

          <div class="section_container visible_overflow">
            {if instance_of($tickets_by_milestone.milestone, 'Milestone')}
              {assign_var name=reorder_tickets_url}{assemble route=project_tickets_reorder project_id=$active_project->getId() milestone_id=$tickets_by_milestone.milestone->getId() async=1}{/assign_var}
              {assign_var name=milestone_name}{$tickets_by_milestone.milestone->getName()}{/assign_var}
            {else}
              {assign_var name=reorder_tickets_url}{assemble route=project_tickets_reorder project_id=$active_project->getId() milestone_id=0 async=1}{/assign_var}
              {assign_var name=milestone_name}Unknown{/assign_var}
            {/if}
            <ul class="tickets_list common_table_list" reorder_url='{$reorder_tickets_url}'>
            {foreach from=$tickets_by_milestone.objects item=ticket}
              <li class="ticket {cycle values='odd,even' name=$milestone_name} sort" id="ticket{$ticket->getId()}">
                <span class="left_options">
                  <span class="option star">{object_star object=$ticket user=$logged_user}</span>
                  {*}<span class="option">{object_priority object=$ticket}</span>{*}
				  <span class="option">{select_priority_images name=$ticket->getId() value=$ticket->getPriority() url=$ticket->getAjaxChangePriorityUrl()}</span>
                  {* BOF:mod 20110704 ticketid215 *}
                  {*}<span class="option ticket_id">#{$ticket->getTicketId()}</span>{*}
                  {* EOF:mod 20110704 ticketid215 *}
                </span>
                <span class="right_options">
                {if $logged_user->canSeePrivate()}
                  <span class="option">{object_visibility object=$ticket user=$logged_user}</span>
                {/if}
                {* BOF:mod 20110704 ticketid215 *}
                <div style="float:left;width:90px;" align="left">{object_last_comment object=$ticket}</div>
                {* EOF:mod 20110704 ticketid215 *}
                <div style="float:left;width:50px;" align="left">{object_owner object=$ticket}&nbsp;</div>
                <div class="option" style="float:left;width:20px;" align="right"><input type="checkbox" name="tickets[]" value="{$ticket->getId()}" class="auto input_checkbox" /></div>
                </span>
                <span class="main_data">
                  <a href="{$ticket->getViewUrl()}">{$ticket->getName()|clean}</a>
                  <input type="hidden" name="reorder_ticket[{$ticket->getId()}]" />
                </span>
              </li>
              {/foreach}
              <li class="empty_row" style="{if is_foreachable($tickets_by_milestone.objects)}display: none;{/if}">{lang}There are no tickets in this milestone{/lang}</li>
            </ul>
          </div>
        {/if}
      {/foreach}
     {elseif $sort_by=='star' || $sort_by=='priority' || $sort_by=='name' || $sort_by=='owner'}
          <div class="section_container visible_overflow">
            <ul class="tickets_list common_table_list" reorder_url='{$reorder_tickets_url}'>
            {foreach from=$groupped_tickets item=ticket}
	            {if instance_of($ticket.milestone, 'Milestone')}
	              {assign_var name=reorder_tickets_url}{assemble route=project_tickets_reorder project_id=$active_project->getId() milestone_id=$ticket.milestone->getId() async=1}{/assign_var}
	              {assign_var name=milestone_name}{$ticket.milestone->getName()}{/assign_var}
	            {else}
	              {assign_var name=reorder_tickets_url}{assemble route=project_tickets_reorder project_id=$active_project->getId() milestone_id=0 async=1}{/assign_var}
	              {assign_var name=milestone_name}Unknown{/assign_var}
	            {/if}
              <li class="ticket {cycle values='odd,even' name=$milestone_name} sort" id="ticket{$ticket.ticket->getId()}">
                <span class="left_options">
                  <span class="option star">{object_star object=$ticket.ticket user=$logged_user}</span>
                  {*}<span class="option">{object_priority object=$ticket.ticket}</span>{*}
				  <span class="option">{select_priority_images name=$ticket->getId() value=$ticket->getPriority() url=$ticket->getAjaxChangePriorityUrl()}</span>
                  <span class="option ticket_id">#{$ticket.ticket->getTicketId()}</span>
                </span>
                <span class="right_options">
                {if $logged_user->canSeePrivate()}
                  <span class="option">{object_visibility object=$ticket.ticket user=$logged_user}</span>
                {/if}
                <span>{object_owner object=$ticket.ticket}</span>
                  <span class="option"><input type="checkbox" name="tickets[]" value="{$ticket.ticket->getId()}" class="auto input_checkbox" /></span>
                </span>
                <span class="main_data">
                  <a href="{$ticket.ticket->getViewUrl()}">{$ticket.ticket->getName()|clean}</a>
                  <input type="hidden" name="reorder_ticket[{$ticket.ticket->getId()}]" />
                </span>
              </li>
              {/foreach}
              <li class="empty_row" style="{if is_foreachable($groupped_tickets)}display: none;{/if}">{lang}There are no tickets in this milestone{/lang}</li>
            </ul>
          </div>
     {/if}
	<div>
	<select onchange="set_milestone_page(this);">
		<option value="">{lang}Jump to Milestone{/lang}</option>
        {foreach from=$all_milestones item=milestone}
            <option value="{$milestone.id}" {if $milestone.id==$current_milestone}selected{/if}>{$milestone.name}</option>
        {/foreach}
	</select>
	&nbsp;
	Page#<select onchange="set_ticket_page(this);" style="width:50px;">
		{section name=page start=1 loop=$total_pages+1}
			<option value="{$smarty.section.page.index}" {if $smarty.section.page.index==$current_page}selected{/if}>{$smarty.section.page.index}</option>
		{/section}
	</select> of {$total_pages}
	</div>
      <div id="mass_edit">
        <select name="with_selected" id="tickets_action" class="auto">
          <option value="">{lang}With Selected ...{/lang}</option>
          <option value=""></option>
          <option value="complete">{lang}Mark as Completed{/lang}</option>
          <option value=""></option>
          <option value="star">{lang}Star{/lang}</option>
          <option value="unstar">{lang}Unstar{/lang}</option>
          <option value=""></option>
          <option value="trash">{lang}Move to Trash{/lang}</option>
          <option value=""></option>

          <optgroup label="{lang}Visibility{/lang}">
            <option value="set_visibility_0">{lang}Private{/lang}</option>
            <option value="set_visibility_1">{lang}Normal{/lang}</option>
          </optgroup>
          <option value=""></option>

          <optgroup label="{lang}Change priority{/lang}">
		    <option value="set_priority_3">{lang}Urgent{/lang}</option>
            <option value="set_priority_2">{lang}Highest{/lang}</option>
            <option value="set_priority_1">{lang}High{/lang}</option>
            <option value="set_priority_0">{lang}Normal{/lang}</option>
            <option value="set_priority_-1">{lang}Low{/lang}</option>
            <option value="set_priority_-2">{lang}Lowest{/lang}</option>
            {*}<option value="set_priority_-3">{lang}Ongoing{/lang}</option>{*}
            <option value="set_priority_-4">{lang}On Hold{/lang}</option>
          </optgroup>

      {if $logged_user->canSeeMilestones($active_project) && is_foreachable($milestones)}
          <option value=""></option>
          <optgroup label="{lang}Move to milestone{/lang}">
            <option value="move_to_milestone">{lang}&lt;None&gt;{/lang}</option>
        {foreach from=$milestones item=milestone}
            <option value="move_to_milestone_{$milestone->getId()}">{$milestone->getName()|clean}</option>
        {/foreach}
          </optgroup>
      {/if}

          <option value=""></option>
          <optgroup label="{lang}Move to category{/lang}">
            <option value="move_to_category">{lang}&lt;None&gt;{/lang}</option>
        {foreach from=$categories item=category}
            <option value="move_to_category_{$category->getId()}">{$category->getName()|clean}</option>
        {/foreach}
          </optgroup>
        </select>
        {button id="tickets_submit" type="submit"}Go{/button}
      </div>
      <div class="clear"></div>
    {/form}
  {else}
    <p class="empty_page">{lang}No tickets here{/lang}. {if $add_ticket_url}{lang add_url=$add_ticket_url}Would you like to <a href=":add_url">create one</a>{/lang}?{/if}</p>
    {empty_slate name=tickets module=tickets}
  {/if}

    <p class="archive_link">{link href=$tickets_archive_url}Archive{/link}</p>
  </div>

  {*}<ul class="category_list">
    <li {if $active_category->isNew()}class="selected"{/if}><a href="{$tickets_url}"><span>{lang}All Open Tickets{/lang}</span></a></li>
  {if is_foreachable($categories)}
    {foreach from=$categories item=category}
    <li category_id="{$category->getId()}" {if $active_category->isLoaded() && $active_category->getId() == $category->getId()}class="selected"{/if}><a href="{assemble route=project_tickets project_id=$active_project->getId() category_id=$category->getId()}"><span>{$category->getName()|clean}</span></a></li>
    {/foreach}
  {/if}
  {if $can_manage_categories}
    <li id="manage_categories"><a href="{$departments_url}"><span>{lang}Edit Departments{/lang}</span></a></li>
  {/if}
  </ul>{*}
  <ul class="category_list">
    <li {if $current_department_id==''}class="selected"{/if}><a href="{$tickets_url}"><span>{lang}All Open Tickets{/lang}</span></a></li>
  {if is_foreachable($departments)}
    {foreach from=$departments item=department}
    <li category_id="{$department.department_id}" {if $department.department_id == $current_department_id}class="selected"{/if}><a href="{assemble route=project_tickets project_id=$active_project->getId()}&department_id={$department.department_id}"><span>{$department.department_name|clean}</span></a></li>
    {/foreach}
  {/if}
  <li {if $current_department_id=='-99'}class="selected"{/if}><a href="{assemble route=project_tickets project_id=$active_project->getId()}&department_id=-99"><span>{lang}All Closed Tickets{/lang}</span></a></li>
  {if $can_manage_categories}
    <li id="manage_categories"><a href="{$departments_url}"><span>{lang}Edit Departments{/lang}</span></a></li>
  {/if}
  </ul>
  <script type="text/javascript">
    App.resources.ManageCategories.init('manage_categories');
  </script>

  <div class="clear"></div>
</div>
