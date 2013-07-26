{title}Status Update{/title}
{add_bread_crumb}View{/add_bread_crumb}

<div id="status_update_details">
  <a href="#" class="avatar">
    <img src="{$active_status_update_author->getAvatarUrl(true)}" alt="" />
  </a>
  <p class="message">
    <a href="{$active_status_update_author->getViewUrl()}" class="author">{$active_status_update_author->getDisplayName()|clean}</a>: {$active_status_update->getMessage()|clean|clickable}
  </p>
  <span class="details block">{$active_status_update->getCreatedOn()|ago}</span>
  
  <div class="status_update_replies">
    <table class="status_update_replies">
      <tbody>
    {if $active_status_update->hasReplies(true)}
      {foreach from=$active_status_update->getReplies() item=status_update_reply}
        {include_template name=_status_reply_row controller=status module=status}
      {/foreach}
    {/if}
        <tr class="status_update_reply {cycle values='odd,even' name=status_update_replies}" reply_url="{$active_status_update->getReplyUrl()}">
          <td class="avatar"><img src="{$logged_user->getAvatarUrl()}" alt="" /></td>
          <td class="message">
            <div id="status_update_reply">
              <textarea></textarea>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>