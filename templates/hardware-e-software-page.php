<?php get_header(); ?>
<!--  CONTENT  -->
<div id="content">
	<?php get_template_part('sidebar_left');?>
	<div class="wrap-center-content">
	<div class="main forIE main-large">
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<h2 class="orange main-large"><?php the_title(); ?></h2>
			<div class="entry main-large">
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
			<form action="<?php echo the_permalink(); ?>" method="post" accept-charset="utf-8" id="hardware-and-software-form" class="mediateca-form">
		<?php wp_nonce_field('mediateca-check-nonce','mediateca-nonce');; ?>
		<input type="hidden" name="action" value="hardware-e-software-search" id="hardware-e-software-search" />
		<input type="hidden" name="results" value="hardware-e-software" id="hardware-e-software" />
		<div class="select-container">
			<label for="terzo-livello">Tipologia</label><br />
			<select id="media_type" name="media_type">
				<option value="<?php echo HARDWARE_TYPE; ?>"><?php echo ucfirst(HARDWARE_TYPE); ?></option>
				<option value="<?php echo SOFTWARE_TYPE; ?>"><?php echo ucfirst(SOFTWARE_TYPE); ?></option>
			</select>
		</div>
		<?php Mediateca_Render::taxonomySelect( 'categoria', 'categoria', array( 'hide_empty' => Mediateca_Render::HIDE_EMPTY ), true, 'Categoria' ); ?>
		<div class="select-container submit-container">
			<label for="terzo-livello">Terzo livello</label><br />
			<?php  
		wp_dropdown_categories(array(
			'show_option_none' => '&#8212; Seleziona Terzo livello &#8212;',
			'hierarchical' => 0,
			'taxonomy' => 'terzo-livello',
			'orderby' => 'name', 
			'hide_empty' => Mediateca_Render::HIDE_EMPTY, 
			'name' => 'terzo-livello',
			'selected' => 0  

			));
		?>
		<span class="span-submit"><input type="submit" value="Cerca"></span>
	</div>
	
</form>
</div>
<!--  END FORM  -->
<div id="mediateca-text-search" class="mediateca-forms-container">
	<h4>Ricerca testuale</h4>
</div>
<?php endwhile; else: ?>
	<p>Siamo spiacenti, la pagina che stavi cercando non &egrave; stata trovata.</p>
<?php endif; ?>
<!--  END CONTENT  -->
</div>
<?php
do_action('render_search_results');
?>
</div>
</div>
<?php get_footer(); ?>
