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
	public static $meta_boxes = array();
	
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
			remove_meta_box ( 'tagsdiv-terzo-livello', $type, 'side' );
			remove_meta_box ( 'tagsdiv-sezione-libri', $type, 'side' );
			remove_meta_box ( 'sistema-operativodiv', $type, 'side' );
			remove_meta_box ( 'etadiv', $type, 'side' );
			remove_meta_box ( 'tipo-di-handicapdiv', $type, 'side' );
			remove_meta_box ( 'accessibilita-secondariadiv', $type, 'side' );
			remove_meta_box ( 'tagsdiv-genere', $type, 'side' );
			remove_meta_box ( 'tagsdiv-tipo-di-libro', $type, 'side' );
			remove_meta_box ( 'difficolta-compensatadiv', $type, 'side' );
			remove_meta_box ( 'materiale-di-basediv', $type, 'side' );
			remove_meta_box ( 'personaggidiv', $type, 'side' );
			remove_meta_box ( 'temi-trattatidiv', $type, 'side' );
			remove_meta_box ( 'codici-utilizzatidiv', $type, 'side' );
			remove_meta_box ( 'ambiente-prevalentediv', $type, 'side' );
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
		static $count = 1;
		
		$types = $this->types;
		
		array_pop( $types );
		
		$meta_boxes[] = array(
				'id' => 'dati_metabox',
				'title' => 'Dati pubblicazione',
				'pages' => $types, // post type
				'context' => 'normal',
				'priority' => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
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
							'name' => 'Link editore/produttore',
							'desc' => 'Link alla pagina dell\'editore / produttore',
							'id' => $this->meta_prefix . 'riferimenti',
							'type' => 'text_medium'
						),
					array(
						'name' => 'Editore/distributore',
						'desc' => 'Editore/distributore della pubblicazione',
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
						'name' => 'Anno',
						'desc' => 'Anno di pubblicazione',
						'id' => $this->meta_prefix . 'anno',
						'type' => 'text_date'
					),
					array(
						'name' => 'Scuola',
						'desc' => 'Utilizzare questo valore per aggiornare fascia di et&agrave;',
						'id' => $this->meta_prefix . 'scuola',
						'type' => 'text_medium'
					),
					array(
						'name' => 'Et&agrave; consigliata',
						'desc' => 'Et&agrave; consigliata',
						'id' => $this->meta_prefix . 'eta',
						'taxonomy' => 'eta', //Enter Taxonomy Slug
						'type' => 'hierarchical_checkboxes'
					),
					array(
						'name' => 'Lingua',
						'desc' => 'Lingua della pubblicazione',
						'id' => $this->meta_prefix . 'lingua',
						'type' => 'text_medium'
					),
					array(
						'name' => 'Sistema',
						'desc' => 'Sistema operativo a cui riferirsi per dare la nuova tassonomia',
						'id' => $this->meta_prefix . 'sistema',
						'type' => 'text_medium'
					),
					array(
						'name' => 'Sistema operativo',
						'desc' => 'Sistema operativo che supporta la pubblicazione (hardware e software)',
						'id' => $this->meta_prefix . 'sistema-operitivo',
						'taxonomy' => 'sistema-operativo', //Enter Taxonomy Slug
						'type' => 'hierarchical_checkboxes'
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
			$meta_boxes[] = array(
					'id' => 'info_metabox',
					'title' => 'Informazioni aggiuntive',
					'pages' => $types, // post type
					'context' => 'normal',
					'priority' => 'high',
					'show_names' => true, // Show field names on the left
					'fields' => array(
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
						'name' => 'Disponibile in Area',
						'desc' => 'Presenza della pubblicazione in Area',
						'id' => $this->meta_prefix . 'disponibile-in-area',
						'type'    => 'radio_inline',
						'options' => array(
								array( 'name' => 'Si', 'value' => 'SI', ),
								array( 'name' => 'No', 'value' => 'NO', ),
							  ),
						),
						array(
							'name' => 'ID Collocazione',
							'desc' => 'ID Collocazione della pubblicazione',
							'id' => $this->meta_prefix . 'id-collocazione',
							'type' => 'text_small'
						),
						array(
							'name' => 'Tipo di disabilit&agrave;',
							'desc' => 'Handicap preso in considerazione',
							'id' => $this->meta_prefix . 'handicap',
							'type' => 'text_medium'
						),
					),
			);	
			
			if( $count > 1 )
			array_push(self::$meta_boxes, $meta_boxes);
			
			$count += 1;
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
						'name' => 'Durata',
						'desc' => 'Durata (Multimedia)',
						'id' => $this->meta_prefix . 'durata',
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
						'name' => 'Et&agrave; consigliata',
						'desc' => 'Et&agrave; consigliata',
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
						'name' => 'Formato (libro chiuso)',
						'desc' => 'Formato di impaginazione del volume a libro chiuso.',
						'id' => $this->meta_prefix . 'formato',
						'type'    => 'radio_inline',
						'options' => array(
							array( 'name' => 'Piu piccolo di un A4', 'value' => 'Piu piccolo di un A4', 'checked' => 0 ),
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
				/*	array(
						'name' => 'Forma delle pagine',
						'desc' => 'Forma delle pagine',
						'id' => $this->meta_prefix . 'forma-delle-pagine',
						'type'    => 'radio_inline',
						'options' => array(
							array( 'name' => 'Regolare', 'value' => 'Regolare', 'checked' => 0),
							array( 'name' => 'Irregolare', 'value' => 'Irregolare', ),
						),
					),*/
					array(
						'name' => 'Dispositivi per aiutare a sfogliare le pagine',
						'desc' => 'Dispositivi per aiutare a sfogliare le pagine',
						'id' => $this->meta_prefix . 'dispositivi-di-aiuto',
						'type'    => 'multicheck',
						'options' => array(
							'spesse' => 'Pagine spesse',
							'irregolare' => 'Pagine dalla forma irregolare',
							'spirale' => 'Rilegatura a spirale',
							'linguette' => 'Linguette'
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
						'id' => $this->meta_prefix . 'multimedia-type',
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
							array( 'name' => '__NESSUNA__', 'value' => 0, ),
							array( 'name' => '1', 'value' => 1, ),
							array( 'name' => '2', 'value' => 2, ),
							array( 'name' => '3', 'value' => 3, ),
							array( 'name' => '4', 'value' => 4, ),
							array( 'name' => '5', 'value' => 5, ),
						),
					),
					array(
						'name' => 'Complessit&agrave; della storia (descrizione)',
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
						'name' => 'Presenza del testo scritto',
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
							array( 'name' => 'Normale', 'value' => 'Normale', 'checked' => 0),
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
							array( 'name' => 'Minuscolo', 'value' => 'Minuscolo', 'checked' => 0 ),
						),
					),
					array(
						'name' => 'Complessit&agrave; del testo',
						'desc' => 'Complessit&agrave; del testo',
						'id' => $this->meta_prefix . 'complessita-testo',
						'type'    => 'select',
						'options' => array(
							array( 'name' => '__NESSUNA__', 'value' => 0, ),
							array( 'name' => '1', 'value' => 1, ),
							array( 'name' => '2', 'value' => 2, ),
							array( 'name' => '3', 'value' => 3, ),
							array( 'name' => '4', 'value' => 4, ),
							array( 'name' => '5', 'value' => 5, ),
						),
					),
					array(
						'name' => 'Complessit&agrave; testo (descrizione)',
						'desc' => 'Complessit&agrave; testo descrizione',
						'id' => $this->meta_prefix . 'complessita-testo-descrizione',
						'type' => 'textarea_small'
					),
					array(
						'name' => 'Numero di frasi per pagina',
						'desc' => 'Numero di frasi per pagina',
						'id' => $this->meta_prefix . 'lunghezza-testo',
						'type' => 'select',
					'options' => array(
							array( 'name' => '__NESSUNA__', 'value' => 'nessuna', ),
							array( 'name' => '1', 'value' => '1', ),
							array( 'name' => 'da 2 a 5', 'value' => 'da 2 a 5', ),
							array( 'name' => 'pi&ugrave; di 5', 'value' => 'pi&ugrave; di 5', ),
						),
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
						'name' => 'Tipo di immagini',
						'desc' => 'Tipo di immagini',
						'id' => $this->meta_prefix . 'tipo-di-immagini',
						'type'    => 'multicheck',
						'options' => array(
							'visuali' => 'visuali',
							'visuali con dettagli da toccare' => 'visuali con dettagli da toccare',
							'tattili' => 'tattili',
						)
					),
					array(
						'name' => 'Tecnica tattile',
						'desc' => 'Tecnica tattile',
						'id' => $this->meta_prefix . 'tecnica',
						'type'    => 'multicheck',
						'options' => array(
							'Grauffage' => 'Grauffage',
							'Collage di materiali' => 'Collage di materiali',
							'Contorno in rilievo' => 'Contorno in rilievo'
						)
					),
					array(
						'name' => 'Complessit&agrave; delle immagini',
						'desc' => 'Complessit&agrave; delle immagini',
						'id' => $this->meta_prefix . 'complessita-immagini',
						'type'    => 'select',
						'options' => array(
							array( 'name' => '__NESSUNA__', 'value' => 0, ),
							array( 'name' => '1', 'value' => 1, ),
							array( 'name' => '2', 'value' => 2, ),
							array( 'name' => '3', 'value' => 3, ),
							array( 'name' => '4', 'value' => 4, ),
							array( 'name' => '5', 'value' => 5, ),
						),
					),
					array(
						'name' => 'Complessit&agrave; delle immagini (descrizione)',
						'desc' => 'Complessit&agrave; immagini descrizione',
						'id' => $this->meta_prefix . 'complessita-immagini-descrizione',
						'type' => 'textarea_small'
					),
				),
			);
			
			 array_push(self::$meta_boxes, $meta_boxes);
			
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