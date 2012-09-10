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
	private $types;
	public $meta_prefix;
	
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
		add_filter( 'cmb_meta_boxes', array(&$this, 'addHardwareAndSoftwareMetaBoxes' ) );
		add_filter( 'cmb_meta_boxes', array(&$this, 'addLibriMetaBoxes' ) );
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
			remove_meta_box ( 'postcustom', $type, 'normal' );
			remove_meta_box ( 'powerpress-podcast', $type, 'normal' );
			remove_meta_box ( 'commentstatusdiv', $type, 'normal' );
			remove_meta_box ( 'categoriadiv', $type, 'side' );
			remove_meta_box ( 'tagsdiv-sezione', $type, 'side' );
			remove_meta_box ( 'terzo-livellodiv', $type, 'side' );
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
				'id' => 'descrizione_metabox',
				'title' => 'Descrizione del volume',
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
						'name' => 'Autore/i',
						'desc' => 'Autore/i della pubblicazione',
						'id' => $this->meta_prefix . 'autori',
						'type' => 'text'
					),
					array(
						'name' => 'Illustratore/i',
						'desc' => 'Illustratore/i della pubblicazione',
						'id' => $this->meta_prefix . 'illustratori',
						'type' => 'text'
					),
					array(
						'name' => 'Editore',
						'desc' => 'Editore della pubblicazione',
						'id' => $this->meta_prefix . 'editore',
						'type' => 'text_medium'
					),
					array(
						'name' => 'Collana',
						'desc' => 'Collana di pubblicazione',
						'id' => $this->meta_prefix . 'collana',
						'type' => 'text_medium'
					),
					array(
						'name' => 'Distributore',
						'desc' => 'Distributore della pubblicazione',
						'id' => $this->meta_prefix . 'distributore',
						'type' => 'text_medium'
					),
					array(
						'name' => 'ISBN',
						'desc' => 'ISBN',
						'id' => $this->meta_prefix . 'ISBN',
						'type' => 'text_medium'
					),
					array(
						'name' => 'Anno',
						'desc' => 'Anno di pubblicazione',
						'id' => $this->meta_prefix . 'anno',
						'type' => 'text_date'
					),
					array(
						'name' => 'Numero di pagine',
						'desc' => 'Numero di pagine',
						'id' => $this->meta_prefix . 'numero-di-pagine',
						'type'    => 'text_small',
					),
					array(
						'name' => 'Prezzo',
						'desc' => 'Prezzo della pubblicazione',
						'id' => $this->meta_prefix . 'prezzo',
						'type' => 'text_money'
					),
					array(
						'name' => 'Tipologia di libro',
						'desc' => 'Tipologia di libro',
						'id' => $this->meta_prefix . 'tipo-di-libro',
						'taxonomy' => 'tipo-di-libro', //Enter Taxonomy Slug
						'type' => 'taxonomy_select'
					),
					array(
						'name' => 'Fascia di et&agrave;',
						'desc' => 'Fascia di et&agrave;',
						'id' => $this->meta_prefix . 'eta',
						'taxonomy' => 'eta', //Enter Taxonomy Slug
						'type' => 'hierarchical_checkboxes'
					),
				),
			);
			
			$meta_boxes[] = array(
					'id' => 'accesibilita_metabox',
					'title' => 'Accessibilit&agrave; del volume',
					'pages' => $types, // post type
					'context' => 'normal',
					'priority' => 'high',
					'show_names' => true, // Show field names on the left
					'fields' => array(
					array(
						'name' => 'Accessibilit&agrave; primaria',
						'desc' => 'Handicap preso in considerazione',
						'id' => $this->meta_prefix . 'accessibilita-primaria',
						'taxonomy' => 'tipo-di-handicap',
						'type' => 'hierarchical_checkboxes'
					),
					array(
						'name' => 'Accessibilit&agrave; secondaria',
						'desc' => 'Tipo di Accessibilit&agrave; secondaria coperta dalla publicazione e non direttamente prevista dagli autori/editori',
						'id' => $this->meta_prefix . 'accessibilita-secondaria',
						'taxonomy' => 'accessibilita-secondaria',
						'type' => 'hierarchical_checkboxes'
					),
					array(
						'name' => 'Tipo di difficolt&agrave; compensata',
						'desc' => 'Tipo di difficolt&agrave; compensata',
						'id' => $this->meta_prefix . 'difficolta-compensata',
						'taxonomy' => 'difficolta-compensata', //Enter Taxonomy Slug
						'type' => 'hierarchical_checkboxes'
					),
				),
			);
			
			$meta_boxes[] = array(
					'id' => 'descrizione_supporto_metabox',
					'title' => 'Descrizione del supporto',
					'pages' => $types, // post type
					'context' => 'normal',
					'priority' => 'high',
					'show_names' => true, // Show field names on the left
					'fields' => array(
					array(
						'name' => 'Formato',
						'desc' => 'Formato di impaginazione del volume',
						'id' => $this->meta_prefix . 'formato',
						'type'    => 'radio_inline',
						'options' => array(
							array( 'name' => 'Piu piccolo di un A4', 'value' => 'Piu piccolo di un A4', 'checked' => 1),
							array( 'name' => 'A4', 'value' => 'A4', ),
							array( 'name' => 'Piu grande di un A4', 'value' => 'Piu grande di un A4', ),
						),
					),
					array(
						'name' => 'Materiale di base',
						'desc' => 'Materiale di base',
						'id' => $this->meta_prefix . 'materiale-di-base',
						'taxonomy' => 'materiale-di-base', //Enter Taxonomy Slug
						'type' => 'hierarchical_checkboxes'
					),
					array(
						'name' => 'Forma delle pagine',
						'desc' => 'Forma delle pagine',
						'id' => $this->meta_prefix . 'forma-delle-pagine',
						'type'    => 'radio_inline',
						'options' => array(
							array( 'name' => 'Regolare', 'value' => 'Regolare', 'checked' => 1),
							array( 'name' => 'Irregolare', 'value' => 'Irregolare', ),
						),
					),
					array(
						'name' => 'Presenza di dispositivi per aiutare a sfogliare le pagine',
						'desc' => 'Presenza di dispositivi per aiutare a sfogliare le pagine',
						'id' => $this->meta_prefix . 'dispositivi-di-aiuto',
						'type'    => 'radio_inline',
						'options' => array(
							array( 'name' => 'Si', 'value' => 1, ),
							array( 'name' => 'No', 'value' => 0, ),
						),
					),
					array(
						'name' => 'Presenza di elementi mobili',
						'desc' => 'Presenza di elementi mobili',
						'id' => $this->meta_prefix . 'elementi-mobili',
						'type'    => 'radio_inline',
						'options' => array(
							array( 'name' => 'Si', 'value' => 1, ),
							array( 'name' => 'No', 'value' => 0, ),
						),
					),
					array(
						'name' => 'Presenza di elementi staccabili',
						'desc' => 'Presenza di elementi staccabili',
						'id' => $this->meta_prefix . 'elementi-staccabili',
						'type'    => 'radio_inline',
						'options' => array(
							array( 'name' => 'Si', 'value' => 1, ),
							array( 'name' => 'No', 'value' => 0, ),
						),
					),
					array(
						'name' => 'Multimedia',
						'desc' => 'Presenza di assets multimediali',
						'id' => $this->meta_prefix . 'multimedia',
						'type'    => 'radio_inline',
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
						'id' => $this->meta_prefix . 'multimedia-link',
					),
				),
			);
			
			$meta_boxes[] = array(
					'id' => 'descrizione_contenuto_metabox',
					'title' => 'Descrizione del contenuto',
					'pages' => $types, // post type
					'context' => 'normal',
					'priority' => 'high',
					'show_names' => true, // Show field names on the left
					'fields' => array(
					array(
						'name' => 'Genere',
						'desc' => 'Genere',
						'id' => $this->meta_prefix . 'genere',
						'taxonomy' => 'genere', //Enter Taxonomy Slug
						'type' => 'taxonomy_select'
					),
					array(
						'name' => 'Temi trattati',
						'desc' => 'Temi trattati',
						'id' => $this->meta_prefix . 'temi-trattati',
						'taxonomy' => 'temi-trattati', //Enter Taxonomy Slug
						'type' => 'hierarchical_checkboxes'
					),
					array(
						'name' => 'Ambiente prevalente',
						'desc' => 'Ambiente prevalente',
						'id' => $this->meta_prefix . 'ambiente-prevalente',
						'taxonomy' => 'ambiente-prevalente', //Enter Taxonomy Slug
						'type' => 'hierarchical_checkboxes'
					),
					array(
						'name' => 'Personaggi',
						'desc' => 'Personaggi',
						'id' => $this->meta_prefix . 'personaggi',
						'taxonomy' => 'personaggi', //Enter Taxonomy Slug
						'type' => 'hierarchical_checkboxes'
					),
					array(
						'name' => 'Complessit&agrave; della storia',
						'desc' => 'Complessit&agrave; della storia',
						'id' => $this->meta_prefix . 'complessita-storia',
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
						'name' => 'Complessit&agrave; della storia descrizione',
						'desc' => 'Complessit&agrave; della storia descrizione',
						'id' => $this->meta_prefix . 'complessita-storia-descrizione',
						'type' => 'textarea_small'
					),
				),
			);
			
			$meta_boxes[] = array(
					'id' => 'descrizione_testo_metabox',
					'title' => 'Descrizione del testo scritto',
					'pages' => $types, // post type
					'context' => 'normal',
					'priority' => 'high',
					'show_names' => true, // Show field names on the left
					'fields' => array(
					array(
						'name' => 'Presenza del testo',
						'desc' => 'Presenza del testo',
						'id' => $this->meta_prefix . 'presenza-testo',
						'type'    => 'radio',
						'options' => array(
							array( 'name' => 'Si', 'value' => 1, ),
							array( 'name' => 'No', 'value' => 0, ),
						),
					),
					array(
						'name' => 'Codici utilizzati',
						'desc' => 'Codici utilizzati',
						'id' => $this->meta_prefix . 'codici-utilizzati',
						'taxonomy' => 'codici-utilizzati', //Enter Taxonomy Slug
						'type' => 'hierarchical_checkboxes'
					),
					array(
						'name' => 'Dimensione del carattere',
						'desc' => 'Dimensione del carattere',
						'id' => $this->meta_prefix . 'dimensione-carattere',
						'type'    => 'radio',
						'options' => array(
							array( 'name' => 'Normale', 'value' => 'Normale', 'checked' => 1),
							array( 'name' => 'Grande', 'value' => 'Grande', ),
						),
					),
					array(
						'name' => 'Font',
						'desc' => 'Maiuscolo o minuscolo',
						'id' => $this->meta_prefix . 'font',
						'type'    => 'radio',
						'options' => array(
							array( 'name' => 'Maiuscolo', 'value' => 'Maiuscolo', ),
							array( 'name' => 'Minuscolo', 'value' => 'Minuscolo', 'checked' => 1 ),
						),
					),
					array(
						'name' => 'Complessit&agrave; del testo',
						'desc' => 'Complessit&agrave; del testo',
						'id' => $this->meta_prefix . 'complessita-testo',
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
						'name' => 'Complessit&agrave; testo descrizione',
						'desc' => 'Complessit&agrave; testo descrizione',
						'id' => $this->meta_prefix . 'complessita-testo-descrizione',
						'type' => 'textarea_small'
					),
				),
			);
			
			$meta_boxes[] = array(
					'id' => 'descrizione_immagini_metabox',
					'title' => 'Descrizione delle immagini',
					'pages' => $types, // post type
					'context' => 'normal',
					'priority' => 'high',
					'show_names' => true, // Show field names on the left
					'fields' => array(
					array(
						'name' => 'Presenza di immagini',
						'desc' => 'Presenza di immagini',
						'id' => $this->meta_prefix . 'presenza-immagini',
						'type'    => 'radio_inline',
						'options' => array(
							array( 'name' => 'Si', 'value' => 1, ),
							array( 'name' => 'No', 'value' => 0, ),
						),
					),
					array(
						'name' => 'Rapporto spaziale col testo',
						'desc' => 'Rapporto spaziale col testo',
						'id' => $this->meta_prefix . 'rapporto-con-testo',
						'type' => 'multicheck',
						'options' => array(
							'Separate dal testo' => 'Separate dal testo',
							'Integrate nel testo' =>'Integrate nel testo'
						)
					),
					array(
						'name' => 'Colore',
						'desc' => 'Colore',
						'id' => $this->meta_prefix . 'colore-immagini',
						'type' => 'multicheck',
						'options' => array(
							'A colori' => 'A colori',
							'In bianco e nero' =>'In bianco e nero'
						)
					),
					array(
						'name' => 'Tecnica',
						'desc' => 'Tecnica',
						'id' => $this->meta_prefix . 'tecnica',
						'type'    => 'multicheck',
						'options' => array(
							'Stampa'  => 'Stampa',
							'Tattile' => 'Tattile',
							'Grauffage' => 'Grauffage',
							'Collage di materiali' => 'Collage di materiali',
							'Contorno in rilievo' => 'Contorno in rilievo'
						)
					),
					array(
						'name' => 'Complessit&agrave; del immagini',
						'desc' => 'Complessit&agrave; del immagini',
						'id' => $this->meta_prefix . 'complessita-immagini',
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
						'name' => 'Complessit&agrave; immagini descrizione',
						'desc' => 'Complessit&agrave; immagini descrizione',
						'id' => $this->meta_prefix . 'complessita-immagini-descrizione',
						'type' => 'textarea_small'
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