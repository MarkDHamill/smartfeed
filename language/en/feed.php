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
	'SMARTFEED_APACHE_AUTHENTICATION_WARNING_REG'		=> 'Smartfeed cannot be used on this board. Contact the Forum Administrator for more information.',
	'SMARTFEED_ATOM_10'									=> 'Atom 1.0',
	'SMARTFEED_BAD_BOOKMARKS_VALUE'						=> 'The bookmarks (b) parameter, if used, may only have a value of 1. Please rerun Smartfeed on the forum to generate a correct feed URL.',
	'SMARTFEED_BAD_MARK_PRIVATE_MESSAGES_READ_ERROR'	=> 'If present, the mark private messages read (k) parameter must have a value of 1. Please rerun Smartfeed on the forum to generate a correct feed URL.',
	'SMARTFEED_BAD_PASSWORD_ERROR'						=> 'Authentication failure. &ldquo;e&rdquo; parameter &ldquo;%s&rdquo; is invalid with &ldquo;u&rdquo; parameter of &ldquo;%s&rdquo;. This error may be caused by changing your phpBB password, or due to an upgrade in this Smartfeed software. Please rerun Smartfeed on the forum to generate a correct feed URL.',
	'SMARTFEED_BAD_PMS_VALUE'							=> 'If present, the show private messages (m) parameter must have a value of 1. Please rerun Smartfeed on the forum to generate a correct feed URL.',
	'SMARTFEED_BOARD_DISABLED'							=> 'This board is currently offline. Consequently, the newsfeed functionality has been disabled. When the board goes back online, you will be able to retrieve newsfeeds again.',
	'SMARTFEED_DELIMITER'								=> ' :: ', // Used to break up forum names, topic name and post subjects that all appear together in the feed, such as in the item title
	'SMARTFEED_EXTERNAL_ITEM'							=> 'External item',
	'SMARTFEED_ERROR'									=> 'Smartfeed error',
	'SMARTFEED_FEED'									=> 'Smartfeed newsfeed',
	'SMARTFEED_FILTER_FOES_ERROR'						=> 'The filter foes (ff) parameter value is invalid. If present it should only have a value of 1. Please rerun Smartfeed on the forum to generate a correct feed URL.',
	'SMARTFEED_GLOBAL_ANNOUNCEMENT'						=> 'GLOBAL ANNOUNCEMENT',
	'SMARTFEED_IP_AUTH_ERROR'							=> 'The Internet Protocol (IP) address of the client making the Smartfeed request is not authorized to access the feed because it did not pass the proper credential. Please rerun Smartfeed on the forum to generate a correct feed URL.', 
	'SMARTFEED_IP_RANGE_ERROR'							=> 'Your IP of %s is invalid.',
	'SMARTFEED_LASTVISIT_ERROR'							=> 'The last visit (l) parameter value specified is invalid. If present it must have a value of 1 only. Please rerun Smartfeed on the forum to generate a correct feed URL.',
	'SMARTFEED_LIMIT_FORMAT_ERROR'						=> 'The time limit (t) parameter is not an allowed value. Please rerun Smartfeed on the forum to generate a correct feed URL.',
	'SMARTFEED_MAX_ITEMS_ERROR'							=> 'If specified, the maximum items (x) parameter value must be a whole number greater than 0. Please rerun Smartfeed on the forum to generate a correct feed URL.',
	'SMARTFEED_MAX_WORD_SIZE_ERROR'						=> 'The maximum words in a post (w) parameter value is invalid. If used it must be a whole number. Please rerun Smartfeed on the forum to generate a correct feed URL.',
	'SMARTFEED_MAX_WORDS_NOTIFIER'						=> ' ...',
	'SMARTFEED_MIN_WORD_SIZE_ERROR'						=> 'The minimum word size (i) parameter value is invalid. It should must be a whole number. Please rerun Smartfeed on the forum to generate a correct feed URL.',
	'SMARTFEED_NEW_PMS_NOTIFICATIONS_SHORT'				=> 'You have new private messages',
	'SMARTFEED_NEW_POST_NOTIFICATION'					=> 'There are new posts in this topic. Please login to the forum to read them.',
	'SMARTFEED_NEW_TOPIC_NOTIFICATION'					=> 'This is a new topic. Please login to the forum to read it.',
	'SMARTFEED_NO_ACCESSIBLE_FORUMS'					=> 'You have no permission to access any forums on this site. If you are registered on this site, you may need the administrator to give you privileges to read forums or reinstate you as an active member.',
	'SMARTFEED_NO_BOOKMARKS'							=> 'You have no bookmarked topics but you requested to show bookmarked topics only. Consequently, there are no posts in the feed. If you wish to use bookmarks with Smartfeed, please visit the forum and bookmark one or more topics.',
	'SMARTFEED_NO_FORUMS_ACCESSIBLE' 					=> 'Sorry, due to Smartfeed forum exclusions and your forum access privileges, you cannot access any forums',
	'SMARTFEED_NO_E_ARGUMENT'							=> 'To authenticate a member, the &ldquo;e&rdquo; parameter must be used with the &ldquo;u&rdquo; parameter. The &ldquo;u&rdquo; parameter is present but the &ldquo;e&rdquo; parameter is not. Please rerun Smartfeed on the forum to generate a correct feed URL.',
	'SMARTFEED_NO_ERRORS' 								=> 'No errors.',
	'SMARTFEED_NO_OPENSSL_MODULE'						=> 'Smartfeed cannot support user authentication because the forum does not support the PHP openssl module. Please rerun Smartfeed on the forum while logged out to acquire a valid URI for Smartfeed.',
	'SMARTFEED_NO_U_ARGUMENT'							=> 'To authenticate a member, the &ldquo;u&rdquo; parameter must be used with the &ldquo;e&rdquo; parameter. The &ldquo;e&rdquo; parameter is present but the &ldquo;u&rdquo; parameter is not. Please rerun Smartfeed on the forum to generate a correct feed URL.',
	'SMARTFEED_POST_IMAGE_TEXT'							=> '<br>(Click on the image to see it full size.)',
	'SMARTFEED_POST_SIGNATURE_DELIMITER'				=> '<br>____________________<br>', // Place here whatever code (make sure it is valid HTML) you want to use to distinguish the end of a post from the beginning of the signature line
	'SMARTFEED_REMOVE_MINE_ERROR'						=> 'The remove my posts (r) parameter value is invalid. If present it should only have a value of 1. Please rerun Smartfeed on the forum to generate a correct feed URL.',
	'SMARTFEED_REPLY'									=> 'Reply',
	'SMARTFEED_REPLY_BY'								=> 'Reply by',
	'SMARTFEED_SORT_BY_ERROR'							=> 'Smartfeed cannot accept the sort by (s) parameter value. Please rerun Smartfeed on the forum to generate a correct feed URL.',
	'SMARTFEED_STYLE_ERROR'								=> 'The style parameter is not one of the allowed values, or is absent. Please rerun Smartfeed on the forum to generate a correct feed URL.',
	'SMARTFEED_USER_ID_DOES_NOT_EXIST'					=> 'User ID identified by the &ldquo;u&rdquo; parameter does not exist or is not allowed to access a feed. Please rerun Smartfeed on the forum to generate a correct feed URL.',
));
