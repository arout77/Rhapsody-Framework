<?php

namespace Core;

use PDO;

/**
 * The base model which all other models will extend.
 */
abstract class BaseModel
{
    protected PDO $db;

    public function __construct()
    {
        // Get the singleton PDO instance
        $this->db = Database::getInstance();
    }
}
