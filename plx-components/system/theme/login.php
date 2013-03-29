<form class="default login" method="post" action="">
	<p class="ident">
		<label for="login"><?=§('Name, ID, Email')?></label>
		<input type="text" id="login" class="text" name="login" />
	</p>
	<p class="password">
		<label for="password"><?=§('Password')?></label>
		<input type="password" id="password" class="text" name="password" />
	</p>
	<p class="remember">
		<input class="reverse" type="checkbox" class="checkbox" id="remember" name="remember" />
		<label class="checkbox" for="remember"><?=§('Remember me on this computer')?></label>
	</p>
	<p class="submit">
		<button type="submit"><?=§('Login')?></button>
		<a href="<?=$this->addr->assigned('system.users.password', '', 1)?>"><?=§('Lost my password')?></a>
	</p>
	<input type="hidden" name="plexusLogin" value="1" />
</form>
<div class="clear"></div>
