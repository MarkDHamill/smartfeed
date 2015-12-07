<?php
/**
*
* @package phpBB Extension - Smartfeed
* @copyright (c) 2015 Mark D. Hamill (mark@phpbbservices.com)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbbservices\smartfeed\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use phpbbservices\smartfeed\constants\constants;

/**
* Event listener
*/
class main_listener implements EventSubscriberInterface
{
	/* @var \phpbb\config\config */
	protected $config;
	
	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/**
	* Constructor
	*
	* @param \phpbb\config\config		$config
	* @param \phpbb\controller\helper	$helper		Controller helper object
	* @param \phpbb\template			$template	Template object
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->template = $template;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'						=> 'load_language_on_setup',
			'core.page_header'						=> 'add_page_header_link',
			'core.page_header_after'  				=> 'overall_header_head_append',
		);
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'phpbbservices/smartfeed',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function add_page_header_link($event)
	{
		$this->template->assign_vars(array(
			'U_SMARTFEED_PAGE'	=> $this->helper->route('phpbbservices_smartfeed_controller', array('name' => 'smartfeed')),
			'U_SMARTFEED_FEED'	=> $this->helper->route('phpbbservices_smartfeed_controller', array('name' => 'feed')),
		));
	}
	
	public function overall_header_head_append($event)
	{
		
		global $phpEx;

		$this->template->assign_vars(array(
			'S_AUTO_ADVERTISE_PUBLIC_FEED'	=> !empty($this->config['phpbbservices_smartfeed_auto_advertise_public_feed']) ? $this->config['phpbbservices_smartfeed_auto_advertise_public_feed'] : false,
			'U_ATOM_PARAMETERS'					=> $this->config['phpbbservices_smartfeed_public_feed_url_suffix_atom'],
			'U_RSS_PARAMETERS'					=> $this->config['phpbbservices_smartfeed_public_feed_url_suffix_rss'], 
			'U_SMARTFEED_URL'					=> generate_board_url() . "/app.$phpEx/smartfeed/feed?",
		));
	}
   	
}
