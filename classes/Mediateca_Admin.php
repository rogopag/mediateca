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
		$this->metaBoxes = add_filter( 'cmb_meta_boxes', array(&$this, 'addHardwareAndSoftwareMetaBoxes' ) );
		$foo = add_filter( 'cmb_meta_boxes', array(&$this, 'addLibriMetaBoxes' ) );
		print_r($foo);
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
	 * aaddHardwareAndSoftwareMetaBoxes() - callback function to cmb_meta_boxes
	 * @param $meta_boxes
	 * @return array
	 * @author Riccardo Strobbia
	 **/
	public function addHardwareAndSoftwareMetaBoxes( $meta_boxes )
	{
		$types = $this->types;
		
		array_pop( $types );
		
		$meta_boxes[] = array(
				'id' => 'test_metabox',
				'title' => 'Dati pubblicazione',
				'pages' => $types, // post type
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
public function addLibriMetaBoxes( $meta_boxes )
	{
		$types = array(LIBRI_TYPE);
		
		$meta_boxes[] = array(
				'id' => 'test_metabox',
				'title' => 'Dati pubblicazione',
				'pages' => $types, // post type
				'context' => 'normal',
				'priority' => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
					array(
						'name' => 'Sezione',
						'desc' => 'Sezione di appartenenza della pubblicazione',
						'id' => $this->meta_prefix . 'sezione-libri',
						'taxonomy' => 'sezione-libri', //Enter Taxonomy Slug
						'type' => 'taxonomy_radio'
					),
					array(
						'name' => 'Handicap preso in considerazione',
						'desc' => 'Handicap preso in considerazione',
						'id' => $this->meta_prefix . 'tipo-di-handicap',
						'taxonomy' => 'tipo-di-handicap',
						'type' => 'checkbox'
					),
					array(
						'name' => 'Genere',
						'desc' => 'Genere',
						'id' => $this->meta_prefix . 'genere',
						'taxonomy' => 'genere', //Enter Taxonomy Slug
						'type' => 'checkbox'
					),
					array(
						'name' => 'Tipologia di libro',
						'desc' => 'Tipologia di libro',
						'id' => $this->meta_prefix . 'tipo-di-libro',
						'taxonomy' => 'tipo-di-libro', //Enter Taxonomy Slug
						'type' => 'checkbox'
					),
					array(
						'name' => 'Fascia di et&agrave;',
						'desc' => 'Fascia di et&agrave;',
						'id' => $this->meta_prefix . 'eta',
						'taxonomy' => 'eta', //Enter Taxonomy Slug
						'type' => 'checkbox'
					),
					array(
						'name' => 'Tipo di difficolt&agrave; compensata',
						'desc' => 'Tipo di difficolt&agrave; compensata',
						'id' => $this->meta_prefix . 'difficolta-compensata',
						'taxonomy' => 'difficolta-compensata', //Enter Taxonomy Slug
						'type' => 'checkbox'
					),
					array(
						'name' => 'Personaggi',
						'desc' => 'Personaggi',
						'id' => $this->meta_prefix . 'personaggi',
						'taxonomy' => 'personaggi', //Enter Taxonomy Slug
						'type' => 'checkbox'
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
						'name' => 'Prezzo',
						'desc' => 'Prezzo della pubblicazione',
						'id' => $this->meta_prefix . 'prezzo',
						'type' => 'text_money'
					),
					array(
						'name' => 'Maneggevolezza',
						'desc' => 'Grado di difficolt&agrave; nella manipolazione',
						'id' => $this->meta_prefix . 'maneggevolezza',
						'type'    => 'select',
						'options' => array(
							array( 'name' => '1', 'value' => 1, ),
							array( 'name' => '2', 'value' => 2, ),
							array( 'name' => '3', 'value' => 3, ),
							array( 'name' => '4', 'value' => 4, ),
							array( 'name' => '5', 'value' => 5, ),
						),
					),
					array(
						'name' => 'Numero di pagine',
						'desc' => 'Numero di pagine',
						'id' => $this->meta_prefix . 'numero-di-pagine',
						'type'    => 'select',
						'options' => array(
							array( 'name' => 'meno di 10', 'value' => 'meno di 10', ),
							array( 'name' => 'tra 10 e 50', 'value' => 'tra 10 e 50', ),
							array( 'name' => 'tra 50 e 100', 'value' => 'tra 10 e 50', ),
							array( 'name' => 'pi&ugrave; di 100', 'value' => 'pi&ugrave; di 100', ),
						),
					),
					array(
						'name' => 'Complessit&agrave;',
						'desc' => 'Grado di complessit&agrave; nella fruizione',
						'id' => $this->meta_prefix . 'complessita',
						'type'    => 'select',
						'options' => array(
							array( 'name' => '1', 'value' => 1, ),
							array( 'name' => '2', 'value' => 2, ),
							array( 'name' => '3', 'value' => 3, ),
							array( 'name' => '4', 'value' => 4, ),
							array( 'name' => '5', 'value' => 5, ),
						),
					),
					array(
						'name' => 'Font',
						'desc' => 'Maiuscolo o minuscolo',
						'id' => $this->meta_prefix . 'font',
						'type'    => 'radio',
						'options' => array(
							array( 'name' => 'Maiuscolo', 'value' => 0, ),
							array( 'name' => 'Minuscolo', 'value' => 1, ),
						),
					),
					array(
						'name' => 'Dimesnione del carattere',
						'desc' => 'Dimesnione del carattere',
						'id' => $this->meta_prefix . 'dimensione-carattere',
						'type'    => 'radio',
						'options' => array(
							array( 'name' => 'Normale', 'value' => 0, ),
							array( 'name' => 'Grande', 'value' => 1, ),
						),
					),
					array(
						'name' => 'Esplorabilit&agrave;',
						'desc' => 'Grado di Esplorabilit&agrave;',
						'id' => $this->meta_prefix . 'esploralibita',
						'type'    => 'select',
						'options' => array(
							array( 'name' => '1', 'value' => 1, ),
							array( 'name' => '2', 'value' => 2, ),
							array( 'name' => '3', 'value' => 3, ),
							array( 'name' => '4', 'value' => 4, ),
							array( 'name' => '5', 'value' => 5, ),
						),
					),
					array(
						'name' => 'Multimedia',
						'desc' => 'Presenza di assets multimediali',
						'id' => $this->meta_prefix . 'multimedia',
						'type'    => 'radio',
						'options' => array(
							array( 'name' => 'Si', 'value' => 1, ),
							array( 'name' => 'No', 'value' => 0, ),
						),
					),
					array(
						'name' => 'Tipo di supporto multimediale',
						'desc' => 'Tipo di supporto multimediale',
						'id' => $this->meta_prefix . 'Multimedia type',
						'type'    => 'select',
						'options' => array(
							array( 'name' => 'nessuno', 'value' => '', ),
							array( 'name' => 'Cd Audio', 'value' => 'Cd Audio', ),
							array( 'name' => 'Cd Mp3', 'value' => 'Cd Mp3', ),
							array( 'name' => 'Mp3 scaricabile', 'value' => 'Mp3 scaricabile', ),
							array( 'name' => 'Video', 'value' => 'Video', ),
							array( 'name' => 'Scaricabile', 'value' => 'Scaricabile', ),
						),
					),
					array(
						'name' => 'Multimedia link',
						'desc' => 'Lunk all\'asset multimediale',
						'type' => 'text',
						'id' => $prefix . 'multimedia-link'
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
		if ( !empty( $field['desc'] ) ) echo '<p class="cmb_metabox_description">' . $field['desc'] . '</p>';
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