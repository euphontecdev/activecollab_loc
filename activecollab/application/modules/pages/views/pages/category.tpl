{title not_lang=yes}{$active_category->getName()}{/title}
{add_bread_crumb}Category Pages{/add_bread_crumb}

<div id="category_pages" class="list_view">
  <div class="object_list">
  {if is_foreachable($pages)}
    {pages_tree pages=$pages user=$logged_user}
  {else}
    <p class="empty_page">{lang}There are no pages in this category{/lang}. {if $add_page_url}<a href="{$add_page_url}">{lang}Create a new page now{/lang}</a>?{/if}</p>
    {empty_slate name=pages module=pages}
  {/if}
  </div>
{*}BOF:mod 20121108
  <ul class="category_list">
    <li {if $active_category->isNew()}class="selected"{/if}><a href="{$pages_url}"><span>{lang}Recently Updated{/lang}</span></a></li>
  {if is_foreachable($categories)}
    {foreach from=$categories item=category}
    <li category_id="{$category->getId()}" {if $active_category->isLoaded() && $active_category->getId() == $category->getId()}class="selected"{/if}><a href="{assemble route=project_pages project_id=$active_project->getId() category_id=$category->getId()}"><span>{$category->getName()|clean}</span></a></li>
    {/foreach}
  {/if}
    <li id="manage_categories"><a href="{$categories_url}"><span>{lang}Manage Categories{/lang}</span></a></li>
  </ul>
  <script type="text/javascript">
    App.resources.ManageCategories.init('manage_categories');
  </script>
  EOF:mod 20121108{*}
  {*}BOF:mod 20121108{*}
  <ul class="category_list">
    <li {if $selected_category_id==''}class="selected"{/if}><a href="{$pages_url}"><span>{lang}Recently Updated{/lang}</span></a></li>
  {if is_foreachable($departments)}
	    {foreach from=$departments item=department}
    		<li {if $selected_category_id==$department.id}class="selected"{/if}><a href="{assemble route=project_pages project_id=$active_project->getId() category_id=$department.id}"><span>{lang}{$department.text}{/lang}</span></a></li>
    	{/foreach}
  {/if}
  {if $can_manage_categories}
	<li id="manage_categories"><a href="{$categories_url}"><span>{lang}Manage Departments{/lang}</span></a></li>
  {/if}
  </ul>
  {*}EOF:mod 20121108{*}
  <div class="clear"></div>
</div>