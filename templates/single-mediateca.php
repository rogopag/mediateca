<?php 
global $mediatecaRender;
get_header(); ?>

<div id="content">

	<?php include_once 'mediateca_sidebar.php';?>
	<div class="main forIE">
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<h2 class="orange"><?php the_title(); ?></h2>
			<p class="postmetadata">Pubblicato in <?php echo dito_printObjectTermsInNiceFormat( $post->ID ); ?></p>
			<div class="entry">
				<?php the_content('<p class="serif">Read the rest of this page &raquo;</p>'); ?>
				<small>
					<?php 
				if (is_user_logged_in())
				{
					edit_post_link();
				}
				?>
			</small>
			<small class="smallAlignRight">
				<?php if(function_exists('wp_print')) { print_link(); } ?>  	
			</small>		
		</div>

		<div class="comments">
<?php 
		if ( $mediatecaRender->show_comments )
		{
			comments_template();
		} 
		?>
	</div>
<?php endwhile; else: ?>

	<p>Sorry, no posts matched your criteria.</p>

<?php endif; ?>
</div>
<?php //get_sidebar(); ?>
</div>

<?php get_footer(); ?>
