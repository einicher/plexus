<?php
	class Access extends Core
	{
		static $instance;
		static $user;
		static $rights = array();

		static function getInstance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function showLogin()
		{
			return new Page($this->lang->get('Login'), $this->getLoginDialog());
		}

		function getLoginDialog()
		{
			return $this->tpl->get('login.php');
		}

		function login($login, $password = '', $remember = '', $redirect = TRUE, $encrypted = FALSE)
		{
			$error = FALSE;
			
			if (is_numeric($login)) {
				$check = $this->db->fetch('SELECT id FROM '.$this->db->table('index').' WHERE id="'.$this->db->escape($login).'" AND type="USER"');
			} elseif(is_object($login)) {
				$user = $login;
				$check = TRUE;
				$encrypted = TRUE;
				$password = $user->password;
			} else {
				$check = $this->db->fetch('
					SELECT parent id FROM '.$this->db->table('properties').' WHERE parent IN (
						SELECT id FROM '.$this->db->table('index').' WHERE type="USER"
					) AND (
						(name="email" AND value="'.$this->db->escape($login).'")
						OR (name="name" AND value="'.$this->db->escape($login).'")
					)
				');
			}

			if (empty($check)) {
				$error = TRUE;
			} else {
				if (empty($user)) {
					$user = $this->getData($check->id);
				}
				$p = $encrypted ? $password : sha1($password);
				$pass = array($user->password);
				$passwords = $this->getOption('temporary', $user->id);
				if (!empty($passwords)) {
					if (is_array($passwords)) {
						foreach ($passwords as $password) {
							$password = json_decode($password->value);
							if ($password->time > strtotime('now -1 week')) {
								$pass[] = $password->password;
							}
						}
					} else {
						$password = json_decode($passwords->value);
						if ($password->time > strtotime('now -1 week')) {
							$pass[] = $password->password;
						}
					}
				}
				if (in_array($p, $pass)) {
					$_SESSION['user']['id'] = $user->id;
					$_SESSION['user']['pw'] = $p;
					if (!empty($remember)) {
						setcookie('user[id]', $user->id, time()+31536000, '/');
						setcookie('user[pw]', $user->password, time()+31536000, '/');
						$_COOKIE['user']['id'] = $user->id;
						$_COOKIE['user']['pw'] = $user->password;
					}
					Access::$user = $user;

					if ($redirect) {
						header('Location:'.$this->addr->current(1));
						exit;
					} else {
						return TRUE;
					}
				} else {
					$error = TRUE;
				}
			}

			if ($error) {
				$this->error($this->lang->get('Access denied. Wrong password or Name/ID/Email.'));
			}
			return FALSE;
		}

		function logout($redirect = TRUE)
		{
			session_destroy();
			setcookie('user[id]', '', time()-86401, '/');
			setcookie('user[pw]', '', time()-86401, '/');
			unset($_COOKIE['user']);
			if ($redirect) {
				header('Location: '.$this->addr->current(-1));
			}
		}

		function check()
		{
			if (isset($_COOKIE['user']['id']) || isset($_COOKIE['user']['pw'])) {
				if (empty($_COOKIE['user']['id']) || empty($_COOKIE['user']['pw'])) {
					unset($_COOKIE['user']);
					setcookie('user[id]', '', time()-86401, '/');
					setcookie('user[pw]', '', time()-86401, '/');
				} else {
					if (empty($_SESSION['user']['id'])) {
						$_SESSION['user']['id'] = $_COOKIE['user']['id'];
					}
					if (empty($_SESSION['user']['pw'])) {
						$_SESSION['user']['pw'] = $_COOKIE['user']['pw'];
					}
				}
			}

			if (isset($_SESSION['user']['id']) || isset($_SESSION['user']['pw'])) {
				if (empty($_SESSION['user']['id']) || empty($_SESSION['user']['pw'])) {
					unset($_SESSION['user']);
				} else {
					if ($this->login($_SESSION['user']['id'], $_SESSION['user']['pw'], NULL, FALSE, TRUE)) {
						return TRUE;
					} else {
						$this->logout(FALSE);
						return FALSE;
					}
				}
			}
		}

		function granted($right = '')
		{
			#return TRUE;
			if (empty(Access::$user->id)) {
				return FALSE;
			} else {
				if (Access::$user->rights == 'OVERLOARD') {
					return TRUE;
				} else {
					if (empty($right)) {
						return TRUE;
					} else {
						if (in_array($right, Access::$user->rights)) {
							return TRUE;
						} else {
							return FALSE;
						}
					}
				}
			}
		}

		function getUser($property = '')
		{
			if (is_object(self::$user)) {
				if (empty($property)) {
					return self::$user;
				} elseif (!empty(self::$user->$property)) {
					return self::$user->$property;
				} else {
					return FALSE;
				}
			} else {
				return FALSE;
			}
		}

		function registerRight($name, $label)
		{
			$dbg = debug_backtrace();
			if (isset($dbg[0]['file'])) {
				preg_match('='.PLX_COMPONENTS.'([^/]*)/=', $dbg[0]['file'], $results);
				if (!empty($results[1])) {
					$component = $results[1];
				}
			}
			self::$rights[$component][$name] = $label;
		}
		
		function rightsDialog($action, $attributes, $field, $fields)
		{
			ob_start();
?>
<div class="plxRightsDialog">
	<strong><?=$this->lang->get('Rights')?></strong><br />
	<div class="plxRightsDialogContainer">
	<? foreach (self::$rights as $component => $rights) : ?>
		<dl class="plxRightsDialogComponent">
			<dt><?=$component?></dt>
			<dd>
				<? foreach ($rights as $right => $label) :
					if (is_array($label)) :
						echo $label[0]->$label[1]($right, $field);
					else :
				?>
				<input type="checkbox" id="rights-<?=$right?>" name="rights[]" value="<?=$right?>" <?= @in_array($right, $field->value) ? ' checked="checked"' : '' ?>/>
				<label for="rights-<?=$right?>"><?=$this->lang->get($label)?></label><br />
				<? endif; endforeach; ?>
			</dd>
		</dl>
		<? if ($component == 'system') : ?>
			<dl class="plxRightsDialogComponent">
				<dt><?=$this->lang->get('Data type access')?></dt>
				<dd>
					<? foreach (Core::$types as $name => $type) : $right = 'system.data.'.strtolower($name); ?>
					<input type="checkbox" id="rights-<?=$right?>" name="rights[]" value="<?=$right?>" <?= @in_array($right, $field->value) ? ' checked="checked"' : '' ?>/>
					<label for="rights-<?=$right?>"><?=$this->lang->get($type['label'])?></label><br />
					<? endforeach; ?>
				</dd>
			</dl>
		<? endif; ?>
		<? endforeach; ?>
		<div class="clear"></div>
	</div>
</div>
<br />
<?php
			return ob_get_clean();
		}

		function groupsDialog($action, $attributes, $field, $fields)
		{
			ob_start();
?>
<div class="plxGroupsDialog">
	<strong><?=$this->lang->get('Groups')?></strong> (<?=$this->lang->get('Group rights will be inherited to this user')?>)
	<div class="plxRightsDialogContainer">
			<input type="checkbox" id="group--1" name="groups[]" value="-1" <?= in_array(-1, $field->value) ? 'checked="checked"' : '' ?>/> <label for="group--1"><?=$this->lang->get('Overloards')?> <span class="description">(<?=$this->lang->get('All time full access, no matter what')?>)</span></label><br />
<? while ($fetch = $this->db->fetch('SELECT * FROM '.$this->db->table('index').' WHERE type="GROUP"', 1)) :
		$group = $this->type($fetch); ?>
			<input type="checkbox" id="group-<?=$group->id?>" name="groups[]" value="<?=$group->id?>" <?= in_array($group->id, $field->value) ? 'checked="checked"' : '' ?>/> <label for="group-<?=$group->id?>"><?=$group->name?><? if (!empty($group->description)) : ?> (<span class="description" title="<?=strip_tags($group->description)?>"><?=$this->tools->cutByWords(strip_tags($group->description), 7)?></span>)<? endif; ?></label><br />
<? endwhile; ?>
			<div class="clear"></div>
	</div>
</div><br />
<?php
			return ob_get_clean();
		}

		function inheritGroupRights($rights, $groups)
		{
			if (in_array(-1, $groups)) {
				return 'OVERLOARD';
			}

			$sql = 'SELECT * FROM '.$this->db->table('index').' WHERE type="GROUP" AND id IN('.implode(',', $groups).')';
			while ($fetch = $this->db->fetch($sql, 1)) {
				$group = $this->type($fetch);
				foreach ($group->rights as $right) {
					$rights[] = $right;
				}
			}
			$this->db->clear($sql);

			return $rights;
		}

		function getObservers()
		{
			$users = array();
			$qry = mysql_query('SELECT i.* FROM '.Database::table('index').' i JOIN '.Database::table('properties').' t ON t.parent=i.id WHERE t.name="groups" AND FIND_IN_SET("-1", t.value)');
			while ($fetch = mysql_fetch_object($qry)) {
				$users[$fetch->id] = $this->getData($fetch);
			}
			return $users;
		}
	}
?>
