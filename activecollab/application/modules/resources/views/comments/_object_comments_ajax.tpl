{foreach from=$_object_comments_comments item=_object_comments_comment name=_object_comments_comments}
	{if $smarty.foreach._object_comments_comments.iteration < $_counter}  
		{assign var=_object_comment_author value=$_object_comments_comment->getCreatedBy()}
		<div class="subobject comment {if $smarty.foreach._object_comments_comments.iteration == 1}first_subobject{/if} {cycle values='odd,even' name=object_comments}" id="comment{$_object_comments_comment->getId()}" {if $_object_comments_comment->getIntegerField2()>0} style="margin-left:1.25cm;border-left:3px solid black;" {/if}>
			<div class="subobject_author">
				<a class="avatar" href="{if instance_of($_object_comment_author, 'User')}{$_object_comment_author->getViewUrl()}{elseif instance_of($_object_comment_author, 'AnonymousUser') && trim($_object_comment_author->getName()) && is_valid_email($_object_comment_author->getEmail())}mailto:{$_object_comment_author->getEmail()}{/if}">
					<img src="{$_object_comment_author->getAvatarUrl(true)}" alt="avatar" />
				</a>
          
				<ul class="comment_options">
					<li class="comment_options_first">&nbsp;</li>
					<li>{link href=$_object_comments_comment->getPrintCommentUrl() title='Print Comment'}<img src="{image_url name=icons/print.gif}" alt="" />{/link}</li>
					<li>{link href=$_object_comments_comment->getViewUrl() title='Permalink' class='subobject_permalink' not_lang=true}{lang}#{/lang}{counter name=comment_num}{/link}</li>
					{if $_object_comments_comment->canEdit($logged_user)}
						<li>{link href=$_object_comments_comment->getAttachmentsUrl() title='Manage Attachments'}<img src="{image_url name=gray-attachment.gif}" alt="" />{/link}</li>
						<li>{link href=$_object_comments_comment->getEditUrl() title='Update Comment'}<img src="{image_url name=gray-edit.gif}" alt="" />{/link}</li>
					{/if}
					{if $_object_comments_comment->canDelete($logged_user)}
						<li><a href="{$_object_comments_comment->getTrashUrl()}" title="Move to Trash" onclick="if(confirm('Are you sure you want to\n\n delete this comment?\n\n')) {ldelim} App.postLink('{$_object_comments_comment->getTrashUrl()}'); {rdelim} return false;" ><img src='{image_url name=gray-delete.gif}' alt='delete' /></a></li>
					{/if}
					{if $_object_comments_comment->getVisibility() == VISIBILITY_PRIVATE}
						<li>{object_visibility object=$_object_comments_comment user=$logged_user}</li>
					{/if}
				</ul>
				<script type="text/javascript">
					App.CommentOptions.init('comment{$_object_comments_comment->getId()}');
				</script>
				<div class="subobject_author_info">
					{user_link user=$_object_comment_author} {lang}said{/lang}<br />
					<span class="subobject_date">{$_object_comments_comment->getCreatedOn()|ago}</span>
				</div>
			</div>
			{if $_object_comments_comment->is_action_request_user}
				<div id="action_request_div">
					<div style="width:50px;display:inline;"><input type="checkbox" id="chk_action_request_cID_{$_object_comments_comment->getId()}_userID_{$logged_user->getId()}" style="width:20px;" onclick="set_action_request_fyi_flag(this);" /></div>
					<div style="display:inline;">Action request has been completed.</div>
				</div>
			{/if}
			{if $_object_comments_comment->is_fyi_user}
				<div id="fyi_div">
					<div style="width:50px;display:inline;"><input type="checkbox" id="chk_fyi_user_cID_{$_object_comments_comment->getId()}_userID_{$logged_user->getId()}" style="width:20px;" onclick="set_action_request_fyi_flag(this);" /></div>
					<div style="display:inline;">Request has been read.</div>
				</div>
			{/if}
			<div class="content" id="comment_body_{$_object_comments_comment->getId()}">
				{*}BOF:mod 20130429{get_custom_comment_body comment=$_object_comments_comment }EOF:mod 20130429{*}
				{*}BOF:mod 20130429{*}
				{$_object_comments_comment->getFormattedBody()}
				{get_comment_custom_info comment=$_object_comments_comment }
				{*}EOF:mod 20130429{*}
			</div>
			{if $_object_comments_comment->getSource() == $smarty.const.OBJECT_SOURCE_EMAIL}
				<script type="text/javascript">
					App.EmailObject.init('comment_body_{$_object_comments_comment->getId()}');
				</script>
			{/if}
			{object_attachments object=$_object_comments_comment brief=yes show_reply_to_comment_link=true}
		</div>
	{/if}
{/foreach}