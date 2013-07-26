{title not_lang=yes}{$active_company->getName()}{/title}
{add_bread_crumb}Profile{/add_bread_crumb}
{page_object object=$active_company}

{object_quick_options object=$active_company user=$logged_user}
<div class="company main_object" id="company_details">
  <div class="body">
    {if $active_company->canEdit($logged_user)}
      <a href="{$active_company->getEditLogoUrl()}" id="select_company_icon">
        <img src="{$active_company->getLogoUrl(true)}" alt="" class="properties_icon" />
      </a>
    <script type="text/javascript">
      App.widgets.IconPicker.init('edit_icon','select_company_icon', App.lang('Change Logo'));
    </script>
    {else}
      <img src="{$active_company->getLogoUrl(true)}" alt="" class="properties_icon" />    
    {/if}
    <dl class="properties">
      <dt>{lang}Name{/lang}</dt>
      <dd>{$active_company->getName()|clean}</dd>
      
      <dt>{lang}Address{/lang}</dt>
      <dd>
      {if $active_company->getConfigValue('office_address')}
        {$active_company->getConfigValue('office_address')|clean|nl2br}
      {else}
        <em>{lang}not set{/lang}</em>
      {/if}
      </dd>
    
      <dt>{lang}Phone Number{/lang}</dt>
      <dd>
      {if $active_company->getConfigValue('office_phone')}
        {$active_company->getConfigValue('office_phone')|clean}
      {else}
        <em>{lang}not set{/lang}</em>
      {/if}
      </dd>
    {if $active_company->getConfigValue('office_fax')}
      <dt>{lang}Fax Number{/lang}</dt>
      <dd>{$active_company->getConfigValue('office_fax')|clean}</dd>
    {/if}
    {if is_valid_url($active_company->getConfigValue('office_homepage'))}
      <dt>{lang}Homepage{/lang}</dt>
      <dd><a href="{$active_company->getConfigValue('office_homepage')}">{$active_company->getConfigValue('office_homepage')|clean}</a></dd>
    {/if}
    </dl>
    
    <div class="body content">
    {if is_foreachable($users)}
      <table>
        <tr>
          <th class="icon"></th>
          <th class="name">{lang}Person{/lang}</th>
		  {*BOF:mod 20120831*}
		  <th>{lang}IP Address{/lang}</th>
		  <th>{lang}Location{/lang}</th>
		  {*EOF:mod 20120831*}
          <th class="last_activity">{lang}Last Seen{/lang}</th>
        </tr>
      {foreach from=$users item=user}
        <tr class="{cycle values='odd,even'}">
          <td class="icon"><img src="{$user->getAvatarUrl(true)}" alt="" /></td>
          <td class="name">
            <nobr>{user_link user=$user}</nobr>
            {*}<br/><nobr><a href="{assemble route="goto_home_tab"}&user_id={$user->getId()}">AC Home Page</a></nobr>{*}
			<br/><nobr><a href="{$user->getTaskPageUrlForUser()}">Task Page</a></nobr>
            {if $user->getConfigValue('title')}<nobr><span class="details block">{$user->getConfigValue('title')|clean}</span></nobr>{/if}
          </td>
		  {*BOF:mod 20120831*}
		  <td>{user_ipaddress user=$user}</td>
		  <td>{user_location_by_ipaddress user=$user}</td>
		  {*EOF:mod 20120831*}
          <td class="last_activity details">{if $logged_user->getId() != $user->getId()}{$user->getLastActivityOn()|ago}{/if}</td>
        </tr>
      {/foreach}
      </table>
    {else}
      <p class="empty_page">{lang}There are no users in this company{/lang}{if $add_user_url}. {lang add_url=$add_user_url}Would you like to <a href=":add_url">create one</a>?{/lang}{/if}</p>
    {/if}
    </div>
  </div>
</div>