{if $selected_user}
  {title not_lang=yes}{lang display_name=$selected_user->getDisplayName()}:display_name's Status Updates{/lang}{/title}
  {add_bread_crumb}{$selected_user->getDisplayName()}{/add_bread_crumb}
{else}
  {title}Status Archive{/title}
  {add_bread_crumb}Archive{/add_bread_crumb}
{/if}

<div id="status_updates_archive">
  <div class="filter_style dont_print">
    {lang}User{/lang}: 
    <select name="user" id="select_user">
      <option value="{assemble route=status_updates}">--{lang}All users{/lang}--</option>
      {if is_foreachable($visible_users)}
        {foreach from=$visible_users key=company_name item=company_users}
          {if is_foreachable($company_users)}
          <optgroup label="{$company_name}">
            {foreach from=$company_users item=user}
              <option value="{assemble route=status_updates user_id=$user.id}"{if ($selected_user) && ($selected_user->getId()==$user.id)} selected="selected"{/if}>{$user.display_name}</option>
            {/foreach}
          </optgroup>
          {/if}
        {/foreach}
      {/if}
    </select>
    
    <img src="{image_url name=indicator.gif}" alt="Working" id="status_update_archive_work_indicator" style="display: none"/>
  </div>
  
    {if is_foreachable($status_updates)}
      {if $pagination->getLastPage() > 1}
        <div class="pagination_container top">
          {if $selected_user}
            {pagination pager=$pagination}{assemble route=status_updates user_id=$selected_user->getId() page='-PAGE-'}{/pagination}
          {else}
            {pagination pager=$pagination}{assemble route=status_updates page='-PAGE-'}{/pagination}
          {/if}
        </div>
      {/if}
      
      <table class="status_updates">
      {foreach from=$status_updates item=status_update}
        {include_template name=_status_row controller=status module=status}
      {/foreach}
      </table>
      
      {if $pagination->getLastPage() > 1 && !$pagination->isLast()}
        {if $selected_user}
          <p class="next_page"><a href="{assemble route=status_updates user_id=$selected_user->getId() page=$pagination->getNextPage()}">{lang}Next Page{/lang}</a></p>
        {else}
          <p class="next_page"><a href="{assemble route=status_updates page=$pagination->getNextPage()}">{lang}Next Page{/lang}</a></p>
        {/if}
      {/if}
    {else}
      {if $selected_user}
        <p class="empty_page">{lang}User has no status updates{/lang}</p>
      {else}
        <p class="empty_page">{lang}No status messages logged{/lang}</p>
      {/if}
    {/if}
    
    <p class="recent_activities_rss">{link href=$rss_url}Track using RSS{/link}</p>
</div>