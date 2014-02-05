<?php
	class Access extends Core
	{
		static $instance;
		static $user;
		static $rights = array();

		static public function &instance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function showLogin()
		{
			return new Page(§('Login'), $this->getLoginDialog());
		}

		function getLoginDialog()
		{
			return $this->t->get('login.php');
		}

		function login($login, $password = '', $remember = '', $redirect = TRUE, $encrypted = FALSE)
		{
			$error = FALSE;
			
			if (is_numeric($login)) {
				$check = $this->d->get('SELECT id FROM `#_index` WHERE id="'.$this->d->escape($login).'" AND type="USER"');
			} elseif(is_object($login)) {
				$user = $login;
				$check = TRUE;
				$encrypted = TRUE;
				$password = $user->password;
			} else {
				$check = $this->d->get('
					SELECT parent id FROM `#_properties` WHERE parent IN (
						SELECT id FROM `#_index` WHERE type="USER"
					) AND (
						(name="email" AND value="'.$this->d->escape($login).'")
						OR (name="name" AND value="'.$this->d->escape($login).'")
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
						header('Location:'.$this->a->current(1));
						exit;
					} else {
						return TRUE;
					}
				} else {
					$error = TRUE;
				}
			}

			if ($error) {
				$this->error(§('Access denied. Wrong password or Name/ID/Email.'));
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
				header('Location: '.$this->a->current(-1));
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
	<strong><?php echo §('Rights')?></strong><br />
	<div class="plxRightsDialogContainer">
	<? foreach (self::$rights as $component => $rights) : ?>
		<dl class="plxRightsDialogComponent">
			<dt><?php echo $component?></dt>
			<dd>
				<? foreach ($rights as $right => $label) :
					if (is_array($label)) :
						echo $label[0]->$label[1]($right, $field);
					else :
				?>
				<input type="checkbox" id="rights-<?php echo $right?>" name="rights[]" value="<?php echo $right?>" <?php echo  @in_array($right, $field->value) ? ' checked="checked"' : '' ?>/>
				<label for="rights-<?php echo $right?>"><?php echo §($label)?></label><br />
				<? endif; endforeach; ?>
			</dd>
		</dl>
		<? if ($component == 'system') : ?>
			<dl class="plxRightsDialogComponent">
				<dt><?php echo §('Data type access')?></dt>
				<dd>
					<? foreach (Core::$types as $name => $type) : $right = 'system.data.'.strtolower($name); ?>
					<input type="checkbox" id="rights-<?php echo $right?>" name="rights[]" value="<?php echo $right?>" <?php echo  @in_array($right, $field->value) ? ' checked="checked"' : '' ?>/>
					<label for="rights-<?php echo $right?>"><?php echo §($type['label'])?></label><br />
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
	<strong><?php echo §('Groups')?></strong> (<?php echo §('Group rights will be inherited to this user')?>)
	<div class="plxRightsDialogContainer">
			<input type="checkbox" id="group--1" name="groups[]" value="-1" <?php echo  in_array(-1, $field->value) ? 'checked="checked"' : '' ?>/> <label for="group--1"><?php echo §('Overloards')?> <span class="description">(<?php echo §('All time full access, no matter what')?>)</span></label><br />
<?
	$q = $this->d->query('SELECT * FROM `#_index` WHERE `type`="GROUP"');
	while ($group = $q->fetch_object('Group')) :
?>
			<input type="checkbox" id="group-<?php echo $group->id?>" name="groups[]" value="<?php echo $group->id?>" <?php echo  in_array($group->id, $field->value) ? 'checked="checked"' : '' ?>/> <label for="group-<?php echo $group->id?>"><?php echo $group->name?><? if (!empty($group->description)) : ?> (<span class="description" title="<?php echo strip_tags($group->description)?>"><?php echo $this->tools->cutByWords(strip_tags($group->description), 7)?></span>)<? endif; ?></label><br />
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

			$q = $this->d->query('SELECT * FROM `#_index` WHERE `type`="GROUP" AND id IN('.implode(',', $groups).')');
			while ($group = $q->fetch_object('Group')) {
				foreach ($group->rights as $right) {
					$rights[] = $right;
				}
			}

			return $rights;
		}

		function getObservers()
		{
			$users = array();
			$q = $this->d->query('SELECT i.* FROM `#_index` i JOIN `#_properties` p ON p.parent=i.id WHERE p.name="groups" AND FIND_IN_SET("-1", p.value)');
			while ($fetch = $q->fetch_object()) {
				$users[$fetch->id] = $this->getData($fetch);
			}
			return $users;
		}
	}
?>
