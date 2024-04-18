<?php
namespace App\Controller {
	use Src\Controller\Base_Controller;

	class Documentation_Controller extends Base_Controller
	{
		public function architecture()
		{
			$page    = $this->route->parameter[1];
			$version = $this->config->setting( 'software_version' );

			$db = $this->model( "Documentation" );

			// Save this page to DB if it isn't already
			if ( empty( $db->getDocPage( "Architecture", ucwords( $page ), $version, $version ) ) )
			{
				$db->addDocPage( "Architecture", ucwords( $page ), $version );
			}
			else
			{
				// Otherwise update it
				$db->updateDocPage( "Architecture", ucwords( $page ), $version );
			}

			$this->template->render(
				'docs/architecture/' . $page . '.html.twig'
			);
		}

		public function components()
		{
			$page    = $this->route->parameter[1];
			$version = $this->config->setting( 'software_version' );

			$db = $this->model( "Documentation" );

			// Save this page to DB if it isn't already
			if ( empty( $db->getDocPage( "Components", ucwords( $page ), $version ) ) )
			{
				$db->addDocPage( "Components", ucwords( $page ), $version );
			}
			else
			{
				// Otherwise update it
				$db->updateDocPage( "Components", ucwords( $page ), $version );
			}

			$this->template->render(
				'docs/components/' . $page . '.html.twig'
			);
		}

		public function getting_started()
		{
			$page    = $this->route->parameter[1];
			$version = $this->config->setting( 'software_version' );

			$db = $this->model( "Documentation" );

			// Save this page to DB if it isn't already
			if ( empty( $db->getDocPage( "Getting Started", ucwords( $page ), $version ) ) )
			{
				$db->addDocPage( "Getting Started", ucwords( $page ), $version );
			}
			else
			{
				// Otherwise update it
				$db->updateDocPage( "Getting Started", ucwords( $page ), $version );
			}

			$this->template->render(
				'docs/getting-started/' . $page . '.html.twig'
			);
		}

		public function index()
		{
			$this->template->render( 'docs/index.html.twig', [
				'message' => 'Page Not Found',
				'site_name' => 'Rhapsody Framework',
			] );
		}

		public function middleware()
		{
			$page    = $this->route->parameter[1];
			$version = $this->config->setting( 'software_version' );

			$db = $this->model( "Documentation" );

			// Save this page to DB if it isn't already
			if ( empty( $db->getDocPage( "Middleware", ucwords( $page ), $version ) ) )
			{
				$db->addDocPage( "Middleware", ucwords( $page ), $version );
			}
			else
			{
				// Otherwise update it
				$db->updateDocPage( "Middleware", ucwords( $page ), $version );
			}

			$this->template->render(
				'docs/middleware/' . $page . '.html.twig'
			);
		}
	}
}
