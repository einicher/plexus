<p><?=§('Trackbacks for {{<a href="'.$data->getLink().'">'.$data->getTitle().'</a>}} are possible.')?></p>
<?
	if (!isset($_SESSION['trackbackCaptcha'])) {
		$_SESSION['trackbackCaptcha'] = substr(sha1(time()), 5, 5);
	}
?>
<? if (empty($_POST['trackbackCaptcha']) || strrev($_SESSION['trackbackCaptcha']) != strtolower($_POST['trackbackCaptcha'])) : ?>
<h2><?=§('Captcha')?></h2>
<form method="post" action="" />
<? if (isset($_POST['trackbackCaptcha']) && strrev($_SESSION['trackbackCaptcha']) != strtolower($_POST['trackbackCaptcha'])) : ?>
	<div class="errors"><?=§('Wrong captcha string.')?></div>
<? endif; ?>
	<p>
		<span style="display: block; margin: 0 0 5px 0;"><?=§('To get a trackback link type in the string “{{'.$_SESSION['trackbackCaptcha'].'}}” in reverse order:')?></span>
		<input type="text" name="trackbackCaptcha" value="" />
		<button type="submit"><?=§('Send')?></button>
	</p>
</form>
<? else : ?>
<p><?=§('Just send a Trackback to the following URL: {{<code class="code">'.$this->addr->current('', false, '', true).'</code>}}')?></p>
<? endif; ?>

<? $trackbacks = $data->getTrackbacks(); ?>
<? if (!empty($trackbacks)) : ?>
<div class="trackbacksFeed">
<h2><?=§('Trackbacks already received')?></h2>
<? foreach ($trackbacks as $trackback) : ?>
	<article class="trackback">
		<a href="<?=$trackback->url?>" target="_blank">
			<div class="sitename"><?=$trackback->blog_name?> »</div>
			<div class="title"><?=$trackback->title?></div>
			<div class="time"><?=$this->tools->detectTime($trackback->time)?></div>
			<div class="excerpt"><?=$trackback->excerpt?></div>
		</a>
	</article>
<? endforeach; ?>
</div>
<? endif; ?>
