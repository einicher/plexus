<p>
	<?=§('Please enter your username or email address. You will receive a temporary password via email.')?>
</p>
<form method="post" action="">
	<p>
		<label for="request"><?=§('Name or Email')?></label>
		<input type="text" id="request" name="request" value="<?=@$_POST['request']?>" />
	</p>
	<p>
		<button type="submit"><?=§('Request new password')?></button>
	</p>
</form>