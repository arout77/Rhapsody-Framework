<?php
namespace App\Model;
use Src\Model\System_Model;

class SearchModel extends System_Model
{
	/**
	 * @param $searchTerm
	 * @return mixed
	 */
	public function getSearchResults( $searchTerm )
	{
		$content = [];

		$query = $this->getAll( "SELECT * FROM documentation WHERE MATCH(content) AGAINST('$searchTerm' IN BOOLEAN MODE) ORDER BY MATCH(content) AGAINST('$searchTerm') DESC" );

		foreach ( $query as $key => $results )
		{
			$results   = str_replace( "$searchTerm", "<span style='background-color: yellow; color: #242424;'>$searchTerm</span>", $results );
			$content[] = $results;
		}

		return $content;
	}

	/**
	 * @param $searchTerm
	 */
	public function searchDocsPages( $searchTerm )
	{
		$this->getAll( 'SELECT content FROM documentation WHERE content LIKE ?',
			['%' . $searchTerm . '%']
		);
	}

	/**
	 * @return mixed
	 */
	public function testing()
	{
		$content = [];

		$query = $this->getAll( "SELECT * FROM documentation" );

		foreach ( $query as $key => $results )
		{
			$content[] = $results;
		}

		return $content;
	}
}