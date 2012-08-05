<?php
error_reporting(E_ALL); 
ini_set("display_errors", 0);
class Mediateca_Render
{
	private $types;
	public $show_comments;
	public static $PAGES_SLUG = array( MEDIATECA_SLUG, HARDWARE_SOFTWARE_SLUG, LIBRI_SLUG );
	
	public function __construct()
	{
		$this->types = Mediateca_Init::$types;
		$this->show_comments = false;
		
		add_action( 'sidebar_left_home_first_box', array(&$this, 'sidebarLeftBox'), 10 );
		add_filter( 'single_template', array(&$this, 'get_custom_post_type_single_template'), 11 );
		add_filter( 'page_template', array(&$this, 'mediatecaTemplate') );
		add_filter( 'page_template', array(&$this, 'hardware_e_softwareTemplate') );
		add_filter( 'page_template', array(&$this, 'libriTemplate') );
	}
	public function hardware_e_softwareTemplate($page_template) 
	{
	     global $post, $wp;
	
	     if ($post->post_name == HARDWARE_SOFTWARE_SLUG && file_exists( MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG.'-page.php' ) ) 
	     {
	     	  if( $wp->query_vars['results'] && $wp->query_vars['results'] == HARDWARE_SOFTWARE_SLUG )
	     	  {
	     	  	
	     	  	$page_template = MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG.'-'.MEDIATECA_RESULTS_PAGE.'-page.php';
	     	  }
	     	  else
	     	  {
	     	  	$page_template = MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG.'-page.php';
	     	  }
	     }
	     return $page_template;
	}
	public function libriTemplate($page_template) 
	{
	     global $post, $wp;
	
	     if ( $post->post_name == LIBRI_SLUG && file_exists( LIBRI_SOFTWARE_SLUG.'-page.php' ) ) 
	     {
	     	  if( $wp->query_vars['results'] && $wp->query_vars['results'] == LIBRI_SOFTWARE_SLUG )
	     	  {
	     	  	
	     	  	$page_template = MEDIATECA_TEMPLATE_PATH . LIBRI_SOFTWARE_SLUG.'-'.MEDIATECA_RESULTS_PAGE.'-page.php';
	     	  }
	     	  else 
	     	  {
	     	  	$page_template = MEDIATECA_TEMPLATE_PATH . LIBRI_SOFTWARE_SLUG.'-page.php';
	     	  }
	     }
	     return $page_template;
	}
	public function mediatecaTemplate($page_template) 
	{
	     global $post;
	
	     if ( $post->post_name == MEDIATECA_SLUG && file_exists( MEDIATECA_TEMPLATE_PATH . MEDIATECA_SLUG.'-page.php' ) ) 
	     {
	          $page_template = MEDIATECA_TEMPLATE_PATH .  MEDIATECA_SLUG.'-page.php';
	     }
	     return $page_template;
	}
	public function get_custom_post_type_single_template($single_template) 
	{
	     global $post;
	
	     if ( in_array($post->post_type, $this->types) && file_exists( MEDIATECA_TEMPLATE_PATH . 'single-mediateca.php' ) ) 
	     {
	          $single_template = MEDIATECA_TEMPLATE_PATH . '/single-mediateca.php';
	     }
	     return $single_template;
	}
	public function sidebarLeftBox()
	{
		if( is_front_page() && file_exists( MEDIATECA_TEMPLATE_PATH . 'sidebar_boxes.php' ) )
		{
			$posts = $this->getPosts( array(HARDWARE_TYPE, SOFTWARE_TYPE) );
			
			include_once MEDIATECA_TEMPLATE_PATH.'sidebar_boxes.php';
		}
		return $posts;	
	}
	public function getPosts( $types, $number = 2, $taxonomies = array(), $metas = array() )
	{
		$args = array(
	    'numberposts'     => $number,
	    'offset'          => 0,
	    'tax_query'        => $taxonomies,
	    'orderby'         => 'post_date',
	    'order'           => 'DESC',
	    'include'         => '',
	    'exclude'         => '',
	    'meta_query'        => $metas,
	    'post_type'       => $types,
	    'post_mime_type'  => '',
	    'post_parent'     => '',
	    'post_status'     => 'publish' );
		
		$ps = &get_posts( $args );
		
		return $ps;
	}
	public function getUserNiceName( $id )
	{
		$user = get_user_by('id', $id);
		return $user->display_name;
	}
}
?>