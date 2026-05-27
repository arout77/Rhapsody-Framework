<?php
namespace App\Controllers;

use Core\BaseController;
use Core\Database;
use Core\Request;
use Core\Response;

class TriviaController extends BaseController
{
    /**
     * GET /trivia
     * Renders the HTML template containing the React DOM root mount point
     */
    public function index(): Response
    {
        return $this->view('trivia/trivia.twig');
    }
}
