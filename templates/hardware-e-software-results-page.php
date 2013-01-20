<?php $title = ( $wp->query_vars[MEDIATECA_TEXT_SEARCH] ) ? ucfirst($wp->query_vars[MEDIATECA_TEXT_SEARCH]) : ucfirst( $this->type ); 
?>
<div class="main forIE main-large <?php echo $visible . ' ' . strtolower($title);?> padding-194" id="search-results">
	<h2 class="orange main-large">
	<?php printf( ( $search->found_posts == 1 ) ? "%d risultato per %s" : "%d risultati per %s" , $search->found_posts, $title);?></h2>
	<?php if ($search->have_posts()) : while ($search->have_posts()) : $search->the_post(); ?>
		<div class="entry search-entry main-large">
			<?php echo $this->grabPostThumbIfAny( get_the_ID(), 'mediateca-thumb' ); ?>
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
<?php 
endwhile; 
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