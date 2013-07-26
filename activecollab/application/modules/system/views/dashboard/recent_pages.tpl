{title}Recent Pages{/title}
{add_bread_crumb}Recent Pages{/add_bread_crumb}
{*}
<div class="section_container visible_overflow">
	<table style="font-size:11px;">
	{foreach from=$recent_pages item=recent_page}
		<tr>
			<td align="right">
				{$recent_page.count}&nbsp;&nbsp;
			</td>
			<td>
				<a href="{$recent_page.url}">{$recent_page.description}</a>
			</td>
			<td>
				{$recent_page.access_time}
			</td>
		</tr>
	{/foreach}
	</table>
</div>
{*}
<div class="section_container visible_overflow">
	<ul class="tickets_list common_table_list">
	{foreach from=$recent_pages item=recent_page}
		<li class="ticket {cycle values='odd,even'} sort" style="margin-bottom:5px;">
			<span class="left_options" style="font-size:12px;">
				<span class="option">{$recent_page.count}</span>
				<span class="option"><a href="{$recent_page.url}">{$recent_page.description}</a></span>
			</span>
			<span class="right_options" style="font-size:12px;">
				<span class="option">{$recent_page.access_time}</span>
			</span>
		</li>
	{/foreach}
	</ul>
</div>