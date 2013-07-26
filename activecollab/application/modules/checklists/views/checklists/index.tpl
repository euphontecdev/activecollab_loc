{*}
{title}Active Checklists{/title}
{add_bread_crumb}Active{/add_bread_crumb}

<div id="checklists">
  {if is_foreachable($checklists)}
    <div class="checklists_container">
    {foreach from=$checklists item=checklist}
      <div class="checklist even" id="checklist_{$checklist->getId()}" checklist_id="{$checklist->getId()}">
        <table class="checklists_table">
          <tr>
            <td class="star">{object_star object=$checklist user=$logged_user}</td>
            <td class="star expander"><a href="{$checklist->getViewUrl()}" class="collapsed">{image name=expand_collapsed.gif}</a></td>
            <td>{object_link object=$checklist}</td>
            <td class="stats"><span style="display: none;">{lang open_count=$checklist->countOpenTasks() total_count=$checklist->countTasks()}:open_count open tasks of :total_count tasks in list{/lang}</span></td>
            <td class="visibility">{object_visibility object=$checklist user=$logged_user}</td>
          </tr>
        </table>
        <div class="tasks_container"></div>
      </div>
    {/foreach}
    </div> 
  {else}
    <p class="empty_page">{lang}There are no active checklists here{/lang}. {if $add_checklist_url}{lang add_url=$add_checklist_url}Would you like to <a href=":add_url">create one</a>{/lang}?{/if}</p>
    {empty_slate name=checklists module=checklists}
  {/if}
  
  <p class="archive_link">{link href=$checklists_archive_url}Archive{/link}</p>
</div>
{*}
{title}Active Checklists{/title}
{add_bread_crumb}Active{/add_bread_crumb}
<div>
<div class="list_view">
	<div class="object_list">
    	<table>
  		{if is_foreachable($departments)}
    		{foreach from=$departments item=department}
    		{if $selected_category_id == '' && is_foreachable($department.items)}
      		<tr>
      			<td><h2 class="section_name"><span class="section_name_span">{$department.text}</span></h2></td>
      		</tr>
      		{/if}
      		{if $selected_category_id == '' || $selected_category_id==$department.id}
      			{if is_foreachable($department.items)}
    			{foreach from=$department.items item=checklist}
    			<tr>
					<td>
						<div class="checklist even" id="checklist_{$checklist->getId()}" checklist_id="{$checklist->getId()}">
							<table class="checklists_table">
								<tr>
									<td class="star">{object_star object=$checklist user=$logged_user}</td>
									<td class="star expander"><a href="{$checklist->getViewUrl()}" class="collapsed">{image name=expand_collapsed.gif}</a></td>
									<td>{object_link object=$checklist}</td>
									<td class="stats"><span style="display: none;">{lang open_count=$checklist->countOpenTasks() total_count=$checklist->countTasks()}:open_count open tasks of :total_count tasks in list{/lang}</span></td>
									<td class="visibility">{object_visibility object=$checklist user=$logged_user}</td>
								</tr>
							</table>
							<div class="tasks_container"></div>
						</div>
					</td>
				</tr>
      			{/foreach}
      			{else}
      				{if $selected_category_id!=''}
		    		<p class="empty_page">{lang}There are no active checklists here{/lang}. {if $add_checklist_url}{lang add_url=$add_checklist_url}Would you like to <a href=":add_url">create one</a>{/lang}?{/if}</p>
		    		{empty_slate name=checklists module=checklists}
		    		{/if}
      			{/if}
      		{/if}
    		{/foreach}
    	</table>
    	<div class="tasks_container"></div>
  		{else}
    		<p class="empty_page">{lang}There are no active checklists here{/lang}. {if $add_checklist_url}{lang add_url=$add_checklist_url}Would you like to <a href=":add_url">create one</a>{/lang}?{/if}</p>
    		{empty_slate name=checklists module=checklists}
  		{/if}
  		<p class="archive_link">{link href=$checklists_archive_url}Archive{/link}</p>
	</div>

  	<ul class="category_list">
  		<li {if $selected_category_id==''}class="selected"{/if}><a href="{$checklists_url}"><span>{lang}View All{/lang}</span></a></li>
    {foreach from=$departments item=department}
    	<li {if $selected_category_id==$department.id}class="selected"{/if}><a href="{$checklists_url}&category_id={$department.id}"><span>{lang}{$department.text}{/lang}</span></a></li>
    {/foreach}
    	<li id="manage_categories"><a href="{$categories_url}"><span>{lang}Edit Departments{/lang}</span></a></li>
  	</ul>
</div>

</div>