<?php
error_reporting ( E_ALL );
ini_set ( "display_errors", 0 );
class Mediateca_Render {
	private $types;
	private $type;
	public $show_comments;
	public static $PAGES_SLUG = array (MEDIATECA_SLUGS, HARDWARE_SOFTWARE_SLUG, LIBRI_SLUG );
	const POSTS_PER_PAGE = 10;
	private $taxonomies = array ( );
	private $metas = array ( );
	private $number_of_pages = 1;
	private $mother_page;
	private $pagename;
	const HIDE_EMPTY = 1;
	
	public function __construct() {
		$this->initSession();
		
		$this->types = Mediateca_Init::$types;
		
		$this->show_comments = false;
		
		add_action ( 'sidebar_left_home_first_box', array (&$this, 'sidebarLeftBox' ), 10 );
		add_filter ( 'single_template', array (&$this, 'get_custom_post_type_single_template' ), 11 );
		add_filter ( 'page_template', array (&$this, 'mediatecaTemplate' ) );
		add_filter ( 'page_template', array (&$this, 'hardware_e_softwareTemplate' ) );
		add_filter ( 'page_template', array (&$this, 'libriTemplate' ) );
		
		add_action ( 'wp_ajax_'.MEDIATECA_HARDWARE_AND_SOFTWARE_SEARCH, array (&$this, 'ajaxResult' ) );
		add_action ( 'wp_ajax_nopriv_'.MEDIATECA_HARDWARE_AND_SOFTWARE_SEARCH, array (&$this, 'ajaxResult' ) );
		
		add_action ( 'wp_ajax_manage_category_select', array (&$this, 'populateSubcategories' ) );
		add_action ( 'wp_ajax_nopriv_manage_category_select', array (&$this, 'populateSubcategories' ) );
		
		add_action ( 'wp_ajax_do_text_search', array (&$this, 'doTextSearch' ) );
		add_action ( 'wp_ajax_nopriv_do_text_search', array (&$this, 'doTextSearch' ) );
		
		add_action ( 'wp_ajax_change-sezione-libri', array (&$this, 'manageSezioneLibri' ) );
		add_action ( 'wp_ajax_nopriv_change-sezione-libri', array (&$this, 'manageSezioneLibri' ) );
		
		add_action ( 'wp_ajax_'.MEDIATECA_LIBRI_SEARCH, array (&$this, 'ajaxLibriResult' ) );
		add_action ( 'wp_ajax_nopriv_'.MEDIATECA_LIBRI_SEARCH, array (&$this, 'ajaxLibriResult' ) );
		
	}
	private function initSession() {
		if ( !session_id () ) {
			session_start ();
		}
	}
	public function ajaxLibriResult()
	{
		global $wp;
		
		
		//make sure add this plugin shit doesn't bother
		if ( function_exists ( 'addthis_init' ) ) {
			remove_filter ( 'the_content', 'addthis_display_social_widget', 15 );
			remove_filter ( 'get_the_excerpt', 'addthis_display_social_widget_excerpt', 11 );
		}
		
		$this->pagename = isset ( $_POST ['pagename'] ) ? $_POST ['pagename'] : $wp->query_vars ['pagename'];
		
		if( $_POST && wp_verify_nonce( $_POST ['mediateca-libri-nonce'], 'mediateca-check-libri-nonce' ) )
		{
			$this->type = $_SESSION['media_type'] = LIBRI_TYPE;
			
			$section = $_POST['sezione-libri'];
			
			$age = $_POST['eta'];
			
			$book_type = $_POST['tipo-di-libro'];
			
			$accessibilities = $_POST['tax_input']['tipo-di-handicap'];
			
			$this->taxQuery ( 'sezione-libri', $section, 'slug' );
			$this->taxQuery ( 'eta', $age );
			$this->taxQuery ( 'tipo-di-libro', $book_type );
			
			foreach( $accessibilities as $accessibility )
			{
				$this->taxQuery ( 'tipo-di-handicap', $accessibility );
			}
			 
			$_SESSION['previous_query'] = null;
			
			if ($this->isAjax ())
				$visible = 'hidden';
			
			$search = $this->getQueryObject ( $this->type, $this->taxonomies );
			
			include_once MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG . '-' . MEDIATECA_RESULTS_PAGE . '-page.php';
			
		}
		else if ( ($_GET && $_GET ['results'] == $this->mother_page) || ($_POST && $_POST ['paginated']) ) 
		{
				$this->type = $_SESSION['media_type'];
				
				$search = $this->getQueryObject ( null, null, null, true );
				
				include_once MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG . '-' . MEDIATECA_RESULTS_PAGE . '-page.php';
				
				if ( $this->isAjax () )
					die ( '' );
		}
		else
		{
			die('Problema di validazione del form');
		}
		
		if ($this->isAjax ()) die('');
	}
	public function manageSezioneLibri()
	{
		if( $_POST && $_POST['action'] == 'change-sezione-libri' )
		{
			include MEDIATECA_TEMPLATE_PATH . $_POST['sezione'] . '_block.php';
		}
		die('');
	}
	public function ajaxResult() {
		global $wp;
		
		//make sure add this plugin shit doesn't bother
		if (function_exists ( 'addthis_init' )) {
			remove_filter ( 'the_content', 'addthis_display_social_widget', 15 );
			remove_filter ( 'get_the_excerpt', 'addthis_display_social_widget_excerpt', 11 );
		}
		
		$this->pagename = isset ( $_POST ['pagename'] ) ? $_POST ['pagename'] : $wp->query_vars ['pagename'];
		
		//check if it is a from submission and we ave something
		if ( ($_POST && $_POST ['media_type']) ) {
			
			//check if the form was submitted from our form 
			if ( wp_verify_nonce( $_POST['mediateca-nonce'], 'mediateca-check-nonce' ) ) {
				
				
				//fill up the vars and render
				$this->type = $_SESSION['media_type'] = $_POST ['media_type'];
				
				$categoria = $_POST ['sottocategoria'] ? $_POST ['sottocategoria'] : $_POST ['categoria'];
				
				$this->taxQuery ( 'categoria', $categoria );
				
				$this->taxQuery ( 'terzo-livello', $_POST ['terzo-livello'] );
				
				//keep track of the last query to paginate results
				$_SESSION['previous_query'] = null;
				
				if ($this->isAjax ())
					$visible = 'hidden';
				
				$search = $this->getQueryObject ( $this->type, $this->taxonomies );
				
				include_once MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG . '-' . MEDIATECA_RESULTS_PAGE . '-page.php';
				
				if ( $this->isAjax () )
					die ( '' );
			} else {
				die ( "Problema di validazione del form." );
			}
		} else if (($_GET && $_GET ['results'] == $this->mother_page) || ($_POST && $_POST ['paginated'])) {
			$this->type = $_SESSION['media_type'];
			
			$search = $this->getQueryObject ( null, null, null, true );
			
			include_once MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG . '-' . MEDIATECA_RESULTS_PAGE . '-page.php';
			
			if ( $this->isAjax () )
				die ( '' );
		}
	
	}
	private function styleAndScripts() {
		global $post, $wp;
		
		$wp->query_vars ['current_page'] = $post->post_name;
		
		wp_enqueue_style ( 'mediateca-front', MEDIATECA_URL . 'css/style.css', '', '0.1', 'screen' );
		wp_enqueue_script ( 'mediateca-js', MEDIATECA_URL . 'js/js.js', array ('jquery' ), '0.1', 'screen' );
		wp_localize_script ( 'mediateca-js', 'Mediateca', array ('plugin_url' => MEDIATECA_URL, ajaxurl => admin_url ( 'admin-ajax.php' ), 'page' => get_permalink ( $post->ID ), 'query' => $wp->query_vars, 'slug' => $post->post_name ) );
	}
	public function hardware_e_softwareTemplate($page_template) {
		global $post, $wp;
		
		if ($post->post_name == HARDWARE_SOFTWARE_SLUG && file_exists ( MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG . '-page.php' )) {
			$this->mother_page = $post->post_name;
			
			$this->styleAndScripts();
			
			if (isset ( $wp->query_vars ['results'] ) && $wp->query_vars ['results'] && $wp->query_vars ['results'] == HARDWARE_SOFTWARE_SLUG) {
				add_action ( 'render_search_results', array (&$this, 'ajaxResult' ) );
			} elseif (isset ( $wp->query_vars [MEDIATECA_TEXT_SEARCH] ) && $wp->query_vars [MEDIATECA_TEXT_SEARCH]) {
				add_action ( 'render_search_results', array (&$this, 'doTextSearch' ) );
			}
			
			$page_template = MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG . '-page.php';
		
		}
		return $page_template;
	}
	public function doTextSearch() {
		global $wp;
		
		if (($_POST && wp_verify_nonce ( $_POST ['mediateca-nonce-text'], 'mediateca-check-text-nonce' ) || $_POST && $_POST ['paginated']) || $wp->query_vars ['search']) {
			
			$this->pagename = isset ( $_POST ['pagename'] ) ? $_POST ['pagename'] : $wp->query_vars ['pagename'];
			
			if ($this->isAjax ()) {
				$this->mother_page = $_POST ['current_page'];
				$wp->query_vars [MEDIATECA_TEXT_SEARCH] = ($_POST ['paginated']) ? $_POST ['paginated'] : $_POST [MEDIATECA_TEXT_SEARCH];
			}
			
			if ($this->mother_page == HARDWARE_SOFTWARE_SLUG) {
				$types = array (SOFTWARE_TYPE, HARDWARE_TYPE );
			} 

			elseif ($this->mother_page == LIBRI_SLUG) {
				$types = array (LIBRI_TYPE );
			}
			
			$args = array ('s' => $wp->query_vars [MEDIATECA_TEXT_SEARCH], 'post_type' => $types, //'showposts' => 50
'paged' => $this->getCurrent (), 'posts_per_page' => self::POSTS_PER_PAGE, 'orderby' => 'title', 'order' => 'ASC' );
			
			$search = new WP_Query ( $args );
			
			$this->number_of_pages = $search->max_num_pages;
			
			//make sure add this plugin shit doesn't bother
			if (function_exists ( 'addthis_init' )) {
				remove_filter ( 'the_content', 'addthis_display_social_widget', 15 );
				remove_filter ( 'get_the_excerpt', 'addthis_display_social_widget_excerpt', 11 );
			}
			
			include_once MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG . '-' . MEDIATECA_RESULTS_PAGE . '-page.php';
			
			if ($this->isAjax ())
				die ( '' );
		}
	}
	private function getCurrent() {
		global $wp_query;
		
		$wp_query->query_vars ['paged'] > 1 ? $current = $wp_query->query_vars ['paged'] : $current = 1;
		
		isset ( $_POST ['pagenum'] ) ? $current = $_POST ['pagenum'] : $current = $current;
		
		return $current;
	}
	public function libriTemplate($page_template) {
		global $post, $wp;
		
		if ($post->post_name == LIBRI_SLUG && file_exists ( MEDIATECA_TEMPLATE_PATH . LIBRI_SLUG . '-page.php' )) {
			$this->mother_page = $post->post_name;
			
			$this->styleAndScripts ();
			
			if ( isset ( $wp->query_vars ['results'] ) && $wp->query_vars ['results'] && $wp->query_vars ['results'] == LIBRI_SLUG) {	
				add_action ( 'render_search_results', array (&$this, 'ajaxLibriResult' ) );
			} elseif (isset ( $wp->query_vars [MEDIATECA_TEXT_SEARCH] ) && $wp->query_vars [MEDIATECA_TEXT_SEARCH]) {
				add_action ( 'render_search_results', array (&$this, 'doTextSearch' ) );
			}
			
			$page_template = MEDIATECA_TEMPLATE_PATH . LIBRI_SLUG . '-page.php';
		}
		return $page_template;
	}
	public function mediatecaTemplate($page_template) {
		global $post;
		
		if ($post->post_name == MEDIATECA_SLUG && file_exists ( MEDIATECA_TEMPLATE_PATH . MEDIATECA_SLUG . '-page.php' )) {
			$this->styleAndScripts ();
			$page_template = MEDIATECA_TEMPLATE_PATH . MEDIATECA_SLUG . '-page.php';
			add_filter ( 'the_content', array (&$this, 'modifyMediatecaPageContent' ), 10, 1 );
		}
		return $page_template;
	}
	public function modifyMediatecaPageContent($content) {		
		$html = '<div class="buttonsContainer">
				<div id="buttonLikeDiv" class="mediatecaButtons"><a href="' . get_bloginfo ( 'url' ) . '/' . MEDIATECA_SLUG . '/' . HARDWARE_SOFTWARE_SLUG . '">Hardware&Software</a></div>
				<div id="buttonLikeDiv" class="mediatecaButtons"><a href="' . get_bloginfo ( 'url' ) . '/' . MEDIATECA_SLUG . '/' . LIBRI_SLUG . '">Libri</a></div>
				</div>';
		return $content . $html;
	}
	public function get_custom_post_type_single_template($single_template) {
		global $post;
		
		if (in_array ( $post->post_type, $this->types ) && file_exists ( MEDIATECA_TEMPLATE_PATH . 'single-' . MEDIATECA_SLUG . '.php' )) {
			$this->styleAndScripts();
			$single_template = MEDIATECA_TEMPLATE_PATH . '/single-' . MEDIATECA_SLUG . '.php';
		}
		return $single_template;
	}
	public function sidebarLeftBox() {
		if (is_front_page () && file_exists ( MEDIATECA_TEMPLATE_PATH . 'sidebar_boxes.php' )) {
			$posts = $this->getPosts ( array (HARDWARE_TYPE, SOFTWARE_TYPE ) );
			
			include_once MEDIATECA_TEMPLATE_PATH . 'sidebar_boxes.php';
		}
		return $posts;
	}
	public function getPosts($types, $number = 2, $taxonomies = array(), $metas = array()) {
		$args = array ('posts_per_page' => $number, 'offset' => 0, 'tax_query' => $taxonomies, 'orderby' => 'post_date', 'order' => 'DESC', 'include' => '', 'exclude' => '', 'meta_query' => $metas, 'post_type' => $types, 'post_mime_type' => '', 'post_parent' => '', 'post_status' => 'publish' );
		
		$ps = &get_posts ( $args );
		
		return $ps;
	}
	private function getQueryObject($types, $taxonomies = array(), $metas = array(), $is_pagination_query = false) {
		if ($_POST && $_POST ['pagenum']) {
			$page = $_POST ['pagenum'];
		} else {
			$page = (get_query_var ( 'paged' )) ? get_query_var ( 'paged' ) : 1;
		}
		
		$args = array ('offset' => 0, 'tax_query' => $taxonomies, 'orderby' => 'post_date', 'order' => 'DESC', 'include' => '', 'exclude' => '', 'meta_query' => $metas, 'post_type' => $types, 'post_mime_type' => '', 'post_parent' => '', 'post_status' => 'publish', 'paged' => $page, 'posts_per_page' => 2 /*self::POSTS_PER_PAGE*/ );
		
		if ($is_pagination_query == false) {
			$_SESSION['previous_query'] = $args;
			$query = $args;
		} else if ($is_pagination_query == true) {
			$query = $_SESSION['previous_query'];
			$query ['paged'] = $page;
		}
		$q = new WP_Query ( $query );
		
		$this->number_of_pages = $q->max_num_pages;
		
		return $q;
	}
	private function taxQuery($tax, $term, $field = 'id' ) {
		
		if (! $term || $term == - 1)
			return array ( );
			
		$query = array ('taxonomy' => $tax, 'field' => $field, 'terms' => $term );
		
		return array_push ( $this->taxonomies, $query );
	}
	
	public function paginationLinks() {
		global $wp_query, $wp_rewrite, $wp;
		
		$pagination ['add_args'] = array ( );
		
		$pagination = array ('base' => add_query_arg ( 'page', '%#%' ), 'format' => '', 'total' => $this->number_of_pages, 'current' => $this->getCurrent (), 'show_all' => true, 'type' => 'list', 'next_text' => '&raquo;', 'prev_text' => '&laquo;' );
		
		if ($wp_rewrite->using_permalinks ()) {
			if (($_POST ['paginated'] && $_POST ['paginated'] == $_POST ['kind']) || (! empty ( $wp_query->query_vars [MEDIATECA_TEXT_SEARCH] ) || $_POST [MEDIATECA_TEXT_SEARCH])) {
				$pagination ['base'] = user_trailingslashit ( trailingslashit ( remove_query_arg ( MEDIATECA_TEXT_SEARCH, get_pagenum_link ( 1 ) ) ) . 'page/%#%/', 'paged' );
				$pagination ['add_args'] [MEDIATECA_TEXT_SEARCH] = (get_query_var ( MEDIATECA_TEXT_SEARCH )) ? get_query_var ( MEDIATECA_TEXT_SEARCH ) : ($_POST [MEDIATECA_TEXT_SEARCH]) ? $_POST [MEDIATECA_TEXT_SEARCH] : $_POST ['paginated'];
			}
			
			if (($_POST ['paginated'] && $_POST ['kind'] == MEDIATECA_HARDWARE_AND_SOFTWARE_SEARCH) || ($wp->query_vars ['results'] || $_POST ['results'])) {
				$pagination ['base'] = user_trailingslashit ( trailingslashit ( remove_query_arg ( 'results', get_pagenum_link ( 1 ) ) ) . 'page/%#%/', 'paged' );
				$pagination ['add_args'] ['results'] = ($wp->query_vars ['results']) ? $wp->query_vars ['results'] : ($_POST ['results']) ? $_POST ['results'] : $_POST ['paginated'];
			
			}
		
		}
		
		if (strpos ( $pagination ['base'], 'wp-admin/admin-ajax.php' )) {
			$pagination ['base'] = str_replace ( 'wp-admin/admin-ajax.php', $this->pagename, $pagination ['base'] );
		}
		
		echo paginate_links ( $pagination );
	}
	public function getUserNiceName($id) {
		$user = get_user_by ( 'id', $id );
		return $user->display_name;
	}
	public function taxonomySelect($name, $taxonomy, $args = null, $parent = 0, $label = 'Select', $class = "visible") {
		if ($args) {
			echo '<div class="select-container ' . $class . '">';
			echo '<label for="' . $name . '" class="mediateca_search_select">' . ucfirst ( $label ) . '</label><br />';
			
			echo '<select name="' . $name . '" id="' . $name . '">';
			
			$terms = get_terms ( $taxonomy, $args );
			
			if (count ( $terms ) > 0) {
				echo '<option value="" selected>&#8212; Seleziona ' . ucfirst ( $label ) . ' &#8212;</option>';
				foreach ( $terms as $term ) {
					if ($parent && $term->parent == 0) {
						echo '<option value="' . $term->term_id . '">' . $term->name . '</option>';
					} else if (! $parent) {
						echo '<option value="' . $term->term_id . '">' . $term->name . '</option>';
					}
				
				}
			}
			echo '</select></div>';
		}
	}
	public function populateSubcategories() {
		if ( $_POST && wp_verify_nonce ( $_POST['mediateca-nonce'], 'mediateca-check-nonce' ) ) {
			$args = array ('hide_empty' => self::HIDE_EMPTY, 'hierarchical' => true, 'child_of' => $_POST ['parent'] );
			$this->taxonomySelect ( 'sottocategoria', 'categoria', $args, false, 'Sottocategoria', 'hidden' );
			die ( '' );
		}
		else
		{
			die('nonce problem');
		}
	}
	public function isAjax() {
		return (isset ( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) && ($_SERVER ['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
	}
	public function grabPostThumbIfAny( $post_id, $size =  'thumbnail' )
	{
		$id = $post_id;
		$thumb = get_the_post_thumbnail($id, $size);
		
		if( $thumb ) 
		{
			return '<a href="'.get_permalink( $id ).'" class="mediateca-image-anchor">'.$thumb.'</a>';
		}
		else if( get_post_meta($id, '_mediateca_featured_image', true) )
		{
			switch( $size )
			{
				case 'thumbnail':
				$wh = ' width="180px" height="135px"';
				break;
				case 'mediateca-thumb':
				$wh = ' width="100px" height="75px"';
				break;
			}
			$thumb = get_post_meta($id, '_mediateca_featured_image', true);
			
			if( strpos( $thumb, 'ImmaginiDB') )
			{
				$thumb = str_replace('/ImmaginiDB/', '', $thumb);
			}
			$upload = wp_upload_dir();
			
			$thumb = $upload['baseurl'] . '/ImmaginiDB/' . $thumb;
			
			return '<a href="'.get_permalink( $id ).'" class="mediateca-image-anchor"><img src="'.$thumb.'" class="mediateca-thumbs" id="mediateca-thumbs_'.$id.'"  '.$wh.'/></a>';
		}
		return false;
	}
	public function printAuthorsAndIllustratorsIfAny( $post_id )
	{
		global $mediatecaAdmin;
		$meta_prefix = $mediatecaAdmin->meta_prefix;
		$html = '<small class="postmetadata mediateca-metadata">';
		$data = '';
		$id = $post_id;
		$author = get_post_meta( $id, $meta_prefix . 'autori', true );
		$illustrator = get_post_meta( $id, $meta_prefix . 'illustratori', true );
		if( $author ) $data .=  $author . '. ';
		if( $illustrator ) $data .= ' <strong>Illustrazioni di:</strong> ' . $illustrator;
		
		if( $data != '' ) 
		{
			$html .= rtrim( $data, ', ') . '</small>';
			return '<small class="postmetadata mediateca-metadata">' . $html . '</small>';
		}
		return '';
	}
}
?>