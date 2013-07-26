{* 5/5/2012 (SA) Ticket #825: limit number of versions visible*}
{if is_foreachable($versions)}
      {foreach from=$versions item=_version name=ver}      	
        <tr class="{cycle values='odd,even'}">
          <td class="revision_num"><a href="{$_version->getViewUrl()}">#{$_version->getVersion()}</a></td>
          <td class="author">{action_on_by user=$_version->getCreatedBy() datetime=$_version->getCreatedOn()}</td>
          <td class="options">
          {if $active_page->canEdit($logged_user)}
            {link href=$active_page->getRevertUrl($_version) title='Revert to this version' confirm='Are you sure that you want to revert back to this version?' method=post}<img src="{image_url name=revert-gray.gif module=pages}" alt="" />{/link}
          {/if}
          {if $_version->canDelete($logged_user)}
            {link href=$_version->getDeleteUrl() title='Permanently delete version' class=remove_revision}<img src="{image_url name=gray-delete.gif}" alt="" />{/link}
          {/if}
          </td>
        </tr>      
      {/foreach}      
{/if}