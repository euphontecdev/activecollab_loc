{title  name=$active_ticket->getName()}Ticket: :name - Print Tasks{/title}
{add_bread_crumb}Print Comment{/add_bread_crumb}


<div class="body">
<table width="100%">
    <tr><td width="15%" style="font-weight:bold;text-decoration: underline;">Priority</td><td width="65%" style="font-weight:bold;text-decoration: underline;">Task name</td><td width="20%" style="font-weight:bold;text-decoration: underline;">Due Date</td></tr>
    {if is_foreachable($_open_tasks)}
      {foreach from=$_open_tasks item=_object_task}
          <tr><td width="15%">{$_object_task->getPriorityName()}</td><td width="65%">{$_object_task->getBody()}</td><td width="20%">{$_object_task->getDueOn()}</td></tr>
      {/foreach}
    {/if}
</table>
</div>
<script type="text/javascript">window.print();</script>