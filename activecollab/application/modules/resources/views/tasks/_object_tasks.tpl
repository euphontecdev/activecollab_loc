{*27 March 2012 Ticket #770: modify Print function for Pages in AC (SA)*}
{*11 April 2012 Ticket #784: check Recurring Reminder email script in AC*}
{if !$_object_tasks_skip_wrapper}
<div class="resource object_tasks object_section" id="object_tasks_for_{$_object_tasks_object->getId()}" {if !$_object_tasks_force_show && !(is_foreachable($_object_tasks_open) || is_foreachable($_object_tasks_completed))}style="display: none"{/if}>
{/if}

  {if $_object_tasks_skip_head==false}
    <div class="head">
      {assign_var name=section_title}
	  {lang}Tasks{/lang}
	  {if $_object_tasks_object->getType()!='Checklist'}
		&nbsp;
		<span style="cursor:pointer;text-decoration:underline;color:rgb(0,0,255);" onmouseover="showtitle('tasks_sorting');" onmouseout="hidetitle('tasks_sorting');">?</span>
		<div id="tasks_sorting"  style="display:none;border:1px solid black;background-color:#FBF9EA;width:600px;position:absolute;margin:10px;padding:15px;z-index:100;font-weight:normal;">
		Tasks are sorted into this order by default:
		<ol>
			<li>Tasks marked Urgent Priority.</li>
			<li>Tasks that are past due or that have a reminder that is past due (regardless of priority setting).</li>
			<li>Tasks marked Highest Priority.</li>
			<li>Tasks that are due today or that have a reminder that is due today and that have a priority setting of High, Normal, Low, Lowest or Hold.</li>
			<li>Tasks marked High Priority.</li>
			<li>Tasks that are due in the next 7 days or that have a reminder that is due in the next 7 days and that have a priority setting of Normal, Low, Lowest or Hold.</li>
			<li>Tasks marked Normal Priority.</li>
			<li>Tasks that are due in the next 8-30 days or that have a reminder that is due in the next 8-30 days and that have a priority setting of Low, Lowest or Hold.</li>
			<li>Tasks marked Low Priority.</li>
			<li>Tasks that are due in the next 31-90 days or that have a reminder that is due in the next 31-90 days and that have a priority setting of Lowest or Hold.</li>
			<li>Tasks marked Lowest Priority.</li>
			<li>Tasks marked Hold.</li>
		</ol>
		</div>
		{/if}
	  {/assign_var}
      {if $_object_tasks_object->canSubtask($logged_user)}
        <h2 class="section_name"><span class="section_name_span">
          <span class="section_name_span_span">{$section_title}</span>
          <div style="display:inline;float:right;vertical-align:middle;margin-top:-8px;"><a href="{$_object_tasks_object->getPostTaskUrl()}" class="add_task_link button_add dont_print top_link"><span>{lang}Add a Task{/lang}</span></a></div>
		  <div style="display:inline;float:right;vertical-align:middle;">&nbsp;<a href="javascript://" id="expand_all_tasks"><span>{lang}More{/lang}</span></a>&nbsp;</div>
          <div style="display:inline;float:right;vertical-align:middle;padding-left:10px;">{link href=$_object_tasks_object->getPrintTasksUrl() title='Print Tasks'}<img src="{image_url name=icons/print.gif}" alt="" />{/link}</div>
          <div class="clear"></div>
        </span>
		</h2>
      {else}
        <h2 class="section_name"><span class="section_name_span">
          <span class="section_name_span_span">{$section_title}</span>
        </span>
		</h2>
      {/if}
    </div>
  {/if}
  <div class="body">
  {form method='POST' action=$_object_tasks_object->getReorderTasksUrl(true) class='sort_form visible_overflow'}
  <ul class="tasks_table common_table_list highlight_priority open_tasks_table">
    {if is_foreachable($_object_tasks_open)}
      {foreach from=$_object_tasks_open item=_object_task}
        {include_template module=resources controller=tasks name=_task_opened_row}
      {/foreach}
    {/if}
    <li class="empty_row" style="{if is_foreachable($_object_tasks_open)}display: none{/if}">{lang object_type=$_object_tasks_object->getVerboseType()}There are no active Tasks in this :object_type{/lang}</li>
  </ul>
  {/form}
  {if $_object_tasks_object->canSubtask($logged_user)}
    <div class="hidden_overflow">
      <div class="add_task_form" style="display:none;">
        {form action=$_object_tasks_object->getPostTaskUrl() method=post}
          <div class="columns">
            <div class="form_left_col" style="position:relative;width:100%;">
              {wrap field=body}
                {label for=taskSummary required=yes}Summary{/label}
                {*}{text_field name='task[body]' class='long required' id=taskSummary}{*}
                {*}<textarea name="task[body]" class='long required' id="taskSummary" style="width:50%;height:100px;" hide_editor="true" visual="false"></textarea>{*}
                {*}<textarea mce_editable="false" name="task[body]" class='long required' id="taskSummary"></textarea>{*}
                <span id="span_task_summary_editor" style="display:block;">
				{*}{editor_field name='task[body]' class='long required' id="taskSummary" mce_editable="false" visual="false"}{/editor_field}{*}
				{editor_field name='task[body]' class='long required' id="taskSummary" mce_editable="false" visual="false"}{/editor_field}
				</span>
                <span id="span_task_summary_plain" style="display:none;">
				{text_field name='task[body_plain]' class='long required taskSummaryText' id='taskSummary'}
				</span>
              {/wrap}
              {*}<input type="hidden" id="mode" value="editor" />{*}
              
              {*}<p class="show_due_date_and_priority" style="display:inline;"><a class="additional_form_links" href="#">{lang}Set priority and due date...{/lang}</a>&nbsp;&nbsp;&nbsp;</p><a class="additional_form_links" href="javascript://" id="ancEditType">Quick edit</a>{*}
              <a class="additional_form_links" href="javascript://" id="ancEditType">Quick edit</a>
              
              <div class="due_date_and_priority_">
                <div class="col_wide" style="float:left;width:15%;">
                {wrap field=priority}
                  {label for=taskPriority}Priority{/label}
                  {select_priority name='task[priority]' id=taskPriority style="width:100px;"}
                {/wrap}
                </div>
                
                <div class="col_wide2" style="float:left;width:18%;">
                {wrap field=due_on}
                  {label for=taskDueOn}Due on{/label}
                  {select_date name='task[due_on]' id=taskDueOn}
                {/wrap}
                </div>
				{*}
                <div class="col_wide2" style="float:left;width:15%;">
                {wrap field=reminder}
                  {label for=taskReminder}Reminder{/label}
                  {select_date name='task[reminder]' id=taskReminder onchange="setHour()"}
                {/wrap}
                </div>
                {*}
                <div class="col_wide2" style="float:left;width:15%;">
                  {wrap field=email}
                    {label for=taskemail}Email Reminder{/label}
                    <input type="checkbox" name="task[email_flag]" value="1" style="width:20px;" onclick="email_reminder_onclick(this);" /> Yes
                  {/wrap}
                </div>
                <div class="col_wide2" style="float:left;width:20%;">
				  {wrap field=daysbeforedue}
					{label for=taskemail}Days before Due Date{/label}
					<input type="text" name="task[figure_before_due_date]" value="0" style="width:20px;" disabled />
					<select name="task[unit_before_due_date]" style="width:90px;" disabled>
						<option value=""></option>
						<option value="D" selected>Day(s)</option>
						<option value="W">Week(s)</option>
						<option value="M">Month(s)</option>
					</select>
				  {/wrap}
                </div>
                <div class="col_wide2" style="float:left;width:22%;">
                  {wrap field=time}
                    {label for=taskremindertime}Time{/label}
                    <select name="task[reminderhours]" style="width:50px;" disabled>
                        <option value=""></option>
                        {section name=hour start=1 loop=13}
                            <option value="{$smarty.section.hour.index}"  {if $smarty.section.hour.index==6}selected{/if}>{if $smarty.section.hour.index<10}0{/if}{$smarty.section.hour.index}</option>
                        {/section}
                    </select>:<input type="hidden" name="task[reminderminutes]" value="0"/>00 <!-- select name="task[reminderminutes]" style="width:50px;">
                        <option value=""></option>
                        {section name=minute start=0 loop=60}
                            <option value="{$smarty.section.minute.index}">{if $smarty.section.minute.index<10}0{/if}{$smarty.section.minute.index}</option>
                        {/section}
                    </select//--><select name="task[remindermeridian]" style="width:50px;" disabled>
                        <option value="AM" {if 'AM'==$task_data.remindermeridian}selected{/if}>AM</option>
                        <option value="PM" {if 'PM'==$task_data.remindermeridian}selected{/if}>PM</option>
                    </select>
                  {/wrap}
                </div>
              </div>
  <div>
    <div style="display:inline;float:left;">
    {label for=taskDueOn}Recurring{/label}
    <input type="radio" name="task[recurring_flag]" value="1" style="width:15px;" onclick="on_recurring_flag_selected(this);" />Yes
    <input type="radio" name="task[recurring_flag]" value="0" style="width:15px;" checked onclick="on_recurring_flag_selected(this);" />No
    <div id="recurring_params" style="visibility:hidden;">
    {label for=taskDueOn}Frequency{/label}
    <input type="text" name="task[recurring_period]" maxlength="3" value="7" style="width:25px;" />    
    <input type="radio" name="task[recurring_period_type]" value="D" style="width:15px;" checked />Day(s)
    <input type="radio" name="task[recurring_period_type]" value="W" style="width:15px;" />Week(s)
    <input type="radio" name="task[recurring_period_type]" value="M" style="width:15px;" />Month(s)
    <br/>
    <input type="radio" name="task[recurring_period_condition]" value="after_due_date" style="width:15px;" checked  />After Due Date or
    <input type="radio" name="task[recurring_period_condition]" value="after_task_complete" style="width:15px;" />After Task is Completed
    {label for=taskRecurringEndDate}End Date{/label}
    {select_date name='task[recurring_end_date]' id=taskRecurringEndDate}
    </div>
    </div>
            <div class="form_right_col_" style="display:inline;float:right">
              {wrap field=assignees}
                {label for=taskAssignees}Assignees{/label}
                {select_assignees name='task[assignees]' object=$_object_tasks_object project=$active_project}
              {/wrap}
            </div>
  </div>
              <div class="clear"></div>
            </div>
            

          </div>
          {wrap_buttons}
            {submit}Submit{/submit}
            <a href="#" class="text_button cancel_button">{lang}Done adding tasks?{/lang}</a>
          {/wrap_buttons}
        {/form}
        {*}<span id="prev_content" style="display:none;"></span>{*}
      </div>
      <a href="{$_object_tasks_object->getPostTaskUrl()}" class="add_task_link button_add dont_print"><span>{lang}Add Another Task{/lang}</span></a>
      {if $_object_tasks_object->getType()=='Checklist'}
		<a style="margin-left:25px;margin-top:5px;" href="{assemble route='project_checklist_open_tasks' project_id=$active_project->getId() checklist_id=$_object_tasks_object->getId()}" class="button_add dont_print"><span>{lang}Set All Tasks to Active{/lang}</span></a>
      {/if}
    </div>
  {/if}

  <ul class="tasks_table common_table_list completed_tasks_table">
  {if is_foreachable($_object_tasks_completed)}
    {foreach from=$_object_tasks_completed item=_object_task}
      {include_template module=resources controller=tasks name=_task_completed_row}
    {/foreach}
    {if $_object_tasks_completed_remaining > 0}
      <li class="list_all_completed"><a href="{assemble route='project_tasks_list_completed' project_id=$active_project->getId() parent_id=$_object_tasks_object->getId()}">{lang remaining_count=$_object_tasks_completed_remaining}Show :remaining_count remaining completed tasks{/lang}</a></li>
    {/if}
  {/if}
  </ul>
  </div>
  
{if !$_object_tasks_skip_wrapper}
</div>
<script type="text/javascript">
  App.layout.init_object_tasks('object_tasks_for_{$_object_tasks_object->getId()}', '{$_object_tasks_can_reorder}');
</script>
{/if}
<script type="text/javascript">
function email_reminder_onclick(chk_ref){ldelim}
	if ($(chk_ref).attr('checked')){ldelim}
		$('input[name="task[figure_before_due_date]"]').val('0').removeAttr('disabled');
		$('select[name="task[unit_before_due_date]"]').val('D').removeAttr('disabled');
		$('select[name="task[reminderhours]"]').val('6').removeAttr('disabled');
		$('select[name="task[remindermeridian]"]').val('AM').removeAttr('disabled');
	{rdelim} else {ldelim}
		$('input[name="task[figure_before_due_date]"]').val('0').attr('disabled', 'disabled');
		$('select[name="task[unit_before_due_date]"]').val('D').attr('disabled', 'disabled');
		$('select[name="task[reminderhours]"]').val('6').attr('disabled', 'disabled');
		$('select[name="task[remindermeridian]"]').val('AM').attr('disabled', 'disabled');
	{rdelim}
{rdelim}
</script>