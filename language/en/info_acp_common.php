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

global $phpbb_container;

$phpEx = $phpbb_container->getParameter('core.php_ext');

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACP_SMARTFEED'												=> 'Smartfeed',
	'ACP_SMARTFEED_ADDITIONAL'									=> 'Additional settings and options',
	'ACP_SMARTFEED_ADDITIONAL_EXPLAIN'							=> 'Adjust Smartfeed&rsquo;s miscellaneous settings and options',
	'ACP_SMARTFEED_ALL_BY_DEFAULT'								=> 'Select all forums by default',
	'ACP_SMARTFEED_ALL_BY_DEFAULT_EXPLAIN'						=> 'If set to Yes, when the Smartfeed URL generation page is displayed, all the forums will be checked. If you have a lot of forums, it may be better to set this to No.',
	'ACP_SMARTFEED_APACHE_HTACCESS_ENABLED'						=> 'Apache .htaccess enabled for Smartfeed',
	'ACP_SMARTFEED_APACHE_HTACCESS_ENABLED_EXPLAIN'				=> 'If you use Apache authentication, you first need to manually correct your board&rsquo;s .htaccess file to allow the app.' . $phpEx . '/smartfeed/feed program to bypass authentication. Otherwise it is impossible for Smartfeed to work, since Apache authentication won&rsquo;t let it through. If you have fixed your .htaccess file then you can set this to true. Of course, if you do not use Apache authentication, this setting is ignored.',
	'ACP_SMARTFEED_AUTO_ADVERTISE_PUBLIC_FEED'					=> 'Auto-advertise your public feeds',
	'ACP_SMARTFEED_AUTO_ADVERTISE_PUBLIC_FEED_EXPLAIN'			=> 'Set to true if you want to expose your public feed automatically.',
	'ACP_SMARTFEED_DEFAULT_FETCH_TIME_LIMIT'					=> 'Maximum post time range',
	'ACP_SMARTFEED_DEFAULT_FETCH_TIME_LIMIT_EXPLAIN'			=> 'This will set a point in time in hours from now beyond which no posts can be retrieved. Otherwise on highly trafficked boards it could take minutes or hours to assemble a newsfeed, possibly impacting other members. The default is to go back 30 days (720 hours). Caution: if you set this to zero, you could be giving permission for all members to create a feed with hundreds or thousands of posts.',
	'ACP_SMARTFEED_EXCLUDE_FORUMS'								=> 'Always exclude these forums',
	'ACP_SMARTFEED_EXCLUDE_FORUMS_EXPLAIN'						=> 'Enter the forum_ids for forums that must never appear in any newsfeed. Separate the forum_ids with commas. If left blank, no forums have to be excluded. To determine the forum_ids, when browsing a forum observe the &ldquo;f&rdquo; parameter on the URL field. This is the forum_id. Example: http://www.example.com/phpBB3/viewforum.php?f=1. Do not use forum_ids that correspond to categories. Categories cannot be selected with phpBB Smartfeed. Note that by default Smartfeed prohibits anyone from reading forums for which they do not have read privileges.',
	'ACP_SMARTFEED_EXTERNAL_FEEDS'								=> 'External feeds',
	'ACP_SMARTFEED_EXTERNAL_FEEDS_EXPLAIN'						=> 'Enter the URIs of external feeds that you want to appear in the newsfeed, putting each URL on a separate line. External feeds appear in the order they are entered. Note: filtering rules that apply to posts also apply to items in external feeds, except these items do not count against the maximum number of posts in the feed. In particular, if the publish date of the article does not fall within the date range of the feed (like seven days) these articles will not appear. No more than 255 characters can physically be stored in this field.',
	'ACP_SMARTFEED_EXTERNAL_FEEDS_TOP'							=> 'External feed items at top of feed',
	'ACP_SMARTFEED_EXTERNAL_FEEDS_TOP_EXPLAIN'					=> 'If you select No, external feed items will be at the bottom of the feed. Private messages, if any, will always appear first in the feed.',
	'ACP_SMARTFEED_FEED_IMAGE_PATH'								=> 'Feed image path and file',
	'ACP_SMARTFEED_FEED_IMAGE_PATH_EXPLAIN'						=> 'The path to the image you want to appear in the feed to brand your feed. The default image is site_logo.gif, which is the phpBB logo (or the image you substituted for it). The default style folder will be used, so set it to the relative path from your board&rsquo;s default style directory. Markup appears in RSS 1.0 and RSS 2.0 feeds only. Whether the logo actually shows depends on the capabilities of the newsreader used.',
	'ACP_SMARTFEED_HOURS'										=> 'hrs',
	'ACP_SMARTFEED_INCLUDE_FORUMS'								=> 'Always include these forums',
	'ACP_SMARTFEED_INCLUDE_FORUMS_EXPLAIN'						=> 'Enter the forum_ids for forums that must appear in any newsfeed. Separate the forum_ids with commas. If left blank, no forums have to be included. To determine the forum_ids, when browsing a forum observe the &ldquo;f&rdquo; parameter on the URL field. This is the forum_id. Example: http://www.example.com/phpBB3/viewforum.php?f=1. Do not use forum_ids that correspond to categories. Categories cannot be selected with Smartfeed.',
	'ACP_SMARTFEED_MAX_ITEMS'									=> 'Maximum items allowed in any feed',
	'ACP_SMARTFEED_MAX_ITEMS_EXPLAIN'							=> 'This is used to set some upper bound on the number of items permitted in a newsfeed. If 0, there is no limit. For heavily trafficked boards you may find you have to set a limit to keep the board from getting bogged down. Make sure this is a whole number.',
	'ACP_SMARTFEED_MAX_WORD_SIZE'								=> 'Maximum words to display in a post',
	'ACP_SMARTFEED_MAX_WORD_SIZE_EXPLAIN'						=> 'No post in any feed can exceed this number of words. Enter 0 to allow an unrestrained word size for any post. The user always has the option to limit the number of words in a post to less than the board limit. Notice: To ensure consistent rendering, if a post must be truncated, the markup will be removed from the post. <i>Note:</i> items in external feeds are unaffected.',
	'ACP_SMARTFEED_MINUTES'										=> 'min',
	'ACP_SMARTFEED_NEW_POST_NOTIFICATIONS_ONLY'					=> 'Show new post and private message notifications only in the feed',
	'ACP_SMARTFEED_NEW_POST_NOTIFICATIONS_ONLY_EXPLAIN'			=> 'If your content is highly sensitive you might want to enable this feature. If enabled, the feed will not show the content of any posts or private messages, but will present a message for each topic for which there are new posts and a notification if there are new private messages. The user would have to login to the board to view the new posts or private messages. Note that this is a global setting, so it will affect all categories, forums and users as well as private messages. Author names and post subjects are hidden but the topic name is shown.',
	'ACP_SMARTFEED_PPT'											=> 'Primary performance throttles',
	'ACP_SMARTFEED_PPT_EXPLAIN'									=> 'Adjust Smartfeed&rsquo;s major performance throttles',
	'ACP_SMARTFEED_PRIVACY_MODE'								=> 'Privacy mode',
	'ACP_SMARTFEED_PRIVACY_MODE_EXPLAIN'						=> 'If Yes, real poster email addresses are not shown in the feed and a fake email address is substituted if necessary to validate the feed. Even if set to No, if the member specifies in their board preferences not to show their email address, then a fake email address will be substituted. Also in this mode signature blocks are not shown to public members. The idea is to keep spammers from having yet another way to harvest email addresses and to maximize the privacy of your member&rsquo;s information.',
	'ACP_SMARTFEED_REQUIRE_IP_AUTHENTICATION'					=> 'Require IP authentication',
	'ACP_SMARTFEED_REQUIRE_IP_AUTHENTICATION_EXPLAIN'			=> 'Setting this to Yes tightens security for all registered members by limiting the IP range for which Smartfeed will return a feed. You should enable this setting if your board contains sensitive information. If you leave this set at No, each registered member can decide for themselves if they want this extra security feature. Please note that unless your board uses HTTPS or is accessed through a VPN, the feed will be unencrypted.',
	'ACP_SMARTFEED_RFC1766_LANG'								=> 'RFC-1766 Language Code',
	'ACP_SMARTFEED_RFC1766_LANG_EXPLAIN'						=> 'Language of your feed content. This is used in ATOM and RSS 2.0 feeds. <a href="http://www.w3.org/TR/REC-html40/struct/dirlang.html#langcodes">List of valid codes</a>.',
	'ACP_SMARTFEED_SECURITY'									=> 'Security settings',
	'ACP_SMARTFEED_SECURITY_EXPLAIN'							=> 'Adjust Smartfeed&rsquo;s security settings',
	'ACP_SMARTFEED_SHOW_SESSIONS'								=> 'Show Smartfeed sessions',
	'ACP_SMARTFEED_SHOW_SESSIONS_EXPLAIN'						=> 'If Yes, the View Online information will be misleading because people or guests will appear online who are in fact accessing a feed. Most Smartfeed sessions will show up as guest sessions. Setting this to Yes may also skew the most users online ever value.',
	'ACP_SMARTFEED_SHOW_USERNAME_IN_FIRST_TOPIC_POST'			=> 'Show member name in the first topic post',
	'ACP_SMARTFEED_SHOW_USERNAME_IN_FIRST_TOPIC_POST_EXPLAIN'	=> 'Set to Yes if you want the member name to appear in the item title of the first topic in a post. You might want to set this to No if you want to cannibalize your own feed elsewhere, for example to show a list of new topics on your web site main page. Note that the item author value is always set, but not all newsreaders will display it.',
	'ACP_SMARTFEED_SHOW_USERNAME_IN_REPLIES'					=> 'Show member name in replies',
	'ACP_SMARTFEED_SHOW_USERNAME_IN_REPLIES_EXPLAIN'			=> 'If you set this to No, member names will not appear in topic replies. This could be unwise because the newsreader may choose not to show the authors of individual items in the feed, so it would be hard for readers to know who posted the reply.',
	'ACP_SMARTFEED_SUPPRESS_FORUM_NAMES'						=> 'Suppress forum names',
	'ACP_SMARTFEED_SUPPRESS_FORUM_NAMES_EXPLAIN'				=> 'By default the forum name appears in the item title. However, forum names can be very long and when it is joined with the topic name, the item title may be very long. To suppress the forum name appearing in the item title for all members, set this value to Yes.',
	'ACP_SMARTFEED_TITLE'										=> 'Smartfeed Settings',
	'ACP_SMARTFEED_TTL'											=> 'Newsfeed Time to Live (TTL)',
	'ACP_SMARTFEED_TTL_EXPLAIN'									=> 'How many minutes should the newsreader cache the feed before refreshing it? Throttle the number up if your board is getting overwhelmed, but newsreaders may ignore your advice. Note that this is a feature of RSS 2.0 only, and individual newsreaders may ignore the setting.',
	'ACP_SMARTFEED_WEBMASTER'									=> 'Webmaster E-Mail Address',
	'ACP_SMARTFEED_WEBMASTER_EXPLAIN'							=> 'If so inclined, enter the email address of the webmaster of the phpBB forum or whoever handles feed related questions. The email address will appear in RSS 2.0 feeds. For maximum interoperability, include the name of the person associated with the email address in parentheses, ex: jjones@example.com (John Jones).',
	'LOG_CONFIG_SMARTFEED_EXTFEED'								=> '<strong>Smartfeed requested external feed &ldquo;%s&rdquo; is bad or cannot be parsed as a feed.</strong>',));
