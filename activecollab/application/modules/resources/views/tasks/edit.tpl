{title}Edit task{/title}
{add_bread_crumb}Edit task{/add_bread_crumb}

<p>&laquo; {lang view_url=$active_task_parent->getViewUrl() name=$active_task_parent->getName()}Back to <a href=":view_url">:name</a>{/lang}.</p>

{form id="form_edit_task" action=$active_task->getEditUrl() method=post}
  {include_template name=_task_form module=resources controller=tasks}
  {wrap_buttons}
    {submit}Submit{/submit}
  {/wrap_buttons}
{/form}
<script type="text/javascript">
  //App.layout.init_edit_task();
			var form = $('#form_edit_task');
			form.find('#ancEditType').click(function(){ldelim}
		       	if ($(this).html()=='Quick edit'){ldelim}
		       		form.find('#span_task_summary_plain').css('display', 'block');
		       		form.find('#span_task_summary_plain #taskSummary').attr('name', 'task[body]');
		       		form.find('#span_task_summary_editor').css('display', 'none');
		       		form.find('#span_task_summary_editor #taskSummary').attr('name', 'task[body_editor]');
		       		$(this).html('Text editor');
		       		$('#span_task_summary_plain #taskSummary').focus();
		       	{rdelim} else {ldelim}
		       		form.find('#span_task_summary_plain').css('display', 'none');
		       		form.find('#span_task_summary_plain #taskSummary').attr('name', 'task[body_plain]');
		       		form.find('#span_task_summary_editor').css('display', 'block');
		       		form.find('#span_task_summary_editor #taskSummary').attr('name', 'task[body]');
		       		$(this).html('Quick edit');
		       	{rdelim}
			{rdelim});
</script>