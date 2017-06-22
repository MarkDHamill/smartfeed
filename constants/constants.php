<?php
/**
*
* @package phpBB Extension - Smartfeed
* @copyright (c) 2016 Mark D. Hamill (mark@phpbbservices.com)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbbservices\smartfeed\constants;

class constants {

	// These constants are used to identify URI key/value pair associations. Note these have changed since the phpBB 3.0 mod so URIs will be shorter.
	const SMARTFEED_BOOKMARKS = 'b'; 					// was bookmarks
	const SMARTFEED_ENCRYPTION_KEY = 'e';				// unchanged
	const SMARTFEED_FEED_STYLE = 'd';					// was feed_style
	const SMARTFEED_FEED_TYPE = 'y';					// was feed_type
	const SMARTFEED_FILTER_FOES = 'ff';					// was filter_foes
	const SMARTFEED_FIRST_POST = 'fp';					// was firstpostonly
	const SMARTFEED_FORUMS = 'f';						// was forum
	const SMARTFEED_MARK_PRIVATE_MESSAGES = 'k';		// was pms
	const SMARTFEED_MAX_ITEMS = 'x';					// was count_limit
	const SMARTFEED_MAX_WORDS = 'w';					// was max_word_size
	const SMARTFEED_MIN_WORDS = 'i';					// was min_word_size
	const SMARTFEED_PRIVATE_MESSAGE = 'm';				// was pms
	const SMARTFEED_REMOVE_MINE = 'r';					// was removemine
	const SMARTFEED_SINCE_LAST_VISIT = 'l';				// was lastvisit
	const SMARTFEED_SORT_BY = 's';						// was sort_by
	const SMARTFEED_TIME_LIMIT = 't';					// was limit
	const SMARTFEED_USER_ID = 'u';						// unchanged
	
	// These constants are used to set the time limit for the feed
	const SMARTFEED_USE_DEFAULT_FETCH_TIME_LIMIT = 0;	// Not selectable means was not specified
	const SMARTFEED_SINCE_LAST_VISIT_VALUE = 1; 		// was LF for Last Visit
	const SMARTFEED_NO_LIMIT_VALUE = 2; 				// was NO_LIMIT
	const SMARTFEED_LAST_QUARTER_VALUE = 3; 			// was 3_MONTH
	const SMARTFEED_LAST_MONTH_VALUE = 4; 				// was 1_MONTH
	const SMARTFEED_LAST_TWO_WEEKS_VALUE = 5; 			// was 14_DAY
	const SMARTFEED_LAST_WEEK_VALUE = 6; 				// was 7_DAY
	const SMARTFEED_LAST_DAY_VALUE = 7; 				// was 1_DAY
	const SMARTFEED_LAST_12_HOURS_VALUE = 8; 			// was 12_HOUR
	const SMARTFEED_LAST_6_HOURS_VALUE = 9; 			// was 6_HOUR
	const SMARTFEED_LAST_3_HOURS_VALUE = 10; 			// was 3_HOUR
	const SMARTFEED_LAST_1_HOURS_VALUE = 11; 			// was 1_HOUR
	const SMARTFEED_LAST_30_MINUTES_VALUE = 12;			// was 30_MINUTE
	const SMARTFEED_LAST_15_MINUTES_VALUE = 13; 		// was 15_MINUTE
	
	// These constants are used to sort items in the feed
	const SMARTFEED_BOARD = '0'; 						// Was user
	const SMARTFEED_STANDARD = '1'; 					// Was standard
	const SMARTFEED_STANDARD_DESC = '2'; 				// Was standard_desc
	const SMARTFEED_POSTDATE = '3'; 					// Was postdate
	const SMARTFEED_POSTDATE_DESC = '4'; 				// Was postdate_desc
	
	// These constants are used to describe the feed style
	const SMARTFEED_COMPACT = '0'; 						// Was COMPACT
	const SMARTFEED_BASIC = '1'; 						// Was BASIC
	const SMARTFEED_HTMLSAFE = '2'; 					// Was HTMLSAFE
	const SMARTFEED_HTML = '3'; 						// Was HTML
	
	// These constants are used to describe the feed type
	const SMARTFEED_ATOM = '0'; 							// Was ATOM1.0
	const SMARTFEED_RSS1 = '1'; 							// Was RSS1.0
	const SMARTFEED_RSS2 = '2'; 							// Was RSS2.0
	
	// Miscellaneous
	const SMARTFEED_GENERATOR = 'Smartfeed extension for phpBB';	// Does not need to be language specific, used in feed text because some feed formats require it.
	const SMARTFEED_VERSION = '3.0.8'; // Update for each release. Needed to support <generator> tag in Atom 1.0.

}
