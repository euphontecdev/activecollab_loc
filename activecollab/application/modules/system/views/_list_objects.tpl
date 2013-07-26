{if is_foreachable($_list_objects_objects)}
<table class="common_table" id="{$_list_objects_id}">
{if $_list_objects_show_header}
  <thead>
    <tr>
	{*}BOF:mod 20120822{*}
	{if $_search_object!='Attachment'}
	{*}EOF:mod 20120822{*}
		{if $_list_objects_show_star}
		  <th class="star"></th>
		{/if}
		{if $_list_objects_show_priority}
		  <th class="priority"></th>
		{/if}
	{*}BOF:mod 20120822{*}
	{/if}
	{*}EOF:mod 20120822{*}
      <th class="name">{lang}Object{/lang}</th>
    {if $_list_objects_show_checkboxes}
      <th class="checkbox"><input type="checkbox" class="auto master_checkbox input_checkbox" /></th>
    {/if}
	{*}BOF:mod 20120711{*}
	<th><span id="datecol" style="cursor:pointer;text-decoration:underline;">{lang}Date{/lang}</span></th>
	{*}EOF:mod 20120711{*}
    </tr>
  </thead>
{/if}
  <tbody>
{*}BOF:mod 20120822{*}
{if $_search_object=='Attachment'}
	{foreach from=$_list_objects_objects item=_list_objects_object}
		<tr class="{cycle values='odd,even'}">
			{php}
			$obj = $this->get_template_vars('_list_objects_object');
			$cur_obj = new Attachment($obj->getId());
			
			$parent_obj = null;
			$parent_id = $cur_obj->getParentId();
			if (!empty($parent_id)){
				$parent_type = $cur_obj->getParentType();
				$parent_obj = new $parent_type($parent_id);
			}
			
			$dateval = $cur_obj->getCreatedOn();
			$temp = date('m-d-Y H:i:s', strtotime($dateval));
			$this->assign('date_val', $temp);
			
			$this->assign('cur_obj', $cur_obj);
			$this->assign('parent_obj', $parent_obj);
			{/php}
			<td valign="top">
				{link href=$cur_obj->getViewUrl() class="filename"}{$cur_obj->getName()|clean}{/link}, 
				<span class="filesize">{$cur_obj->getSize()|filesize}</span>
				<br/>
				{if $parent_obj}
				Parent link: {link href=$parent_obj->getViewUrl()}{$parent_obj->getName()|clean}{/link}
				{/if}
			</td>
			<td valign="top">{$date_val}</td>
		</tr>
	{/foreach}
{else}
{*}EOF:mod 20120822{*}
	{foreach from=$_list_objects_objects item=_list_objects_object}
		<tr class="{cycle values='odd,even'}">
		{if $_list_objects_show_star}
		  <td class="star">{object_star object=$_list_objects_object user=$logged_user}</td>
		{/if}
		{if $_list_objects_show_priority}
		  <td class="priority">{object_priority object=$_list_objects_object}</td>
		{/if}
		  <td class="name">
			{$_list_objects_object->getVerboseType()|clean}: {object_link object=$_list_objects_object del_completed=$_list_objects_del_completed}
			<span class="details block">{action_on_by user=$_list_objects_object->getCreatedBy() datetime=$_list_objects_object->getCreatedOn()}{if $_list_objects_show_project} {lang}in{/lang} {project_link project=$_list_objects_object->getProject()}{/if} {object_owner object=$_list_objects_object}{if $_list_objects_object->can_be_completed && $_list_objects_object->isOpen() && $_list_objects_object->getDueOn()} | {due object=$_list_objects_object}{/if}</span>
		  </td>
		{if $_list_objects_show_checkboxes}
		  <td class="checkbox"><input type="checkbox" name="objects[]" value="{$_list_objects_object->getId()}" class="auto slave_checkbox input_checkbox" /></td>
		{/if}
		{*}BOF:mod 20120711{*}
		{php}
		$obj = $this->get_template_vars('_list_objects_object');
		$dateval = $obj->getCreatedOn();
		$temp = date('m-d-Y H:i:s', strtotime($dateval));
		$this->assign('formatted_date', $temp);
		{/php}
		<td>{$formatted_date}</td>
		{*}EOF:mod 20120711{*}
		</tr>
	{/foreach}
{*}BOF:mod 20120822{*}
{/if}
{*}EOF:mod 20120822{*}
  </tbody>
</table>
{if $_list_objects_show_checkboxes}
<script type="text/javascript">
$(document).ready(function() {ldelim}
    $('#{$_list_objects_id}').checkboxes();
  {rdelim});
</script>
{/if}
{/if}
{*}BOF:mod 20120711{*}
<script type="text/javascript">
	$('span#datecol').click(function(){ldelim}
		if (location.href.indexOf('&datesort=')==-1)
			location.href = location.href + '&datesort=d';
		else if (location.href.indexOf('&datesort=a')!=-1)
			location.href = location.href.replace(/&datesort=a/i, '&datesort=d');
		else if (location.href.indexOf('&datesort=d')!=-1)
			location.href = location.href.replace(/&datesort=d/i, '&datesort=a');
	{rdelim});
</script>
{*}EOF:mod 20120711{*}