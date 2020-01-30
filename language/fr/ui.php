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

global $phpbb_container;

// Encapsulate certain phpBB objects inside this class to minimize security issues
$this->config = $phpbb_container->get('config');

$lang = array_merge($lang, array(
	'SMARTFEED_ADDITIONAL_CRITERIA'						=> 'Critères supplémentaires',
	'SMARTFEED_ALL_FORUMS'								=> 'Tous',
	'SMARTFEED_APACHE_AUTHENTICATION_WARNING_ADMIN'		=> 'Smartfeed ne peut pas être utilisé avec l’authentification Apache tant que l’administrateur n’a pas adapté le fichier .htaccess du forum. L’administrateur doit ajuster ce fichier puis modifier les paramètres Smartfeed dans le panneau de configuration de l’administration.',
	'SMARTFEED_ATOM_10'									=> 'ATOM 1.0',
	'SMARTFEED_EXPLANATION'								=> 'Avec Smartfeed, vous pouvez créer votre propre flux d’informations personnalisé à partir des messages de ce forum et les lire avec le lecteur de nouvelles de votre choix. Smartfeed prend en charge l’authentification des membres enregistrés, leur permettant de consulter des messages dans des forums que d’autres personnes ne peuvent pas lire.',
	'SMARTFEED_FEED_STYLING'							=> 'Style du flux d’informations',
	'SMARTFEED_FEED_STYLING_EXPLAIN'					=> 'Veuillez noter que la quantité de style de flux réellement appliquée dépend des capacités de votre lecteur de nouvelles. La plupart des lecteurs de nouvelles n’appliquent pas tous les styles HTML. Placez votre curseur sur le texte du style pour en savoir plus.<br><em>Note</em> : le style ne s’applique pas aux éléments du flux qui proviennent de flux externes requis par l’administrateur.',
	'SMARTFEED_FILTER_CRITERIA'							=> 'Filtres des messages',
	'SMARTFEED_FILTER_FOES'								=> 'Retirer les messages des personnes bloquées',
	'SMARTFEED_FORMAT_AND_ACCESS'						=> 'Format et contrôle d’accès',
	'SMARTFEED_FORUM_SELECTION'							=> 'Sélection des forums',
	'SMARTFEED_GENERATE_BUTTON'							=> 'Générer l’URL',
	'SMARTFEED_GENERATE_BUTTON_EXPLAIN'					=> 'Cliquez sur l’URL pour la copier. La totalité du texte sera automatiquement sélectionné. Ensuite, faites un coller (CTRL+V) dans votre lecteur de nouvelles.',
	'SMARTFEED_GENERATE_URL_TEXT'						=> 'Générer',
	'SMARTFEED_IP_AUTH'									=> 'Authentification IP',
	'SMARTFEED_IP_AUTHENTICATION'						=> 'Authentification IP',
	'SMARTFEED_IP_AUTHENTICATION_EXPLAIN'				=> 'Votre adresse IP actuelle est %s. Si vous sélectionnez Oui, l’URL générée fera fonctionner le flux uniquement pour cette plage d’IP.',
	'SMARTFEED_LAST_1_HOURS'							=> 'Dernière heure',
	'SMARTFEED_LAST_12_HOURS'							=> '12 dernières heures',
	'SMARTFEED_LAST_15_MINUTES'							=> '15 dernières minutes',
	'SMARTFEED_LAST_3_HOURS'							=> ' 3 dernières heures',
	'SMARTFEED_LAST_30_MINUTES'							=> '30 dernières minutes',
	'SMARTFEED_LAST_6_HOURS'							=> ' 6 dernières heures',
	'SMARTFEED_LAST_DAY'								=> '24 dernières heures',
	'SMARTFEED_LAST_MONTH'								=> '30 derniers jours',
	'SMARTFEED_LAST_QUARTER'							=> '90 derniers jours',
	'SMARTFEED_LAST_TWO_WEEKS'							=> '14 derniers jours',
	'SMARTFEED_LAST_WEEK'								=> ' 7 derniers jours',
	'SMARTFEED_LASTVISIT_RESET'							=> 'Réinitialiser la date de ma dernière visite sur l’accès au flux d’informations',
	'SMARTFEED_LIMIT'									=> 'Délai',
	'SMARTFEED_LIMIT_EXPLAIN'							=> 'Inclure dans le flux d’informations les messages couvrant cette période jusqu’à aujourd’hui.',
	'SMARTFEED_LIMIT_SET_EXPLAIN'						=> 'Indépendamment des périodes indiquées, ce tableau a une limite fixée de %d jours au-delà desquels aucun message ne peut être récupéré. Ceci est nécessaire pour s’assurer que la récupération des flux ne ralentit pas l’accès global à ce forum.',
	'SMARTFEED_MARK_READ'								=> 'Marquer les messages privés comme lus lorsqu’ils apparaissent dans le flux',
	'SMARTFEED_MAX_ITEMS'								=> 'Nombre maximum d’articles dans le flux ',
	'SMARTFEED_MAX_ITEMS_EXPLAIN'						=> 'Le nombre maximum de messages dans un flux autorisé par l’administrateur du forum est de %d. S’il est égal à 0, pas de limite au nombre d’articles autorisés. Si vous indiquez 0, la limite du forum sera utilisée si elle est fixée.',
	'SMARTFEED_MAX_ITEMS_EXPLAIN_BLANK'					=> 'Si égal à 0, un nombre quelconque d’articles peut se trouver dans le flux.',
	'SMARTFEED_MAX_WORD_SIZE'							=> 'Nombre maximum de mots à afficher dans un message ou un message privé',
	'SMARTFEED_MAX_WORD_SIZE_EXPLAIN'					=> 'Si égal à 0, un message ou un message privé peut être de n’importe quelle taille dans le flux jusqu’à la limite du forum, le cas échéant.<br><em>Note</em>: S’il n’est pas égale à 0, alors pour assurer un rendu cohérent, si un message ou un message privé doit être tronqué, le HTML sera supprimé. Un nombre maximum de %d mots est autorisé.',
	'SMARTFEED_MAX_WORD_SIZE_EXPLAIN_BLANK'				=> 'Si égal à 0, un message ou un message privé peut être de n’importe quelle taille dans le flux jusqu’à la limite du forum, le cas échéant.<br><em>Note</em>: S’il n’est pas égale à 0, alors pour assurer un rendu cohérent, si un message ou un message privé doit être tronqué, le HTML sera supprimé.',
	'SMARTFEED_MIN_WORDS'								=> 'Minimum de mots requis dans un message pour apparaître dans un flux',
	'SMARTFEED_MIN_WORDS_EXPLAIN'						=> 'Si égal à 0, il n’y a pas de nombre minimum de mots requis. Cette limite ne s’applique pas aux messages privés.',
	'SMARTFEED_NEW_PMS_NOTIFICATIONS_ONLY'				=> 'Vous avez de nouveaux messages privés sur le forum. Veuillez vous connecter au forum pour les lire.',
	'SMARTFEED_NO_FORUMS_SELECTED'						=> 'Vous n’avez sélectionné aucun forum, donc aucune URL ne peut être générée. Veuillez sélectionner au moins un forum.',
	'SMARTFEED_NO_LIMIT'								=> 'Aucun',
	'SMARTFEED_NO_OPENSSL_SUPPORT'						=> '<strong>Note: Ce site peut fournir des flux pour les forums publics uniquement</strong>',
	'SMARTFEED_NOT_LOGGED_IN'							=> '<strong>Comme vous n’êtes pas connecté et que l’authentification OAuth est utilisée ou que votre statut de membre ne le permet pas, vous ne pouvez vous inscrire qu’à la liste des forums publics présentée ci-dessous. Veuillez <a href="%s" class="postlink">vous connecter</a> ou <a href="%s" class="postlink">vous enregistrer</a> si vous souhaitez également vous abonner à des forums non publics ou accéder à des fonctionnalités réservées aux membres enregistrés.</strong>',
	'SMARTFEED_POSTS_TYPE_ANY'							=> 'Tous les messages',
	'SMARTFEED_POSTS_TYPE_FIRST'						=> 'Premier message des sujets seulement',
	'SMARTFEED_POSTS_TYPE_LAST'							=> 'Dernier message des sujets seulement',
	'SMARTFEED_POWERED_BY'								=> 'phpbbservices.com',
	'SMARTFEED_PRIVATE_MESSAGES_IN_FEED'				=> 'Ajouter mes messages privés non lus',
	'SMARTFEED_REMOVE_YOURS'							=> 'Retirer mes messages',
	'SMARTFEED_RSS_10'									=> 'RSS 1.0 (RDF)',
	'SMARTFEED_RSS_20'									=> 'RSS 2.0',
	'SMARTFEED_SELECT_FORUMS'							=> 'Inclure les messages pour ces forums',
	'SMARTFEED_SELECT_FORUMS_EXPLAIN'					=> 'Les noms de forum en gras, s’il y en a, sont des forums dont l’administrateur exige l’affichage dans tout les flux de nouvelles. Vous ne pouvez pas désélectionner ces forums. Les noms des forums dont le texte est barré ne sont pas autorisés dans un flux d’informations et ne peuvent pas être sélectionnés. Si vous êtes connecté, la sélection des forums est désactivée si vous avez sélectionné "Sujets favoris uniquement".',
	'SMARTFEED_SINCE_LAST_VISIT_TEXT'					=> 'Depuis ma dernière visite',
	'SMARTFEED_SORT_BY'									=> 'Ordre de tri des messages',
	'SMARTFEED_SORT_BY_EXPLAIN'							=> 'L’ordre par défaut est l’ordre utilisé par le forum si vous ne le modifiez pas dans le panneau de contrôle de l’utilisateur. Par défaut, les messages du flux sont affichés dans l’ordre des catégories (ordre croissant), puis dans l’ordre des forums (ordre croissant) au sein des catégories, puis dans l’ordre du temps de publication du dernier sujet (ordre décroissant) au sein d’un forum et enfin dans l’ordre du temps de publication (ordre croissant) au sein d’un sujet.',
	'SMARTFEED_SORT_FORUM_TOPIC'						=> 'Ordre par défaut',
	'SMARTFEED_SORT_FORUM_TOPIC_DESC'					=> 'Ordre par défaut, avec les derniers messages en premier',
	'SMARTFEED_SORT_POST_DATE'							=> 'Du plus ancien au plus récent',
	'SMARTFEED_SORT_POST_DATE_DESC'						=> 'Du plus récent au plus ancien',
	'SMARTFEED_SORT_USER_ORDER'							=> 'Utiliser les préférences d’affichage du forum',
	'SMARTFEED_STYLE_BASIC'								=> 'De base',
	'SMARTFEED_STYLE_BASIC_EXPLAIN'						=> 'Ce style de base supprimera le formatage et le BBCode mais appliquera les signatures si cela est autorisé.',
	'SMARTFEED_STYLE_COMPACT'							=> 'Compact',
	'SMARTFEED_STYLE_COMPACT_EXPLAIN'					=> 'Ce style supprime le formatage, le BBCode, les signatures et réduit les paragraphes.',
	'SMARTFEED_STYLE_HTML'								=> 'HTML',
	'SMARTFEED_STYLE_HTML_EXPLAIN'						=> 'Ce style fournira le formatage, le BBCode et les signatures (si elles sont autorisées). Les messages ressembleront à la façon dont ils apparaissent dans le forum. Les flux HTML peuvent ne pas être validés.',
	'SMARTFEED_STYLE_HTML_SAFE'							=> '<a href="http://validator.w3.org/feed/docs/warning/SecurityRiskAttr.html" class="postlink" onclick="window.open(this.href);return false;">HTML sécurisé</a>',
	'SMARTFEED_STYLE_HTML_SAFE_EXPLAIN'					=> 'Ce style supprime les balises considérées comme dangereuses pour les lecteurs de nouvelles, selon le W3C.',
	'SMARTFEED_SUPPRESS_FORUM_NAMES'					=> 'Supprimer les noms de forum',
	'SMARTFEED_SUPPRESS_FORUM_NAMES_EXPLAIN'			=> 'Conserve le nom du forum apparaissant dans le titre de l’article, ce qui donne un titre d’article plus succinct.',
	'SMARTFEED_SUPPRESS_USERNAMES'						=> 'Supprimer les noms d’utilisateur',
	'SMARTFEED_SUPPRESS_USERNAMES_EXPLAIN'				=> 'Le flux n’indiquera pas le nom de la personne dans le titre de l’article. Ceci est utile pour rendre les titres de flux plus attrayants.',
	'SMARTFEED_TITLE'									=> 'Smartfeed',
	'SMARTFEED_TOPIC_TITLES'							=> 'Titres des sujets uniquement',
	'SMARTFEED_TOPIC_TITLES_EXPLAIN'					=> 'Le flux affichera le titre du sujet plutôt que le sujet du message. Cela évite que beaucoup de "Re :" n’apparaissent dans le titre du fil de nouvelles.',
	'SMARTFEED_URL'										=> 'Générer et visualiser les flux',
	'SMARTFEED_USE_BOOKMARKS'							=> 'Sujets favoris uniquement',
	'SMARTFEED_VALID_ATOM_1'							=> 'Smartfeed génère des flux ATOM 1.0 validés, tel que testé par le service de validation de balisage W3C (https://validator.w3.org).',
	'SMARTFEED_VALID_RSS_1'								=> 'Smartfeed génère des flux RSS 1.0 validés, tel que testé par le service de validation de balisage W3C (https://validator.w3.org).',
	'SMARTFEED_VALID_RSS_2'								=> 'Smartfeed génère des flux RSS 2.0 validés, tel que testé par le service de validation de balisage W3C (https://validator.w3.org).',
	'SMARTFEED_VIEW_FEED'								=> 'Afficher le flux',
	'SMARTFEED_VIEW_FEED_BUTTON'						=> 'Afficher le flux',
	'SMARTFEED_VIEW_FEED_BUTTON_EXPLAIN'				=> 'Vous permet d’inspecter le code source du flux généré dans une fenêtre.',
));
