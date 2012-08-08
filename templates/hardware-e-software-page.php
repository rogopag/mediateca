<?php get_header(); ?>
<!--  CONTENT  -->
<div id="content">
	<?php get_template_part('sidebar_left');?>
	<div class="main forIE main-large">
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<h2 class="orange main-large"><?php the_title(); ?></h2>
			<div class="entry intro main-large">
				<?php the_content('<p class="serif">Read the rest of this page &raquo;</p>'); ?>
				<small class="float-right">
					<?php  
				if (is_user_logged_in())
				{
					edit_post_link();
				}
				?>
			</small>		
		</div>
<!--  FORM  -->
<form action="<?php echo the_permalink(); ?>" method="post" accept-charset="utf-8" id="hardware-and-software-form">
<?php wp_nonce_field('mediateca-check-nonce','mediateca-nonce');; ?>
<input type="hidden" name="action" value="hardware-e-software-search" id="hardware-e-software-search" />
<input type="hidden" name="results" value="hardware-e-software" id="hardware-e-software" />
	

<p><input type="submit" value="Cerca"></p>
</form>

<!--  END FORM  -->
<?php endwhile; else: ?>
<p>Sorry, no posts matched your criteria.</p>
<?php endif; ?>
<!--  END CONTENT  -->
<?php
if($_POST && $_POST['results']):
include MEDIATECA_TEMPLATE_PATH . HARDWARE_SOFTWARE_SLUG.'-'.MEDIATECA_RESULTS_PAGE.'-page.php';
endif; ?>

</div>
<?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
