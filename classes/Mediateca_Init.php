<?php
error_reporting(E_ALL); 
ini_set("display_errors", 0);
class Mediateca_Init
{
	private static $instance;
	public static $types = array(HARDWARE_TYPE, SOFTWARE_TYPE, LIBRI_TYPE);
	public static $pages;
	
	private function __construct()
	{
		$this->createPostTypes();
		$this->createHardwareAndSoftwareTaxonomies();
		$this->createLibriTaxonomies();
		add_filter('query_vars', array(&$this, 'addQueryVars'));
		register_activation_hook( MEDIATECA_PATH.'mediateca', array( $this, 'createPluginPages' ) );
		self::$pages = array( MEDIATECA_SLUG => get_page_by_title( ucfirst(MEDIATECA_SLUG) ), HARDWARE_SOFTWARE_SLUG => get_page_by_title( ucfirst( str_replace('-', ' ', HARDWARE_SOFTWARE_SLUG) ) ), LIBRI_SLUG => get_page_by_title( ucfirst( str_replace('-', ' ', LIBRI_SLUG) ) ));
	}
	private function createPostTypes()
	{
		foreach(self::$types as $type)
		{
			$labels = array(
				'name' => __(ucfirst($type), MEDIATECA_TD),
				'singular_name' => __(ucfirst($type), MEDIATECA_TD),
				'add_new' => __('Add New', MEDIATECA_TD),
				'add_new_item' => __('Add New '. ucfirst($type),MEDIATECA_TD),
				'edit_item' => __('Edit '.ucfirst($type),MEDIATECA_TD),
				'new_item' => __('New '.ucfirst($type),MEDIATECA_TD),
				'view_item' => __('View '.ucfirst($type),MEDIATECA_TD),
				'search_items' => __('Search '.ucfirst($type) ,MEDIATECA_TD),
				'not_found' =>  __('No  ' . ucfirst($type),MEDIATECA_TD),
				'not_found_in_trash' => __('No '.$type.' found in Trash',MEDIATECA_TD),
				'parent_item_colon' => ''
			);
			// Register custom post types
			register_post_type($type, array(
				'labels' => $labels,
				'singular_label' => __(ucfirst($type),MEDIATECA_TD),
				'public' => true,
				'show_ui' => true,
				//'_builtin' => false,
				//'_edit_link' => 'post.php?post=%d',
				'capability_type' => 'post',
				'hierarchical' => false,
				'rewrite' => true,
				'query_var' => $type,
				'supports' => array('title', 'author', 'editor', 'excerpt', 'revisions', 'thumbnail', 'custom-fields', 'post-formats', 'comments'),
				'taxonomies' => array('categoria', 'terzo-livello', 'sezione')
			));
		}
	}
	private function createHardwareAndSoftwareTaxonomies()
	{
		$types = self::$types;
		
		array_pop( $types );	
	
		register_taxonomy( 'categoria', $types,
		array(
			'hierarchical' => true,
			'label' => __('Categoria',MEDIATECA_TD),
			'query_var' => 'categoria',
			'rewrite' => array('slug' => 'categoria' )
			));
		register_taxonomy( 'terzo-livello', $types,
		array(
			'hierarchical' => false,
			'label' => __('Terzo livello',MEDIATECA_TD),
			'query_var' => 'terzo-livello',
			'rewrite' => array('slug' => 'terzo-livello' )
			));
		register_taxonomy( 'sezione', $types,
		array(
			'hierarchical' => true,
			'label' => __('Sezione',MEDIATECA_TD),
			'query_var' => 'sezione',
			'rewrite' => array('slug' => 'sezione' )
			));
	}
	public function createLibriTaxonomies()
	{
		$types = LIBRI_TYPE;
		register_taxonomy( 'sezione-libri', $types,
		array(
			'hierarchical' => false,
			'label' => __('Sezione',MEDIATECA_TD),
			'query_var' => 'sezione-libri',
			'rewrite' => array('slug' => 'sezione-libri' )
			));
		register_taxonomy( 'tipo-di-handicap', $types,
		array(
			'hierarchical' => true,
			'label' => __('Tipo di handicap',MEDIATECA_TD),
			'query_var' => 'tipo-di-handicap',
			'rewrite' => array('slug' => 'tipo-di-handicap' )
			));
		register_taxonomy( 'accessibilita-secondaria', $types,
		array(
			'hierarchical' => true,
			'label' => __('Accessibilit&agrave; secondaria',MEDIATECA_TD),
			'query_var' => 'accessibilita-secondaria',
			'rewrite' => array('slug' => 'accessibilita-secondaria' )
			));
		register_taxonomy( 'genere', $types,
		array(
			'hierarchical' => false,
			'label' => __('Genere',MEDIATECA_TD),
			'query_var' => 'genere',
			'rewrite' => array('slug' => 'genere' )
			));
		register_taxonomy( 'tipo-di-libro', $types,
		array(
			'hierarchical' => false,
			'label' => __('Tipologia di libro',MEDIATECA_TD),
			'query_var' => 'tipo-di-libro',
			'rewrite' => array('slug' => 'tipo-di-libro' )
			));
		register_taxonomy( 'eta', $types,
		array(
			'hierarchical' => true,
			'label' => __('Fascia di et&agrave;',MEDIATECA_TD),
			'query_var' => 'eta',
			'rewrite' => array('slug' => 'eta' )
			));
		register_taxonomy( 'difficolta-compensata', $types,
		array(
			'hierarchical' => true,
			'label' => __('Tipo di difficolt&agrave; compensata',MEDIATECA_TD),
			'query_var' => 'difficolta-compensata',
			'rewrite' => array('slug' => 'difficolta-compensata' )
			));
		register_taxonomy( 'materiale-di-base', $types,
		array(
			'hierarchical' => true,
			'label' => __('Materiale di base',MEDIATECA_TD),
			'query_var' => 'materiale-di-base',
			'rewrite' => array('slug' => 'materiale-di-base' )
			));
		register_taxonomy( 'personaggi', $types,
		array(
			'hierarchical' => true,
			'label' => __('Personaggi',MEDIATECA_TD),
			'query_var' => 'personaggi',
			'rewrite' => array('slug' => 'personaggi' )
			));
		register_taxonomy( 'temi-trattati', $types,
		array(
			'hierarchical' => true,
			'label' => __('Temi trattati',MEDIATECA_TD),
			'query_var' => 'temi-trattati',
			'rewrite' => array('slug' => 'temi-trattati' )
			));
		register_taxonomy( 'codici-utilizzati', $types,
		array(
			'hierarchical' => true,
			'label' => __('Codici utilizzati',MEDIATECA_TD),
			'query_var' => 'codici-utilizzati',
			'rewrite' => array('slug' => 'codici-utilizzati' )
			));
		register_taxonomy( 'ambiente-prevalente', $types,
		array(
			'hierarchical' => true,
			'label' => __('Ambiente prevalente',MEDIATECA_TD),
			'query_var' => 'ambiente-prevalente',
			'rewrite' => array('slug' => 'ambiente-prevalente' )
			));
	}
	//push our types in the array of valid query vars
	public function addQueryVars($vars) 
	{
		// add mediateca_views to the valid list of variables
		$new_vars = self::$types;
		array_push($new_vars, MEDIATECA_RESULTS_PAGE);
		array_push($new_vars, MEDIATECA_TEXT_SEARCH);
		$vars = $new_vars + $vars;
		return $vars;
	}
	//Our class is singleton class will be instantiated only once every Wordpress instance
	public static function getInstance() 
	{ 
	    if (!self::$instance) 
	    { 
	        self::$instance = new Mediateca_Init(); 
	    } 

	    return self::$instance; 
	}
}
?>