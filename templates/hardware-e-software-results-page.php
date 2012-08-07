<?php get_header(); 
print_r($_POST);
?>
<!--  CONTENT  -->
<div id="content">
	<?php get_template_part('sidebar_left');?>
	<div class="main forIE">
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<h2 class="orange"><?php the_title(); ?> risultati</h2>
			<div class="entry intro">
					
			</div>
<?php endwhile; else: ?>
	<p>Sorry, no posts matched your criteria.</p>
<?php endif; ?>
<!--  END CONTENT  -->


</div>
<?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
