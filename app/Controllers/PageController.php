<?php

namespace App\Controllers;

use App\Models\User;
use Core\BaseController;
use Core\Pagination;
use Core\Request;
use Core\Response;

class PageController extends BaseController
{
    /**
     * Show the home page.
     *
     * @return Response
     */
    public function index(): Response
    {
        // Data to pass to the template
        $data = [
            'name' => 'Gemini',
        ];

        // Render the 'home.twig' view and pass the data to it
        return $this->view( 'home/index.twig', $data );
    }

    /**
     * Show the about page.
     *
     * @return Response
     */
    public function about(): Response
    {
        // 1. Create a new Response object
        $response = new Response();

        // 2. Use the correct setContent() method
        $response->setContent( "<h1>About Us</h1><p>This page is working!</p>" );

        // 3. Return the configured response
        return $response;
    }

    /**
     * Shows a dynamic page based on a URL parameter.
     *
     * @param string $slug The value captured from the URL (e.g., 'my-first-post').
     * @return Response
     */
    public function showPost( string $slug ): Response
    {
        $response = new Response();

        // Sanitize the output to prevent XSS attacks
        $safeSlug = htmlspecialchars( $slug, ENT_QUOTES, 'UTF-8' );

        $response->setContent( "<h1>Viewing Post</h1><p>The slug is: <strong>{$safeSlug}</strong></p>" );

        return $response;
    }

    /**
     * @return mixed
     */
    public function showUsers(): Response
    {
        $request = new Request;

        // 1. Create an instance of our User model
        $userModel = new \App\Models\User();

        // 1. Define the allowed options for items per page.
        $allowedLimits = [5, 10, 25, 50];
        $defaultLimit  = 10;

        // 2. Get the user's selection from the URL, default to 10.
        $usersPerPage = (int) $request->getQueryParam( 'limit', $defaultLimit );

        // 3. Ensure the selected limit is a valid one.
        if ( !in_array( $usersPerPage, $allowedLimits ) )
        {
            $usersPerPage = $defaultLimit;
        }

        // 1. Get the current page from the URL, default to 1
        $currentPage = (int) $request->getQueryParam( 'page', 1 );

        // 2. Get the total number of users
        $totalusers = $userModel->countAll();

        // 3. Create the Pagination object
        $pagination = new \Core\Pagination( $totalusers, $usersPerPage, $currentPage );

        // 4. Fetch only the users for the current page
        $users = $userModel->findPaginated( $pagination->getLimit(), $pagination->getOffset() );

        // 5. Pass both the users and the pagination object to the view
        return $this->view( 'home/users.twig', [
            'users'         => $users,
            'pagination'    => $pagination,
            'allowedLimits' => $allowedLimits,
        ] );
    }

    /**
     * @param $user_id
     * @return mixed
     */
    public function viewUser( Request $request, string $user_id ): Response
    {
        $userModel = new User();

        // Sanitize the incoming user_id
        $uid = preg_replace( "/[^a-zA-Z0-9]/", "", $user_id );

        $userData = $userModel->getUserById( $uid );

        // We need a view for this! Let's create a placeholder for now.
        // You'll need to create the file: views/users/user.twig
        return $this->view( 'users/user.twig', [
            'user' => $userData,
        ] );
    }

    /**
     * THIS METHOD DISPLAYS THE FORM
     * It corresponds to the Router::get('/contact', ...) route.
     * This is the method that was missing.
     */
    public function contact(): Response
    {
        // This just renders the Twig template with the form.
        return $this->view( 'contact/contact.twig' );
    }

    /**
     * THIS METHOD HANDLES THE SUBMISSION
     * It corresponds to the Router::post('/contact', ...) route.
     */
    public function handleContact( Request $request ): Response
    {
        // Get the sanitized data from the request body
        $data = $request->getBody();

        // For now, just display the data back to the user to confirm it worked.
        $name = htmlspecialchars( $data['name'] ?? 'Guest', ENT_QUOTES, 'UTF-8' );

        $response = new Response();
        $response->setContent( "<h1>Thank You, {$name}!</h1><p>We received your message.</p>" );
        return $response;
    }
}
