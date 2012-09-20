<?php
error_reporting(E_ALL); 
ini_set("display_errors", 0); 
class Batch_Mediateca
{
	private $db, $dbUser, $dbPassword, $dbHost, $batch_db, $wp, $admin, $cards_count;
	
	public function __construct()
	{
		global $wpdb, $mediatecaAdmin;
		$this->admin = $mediatecaAdmin;
		$this->db = 'mediateca';
		$this->dbUser = DB_USER;
		$this->dbPassword = DB_PASSWORD;
		$this->dbHost = DB_HOST;
		$this->batch_db = new wpdb($this->dbUser, $this->dbPassword, $this->db, $this->dbHost);
		$this->wp = $wpdb;
		add_action('admin_menu', array(&$this, 'initAdmin'), 10);
	}
	public function initAdmin()
	{
		add_menu_page( 'Batch Mediateca', 'Batch Mediateca', 'manage_options', 'batch-mediateca', array( &$this, 'printAdminScreen' ) );
	}
	public function printAdminScreen()
	{
		$form = '<form id="Batch_Terms_submit"  action="'. $_SERVER['REQUEST_URI'] .'" method="post" accept-charset="utf-8" name="Batch_Terms_submit">
		<p><input type="submit" value="Batch Terms" name="batch_terms" /></form></p>';
		$form .= '<form id="Batch_Posts_submit"  action="'. $_SERVER['REQUEST_URI'] .'" method="post" accept-charset="utf-8" name="Batch_Posts_submit">
		<p><input type="submit" value="Batch Posts" name="batch_posts" /></form></p>';
		$form .= '<form id="Batch_Libri_Terms_submit"  action="'. $_SERVER['REQUEST_URI'] .'" method="post" accept-charset="utf-8" name="Batch_Libri_Terms_submit">
		<p><input type="submit" value="Batch Libri Terms" name="batch_libri_terms" /></form></p>';
		echo $form;
		
		$this->doBatches();
	}
	private function doBatches()
	{
		if( $_POST['batch_terms'] )
		{
			
			$this->createTerms();
		}
		if( $_POST['batch_posts'])
		{
			$this->savePostsAndPostData();
		}
		if( $_POST['batch_libri_terms'])
		{
			$this->batch_libri_terms();
		}
	}
	private function batch_libri_terms()
	{
		$tax_arr = $this->libriTaxonomiesData();
		
		foreach( $tax_arr as $key => $val )
		{
			foreach( $val as $term )
			{
				$t =  $this->doSlugFromTermName( strtolower( $term ) );
				$this->insertTerm( ucfirst($term), $key, array('slug' => $t ) );
				print 'Inserted term <strong>' . $t . '</strong> nice name is '.$term.' in taxonomy <strong>' .$key. '</strong><br />';
			}
			print '<br />________________________________________________________________________________________<br />';
		}
	}
	private function savePostsAndPostData()
	{
		$cards = $this->batch_db->get_results("SELECT * FROM Schede");
		
		$this->cards_count = count( $cards );
		
		foreach($cards as $card)
		{
			$this->insertPost( $card );
		}
	}
	private function insertPost( $card )
	{
		global $current_user;
		
		static $count_h = 0;
		
		static $count_s = 0;
		
		$title = ucfirst( mb_strtolower( $card->Titolo ) );
		
		$type = mb_strtolower($card->Tipologia);
		
		get_currentuserinfo();
		
		$user = $current_user;
		
		$exists = $this->wp->get_var("SELECT post_title FROM wp_posts WHERE post_title = '$title' AND post_type = '$type'");
		
		if( !$exists )
		{
			print $title . ' do not exists ' . $exists . '<br />';
			
			$postdata = array(
						'post_title' => $title,
						'post_content' => $card->Scheda,
						'post_status' => 'publish',
						'post_type' => $type,
						'post_author' => $user->ID,
						'ping_status' => get_option('default_ping_status'),
						'post_parent' => 0,
						'menu_order' => 0,
						'to_ping' =>  '',
						'pinged' => '',
						'post_password' => '',
						'guid' => '',
						'post_content_filtered' => '',
						'post_excerpt' => '',
						'import_id' => 0,
						'post_date' => $card->DataModifica,
						);
			
	
			if( $postdata['post_type'] == 'hardware' ) $count_h++;
			if( $postdata['post_type'] == 'software' ) $count_s++;
					
			$post_id = wp_insert_post( $postdata, true );
			
			
			if( $post_id  && !is_wp_error($post_id ) ) 
			{
				$this->giveTermsToPost($card, $post_id);
			}
			
			$foo = ( $postdata['post_type'] == 'hardware' ) ? $count_h : $count_s;
			
			print "Inserted post " . $post_id . ' type ' . $postdata['post_type'] . " c " . $foo;
			
			print "<p>_________________________________________________________________________________________________________</p>";
			
			$total = $count_h + $count_s;
		}
		
		print "<p>Get everything in total we have " . $total . " == " . $this->cards_count / 2 .'</p>';

	}
	private function giveTermsToPost($card, $post_id = null)
	{
		$categoria = mb_strtolower( $this->batch_db->get_var("SELECT Categoria FROM Categorie WHERE ID = '$card->IDCategoria'") );
		$parentCatId = $this->getTermId($categoria);
		$sottoCategoria = mb_strtolower( $this->batch_db->get_var("SELECT SottoCategoria FROM SottoCategorie WHERE ID = '$card->IDSottoCategoria'") );
		$catId = $this->getTermId($sottoCategoria);
		
		$terzoLivello = $this->doSlugFromTermName( mb_strtolower( $this->batch_db->get_var("SELECT TerzoLivello FROM TerzoLivello WHERE ID = '$card->TerzoLivello'") ) );
		//$sezione = $this->doSlugFromTermName( mb_strtolower($card->Sezione) );
		
		$c = wp_set_post_terms( $post_id, array($parentCatId, $catId), 'categoria', false );
		
		$t = wp_set_object_terms( $post_id, array($terzoLivello), 'terzo-livello', false );
		
		//$s = wp_set_object_terms( $post_id, array($sezione), 'sezione', false );
		
		$this->giveMetaToPost( $card, $post_id );
		
		//echo $post_id . " => " .  ' '. $c[0] . " " . $c[1] . " " . $t[0] . " " . $s[0];
		
	}
	private function giveMetaToPost($card, $post_id = null)
	{
		global $mediatecaAdmin;
		
		if($card->Riferimenti) add_post_meta( $post_id, $mediatecaAdmin->meta_prefix . 'riferimenti', $card->Riferimenti, true );
		if($card->Collocazione) add_post_meta( $post_id, $mediatecaAdmin->meta_prefix . 'collocazione', $card->Collocazione, true );
		if($card->Handicap) add_post_meta( $post_id, $mediatecaAdmin->meta_prefix . 'handicap', $card->Handicap, true );
		if($card->Scuola) add_post_meta( $post_id, $mediatecaAdmin->meta_prefix . 'scuola', $card->Scuola, true );
		if($card->Lingua) add_post_meta( $post_id, $mediatecaAdmin->meta_prefix . 'lingua', $card->Lingua, true );
		if($card->Anno) add_post_meta( $post_id, $mediatecaAdmin->meta_prefix . 'anno', $card->Anno, true );
		if($card->Collana) add_post_meta( $post_id, $mediatecaAdmin->meta_prefix . 'collana', $card->Collana, true );
		if($card->Autori) add_post_meta( $post_id, $mediatecaAdmin->meta_prefix . 'autori', $card->Autori, true );
		if($card->Disrtibutore) add_post_meta( $post_id, $mediatecaAdmin->meta_prefix . 'disrtibutore', $card->Disrtibutore, true );
		if($card->Editore) add_post_meta( $post_id, $mediatecaAdmin->meta_prefix . 'editore', $card->Editore, true );
		if($card->Sistema) add_post_meta( $post_id, $mediatecaAdmin->meta_prefix . 'sistema', $card->Sistema, true );
		if($card->HardwareNecessario) add_post_meta( $post_id, $mediatecaAdmin->meta_prefix . 'hardware_necessario', $card->HardwareNecessario, true );
		if($card->Immagine) 
		{
			if( strpos( $card->Immagine, 'ImmaginiDB') )
			{
				$link = str_replace('~/ImmaginiDB/', '', $card->Immagine);
			}
			elseif( strpos( $card->Immagine, "\\" ) )
			{
				$tmp = explode("\\", $card->Immagine);
				$link = $tmp[1];
			}
			else 
			{
				$link = $card->Immagine;
			}
			
			$upload = wp_upload_dir();
			
			if( file_exists( $upload['basedir'].'/ImmaginiDB/'.$link ) && $link != 'logo.jpg' )
			{
				print " Image for this post is " . $link . ' originally was ' . $card->Immagine . " and the path is " . $upload['basedir'].'/ImmaginiDB/'.$link;
			
				add_post_meta( $post_id, $mediatecaAdmin->meta_prefix . 'featured_image', $link, true );
			}
		}
	}
	private function createTerms()
	{
		$termsData = $this->batch_db->get_col("SELECT Categoria FROM Categorie");
		
		foreach( $termsData as $term )
		{
			if( $term != 'APPRENDIMENTO' )
			{
				$normal = mb_strtolower($term);
				$this->insertTerm( ucfirst( $normal ), 'categoria', array('slug' => $this->doSlugFromTermName($normal) ) );
				print ucfirst( $normal ) . " " . mb_strtolower( $this->doSlugFromTermName($normal) ) . "<br />";
			}
		}
		
		print "<p>_____________________________________________________________________________________________</p>";
		
		$termsData = $this->batch_db->get_results("SELECT IDCategoria, SottoCategoria FROM SottoCategorie");
		
		foreach( $termsData as $term )
		{
			$normal = mb_strtolower($term->SottoCategoria);
			$parent =  ucfirst( mb_strtolower( $this->batch_db->get_var("SELECT Categoria FROM Categorie WHERE ID = '$term->IDCategoria'") ) );
			if( $parent == 'Apprendimento' )
			{
				$parent_term_id = 0;
			}
			else 
			{
				$parent_term_id = $this->getTermId( $parent );
			}
			
			print "<strong>SottoCategoria:: </strong>".ucfirst( $normal ) . " <strong>slug::</strong> " . $this->doSlugFromTermName($normal) . " <strong>parent::</strong> " . ucfirst($parent) . " <strong>parent slug::</strong> " . $this->doSlugFromTermName($parent) ."<br />";
			$this->insertTerm( ucfirst( $normal ), 'categoria', array('slug' => $this->doSlugFromTermName($normal), 'parent' =>  $parent_term_id ) );
		}
		
	}
	private function getTermId( $name )
	{
			$term = term_exists( $name, 'categoria' ); // array is returned if taxonomy is given
			$term_id = $term['term_id']; // get numeric term id
			return $term_id;
	}
	private function doSlugFromTermName( $name )
	{
		$str = str_replace(array(" ", "'"), "-", $name);
		return $this->unaccent($str);
	}
	private function insertTerm ($term, $taxonomy, $args = array()) 
	{
	        if (isset($args['parent'])) {
	            $parent = $args['parent'];
	        } else {
	            $parent = 0;
	        }
	        $result = term_exists($term, $taxonomy, $parent);
	        if ($result == false || $result == 0) {
	            return wp_insert_term($term, $taxonomy, $args);             
	        } else {
	            return is_object( $result ) ? (array) $result : $result;
	        }       
	}
	//This cool method removes special chars
	private function unaccent($string)
	{
		return preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'));
	}
	private function libriTaxonomiesData()
	{
			$populate = array(
			'sezione-libri' => array(
				'Libri accessibili',
				'Libri sulla disabilita',
			),
			'tipo-di-libro' => array(
				'Libro tradizionale a stampa',
				'Libro digitale',
				'Libro tattile',
				'Libro corredato da simboli',
				'Libro con traduzione in LIS-Lingua dei Segni Italiana',
				'Libro con carattere specifico per la dislessia',
				'Audiolibro',
				'Libro senza parole',
				'altro'
			),
			'tipo-di-handicap' => array(
				'Nessuna disabilita',
				'Disabilita uditiva',
				'Disabilita visiva',
				'Disabilita intellettiva',
				'Disabilita motoria',
				'Autismo e disturbi della comunicazione',
				'DSA-Disturbi Specifici dell\'Apprendimento',
			),
			'accessibilita-secondaria' => array(
				'Nessuna disabilita',
				'Disabilita uditiva',
				'Disabilita visiva',
				'Disabilita intellettiva',
				'Disabilita motoria',
				'Autismo e disturbi della comunicazione',
				'DSA-Disturbi Specifici dell\'Apprendimento',
			),
			'difficolta-compensata' => array(
				'Nessuna',
				'Manipolazione dell\'oggetto-libro',
				'Cecita',
				'Ipovisione',
				'Riconoscimento delle lettere e delle parole',
				'Comprensione del testo',
				'Comprensione del senso della frase',
				'Comprensione del lessico',
				'Comprensione delle immagini',
				'Attenzione',
				'Pronuncia ad alta voce del testo-difficolta fonologiche',
				'Memoria',
				'Altro',
			),
			'genere' => array(
				'albo',
				'fiaba',
				'poesie e filastrocche',
				'racconto',
				'romanzo',
				'diario',
				'fumetto',
				'altro'
			),
			'temi-trattati' => array(
				'affetti',
				'amicizia',
				'avventura',
				'disabilita',
				'diversita',
				'ecologia',
				'emozioni',
				'famiglia',
				'fantasy',
				'fantascienza',
				'giallo indagini misteri',
				'guerra e conflitti',
				'mitologia',
				'sport',
				'storia',
				'vita scolastica',
				'viaggio',
				'altro'
			),
			'personaggi' => array(
				'animali',
				'animali che si comportanmo comne umani',
				'persone',
				'oggetti animati',
				'mostri e creature fantastiche',
				'personaggi di fantasia visti in tv',
				'altro'
			),
			'eta' => array(
				'0-2-anni',
				'3-5-anni',
				'6-8-anni',
				'9-11-anni',
				'12-14-anni',
			),
			'ambiente-prevalente' => array(
				'domestico',
				'scolastico',
				'urbano',
				'naturale',
				'fantastico',
				'altro'
			),
			'codici-utilizzati' => array(
				'testo a stampa',
				'braille',
				'lingua italiana dei segni',
				'simboli pcs',
				'altri tipi di simboli',
				'altro'
			),
			'materiale-di-base' => array(
				'Carta',
				'Cartoncino rigido',
				'Carta plastificata',
				'Plastica',
				'Stoffa',
				'Legno',
				'Altro'
			)
		);
		return $populate;
	}
}
?>