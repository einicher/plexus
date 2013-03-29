<p><?=§('The requested page was not found.')?></p>
<?=$this->tpl->cut('result.php', 'search', array('search' => (object) array('pattern' => '', 'action' => $this->addr->getRoot($this->addr->assigned('system.search')))))?>
<p></p>
<p><?=§('Do you want to {{<a href="'.$this->addr->assigned('system.create', '', 1).'" rel="nofollow">'.§('create').'</a>}} it?')?></p>
