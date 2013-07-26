{if $gettype=='projects'}
    {foreach from=$projects item=project}
    <option value="{$project->getId()}">{$project->getName()}</option>
    {/foreach}
{elseif $gettype=='tickets'}
    {foreach from=$tickets item=ticket}
    <option value="{$ticket->getId()}">{$ticket->getName()}</option>
    {/foreach}
{elseif $gettype=='pages'}
	{if is_foreachable($pages_with_milestone) && is_foreachable($pages_with_project) }
	<optgroup label="Pages associated with selected project">
	{/if}
    {foreach from=$pages_with_milestone item=page}
    <option value="{$page->getId()}">{$page->getName()}</option>
    {/foreach}
	{if is_foreachable($pages_with_milestone) && is_foreachable($pages_with_project) }
	</optgroup>
	{/if}
	
	{if is_foreachable($pages_with_milestone) && is_foreachable($pages_with_project) }
	<optgroup label="Pages associated with selected team">
	{/if}
    {foreach from=$pages_with_project item=page}
    <option value="{$page->getId()}">{$page->getName()}</option>
    {/foreach}
	{if is_foreachable($pages_with_milestone) && is_foreachable($pages_with_project) }
	</optgroup>
	{/if}
{elseif $gettype=='action'}
    {$link}
{else}
	<table style="width:100%;">
		<tr>
			<td style="width:15%;">Team</td>
			<td style="width:85%">
				<select id="teams" onchange="get_projects_by_team_id('{$cur_project_id}', '{$cur_attachment_id}');">
					<option value="">--Select Team--</option>
					{foreach from=$teams key=team_id item=team_name}
					<option value="{$team_id}">{$team_name}</option>
					{/foreach}
				</select>
				<span id="loader_projects"></span>
			</td>
		</tr>
		<tr>
			<td>Project</td>
			<td>
            <select id="projects" onchange="get_tickets_n_pages_by_project_id('{$cur_project_id}', '{$cur_attachment_id}');object_is_selected(this);">
                <option value="">--Select Project--</option>
            </select>
			<span id="loader_objects"></span>
			</td>
		</tr>
		<tr>
			<td>Ticket</td>
			<td>
            <select id="tickets" onchange="object_is_selected(this);">
                <option value="">--Select Ticket--</option>
            </select>
			</td>
		</tr>
		<tr>
			<td>Page</td>
			<td>
            <select id="pages" onchange="object_is_selected(this);">
                <option value="">--Select Page--</option>
            </select>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center"><span id="ack">&nbsp;</span></td>
		</tr>
	</table>
	<input type="hidden" name="current_project_id" value="{$cur_project_id}" />
	<input type="hidden" name="attachment_id" value="{$cur_attachment_id}" />
	<input type="hidden" name="copy_to_object_id" value="" />
    {*}<div>
        <div style="width:75px;height:26px;float:left;display:inline;">Team</div>
        <div style="float:right;display:inline;">
            <select id="teams" onchange="get_projects_by_team_id('{$cur_project_id}', '{$cur_attachment_id}');">
                <option value="">--Select Team--</option>
                {foreach from=$teams key=team_id item=team_name}
                <option value="{$team_id}">{$team_name}</option>
                {/foreach}
            </select>
        </div>
    </div>
    <div>
        <div id="projects" style="width:75px;height:26px;float:left;display:inline;">Project</div>
        <div style="float:right;display:inline;">
            <select id="projects" onchange="get_tickets_by_project_id('{$cur_project_id}', '{$cur_attachment_id}');">
                <option value="">--Select Project--</option>
            </select>
        </div>
    </div>
    <div>
        <div id="tickets" style="width:75px;height:26px;float:left;display:inline;">Ticket</div>
        <div style="float:right;display:inline;">
            <select id="tickets" >
                <option value="">--Select Ticket--</option>
            </select>
        </div>
    </div>
    <div>
        <div id="tickets" style="width:75px;height:26px;float:left;display:inline;">Page</div>
        <div style="float:right;display:inline;">
            <select id="pages" >
                <option value="">--Select Page--</option>
            </select>
        </div>
    </div>
    <div>
        <div id="action" style="width:75px;height:26px;float:left;display:inline;">&nbsp;</div>
        <div style="float:right;display:inline;height:26px;">
            <input type="button" value="Copy" onclick="copy_attachment_to('{$cur_project_id}', '{$cur_attachment_id}')" style="width:100px;" />
        </div>
    </div>{*}
{/if}