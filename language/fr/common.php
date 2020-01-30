<?php
/**
*
* @package phpBB Extension - Smartfeed
* @copyright (c) 2020 Mark D. Hamill (mark@phpbbservices.com)
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
	'SMARTFEED_FEED_TYPE'								=> 'Format du flux d’informations',
	'SMARTFEED_FIRST_POST_ONLY'							=> 'Types de messages dans le flux',
	'SMARTFEED_INSTALL_REQUIREMENTS'					=> 'Les extensions PHP suivantes sont nécessaires : xml, pcre et openssl. Votre version de PHP doit être &gt; 3.2.0 et &lt; 3.3. Une ou plusieurs de ces exigences font défaut. Veuillez résoudre ces problèmes, puis essayez d’activer à nouveau l’extension.',
	'SMARTFEED_NO_FORUMS_AVAILABLE' 					=> 'Désolé, en raison de votre statut d’utilisateur, vous ne pouvez accéder à aucun forum',
	'SMARTFEED_PAGE'									=> 'Smartfeed',
));
