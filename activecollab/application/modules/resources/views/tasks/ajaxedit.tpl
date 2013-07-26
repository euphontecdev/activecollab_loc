{assign var=task_id value=$active_task->getId()}
{assign var=editor_id value="taskSummaryPop_$task_id"}
{form action=$active_task->getAjaxEditUrl() method=post id="ajaxedit_form"}
<div class="form_full_view">
  {wrap field=body}
    {label for=$editor_id required=yes}Summary{/label}
	{*}<textarea name="task_summary" class='title required' id="taskSummary" style="width:95%;height:100px;">{$task_summary}</textarea>{*}
	{*}{editor_field name='task_summary' class='title required' id=$editor_id mce_editable="true" visual="true"}{$task_summary}{/editor_field}{*}
                <span id="span_task_summary_editor" style="display:block;">
				{editor_field name='task_summary' class='title required taskSummary' id=$editor_id mce_editable="true" visual="true"}{$task_summary}{/editor_field}
				</span>
                <span id="span_task_summary_plain" style="display:none;">
				{text_field name='task_summary_plain' class='title required taskSummary' id="body_plain" value=$task_summary}
				</span>
  {/wrap}
  <div class="clear"></div>
</div>
<a class="additional_form_links" href="javascript://" id="ancEditType">Quick edit</a>
  <input type="hidden" id="cur_task_id" value="{$active_task->getId()}" />
  <input type="hidden" id="mode" value="editor" />
    {submit id="edit_task_submit_button"}Submit{/submit}<img src="{image_url name=indicator.gif}" alt="Working" id="edit_task_indicator" style="display: none" />
	&nbsp;&nbsp;&nbsp;<span id="edit_task_response"></span>
{/form}

<script type="text/javascript">
  App.EditTask.init();
	if (tinyMCE.get("{$editor_id}")) {ldelim}
		try {ldelim}
			tinyMCE.remove(tinyMCE.get("{$editor_id}"));
		{rdelim} catch(e) {ldelim}
			alert(e);
		{rdelim}
	{rdelim}
	tinyMCE.execCommand("mceAddControl", true, "{$editor_id}");
</script>