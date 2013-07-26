{if $is_ajax_call}
({$action_request_links} / {$fyi_links})
{else}
    {title not_lang=yes}{lang name=$current_user->getDisplayName()}Notifications for :name{/lang}{/title}
    {add_bread_crumb}{lang}Notifications{/lang}{/add_bread_crumb}
     <div>
    <div style="float:right;">
        {*BOF:mod #59_303*}
        <form action="{assemble route=goto_home_tab}" method="post">
        <input type="hidden" name="user_id" id="user_id" value="{$current_user->getId()}" />
        <select name="layout_type" id="layout_type" onchange="this.form.submit();">
            <option value="summary" {if ($layout_type=='summary')} selected {/if}>View Summary</option>
            <option value="details" {if ($layout_type=='details')} selected {/if}>View Details</option>
        </select>
        </form>
        {*EOF:mod #59_303*}
    </div>
     {$home_tab_content}
     </div>
         <script type="text/javascript">
             hometab_links_summary();
         </script>
{/if}