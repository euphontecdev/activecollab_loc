{if is_foreachable($options)}
	<option value="">-- Select Object --</option>
	{foreach from=$options item=object}
		<option value="{$object->getId()}">{$object->getName()}</option>
	{/foreach}
{else}
	<option value="">No Listing Available</option>
{/if}