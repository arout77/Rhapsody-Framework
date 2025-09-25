<?php

namespace App\Controllers;

use App\Models\User;
use Core\BaseController;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class AuthController extends BaseController
{
    /**
     * @return mixed
     */
    public function showLoginForm(): Response
    {
        return $this->view( 'auth/login.twig' );
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function login( Request $request ): Response
    {
        $data      = $request->getBody();
        $userModel = new User();
        $user      = $userModel->findByEmail( $data['email'] );

        if ( $user && password_verify( $data['password'], $user['password'] ) )
        {
            Session::regenerate();
            Session::set( 'user_id', $user['user_id'] );
            // Redirect to a protected page, like a dashboard
            header( 'Location: ' . getenv( 'APP_BASE_URL' ) . '/dashboard' );
            exit();
        }

        // Failed login
        return $this->view( 'auth/login.twig', ['error' => 'Invalid credentials.'] );
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
        var_dump( getenv( 'DB_PASS' ) );
        die();

        $data      = $request->getBody();
        $validator = new Validator();
        $rules     = [
            'name'     => 'required|min:2',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ];

        if ( $validator->validate( $data, $rules ) )
        {
            $userModel = new User();
            $userModel->create( $data );

            // Automatically log the new user in
            $newUser = $userModel->findByEmail( $data['email'] );
            Session::regenerate();
            Session::set( 'user_id', $newUser['user_id'] );
            header( 'Location: ' . getenv( 'APP_BASE_URL' ) . '/dashboard' );
            exit();
        }

        return $this->view( 'auth/register.twig', [
            'errors' => $validator->getErrors(),
            'old'    => $data,
        ] );
    }

    public function logout(): void
    {
        Session::destroy();
        header( 'Location: ' . getenv( 'APP_BASE_URL' ) . '/' );
        exit();
    }
}
