<?php $title = ( $wp->query_vars[MEDIATECA_TEXT_SEARCH] ) ? ucfirst($wp->query_vars[MEDIATECA_TEXT_SEARCH]) : ucfirst( $this->type ); ?>
<div class="main forIE main-large <?php echo $visible;?>" id="search-results">
	<h2 class="orange main-large">
	<?php echo $search->found_posts .' risultati per '.  $title; ?></h2>
	<?php if ($search->have_posts()) : while ($search->have_posts()) : $search->the_post(); ?>
		<div class="entry search-entry main-large">
			<h4 class="search-result-entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
			<?php 
				the_advanced_excerpt('length=150&use_words=0&no_custom=1&ellipsis=%26hellip;&exclude_tags=img');
			?>		
		</div>
<?php 
endwhile; 
$this->paginationLinks();
wp_reset_query();
else: ?>
<div class="entry search-entry">
	<p>Siamo spiacenti, la tua ricerca non ha prodotto risultati.</p>
</div>
<?php endif; ?>

