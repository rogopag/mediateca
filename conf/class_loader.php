<?php
function __autoload($classname) {
	if(file_exists(MEDIATECA_PATH . 'classes/' . $classname . '.php')) {
   		include_once( MEDIATECA_PATH."classes/" . $classname . ".php"); 
	}
}
?>