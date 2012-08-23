<div class="main forIE main-large <?php echo $visible;?>" id="search-results">
	<h2 class="orange main-large"><?php echo count( $search ); ?> risultati per la ricerca di "<?php echo ucfirst( $wp->query_vars[MEDIATECA_TEXT_SEARCH] ); ?>" </h2>
	<?php
		if( count( $search ) > 0 ):
		
		foreach($search as $result):
		?>
		
		
	 	<div class="entry search-entry main-large">
			<h4 class="search-result-entry-title"><a href="<?php echo get_permalink($result->ID); ?>"><?php echo ucfirst($result->post_title); ?></a></h4>
			<p>
			<?php 
				echo $result->post_content;
			?>
			</p>
		</div>
<?php 
endforeach; 
else: ?>
<div class="entry search-entry">
	<p>Siamo spiacenti, la tua ricerca non ha prodotto risultati.</p>
</div>
<?php endif; ?>

