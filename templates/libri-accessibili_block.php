<div id="libri-form-removables">
	<div class="select-container select-container-libri">
	<?php Mediateca_Render::taxonomySelect( 'tipo-di-libro', 'tipo-di-libro', array( 'hide_empty' => false, 'order' => 'DESC' ), 0, 'Tipologia di libro', 'visible' ); ?>
	</div>
	<div class="select-container select-container-libri">
	<?php Mediateca_Render::taxonomySelect( 'eta', 'eta', array( 'hide_empty' => false ), 0, 'Fascia di et&agrave;', 'visible' ); ?>
	</div>
	<div class="select-container select-container-libri submit-container">
		<label>Accessibile in caso di:</label>
		<?php Mediateca_Admin::render_hierarchical_checkboxes( array('taxonomy'=>'tipo-di-handicap') );?>
		
		<span class="span-submit libri-submit"><input name="submit-search" type="image" value="submit" src="<?php bloginfo('url'); ?>/wp-content/themes/area/imgs/search.gif" /> <span id="cerca">Cerca</span></span>
	</div>
</div>