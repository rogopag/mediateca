<?php
class Mediateca_Utils 
{
	
	public function __construct() 
	{
	
	}
	public static function printTermsAsCommaSeparatedList()
	{
		$terms = get_terms(array('categoria', 'terzo-livello'), 'orderby=count&hide_empty=0');
		$list = '';
		
		foreach($terms as $term)
		{
			echo $term->name . ', ';
		}
	}
}
?>
