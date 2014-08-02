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

class admin_controller
{
	/* @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\service */
	protected $cache;

	/* @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\log\log */
	protected $log;

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

	/** string Custom form action */
	protected $u_action;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth                     $auth             Auth object
	* @param \phpbb\cache\service                 $cache            Cache object
	* @param \phpbb\config\config                 $config           Config object
	* @param \phpbb\db\driver\driver_interface    $db               Database object
	* @param \phpbb\controller\helper             $helper           Helper object
	* @param \phpbb\log\log                       $log              Log object
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
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\log\log $log, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, Container $phpbb_container, $root_path, $php_ext, $categories_table, $articles_table)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->log = $log;
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
	* Display the categories
	*
	* @return null
	* @access public
	*/
	public function display_categories()
	{
		$sql = 'SELECT *
			FROM ' . $this->categories_table . '
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('categories', array(
				'CATEGORY_ID'		=> $row['category_id'],
				'CATEGORY_NAME'		=> $row['category_name'],

				'U_MOVE_UP'			=> $this->u_action . '&amp;action=move_up&amp;c=' . $row['category_id'],
				'U_MOVE_DOWN'		=> $this->u_action . '&amp;action=move_down&amp;c=' . $row['category_id'],
				'U_EDIT'			=> $this->u_action . '&amp;action=edit&amp;c=' . $row['category_id'],
				'U_DELETE'			=> $this->u_action . '&amp;action=delete&amp;c=' . $row['category_id'],
			));
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars(array(
			'U_ADD_CATEGORY'	=>$this->u_action . '&amp;action=add',
		));
	}

	/**
	* Add a category
	*
	* @return null
	* @access public
	*/
	public function add_category()
	{
		if ($this->request->is_set_post('submit'))
		{
			$sql = 'SELECT MAX(right_id) AS right_id
				FROM ' . $this->categories_table;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$category_data = array(
				'category_name'	=> $this->request->variable('category_name', '', true),
				'left_id'		=> $row['right_id'] + 1,
				'right_id'		=> $row['right_id'] + 2,
			);

			$sql = 'INSERT INTO ' . $this->categories_table . ' ' . $this->db->sql_build_array('INSERT', $category_data);
			$this->db->sql_query($sql);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->data['user_ip'], 'LOG_CATEGORY_ADD', time(), array($category_data['category_name']));

			trigger_error($this->user->lang['CATEGORY_CREATED'] . adm_back_link($this->u_action));
		}
	}

	/**
	* Edit a category
	*
	* @param int $category_id The category identifier to edit
	*
	* @return null
	* @access public
	*/
	public function edit_category($category_id)
	{
		if (!$category_id)
		{
			trigger_error($this->user->lang['NO_CATEGORY'] . adm_back_link($this->u_action));
		}

		$sql = 'SELECT *
			FROM ' . $this->categories_table . '
			WHERE category_id = ' . (int) $category_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			trigger_error("Category #$category_id does not exist", E_USER_ERROR);
		}

		if ($this->request->is_set_post('submit'))
		{
			$new_category_name = $this->request->variable('category_name', '', true);
			$sql = 'UPDATE ' . $this->categories_table . '
				SET category_name = "' . $new_category_name . '"
				WHERE category_id = ' . (int) $category_id;
			$this->db->sql_query($sql);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->data['user_ip'], 'LOG_CATEGORY_EDIT', time(), array($row['category_name'], $new_category_name));

			trigger_error($this->user->lang['CATEGORY_UPDATED'] . adm_back_link($this->u_action));
		}

		$this->template->assign_vars(array(
			'CATEGORY_NAME'	=> $row['category_name'],

			'S_CATEGORY_EDIT'	=> true,

			'U_EDIT_CATEGORY'	=> $this->u_action . '&amp;action=edit&amp;c=' . (int) $category_id,
		));
	}

	/**
	* Delete a category
	*
	* @param int $category_id The category identifier to delete
	*
	* @return null
	* @access public
	*/
	public function delete_category($category_id)
	{
		if (!$category_id)
		{
			trigger_error($this->user->lang['NO_CATEGORY'] . adm_back_link($this->u_action));
		}

		$sql = 'SELECT COUNT(article_id) as articles
			FROM ' . $this->articles_table . '
			WHERE category_id = ' . (int) $category_id;
		$this->db->sql_query($sql);
		$articles = $this->db->sql_fetchfield('articles');

		$sql = 'SELECT *
			FROM ' . $this->categories_table . '
			WHERE category_id = ' . (int) $category_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			trigger_error("Category #$category_id does not exist", E_USER_ERROR);
		}

		if ($articles == 0)
		{
			if (confirm_box(true))
			{
				$category_name = $row['category_name'];

				$sql = 'DELETE FROM ' . $this->categories_table . '
					WHERE category_id = ' . (int) $category_id;
				$this->db->sql_query($sql);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->data['user_ip'], 'LOG_CATEGORY_DELETE', time(), array($category_name));

				trigger_error($this->user->lang['CATEGORY_DELETED'] . adm_back_link($this->u_action));
			}
			else
			{
				confirm_box(false, sprintf($this->user->lang['CATEGORY_CONFIRM'], strtolower($this->user->lang['DELETE'])));
			}
		}
		else
		{
			$delete_articles = $this->request->variable('delete_articles', 0);
			$move_to_category = $this->request->variable('move_to_category', 0);

			if ($this->request->is_set_post('submit'))
			{
				$category_name = $row['category_name'];

				if ($delete_articles || !$move_to_category)
				{
					$sql = 'SELECT article_id
						FROM ' . $this->articles_table . '
						WHERE category_id = ' . (int) $category_id;
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$sql = 'DELETE FROM ' . $this->articles_table . '
							WHERE article_id = ' . $row['article_id'];
						$this->db->sql_query($sql);
					}

					$sql = 'DELETE FROM ' . $this->categories_table . '
						WHERE category_id = ' . (int) $category_id;
					$this->db->sql_query($sql);
				}
				else if ($move_to_category)
				{
					$sql = 'UPDATE  ' . $this->articles_table . '
						SET category_id = ' . $move_to_category . '
						WHERE category_id = ' . (int) $category_id;
					$this->db->sql_query($sql);

					$sql = 'DELETE FROM ' . $this->categories_table . '
						WHERE category_id = ' . (int) $category_id;
					$this->db->sql_query($sql);
				}

				$this->log->add('admin', $this->user->data['user_id'], $this->user->data['user_ip'], 'LOG_CATEGORY_DELETE', time(), array($category_name));

				trigger_error($this->user->lang['CATEGORY_DELETED'] . adm_back_link($this->u_action));
			}

			$this->template->assign_vars(array(
				'DELETE_CATEGORY_ID'	=> (int) $category_id,

				'S_CATEGORY_DELETE'	=> true,

				'U_DELETE_CATEGORY'	=> $this->u_action . '&amp;action=delete&amp;c=' . (int) $category_id,
			));
		}
	}

	/**
	* Move a category up/down
	*
	* @param int $category_id The category identifier to move
	* @param string $direction The direction (up|down)
	* @param int $amount The number of places to move the category
	*
	* @return null
	* @access public
	*/
	public function move_category($category_id, $direction, $amount = 1)
	{
		if (!$category_id)
		{
			trigger_error($this->user->lang['NO_CATEGORY'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$sql = 'SELECT *
			FROM ' . $this->categories_table . '
			WHERE category_id = ' . (int) $category_id;
		$result = $this->db->sql_query($sql);
		$category_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$category_row)
		{
			trigger_error($this->user->lang['NO_CATEGORY'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$sql = 'SELECT *
			FROM ' . $this->categories_table . '
			WHERE ' . (($direction == 'up') ? "right_id < {$category_row['right_id']} ORDER BY right_id DESC" : "left_id > {$category_row['left_id']} ORDER BY left_id ASC");
		$result = $this->db->sql_query_limit($sql, 1);

		$target = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$target = $row;
		}
		$this->db->sql_freeresult($result);

		if (!sizeof($target))
		{
			// The category is already on top or bottom
			return false;
		}

		/**
		* $left_id and $right_id define the scope of the nodes that are affected by the move.
		* $diff_up and $diff_down are the values to substract or add to each node's left_id
		* and right_id in order to move them up or down.
		* $move_up_left and $move_up_right define the scope of the nodes that are moving
		* up. Other nodes in the scope of ($left_id, $right_id) are considered to move down.
		*/
		if ($direction == 'up')
		{
			$left_id = $target['left_id'];
			$right_id = $category_row['right_id'];

			$diff_up = $category_row['left_id'] - $target['left_id'];
			$diff_down = $category_row['right_id'] + 1 - $category_row['left_id'];

			$move_up_left = $category_row['left_id'];
			$move_up_right = $category_row['right_id'];
		}
		else
		{
			$left_id = $category_row['left_id'];
			$right_id = $target['right_id'];

			$diff_up = $category_row['right_id'] + 1 - $category_row['left_id'];
			$diff_down = $target['right_id'] - $category_row['right_id'];

			$move_up_left = $category_row['right_id'] + 1;
			$move_up_right = $target['right_id'];
		}

		// Now do the dirty job
		$sql = 'UPDATE ' . $this->categories_table . "
			SET left_id = left_id + CASE
				WHEN left_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END,
			right_id = right_id + CASE
				WHEN right_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END
			WHERE
				left_id BETWEEN {$left_id} AND {$right_id}
				AND right_id BETWEEN {$left_id} AND {$right_id}";
		$this->db->sql_query($sql);

		if ($target['category_name'] !== false)
		{
			$this->log->add('admin', $this->user->data['user_id'], $this->user->data['user_ip'], 'LOG_CATEGORY_' . strtoupper($direction), time(), array($category_row['category_name'], $target['category_name']));
			$this->cache->destroy('sql', $this->categories_table);
		}
	}

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	*
	* @return null
	* @access public
	*/
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}