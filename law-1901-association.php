<?php
/**
 * Plugin Name: Law 1901 association
 * Description: Handle association members
 * Version: 1.0.4
 * Domain Path: /languages/
 * Author: Regis Grison - regis@grison.pro
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP: 7.1
 *
 * @package Law_1901_Association
 */

// No direct access to script.
defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
require_once __DIR__ . '/class/class-law-1901-association-member-list-table.php';
require_once __DIR__ . '/class/class-law-1901-association.php';

new Law_1901_Association();


/*
Addon 1 :

- il faudra une page pour cocher dans les champs ACF ce qu'on veut afficher dans la liste des membres,
	ce qu'on veut afficher dans la page profil à destination du bureau et à destination des membres eux-mêmes

- personnaliser l'accueil aussi

Addon 2 :

- il faut voir la question du paiement en ligne

Addon 3 :

- envoi de rappels pour cotisation (avant/après la date)

- envoi d'invitation pour AG ou autre

*/
