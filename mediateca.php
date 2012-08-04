<?php
/*
Plugin Name: Mediateca
Plugin URI: http://dito.areato.org
Description: displays and manages madiateca content
Version: 0.1
Author: Riccardo, Paolo, Stefano
Author URI: http://foo.bar
License: A "Slug" license name e.g. GPL2
*/
define('MEDIATECA_PATH', WP_PLUGIN_DIR . '/mediateca/');
if(!defined('MEDIATECA_URL')) define('MEDIATECA_URL', get_bloginfo('url').'/wp-content/plugins/mediateca/');
if(!defined('MEDIATECA_JS_URL')) define('MEDIATECA_JS_URL', MEDIATECA_URL.'/js/');
define('MEDIATECA_SLUG', 'mediateca');
define('HARDWARE_TYPE', 'hardware');
define('SOFTWARE_TYPE', 'software');
define('LIBRI_TYPE', 'libri');
define('POSTS_PER_PAGE', get_option('posts_per_page'));
define('MEDIATECA_CATS', 'categoria');
define('MEDIATECA_SUB_CATS', 'sotto-categoria');
define('MEDIATECA_THIRD_LEVEL', 'terzo-livello');
//The name / slug of the template page to show the loop of the component
define('MEDIATECA_LAND_PAGE', 'mediateca');
define('LIBRI_SLUG', 'libri');
define('HARDWARE_SOFTWARE_SLUG', 'hardware-e-software');
define('MEDIATECA_TD', 'mediateca');

require_once 'conf/class_loader.php';

add_action('init', 'mediatecaCreateInstances', 11);

function mediatecaCreateInstances()
{
	global $mediatecaInit, $mediatecaAdmin, $batchMediateca;
	//class is singleton
	$mediatecaInit = Mediateca_Init::getInstance();
	$mediatecaAdmin = Mediateca_Admin::getInstance();
	//$batchMediateca = new Batch_Mediateca();
}
?>