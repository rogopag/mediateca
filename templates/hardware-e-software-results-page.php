<?php
global $post;

$suggestions = array(); 

$title = ( $wp->query_vars[MEDIATECA_TEXT_SEARCH] ) ? ucfirst($wp->query_vars[MEDIATECA_TEXT_SEARCH]) : ucfirst( $this->type ); 
?>
<div class="sidebar-results">
	<?php do_action('sidebar-results');?>
</div>
<div class="main forIE main-large <?php echo $visible . ' ' . strtolower($title);?> padding-194" id="search-results">
	<h2 class="mediateca-results-title">
	<?php printf( ( $search->found_posts == 1 ) ? "%d risultato per %s" : "%d risultati per %s" , $search->found_posts, $title);?></h2>
	<?php if ($search->have_posts()) : while ($search->have_posts()) : $search->the_post(); ?>
		<?php if( $this->post_in_second( get_the_ID(), 'tipo-di-handicap','accessibilita-secondaria' ) ):
				array_push($suggestions, $post);
		  	else:	
		?>
		<div class="entry search-entry main-large">
			<?php echo $this->grabPostThumbIfAny( get_the_ID(), 'mediateca-thumb' ); ?>
			<div class="results-text-box">
			<h4 class="search-result-entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
			<p class="postmetadata">Pubblicato in <?php echo dito_printObjectTermsInNiceFormat( get_the_ID(), array(), array('eta') ); ?></p>
			<?php 
				if( function_exists('the_advanced_excerpt') ):
					ditoDoExerpt();
				else:
					the_content();
				endif;
			?>
			</div>		
		</div>
<?php
endif;
endwhile;
if( $suggestions ):
$suggestions = array_reverse($suggestions);
?>
<h2 class="mediateca-results-title-below">Suggeriamo anche</h2>
<?php
	foreach( $suggestions as $suggestion ):
		$post = $suggestion;
		
?>
	<div class="entry search-entry main-large">
		<?php echo $this->grabPostThumbIfAny( get_the_ID(), 'mediateca-thumb' ); ?>
		<div class="results-text-box">
		<h4 class="search-result-entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
		<p class="postmetadata">Pubblicato in <?php echo dito_printObjectTermsInNiceFormat( get_the_ID(), array(), array('eta') ); ?></p>
		<?php 
			if( function_exists('the_advanced_excerpt') ):
				echo pippin_excerpt_by_id( $suggestion, 55 );
			else:
				the_content();
			endif;
		?>
		</div>		
	</div>
<?php
endforeach;
endif;
$this->paginationLinks();
wp_reset_query();
$search->rewind_posts();
else: ?>
<div class="entry search-entry">
	<p>Siamo spiacenti, la tua ricerca non ha prodotto risultati.</p>
</div>
<?php 
endif; 
?>