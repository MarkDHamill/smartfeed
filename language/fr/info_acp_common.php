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

global $phpbb_container;

$phpEx = $phpbb_container->getParameter('core.php_ext');

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACP_SMARTFEED'												=> 'Smartfeed',
	'ACP_SMARTFEED_ADDITIONAL'									=> 'Paramètres et options supplémentaires',
	'ACP_SMARTFEED_ADDITIONAL_EXPLAIN'							=> 'Ajustez les divers paramètres et options de Smartfeed',
	'ACP_SMARTFEED_ALL_BY_DEFAULT'								=> 'Sélectionnez tous les forums par défaut',
	'ACP_SMARTFEED_ALL_BY_DEFAULT_EXPLAIN'						=> 'Si défini sur Oui, lorsque la page de génération de l’URL Smartfeed est affichée, tous les forums seront vérifiés. Si vous avez beaucoup de forums, il serait préférable de mettre cette option sur Non.',
	'ACP_SMARTFEED_APACHE_HTACCESS_ENABLED'						=> 'Apache .htaccess activé pour Smartfeed',
	'ACP_SMARTFEED_APACHE_HTACCESS_ENABLED_EXPLAIN'				=> 'Si vous utilisez l’authentification Apache, vous devez d’abord corriger manuellement le fichier .htaccess de votre forum pour autoriser le programme app.' . $phpEx . '/smartfeed/feed de contourner l’authentification. Sinon, il est impossible à Smartfeed de fonctionner, car l’authentification Apache l’en empêche. Si vous avez corrigé votre fichier .htaccess, vous pouvez définir ce paramètre à Oui. Bien entendu, si vous n’utilisez pas l’authentification Apache, ce paramètre est ignoré.',
	'ACP_SMARTFEED_AUTO_ADVERTISE_PUBLIC_FEED'					=> 'Auto-annoncez vos flux publics',
	'ACP_SMARTFEED_AUTO_ADVERTISE_PUBLIC_FEED_EXPLAIN'			=> 'Réglez sur vrai si vous voulez exposer votre flux public automatiquement. Cela ajoute des balises dans les en-têtes HTML contenant des URL aux flux publics Atom et RSS par défaut.',
	'ACP_SMARTFEED_DEFAULT_FETCH_TIME_LIMIT'					=> 'Période maximale d’affichage',
	'ACP_SMARTFEED_DEFAULT_FETCH_TIME_LIMIT_EXPLAIN'			=> 'Fixe en heures un point dans le temps au-delà duquel aucun message ne peut être récupéré. Sinon, sur les forums à fort trafic, il faudrait des minutes ou des heures pour assembler un flux d’informations, ce qui pourrait avoir un impact sur les autres membres du forum. La valeur par défaut est de 30 jours en arrière (720 heures). <em>Attention</em>: si vous mettez cette valeur à zéro, vous pourriez donner à tous les membres la permission de créer un flux avec des centaines ou des milliers de messages.',
	'ACP_SMARTFEED_EXCLUDE_FORUMS'								=> 'Toujours exclure ces forums',
	'ACP_SMARTFEED_EXCLUDE_FORUMS_EXPLAIN'						=> 'Entrez l’identifiant (ID) des forums qui ne doivent jamais apparaître dans le flux d’informations. Séparez les par des virgules. Si cette case est laissé vide, aucun forum ne sera exclu. Pour déterminer l’identifiant, lorsque vous naviguez sur un forum, observez le paramètre &ldquo;f&rdquo; dans le champ URL. Exemple: http://www.example.com/phpBB3/viewforum.php?f=1. N’utilisez pas les identifiants de forum qui correspondent à des catégories car elle ne peuvent pas être sélectionnées avec Smartfeed. Notez que par défaut Smartfeed interdit à toute personne de lire les forums pour lesquels elle ne dispose pas de privilèges de lecture.',
	'ACP_SMARTFEED_EXTERNAL_FEEDS'								=> 'Flux externes',
	'ACP_SMARTFEED_EXTERNAL_FEEDS_EXPLAIN'						=> 'Saisissez les adresses URL des flux externes que vous souhaitez voir apparaître dans le flux d’informations, en plaçant chaque URL sur une ligne distincte. Les flux externes apparaissent dans l’ordre dans lequel ils ont été saisis. Note : le cas échéant, les règles de filtrage qui s’appliquent aux messages s’appliquent également aux éléments des flux externes. En particulier, si la date de publication de l’article ne se situe pas dans la plage de dates du flux (par exemple sept jours), ces articles n’apparaîtront pas. Ce champ ne peut pas contenir plus de 255 caractères.',
	'ACP_SMARTFEED_EXTERNAL_FEEDS_TOP'							=> 'Flux externes au dessus',
	'ACP_SMARTFEED_EXTERNAL_FEEDS_TOP_EXPLAIN'					=> 'Si vous sélectionnez Non, les éléments de flux externes se trouveront à la fin. Les messages privés, s’il y en a, apparaîtront toujours en premier.',
	'ACP_SMARTFEED_FEED_IMAGE_PATH'								=> 'Chemin du fichier de l’image de flux',
	'ACP_SMARTFEED_FEED_IMAGE_PATH_EXPLAIN'						=> 'Le chemin vers l’image que vous souhaitez voir apparaître dans le flux pour personnaliser votre flux. L’image par défaut est site_logo.gif, qui correspond au logo phpBB (ou l’image que vous lui avez substituée). Le dossier du style par défaut sera utilisé, donc définissez le chemin relatif à partir du répertoire de style par défaut de votre forum. Le balisage apparaît uniquement dans les flux RSS 1.0 et RSS 2.0. Le fait que le logo s’affiche ou non dépend des capacités du lecteur de nouvelles utilisé.',
	'ACP_SMARTFEED_HOURS'										=> 'hrs',
	'ACP_SMARTFEED_INCLUDE_FORUMS'								=> 'Toujours inclure ces forums',
	'ACP_SMARTFEED_INCLUDE_FORUMS_EXPLAIN'						=> 'Entrez les identifiants (ID) des forums qui doivent apparaître dans tout flux de nouvelles. Séparez les par des virgules. Si vous laissez cette case vide, aucun forum ne sera inclus par défaut. Pour déterminer les ID des forums, lorsque vous naviguez sur un forum, observez le paramètre &ldquo;f&rdquo; dans le champ URL. Il s’agit de l’identifiant du forum. Exemple : http://www.example.com/phpBB3/viewforum.php?f=1. N’utilisez pas les ID qui correspondent à des catégories car elles ne peuvent pas être sélectionnées avec Smartfeed.',
	'ACP_SMARTFEED_MAX_ITEMS'									=> 'Nombre maximal d’articles autorisés dans un flux ',
	'ACP_SMARTFEED_MAX_ITEMS_EXPLAIN'							=> 'Fixe une limite supérieure du nombre d’articles autorisés dans un flux d’informations. Si égal à 0, il n’y a pas de limite. Pour les forums à fort trafic, vous devrez peut-être fixer une limite pour éviter que le forum ne se bloque.',
	'ACP_SMARTFEED_MAX_WORD_SIZE'								=> 'Nombre maximum de mots à afficher dans un message',
	'ACP_SMARTFEED_MAX_WORD_SIZE_EXPLAIN'						=> 'Aucun message dans un flux ne peut dépasser ce nombre de mots. Entrez 0 pour autoriser une taille de mot illimitée pour tout message. L’utilisateur a toujours la possibilité de limiter le nombre de mots dans un message à un nombre inférieur à la limite du forum. Avis : Pour assurer un rendu cohérent, si un message doit être tronqué, la balise sera supprimée du message.<br><em>Note:</em> les éléments dans les flux externes ne sont pas affectés.',
	'ACP_SMARTFEED_MINUTES'										=> 'min',
	'ACP_SMARTFEED_NEW_POST_NOTIFICATIONS_ONLY'					=> 'Afficher les notifications de nouveaux messages et des messages privés uniquement dans le flux',
	'ACP_SMARTFEED_NEW_POST_NOTIFICATIONS_ONLY_EXPLAIN'			=> 'Si votre contenu est très sensible, vous pouvez activer cette fonction. Si elle est activée, le flux n’affichera pas le contenu des messages ou des messages privés, mais présentera un message pour chaque sujet pour lequel il y a de nouveaux messages et une notification s’il y a de nouveaux messages privés. L’utilisateur devra se connecter au forum pour voir les nouveaux messages ou les messages privés. Notez que ce paramètre est global, donc il affectera toutes les catégories, forums et utilisateurs ainsi que les messages privés. Les noms des auteurs et les sujets des messages sont cachés mais le nom du sujet est affiché.',
	'ACP_SMARTFEED_PPT'											=> 'Contrôle de performance initial',
	'ACP_SMARTFEED_PPT_EXPLAIN'									=> 'Ajustez les principaux paramètres de performance de Smartfeed',
	'ACP_SMARTFEED_PRIVACY_MODE'								=> 'Mode de confidentialité',
	'ACP_SMARTFEED_PRIVACY_MODE_EXPLAIN'						=> 'Si défini sur Oui, les vraies adresses e-mail des auteurs ne seront pas affichées dans le flux et une fausse adresse e-mail sera substituée si nécessaire pour valider le flux. Même si ce paramètre est réglé sur Non et que le membre spécifie dans ses préférences du forum de ne pas permettre à d’autres personnes de le contacter par e-mail via le forum, alors une fausse adresse e-mail sera substituée. Dans ce mode également, les blocs de signature ne seront pas montrés aux membres publics. L’idée est d’empêcher les spammeurs d’avoir une autre façon de récolter les adresses e-mail et de maximiser la confidentialité des informations de vos membres.',
	'ACP_SMARTFEED_REQUIRE_IP_AUTHENTICATION'					=> 'Exiger une authentification par IP',
	'ACP_SMARTFEED_REQUIRE_IP_AUTHENTICATION_EXPLAIN'			=> 'En réglant ce paramètre sur Oui, on renforce la sécurité de tous les membres enregistrés en limitant la plage d’IP pour laquelle Smartfeed renvoie un flux. Vous devez activer ce paramètre si votre forum contient des informations sensibles. Si vous laissez ce paramètre sur Non, chaque membre inscrit peut décider lui-même s’il souhaite cette fonction de sécurité supplémentaire. Veuillez noter qu’à moins que votre forum n’utilise le HTTPS ou ne soit accessible via un VPN, le flux ne sera pas crypté.',
	'ACP_SMARTFEED_RFC1766_LANG'								=> 'Code de langue RFC-1766',
	'ACP_SMARTFEED_RFC1766_LANG_EXPLAIN'						=> 'Langue du contenu de votre flux. Cette langue est utilisée dans les flux ATOM et RSS 2.0. <a href="http://www.w3.org/TR/REC-html40/struct/dirlang.html#langcodes" target="_blank">Liste des codes valides</a>.',
	'ACP_SMARTFEED_SECURITY'									=> 'Paramètres de sécurité',
	'ACP_SMARTFEED_SECURITY_EXPLAIN'							=> 'Ajustez les paramètres de sécurité de Smartfeed',
	'ACP_SMARTFEED_SHOW_SESSIONS'								=> 'Montrez les sessions Smartfeed',
	'ACP_SMARTFEED_SHOW_SESSIONS_EXPLAIN'						=> 'Si défini sur oui, les informations de la rubrique "Qui est en ligne" seront trompeuses car des personnes ou des invités qui accèdent à un flux apparaîtront en ligne. La plupart des sessions Smartfeed apparaîtront en tant que sessions d’invités. Le réglage de cette option sur Oui peut également fausser la valeur du plus grand nombre d’utilisateurs en ligne.',
	'ACP_SMARTFEED_SHOW_USERNAME_IN_FIRST_TOPIC_POST'			=> 'Afficher le nom du membre dans le premier message du sujet',
	'ACP_SMARTFEED_SHOW_USERNAME_IN_FIRST_TOPIC_POST_EXPLAIN'	=> 'Définir à Oui si vous voulez que le nom du membre apparaisse dans le titre du premier message d’un sujet. Vous pouvez aussi definir ce paramètre sur Non si vous voulez cannibaliser votre propre flux ailleurs, par exemple pour afficher une liste de nouveaux sujets sur la page principale de votre site web. Notez que la valeur de l’auteur de l’article est toujours définie, mais tous les lecteurs de nouvelles ne l’afficheront pas.',
	'ACP_SMARTFEED_SHOW_USERNAME_IN_REPLIES'					=> 'Afficher le nom du membre dans les réponses',
	'ACP_SMARTFEED_SHOW_USERNAME_IN_REPLIES_EXPLAIN'			=> 'Si vous réglez cette option sur Non, les noms des membres n’apparaîtront pas dans les réponses des sujets. Cela pourrait être imprudent car le lecteur de nouvelles peut choisir de ne pas montrer les auteurs des articles individuels dans le flux, de sorte qu’il serait difficile pour les lecteurs de savoir qui a publié la réponse.',
	'ACP_SMARTFEED_SUPPRESS_FORUM_NAMES'						=> 'Supprimer les noms de forum',
	'ACP_SMARTFEED_SUPPRESS_FORUM_NAMES_EXPLAIN'				=> 'Par défaut, le nom du forum apparaît dans le titre de l’article. Cependant, les noms de forum peuvent être très longs et lorsqu’il est joint au nom du sujet, le titre de l’article peut être très long. Pour supprimer le nom du forum apparaissant dans le titre de l’article pour tous les membres, réglez cette valeur sur Oui.',
	'ACP_SMARTFEED_TITLE'										=> 'Paramètres Smartfeed',
	'ACP_SMARTFEED_TTL'											=> 'Durée de vie du flux d’informations (TTL)',
	'ACP_SMARTFEED_TTL_EXPLAIN'									=> 'Combien de minutes le lecteur de nouvelles doit-il mettre le flux en cache avant de le rafraîchir ? Augmentez le nombre de minutes si votre forum est surchargé, mais les lecteurs de nouvelles peuvent ignorer vos conseils. Notez qu’il s’agit d’une fonctionnalité de RSS 2.0 uniquement, et que les lecteurs de nouvelles individuels peuvent ignorer ce paramètre.',
	'ACP_SMARTFEED_WEBMASTER'									=> 'Adresse électronique du webmestre',
	'ACP_SMARTFEED_WEBMASTER_EXPLAIN'							=> 'Si vous le souhaitez, entrez l’adresse électronique du webmaster du forum ou de la personne qui s’occupe des questions relatives aux flux. L’adresse électronique apparaîtra dans les flux RSS 2.0. Pour une interopérabilité maximale, inclure le nom de la personne associée à l’adresse e-mail entre parenthèses, ex : jjones@example.com (John Jones).',
	'LOG_CONFIG_SMARTFEED_EXTFEED'								=> '<strong>Smartfeed a demandé le flux externe &ldquo;%s&rdquo; qui est mauvais ou ne peut pas être analysé comme un flux.</strong>',));
