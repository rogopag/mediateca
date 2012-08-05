<?php if( class_exists("Mediateca_Render") && is_array( $posts ) ): ?>
<div class="forums">
<h2 class="green">Mediateca</h2>
<?php
foreach( $posts as $p ):
?>
	<h4><a href="<?php echo get_permalink($p->ID);  ?>"><?php echo $p->post_title; ?></a></h4>
	<em><?php echo Mediateca_Render::getUserNiceName( $p->post_author ); ?></em>
	<p> <?php echo dito_shortern_content( $p->post_content ) ?> [<a href="<?php echo get_permalink($p->ID);  ?>" >...</a>]</p>
	
<?php endforeach; ?>

</div>
<?php endif; ?>