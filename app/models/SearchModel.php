<?php
namespace App\Model;
use Src\Model\System_Model;

class SearchModel extends System_Model
{
    public function searchDocsPages( $searchTerm )
    {
        $this->getAll( 'SELECT content FROM documentation WHERE content LIKE ?',
            [ '%'.$searchTerm.'%' ]
        );
    }

    public function getSearchResults( $searchTerm )
    {
        $content = [];

        $query = $this->getAll( "SELECT * FROM documentation WHERE MATCH(content) AGAINST('+$searchTerm' IN BOOLEAN MODE) ORDER BY MATCH(content) AGAINST('$searchTerm') DESC" );

        foreach( $query as $key => $results )
        {
            $results = str_replace("$searchTerm", "<span style='background-color: yellow;'>$searchTerm</span>", $results);
            $content[] = $results;
        }

        return $content;
    }
}