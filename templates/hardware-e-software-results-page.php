<div class="main forIE main-large <?php echo $visible;?>" id="search-results">
	<h2 class="orange"><?php echo ucfirst( $this->type ); ?> risultati</h2>
	<?php if ($search->have_posts()) : while ($search->have_posts()) : $search->the_post(); ?>
		<h4><?php the_title(); ?></h4>
		<div class="entry">
			<?php //the_content(); ?>		
		</div>
<?php 
endwhile; 
$this->paginationLinks();
else: ?>
	<p>Sorry, no posts matched your criteria.</p>
<?php endif; ?>

