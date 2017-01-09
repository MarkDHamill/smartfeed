<?php
/**
*
* @package phpBB Extension - Smartfeed
* @copyright (c) 2017 Mark D. Hamill (mark@phpbbservices.com)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'SMARTFEED_FEED_TYPE'								=> 'Newsfeed format',
	'SMARTFEED_FIRST_POST_ONLY'							=> 'Types of posts in feed',
	'SMARTFEED_NO_FORUMS_AVAILABLE' 					=> 'Sorry, due to your user status you cannot access any forums',
	'SMARTFEED_PAGE'									=> 'Smartfeed',
));
