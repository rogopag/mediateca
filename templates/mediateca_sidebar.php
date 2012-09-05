<div id="sidebarLeft">
<?php
echo '<div class="sideLeftInt">';
if (function_exists('bcn_display'))
{
	// Display the breadcrumb
	bcn_display();
} echo"</div>";
?>
<div class="mediateca-sidebar-thumb">
<?php
echo $mediatecaRender->grabPostThumbIfAny( $post->ID );
?>
</div>
<?php
echo '<div class="arealog logfl"><a href="http://www.areato.org/" target="_blank"><img alt="" src="http://dito.areato.org/wp-content/themes/area/imgs/arealog.jpg" class="imgLogo"/></a></div>';
?>
</div>