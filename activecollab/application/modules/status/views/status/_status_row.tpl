{assign var=status_update_user value=$status_update->getCreatedBy()}
<tr class="status_update" status_update_id="{$status_update->getId()}">
  <td class="avatar"><img src="{$status_update_user->getAvatarUrl(true)}" alt="" /></td>
  <td class="message">
    <p class="message"><a href="{$status_update_user->getViewUrl()}" class="author">{$status_update_user->getDisplayName()|clean}</a>: {$status_update->getMessage()|clean|clickable}</p>
    <span class="details block">{$status_update->getCreatedOn()|ago} &middot; <a href="{$status_update->getViewUrl()}" title="{lang}Permalink{/lang}">#</a></span>
        
  
    <div class="status_update_replies">
      <table class="status_update_replies" cellspacing="2">
        <tbody>
      {if $status_update->hasReplies(true)}
        {foreach from=$status_update->getReplies() item=status_update_reply}
          {assign var=status_update_reply_user value=$status_update_reply->getCreatedBy()}
          <tr class="status_update_reply {cycle values='odd,even' name=status_update_replies}" id="status_update_reply_{$status_update_reply->getId()}" parent_id="{$status_update_reply->getParentId()}">
            <td class="avatar"><img src="{$status_update_reply_user->getAvatarUrl()}" alt="" /></td>
            <td class="reply_message">
              <a href="{$status_update_reply_user->getViewUrl()}" class="reply_author">{$status_update_reply_user->getDisplayName()|clean}</a>: {$status_update_reply->getMessage()|clean|clickable} <span class="details italic">({$status_update_reply->getCreatedOn()|ago})</span>
            </td>
          </tr>
        {/foreach}
      {/if}
          <tr class="status_update_reply_textarea">
            <td class="avatar"></td>
            <td><textarea></textarea></td>
          </tr>
          <tr class="status_update_reply_link">
            <td class="avatar"></td>
            <td>{link href=$status_update->getReplyUrl() status_message_id=$status_update->getId() class=status_message_reply}Reply{/link}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </td>
</tr>