<?php
error_reporting(E_ALL); 
ini_set("display_errors", 0);
class Mediateca_Render
{
	private $types;
	private $type;
	public $show_comments;
	public static $PAGES_SLUG = array( MEDIATECA_SLUG, HARDWARE_SOFTWARE_SLUG, LIBRI_SLUG );
	const POSTS_PER_PAGE = 10; 
	private $taxonomies = array();
	private $metas = array();
	private $number_of_pages = 1;
	private $mother_page;
	private $pagename;
	
	public function __construct()
	{
		$this->initSession();
		
		$this->types = Mediateca_Init::$types;
		
		$this->show_comments = false;
		
		add_action( 'sidebar_left_home_first_box', array(&$this, 'sidebarLeftBox'), 10 );
		add_filter( 'single_template', array(&$this, 'get_custom_post_type_single_template'), 11 );
		add_filter( 'page_template', array(&$this, 'mediatecaTemplate') );
		add_filter( 'page_template', array(&$this, 'hardware_e_softwareTemplate') );
		add_filter( 'page_template', array(&$this, 'libriTemplate') );
		add_action( 'wp_ajax_hardware-e-software-search', array(&$this, 'ajaxResult') );
		add_action( 'wp_ajax_nopriv_hardware-e-software-search', array(&$this, 'ajaxResult') );
		add_action( 'wp_ajax_manage_category_select', array(&$this, 'populateSubcategories') );
	}
	private function initSession()
	{
		if (!session_id()) {
	        session_start();
	        $_SESSION['previous_query'] = null;
	    }
	}
	public function ajaxResult()
	{	
		global $wp;
		
		if( ( $_POST && $_POST['media_type'] ) )
			{
				
				if(  wp_verify_nonce($_POST['mediateca-nonce'], 'mediateca-check-nonce') )
				{
					$this->pagename = isset( $_POST['pagename'] ) ? $_POST['pagename'] : $wp->query_vars['pagename'];
					
					$this->type = $_POST['media_type'];
					
					$categoria = $_POST['sottocategoria'] ? $_POST['sottocategoria'] : $_POST['categoria'];
					
					$this->taxQuery( 'categoria', $categoria );
					
					$this->taxQuery( 'terzo-livello', $_POST['terzo-livello'] );
					
					$_SESSION['previous_query'] = null;
					
					if( $this->isAjax() ) $visible = 'hidden';
					
					$search = $this->getQueryObject( $this->type,  $this->taxonomies );
					
					include_once MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG.'-'.MEDIATECA_RESULTS_PAGE.'-page.php';
					
					if( $this->isAjax() ) die('');
				}
				else
				{
					die("Problema di validazione del form.");
				}
		}
		else if( $_GET && $_GET['results'] == $this->mother_page )
		{
				
				$search = $this->getQueryObject( null,  null, null, true );
				
				include_once MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG.'-'.MEDIATECA_RESULTS_PAGE.'-page.php';
				
				if( $this->isAjax() ) die('');
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
	
	     if ( $post->post_name == HARDWARE_SOFTWARE_SLUG && file_exists( MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG.'-page.php' ) )
	     {
	     	  $this->mother_page = $post->post_name;
	     	  
	     	  $this->styleAndScripts();
	     	  
	     	  if( $wp->query_vars['results'] && $wp->query_vars['results'] == HARDWARE_SOFTWARE_SLUG )
	     	  {
	     	  	add_action( 'render_search_results', array(&$this, 'ajaxResult') );
	     	  }
	     	  
	     	  $page_template = MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG.'-page.php';
	     	  
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
	    'posts_per_page'     => $number,
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
	private function getQueryObject( $types, $taxonomies = array(), $metas = array(), $is_pagination_query = false )
	{
		$page = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
		
		
		$args = array(
	    'offset'          => 0,
	    'tax_query'        => $taxonomies ,
	    'orderby'         => 'post_date',
	    'order'           => 'DESC',
	    'include'         => '',
	    'exclude'         => '',
	    'meta_query'        => $metas,
	    'post_type'       => $types,
	    'post_mime_type'  => '',
	    'post_parent'     => '',
	    'post_status'     => 'publish', 
		'paged'			  => $page,
		'posts_per_page'  => self::POSTS_PER_PAGE 
		);
		
		if( $is_pagination_query == false ) 
		{
			$_SESSION['previous_query'] = $args;
			$query = $args;
		} 
		else if( $is_pagination_query == true )
		{
			$query = $_SESSION['previous_query'];
			$query['paged'] = $page;
		}
		
		$q = new WP_Query( $query );
		
		$this->number_of_pages = $q->max_num_pages;
		
		return $q;
	}
	private function taxQuery( $tax, $term )
	{
		if( !$term || $term == -1 ) return array();
		
		$query = array(
			'taxonomy' => $tax,
			'field' => 'id',
			'terms' => $term);
		
		return array_push($this->taxonomies, $query);
	}
	public function paginationLinks()
	{
		global $wp_query, $wp_rewrite, $wp;
		
		$wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;
		
		$pagination['add_args'] = array();
 
		$pagination = array(
			'base' => add_query_arg('page','%#%'),
			'format' => '',
			'total' => $this->number_of_pages,
			'current' => $current,
			'show_all' => true,
			'type' => 'list',
			'next_text' => '&raquo;',
			'prev_text' => '&laquo;'
			);
		
		
			
		if( $wp_rewrite->using_permalinks() )
		{
			$pagination['base'] = user_trailingslashit( trailingslashit( remove_query_arg( 's', get_pagenum_link( 1 ) ) ) . 'page/%#%/', 'paged' );
			$pagination['base'] = user_trailingslashit( trailingslashit( remove_query_arg( 'results', get_pagenum_link( 1 ) ) ) . 'page/%#%/', 'paged' );
		}
		
		if( strpos( $pagination['base'], 'wp-admin/admin-ajax.php') )
		{
			$pagination['base'] = str_replace('wp-admin/admin-ajax.php', $this->pagename, $pagination['base']);
		}
		
		if( $wp->query_vars['results'] || $_POST['results'] )
		{
			$pagination['add_args']['results'] = ( $wp->query_vars['results'] ) ? $wp->query_vars['results'] : $_POST['results'];
		}
		
		if( !empty($wp_query->query_vars['s']) )
			$pagination['add_args'] = array( 's' => get_query_var( 's' ) );
		
		echo paginate_links( $pagination );
	}
	public function getUserNiceName( $id )
	{
		$user = get_user_by('id', $id);
		return $user->display_name;
	}
	public function taxonomySelect($name, $taxonomy, $args = null, $parent = 0,  $label = 'Select', $class="visible")
	{
		if( $args )
		{
			echo '<div class="select-container '. $class.'">';
			echo '<label for="'.$name.'" class="mediateca_search_select">'.ucfirst($label).'</label><br />';
			
			echo '<select name="'.$name.'" id="'.$name.'">';
			
			$terms = get_terms( $taxonomy,  $args);
			
			if( count( $terms ) > 0)
			{
				echo '<option value="" selected>&#8212; Seleziona ' . ucfirst($name) . ' &#8212;</option>';
				foreach ( $terms as $term ) 
				{
					if( $parent && $term->parent == 0 )
					{
						echo '<option value="' . $term->term_id . '">' . $term->name . '</option>';
					}
					else if( !$parent )
					{
						echo '<option value="' . $term->term_id . '">' . $term->name . '</option>';
					}
					
				}
			}
		echo '</select></div>';
		}
	}
	public function populateSubcategories()
	{
		if( $_POST && wp_verify_nonce($_POST['mediateca-nonce'],'mediateca-check-nonce') )
		{
			$args = array( 'hide_empty' => 0, 'hierarchical' => true, 'child_of' =>  $_POST['parent'] );
			$this->taxonomySelect('sottocategoria', 'categoria', $args, false, 'Sottocategoria', 'hidden' );
			die('');
		}
	}
	public function isAjax() {
	   return (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
	              &&
	            ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
	}
}
?>