<?php
/**
*
* @package phpBB Extension - Smartfeed
* @copyright (c) 2020 Mark D. Hamill (mark@phpbbservices.com)
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
	'SMARTFEED_ATOM_FEED'					=> 'Atom feed',
	'SMARTFEED_ATOM_TOPIC_FEED'				=> 'Atom topic feed',
	'SMARTFEED_FEED_TYPE'					=> 'Newsfeed format',
	'SMARTFEED_FIRST_POST_ONLY'				=> 'Types of posts in feed',
	'SMARTFEED_INSTALL_REQUIREMENTS'		=> 'The following PHP extensions are required: xml, pcre and openssl. Your version of PHP must be &gt; 3.3.0 and &lt; 4.0. One or more of these requirements are missing. Please address these issues, then try enabling the extension again.',
	'SMARTFEED_NO_FORUMS_AVAILABLE' 		=> 'Sorry, due to your user status you cannot access any forums',
	'SMARTFEED_PAGE'						=> 'Smartfeed',
	'SMARTFEED_RSS_FEED'					=> 'RSS feed',
	'SMARTFEED_RSS_TOPIC_FEED'				=> 'RSS topic feed',
));
