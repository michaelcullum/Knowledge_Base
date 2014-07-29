<?php
/**
*
* Knowledge Base extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace tmbackoff\knowledgebase\controller;

use Symfony\Component\DependencyInjection\Container;

class main_controller
{
	/* @var \phpbb\auth\auth */
	protected $auth;

	/* @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request */
	protected $request;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/** @var Container */
	protected $phpbb_container;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/**
	* The database table the categories are stored in
	*
	* @var string
	*/
	protected $categories_table;

	/**
	* The database table the articles are stored in
	*
	* @var string
	*/
	protected $articles_table;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth                     $auth             Auth object
	* @param \phpbb\config\config                 $config           Config object
	* @param \phpbb\db\driver\driver_interface    $db               Database object
	* @param \phpbb\controller\helper             $helper           Helper object
	* @param \phpbb\request\request               $request          Request object
	* @param \phpbb\template\template             $template         Template object
	* @param \phpbb\user                          $user             User object
	* @param Container                            $phpbb_container  Service container
	* @param string                               $root_path        phpBB root path
	* @param string                               $php_ext          phpEx
	* @param string                               $categories_table Name of the table used to store category data
	* @param string                               $articles_table   Name of the table used to store article data
	*
	* @return \tmbackoff\knowledgebase\controller\main_controller
	* @access public
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, Container $phpbb_container, $root_path, $php_ext, $categories_table, $articles_table)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_container = $phpbb_container;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->categories_table = $categories_table;
		$this->articles_table = $articles_table;
	}

	/**
	* Knowledge Base controller for route /kb/{name}
	*
	* @param string		$name
	* @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function display($name)
	{
		$this->template->assign_var(
			'U_MCP', ($this->auth->acl_get('m_') || $this->auth->acl_getf_global('m_')) ? append_sid("{$this->root_path}mcp.$this->php_ext", 'i=main&amp;mode=front', true, $this->user->session_id) : ''
		);

		if (!$this->auth->acl_get('u_kb_read'))
		{
			return $this->helper->error($this->user->lang('NOT_AUTHORISED'));
		}

		switch ($name)
		{
			case 'index':
				$category_id = $this->request->variable('c', 'all');
				$type = $this->request->variable('type', 'approved');

				$sql = 'SELECT *
					FROM ' . $this->categories_table . '
					ORDER BY left_id ASC';
				$result = $this->db->sql_query($sql);
				$category_id_array = array();
				while ($row = $this->db->sql_fetchrow($result))
				{
					$category_id_array[] = $row['category_id'];

					$this->template->assign_block_vars('categories', array(
						'CATEGORY_ID'	=> $row['category_id'],
						'CATEGORY_NAME'	=> $row['category_name'],

						'U_CATEGORY'	=> $this->helper->route('knowledgebase_main_controller', array('name' => 'index', 'c' => $row['category_id'])),
					));
				}
				$this->db->sql_freeresult($result);

				if (!in_array($category_id, $category_id_array) && $category_id != 'all')
				{
					return $this->helper->error($this->user->lang('NO_CATEGORY'));
				}

				$type_array = array('approved', 'disapproved', 'denied');
				foreach ($type_array as $key)
				{
					$this->template->assign_block_vars('types', array(
						'TYPE'	=> $this->user->lang(strtoupper($key)),

						'U_TYPE'	=> $this->helper->route('knowledgebase_main_controller', array('name' => 'index', 'type' => $key)),
					));
				}

				$sql_where = '';
				if (($this->auth->acl_get('m_kb_approve') || $this->auth->acl_get('m_kb_deny') || $this->auth->acl_get('m_kb_disapprove')) && $type)
				{
					switch ($type)
					{
						case 'approved':
							$sql_where .= ' AND article_approved = 1';
						break;

						case 'disapproved':
							$sql_where .= ' AND article_approved = 0 AND article_denied = 0';
						break;

						case 'denied':
							$sql_where .= ' AND article_approved = 0 AND article_denied = 1';
						break;
					}
				}
				else
				{
					$sql_where .= ' AND article_approved = 1';
				}

				if ((!$this->auth->acl_get('m_kb_approve') && !$this->auth->acl_get('m_kb_deny') && !$this->auth->acl_get('m_kb_disapprove')) && $type != 'approved')
				{
					return $this->helper->error($this->user->lang('NOT_AUTHORISED'));
				}

				$sql_where .= ($category_id != 'all') ? ' AND a.category_id = ' . (int) $category_id : '';

				$sql = 'SELECT a.*, c.*, u.user_id, u.user_colour, u.username
					FROM ' . $this->articles_table . ' a, ' . $this->categories_table . ' c, ' . USERS_TABLE . " u
					WHERE u.user_id = a.article_poster
						AND a.category_id = c.category_id
						$sql_where";
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$this->template->assign_block_vars('article_row', array(
						'ARTICLE_TITLE'			=> $row['article_title'],
						'ARTICLE_DESCRIPTION'	=> $row['article_description'],
						'ARTICLE_POSTER'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
						'ARTICLE_CATEGORY'		=> $row['category_name'],
						'ARTICLE_TIME'			=> $this->user->format_date($row['article_time']),

						'U_VIEW_ARTICLE'	=> $this->helper->route('knowledgebase_main_controller', array('name' => 'viewarticle', 'a' => $row['article_id'])),
					));
				}
				$this->db->sql_freeresult($result);

				$this->template->assign_vars(array(
					'S_TYPE'	=> $type,

					'U_ALL_CATEGORIES'		=> $this->helper->route('knowledgebase_main_controller', array('name' => 'index')),
					'U_POST_NEW_ARTICLE'	=> ($this->auth->acl_get('u_kb_post')) ? $this->helper->route('knowledgebase_main_controller', array('name' => 'posting', 'mode' => 'post')) : '',
				));

				return $this->helper->render('index_body.html', $this->user->lang('KNOWLEDGE_BASE'));
			break;

			case 'mcp':
				$article_id = $this->request->variable('a', 0);
				$mode = $this->request->variable('mode', '');

				$submit = ($this->request->is_set_post('submit')) ? true : false;
				$cancel = ($this->request->is_set_post('cancel')) ? true : false;

				if (!$article_id)
				{
					return $this->helper->error($this->user->lang('NO_ARTICLE'));
				}

				if (!in_array($mode, array('approve', 'delete', 'deny', 'disapprove')))
				{
					return $this->helper->error($this->user->lang('NO_MODE'));
				}

				$sql = 'SELECT *
					FROM ' . $this->articles_table . '
					WHERE article_id = ' . (int) $article_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if (!$row)
				{
					return $this->helper->error($this->user->lang('NO_ARTICLE'));
				}

				if ($cancel)
				{
					$meta_info = $this->helper->route('knowledgebase_main_controller', array('name' => 'viewarticle', 'a' => (int) $article_id));
					redirect($meta_info);
				}

				/*$notes = $this->request->variable('notes', '', true);

				if ($notes === '')
				{
					$notes = 'none';
				}*/

				switch ($mode)
				{
					case 'approve':
						if (!$this->auth->acl_get('m_kb_approve') || $row['article_approved'])
						{
							return $this->helper->error($this->user->lang('NOT_AUTHORISED'));
						}

						if (confirm_box(true))
						{
							$data = array(
								'article_approved' => true,
								'article_denied' => false, // Make sure the article is not denied just to be safe
							);

							$sql = 'UPDATE ' . $this->articles_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . ' WHERE article_id = ' . (int) $article_id;
							$this->db->sql_query($sql);

							$meta_info = $this->helper->route('knowledgebase_main_controller', array('name' => 'viewarticle', 'a' => (int) $article_id));
							meta_refresh(3, $meta_info);
							return $this->helper->error(sprintf($this->user->lang['ARTICLE_STATUS'], strtolower($this->user->lang['APPROVED'])));
						}
						else
						{
							confirm_box(false, sprintf($this->user->lang['ARTICLE_CONFIRM'], strtolower($this->user->lang['APPROVE'])));
						}
					break;

					case 'delete':
						if (!$this->auth->acl_get('m_kb_delete'))
						{
							return $this->helper->error($this->user->lang('NOT_AUTHORISED'));
						}

						if (confirm_box(true))
						{
							$sql = 'DELETE FROM ' . $this->articles_table . '
								WHERE article_id = ' . (int) $article_id;
							$this->db->sql_query($sql);

							$meta_info = $this->helper->route('knowledgebase_main_controller', array('name' => 'index'));
							meta_refresh(3, $meta_info);
							return $this->helper->error(sprintf($this->user->lang['ARTICLE_STATUS'], strtolower($this->user->lang['DELETED'])));
						}
						else
						{
							confirm_box(false, sprintf($this->user->lang['ARTICLE_CONFIRM'], strtolower($this->user->lang['DELETE'])));
						}
					break;

					case 'deny':
						if (!$this->auth->acl_get('m_kb_deny') || $row['article_denied'])
						{
							return $this->helper->error($this->user->lang('NOT_AUTHORISED'));
						}

						if (confirm_box(true))
						{
							$data = array(
								'article_approved' => false, // Set the approval state to false - denied articles are not approved
								'article_denied' => true,
							);

							$sql = 'UPDATE ' . $this->articles_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . ' WHERE article_id = ' . (int) $article_id;
							$this->db->sql_query($sql);

							$meta_info = $this->helper->route('knowledgebase_main_controller', array('name' => 'viewarticle', 'a' => (int) $article_id));
							meta_refresh(3, $meta_info);
							return $this->helper->error(sprintf($this->user->lang['ARTICLE_STATUS'], strtolower($this->user->lang['DENIED'])));
						}
						else
						{
							confirm_box(false, sprintf($this->user->lang['ARTICLE_CONFIRM'], strtolower($this->user->lang['DENY'])));
						}
					break;

					case 'disapprove':
						if (!$this->auth->acl_get('m_kb_disapprove') || (!$row['article_approved'] && !$row['article_denied']))
						{
							return $this->helper->error($this->user->lang('NOT_AUTHORISED'));
						}


						if (confirm_box(true))
						{
							$data = array(
								'article_approved' => false,
								'article_denied' => false,
							);

							$sql = 'UPDATE ' . $this->articles_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . ' WHERE article_id = ' . (int) $article_id;
							$this->db->sql_query($sql);

							$this->send_notification($article_id, $row['article_title']);

							$meta_info = $this->helper->route('knowledgebase_main_controller', array('name' => 'viewarticle', 'a' => (int) $article_id));
							meta_refresh(3, $meta_info);
							return $this->helper->error(sprintf($this->user->lang['ARTICLE_STATUS'], strtolower($this->user->lang['DISAPPROVED'])));
						}
						else
						{
							confirm_box(false, sprintf($this->user->lang['ARTICLE_CONFIRM'], strtolower($this->user->lang['DISAPPROVE'])));
						}
					break;
				}

				return $this->helper->render('mcp_body.html', $this->user->lang('KNOWLEDGE_BASE'));
			break;

			case 'posting':
				$article_id = $this->request->variable('a', 0);
				$mode = $this->request->variable('mode', '');

				$submit = ($this->request->is_set_post('post')) ? true : false;
				$preview = ($this->request->is_set_post('preview')) ? true : false;
				$cancel = ($this->request->is_set_post('cancel')) ? true : false;

				$error = $data = array();
				$current_time = time();

				$this->user->add_lang(array('posting'));

				if ($cancel)
				{
					$meta_info = ($article_id) ? $this->helper->route('knowledgebase_main_controller', array('name' => 'viewarticle', 'a' => (int) $article_id)) : $this->helper->route('knowledgebase_main_controller', array('name' => 'index'));
					redirect($meta_info);
				}

				if (!in_array($mode, array('post', 'edit', 'delete')))
				{
					return $this->helper->error($this->user->lang('NO_POST_MODE'));
				}

				switch ($mode)
				{
					case 'edit':
					case 'delete':
						if (!$article_id)
						{
							return $this->helper->error($this->user->lang('NO_ARTICLE'));
						}

						$sql = 'SELECT a.*, c.*
							FROM ' . $this->articles_table . ' a, ' . $this->categories_table . ' c
							WHERE a.article_id = ' . (int) $article_id . '
								AND a.category_id = c.category_id';
					break;

					default:
						$sql = '';
					break;
				}

				if (!$sql && $mode != 'post')
				{
					return $this->helper->error($this->user->lang('NO_POST_MODE'));
				}

				$result = $this->db->sql_query($sql);
				$data = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if (!$data && $mode != 'post')
				{
					return $this->helper->error($this->user->lang('NO_ARTICLE'));
				}

				switch ($mode)
				{
					case 'post':
						if (!$this->auth->acl_get('u_kb_post'))
						{
							return $this->helper->error($this->user->lang('NOT_AUTHORISED'));
						}
					break;

					case 'edit':
						if ((!$this->auth->acl_get('u_kb_edit') || $this->user->data['user_id'] != $data['article_poster']) && !$this->auth->acl_get('m_kb_edit'))
						{
							return $this->helper->error($this->user->lang('NOT_AUTHORISED'));
						}
					break;

					case 'delete':
						if ((!$this->auth->acl_get('u_kb_delete') || $this->user->data['user_id'] != $data['article_poster']) && !$this->auth->acl_get('m_kb_delete'))
						{
							return $this->helper->error($this->user->lang('NOT_AUTHORISED'));
						}
					break;
				}

				if ($mode == 'delete')
				{
					if (confirm_box(true))
					{
						$sql = 'DELETE FROM ' . $this->articles_table . '
							WHERE article_id = ' . (int) $article_id;
						$this->db->sql_query($sql);

						$meta_info = $this->helper->route('knowledgebase_main_controller', array('name' => 'index'));
						meta_refresh(3, $meta_info);
						return $this->helper->error(sprintf($this->user->lang['ARTICLE_STATUS'], strtolower($this->user->lang['DELETED'])));
					}
					else
					{
						confirm_box(false, sprintf($this->user->lang['ARTICLE_CONFIRM'], strtolower($this->user->lang['DELETE'])));
					}
				}

				include ($this->root_path . 'includes/functions_posting.' . $this->php_ext);
				include ($this->root_path . 'includes/functions_display.' . $this->php_ext);

				display_custom_bbcodes();
				generate_smilies('inline', 0);

				$uid = $bitfield = $options = '';
				$allow_bbcode = $allow_urls = $allow_smilies = true;

				if ($mode == 'edit')
				{
					decode_message($data['message'], $data['bbcode_uid']);

					$title = $data['article_title'];
					$description = $data['article_description'];
					$category_id = $data['category_id'];
					$text = $data['message'];
				}

				if ($mode == 'post' || $submit)
				{
					$title = $this->request->variable('article_title', '', true);
					$description = $this->request->variable('article_description', '', true);
					$category_id = $this->request->variable('category_id', 0);
					$text = $this->request->variable('message', '', true);
				}

				if ($submit)
				{
					if (utf8_clean_string($title) === '')
					{
						$error[] .= $this->user->lang['EMPTY_TITLE'];
					}

					if (utf8_clean_string($description) === '')
					{
						$error[] .= $this->user->lang['EMPTY_DESCRIPTION'];
					}

					if (!$category_id)
					{
						$error[] .= $this->user->lang['EMPTY_CATEGORY_ID'];
					}

					if (utf8_clean_string($text) === '')
					{
						$error[] .= $this->user->lang['EMPTY_TEXT'];
					}

					if (!sizeof($error))
					{
						generate_text_for_storage($text, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);

						$sql_array = array(
							'category_id'			=> (int) $category_id,
							'article_title'			=> $title,
							'article_description'	=> $description,
							'article_poster'		=> ($mode == 'edit') ? $data['article_poster'] : $this->user->data['user_id'],
							'article_time'			=> ($mode == 'edit') ? $data['article_time'] : $current_time,
							'article_approved'		=> 0,
							'article_denied'		=> 0,
							'enable_bbcode'			=> $allow_bbcode,
							'enable_smilies'		=> $allow_smilies,
							'enable_magic_url'		=> $allow_urls,
							'message'				=> $text,
							'bbcode_bitfield'		=> $bitfield,
							'bbcode_uid'			=> $uid,
						);

						if ($mode == 'post')
						{
							$sql = 'INSERT INTO ' . $this->articles_table . ' ' . $this->db->sql_build_array('INSERT', $sql_array);
						}
						else
						{
							$sql = 'UPDATE ' . $this->articles_table . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE article_id = ' . (int) $data['article_id'];
						}
						$this->db->sql_query($sql);

						$article_id = (isset($data['article_id'])) ? $data['article_id'] : $this->db->sql_nextid();

						$this->send_notification($article_id, $title);

						if (!$this->auth->acl_get('m_kb_approve') && !$this->auth->acl_get('m_kb_disapprove') && !$this->auth->acl_get('m_kb_deny'))
						{
							$meta_info = $this->helper->route('knowledgebase_main_controller', array('name' => 'index'));
						}
						else
						{
							$meta_info = $this->helper->route('knowledgebase_main_controller', array('name' => 'viewarticle', 'a' => (int) $article_id));
						}
						meta_refresh(3, $meta_info);

						return $this->helper->error($this->user->lang['ARTICLE_POSTED_MOD']);
					}
				}

				$sql = 'SELECT *
					FROM ' . $this->categories_table . '
					ORDER BY left_id ASC';
				$result = $this->db->sql_query($sql);
				$category_id_array = array();
				while ($row = $this->db->sql_fetchrow($result))
				{
					$category_id_array[] = $row['category_id'];

					$this->template->assign_block_vars('categories', array(
						'CATEGORY_ID'	=> $row['category_id'],
						'CATEGORY_NAME'	=> $row['category_name'],
						'SELECTED'		=> ($row['category_id'] == $category_id) ? ' selected="selected"' : '',
					));
				}
				$this->db->sql_freeresult($result);

				$this->template->assign_vars(array(
					'ARTICLE_TITLE'			=> $title,
					'ARTICLE_DESCRIPTION'	=> $description,
					'CATEGORY_ID'			=> $category_id,
					'MESSAGE'				=> $text,

					'ERROR'	=> (sizeof($error)) ? implode('<br />', $error) : '',

					'S_BBCODE_ALLOWED'	=> $allow_bbcode,
					'S_LINKS_ALLOWED'	=> $allow_urls,
					'S_BBCODE_IMG'		=> true,
					'S_BBCODE_QUOTE'	=> true,

					'U_MORE_SMILIES'	=> append_sid("{$this->root_path}posting.$this->php_ext", 'mode=smilies'),
				));

				return $this->helper->render('posting_body.html', $this->user->lang('KNOWLEDGE_BASE') . ' - ' . $this->user->lang('POST_ARTICLE'));
			break;

			case 'viewarticle':
				$article_id = $this->request->variable('a', 0);

				if (!$article_id)
				{
					return $this->helper->error($this->user->lang('NO_ARTICLE'));
				}

				$sql_where = '';
				$sql_where = (!$this->auth->acl_get('m_kb_approve') && !$this->auth->acl_get('m_kb_disapprove') && !$this->auth->acl_get('m_kb_deny')) ? ' AND article_approved = 1' : '';

				$sql = 'SELECT a.*, c.*, u.user_id, u.user_colour, u.username
					FROM ' . $this->articles_table . ' a, ' . $this->categories_table . ' c, ' . USERS_TABLE . ' u
					WHERE u.user_id = a.article_poster
						AND a.article_id = ' . (int) $article_id . "
						AND a.category_id = c.category_id
						$sql_where";
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if (!$row)
				{
					return $this->helper->error($this->user->lang('NO_ARTICLE'));
				}

				$row['bbcode_options'] = (($row['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) +
					(($row['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) +
					(($row['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
				$text = generate_text_for_display($row['message'], $row['bbcode_uid'], $row['bbcode_bitfield'], $row['bbcode_options']);

				$board_url = generate_board_url();

				$this->template->assign_vars(array(
					'ARTICLE_TITLE'			=> $row['article_title'],
					'ARTICLE_DESCRIPTION'	=> $row['article_description'],
					'ARTICLE_POSTER'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
					'ARTICLE_TIME'			=> $this->user->format_date($row['article_time']),
					'MESSAGE'				=> $text,

					'U_APPROVE'			=> $this->auth->acl_get('m_kb_approve') ? ($row['article_approved'] ? '' : $this->helper->route('knowledgebase_main_controller', array('name' => 'mcp', 'mode' => 'approve', 'a' => $row['article_id']))) : '',
					'U_DELETE'			=> ($this->auth->acl_get('u_kb_delete') && $this->user->data['user_id'] == $row['article_poster']) ? $this->helper->route('knowledgebase_main_controller', array('name' => 'posting', 'mode' => 'delete', 'a' => $row['article_id'])) : ($this->auth->acl_get('m_kb_delete') ? $this->helper->route('knowledgebase_main_controller', array('name' => 'mcp', 'mode' => 'delete', 'a' => $row['article_id'])) : ''),
					'U_DENY'			=> $this->auth->acl_get('m_kb_deny') ? ($row['article_denied'] ? '' : $this->helper->route('knowledgebase_main_controller', array('name' => 'mcp', 'mode' => 'deny', 'a' => $row['article_id']))) : '',
					'U_DISAPPROVE'		=> $this->auth->acl_get('m_kb_disapprove') ? ($row['article_approved'] || $row['article_denied'] ? $this->helper->route('knowledgebase_main_controller', array('name' => 'mcp', 'mode' => 'disapprove', 'a' => $row['article_id'])) : '') : '',
					'U_EDIT'			=> ($this->auth->acl_get('m_kb_edit') || ($this->auth->acl_get('u_kb_edit') && $this->user->data['user_id'] == $row['article_poster'])) ? $this->helper->route('knowledgebase_main_controller', array('name' => 'posting', 'mode' => 'edit', 'a' => $row['article_id'])) : '',
					'U_VIEW_ARTICLE'	=> ($this->config['enable_mod_rewrite']) ? append_sid($board_url . "/kb/viewarticle", 'a=' . $row['article_id']) : append_sid($board_url . "/app.php/kb/viewarticle", 'a=' . $row['article_id']),
				));

				return $this->helper->render('viewarticle_body.html', $this->user->lang('KNOWLEDGE_BASE') . ' - ' . $row['article_title']);
			break;

			default:
				return $this->helper->error($this->user->lang('INVALID_MODE'));
			break;
		}
	}

	/**
	* Send notification to users
	*
	* @return null
	* @access public
	*/
	public function send_notification($article_id, $article_title)
	{
		// Store the notification data we will use in an array
		$notification_data = array(
			'article_id' => $article_id,
			'article_title' => $article_title
		);

		$phpbb_notifications = $this->phpbb_container->get('notification_manager');

		/**
		* Delete the old notification before adding a new one. This fixes a bug where a new notification
		* wasn't created when disapproving an article that was already disapproved once. See
		* https://www.phpbb.com/community/viewtopic.php?f=461&t=2249811 for more information.
		*/
		$phpbb_notifications->delete_notifications('article_in_queue', $notification_data);

		// Create the notification
		$phpbb_notifications->add_notifications('article_in_queue', $notification_data);
	}
}
