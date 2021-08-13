<?php
/**
*
* @package phpBB Extension - Smartfeed
* @copyright (c) 2020 Mark D. Hamill (mark@phpbbservices.com)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbbservices\smartfeed\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class main_listener implements EventSubscriberInterface
{
	private $config;
	private $helper;
	private $template;

	/**
	* Constructor
	*
	* @param \phpbb\config\config		$config
	* @param \phpbb\controller\helper	$helper		Controller helper object
	* @param \phpbb\template\template	$template	Template object
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
		// This language file is needed pretty much everywhere, since among other things it places
		// content in the <head> section for most pages.
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'phpbbservices/smartfeed',
			'lang_set' => array('common','ui'),
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function add_page_header_link()
	{
		$this->template->assign_vars(array(
			'U_SMARTFEED_PAGE'	=> $this->helper->route('phpbbservices_smartfeed_ui_controller'),
		));
	}
	
	public function overall_header_head_append()
	{
		$this->template->assign_vars(array(
			'S_AUTO_ADVERTISE_PUBLIC_FEED'		=> $this->config['phpbbservices_smartfeed_auto_advertise_public_feed'],
			'S_SMARTFEED_UI_LOCATION'			=> $this->config['phpbbservices_smartfeed_ui_location'],
			'U_SMARTFEED_URL_ATOM'				=> $this->helper->route('phpbbservices_smartfeed_feed_controller'),
			'U_SMARTFEED_URL_RSS'				=> $this->helper->route('phpbbservices_smartfeed_feed_controller', array('y'=>2)),
		));
	}
   	
}
