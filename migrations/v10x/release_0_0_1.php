<?php
/**
*
* Knowledge Base extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace tmbackoff\knowledgebase\migrations\v10x;

class release_0_0_1 extends \phpbb\db\migration\migration
{
	/**
	* Add or update schema in the database
	*
	* @return array Array of table schema
	* @access public
	*/
	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'categories'	=> array(
					'COLUMNS'	=> array(
						'category_id'		=> array('UINT', null, 'auto_increment'),
						'left_id'			=> array('UINT', 0),
						'right_id'			=> array('UINT', 0),
						'category_name'		=> array('VCHAR:200', ''),
					),
					'PRIMARY_KEY'	=> 'category_id',
				),

				$this->table_prefix . 'articles'	=> array(
					'COLUMNS'	=> array(
						'article_id'				=> array('UINT', null, 'auto_increment'),
						'category_id'				=> array('UINT', 0),
						'article_title'				=> array('VCHAR:200', ''),
						'article_description'		=> array('VCHAR:200', ''),
						'article_poster'			=> array('UINT', 0),
						'article_time'				=> array('TIMESTAMP', 0),
						'article_approved'			=> array('BOOL', 0),
						'article_denied'			=> array('BOOL', 0),
						'enable_bbcode'				=> array('BOOL', 1),
						'enable_smilies'			=> array('BOOL', 1),
						'enable_magic_url'			=> array('BOOL', 1),
						'message'					=> array('MTEXT_UNI', ''),
						'bbcode_bitfield'			=> array('VCHAR:255', ''),
						'bbcode_uid'				=> array('VCHAR:8', ''),
					),
					'PRIMARY_KEY'	=> 'article_id',
				),
			),
		);
	}

	/**
	* Add or update data in the database
	*
	* @return array Array of table data
	* @access public
	*/
	public function update_data()
	{
		return array(
			// Add permission
			array('permission.add', array('a_kb_manage', true)),

			array('permission.add', array('m_kb_approve', true)),
			array('permission.add', array('m_kb_delete', true)),
			array('permission.add', array('m_kb_deny', true)),
			array('permission.add', array('m_kb_disapprove', true)),
			array('permission.add', array('m_kb_edit', true)),

			array('permission.add', array('u_kb_delete', true)),
			array('permission.add', array('u_kb_edit', true)),
			array('permission.add', array('u_kb_post', true)),
			array('permission.add', array('u_kb_read', true)),

			// Set permissions
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_kb_manage')),
			array('permission.permission_set', array('ROLE_ADMIN_STANDARD', 'a_kb_manage')),

			array('permission.permission_set', array('ROLE_MOD_FULL', 'm_kb_approve')),
			array('permission.permission_set', array('ROLE_MOD_STANDARD', 'm_kb_approve')),
			array('permission.permission_set', array('ROLE_MOD_FULL', 'm_kb_delete')),
			array('permission.permission_set', array('ROLE_MOD_STANDARD', 'm_kb_delete')),
			array('permission.permission_set', array('ROLE_MOD_FULL', 'm_kb_deny')),
			array('permission.permission_set', array('ROLE_MOD_STANDARD', 'm_kb_deny')),
			array('permission.permission_set', array('ROLE_MOD_FULL', 'm_kb_disapprove')),
			array('permission.permission_set', array('ROLE_MOD_STANDARD', 'm_kb_disapprove')),
			array('permission.permission_set', array('ROLE_MOD_FULL', 'm_kb_edit')),
			array('permission.permission_set', array('ROLE_MOD_STANDARD', 'm_kb_edit')),

			array('permission.permission_set', array('ROLE_USER_FULL', 'u_kb_delete')),
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_kb_delete')),
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_kb_edit')),
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_kb_edit')),
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_kb_post')),
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_kb_post')),
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_kb_read')),
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_kb_read')),

			// Add example categories and articles
			array('custom', array(array($this, 'insert_sample_data'))),

			// Add the ACP module
			array('module.add', array('acp', 'ACP_CAT_DOT_MODS', 'KNOWLEDGE_BASE')),
			array('module.add', array(
				'acp', 'KNOWLEDGE_BASE', array(
					'module_basename'	=> '\tmbackoff\knowledgebase\acp\knowledgebase_module',
					'modes'				=> array('manage'),
				),
			)),
		);
	}

	/**
	* Custom function to add sample data to the tables
	*
	* @return null
	* @access public
	*/
	public function insert_sample_data()
	{
		// Define sample data
		$sample_data_categories = array(
			array(
				'category_id'		=> 1,
				'left_id'			=> 1,
				'right_id'			=> 2,
				'category_name'		=> 'Example Category 1',
			),
			array(
				'category_id'		=> 2,
				'left_id'			=> 3,
				'right_id'			=> 4,
				'category_name'		=> 'Example Category 2',
			),
		);

		$sample_data_articles = array(
			array(
				'article_id'				=> 1,
				'category_id'				=> 1,
				'article_title'				=> 'Test Article #1',
				'article_description'		=> 'This is a test article description',
				'article_poster'			=> 2,
				'article_time'				=> time(),
				'article_approved'			=> 1,
				'article_denied'			=> 0,
				'enable_bbcode'				=> 1,
				'enable_smilies'			=> 1,
				'enable_magic_url'			=> 1,
				'message'					=> 'This is a [b:u4rxox2x][u:u4rxox2x]test[/u:u4rxox2x][/b:u4rxox2x] article.',
				'bbcode_bitfield'			=> 'QQ==',
				'bbcode_uid'				=> 'u4rxox2x',
			),
		);

		// Insert sample data
		$this->db->sql_multi_insert($this->table_prefix . 'categories', $sample_data_categories);
		$this->db->sql_multi_insert($this->table_prefix . 'articles', $sample_data_articles);
	}

	/**
	* Drop the schema from the database
	*
	* @return array Array of table schema
	* @access public
	*/
	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'categories',
				$this->table_prefix . 'articles',
			),
		);
	}
}
