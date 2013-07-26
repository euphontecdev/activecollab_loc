{if is_foreachable($repositories)}
  <ul class="list_with_icons">
  {foreach from=$repositories item=repository}
    <li class="obj_link discussions_obj_link">
      <a href="{mobile_access_get_view_url object=$repository}">
        <span class="main_line">
          {$repository->getName()|clean|excerpt:18}
        </span>
      </a>
    </li>
  {/foreach}
  </ul>
{else}
  <div class="wrapper">
    <div class="box">
      <ul class="menu">
        <li>{lang}No Repositories in this project{/lang}</li>
      </ul>
    </div>
  </div>
{/if}