<?php
	class PlexusDataControl extends Core
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
		}

		function getDataById($id, &$assignment = '', $sql = '')
		{
			return self::fetchDataSet($id, $assignment, $sql);
		}

		function getDataByObject($object, &$assignment = '', $sql = '')
		{
			return self::fetchDataSet($object, $assignment, $sql);
		}

		function fetchDataSet($mixed, &$assignment = '', $sql = '')
		{
			$init = false;

			if (is_numeric($mixed)) {
				$id = $mixed;
				$fetch = $this->d->get('SELECT * FROM `#_index` WHERE `id`='.$id);
			} elseif (is_object($mixed)) {
				$id = $mixed->id;
				$fetch = $mixed;
			} elseif (is_array($mixed)) {
				$id = $mixed['id'];
				$fetch = (object) $mixed;
			} elseif (is_string($mixed)) {
				return $this->control->run($mixed);
			} else {
				trigger_error('No possible value given.', E_USER_WARNING);
			}

			if (empty($fetch->id)) {
				return false;
			}

			if (empty($assignment)) {
				require_once Core::$types[$fetch->type]['file'];
				$assignment = new Core::$types[$fetch->type]['class'];
				$init = true;
			}

			foreach ($fetch as $name => $value) {
				$assignment->$name = $value;
			}

			$properties = $this->d->get('SELECT `name`,`value` FROM `#_properties` WHERE `parent`='.$id, array(
				'force_array' => true
			));
			if (!empty($properties)) {
				foreach ($properties as $property) {
					$assignment->{$property->name} = $property->value;
				}
			}

			if ($init) {
				$assignment->init();
			}

			return $assignment;
		}

		function getProperty($parent, $name)
		{
			$fetch = $this->d->getPrepared('SELECT `value` FROM `#_properties` WHERE `parent`=? && `name`=?', 'is', $parent, $name);
			if (!empty($fetch->value)) {
				return $fetch->value;
			}
			return null;
		}

		function unifyAddress($parent, $address)
		{
			$fetch = Database2::instance()->getPrepared('SELECT * FROM `#_index` WHERE `parent`=? && `address`=?', 'is', $parent, $address);
			if (empty($fetch)) {
				return $address;
			}

			$parts = explode('-', $address);
			if (count($parts) > 1) {
				$lastpart = array_pop($parts);
				if (is_numeric($lastpart)) {
					$lastpart++;
					$address = implode('-', $parts).'-'.$lastpart;
				} else {
					$address = implode('-', $parts).'-'.$lastpart.'-1';
				}
			} else {
				$address = $address.'-1';
			}

			return self::unifyAddress($parent, $address);
		}

		function save(&$bluePrint, &$data, $autoAddress = FALSE)
		{
			$force = false;
			$addr = Address::getInstance();
			$d = Database2::instance();

			if (($addr->getLevel(-2) == Address::$reserved['system.create']['address'] && $addr->getLevel(-3) == '')
				|| ($addr->getLevel(-1) == Address::$reserved['system.edit']['address'] && $addr->getLevel(-2) == '')
				|| (count(Control::$languages) > 1 && (
					($addr->getLevel(-2) == Address::$reserved['system.translate']['address'] && count($addr->levels) == 4)
					 || ($addr->getLevel(-2) == Address::$reserved['system.create']['address'] && count($addr->levels) == 4)
					 || ($addr->getLevel(-1) == Address::$reserved['system.edit']['address'] && count($addr->levels) == 4)
					)
				)
			) {
				$force = true;
			}
			$address = $data->address;
			if ($autoAddress || (empty($data->address) && !$force)) {
				foreach ($bluePrint as $field) {
					if (isset($field['options']['transformToAddress'])) {
						if (!empty($data->$field['name'])) {
							$data->address = Address::transform($data->$field['name']);
						}
					}
				}
			}

			$data->address = self::unifyAddress($data->parent, $data->address);

			self::checkForUpToDateTables();

			if (empty($data->id)) {
				$d->getPrepared('INSERT INTO #_index SET parent=?,type=?,address=?,status=?,author=?,published=?,language=?', 'issiiis', $data->parent, $data->type, $data->address, $data->status, $data->author, $data->published, $data->language);
				$data->id = $d->insert_id;
			} else {
				$d->getPrepared('UPDATE #_index SET parent=?,type=?,address=?,status=?,author=?,published=?,language=? WHERE id=?', 'issiiisi', $data->parent, $data->type, $data->address, $data->status, $data->author, $data->published, $data->language, $data->id);
			}

			if (empty($data->address) && !$force) {
				$data->address = base_convert($data->id+72000, 10, 36);
				$data->address = self::unifyAddress($data->parent, $data->address);
				$d->getPrepared('UPDATE #_index SET address=? WHERE id=?', 'si', $data->address, $data->id);
			}

			if ($address != $data->address) {
				#Database::query('INSERT INTO '.Database::table('index').' SET status=2, parent=0, type="REDIRECT", address="'.Database::escape($address).'"');
			}

			foreach ($bluePrint as $field) {
				if (isset($field['bevoreSaving'])) {
					$field['value'] = $field['bevoreSaving'][1]->$field['bevoreSaving'][0]($field['value']);
				}

				if (!in_array($field['name'], array('id', 'parent', 'address', 'type', 'status', 'author', 'published', 'language'))) { //these are already saved to the index table a few lines above
					if ($field['type'] == 'custom') {
						if (!empty($field['options']) && !empty($field['options']['type'])) {
							$field['type'] = $field['options']['type'];
						}
					}

					switch($field['type']) {
						case 'string':
						case 'text':
						case 'wysiwyg':
						case 'select-string':
						case 'password':
						case 'number':
						case 'select':
						case 'checkbox':
						case 'custom':
							if ($field['type'] == 'checkbox') {
								if (!empty($data->$field['name'])) {
									$data->$field['name'] = 1;
								} else {
									$data->$field['name'] = 0;
								}
							}
							self::saveProperty($data->id, $field['name'], $data->$field['name']);
						break;

						case 'date':
						case 'time':
						case 'datetime':
							if (!is_numeric($data->$field['name'])) {
								$data->$field['name'] = strtotime($data->$field['name']);
							}
							self::saveProperty($data->id, $field['name'], $data->$field['name']);
						break;

						case 'file':
							$filename = empty($data->$field['name']) ? '' : $data->$field['name'];
							if (!isset($field['options']['target'])) {
								exit('Set $field[\'options\'][\'target\'] @'.$field['name']);
							}

							$targetDir = Core::getStorage($field['options']['target']).'/';
							if (!file_exists($targetDir)) {
								if (mkdir($targetDir, 0777)) {
									chmod($targetDir, 0777);
								} else {
									exit('No permissions to create '.$targetDir);
								}
							}

							if (isset($_FILES[$field['name']])) {
								if (empty($_FILES[$field['name']]['error'])) {
									$filename = $data->id.'-'.Address::transform($_FILES[$field['name']]['name']);
									move_uploaded_file($_FILES[$field['name']]['tmp_name'], $targetDir.$filename);
									@chmod($targetDir.$filename, 0777);
								}
							}

							if (!empty($data->{$field['name'].'URL'})) {
								$filename = explode('/', $data->{$field['name'].'URL'});
								$filename = array_pop($filename);
								$filename = $data->id.'-'.Address::transform($filename);
								copy($data->{$field['name'].'URL'}, $targetDir.$filename);
								@chmod($targetDir.$filename, 0777);
							}

							if (!empty($data->{$field['name'].'-data'})) {
								$filename = $data->$field['name'];
								$fs = fopen($targetDir.$filename, 'w');
								fwrite($fs, base64_decode($data->{$field['name'].'-data'}));
								fclose($fs);
								@chmod($targetDir.$filename, 0777);
							}

							if (!empty($data->$field['name']) && $data->$field['name'] != $filename) {
								@unlink($targetDir.$data->$field['name']);
								@chmod($targetDir.$filename, 0777);
							}
							self::saveProperty($data->id, $field['name'], $filename);
						break;
					}
				}
			}

			return $data->id;
		}

		function saveProperty($id, $name, $value)
		{
			$d = Database2::instance();
			$check = $d->query('SELECT * FROM `'.$d->table('properties').'` WHERE name="'.$d->escape($name).'" AND parent='.$id) OR exit($d->error);
			if ($check && $check->num_rows) {
				if (empty($value)) {
					return $d->query('DELETE FROM `'.$d->table('properties').'` WHERE name="'.$d->escape($name).'" AND parent='.$id) OR exit($d->error);
				} else {
					return $d->query('UPDATE `'.$d->table('properties').'` SET value="'.$d->escape($value).'" WHERE name="'.$d->escape($name).'" AND parent='.$id) OR exit($d->error);
				}
			} else {
				return $d->query('INSERT INTO `'.$d->table('properties').'` SET value="'.$d->escape($value).'", name="'.$d->escape($name).'", parent='.$id) OR exit($d->error);
			}
			return false;
		}

		function remove($id)
		{
			// remove associated files
			$data = self::getDataById($id);
			foreach (PlexusDataModel::$bluePrints[$data->type] as $field) {
				if ($field['type'] == 'file') {
					$src = $this->getStorage($field['options']['target']).'/';
					$src .= $data->$field['name'];
					if (file_exists($src) && is_file($src)) {
						unlink($src);
					}
				}
			}

			// remove table data
			Database::query('DELETE FROM '.Database::table('properties').' WHERE parent='.$id);
			Database::query('DELETE FROM '.Database::table('index').' WHERE id='.$id);
			return true;
		}

		// mixed $type: if type is array they were treatend as conditions
		function get($type, $conditions = '', $multi = FALSE)
		{
			$sql = '';
			$cols = array(
				'id' => 'index',
				'type' => 'index',
				'address' => 'index',
				'parent' => 'index',
				'status' => 'index',
				'author' => 'index',
				'published' => 'index'
			);

			if (is_array($type)) {
				$multi = $conditions;
				$conditions = $type;
				$type = '';
			} else {
				$class = $this->getType($type)->class;
				require_once $this->getType($type)->file;
				$current = new $class;
				$blueprint =& $current->getBlueprint();
				foreach ($blueprint as $print) {
					$cols[$print['name']] = $print['type'];
				}
			}

			$relations = '';
			$tables = array($this->db->table('index'));
			if (!empty($conditions)) {
				foreach ($conditions as $col => $value) {
					if (empty($cols[$col])) {
						if (strpos($col, '::') !== FALSE) {
							$split = explode('::', $col);
							$col = $split[1];
							$cols[$col] = $split[0];
						} else {
							trigger_error('PlexusDataControl::get column '.$col.' not found.', E_USER_ERROR);
						}
					}
	
					switch ($cols[$col]) {
						case 'index':
							$table = $this->db->table('index');
						break;
						case 'number':
						case 'date':
						case 'time':
						case 'datetime':
							$table = $this->db->table('properties');
						break;
						case 'string':
						case 'text':
							$table = $this->db->table('properties');
						break;
					}
	
					if (!in_array($table, $tables)) {
						$tables[] = $table;
						if ($cols[$col] != 'index') {
							$relations .= ' AND '.$this->db->table('index').'.id='.$table.'.parent';
						}
					}
	
					$operator = '=';
					if (substr($value, 0, 2) == '<=') {
						$operator = '<=';
						$value = substr($value, 2);
					}
					if (substr($value, 0, 2) == '>=') {
						$operator = '>=';
						$value = substr($value, 2);
					}
					if (substr($value, 0, 2) == '<>') {
						$operator = '<>';
						$value = substr($value, 2);
					}
					if (substr($value, 0, 2) == '!=') {
						$operator = '!=';
						$value = substr($value, 2);
					}
					if (substr($value, 0, 1) == '>') {
						$operator = '>';
						$value = substr($value, 1);
					}
					if (substr($value, 0, 1) == '<') {
						$operator = '<';
						$value = substr($value, 1);
					}
	
					if (!is_numeric($value)) {
						$value = '\''.Database::escape($value).'\'';
					}
	
					if ($cols[$col] == 'index') {
						$sql .= ' AND '.$table.'.'.$col.$operator.$value;
					} else {
						$sql .= ' AND ('.$table.'.name="'.Database::escape($col).'" AND '.$table.'.value'.$operator.$value.')';
					}
				}
			}

			$q = 'SELECT '.$this->db->table('index').'.* FROM '.implode(',', $tables).' WHERE 0=0';
			if (!empty($type)) {
				$q .= ' AND '.$this->db->table('index').'.type="'.$type.'"';
			}
			$q .= "\n".$relations."\n".$sql;
#echo µ($q);
			if ($multi) {
				$data = array();
				while ($fetch = Database::fetch($q, TRUE)) {
					$data[] = $this->getDataByObject($fetch);
				}
				return $data;
			} else {
				$data = Database::fetch($q);
				if (empty($data)) {
					return FALSE;
				} else {
					return $this->getDataByObject($data);
				}
			}
		}

		function search($options = '', $directSQL = '')
		{
			$excludedTypes = array('USER', 'GROUP');
			foreach (Core::$types as $type => $props) {
				if (isset($props['options']['excludeFromSearch'])) {
					$excludedTypes[] = $type;
				}
			}

			$tables['`#_index` i'] = 1;
			$tableConditions = array();
			$order = ' ORDER BY i.published DESC';
			$where = ' WHERE i.type NOT IN ("'.implode('","', $excludedTypes).'")';
			$status = ' AND (i.status=1 || i.status=2)';

			if (!empty($options['tags'])) {
				$tables['`#_properties` p'] = 1;
				$tableConditions['i.id=p.parent'] = 1;
				$where .= ' AND p.name="tags" AND (
					FIND_IN_SET("'.$options['tags'].'", p.value)
					OR FIND_IN_SET(" '.$options['tags'].'", p.value)
				)';
			}

			if (!empty($options['pattern'])) {
				$tables['`#_properties` p'] = 1;
				$tableConditions['i.id=p.parent'] = 1;
				$p = '"+'.str_replace(' ', ' +', $this->d->escape($options['pattern'])).'"';
				$where .= ' AND MATCH(p.value) AGAINST('.$p.' IN BOOLEAN MODE) AND !(i.type="IMAGE" && status=2)';
			}

			$start = 0;
			$current = 1;
			$limit = 10;
			if (!empty($options['limit'])) {
				$limit = $options['limit'];
			}
			if (!empty($options['order'])) {
				$order = $options['order'];
			}
			if (Control::getInstance()->paginationActive) {
				$current = Control::getInstance()->paginationPage;
				$start = ceil($current*$limit)-$limit;
			}
			$limitSQL = ' LIMIT '.$start.','.($limit*10);

			if (!empty($options['status'])) {
				$s = explode(',', $options['status']);
				$status = ' AND (i.status='.implode(' || i.status=', $s).')';
			}

			$tableConditions = implodeKeys(' AND ', $tableConditions);
			if (!empty($tableConditions)) {
				$tableConditions = ' AND '.$tableConditions;
			}

			$sql = 'SELECT i.*'.(empty($options['pattern']) ? '' : ',MATCH(p.value) AGAINST('.$p.') `score`').' FROM '.implodeKeys(', ', $tables).$where.$tableConditions.$directSQL.$status.' GROUP BY i.id '.$order;

			$results = $this->d->count($sql);
			$sql .= $limitSQL;

			$i = 0;
			$collect = '';
			$cache = array();

			$r = $this->d->get($sql);
			if (!empty($r)) {
				foreach ($r as $fetch) {
					$i++;
					$data = Core::type($fetch);
					if (method_exists($data, 'result')) {
						if (isset($options['cluster']) && $options['cluster'] === FALSE) {
							$collect .= $data->result($options);
						} else {
							$collect .= $data->result($options, $cache, $i);
							$cache[] = $data;
						}
						if ($i==$limit) {
							break;
						}
					}
				}
				if ($i) {
					Control::getInstance()->paginationUsed = true;
				}
			}

			$this->tpl->set('result.php', 'result');

			if (isset($options['pattern'])) {
				$this->tpl->cut('result.php', 'results', array('search' => (object) array(
					'pattern' => $options['pattern'],
					'results' => $results
				)));
			}

			$collect.'<div class="clear"></div>';

			if (@$options['pagination'] !== FALSE && $results > $limit) {
				$collect .= $this->tools->pagination('searchResults', $results, $current, $limit).'<div class="clear"></div>';
			}

			return $collect;
		}

		static function checkForUpToDateTables()
		{
			$language = '';
			$query = mysql_query('SHOW columns FROM '.Database::table('index'));
			while ($fetch = mysql_fetch_object($query)) {

				// removed due to conflict with $this->lang
				if ($fetch->Field == 'lang') {
					mysql_query('ALTER TABLE '.Database::table('index').' DROP `lang`'); 
				}

				// added in 0.4.1
				if ($fetch->Field == 'language') {
					$language = 1;
				}
			}
			if (!$language) {
				mysql_query('ALTER TABLE '.Database::table('index').' ADD `language` varchar(255) NOT NULL');
			}
		}
	}
?>
