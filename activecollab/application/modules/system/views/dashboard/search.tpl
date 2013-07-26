{*//12 April 2012 (SA) Ticket #769: modify ac search results to list active tickets first*}
{title}Search{/title}
{add_bread_crumb}Search{/add_bread_crumb}
<div id="search">
  {form action="?route=search" method="get" show_errors=no id=search_form}
  
  <table class="search_form stripped_background">
  	
    <tr>
      <td class="search_form_caption" valign="top">{lang}Search{/lang}:
		<br/>
		<span style="cursor:pointer;text-decoration:underline;color:rgb(0,0,255);" onmouseover="showtitle('search_text');" onmouseout="hidetitle('search_text');">?</span>
		<div id="search_text"  style="display:none;border:1px solid black;background-color:#FBF9EA;width:400px;position:absolute;margin:10px;padding:5px;font-weight:normal;">
			<br/>
			The Search script first searches for entries that contain full search string (in same order as they appear),<br/>
			then it searches for entries that contain all parts of search string,<br/>
			and finally, it searches for entries that contain some parts of search string,<br/>
			and then it displays the results by date.<br/><br/>
			Users can also search for a specific "Type" of object in AC.
			<br/>
		</div>
	  </td>
      <td class="search_for" valign="top">
      {wrap field=search_for}
        {text_field name=q id=search_for_input value=$search_for class=required}
      {/wrap}      
      </td>
      <td class="search_type" valign="top">
      {wrap field=search_type}
        <select name="type" id="search_for_type" onchange="var obj = document.getElementById('addnl_dtls');if (this.options.selectedIndex==0) obj.style.display=''; else obj.style.display='none';">
          <option value="in_projects" {if $search_type == 'in_projects'}selected="selected"{/if}>{lang}In projects{/lang}</option>
          <option value="for_people" {if $search_type == 'for_people'}selected="selected"{/if}>{lang}For users{/lang}</option>
          <option value="for_projects" {if $search_type == 'for_projects'}selected="selected"{/if}>{lang}For projects{/lang}</option>
        </select>
      {/wrap}
      
        
      <td class="search_form_button" valign="top">{submit class='grey_button'}Go{/submit}</td>
    </tr>
    
    <tr>
    	<td colspan="3" align="right" class="search_object">
    	<div id="addnl_dtls">
  {if is_foreachable($search_projects)}  
  <select name="search_project_id" id="search_project_id">
  	<option value="0" {if !$search_project_id} selected="true" {/if}>Search All Teams</option>
  {foreach from=$search_projects item=project}
  	<option value="{$project->getId()}" {if $search_project_id==$project->getId()} selected="true" {/if}>{$project->getName()}</option>
  {/foreach}
  </select>
  <br><br>
  {/if}
  
          {wrap field=search_object}
		  <select name="search_object" id="search_object">
		  {foreach from=$object_types item=type}
		  	<option value="{$type.id}" {if $search_object==$type.id}selected="selected"{/if}>{$type.text}</option>
		  {/foreach}
		  </select>
		  {/wrap}
		</div>
    	</td>
    	<td>&nbsp;</td>
    </tr>
  </table>
  <table>
  <tr>
  	<td align="left" width="200">
      <input style="width:20px;" type="checkbox" id="complete" name="complete" value="1" {if $complete == '1'}checked{/if}/>Display Inactive Items ({$completedCount})
     </td>  		 
  	</tr>
  	</table>
  {/form}
  
{if $search_for && $search_type}
  <div class="clear"></div>
  {if is_foreachable($search_results)}
  <div id="search_results">
    <p class="pagination top">
      <span class="inner_pagination">
	  {*}BOF:mod 20120711
      {lang}Page{/lang}: {pagination pager=$pagination}{assemble route=search q=$search_for type=$search_type page='-PAGE-' search_object=$search_object complete=$complete}{/pagination}
	  EOF:mod 20120711{*}
	  {*}BOF:mod 20120711{*}
	  {lang}Page{/lang}: {pagination pager=$pagination}{assemble route=search q=$search_for type=$search_type page='-PAGE-' search_object=$search_object complete=$complete datesort=$datesort}{/pagination}
	  {*}EOF:mod 20120711{*}
      </span>
    </p>
    
    <div class="clear"></div>
    {if $search_type == 'in_projects'}
	{*}BOF:mod 20120711
      {list_objects objects=$search_results show_checkboxes=no show_header=no id=search_results}
	EOF:mod 20120711{*}
	{*}BOF:mod 20120711{*}
	{*}BOF:mod 20120822 {list_objects objects=$search_results show_checkboxes=no show_header=yes id=search_results} EOF:mod 20120822{*}
	  {*}BOF:mod 20120822{*}
	  {list_objects objects=$search_results show_checkboxes=no show_header=yes id=search_results search_object=$search_object}
	  {*}EOF:mod 20120822{*}
	{*}EOF:mod 20120711{*}
    {elseif $search_type == 'for_people'}
    <table id="people_list">
    {foreach from=$search_results item=user}
      <tr class="{cycle values='odd,even'}">
        <td class="avatar"><img src="{$user->getAvatarUrl()}" alt="" /></td>
        <td class="name">{user_link user=$user}</td>
        <td class="email"><a href="mailto:{$user->getEmail()|clean}">{$user->getEmail()|clean}</a></td>
      </tr>
    {/foreach}
    </table>
    {elseif $search_type == 'for_projects'}
    <table id="projects_list">
    {foreach from=$search_results item=project}
      <tr class="{cycle values='odd,even'}">
        <td class="icon"><img src="{$project->getIconUrl()}" alt="" /></td>
        <td class="name"><a href="{$project->getOverviewUrl()}">{$project->getName()|clean}</a></td>
        <td class="progress">{project_progress project=$project info=no}</td>
      </tr>
    {/foreach}
    </table>
    {/if}
  </div>
  {else}
    <p class="empty_page">{lang for=$search_for}Search failed to find any object that match your request{/lang}</p>
  {/if}
{/if}
  
</div>