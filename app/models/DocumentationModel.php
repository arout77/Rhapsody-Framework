<?php 
namespace App\Model;

use Src\Model\System_Model;

class DocumentationModel extends System_Model
{
    public function addDocPage($category, $subcategory)
    {
        // Create new page
        $db = $this->dispense( 'documentation' );
        $db->category = $category;
        $db->subcategory = $subcategory;
        $db->content = '';
        $id = $this->store( $db );
    }

    public function getDocPage($category, $subcategory)
    {
        // Check if this page exists
        return $this->find( 'documentation', 'category = ? AND subcategory = ?', [$category, $subcategory] );
    }
}