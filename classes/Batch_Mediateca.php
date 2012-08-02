<?php
class Batch_Mediateca
{
	private $db, $dbUser, $dbPassword, $dbHost, $batch_db, $wp;
	
	public function __construct()
	{
		global $wpdb; 
		$this->db = 'mediateca';
		$this->dbUser = 'root';
		$this->dbPassword = 'hotrats';
		$this->dbHost = '127.0.0.1';
		$this->batch_db = new wpdb($this->dbUser, $this->dbPassword, $this->db, $this->dbHost);
		$this->wp = $wpdb;
		add_action('admin_menu', array(&$this, 'initAdmin'), 10);
	}
	public function initAdmin()
	{
		add_menu_page( 'Batch Mediateca', 'Batch Mediateca', 'manage_options', 'batch-mediateca', array( &$this, 'printAdminScreen' ) );
	}
	public function printAdminScreen()
	{
		$form = '<form id="Batch_Mediateca_submit"  action="'. $_SERVER['REQUEST_URI'] .'" method="post" accept-charset="utf-8" name="Batch_Mediateca_submit">
		<p><input type="submit" value="Batch &rarr;" name="submit" /></form></p>';
		echo $form;
		
		$this->doSomething();
	}
	private function doSomething()
	{
		if( $_POST['submit'])
		{
			
		}
	}
	private function createTerms()
	{
		
	}
}
?>