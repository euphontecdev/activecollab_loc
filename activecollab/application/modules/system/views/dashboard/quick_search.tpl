{form action="?route=quick_search" method=post id="quick_search_form"}
  <input type="text" name="search_for" id="quick_search_input" value="{$search_string}" /> <input type="image" src="{image_url name=search_small.gif}" id="quick_search_button" class="auto" /> <img src="{image_url name=indicator.gif}" alt="Working" id="quick_search_indicator" style="display: none" />
  <input type="hidden" name="search_type" value="in_projects" id="quick_search_type" />
  <ul>
    <li id="search_in_projects" class="selected" onclick="">{lang}In Projects{/lang}</li>
    <li id="search_for_people">{lang}For Users{/lang}</li>
    <li id="search_for_projects">{lang}For Projects{/lang}</li>
  </ul>
  {if is_foreachable($search_projects)}
  <select name="search_project_id" id="search_project_id">
  <option value="0">Search All Teams</option>
  {foreach from=$search_projects item=project}
  	<option value="{$project->getId()}">{$project->getName()}</option>
  {/foreach}
  </select>
  <br><br>
  {/if}
  <select name="search_object_type" id="search_object_type">
  {foreach from=$object_types item=type}
  	<option value="{$type.id}">{$type.text}</option>
  {/foreach}
  </select>
  <div id="quick_search_results"></div>
{/form}
<script type="text/javascript">
  App.QuickSearch.init();
</script>