<?php
/**
 *
 * @package phpBB Extension - Smartfeed
 * @copyright (c) 2016 Mark D. Hamill (mark@phpbbservices.com)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace phpbbservices\smartfeed;

/**
 * @ignore
 */

class ext extends \phpbb\extension\base
{

	public function is_enableable()
	{
		// Check to make sure the installed version of PHP supports the minimum requirements of SimplePie.
		// If not, do not allow the Smartfeed extension to be installed.
		return (!extension_loaded('xml') || !extension_loaded('pcre')) ? false : true;
	}

}
