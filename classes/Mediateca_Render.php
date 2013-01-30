<?php
error_reporting ( E_ALL );
ini_set ( "display_errors", 0 );
class Mediateca_Render {
	
	public $show_comments;
	
	public static $PAGES_SLUG = array (MEDIATECA_SLUG, HARDWARE_SOFTWARE_SLUG, LIBRI_SLUG, APP_PER_TABLET_SLUG );
	
	const POSTS_PER_PAGE = 10;
	const HIDE_EMPTY = 1;
	
	private $types;
	private $type;
	private $taxonomies = array ( );
	private $metas = array ( );
	private $number_of_pages = 1;
	private $mother_page;
	private $pagename;
	
	public function __construct() {
		$this->initSession ();
		
		$this->types = Mediateca_Init::$types;
		
		$this->show_comments = false;
		
		add_action ( 'sidebar_left_home_first_box', array (&$this, 'sidebarLeftBox' ), 10 );
		add_filter ( 'single_template', array (&$this, 'get_custom_post_type_single_template' ), 11 );
		
		$this->giveTemplatePages ();
		
		add_action ( 'wp_ajax_' . MEDIATECA_HARDWARE_AND_SOFTWARE_SEARCH, array (&$this, 'ajaxResult' ) );
		add_action ( 'wp_ajax_nopriv_' . MEDIATECA_HARDWARE_AND_SOFTWARE_SEARCH, array (&$this, 'ajaxResult' ) );
		
		add_action ( 'wp_ajax_manage_category_select', array (&$this, 'populateSubcategories' ) );
		add_action ( 'wp_ajax_nopriv_manage_category_select', array (&$this, 'populateSubcategories' ) );
		
		add_action ( 'wp_ajax_do_text_search', array (&$this, 'doTextSearch' ) );
		add_action ( 'wp_ajax_nopriv_do_text_search', array (&$this, 'doTextSearch' ) );
		
		add_action ( 'wp_ajax_change-sezione-libri', array (&$this, 'manageSezioneLibri' ) );
		add_action ( 'wp_ajax_nopriv_change-sezione-libri', array (&$this, 'manageSezioneLibri' ) );
		
		add_action ( 'wp_ajax_' . MEDIATECA_LIBRI_SEARCH, array (&$this, 'ajaxLibriResult' ) );
		add_action ( 'wp_ajax_nopriv_' . MEDIATECA_LIBRI_SEARCH, array (&$this, 'ajaxLibriResult' ) );
		
		add_action ( 'print_mediateca_menu_links', array (&$this, 'printMediatecaMenuLinks' ), 10 );
	
	}
	public function tax_posts_join($a, $b) {
		return $a;
	}
	//This one makes sense of the double category query with OR operator
	public function tax_posts_where($a, $b) {
		$t = $b->get ( 'tax_query' );
		$prev = 0;
		
		foreach ( $t as $q ) {
			if ($q ['taxonomy'] == 'tipo-di-handicap' || $q ['taxonomy'] == 'accessibilita-secondaria') {
				
				if ($prev % 2 == 0) {
					$term = $this->getTermTaxonomyID ( $q ['terms'], $q ['taxonomy'] );
					$a = str_replace ( 'IN (' . $term . ') AND', 'IN (' . $term . ') OR', $a );
				}
				
				$prev ++;
			}
		}
		foreach ( $t as $q ) {
			if ($q ['taxonomy'] == 'tipo-di-handicap' || $q ['taxonomy'] == 'accessibilita-secondaria') {
				$a = str_replace ( 'AND tt', 'AND (tt', $a );
				$term = $this->getTermTaxonomyID ( $q ['terms'], $q ['taxonomy'] );
				$a = str_replace ( 'IN (' . $term . ') )', 'IN (' . $term . ') ) )', $a );
				$a = str_replace ( 'IN (' . $term . ') AND', 'IN (' . $term . ') ) AND', $a );
			}
		}
		
		return $a;
	}
	public function tax_posts_request($a) {
		return $a;
	}
	private function giveTemplatePages() {
		foreach ( self::$PAGES_SLUG as $func ) {
			$method = str_replace ( '-', '_', $func ) . 'Template';
			
			if (method_exists ( &$this, $method ))
				add_filter ( 'page_template', array (&$this, $method ) );
		}
	}
	public function printMediatecaMenuLinks() {
		$html = '';
		$pages = array (LIBRI_SLUG, HARDWARE_SOFTWARE_SLUG, APP_PER_TABLET_SLUG );
		$count = 1;
		$len = count ( $pages );
		$class = 'com';
		
		foreach ( $pages as $page ) {
			if ($count == $len)
				$class = 'comLast';
			$html .= '<div class="' . $class . '"><a href="' . get_bloginfo ( 'url' ) . '/' . MEDIATECA_SLUG . '/' . $page . '">' . $this->niceNameFromSlug ( $page ) . '</a></div>';
			$count ++;
		}
		echo $html;
	}
	private function niceNameFromSlug($name) {
		return ucfirst ( str_replace ( '-', ' ', $name ) );
	}
	private function initSession() {
		if (! session_id ()) {
			session_start ();
		}
	}
	public function ajaxLibriResult() {
		global $wp;
		$this->taxonomies = array ( );
		
		//make sure add this plugin shit doesn't bother
		if (function_exists ( 'addthis_init' )) {
			remove_filter ( 'the_content', 'addthis_display_social_widget', 15 );
			remove_filter ( 'get_the_excerpt', 'addthis_display_social_widget_excerpt', 11 );
		}
		
		$this->pagename = isset ( $_POST ['pagename'] ) ? $_POST ['pagename'] : $wp->query_vars ['pagename'];
		
		if ($_POST && wp_verify_nonce ( $_POST ['mediateca-libri-nonce'], 'mediateca-check-libri-nonce' )) {
			$this->type = $_SESSION ['media_type'] = LIBRI_TYPE;
			
			$section = $_POST ['sezione-libri'];
			
			$age = $_POST ['eta'];
			
			$book_type = $_POST ['tipo-di-libro'];
			
			//$secondary = $_POST ['tax_input'] ['accessibilita-secondaria'];
			
			$this->taxQuery ( 'sezione-libri', $section, 'slug' );
			$this->taxQuery ( 'eta', $age );
			$this->taxQuery ( 'tipo-di-libro', $book_type );
			
			$accessibilities = $_POST ['tax_input'] ['tipo-di-handicap'];
			foreach ( $accessibilities as $accessibility ) {
				$this->taxQuery ( 'tipo-di-handicap', $accessibility, 'id', 'IN' );
				//$this->taxQuery ( 'accessibilita-secondaria', $accessibility );
			}
			
			$_SESSION ['previous_query'] = null;
			
			if ($this->isAjax ())
				$visible = 'hidden';
				
			//	echo '<br />___________________________________________________________';
			//	print_r( $this->taxonomies );
			

			$search = $this->getQueryObject ( $this->type, $this->taxonomies );
			
			include_once MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG . '-' . MEDIATECA_RESULTS_PAGE . '-page.php';
		
		} else if (($_GET && $_GET ['results'] == $this->mother_page) || ($_POST && $_POST ['paginated'])) {
			$this->type = $_SESSION ['media_type'];
			
			$search = $this->getQueryObject ( null, null, null, true );
			
			include_once MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG . '-' . MEDIATECA_RESULTS_PAGE . '-page.php';
			
			if ($this->isAjax ())
				die ( '' );
		} else {
			die ( 'Problema di validazione del form' );
		}
		
		if ($this->isAjax ())
			die ( '' );
	}
	public function manageSezioneLibri() {
		if ($_POST && $_POST ['action'] == 'change-sezione-libri') {
			include MEDIATECA_TEMPLATE_PATH . $_POST ['sezione'] . '_block.php';
		}
		die ( '' );
	}
	public function ajaxResult() {
		global $wp;
		
		$this->taxonomies = array ( );
		
		//make sure add this plugin shit doesn't bother
		if (function_exists ( 'addthis_init' )) {
			remove_filter ( 'the_content', 'addthis_display_social_widget', 15 );
			remove_filter ( 'get_the_excerpt', 'addthis_display_social_widget_excerpt', 11 );
		}
		
		$this->pagename = isset ( $_POST ['pagename'] ) ? $_POST ['pagename'] : $wp->query_vars ['pagename'];
		
		//check if it is a from submission and we ave something
		if (($_POST && $_POST ['media_type'])) {
			
			//check if the form was submitted from our form 
			if (wp_verify_nonce ( $_POST ['mediateca-nonce'], 'mediateca-check-nonce' )) {
				
				//fill up the vars and render
				$this->type = $_SESSION ['media_type'] = $_POST ['media_type'];
				
				$categoria = $_POST ['sottocategoria'] ? $_POST ['sottocategoria'] : $_POST ['categoria'];
				
				$this->taxQuery ( 'categoria', $categoria );
				
				$this->taxQuery ( 'terzo-livello', $_POST ['terzo-livello'] );
				
				//keep track of the last query to paginate results
				$_SESSION ['previous_query'] = null;
				
				if ($this->isAjax ())
					$visible = 'hidden';
				
				$search = $this->getQueryObject ( $this->type, $this->taxonomies );
				
				include_once MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG . '-' . MEDIATECA_RESULTS_PAGE . '-page.php';
				
				if ($this->isAjax ())
					die ( '' );
			} else {
				die ( "Problema di validazione del form." );
			}
		} else if (($_GET && $_GET ['results'] == $this->mother_page) || ($_POST && $_POST ['paginated'])) {
			$this->type = $_SESSION ['media_type'];
			
			$search = $this->getQueryObject ( null, null, null, true );
			
			include_once MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG . '-' . MEDIATECA_RESULTS_PAGE . '-page.php';
			
			if ($this->isAjax ())
				die ( '' );
		}
	
	}
	private function styleAndScripts() {
		global $post, $wp;
		
		$wp->query_vars ['current_page'] = $post->post_name;
		
		wp_enqueue_style ( 'mediateca-front', MEDIATECA_URL . 'css/style.css', '', '0.1', 'screen' );
		wp_enqueue_script ( 'mediateca-js', MEDIATECA_URL . 'js/js.js', array ('jquery' ), '0.1', 'screen' );
		wp_localize_script ( 'mediateca-js', 'Mediateca', array ('plugin_url' => MEDIATECA_URL, 'ajaxurl' => admin_url ( 'admin-ajax.php' ), 'page' => get_permalink ( $post->ID ), 'query' => $wp->query_vars, 'slug' => $post->post_name ) );
	}
	public function hardware_e_softwareTemplate($page_template) {
		global $post, $wp;
		
		if ($post->post_name == HARDWARE_SOFTWARE_SLUG && file_exists ( MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG . '-page.php' )) {
			$this->mother_page = $post->post_name;
			
			$this->styleAndScripts ();
			
			if (isset ( $wp->query_vars ['results'] ) && $wp->query_vars ['results'] && $wp->query_vars ['results'] == HARDWARE_SOFTWARE_SLUG) {
				add_action ( 'render_search_results', array (&$this, 'ajaxResult' ) );
			} elseif (isset ( $wp->query_vars [MEDIATECA_TEXT_SEARCH] ) && $wp->query_vars [MEDIATECA_TEXT_SEARCH]) {
				add_action ( 'render_search_results', array (&$this, 'doTextSearch' ) );
			}
			
			$page_template = MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG . '-page.php';
		
		}
		return $page_template;
	}
	public function meta_posts_join($a,$b)
	{
		return $a;
	}
	public function meta_posts_where($a,$b)
	{
		
		$a = str_replace('AND ( (wp_postmeta', 'OR ( (wp_postmeta', $a);
		return $a;
	}
	public function doTextSearch() {
		global $wp, $mediatecaAdmin;
		
		add_filter( 'posts_join', array(&$this, 'meta_posts_join'), 10, 2 );
		add_filter( 'posts_where', array(&$this, 'meta_posts_where'), 10, 2 );
		add_filter( 'posts_request', array(&$this, 'tax_posts_request'), 10, 1 );
		
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
			
			$args = array ( 's' => $wp->query_vars [MEDIATECA_TEXT_SEARCH], 
							'post_type' => $types, //'showposts' => 50
							'meta_query' => array(
							'relation' => 'OR',
								array(
									'key' => $mediatecaAdmin->meta_prefix . 'autori',
									'value' => $wp->query_vars [MEDIATECA_TEXT_SEARCH],
									'type' => 'string',
									'compare' => 'LIKE'
								),
								array(
									'key' => $mediatecaAdmin->meta_prefix . 'illustratori',
									'value' => $wp->query_vars [MEDIATECA_TEXT_SEARCH],
									'type' => 'string',
									'compare' => 'LIKE'
								),
							/*	array(
								'key' => $mediatecaAdmin->meta_prefix . 'editore',
								'value' => $wp->query_vars [MEDIATECA_TEXT_SEARCH],
								'type' => 'string',
								'compare' => 'LIKE'
								)*/
							),
							'paged' => $this->getCurrent (), 
							'posts_per_page' => self::POSTS_PER_PAGE, 
							'orderby' => 'title',
							'post_status' => 'publish', 
							'order' => 'ASC' );
			
			$search = new WP_Query ( $args );
			
			$this->number_of_pages = $search->
			max_num_pages;
			
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
			
			if (isset ( $wp->query_vars ['results'] ) && $wp->query_vars ['results'] && $wp->query_vars ['results'] == LIBRI_SLUG) {
				add_action ( 'render_search_results', array (&$this, 'ajaxLibriResult' ) );
			} elseif (isset ( $wp->query_vars [MEDIATECA_TEXT_SEARCH] ) && $wp->query_vars [MEDIATECA_TEXT_SEARCH]) {
				add_action ( 'render_search_results', array (&$this, 'doTextSearch' ) );
			}
			
			$page_template = MEDIATECA_TEMPLATE_PATH . LIBRI_SLUG . '-page.php';
		}
		return $page_template;
	}
	public function app_per_tabletTemplate($page_template) {
		global $post;
		
		if ($post->post_name == APP_PER_TABLET_SLUG && file_exists ( MEDIATECA_TEMPLATE_PATH . APP_PER_TABLET_SLUG . '-page.php' )) {
			$this->styleAndScripts ();
			
			$page_template = MEDIATECA_TEMPLATE_PATH . APP_PER_TABLET_SLUG . '-page.php';
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
			$this->styleAndScripts ();
			$single_template = MEDIATECA_TEMPLATE_PATH . '/single-' . MEDIATECA_SLUG . '.php';
			
			//if we have some metadata please print them out some before and some after the content.
			add_filter ( 'the_content', array (&$this, 'printMetaBelowTitle' ), 10, 2 );
			add_filter ( 'get_the_excerpt', array (&$this, 'printMetaBelowTitle' ), 10, 2 );
			
			add_filter ( 'the_content', array (&$this, 'printMetaBelowContent' ), 10, 2 );
			add_filter ( 'get_the_excerpt', array (&$this, 'printMetaBelowContent' ), 10, 2 );
		}
		return $single_template;
	}
	public function sidebarLeftBox() {
		if (is_front_page () && file_exists ( MEDIATECA_TEMPLATE_PATH . 'sidebar_boxes.php' )) {
			$posts = $this->getPosts ( array (LIBRI_TYPE ) );
			$h2 = $this->niceNameFromSlug ( LIBRI_SLUG );
			include MEDIATECA_TEMPLATE_PATH . 'sidebar_boxes.php';
			rewind_posts ();
			$h2 = $this->niceNameFromSlug ( HARDWARE_SOFTWARE_SLUG );
			$posts = $this->getPosts ( array (HARDWARE_TYPE, SOFTWARE_TYPE ) );
			include MEDIATECA_TEMPLATE_PATH . 'sidebar_boxes.php';
			rewind_posts ();
		}
	}
	public function getPosts($types, $number = 2, $taxonomies = array(), $metas = array()) {
		$args = array ('posts_per_page' => $number, 'offset' => 0, 'tax_query' => $taxonomies, 'orderby' => 'post_date', 'order' => 'DESC', 'include' => '', 'exclude' => '', 'meta_query' => $metas, 'post_type' => $types, 'post_mime_type' => '', 'post_parent' => '', 'post_status' => 'publish' );
		
		$ps = &get_posts ( $args );
		
		return $ps;
	}
	private function getQueryObject($types, $taxonomies = array(), $metas = array(), $is_pagination_query = false) {
		
		//add_filter ( 'posts_join', array (&$this, 'tax_posts_join' ), 10, 2 );
		//add_filter ( 'posts_where', array (&$this, 'tax_posts_where' ), 10, 2 );
		//add_filter ( 'posts_request', array (&$this, 'tax_posts_request' ), 10, 1 );
		
		if ($_POST && $_POST ['pagenum']) {
			$page = $_POST ['pagenum'];
		} else {
			$page = (get_query_var ( 'paged' )) ? get_query_var ( 'paged' ) : 1;
		}
		
		$args = array ('offset' => 0, 'tax_query' => $taxonomies, 'orderby' => 'post_date', 'order' => 'DESC', 'include' => '', 'exclude' => '', 'meta_query' => $metas, 'post_type' => $types, 'post_mime_type' => '', 'post_parent' => '', 'post_status' => 'publish', 'paged' => $page, 'posts_per_page' => self::POSTS_PER_PAGE );
		
		if ($is_pagination_query == false) {
			$_SESSION ['previous_query'] = $args;
			$query = $args;
		} else if ($is_pagination_query == true) {
			$query = $_SESSION ['previous_query'];
			$query ['paged'] = $page;
		}
		
		$q = new WP_Query ( $query );
		
		$this->number_of_pages = $q->max_num_pages;
		
		return $q;
	}
	private function taxQuery($tax, $term, $field = 'id', $operator = 'IN') {
		
		if (! $term || $term == - 1)
			return array ( );
		
		$query = array ('taxonomy' => $tax, 'field' => $field, 'terms' => $term, 'custom_operator' => $operator, 'operator' => $operator );
		
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
			
			if (($_POST ['paginated'] && $_POST ['kind'] == MEDIATECA_HARDWARE_AND_SOFTWARE_SEARCH || $_POST ['kind'] == MEDIATECA_LIBRI_SEARCH) || ($wp->query_vars ['results'] || $_POST ['results'])) {
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
		if ($_POST && wp_verify_nonce ( $_POST ['mediateca-nonce'], 'mediateca-check-nonce' )) {
			$args = array ('hide_empty' => self::HIDE_EMPTY, 'hierarchical' => true, 'child_of' => $_POST ['parent'] );
			$this->taxonomySelect ( 'sottocategoria', 'categoria', $args, false, 'Sottocategoria', 'hidden' );
			die ( '' );
		} else {
			die ( 'nonce problem' );
		}
	}
	public function isAjax() {
		return (isset ( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) && ($_SERVER ['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
	}
	public function grabPostThumbIfAny($post_id, $size = 'thumbnail') {
		global $mediatecaAdmin;
		$id = $post_id;
		$placeholder_link = MEDIATECA_URL . 'img/giochi.jpg';
		$thumb = get_the_post_thumbnail ( $id, $size );
		
		$link = (get_post_meta ( $id, $mediatecaAdmin->meta_prefix . "riferimenti", true )) ? get_post_meta ( $id, $mediatecaAdmin->meta_prefix . "riferimenti", true ) : get_permalink ( $id );
		
		if ($thumb) {
			return '<a href="' . $link . '" class="mediateca-image-anchor" target="_blank">' . $thumb . '</a>';
		} else if (get_post_meta ( $id, '_mediateca_featured_image', true )) {
			switch ( $size) {
				case 'thumbnail' :
					$wh = ' width="180px" height="135px"';
				break;
				case 'mediateca-thumb' :
					$wh = ' width="100px" height="75px"';
				break;
			}
			$thumb = get_post_meta ( $id, '_mediateca_featured_image', true );
			
			if (strpos ( $thumb, 'ImmaginiDB' )) {
				$thumb = str_replace ( '/ImmaginiDB/', '', $thumb );
			}
			$upload = wp_upload_dir ();
			
			$thumb = $upload ['baseurl'] . '/ImmaginiDB/' . $thumb;
			
			return '<a href="' . $link . '" class="mediateca-image-anchor" target="_blank"><img src="' . $thumb . '" class="mediateca-thumbs" id="mediateca-thumbs_' . $id . '"  ' . $wh . '/></a>';
		} else {
			switch ( $size) {
				case 'thumbnail' :
					$wh = ' width="180px" height="70px"';
				break;
				case 'mediateca-thumb' :
					$wh = ' width="100px" height="41px"';
				break;
			}
			return '<a href="' . $link . '" class="mediateca-image-anchor" target="_blank"><img src=' . $placeholder_link . ' alt="giochi" ' . $wh . '/></a>';
		}
	}
	public function printAuthorsAndIllustratorsIfAny($post_id) {
		global $mediatecaAdmin;
		$meta_prefix = $mediatecaAdmin->meta_prefix;
		$html = '<small class="postmetadata mediateca-metadata">';
		$data = '';
		$id = $post_id;
		$author = get_post_meta ( $id, $meta_prefix . 'autori', true );
		$illustrator = get_post_meta ( $id, $meta_prefix . 'illustratori', true );
		if ($author)
			$data .= $author . ' ';
		if ($illustrator)
			$data .= ' <strong>illustrazioni di</strong> ' . $illustrator;
		
		if ($data != '') {
			$html .= rtrim ( $data, ', ' ) . '</small>';
			return '<small class="postmetadata mediateca-metadata">' . $html . '</small>';
		}
		return '';
	}
	private function printMetaBoxesContent($start = 0, $depth = 1) {
		global $post;
		
		$id = $post->ID;
		$tmp = array ( );
		
		$boxes = Mediateca_Admin::$meta_boxes [0];
		
		foreach ( $boxes as $box ) {
			if (in_array ( $post->post_type, $box ['pages'] ))
				array_push ( $tmp, $box );
		}
		
		$output = '';
		$a = '';
		$link = '';
		
		for($i = $start; $i < $depth; $i ++) {
			if (isset ( $tmp [$i] )) {
				$control_terms = $this->postHasTerm ( $post->ID, 'sezione-libri', 'libri-sulla-disabilita' );
				
				if ('Accessibilit&agrave; del volume' == $tmp [$i] ['title'] && $control_terms)
					$tmp [$i] ['title'] = 'Tipo di disabilit&agrave;';
				$output .= '<li class="title-boxes position_' . $start . '">' . $tmp [$i] ['title'] . '</li>';
				
				foreach ( $tmp [$i] ['fields'] as $field ) {
					if ($field ['name'] === 'Featured Image') {
						continue;
					}
					if ($field ['name'] === 'Link editore/produttore') {
						$link = get_post_meta ( $id, $field ['id'], true );
						continue;
					}
					if (array_key_exists ( 'taxonomy', $field )) {
						if ($control_terms && $field ['name'] == 'Accessibilit&agrave; primaria') {
							$field ['name'] = 'Si parla di';
						} elseif ($field ['name'] == 'Accessibilit&agrave; primaria') {
							$field ['name'] = 'Pensato per';
						}
						if ($control_terms && $field ['name'] == 'Accessibilit&agrave; secondaria') {
							$field ['name'] = 'Utile anche in caso di';
						} elseif ($field ['name'] == 'Accessibilit&agrave; secondaria') {
							$field ['name'] = 'Utile anche in caso di';
						}
						$term = dito_printObjectTermsInNiceFormat ( $id, array ($field ['taxonomy'] ) );
						
						$output .= ($term) ? '<li><strong>' . $field ['name'] . ':</strong> ' . $term . '</li>' : '';
					} elseif ($field ['name'] === 'Editore/distributore') {
						$field ['name'] = ($post->post_type == HARDWARE_TYPE) ? 'Produttore' : $field ['name'];
						$meta = get_post_meta ( $id, $field ['id'], true );
						$output .= ($meta) ? '<li><strong>' . $field ['name'] . ':</strong> <a href="' . $link . '">' . $meta . '</a></li>' : '';
					} else if (strpos ( $field ['type'], 'dio' ) && count ( $field ['options'] ) == 2) {
						$output .= $this->manageBooleanMetas ( $field ['id'], $field ['name'], $id, '' );
					} else {
						$meta = get_post_meta ( $id, $field ['id'], true );
						$meta = ($field ['name'] == 'Prezzo') ? $meta . '&euro;' : $meta;
						$meta = ($field ['type'] == 'select' && is_numeric ( $meta ) && count ( $field ['options'] ) > 2) ? $meta . ' su ' . (count ( $field ['options'] ) - 1) : $meta;
						$output .= ($meta) ? '<li><strong>' . $field ['name'] . ':</strong> ' . $meta . '</li>' : '';
					}
				}
			}
		}
		
		if ($output) {
			$a = '<ul class="mediateca-meta-below-title">' . $output . '</ul>';
		}
		
		return $a;
	}
	// Two filter functions to add metadata content to our asset
	public function printMetaBelowTitle($content) {
		$a = $this->printMetaBoxesContent ();
		return $a . $content;
	
	}
	public function printMetaBelowContent($content) {
		$a = $this->printMetaBoxesContent ( 1, 6 );
		return $content . $a;
	}
	// a method to print boolean fields in human language
	public function manageBooleanMetas($meta, $label, $id, $px = '') {
		$val = get_post_meta ( $id, $px . $meta, true );
		
		if (is_numeric ( $val )) {
			$n = (($val) == 1) ? 'SI' : 'NO';
		} else {
			$n = $val;
		}
		return ($n) ? sprintf ( '<li><strong>%s:</strong> %s </li>', $label, $n ) : '';
	}
	private function postHasTerm($id, $tax, $term) {
		$terms = wp_get_object_terms ( $id, $tax, array ('fields' => 'slugs' ) );
		
		if (in_array ( $term, $terms )) {
			return true;
		}
		return false;
	}
	private function getTermTaxonomyID($value, $taxonomy) {
		$term = get_term_by ( 'id', $value, $taxonomy );
		return $term->term_taxonomy_id;
	}
	private function getTermId($value, $taxonomy) {
		$term = get_term_by ( 'slug', $value, $taxonomy );
		return $term->term_id;
	}
}
?>