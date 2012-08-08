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
		add_action( 'wp_ajax_hardware-e-software-search', array(&$this, 'ajaxResult') );
	}
	public function ajaxResult()
	{
		
		if( $_POST )
		{
			if(  wp_verify_nonce($_POST['mediateca-nonce'],'mediateca-check-nonce') )
			{
				include_once MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG.'-'.MEDIATECA_RESULTS_PAGE.'-page.php';
				die('');
			}
			else
			{
				die("Problema di validazione del form.");
			}
		}
	} 
	private function styleAndScripts()
	{
		global $post, $wp;
		wp_enqueue_style('mediateca-front', MEDIATECA_URL.'css/style.css', '', '0.1', 'screen');
		wp_enqueue_script('mediateca-js', MEDIATECA_URL.'js/js.js', array('jquery'), '0.1', 'screen');
		wp_localize_script( 'mediateca-js', 'Mediateca', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'page' => get_permalink($post->ID), 'query' => $wp->query_vars ) );
	}
	public function hardware_e_softwareTemplate($page_template) 
	{
	     global $post, $wp;
	
	     if ($post->post_name == HARDWARE_SOFTWARE_SLUG && file_exists( MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG.'-page.php' ) )
	     {
	     	  $this->styleAndScripts();
	     	  
	     	  if( $wp->query_vars['results'] && $wp->query_vars['results'] == HARDWARE_SOFTWARE_SLUG )
	     	  {
	     	  	
	     	  	//$page_template = MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG.'-'.MEDIATECA_RESULTS_PAGE.'-page.php';
	     	  	$page_template = MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG.'-page.php';
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
	     
	     if ( $post->post_name == LIBRI_SLUG && file_exists( MEDIATECA_TEMPLATE_PATH . LIBRI_SLUG.'-page.php' ) ) 
	     {
	     	  $this->styleAndScripts();
	     	  if( $wp->query_vars['results'] && $wp->query_vars['results'] == LIBRI_SLUG )
	     	  {
	     	  	
	     	  	$page_template = MEDIATECA_TEMPLATE_PATH . LIBRI_SLUG.'-'.MEDIATECA_RESULTS_PAGE.'-page.php';
	     	  }
	     	  else 
	     	  {
	     	  	$page_template = MEDIATECA_TEMPLATE_PATH . LIBRI_SLUG.'-page.php';
	     	  }
	     }
	     return $page_template;
	}
	public function mediatecaTemplate($page_template) 
	{
	     global $post;
	
	     if ( $post->post_name == MEDIATECA_SLUG && file_exists( MEDIATECA_TEMPLATE_PATH . MEDIATECA_SLUG.'-page.php' ) ) 
	     {
	     	  $this->styleAndScripts();
	          $page_template = MEDIATECA_TEMPLATE_PATH .  MEDIATECA_SLUG.'-page.php';
	          add_filter('the_content', array(&$this, 'modifyMediatecaPageContent'), 10, 1);
	     }
	     return $page_template;
	}
	public function modifyMediatecaPageContent($content)
	{
				$html = '<div class="buttonsContainer">
				<div id="buttonLikeDiv" class="mediatecaButtons"><a href="'.get_bloginfo('url') . '/' . MEDIATECA_SLUG . '/' . HARDWARE_SOFTWARE_SLUG.'">Hardware&Software</a></div>
				<div id="buttonLikeDiv" class="mediatecaButtons"><a href="'.get_bloginfo('url') . '/' . MEDIATECA_SLUG . '/' . LIBRI_SLUG.'">Libri</a></div>
				</div>';
		return $content.$html;
	}
	public function get_custom_post_type_single_template($single_template) 
	{
	     global $post;
	
	     if ( in_array($post->post_type, $this->types) && file_exists( MEDIATECA_TEMPLATE_PATH . 'single-'.MEDIATECA_SLUG.'.php' ) ) 
	     {
	          $single_template = MEDIATECA_TEMPLATE_PATH . '/single-'.MEDIATECA_SLUG.'.php';
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