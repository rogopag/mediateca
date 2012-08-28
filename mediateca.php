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
define('MEDIATECA_TEMPLATE_PATH', WP_PLUGIN_DIR . '/mediateca/templates/');
if(!defined('MEDIATECA_URL')) define('MEDIATECA_URL', get_bloginfo('url').'/wp-content/plugins/mediateca/');
if(!defined('MEDIATECA_JS_URL')) define('MEDIATECA_JS_URL', MEDIATECA_URL.'/js/');
define('POSTS_PER_PAGE', get_option('posts_per_page'));
define('MEDIATECA_CATS', 'categoria');
define('MEDIATECA_SUB_CATS', 'sotto-categoria');
//Post type and template stuff
define('HARDWARE_TYPE', 'hardware');
define('SOFTWARE_TYPE', 'software');
define('MEDIATECA_THIRD_LEVEL', 'terzo-livello');
define('LIBRI_TYPE', 'libri');
define('MEDIATECA_LAND_PAGE', 'mediateca');
define('MEDIATECA_SLUG', 'mediateca');
define('LIBRI_SLUG', 'libri');
define('HARDWARE_SOFTWARE_SLUG', 'hardware-e-software');
define('MEDIATECA_TD', 'mediateca');
define('MEDIATECA_RESULTS_PAGE', 'results');
define('MEDIATECA_TEXT_SEARCH', 'search');
define('MEDIATECA_HARDWARE_AND_SOFTWARE_SEARCH', 'hardware-e-software-search');

require_once 'conf/class_loader.php';

register_activation_hook( __FILE__, 'createPluginPages');

add_action('init', 'mediatecaCreateInstances', 11);

function mediatecaCreateInstances()
{
	global $mediatecaInit, $mediatecaAdmin, $batchMediateca, $mediatecaRender;
	//class is singleton
	$mediatecaInit = Mediateca_Init::getInstance();
	//class is singleton
	$mediatecaAdmin = Mediateca_Admin::getInstance();
	$batchMediateca = new Batch_Mediateca();
	$mediatecaRender = new Mediateca_Render();
}

function createPluginPages()
	{
		global $current_user;
		
		get_currentuserinfo();
		
		$user = $current_user;
		
		$pages = array(HARDWARE_SOFTWARE_SLUG, LIBRI_SLUG);
		
		if( !get_page_by_title( ucfirst(MEDIATECA_SLUG) ) )
		{
			$postdata = array(
			'post_title' => __(ucfirst(MEDIATECA_SLUG), MEDIATECA_TD),
			'post_content' => __('This is your '.ucfirst(MEDIATECA_SLUG).' page!', MEDIATECA_TD),
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_author' => $user->ID,
			'ping_status' => get_option('default_ping_status'), 
			'post_name' => MEDIATECA_SLUG,
			'post_parent' => 0,
			'menu_order' => 0,
			'to_ping' =>  '',
			'pinged' => '',
			'post_password' => '',
			'guid' => '',
			'post_content_filtered' => '',
			'post_excerpt' => '',
			'import_id' => 0
			);
		
			$parent = wp_insert_post( $postdata, true );
		}
		else
		{
			$parent = get_page_by_title( ucfirst(MEDIATECA_SLUG) );
		}
		
			
		foreach( $pages as $slug )
		{
			if( !get_page_by_title( ucfirst( str_replace('-', ' ', $slug) ) ) )
			{
				$name = ucfirst( str_replace('-', ' ', $slug) );
				
				$postdata = array(
				'post_title' => __($name, MEDIATECA_TD),
				'post_content' => __('This is your '.$name.' page!', MEDIATECA_TD),
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_author' => $user->ID,
				'ping_status' => get_option('default_ping_status'), 
				'post_name' => $slug,
				'post_parent' => $parent,
				'menu_order' => 0,
				'to_ping' =>  '',
				'pinged' => '',
				'post_password' => '',
				'guid' => '',
				'post_content_filtered' => '',
				'post_excerpt' => '',
				'import_id' => 0
				);
				wp_insert_post( $postdata, true );
			}
		}
	}

?>