<div class="main forIE main-large">
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<h2 class="orange"><?php the_title(); ?> risultati</h2>
		<div class="entry intro">
					
		</div>
<?php endwhile; else: ?>
	<p>Sorry, no posts matched your criteria.</p>
<?php endif; ?>

