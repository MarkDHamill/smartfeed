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

global $phpbb_container;

// Encapsulate certain phpBB objects inside this class to minimize security issues
$this->config = $phpbb_container->get('config');

$lang = array_merge($lang, array(
	'SMARTFEED_ADDITIONAL_CRITERIA'						=> 'Additional criteria',
	'SMARTFEED_ALL_FORUMS'								=> 'All',
	'SMARTFEED_APACHE_AUTHENTICATION_WARNING_ADMIN'		=> 'Smartfeed cannot be used with Apache authentication until the administrator tailors the forum&rsquo;s .htaccess file. The administrator should adjust the .htaccess file and change the Smartfeed settings in the Administration Control Panel.',
	'SMARTFEED_ATOM_10'									=> 'ATOM 1.0',
	'SMARTFEED_EXPLANATION'								=> 'With Smartfeed you can create your own personalized newsfeed from posts on this board and read them with the newsreader of your choice. Smartfeed can support authentication for registered members, allowing them to view posts in forums that others may not be able to read.',
	'SMARTFEED_FEED_STYLING'							=> 'Newsfeed styling',
	'SMARTFEED_FEED_STYLING_EXPLAIN'					=> 'Please note that the amount of feed styling actually applied depends on the capabilities of your news reader. Most news readers will not apply all HTML styles. Place your cursor over the style text to learn more. <em>Note</em>: styling does not apply to items in the feed that come from external feeds required by the administrator.',
	'SMARTFEED_FEED_TYPE_ERROR'							=> 'Smartfeed does not accept the feed type parameter value of y=%s. Acceptable values are 0 (Atom), 1 (RSS 1.0) and 2 (RSS 2.0).',
	'SMARTFEED_FILTER_CRITERIA'							=> 'Post filters',
	'SMARTFEED_FILTER_FOES'								=> 'Remove posts from my foes',
	'SMARTFEED_FIRST_POST_ONLY_ERROR'					=> 'The first post only (fp) parameter value is invalid. If present it should only have a value of 0 or 1. Please rerun Smartfeed on the forum to generate a correct feed URL.',
	'SMARTFEED_FORMAT_AND_ACCESS'						=> 'Format &amp; access control',
	'SMARTFEED_FORUM_SELECTION'							=> 'Forum selection',
	'SMARTFEED_GENERATE_BUTTON'							=> 'Generate the URL',
	'SMARTFEED_GENERATE_BUTTON_EXPLAIN'					=> 'To copy it, click on the URL. All text will be automatically selected. Then copy and paste it in your news reader.',
	'SMARTFEED_GENERATE_URL_TEXT'						=> 'Generate',
	'SMARTFEED_IP_AUTH'									=> 'IP Authentication',
	'SMARTFEED_IP_AUTHENTICATION'						=> 'IP Authentication',
	'SMARTFEED_IP_AUTHENTICATION_EXPLAIN'				=> 'Your current Internet Protocol (IP) address is %s. If you select yes, the URL generated will make the feed work only for this IP range.',
	'SMARTFEED_LAST_1_HOURS'							=> 'In Last Hour',
	'SMARTFEED_LAST_12_HOURS'							=> 'In Last 12 Hours',
	'SMARTFEED_LAST_15_MINUTES'							=> 'In Last 15 Minutes',
	'SMARTFEED_LAST_3_HOURS'							=> 'In Last 3 Hours',
	'SMARTFEED_LAST_30_MINUTES'							=> 'In Last 30 Minutes',
	'SMARTFEED_LAST_6_HOURS'							=> 'In Last 6 Hours',
	'SMARTFEED_LAST_DAY'								=> 'In Last 24 Hours',
	'SMARTFEED_LAST_MONTH'								=> 'In Last 30 Days',
	'SMARTFEED_LAST_QUARTER'							=> 'In Last 90 Days',
	'SMARTFEED_LAST_TWO_WEEKS'							=> 'In Last 14 Days',
	'SMARTFEED_LAST_WEEK'								=> 'In Last 7 Days',
	'SMARTFEED_LASTVISIT_RESET'							=> 'Reset my last visit date on news feed access',
	'SMARTFEED_LIMIT'									=> 'Time limit',
	'SMARTFEED_LIMIT_EXPLAIN'							=> 'Include posts over this time range up to the present in the newsfeed.',
	'SMARTFEED_LIMIT_SET_EXPLAIN'						=> 'Irrespective of the time periods shown, this board has a set limit of %d days beyond which no posts can be retrieved. This is necessary to ensure that fetching feeds does not slow down overall access to this forum.',
	'SMARTFEED_MARK_READ'								=> 'Mark private messages as read when they appear in the feed',
	'SMARTFEED_MAX_ITEMS'								=> 'Maximum number of posts in the feed',
	'SMARTFEED_MAX_ITEMS_EXPLAIN'						=> 'The maximum number of posts in a feed allowed by the board&rsquo;s administrator is %d. You may set this value to any number less than or equal to the board limit, but greater than or equal to %d.',
	'SMARTFEED_MAX_ITEMS_EXPLAIN_BLANK'					=> 'If zero, any number of items can be in the feed.',
	'SMARTFEED_MAX_WORD_SIZE'							=> 'Maximum words to display in a post or private message',
	'SMARTFEED_MAX_WORD_SIZE_EXPLAIN'					=> 'If zero, a post or private message can be of any size in the feed up to the board limit, if any. Notice: if not zero, then to ensure consistent rendering, if a post or private message must be truncated, the HTML will be removed. A maximum number of %d words is allowed.',
	'SMARTFEED_MAX_WORD_SIZE_EXPLAIN_BLANK'				=> 'If zero, a post or private message can be of any size in the feed up to the board limit, if any. Notice: if not zero, then to ensure consistent rendering, if a post or private message must be truncated, the HTML will be removed.',
	'SMARTFEED_MIN_WORDS'								=> 'Minimum words required in post for the post to appear in feed',
	'SMARTFEED_MIN_WORDS_EXPLAIN'						=> 'If zero, there are no minimum number of words required.',
	'SMARTFEED_NEW_PMS_NOTIFICATIONS_ONLY'				=> 'You have new private messages on the forum. Please log in to the forum to read them.',
	'SMARTFEED_NO_FORUMS_SELECTED'						=> 'You have not selected any forums, so no URL can be generated. Please select at least one forum.',
	'SMARTFEED_NO_LIMIT'								=> 'None',
	'SMARTFEED_NO_OPENSSL_SUPPORT'						=> '<strong>Note: this site can provide feeds for public forums only</strong>',
	'SMARTFEED_NOT_LOGGED_IN'							=> '<strong>Because you are not logged in, OAuth authentication is used or your member status does not allow it, you can only subscribe to the list of public forums shown below. Please <a href="./../../ucp.%s?mode=login" class="postlink">log in</a> or <a href="./../../ucp.%s?mode=register" class="postlink">register</a> if you want to also subscribe to nonpublic forums or to access features for registered members only.</strong>',
	'SMARTFEED_POSTS_TYPE_ANY'							=> 'All posts',
	'SMARTFEED_POSTS_TYPE_FIRST'						=> 'First posts of topics only',
	'SMARTFEED_POWERED_BY'								=> 'phpbbservices.com',
	'SMARTFEED_PRIVATE_MESSAGES_IN_FEED'				=> 'Add my unread private messages',
	'SMARTFEED_REMOVE_YOURS'							=> 'Remove my posts',
	'SMARTFEED_RSS_10'									=> 'RSS 1.0 (RDF)',
	'SMARTFEED_RSS_20'									=> 'RSS 2.0',
	'SMARTFEED_SELECT_FORUMS'							=> 'Include posts for these forums',
	'SMARTFEED_SELECT_FORUMS_EXPLAIN'					=> 'Bolded forum names, if any, are forums that the administrator requires to be shown in any newsfeed. You cannot unselect these forums. Forum names that have strikethrough text are not allowed in a newsfeed and cannot be selected. If logged in then all forum selection is disabled when you select bookmarked topics only.',
	'SMARTFEED_SINCE_LAST_VISIT_TEXT'					=> 'Since My Last Visit',
	'SMARTFEED_SIZE_ERROR'								=> sprintf("This field is a required field. You must enter a positive whole number, less than or equal to the maximum allowed by the Forum Administrator. The maximum allowed is %u. If this value is zero, there is no limit.", $this->config['phpbbservices_smartfeed_max_items']),
	'SMARTFEED_SIZE_ERROR_MIN'							=> 'You must enter a whole number or leave the field blank. If this value is zero, there is no limit.',
	'SMARTFEED_SORT_BY'									=> 'Post sort order',
	'SMARTFEED_SORT_BY_EXPLAIN'							=> 'Default order is the order used by phpBB if you don&rsquo;t change it in the User Control Panel. By default, posts in the feed are shown in category order (ascending), then forum order (ascending) within categories, then last topic post time (descending) within a forum and then post time (ascending) within a topic.',
	'SMARTFEED_SORT_FORUM_TOPIC'						=> 'Default order',
	'SMARTFEED_SORT_FORUM_TOPIC_DESC'					=> 'Default order, with latest posts first',
	'SMARTFEED_SORT_POST_DATE'							=> 'From oldest to newest',
	'SMARTFEED_SORT_POST_DATE_DESC'						=> 'From newest to oldest',
	'SMARTFEED_SORT_USER_ORDER'							=> 'Use my board display preferences',
	'SMARTFEED_STYLE_BASIC'								=> 'Basic',
	'SMARTFEED_STYLE_BASIC_EXPLAIN'						=> 'Basic will remove formatting and BBCode but will apply signatures if allowed.',
	'SMARTFEED_STYLE_COMPACT'							=> 'Compact',
	'SMARTFEED_STYLE_COMPACT_EXPLAIN'					=> 'Compact will remove formatting, BBCode, signatures and collapse paragraphs.',
	'SMARTFEED_STYLE_HTML'								=> 'HTML',
	'SMARTFEED_STYLE_HTML_EXPLAIN'						=> 'HTML will provide formatting, BBCode and signatures (if allowed). Posts will look similar to how they appear in the forum. HTML feeds may not validate.',
	'SMARTFEED_STYLE_HTML_SAFE'							=> '<a href="http://validator.w3.org/feed/docs/warning/SecurityRiskAttr.html" class="postlink" onclick="window.open(this.href);return false;">Safe HTML</a>',
	'SMARTFEED_STYLE_HTML_SAFE_EXPLAIN'					=> 'Safe HTML will strip tags considered unsafe for newsreaders, according to the W3C.',
	'SMARTFEED_TEST'									=> 'Test',
	'SMARTFEED_TEST_BUTTON'								=> 'Test the feed',
	'SMARTFEED_TEST_BUTTON_EXPLAIN'						=> 'Run the URL in a new page of your browser. The newsfeed may look better in a newsreader.',
	'SMARTFEED_TITLE'									=> 'Smartfeed',
	'SMARTFEED_URL'										=> 'Generate &amp; test',
	'SMARTFEED_USE_BOOKMARKS'							=> 'Bookmarked topics only',
	'SMARTFEED_VALID_ATOM_1'							=> 'Smartfeed generates validated ATOM 1.0 feeds, as tested by the W3C Markup Validation Service (https://validator.w3.org).',
	'SMARTFEED_VALID_RSS_1'								=> 'Smartfeed generates validated RSS 1.0 feeds, as tested by the W3C Markup Validation Service (https://validator.w3.org).',
	'SMARTFEED_VALID_RSS_2'								=> 'Smartfeed generates validated RSS 2.0 feeds, as tested by the W3C Markup Validation Service (https://validator.w3.org).',
));
