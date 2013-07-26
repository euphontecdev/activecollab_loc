<div class="select_asignees_inline_widget" id="{$_select_assignees_id}">
{if $_select_assignees_choose_responsible}
  <input type="hidden" name="{$_select_assignees_responsible_name}" value="{$_select_assignees_responsible}" id="{$_select_assignees_id}_responsible" />
  <div class="select_asignees_inline_widget_responsible_block">
    <span class="placeholder">{lang}No one is responsible{/lang}</span>
  </div>
{/if}
{if $set_fyi_actionrequest}
        {counter start=0 print=false}
	{foreach from=$_select_assignees_users key=company item=users}
	  {if is_foreachable($users)}
            {capture name=counter}{counter}{/capture}
	    <div class="user_group">
	      <label class="company_name" for="{$_select_assignees_id}_company_{$company|clean}"><input type="checkbox" name="" value="" id="{$_select_assignees_id}_company_{$company|clean}" class="input_checkbox" /><span>{lang company_name=$company}All of :company_name{/lang}</span></label>
	      <div class="company_users">
	        <table width="100%" cellpadding="2">
	          <tr>
	          	<td width="40%"><b style="font-size:10px;">User</b></td>
	          	<td width="20%">
                            <b style="font-size:10px;">Action Request <span style="cursor:pointer;text-decoration:underline;color:rgb(0,0,255);" onmouseover="showtitle('actionrequest_title_{$smarty.capture.counter}');" onmouseout="hidetitle('actionrequest_title_{$smarty.capture.counter}');">?</span></b>
                            <div id="actionrequest_title_{$smarty.capture.counter}" style="text-align:left;display:none;border:1px solid black;background-color:#FBF9EA;width:400px;position:absolute;margin:10px;padding:5px;">
                                <br/>
                                Action Requests are a way of asking a Team Member to do something specific for a Project:
                                <br/><br/>
                                This Action Request will show up on a person's Home Page in AC.
                                <br/>
                            </div>
                        </td>
	          	<td width="20%" align="right" style="display:none;">
                            <b style="font-size:10px;">FYI <span style="cursor:pointer;text-decoration:underline;color:rgb(0,0,255);" onmouseover="showtitle('fyi_title_{$smarty.capture.counter}');" onmouseout="hidetitle('fyi_title_{$smarty.capture.counter}');">?</span></b>
                            <div id="fyi_title_{$smarty.capture.counter}"  style="text-align:left;display:none;border:1px solid black;background-color:#FBF9EA;width:400px;position:absolute;margin:10px;padding:5px;">
                                <br/>
                                FYI Notifications are a way of asking a Team Member to read a Comment that was posted.
                                <br/><br/>
                                To set an FYI Notification for someone,<br/>select the Mark for FYI checkbox next to that person's name.
                                <br/><br/>
                                This FYI Notification will show up on a person's Home Page in AC.
                                <br/>
                            </div>
                        </td>
	          	<td width="20%" align="right">
                            <b style="font-size:10px;">Email <span style="cursor:pointer;text-decoration:underline;color:rgb(0,0,255);" onmouseover="showtitle('email_title_{$smarty.capture.counter}');" onmouseout="hidetitle('email_title_{$smarty.capture.counter}');">?</span></b>
                            <div id="email_title_{$smarty.capture.counter}"  style="text-align:left;display:none;border:1px solid black;background-color:#FBF9EA;width:400px;position:absolute;margin:10px;padding:5px;">
                                <!--<br/>
                                The Email field allows you to have the system send an Email to that person when posting a Comment.
                                <br/><br/>
                                To send an Email to a Team Member when posting a Comment, check the Email checkbox for that person.-->
								Email Comment to Team Member
                                <br/>
                            </div>
                        </td>
                  </tr>
	          {foreach from=$users item=user name=users_loop}
	            <tr>
					<td>
						<span class="company_user">
	              			<input type="checkbox" name="{$_select_assignees_name}[]" value="{$user.id}" id="{$_select_assignees_id}_user_{$user.id}" {if in_array($user.id, $_select_assignees_assigned)}checked="checked"{/if} class="input_checkbox"/>
	              			{if $_select_assignees_choose_responsible && ($_select_assignees_responsible == $user.id)}
	                			<span class="responsible_setter responsible user_selected">{$user.display_name|clean}</span>
	              			{else}
	                			<span class="responsible_setter {if in_array($user.id, $_select_assignees_assigned)}user_selected{/if}">{$user.display_name|clean}</span>
	              			{/if}
	            		</span>
					</td>
					<td>
						<span style="">
							{set_assignee_actionrequest_html object_id=$object_id user_id=$user.id}
						</span>
					</td>
					<td align="right" style="display:none;">
						<span style="">
							{set_assignee_fyi_html object_id=$object_id user_id=$user.id}
						</span>
					</td>
					<td align="right">
						<span style="">
							{set_assignee_email_html object_id=$object_id user_id=$user.id}
						</span>
					</td>
				</tr>
				<tr><td colspan="3" style="height:5px;"></td></tr>
	          {/foreach}
	        </table>
	      </div>
	    </div>
	  {/if}
	{/foreach}
{else} 
	{assign var='count' value=0}
	<table>
	{foreach from=$_select_assignees_users key=company item=users}
		{if is_foreachable($users)}
			{assign var='count' value=$count+1}
			{if $count==1}
				<tr>
			{/if}
			<td class="user_group" style="float:left;width:30%;">
				<label class="company_name" for="{$_select_assignees_id}_company_{$company|clean}"><input type="checkbox" name="" value="" id="{$_select_assignees_id}_company_{$company|clean}}" class="input_checkbox" /><span>{lang company_name=$company}All of :company_name{/lang}</span></label>
				<div class="company_users" style="margin-left:10px;">
					{foreach from=$users item=user name=users_loop}
						<span class="company_user">{$users_loop}
						<input type="checkbox" name="{$_select_assignees_name}[]" value="{$user.id}" id="{$_select_assignees_id}_user_{$user.id}" {if in_array($user.id, $_select_assignees_assigned)}checked="checked"{/if} class="input_checkbox"/>
						{if $_select_assignees_choose_responsible && ($_select_assignees_responsible == $user.id)}
							<span class="responsible_setter responsible">{$user.display_name|clean}</span>
						{else}
							<span class="responsible_setter">{$user.display_name|clean}</span>
						{/if}
						</span>
						<br/>
					{/foreach}
				</div>
			</td>
			{if $count>=3}
				{assign var='count' value=0}
				</tr>
			{/if}
		{/if}
	{/foreach}
	</table>
	{if $count>0}
		</tr>
	{/if}
	{*}{foreach from=$_select_assignees_users key=company item=users}
	  {if is_foreachable($users)}
	    <div class="user_group">
	      <label class="company_name" for="{$_select_assignees_id}_company_{$company|clean}"><input type="checkbox" name="" value="" id="{$_select_assignees_id}_company_{$company|clean}}" class="input_checkbox" /><span>{lang company_name=$company}All of :company_name{/lang}</span></label>
	      <div class="company_users">
	        <table style="width:100%;">
	          <tr>
	          {foreach from=$users item=user name=users_loop}
	            {if ($smarty.foreach.users_loop.index % $_select_assignees_users_per_row == 0) && ($smarty.foreach.users_loop.index !=0)}
	              </tr><tr>
	            {/if}
	            <td style="width:150px;"><span class="company_user">{$users_loop}
	              <input type="checkbox" name="{$_select_assignees_name}[]" value="{$user.id}" id="{$_select_assignees_id}_user_{$user.id}" {if in_array($user.id, $_select_assignees_assigned)}checked="checked"{/if} class="input_checkbox"/>
	              {if $_select_assignees_choose_responsible && ($_select_assignees_responsible == $user.id)}
	                <span class="responsible_setter responsible">{$user.display_name|clean}</span>
	              {else}
	                <span class="responsible_setter">{$user.display_name|clean}</span>
	              {/if}
	            </span></td>
	          {/foreach}
	          </tr>
	        </table>
	      </div>
	    </div>
	  {/if}
	{/foreach}{*}
{/if}
</div>
<script type="text/javascript">
  var picker_wrapper = $('#{$_select_assignees_id}:first');
  App.widgets.SelectAsigneesInlineWidget.init(picker_wrapper, '#{$_select_assignees_id}',{var_export var=$_select_assignees_choose_responsible}{if $_select_assignees_choose_responsible && $_select_assignees_responsible}, {$_select_assignees_responsible}{/if});
</script>