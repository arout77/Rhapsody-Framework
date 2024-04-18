<?php
namespace App\Model;

use Src\Model\System_Model;

class DocumentationModel extends System_Model
{
	public function addDocPage( $category, $subcategory, $version = '1.0.0' )
	{
		// Create new page
		$db                 = $this->dispense( 'documentation' );
		$db->category       = $category;
		$db->subcategory    = $subcategory;
		$db->content        = '';
		$db->version        = $version;
		$db->last_edit_date = date( "Y-m-d" );
		$id                 = $this->store( $db );
	}

	public function getDocPage( $category, $subcategory, $version = '1.0.0' )
	{
		// Check if this page exists
		return $this->find( 'documentation', 'category = ? AND subcategory = ? AND version = ?', [$category, $subcategory, $version] );
	}

	public function updateDocPage( $category, $subcategory, $version = '1.0.0' )
	{
		// Create new page
		$db                 = $this->load( 'documentation' );
		$db->category       = $category;
		$db->subcategory    = $subcategory;
		$db->content        = '';
		$db->version        = $version;
		$db->last_edit_date = date( "Y-m-d" );
		$id                 = $this->store( $db );
	}
}