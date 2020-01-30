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
	'SMARTFEED_APACHE_AUTHENTICATION_WARNING_REG'		=> 'Smartfeed ne peut pas être utilisé sur ce forum. Contactez l’administrateur pour plus d’informations.',
	'SMARTFEED_ATOM_10'									=> 'Atom 1.0',
	'SMARTFEED_BAD_BOOKMARKS_VALUE'						=> 'S’il est utilisé, le paramètre des sujets favoris (b), ne peut avoir qu’une valeur de 1. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_BAD_MARK_PRIVATE_MESSAGES_READ_ERROR'	=> 'Si présent, le paramètre "Marquer les messages privés comme lus (k)" doit avoir la valeur 1. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_BAD_PASSWORD_ERROR'						=> 'Échec de l’authentification. Le paramêtre &ldquo;e&rdquo; de &ldquo;%s&rdquo; est invalide avec le paramêtre &ldquo;u&rdquo; de &ldquo;%s&rdquo;. Cette erreur peut être causée par un changement de votre mot de passe du forum, ou par une mise à jour du logiciel de Smartfeed. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_BAD_PMS_VALUE'							=> 'Si présent, le paramètre "Afficher les messages privés" (m) doit avoir une valeur de 1. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_BOARD_DISABLED'							=> 'Ce forum est actuellement hors ligne. Par conséquent, la fonctionnalité de flux d’informations a été désactivée. Lorsque le forum sera à nouveau en ligne, vous pourrez récupérer les fils d’actualité.',
	'SMARTFEED_DELIMITER'								=> ' :: ', // Utilisé pour séparer les noms de forum, les noms de sujet et les sujets de message qui apparaissent tous ensemble dans le flux, comme par exemple dans le titre de l'article
	'SMARTFEED_EXTERNAL_ITEM'							=> 'Élément externe',
	'SMARTFEED_ERROR'									=> 'Erreur Smartfeed',
	'SMARTFEED_FEED'									=> 'Flux d’informations Smartfeed',
	'SMARTFEED_FEED_TYPE_ERROR'							=> 'Smartfeed n’accepte pas la valeur du paramètre de type de flux de y=%s. Les valeurs acceptables sont 0 (Atom), 1 (RSS 1.0) et 2 (RSS 2.0).',
	'SMARTFEED_FILTER_FOES_ERROR'						=> 'La valeur du paramètre "Filtre foes" (ff) n’est pas valide. Si présente, elle devrait avoir la valeur 1. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_FIRST_POST_ONLY_ERROR'					=> 'La valeur du paramètre "Premier message seulement" (fp) n’est pas valide. Si présente, elle devrait avoir la valeur 1. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_GLOBAL_ANNOUNCEMENT'						=> 'ANNONCE GLOBALE',
	'SMARTFEED_IP_AUTH_ERROR'							=> 'L’adresse IP du client qui fait la demande de Smartfeed n’est pas autorisée à accéder au flux parce qu’elle n’a pas passé l’accréditation appropriée. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_IP_RANGE_ERROR'							=> 'Votre IP %s est invalide.',
	'SMARTFEED_LASTVISIT_ERROR'							=> 'La valeur du paramètre "Dernière visite" (l) n’est pas valide. Si présente, elle devrait avoir la valeur 1. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_LAST_POST_ONLY_ERROR'					=> 'La valeur du paramètre "Dernièr message" (lp) n’est pas valide. Si présente, elle devrait avoir la valeur 1. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_LIMIT_FORMAT_ERROR'						=> 'Le paramètre "Limite de temps" (t) n’est pas une valeur autorisée. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_MAX_ITEMS_ERROR'							=> 'Si elle est spécifiée, la valeur du paramètre "Nombre maximal d’éléments" (x) doit être un nombre entier supérieur à 0. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_MAX_WORD_SIZE_ERROR'						=> 'La valeur du paramètre "nombre maximum de mots dans un message" (w) n’est pas valide. La valeur doit être un nombre entier. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_MAX_WORDS_NOTIFIER'						=> ' ...',
	'SMARTFEED_MIN_WORD_SIZE_ERROR'						=> 'La valeur du paramètre "Taille minimale des mots" (i) n’est pas valide. La valeur doit être un nombre entier. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_NEW_PMS_NOTIFICATIONS_ONLY'				=> 'Vous avez de nouveaux messages privés. Veuillez vous connecter au forum pour les lire.',
	'SMARTFEED_NEW_PMS_NOTIFICATIONS_SHORT'				=> 'Vous avez de nouveaux messages privés',
	'SMARTFEED_NEW_POST_NOTIFICATION'					=> 'Il y a de nouveaux messages dans ce sujet. Veuillez vous connecter au forum pour les lire.',
	'SMARTFEED_NEW_TOPIC_NOTIFICATION'					=> 'Ceci est un nouveau sujet. Veuillez vous connecter au forum pour le lire.',
	'SMARTFEED_NO_ACCESSIBLE_FORUMS'					=> 'Vous n’avez pas l’autorisation d’accéder aux forums de ce site. Si vous êtes inscrit, il se peut que l’administrateur doive vous accorder des privilèges pour lire les forums ou vous réintégrer en tant que membre actif.',
	'SMARTFEED_NO_BOOKMARKS'							=> 'Vous n’avez pas de sujets favoris mais vous avez demandé à ne montrer que les sujets favoris. Par conséquent, il n’y a pas de messages dans le flux. Si vous souhaitez utiliser les sujets favoris avec Smartfeed, veuillez vous rendre sur le forum et ajoutez un ou plusieurs sujets à vos favoris.',
	'SMARTFEED_NO_FORUMS_ACCESSIBLE' 					=> 'Désolé, en raison des exclusions de Smartfeed et de vos privilèges d’accès, vous ne pouvez accéder à aucun forum',
	'SMARTFEED_NO_E_ARGUMENT'							=> 'Pour authentifier un membre, le paramètre &ldquo;e&rdquo; doit être utilisé avec le paramètre &ldquo;u&rdquo;. Le paramètre &ldquo;u&rdquo; est présent mais le paramètre &ldquo;e&rdquo; ne l’est pas. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_NO_OPENSSL_MODULE'						=> 'Smartfeed ne peut pas prendre en charge l’authentification des utilisateurs car le forum ne supporte pas le module PHP openssl. Veuillez relancer Smartfeed en vous déconnectant pour obtenir une URL valide.',
	'SMARTFEED_NO_U_ARGUMENT'							=> 'Pour authentifier un membre, le paramètre &ldquo;u&rdquo; doit être utilisé avec le paramètre &ldquo;e&rdquo;. Le paramètre &ldquo;e&rdquo; est présent mais le paramètre &ldquo;u&rdquo ; ne l’est pas. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_POST_IMAGE_TEXT'							=> '<br>(Cliquez sur l’image pour la voir en taille réelle).',
	'SMARTFEED_POST_SIGNATURE_DELIMITER'				=> '<br>____________________<br>', // Placez ici le code que vous voulez utiliser pour distinguer la fin d'un message du début de la ligne de signature (assurez-vous que ce soit du HTML valide)
	'SMARTFEED_REMOVE_MINE_ERROR'						=> 'La valeur du paramètre "Supprimer mes messages" (r) n’est pas valide. Si présente, elle devrait avoir la valeur 1. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_REPLY'									=> 'Réponse',
	'SMARTFEED_REPLY_BY'								=> 'Réponse de',
	'SMARTFEED_SHOW_TOPIC_TITLES_ERROR'					=> 'La valeur du paramètre "Montrer les titres des sujets" (tt) spécifiée n’est pas valide. Si présente, elle devrait avoir la valeur 1. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_SORT_BY_ERROR'							=> 'Smartfeed ne peut pas accepter la valeur du paramètre "Trier par" (s). Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_STYLE_ERROR'								=> 'Le paramètre de style n’est pas une des valeurs autorisées, ou est absent. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_SUPPRESS_FORUM_NAMES_ERROR'				=> 'La valeur du paramètre "Supprimer les noms de forum" (fn) n’est pas valide. Si présente, elle devrait avoir la valeur 1. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_SUPPRESS_USERNAMES_ERROR'				=> 'La valeur du paramètre "Supprimer les noms d’utilisateur" (un) n’est pas valide. Si présente, elle devrait avoir la valeur 1. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
	'SMARTFEED_USER_ID_DOES_NOT_EXIST'					=> 'L’ID utilisateur identifié par le paramètre &ldquo;u&rdquo; n’existe pas ou n’est pas autorisé à accéder à un flux. Veuillez relancer Smartfeed pour générer une URL de flux correcte.',
));
