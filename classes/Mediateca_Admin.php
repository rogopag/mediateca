<?php
/**
 * Class: Mediateca_Admin - implemented as singleton
 * 
 * @package Mediateca
 * @author Riccardo Strobbia
 *
 **/
class Mediateca_Admin
{
	//this class is singleton
	private static $instance;
	private $type;
	private $types;
	public $meta_prefix;
	public $metaBoxes;
	/**
	 * Class constructor
	 * @private
	 * @return void
	 * @author Riccardo Strobbia
	 **/
	private function __construct()
	{
		$this->meta_prefix = '_mediateca_';
		$this->types = Mediateca_Init::$types;
		$this->type = ( $_GET['post_type'] ) ? $_GET['post_type'] : get_query_var('post_type');
		$this->metaBoxes = add_filter( 'cmb_meta_boxes', array(&$this, 'addMetaBoxes' ) );
		add_action('admin_menu', array(&$this, 'cleanMetaboxes') );
		add_filter( 'cmb_render_hierarchical_checkboxes', array(&$this, 'render_hierarchical_checkboxes'), 10, 2 );
		add_action( 'init', array(&$this, 'initCmbMetaBoxes'), 999 );
		add_action( 'admin_enqueue_scripts', array(&$this, 'add_admin_scripts'), 10, 1 );
	}
	function add_admin_scripts( $hook ) {
	
	    global $post;
	
	    if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
	        if ( in_array($post->post_type, $this->types) ) {     
	            wp_enqueue_style(  'mediateca_admin_style', MEDIATECA_URL.'css/css.css', '', '0.1', 'screen' );
	        }
	    }
	}
	/**
	 * cleanMetaboxes() - Cleans up default metaboxes from edit pages
	 *
	 * @return void
	 * @author Riccardo Strobbia
	 **/
	public function cleanMetaboxes()
	{
		
		foreach( $this->types as $type )
		{
			if( $type == $this->type )
			{
				remove_meta_box('postcustom', $type, 'normal');
				remove_meta_box('powerpress-podcast', $type, 'normal');
				remove_meta_box('commentstatusdiv', $type, 'normal');
				remove_meta_box('categoriadiv', $type, 'side');
				remove_meta_box('tagsdiv-sezione', $type, 'side');
				remove_meta_box('terzo-livellodiv', $type, 'side');
			}
		}
	}
	/**
	 * addMetaBoxes() - callback function to cmb_meta_boxes
	 * @param $meta_boxes
	 * @return array
	 * @author Riccardo Strobbia
	 **/
	public function addMetaBoxes( $meta_boxes )
	{
		$meta_boxes[] = array(
				'id' => 'test_metabox',
				'title' => 'Dati pubblicazione',
				'pages' => $this->types, // post type
				'context' => 'normal',
				'priority' => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
					array(
						'name' => 'Sezione',
						'desc' => 'Sezione di appartenenza della pubblicazione',
						'id' => $this->meta_prefix . 'sezione',
						'taxonomy' => 'sezione', //Enter Taxonomy Slug
						'type' => 'taxonomy_radio'
					),
					array(
						'name' => 'Categoria',
						'desc' => 'Categoria e sottocategoria (figlia) di appartenenza della pubblicazione',
						'id' => $this->meta_prefix . 'categoria',
						'taxonomy' => 'categoria', //Enter Taxonomy Slug
						'type' => 'hierarchical_checkboxes'
					),
					array(
						'name' => 'Terzo livello',
						'desc' => 'Terzo livello di filtro per hardware e software',
						'id' => $this->meta_prefix . 'terzo-livello',
						'taxonomy' => 'terzo-livello', //Enter Taxonomy Slug
						'type' => 'taxonomy_select'
					),
					array(
						'name' => 'Riferimenti',
						'desc' => 'Link alla pagina della pubblicazione',
						'id' => $this->meta_prefix . 'riferimenti',
						'type' => 'text_medium'
					),
					array(
						'name' => 'collocazione',
						'desc' => 'Collocazione della pubblicazione',
						'id' => $this->meta_prefix . 'collocazione',
						'type' => 'text_small'
					),
					array(
						'name' => 'handicap',
						'desc' => 'Handicap preso in considerazione',
						'id' => $this->meta_prefix . 'handicap',
						'type' => 'text_medium'
					),
					array(
						'name' => 'Scuola',
						'desc' => 'Scuola a cui &egrave; a cui &egrave; rivolta la pubblicazione',
						'id' => $this->meta_prefix . 'scuola',
						'type' => 'text_medium'
					),
					array(
						'name' => 'Lingua',
						'desc' => 'Lingua della pubblicazione',
						'id' => $this->meta_prefix . 'lingua',
						'type' => 'text_medium'
					),
					array(
						'name' => 'Anno',
						'desc' => 'Anno di pubblicazione',
						'id' => $this->meta_prefix . 'anno',
						'type' => 'text_date'
					),
					array(
						'name' => 'Collana',
						'desc' => 'Collana di pubblicazione',
						'id' => $this->meta_prefix . 'collana',
						'type' => 'text_medium'
					),
					array(
						'name' => 'Autore/i',
						'desc' => 'Autore/i della pubblicazione',
						'id' => $this->meta_prefix . 'autori',
						'type' => 'text'
					),
					array(
						'name' => 'Distributore',
						'desc' => 'Distributore della pubblicazione',
						'id' => $this->meta_prefix . 'distributore',
						'type' => 'text_medium'
					),
					array(
						'name' => 'Editore',
						'desc' => 'Editore della pubblicazione',
						'id' => $this->meta_prefix . 'editore',
						'type' => 'text_medium'
					),
					array(
						'name' => 'Sistema',
						'desc' => 'Sistema operativo che supporta la pubblicazione (hardware e software)',
						'id' => $this->meta_prefix . 'sistema',
						'type' => 'text_medium'
					),
					array(
						'name' => 'Hardware necessario',
						'desc' => 'Hardware necessario per l\'utilizzo della pubblicazione (hardware e software)',
						'id' => $this->meta_prefix . 'hardware_necessario',
						'type' => 'text_medium'
					),
					array(
						'name' => 'Featured Image',
						'desc' => 'Indirizzo immagine importata dal vecchio DB. Non Utilizzare per nuovi inserimenti e cancellare nel caso di importazione nuova immagine da WP.',
						'id' => $this->meta_prefix . 'featured_image',
						'type' => 'text_medium'
					),
				),
			);

			return $meta_boxes;
	}
	/**
	 * render_hierarchical_taxonomy - creates a field to support hierarchical taxonomies
	 * @public
	 * @arg $field, $meta
	 * @return string
	 * @author Riccardo Strobbia
	 **/
	public function render_hierarchical_checkboxes($field, $meta)
	{
		global $post;
		$args = array(
			'descendants_and_self' => 0,
			'selected_cats' => true,
			'popular_cats' => false,
			'walker' => null,
			'taxonomy' => $field['taxonomy'],
			'checked_ontop' => true
		);
		echo '<ul class="hierarchical_checkboxes">';
		wp_terms_checklist($post->ID, $args);
		echo '</ul>';
	}
	/**
	 * render_hierarchical_taxonomy - creates a field to support hierarchical taxonomies
	 * @public
	 * @arg $field, $meta
	 * @return string
	 * @author Riccardo Strobbia
	 **/
	public function render_hierarchical_taxonomy( $field, $meta )
	{
		wp_dropdown_categories(array(
            'show_option_none' => '&#8212; Select &#8212;',
            'hierarchical' => 1,
            'taxonomy' => $field['taxonomy'],
            'orderby' => 'name', 
            'hide_empty' => 0, 
            'name' => $field['id'],
            'selected' => $meta  

        ));
        if ( !empty( $field['desc'] ) ) echo '<p class="cmb_metabox_description">' . $field['desc'] . '</p>';
	}
	/**
	 * initCmbMetaBoxes() - initialize cmb_Meta_Box class
	 *
	 * @return void
	 * @author Riccardo Strobbia
	 **/
	public function initCmbMetaBoxes()
	{
		if ( ! class_exists( 'cmb_Meta_Box' ) )
				require_once MEDIATECA_PATH.'lib/Custom-Metaboxes/init.php';
	}
	/**
	 * getInstance() - implements the class as singleton
	 * @static
	 * @return Object
	 * @author Riccardo Strobbia
	 **/
	public static function getInstance()
	{
		if(!self::$instance)
		{
			self::$instance = new Mediateca_Admin();
		}
		return self::$instance;
	}
}
?>