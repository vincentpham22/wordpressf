<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en « wp-config.php » et remplir les
 * valeurs.
 *
 * Ce fichier contient les réglages de configuration suivants :
 *
 * Réglages MySQL
 * Préfixe de table
 * Clés secrètes
 * Langue utilisée
 * ABSPATH
 *
 * @link https://fr.wordpress.org/support/article/editing-wp-config-php/.
 *
 * @package WordPress
 */
define( 'WP_MEMORY_LIMIT', '256M' );
// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', 'wordpress1' );

/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', 'root' );

/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', '' );

/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', 'localhost' );

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/**
 * Type de collation de la base de données.
 * N’y touchez que si vous savez ce que vous faites.
 */
define( 'DB_COLLATE', '' );

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clés secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '0[[i4*DQj^Pr-9/ DjQCHv+p?Mv4On8R.Q`/%Cc.tvBb$8G7Jk7 K3{$Ptz:{Z+t' );
define( 'SECURE_AUTH_KEY',  'c`&Uk(p|d<hHC&>E`%^g,-&_/x.k? d/(m#Q~<ldUW8froIKLy&>8sjbyq11z%3%' );
define( 'LOGGED_IN_KEY',    ']Dg{ W-0(t91}X:~49.@/d:hru@~LyXD==awI{&%tf,+d7Va|h+v@xoY|A8t(l%,' );
define( 'NONCE_KEY',        '=lv@A|}f4qAo+h4^V+5}qs5t*<^]5f7<~4/aG;l7fNgv3;n_f}iXCUdRE{0=R|qx' );
define( 'AUTH_SALT',        'O^OjpHJ:dd-jG;#BCT2Rs:+aTZQ{M#.csc|fsPO[{n.^4!*C30&*%|MCNxWJ~`j6' );
define( 'SECURE_AUTH_SALT', 'mqA`K.T2?=oH{(fR{j=X8_:gP]bEzlfnM4qv<;#06Chl32zGFd,Py WfLdagn7[c' );
define( 'LOGGED_IN_SALT',   '8=~Nm54KnE^t36Acz`g%2>=?X2QoU)2B`EHkbJl!CXQ*96$n]@n[YzNt(|?*qS}_' );
define( 'NONCE_SALT',       'qpmH)ufUL&h*2SgZTfsCC$n/I,^a8H|T$$%hb3u-eoi*_9}=3RXH+[yjS1htgp|N' );
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortement recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://fr.wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( ! defined( 'ABSPATH' ) )
  define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once( ABSPATH . 'wp-settings.php' );
