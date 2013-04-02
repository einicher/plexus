			<h1><?=§('Active connections')?></h1>
			<div class="connections">
<?php			
			if (empty($connections)) {
				echo §('Currently there are no active connections.');
			} else {
?>
				<ul>
<? foreach ($connections as $connection) : ?>
					<li>
						<a href="<?=$connection->url?>" target="blank"><?=$connection->name?></a>
	<? if ($connection->status == 5) : ?>
						<a href="<?=$this->addr->current().'?remove='.$connection->id?>" class="remove"><?=§('Remove')?></a>
						<span class="requestCanceled"><?=§('Connection terminated on {{'.date(§('d.m.Y H:i:s'), $connection->disconnected).'}}.')?></span>
	<? else : ?>
						<span><?=date(§('d.m.Y H:i:s'), $connection->requested)?></span>
						<a href="<?=$this->addr->current().'?disconnect='.$connection->id?>" class="cancel"><?=§('Disconnect')?></a>
	<? endif; ?>
					</li>
<? endforeach; ?>	
				</ul>
<?php
			}
?>
			</div>
