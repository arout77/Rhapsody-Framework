<?php
namespace App\Controller;
use Src\Controller\Base_Controller;

class Search_Controller extends Base_Controller
{
    public function index()
    {
        // Model was created and stored at: /app/models/SearchModel.php
        // View was created and stored at: /app/template/views/search/results.html.twig
        $this->template->render("search\index.html.twig");
    }

    public function results()
    {
        $searchTerm = $_POST['navSearch'];

        $db = $this->model('Search');
        $results = $db->getSearchResults( $searchTerm );

        $this->template->render("search/results.html.twig", [
            'results' => $results,
            'searchTerm' => $searchTerm
        ]);
    }
}