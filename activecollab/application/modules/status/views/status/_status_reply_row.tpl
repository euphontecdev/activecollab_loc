{assign var=status_update_reply_user value=$status_update_reply->getCreatedBy()}
<tr class="status_update_reply {cycle values='odd,even' name=status_update_replies}" id="status_update_reply_{$status_update_reply->getId()}" parent_id="{$status_update_reply->getParentId()}">
  <td class="avatar"><img src="{$status_update_reply_user->getAvatarUrl()}" alt="" /></td>
  <td class="message">
    <a href="{$status_update_reply_user->getViewUrl()}" class="reply_author">{$status_update_reply_user->getDisplayName()|clean}</a>: {$status_update_reply->getMessage()|clean|clickable} <span class="details italic">({$status_update_reply->getCreatedOn()|ago})</span>
  </td>
</tr>