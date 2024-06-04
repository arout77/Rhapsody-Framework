<?php
namespace App\Controller;
use Src\Controller\Base_Controller;

class Search_Controller extends Base_Controller
{
	public function index()
	{
		// Model was created and stored at: /app/models/SearchModel.php
		// View was created and stored at: /app/template/views/search/results.html.twig

		$geo = $this->load->middleware( 'geoip' );
		echo $geo->distance( '29.084545', '31.3463234', '30.563235', '31.3455463' );

		$format = $this->load->middleware( 'format' );
		$format->age( "Feb 4th 1977" );

		// $e = new PhoneNumberUtil;

		$validate = $this->load->middleware( 'validation' );
		$validate->form( $_POST, [
			'fullname' => [
				'User Name' => 'min_length,3|max_length,30|required|word',
			],
			'company'  => [
				'Company Name' => 'alphanum|min_length,3|max_length,30|required',
			],
			'email'    => [
				'Email Address' => 'email|required',
			],
			'phone'    => [
				'Telephone' => 'required',
			],
			'message'  => [
				'Message' => 'max_length,300',
			],
		] );
		$errors = $validate->errors();

		$this->template->render( "forms/test.html.twig", ['errors' => $errors] );
	}

	/**
	 * @return mixed
	 */
	public function results()
	{
		$validate = $this->load->middleware( 'validation' );

		$searchTerm = $_GET['navSearch'];

		$validate->form( $_GET, [
			'navSearch' => [
				'Searchbox' => 'required|max_length,30|min_length,3',
			],
		] );

		$errors = $validate->errors();

		$dataset  = $this->model( 'Search' );
		$sql      = $dataset->getSearchResults( $searchTerm );
		$url      = 'search/results/';
		$per_page = 10;
		$pag      = $this->load->middleware( 'pagination' );
		$pag->config( $sql, $url, $per_page );
		$results = $pag->runQuery();
		$links   = $pag->links();

		$this->template->render( "search/results.html.twig", [
			'results'       => $results,
			'links'         => $links,
			'total_records' => $pag->num_records,
			'errors'        => $errors,
		] );
	}
}