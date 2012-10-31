<div id="libri-form-removables">
	<div class="select-container select-container-libri libri-container-list">
		<label>Si parla di:</label>
		<?php Mediateca_Admin::render_hierarchical_checkboxes( array('taxonomy'=>'tipo-di-handicap') );?>
	</div>
	<div class="select-container select-container-libri">
	<?php Mediateca_Render::taxonomySelect( 'genere', 'genere', array( 'hide_empty' => false ), 0, 'Genere', 'visible' ); ?>
	</div>
	<div class="select-container select-container-libri submit-container">
	<?php Mediateca_Render::taxonomySelect( 'eta', 'eta', array( 'hide_empty' => false ), 0, 'Fascia di et&agrave;', 'visible' ); ?>
	<span class="span-submit libri-submit"><input name="submit-search" type="image" value="submit" src="<?php bloginfo('url'); ?>/wp-content/themes/area/imgs/search.gif"></span>
	</div>
</div>