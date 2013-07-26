{title}Completed Milestones{/title}
{add_bread_crumb}Archive: {$selected_category_name}{/add_bread_crumb}

<div class="list_view" id="milestones">
  <div class="object_list">
  {if is_foreachable($milestones)}
  {form action=$mass_edit_milestones_url method=post}
    <table>
      <tbody>
      	{*BOF: task 03 | AD*}
      	<tr>
      		<td class="heading">Star</td>
      		<td class="heading"><span style="cursor:pointer;" onclick="sort_page('priority');">Priority</span></td>
      		<td class="heading"><span style="cursor:pointer;" onclick="sort_page('name');">Name</span></td>
      		<td class="heading"><span style="cursor:pointer;" onclick="sort_page('due_on');">Due Date</span></td>
      	</tr>
      	{*EOF: task 03 | AD*}
      	{*BOF: task 04 | AD*}
      	{foreach from=$milestones item=entry}
      		{if $selected_category_id=='' || $selected_category_id==$entry.category_id}
      			{if $selected_category_id==''}
		      	<tr>
		      		<td colspan="5"><h2 class="section_name"><span class="section_name_span">{$entry.category_name}</span></h2></td>
		      	</tr>
		      	{/if}
		      		{foreach from=$entry.milestones_ item=milestone}
		      	{*}
		      {foreach from=$milestones item=milestone}
		      {*}
		      {*EOF: task 04 | AD*}
		        <tr class="complted {cycle values='odd,even'}">
		          <td class="star">{object_star object=$milestone user=$logged_user}</td>
		          <td class="priority">{object_priority object=$milestone}</td>
		          <td class="name">
		            <a href="{$milestone->getViewUrl()}">{$milestone->getName()|clean}</a>
		            <span class="details block">{action_on_by user=$milestone->getCompletedBy() datetime=$milestone->getCompletedOn() action=Completed}</span>
		          </td>
		          <td class="date">
		          {if $milestone->isDayMilestone()}
		            {$milestone->getDueOn()|date:0}
		          {else}
		            {$milestone->getStartOn()|date:0} &mdash; {$milestone->getDueOn()|date:0}
		          {/if}
		          </td>
		          <td>
		          	<span class="option"><input type="checkbox" name="milestones[]" value="{$milestone->getId()}" class="auto input_checkbox" /></span>
				  </td>
		        </tr>
		      {/foreach}
		      	{*BOF: task 04 | AD*}
			{/if}
      	{/foreach}
      	{*EOF: task 04 | AD*}
      </tbody>
    </table>

      <div id="mass_edit">
        <select name="with_selected" id="milestones_action" class="auto">
          <option value="">{lang}With Selected ...{/lang}</option>
          <option value=""></option>
          <option value="open">{lang}Mark as Open{/lang}</option>
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
		    {*}BOF:mod 20121108{*}
			<option value="set_priority_3">{lang}Urgent{/lang}</option>
			{*}EOF:mod 20121108{*}
            <option value="set_priority_2">{lang}Highest{/lang}</option>
            <option value="set_priority_1">{lang}High{/lang}</option>
            <option value="set_priority_0">{lang}Normal{/lang}</option>
            <option value="set_priority_-1">{lang}Low{/lang}</option>
            <option value="set_priority_-2">{lang}Lowest{/lang}</option>
          </optgroup>
      
          <option value=""></option>
          {*}BOF:mod 20121108 <optgroup label="{lang}Move to category{/lang}"> EOF:mod 20121108{*}
		  {*}BOF:mod 20121108{*}
		  <optgroup label="{lang}Move to department{/lang}">
		  {*}EOF:mod 20121108{*}
            <option value="move_to_category">{lang}&lt;None&gt;{/lang}</option>
			{*}BOF:mod 20121108
			{foreach from=$categories_all item=category}
				<option value="move_to_category_{$category.category_id}">{$category.category_name|clean}</option>
			{/foreach}
			EOF:mod 20121108{*}
			{*}BOF:mod 20121108{*}
			{foreach from=$categories_all item=category}
				<option value="move_to_category_{$category.id}">{$category.name|clean}</option>
			{/foreach}
			{*}EOF:mod 20121108{*}
          </optgroup>
        </select>
        {button id="milestones_submit" type="submit"}Go{/button}
      </div>
    {/form}
  {else}
    <p>{lang add_url=$add_milestone_url}There are no completed milestones in this project. <a href=":add_url">Create a milestone</a>.{/lang}</p>
  {/if}
  </div>
  
  <ul class="category_list">
    <li {if $request->getAction() != 'archive'}class="selected"{/if}><a href="{$milestones_url}"><span>{lang}Active{/lang}</span></a></li>
    <li {if $request->getAction() == 'archive' && $selected_category_id==''}class="selected"{/if}><a href="{assemble route=project_milestones_archive project_id=$active_project->getId()}"><span>{lang}Completed{/lang}</span></a></li>
    {foreach from=$milestones item=entry}
    <li {if $selected_category_id==$entry.category_id}class="selected"{/if}><a href="{$entry.category_url}"><span>{$entry.category_name}</a></li>
    {/foreach}
  </ul>
  
  <div class="clear"></div>
</div>