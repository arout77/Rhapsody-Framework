<?php

namespace App\Models;

use Core\BaseModel;
use \PDO as PDO;

class User extends BaseModel
{
    /**
     * Counts the total number of users.
     * @return int
     */
    public function countAll(): int
    {
        $stmt = $this->db->query( "SELECT COUNT(user_id) FROM users" );
        return (int) $stmt->fetchColumn();
    }

    /**
     * Fetches a paginated list of users.
     *
     * @param int $limit The number of users to fetch.
     * @param int $offset The number of users to skip.
     * @return array
     */
    public function findPaginated( int $limit, int $offset ): array {
        $sql = "SELECT user_id, name, email FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare( $sql );

        // Bind parameters as integers
        $stmt->bindParam( ':limit', $limit, PDO::PARAM_INT );
        $stmt->bindParam( ':offset', $offset, PDO::PARAM_INT );

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Fetches all users from the database.
     *
     * @return array
     */
    public function findAll(): array {
        // Prepare and execute a simple query
        $stmt = $this->db->query( "SELECT user_id, name, email FROM users ORDER BY name DESC" );
        return $stmt->fetchAll();
    }

    /**
     * @param $uid
     * @return mixed
     */
    public function getUserById( string $uid ): array {
        // Prepare and execute a simple query
        $stmt = $this->db->prepare( "SELECT * FROM users WHERE user_id = :uid" );
        $stmt->bindParam( ':uid', $uid, PDO::PARAM_STR );
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param string $email
     * @return mixed
     */
    public function findByEmail( string $email ): array | false
    {
        $stmt = $this->db->prepare( "SELECT * FROM users WHERE email = :email" );
        $stmt->bindParam( ':email', $email, PDO::PARAM_STR );
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create( array $data ): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (user_id, name, email, password) VALUES (:user_id, :name, :email, :password)"
        );
        return $stmt->execute( [
            ':user_id'  => bin2hex( random_bytes( 16 ) ), // Generate a random ID
            ':name' => $data['name'],
            ':email'    => $data['email'],
            ':password' => password_hash( $data['password'], PASSWORD_BCRYPT ), // Hash the password
        ] );
    }
}
