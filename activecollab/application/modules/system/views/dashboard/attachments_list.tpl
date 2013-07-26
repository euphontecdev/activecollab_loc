{title}Attachments list{/title}
{add_bread_crumb}Attachments list{/add_bread_crumb}
<div>
	<table id="file_list" class="common_table" width="100%">
		{if is_foreachable($attachments)}
		<tr>
			<td colspan="3">
				<p class="pagination top">
					<span class="inner_pagination">{lang}Page{/lang}: {pagination pager=$pagination}{assemble route=attachments_list project_id=$active_project->getId() page='-PAGE-'}{/pagination}</span>
				</p>
			</td>
		<tr>
		{/if}
        <tr>
          <th>{lang}Thumbnail{/lang}</th>
          <th>{lang}File Details{/lang}</th>
          <th></th>
        </tr>
        {foreach from=$attachments item=attachment}
        	{if instance_of($attachment, 'Attachment')}
        <tr class="file {cycle values='odd,even'}">
        	<td class="thumbnail" valign="top"><a href="{$attachment->getViewUrl()}"><img src="{$attachment->getThumbnailUrl()}" alt="{lang}Thumbnail{/lang}" /></a></td>
            <td class="details" valign="top">
              {lang}Attachment{/lang}:<span class="filename"><a href="{$attachment->getViewUrl()}" title="{$attachment->getName()|clean}">{$attachment->getName()|excerpt:40|clean}</a>, {$attachment->getSize()|filesize}</span>
            </td>
            <td class="options" valign="top">
              <a href="{$attachment->getViewUrl(null, true)}" class="button_add">{lang}Download{/lang}</a>
            </td>
		</tr>		
        	{/if}
        {/foreach}
		{if is_foreachable($attachments)}
		<tr>
			<td colspan="3">
				<p class="pagination top">
					<span class="inner_pagination">{lang}Page{/lang}: {pagination pager=$pagination}{assemble route=attachments_list project_id=$active_project->getId() page='-PAGE-'}{/pagination}</span>
				</p>
			</td>
		<tr>
		{/if}
	</table>
</div>