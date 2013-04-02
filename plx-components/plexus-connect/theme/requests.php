<? if (!empty($send)) : ?>
	<? if (empty($send->status)) : ?>
			<div class="errors">
		<? if (isset($send->connectionStatus)) :
				switch ($send->connectionStatus) :
					case 0: echo §('A connection request for {{'.$send->url.'}} was made, but the target did not respond. Maybe its not a Plexus driven website?.'); break;
					case 1: echo §('There already is a pending connection request for {{'.$send->url.'}}.'); break;
					case 2: echo §('There already is an established connection for {{'.$send->url.'}}.'); break;
					case 3: echo §('There already has been a connection request for {{'.$send->url.'}}. Sorry, but it has been refused.'); break;
				endswitch;
		   elseif (isset($send->message)) : ?>
				<?=§($send->message)?>
		<? else : ?>
				<?=§('Something went wrong.')?>
		<? endif; ?>
			</div>
	<? else : ?>
			<div class="infos"><?=§('Connection request successfull. You now need to wait until the target accepts/refuses to connect with you.')?></div>
	<? endif; ?>
<? endif; ?>
			<form id="request" method="post" action="">
				<p><?=§('Send a connection request to another Plexus website.')?></p>
				<input type="text" id="plexusConnectionRequest" name="plexusConnectionRequest" />
				<button type="submit"><?=§('Send request')?></button>
			</form>
			<br />
			<br />
			<h2><?=§('Pending connection requests')?></h2>
			<div class="connections">
<?php			
			if (empty($connections)) {
				echo §('Currently there are no pending connection requests.');
			} else {
?>
			<ul>
<? foreach ($connections as $connection) : ?>
	<? if ($connection->status != 2) : ?>
				<li>
					<a href="<?=$connection->url?>" target="blank"><?=$connection->name?></a>
					<span><?=date(§('d.m.Y H:i:s'), $connection->requested)?></span>
	<? if ($connection->status == 0) : ?>
					<span><?=§('requested by you and not confirmed by them. Maybe not a Plexus driven website?')?></span>
					<a href="<?=$this->addr->current().'?cancel='.$connection->id?>" class="cancel"><?=§('Cancel')?></a>
	<? elseif ($connection->status == 1 && isset($connection->validated)) : ?>
					<span><?=§('requested by you')?></span>
					<a href="<?=$this->addr->current().'?cancel='.$connection->id?>" class="cancel"><?=§('Cancel')?></a>
	<? elseif ($connection->status == 2) : ?>

	<? elseif ($connection->status == 3) : ?>
					<a href="<?=$this->addr->current().'?remove='.$connection->id?>" class="remove"><?=§('Remove')?></a>
					<span class="requestCanceled"><?=§('Request refused on {{'.date(§('d.m.Y H:i:s'), $connection->refused).'}}.')?></span>
	<? elseif ($connection->status == 4) : ?>
					<span><?=§('requested by them')?></span>
					<a href="<?=$this->addr->current().'?remove='.$connection->id?>" class="remove"><?=§('Remove')?></a>
					<span class="requestCanceled"><?=§('Request canceled on {{'.date(§('d.m.Y H:i:s'), $connection->canceled).'}}.')?></span>
	<? else : ?>
					<span><?=§('requested by them')?></span>
					<a href="<?=$this->addr->current().'?refuse='.$connection->id?>" class="refuse"><?=§('Refuse')?></a>
					<a href="<?=$this->addr->current().'?accept='.$connection->id?>" class="accept"><?=§('Accept')?></a>
	<? endif; ?>
				</li>
	<? endif; ?>
<? endforeach; ?>			
			</ul>
<?php
			}
?>
			</div>
