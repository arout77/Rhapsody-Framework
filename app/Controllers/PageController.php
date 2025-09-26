<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Models\User;
use Core\BaseController;
use Core\FileUploader;
use Core\Mailer;
use Core\Pagination;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;
use Twig\Environment;

class PageController extends BaseController
{
    /**
     * @param Environment $twig
     */
    public function __construct( Environment $twig )
    {
        parent::__construct( $twig );
    }

    /**
     * Show the home page.
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->view( 'home/index.twig' );
    }

    /**
     * @return mixed
     */
    public function dashboard(): Response
    {
        // The AuthMiddleware now handles protection for this route.
        // The controller's only job is to render the view.
        return $this->view( 'home/dashboard.twig' );
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
        $data      = $request->getBody();
        $validator = new Validator();
        $rules     = [
            'name'    => 'required|min:2|max:50',
            'email'   => 'required|email',
            'message' => 'required|min:10|max:1000',
        ];

        if ( $validator->validate( $data, $rules ) )
        {
            // Validation Passed
            $mailer   = new Mailer();
            $to       = $_ENV['MAIL_FROM_ADDRESS'] ?? 'admin@example.com';
            $subject  = "New Contact Form Submission from {$data['name']}";
            $htmlBody = "<p>Name: {$data['name']}</p><p>Email: {$data['email']}</p><p>Message: {$data['message']}</p>";

            try {
                $mailer->send( $to, $subject, $htmlBody );
                Session::flash( 'success', 'Your message has been sent successfully!' );
            }
            catch ( \Exception $e )
            {
                error_log( $e->getMessage() ); // Log the actual error
                Session::flash( 'error', 'Sorry, we could not send your message at this time.' );
            }

            // --- THIS IS THE CRITICAL FIX ---
            // 1. Force the session data to be written to disk.
            Session::close();

            // 2. NOW issue the redirect.
            header( 'Location: ' . ( $_ENV['APP_BASE_URL'] ?? '' ) . '/contact' );
            exit();
            // --- END FIX ---

        }
        else
        {
            // Validation Failed
            return $this->view( 'contact/contact.twig', [
                'errors' => $validator->getErrors(),
                'old'    => $data,
            ] );
        }
    }

    /**
     * @return mixed
     */
    public function showRegisterForm(): Response
    {
        return $this->view( 'register.twig', ['old' => [], 'errors' => []] );
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function register( Request $request ): Response
    {
        // ... (register method remains the same)
        $postData = $request->getBody();
        $fileData = $request->getFiles();
        $data     = array_merge( $postData, $fileData );

        $validator = new Validator();
        $rules     = [
            'username'   => 'required|alpha_num|min:3|max:20',
            'email'      => 'required|email',
            'password'   => 'required|min:8|confirmed',
            'website'    => 'url',
            'birth_date' => 'required|date_format:Y-m-d',
            'role'       => 'required|in:user,editor',
            'avatar'     => 'required|image|mimes:jpeg,png',
        ];

        if ( $validator->validate( $data, $rules ) )
        {
            $response = new Response();
            $response->setContent( "<h1>Registration Successful!</h1><p>Welcome, {$data['username']}!</p>" );
            return $response;
        }
        else
        {
            return $this->view( 'register.twig', [
                'errors' => $validator->getErrors(),
                'old'    => $postData,
            ] );
        }
    }

    /**
     * @return mixed
     */
    public function showUploadForm(): Response
    {
        return $this->view( 'upload.twig' );
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function handleUpload( Request $request ): Response
    {
        $uploader = new FileUploader();
        $uploader->setAllowedMimes( ['image/jpeg', 'image/png', 'application/pdf'] )
                 ->setMaxSize( 5 * 1024 * 1024 ); // 5 MB

        if ( $uploader->handle( 'documents' ) )
        {
            return $this->json( [
                'success' => true,
                'files'   => $uploader->getUploadedFiles(),
            ] );
        }
        else
        {
            return $this->json( [
                'success' => false,
                'errors'  => $uploader->getErrors(),
            ], 400 );
        }
    }
}
