  {if is_foreachable($commits)}
    <div class="wrapper">  
      {foreach from=$commits item=commits_day key=date}
        <h2 class="label">{$date}</h2>
        <div class="box">
          <ul class="menu list">
          {foreach from=$commits_day item=commit}
            <li>
              <span class="main_link"><span>#{$commit->getId()}</span> - {lang files_commited=$commit->countPaths()}:files_commited files updated{/lang}</span>
              {if $commit->getMessage()}
                <span class="details">
                  {$commit->getMessage()|nl2br}
                </span>
              {/if}
            </li>
          {/foreach}
          </ul>
        </div>  
      {/foreach}
    </div>
    {mobile_access_paginator paginator=$pagination url=$pagination_url url_param_category_id=$selected_category_id}
  {else}
    <div class="wrapper">  
      <h2 class="label">{lang}No Recent Activities{/lang}</h2>  
    </div>
  {/if}

