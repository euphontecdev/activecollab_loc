<div>
{if $message!=''}
	<div>
		{$message}
		<br/>
		<button type="button" onclick="location.href='{$redirect_url}';"><span><span>Continue</span></span></button>
	</div>
{/if}
</div>