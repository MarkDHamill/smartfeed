<?php
/**
 *
 * @package phpBB Extension - Smartfeed
 * @copyright (c) 2020 Mark D. Hamill (mark@phpbbservices.com)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace phpbbservices\smartfeed\acp;

class main_module
{

	public $tpl_name;
	public $u_action;

	/**
	 * Main ACP module
	 *
	 * @param int    $id   The module ID
	 * @param string $mode The module mode (for example: manage or settings)
	 * @throws \Exception
	 */
	function main($id, $mode)
	{

		global $phpbb_container;

		/** @var \phpbbservices\smartfeed\controller\acp_controller $acp_controller */
		$acp_controller = $phpbb_container->get('phpbbservices.smartfeed.controller.acp');

		$this->language = $phpbb_container->get('language');
		$this->language->add_lang('common', 'phpbbservices/smartfeed');

		// Load a template from adm/style for our ACP pages
		$this->tpl_name = 'acp_smartfeed';

		switch ($mode)
		{
			case 'ppt':
			default:
				$this->page_title = $this->language->lang('ACP_SMARTFEED_PPT');
			break;

			case 'security':
				$this->page_title = $this->language->lang('ACP_SMARTFEED_SECURITY');
			break;

			case 'additional':
				$this->page_title = $this->language->lang('ACP_SMARTFEED_ADDITIONAL');
			break;
		}

		// Load the display options handle in our ACP controller, passing the mode
		$acp_controller->display_options($mode, $this->u_action);

	}

}
