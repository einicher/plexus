<?php
	class User extends PlexusCrud
	{
		public $type = 'USER';
		public $status = 2;
		public $author = 0;
		public $rights = array();

		static $cache;

		function construct()
		{
			$this->add('string', 'name', TRUE, array(
				'label' => $this->lang->get('Name'),
				'transformToAddress' => 1
			));
			$this->add('string', 'email', TRUE, array(
				'label' => $this->lang->get('Email')
			));
			$this->add('string', 'password', TRUE, array(
				'label' => $this->lang->get('Password'),
				'beforeSaving' => array('encryptPassword', $this)
			));
			$this->add('wysiwyg', 'bio', FALSE, array(
				'label' => $this->lang->get('Bio'),
				'multimedia' => TRUE
			));
			$this->add('custom', 'groups', FALSE, array(
				'actor' => Access::getInstance(),
				'call' => 'groupsDialog'
			));
			$this->add('custom', 'rights', FALSE, array(
				'actor' => Access::getInstance(),
				'call' => 'rightsDialog'
			));
			$this->add('datetime', 'lastonline', FALSE, -1);
			$this->observer->notify('user.construct', $this);
		}

		function init()
		{
			if (!empty($this->rights)) {
				if ($this->rights == -1) {
					$this->rights = array(-1);
				} elseif (is_string($this->rights)) {
					$this->rights = explode(',', $this->rights);
				}
			} else {
				$this->rights = array();
			}

			if (!empty($this->groups)) {
				$this->groups = explode(',', $this->groups);
				$this->rights = $this->access->inheritGroupRights($this->rights, $this->groups);
			} else {
				$this->groups = array();
			}
			$this->passwordHash = $this->password;
		}

		function beforeEdit($form = FALSE)
		{
			if (!empty($this->id)) {
				$this->change('password', 'password', FALSE, array(
					'label' => $this->lang->get('New password')
				));
				$this->password = '';
				$this->add('password', 'password2', FALSE, array(
					'label' => $this->lang->get('Confirm new password'),
					'after' => 'password'
				));
			}

			if (!isset($this->plexusImport) && (!isset(Access::$user->groups[0]) || Access::$user->groups[0] != -1)) {
				$this->hide('rights');
				$this->hide('groups');
			}

			if (!empty(Access::$user)) {
				if (!isset($this->plexusImport)) {
					$this->remove('lastonline');
				}
			}
		}

		function beforeSave()
		{
			if (empty($this->id)) {
				if (empty($this->doNotEncrypt)) {
					$this->password = sha1($this->password);
				}
			} else {
				if (empty($this->password) && empty($this->password2)) {
					$this->password = $this->passwordHash;
					$this->remove('password2');
				} elseif (empty($this->password) || empty($this->password2) || $this->password != $this->password2) {
					$this->error($this->lang->get('New Password and confirmation password did not match.'));
					$this->password = '';
					$this->password2 = '';
					return FALSE;
				} else {
					$this->password = sha1($this->password);
					$this->remove('password2');
				}
			}

			if (!isset($this->plexusImport)) {
				$check = $this->pdb->get('USER', array(
					'email' => $this->email,
					'id' => '!='.$this->id
				));
				if (!empty($check)) {
					$this->error($this->lang->get('A user with the email “{{'.$this->email.'}}” already exists. {{<a href="'.$this->addr->registered('system.users.password').'">'.$this->lang->get('You can request an new password here.').'</a>}}'));
					return FALSE;
				}
			}

			if (!empty($this->groups)) {
				$this->change('groups', 'text');
				if (is_array($this->groups)) {
					$this->groups = implode(',', $this->groups);
				}
			}

			if (!empty($this->rights)) {
				$this->change('rights', 'text');
				if (is_array($this->rights)) {
					$this->rights = implode(',', $this->rights);
				}
			}
		}

		function getTitle()
		{
			return $this->observer->notify('user.getTitle', $this->name);
		}

		function getContent()
		{
			require_once PLX_SYSTEM.'lib/widget-site-feed.php';
			$w = new SiteFeedWidget;
			$w->sql = 'SELECT * FROM '.$this->db->table('index').' WHERE status=1 AND author='.$this->id.' ORDER BY published DESC';
			$this->feed = $w->view();
			return $this->tpl->get('user.php', array('user' => $this));
		}

		function getTags()
		{
			return implode(', ', $this->getTagsArray());
		}

		function getTagsArray()
		{
			$tags = array();
			while ($fetch = $this->db->fetch('SELECT value FROM '.$this->db->table('textual').' WHERE name="tags" and parent IN (
				SELECT id FROM '.$this->db->table('index').' WHERE author='.$this->id.'
			)', 1)) {
				$tags[$fetch->value]++;
			}
			return $tags;
		}

		function encryptPassword($password)
		{
			return sha1($password);
		}

		function result()
		{
			$this->excerpt = $this->tools->cutByWords(strip_tags($this->tools->detectSpecialSyntax($this->bio)));
			$this->tpl->cut('result.php', 'excerpt', array('result' => $this));
			$this->title = $this->name;
			$result = $this->tpl->cut('result.php', 'result', array('result' => $this));
			$this->tpl->set('result.php', 'excerpt');
			$this->tpl->set('result.php', 'result');
			return $result;
		}
	
		function getName()
		{
			return $this->name;
		}
	}
?>