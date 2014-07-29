<?php
/**
*
* Knowledge Base extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace tmbackoff\knowledgebase\acp;

class knowledgebase_info
{
	function module()
	{
		return array(
			'filename'	=> '\tmbackoff\knowledgebase\acp\knowledgebase_module',
			'title'		=> 'KNOWLEDGE_BASE',
			'modes'		=> array(
				'manage'	=> array('title' => 'KNOWLEDGE_BASE_MANAGE', 'auth' => 'ext_tmbackoff/knowledgebase && acl_a_kb_manage', 'cat' => array('KNOWLEDGE_BASE')),
			),
		);
	}
}