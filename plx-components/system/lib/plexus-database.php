<?php
	class PlexusDatabase extends Page
	{
		static $instance;

		static function instance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function __construct()
		{
			$this->plxDbLoaderJS = '.html(\'<div class=\\\'loading\\\'>'.§('Loading').' ...</div>\')';
		}

		function control($level, $levels, $cache)
		{
			Control::$standalone = true;

			if (isset ($_GET['ajax'])) {
				$this->addr->root = $_GET['ajax'];
			}

			if (empty($levels[2])) {
				$this->main = $this->index();
			} else {
				echo $this->browse($this->addr->getLevel(2, $levels));
				exit;
			}
			return $this;
		}

		function view()
		{
			return $this->t->get('system', 'backend.php', array(
				'main' => $this->main,
				'menu' => $this->getMenu(),
				'backendID' => 'database'
			));
		}

		function getMenu()
		{
			$menu = array(
				array('overview', §('Overview'), 'javascript:plxListType(\'INDEX'.(isset($_GET['ajax']) ? '?ajax='.$_GET['ajax'] : '').'\');')
			);
			foreach (Core::$types as $name => $type) {
				$menu[] = array($name, $name, 'javascript:plxListType(\''.$name.(isset($_GET['ajax']) ? '?ajax='.$_GET['ajax'] : '').'\');');
			}
			return $menu;
		}

		function index()
		{
			$plxDbLoaderJS = $this->plxDbLoaderJS;
			$this->tpl->connect('plxDbLoaderJS', $plxDbLoaderJS);

			$this->main();

			return $this->tpl->get('plexus-database.php');
		}

		function main()
		{
			foreach (Core::$types as $name => $type) {
				$type = (object) array(
					'type' => $name,
					'name' => $type['label'],
					'count' => $this->db->fetch('SELECT COUNT(*) `count` FROM '.$this->db->table('index').' WHERE type="'.$name.'"')->count
				);
				$this->tpl->repeat('plexus-database.php', 'boxType', array('type' => $type));
			}
			foreach (Control::$componentsCallback as $class => &$component) {
				$this->tpl->repeat('plexus-database.php', 'plxDbComponent', array('component' => &$component));
			}
			return $this->tpl->cut('plexus-database.php', 'index');
		}

		function browse($type)
		{
			if ($type == 'INDEX') {
				return $this->main();
			}
			if ($type == 'edit') {
				return $this->edit($this->addr->getLevel(3));
			}
			if ($type == 'delete') {
				$ids = explode(',', $this->addr->getLevel(3));
				foreach ($ids as $id) {
					PlexusDataControl::remove($id);
				}
				return file_get_contents($this->addr->getHome($_GET['back']));
			}

			$plxDbLoaderJS = '.html(\'<div class=\\\'loading\\\'>'.§('Loading').' ...</div>\')';
			$this->tpl->connect('plxDbLoaderJS', $plxDbLoaderJS);

			$browse = new stdClass;
			$browse->type = $type;
			$browse->search = '';
			$browse->sort = '';
			if (!empty($_POST['sort'])) {
				$browse->sort = $_POST['sort'];
			}
			if (!empty($_POST['search'])) {
				$browse->search = $_POST['search'];
			}
			$browse->perPage = 30;
			$browse->start = 0;
			$page = $this->addr->getLevel(3);
			if (empty($page)) {
				$page = 1;
			}
			$browse->current = $page;
			if (!empty($page) && is_numeric($page)) {
				$browse->start = ($page*$browse->perPage)-$browse->perPage;
				$browse->from = $browse->start+1;
			}
			$sql = 'SELECT * FROM '.$this->db->table('index').' WHERE type="'.$type.'"';
			if (!empty($browse->search)) {
				$sql = '
					SELECT
						DISTINCT '.Database::table('index').'.id,
						'.Database::table('index').'.parent,
						'.Database::table('index').'.address,
						'.Database::table('index').'.type,
						'.Database::table('index').'.status,
						'.Database::table('index').'.author,
						'.Database::table('index').'.published
					FROM '.Database::table('index').', '.Database::table('textual').'
					WHERE
						'.Database::table('properties').'.value LIKE "%'.$browse->search.'%"
					AND '.Database::table('index').'.id='.Database::table('textual').'.parent
					AND '.Database::table('index').'.type="'.$type.'"
				';
			}

			$browse->overall = $this->db->count($sql);
			$browse->pages = ceil($browse->overall/$browse->perPage);
			if ($browse->overall > $browse->perPage) {
				if ($page > 1) {
					$this->tpl->cut('plexus-database.php', 'plxDbPrev', array('class' => '', 'action' => 'jQuery(\'#plxDbMain\')'.$plxDbLoaderJS.'.load(root + \''.$this->addr->assigned('system.database').'/'.$type.'/'.($page-1).'\')'));
				} else {
					$this->tpl->cut('plexus-database.php', 'plxDbPrev', array('class' => ' disabled', 'action' => ''));
				}
				if ($page < $browse->pages) {
					$this->tpl->cut('plexus-database.php', 'plxDbNext', array('class' =>  '', 'action' => 'jQuery(\'#plxDbMain\')'.$plxDbLoaderJS.'.load(root + \''.$this->addr->assigned('system.database').'/'.$type.'/'.($page+1).'\')'));
				} else {
					$this->tpl->cut('plexus-database.php', 'plxDbNext', array('class' => ' disabled', 'action' => ''));
				}
			} else {
				$browse->perPage = $browse->overall;
			}
			if ($browse->overall == 0) {
				$browse->from = $browse->current = 0;
			}
			$browse->to = ($browse->from+$browse->perPage)-1;
			if ($browse->to > $browse->overall) {
				$browse->to = $browse->overall;
			}

			switch ($type) {
				case 'PAGE':
				case 'POST':
					$cols = array('id' => 'ID', 'title' => 'Title', ':status' => 'Status', ':author' => 'Author', ':published' => 'Published');
				break;
				case 'MICRO':
					$cols = array('id' => 'ID', ':author' => 'Author', ':published' => 'Published', ':post:shorten' => 'Post');
				break;
				case 'USER':
					$cols = array('id' => 'ID', 'name' => 'Name', 'email' => 'Email');
				break;
				case 'IMAGE':
					$cols = array('id' => 'ID', ':file:thumb' => 'Preview', 'title' => 'Title', ':author' => 'Author', ':published' => 'Published');
				break;
				case 'GALLERY':
					$cols = array('id' => 'ID', ':images:thumbs' => 'Preview', 'title' => 'Title', ':author' => 'Author', ':published' => 'Published');
				break;
				case 'LINK':
					$cols = array('id' => 'ID', 'title' => 'Title', 'link' => 'Link', ':author' => 'Author', ':published' => 'Published');
				break;
				default:
					$cols = array('id' => 'ID', 'address' => 'Address', ':status' => 'Status', ':author' => 'Author', ':published' => 'Published');
			}
			$cols = $this->observer->notify('plexusDatabase.cols.'.strtolower($type), $cols);

			foreach ($cols as $col) {
				$this->tpl->repeat('plexus-database.php', 'typeTH', array('col' => $this->lang->get($col)));
			}

			$class = $browse->perPage%2 ? 'dark' : 'light';
			if (empty($browse->sort)) {
				$sql .= ' ORDER BY published DESC';
			} else {
				$sql .= ' ORDER BY '.Database::table('properties').'.value DESC';
			}
			$sql .= ' LIMIT '.$browse->start.','.$browse->perPage;
			while ($fetch = $this->db->fetch($sql, 1)) {
				$data = PlexusDataControl::fetchDataSet($fetch);
				foreach ($cols as $col => $label) {
					if (substr($col, 0, 1) == ':') {
						$col = substr($col, 1);
						if (strpos($col, ':') !== false) {
							$col = explode(':', $col);
							$v = $data->$col[0];
							$col = $col[1];
						} else {
							$v = $data->$col;
						}
						$value = $this->special($col, $v, $data);
					} else {
						$value = $data->$col;
					}
					$value = $this->observer->notify('plexusDatabase.value.'.strtolower($type).$col, $value, $data);
					$this->tpl->repeat('plexus-database.php', 'typeTD', array('data' => $value, 'item' => (object) array('class' => $class, 'id' => $fetch->id)));
				}
				$action = 'jQuery(\'#plxDbMain\')'.$plxDbLoaderJS.'.load(root + \''.$this->addr->assigned('system.database.edit').'/'.$data->id.'?back='.urlencode($this->addr->path).'\', function(data, status, ajax) { alert(jQuery(\'form.plexusForm\').attr(\'action\')); jQuery(\'form.plexusForm\').ajaxForm({ url: jQuery(\'form.plexusForm\').attr(\'action\'), success: function(data) { alert(data); } }) })';
				$this->tpl->repeat('plexus-database.php', 'item', array('item' => (object) array('class' => $class, 'id' => $fetch->id)));
				$this->tpl->set('plexus-database.php', 'typeTD');
				$class = $class == 'light' ? 'dark' : 'light';
			}			
			$browse->sql = $sql;

			for ($i=1; $i<=$browse->pages; $i++) {
				$this->tpl->repeat('plexus-database.php', 'plxDbPage', array('action' => 'jQuery(\'#plxDbMain\')'.$plxDbLoaderJS.'.load(root + \''.$this->addr->assigned('system.database').'/'.$type.'/'.$i.'\')', 'page' => $i));
			}
			$this->tpl->cut('plexus-database.php', 'pages', array('browse' => $browse));

			return $this->tpl->cut('plexus-database.php', 'plxDbBrowse', array('browse' => $browse));
		}

		function special($special, $value, $data)
		{
			switch ($special) {
				case 'status':
					switch ($value) {
						case 0: $value = §('Draft'); break;
						case 1: $value = §('Published'); break;
						case 2: $value = §('Published Hidden'); break;
					}
				break;
				case 'author':
					if (is_numeric($value)) {
						$author = new User($value);
						$value = $author->name;
					} else {
						$value = $value;
					}
				break;
				case 'published':
					$value = date($this->lang->get('Y-m-d H:i:s'), $value);
				break;
				case 'thumb':
					$value = '<img class="thumb" src="'.$this->imageScaleLink($this->getStorage('images/'.$value), 100, 75).'" alt="" />';
				break;
				case 'thumbs':
					$images = $value;
					$value = '';
					$i=0;
					foreach ($images as $v) {
						$i++;
						if ($i<=5) {
							$img = $this->type($v);
							$value .= '<img class="thumb" src="'.$this->imageScaleLink($this->getStorage('images/'.$img->file), 100, 75).'" alt="" />';
						}
					}
				break;
				case 'shorten':
					$value = $this->tools->cutByChars($value);
				break;
			}
			return $this->observer->notify('plexus.database.listing.value', $value, $special, $data);
		}

		function edit($id)
		{
			$action = '';
			$back = 'jQuery(\'#plxDbMain\')'.$this->plxDbLoaderJS.'.load(root + \''.$_GET['back'].'?back='.urlencode($_GET['back']).'\')';

			if (is_numeric($id)) {
				$data = PlexusDataControl::getDataById($id);
			} else {
				$data = $this->type($id);
			}

			if (!empty($_POST)) {
				$data->doRedirect = FALSE;
				$data->ajaxCreate = true;
				$save = $data->save((object) $_POST);
				if (is_numeric($save)) {
					$data = $this->getData($save);
					$action = $this->addr->assigned('system.database.edit').'/'.$save.'?back='.@$_GET['back'].'&ajax='.@$_GET['ajax'];
					$success = 1;
				}
			}
			ob_start();
?>
			<div id="plxDbScroller">
<? if (!empty(Core::$errors)) : ?>
	<div class="errors"><?=Core::$errors?></div>
<? endif; ?>
<? if (!empty($success)) : ?>
	<div class="infos"><?=$this->lang->get('The data was successfully saved.')?></div>
	<script type="text/javascript" >
		jQuery('#plxDbScroller .infos').delay(5000).fadeOut();
	</script>
<? endif;?>

<?=$data->form($action)?>
			</div>
			<div id="plxDbMainBottomPanel">
				<span class="click posLeft" onclick="<?=$back?>">&lt; <?=$this->lang->get('Back')?></span>
<? if (is_numeric($id)) : ?>
				<span class="click" style="float: right" onclick="window.open('<?=$data->link()?>', 'view')" title="<?=$data->link()?>">Ansehen &gt;</span>
<? endif; ?>
			</div>
<?php
			return ob_get_clean();
		}

		function getTitle()
		{
		}

		function getDescription()
		{
		}
	}
?>
