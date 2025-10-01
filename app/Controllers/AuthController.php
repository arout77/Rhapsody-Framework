<?php

namespace App\Controllers;

use App\Entities\User; // Use the new User Entity
use Core\BaseController;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;
use Doctrine\ORM\EntityManager; // Import the EntityManager
use Twig\Environment;

class AuthController extends BaseController
{
    /**
     * @param EntityManager $em
     * @param Validator $validator
     * @param Environment $twig
     */
    public function __construct(
        protected EntityManager $em, // Inject the EntityManager
        protected Validator $validator,
        Environment $twig
    ) {
        parent::__construct( $twig );
    }

    /**
     * @return mixed
     */
    public function showLoginForm(): Response
    {
        return $this->view( 'auth/login.twig' );
    }

    /**
     * @param Request $request
     */
    public function login( Request $request ): Response
    {
        $data = $request->getBody();

        // Find the user by email using the EntityManager
        $user = $this->em->getRepository( User::class )->findOneBy( ['email' => $data['email']] );

        if ( $user && password_verify( $data['password'], $user->getPassword() ) ) {
            Session::regenerate();
            Session::set( 'user_id', $user->getUserId() );
            return redirect( '/dashboard' );
        }

        return redirect( '/login' )->with( 'error', 'Invalid credentials.' );
    }

    /**
     * @return mixed
     */
    public function showRegisterForm(): Response
    {
        return $this->view( 'auth/register.twig', ['old' => [], 'errors' => []] );
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function register( Request $request ): Response
    {
        $data  = $request->getBody();
        $rules = [
            'name'     => 'required|min:2',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ];

        if ( $this->validator->validate( $data, $rules ) ) {
            // Create a new User entity
            $user = new User();
            $user->setName( $data['name'] );
            $user->setEmail( $data['email'] );
            $user->setPassword( $data['password'] );

            // Tell Doctrine to save the user
            $this->em->persist( $user );
            $this->em->flush(); // This executes the INSERT query

            // Automatically log the new user in
            Session::regenerate();
            Session::set( 'user_id', $user->getUserId() );
            return redirect( '/dashboard' );
        }

        return $this->view( 'auth/register.twig', [
            'errors' => $this->validator->getErrors(),
            'old'    => $data,
        ] );
    }

    public function logout()
    {
        Session::destroy();
        return redirect( '/' );
    }
}
