<?php
/**
*
* common [English]
*
* Knowledge Base extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'ALL_CATEGORIES'		=> 'All categories',
	'APPROVE'				=> 'Approve',
	'APPROVED'				=> 'Approved',
	'ARTICLE_CONFIRM'		=> 'Are you sure you want to %1$s this article?',
	'ARTICLE_POSTED'		=> 'This article has been posted successfully.',
	'ARTICLE_POSTED_MOD'	=> 'This article has been posted successfully, but it will need to be approved by a moderator before it is publicly viewable.',
	'ARTICLE_STATUS'		=> 'The requested article has been %1$s.',
	'ARTICLES'				=> 'Articles',

	'BUTTON_NEW_ARTICLE'	=> 'New Article',

	'CATEGORY'			=> 'Category',
	'CATEGORY_CREATED'	=> 'Category created successfully.',
	'CATEGORY_DELETED'	=> 'Category successfully deleted.',
	'CATEGORY_UPDATED'	=> 'Category information updated successfully.',
	'CREATE_CATEGORY'	=> 'Create new category',

	'DELETE_CATEGORY'	=> 'Delete Category',
	'DELETED'			=> 'Deleted',
	'DENIED'			=> 'Denied',
	'DENY'				=> 'Deny',
	'DESCRIPTION'		=> 'Description',
	'DISAPPROVE'		=> 'Disapprove',
	'DISAPPROVED'		=> 'Disapproved',

	'EDIT'				=> 'Edit',
	'EDIT_CATEGORY'		=> 'Edit Category',
	'EMPTY_CATEGORY_ID'	=> 'Please select a category for this article',
	'EMPTY_DESCRIPTION'	=> 'You must specify a description when posting a new article',
	'EMPTY_TEXT'		=> 'The article\'s text cannot be empty',
	'EMPTY_TITLE'		=> 'You must specify a title when posting a new article',

	'INVALID_MODE'	=> 'Invalid Knowledge Base mode specified.',

	'KNOWLEDGE_BASE'			=> 'Knowledge Base',
	'KNOWLEDGE_BASE_EXPLAIN'	=> 'This section contains detailed articles elaborating on some of the common issues users encounter. Articles submitted by members of the community are checked for accuracy. If you do not find the answer to your question here, we recommend looking through the forums as well as using the search feature.',
	'KNOWLEDGE_BASE_MANAGE'		=> 'Manage Knowledge Base Categories',

	'LINK_TO_ARTICLE'		=> 'Link to this article',
	'LOG_CATEGORY_ADD'		=> '<strong>Created new category</strong><br />» %s',
	'LOG_CATEGORY_DELETE'	=> '<strong>Deleted category</strong><br />» %s',
	'LOG_CATEGORY_EDIT'		=> '<strong>Edited category details</strong><br />» %s',
	'LOG_CATEGORY_DOWN'		=> '<strong>Moved category</strong> %1$s <strong>below</strong> %2$s',
	'LOG_CATEGORY_UP'		=> '<strong>Moved category</strong> %1$s <strong>above</strong> %2$s',

	'MANAGE_CATEGORIES'	=> 'Manage Categories',

	'NOTIFICATION_ARTICLE_IN_QUEUE'	=> '<strong>Article approval</strong> request: %1$s',
	'NO_ARTICLE'					=> 'The requested article does not exist.',
	'NO_ARTICLES'					=> 'There are no articles in this category.',
	'NO_CATEGORIES'					=> 'No categories',
	'NO_CATEGORY'					=> 'The requested category does not exist.',

	'POST_ARTICLE'	=> 'Post a new article',

	'SELECT_CATEGORY'	=> 'Please select a category',

	'TITLE'	=> 'Title',
	'TYPE'	=> 'Type',

	'VIEWONLINE'	=> 'Viewing Knowledge Base',
));

$lang = array_merge($lang, array(
	'ACL_U_KB_DELETE'	=> 'Can delete own articles',
	'ACL_U_KB_EDIT'		=> 'Can edit own articles',
	'ACL_U_KB_POST'		=> 'Can post new articles',

	'ACL_M_KB_APPROVE'		=> 'Can approve articles',
	'ACL_M_KB_DELETE'		=> 'Can delete articles',
	'ACL_M_KB_DENY'			=> 'Can deny articles',
	'ACL_M_KB_DISAPPROVE'	=> 'Can disapprove articles',
	'ACL_M_KB_EDIT'			=> 'Can edit articles',

	'ACL_A_KB_MANAGE'	=> 'Can manage Knowledge Base',
));
