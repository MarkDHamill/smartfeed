<?php
/**
 *
 * @package phpBB Extension - Smartfeed
 * @copyright (c) 2017 Mark D. Hamill (mark@phpbbservices.com)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace phpbbservices\smartfeed\migrations;

class release_3_0_5 extends \phpbb\db\migration\migration
{

	static public function depends_on()
	{
		return array(
			'\phpbbservices\smartfeed\migrations\release_3_0_4',
			'\phpbb\db\migration\data\v31x\v319',
		);
	}


	public function update_data()
	{
		return array(

			// Add Smartfeed extension configuration variables for external feed functionality
			array('config.add',	array('phpbbservices_smartfeed_external_feeds', '')),	// New
			array('config.add',	array('phpbbservices_smartfeed_external_feeds_top', '0')),	// New

		);
	}

}
