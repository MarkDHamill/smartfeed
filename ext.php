<?php
/**
 *
 * @package phpBB Extension - Smartfeed
 * @copyright (c) 2017 Mark D. Hamill (mark@phpbbservices.com)
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

		$config = $this->container->get('config');

		// phpBB 3.2 is supported. phpBB 3.1 is not supported in this version. Also check to make sure the installed version
		// of PHP supports the minimum requirements of SimplePie documented at: http://simplepie.org/wiki/setup/requirements
		return (phpbb_version_compare($config['version'], '3.2.0', '>=') && phpbb_version_compare($config['version'], '3.3', '<') && extension_loaded('xml') && extension_loaded('pcre'));

	}

}
