<? if (!empty($user->bio)) : ?>
<h2><?=§('Bio')?></h2>
<?=$user->bio?>
<? endif; ?>
<h2><?=§('Latest Activities')?></h2>
<?=$user->feed?>