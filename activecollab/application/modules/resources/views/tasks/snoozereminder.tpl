<div>
	<form name="frm_snooze" action="{$form_action_url}" method="post">
		Snooze for: 
		<input type="text" name="snooze[duration]" value="{$snooze_duration}" maxlength="2" style="width:50px;" />
		&nbsp;
		<select name="snooze[unit]" style="width:100px;">
			<option value="" selected>-- Select --</option>
			<option value="I">Minutes</option>
			<option value="H">Hours</option>
			<option value="D">Days</option>
			<option value="W">Weeks</option>
			<option value="M">Months</option>
		</select>
		&nbsp;
		from now.
	</form>
	{*}<div>
		Previous setting: {$snooze_date}
	</div>{*}
</div>