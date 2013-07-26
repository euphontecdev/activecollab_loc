<div>
    {form name="tabsmanager" method=post id="ajaxedit_form" action=$action_url onsubmit="return customtab_form_validate();"}
        <input type="hidden" name="tab_info[index]" value="{$tab_index}" />
        <div style="font-weight:bold;margin-bottom:10px;font-size:11px;">
            {$logged_user->getFirstName()}, you are about to update name/link for tab# {$tab_index}
        </div>
        <div style="margin-bottom:5px;">
            <div style="width:100px;float:left;">Tab Name*</div>
            <div style="width:20px;float:left;">&nbsp;:&nbsp;</div>
            <div><input name="tab_info[name]" id="tab_name" maxlength="50" value="{$tab_description}" /></div>
        </div>
        <div style="margin-bottom:5px;">
            <div style="width:100px;float:left;">URL*</div>
            <div style="width:20px;float:left;">&nbsp;:&nbsp;</div>
            <div><input name="tab_info[url]" id="tab_url" maxlength="200" value="{$tab_link}" /></div>
        </div>
        <div align="center" style="margin-bottom:5px;">
            {submit}Submit{/submit}&nbsp;
            {if $tab_description}
                {button onclick="customtab_remove();"}Remove Link{/button}&nbsp;
            {/if}
            {button type="reset"}Reset{/button}
        </div>
    {/form}
</div>