<?php get_header(); ?>

<div id="content">
	<?php get_template_part('sidebar_left');?>
	<div class="wrap-center-content">
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
		<!--  ADVANCED FORM  -->
		<div id="mediateca-form-container" class="mediateca-forms-container">
			<h4>Ricerca avanzata</h4>
		<form action="<?php echo the_permalink(); ?>" method="post" accept-charset="utf-8" id="libri-form" class="mediateca-form">
		<?php wp_nonce_field('mediateca-check-libri-nonce','mediateca-libri-nonce'); ?>
		<input type="hidden" name="action" value="<?php echo MEDIATECA_LIBRI_SEARCH; ?>" id="<?php echo MEDIATECA_LIBRI_SEARCH; ?>" />
		<input type="hidden" name="results" value="<?php echo LIBRI_SLUG;?>" id="<?php echo LIBRI_SLUG;?>" />
		<label>Sezione</label>
		<div class="select-container external-label">
		<span><input type="radio" name="sezione-libri" value="libri-accessibili" checked="checked"/><label>Libri accessibili</label></span>
		<span><input type="radio" name="sezione-libri" value="libri-sulla-disabilita" /> <label>Libri sulla disabilit&agrave;</label></span>
		</div>
		
		<div id="libri-removables-container">
		<?php include 'libri-accessibili_block.php';?>
		</div>
		</form>
		</div>
		
<?php endwhile; else: ?>

	<p>Sorry, no posts matched your criteria.</p>

<?php endif; ?>
</div>
<?php
do_action('render_search_results');
?>
</div>
</div>
<?php get_footer(); ?>
