{*27/3/2012 Ticket #298: Create a page that shows Action Requests that you have Assigned (SA)*}
{if $is_ajax_call}
({$action_request_links} / {$fyi_links})
{else}
    {title not_lang=yes}{lang name=$current_user->getDisplayName()}Assigned Action Requests by :name{/lang}{/title}
    {add_bread_crumb}{lang}Assigned Action Requests{/lang}{/add_bread_crumb}
     <div>
     {$assigned_ar_content}
     </div>
        
{/if}