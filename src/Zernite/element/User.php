<?php

namespace Zernite\element;

use Zernite\util\Identifier;
use Zernite\util\ImplDatabase;
use Zernite\util\ImplSession;

require_once __DIR__ . '/../../../vendor/autoload.php'; // Include Composer autoload file

class User {
    protected ImplSession $session;
    protected ImplDatabase $db;
    protected string $table_name = 'users';

    public function __construct() {
        $this->db = Identifier::initDatabase();
        $this->session = Identifier::initSession();
    }

    /**
     * <b>Database</b><br>
     * Create a new user
     * Handles both permanent and temporary users
     *
     * @param array $data
     * @return bool
     */
    public function createUser(array $data): bool {
        return $this->db->insert($this->table_name, $data);
    }

    /**
     * Check if a user exists by username or email
     *
     * @param string $username
     * @param string $email
     * @return bool
     */
    public function getUserByUsernameOrEmail(string $username, string $email): bool {
        $sql = "SELECT COUNT(*) AS count FROM $this->table_name WHERE Username = :username OR Email = :email";
        $result = $this->db->fetchOne($sql, ['username' => $username, 'email' => $email]);
        return (int) $result['count'] > 0;
    }

    /**
     * Get user by ID
     *
     * @param int $userId
     * @return array
     */
    public function getUserById(int $userId): array {
        $sql = "SELECT UserID,Name,Username,Email,ProfilePicture FROM $this->table_name WHERE UserID = :id";
        return $this->db->fetchOne($sql, ['id' => $userId]);
    }

    /**
     * Update user information
     *
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateUser(int $userId, array $data): bool {
        $condition = 'UserID = :id';
        return $this->db->update($this->table_name, $data, $condition, ['id' => $userId]);
    }

    /**
     * Delete a user by ID
     *
     * @param int $userId
     * @return bool
     */
    public function deleteUser(int $userId): bool {
        $condition = 'UserID = :id';
        return $this->db->delete($this->table_name, $condition, ['id' => $userId]);
    }

    /**
     * Retrieve all users
     *
     * @return array
     */
    public function getAllUsers(): array {
        $sql = "SELECT * FROM $this->table_name";
        return $this->db->fetchAll($sql);
    }
    /**
     * <b>Network</b><br>
     * Log in a user by username/email and password.
     *
     * @param string $username Username or email
     * @param string $password
     *
     * @return bool
     */
    public function login(string $username, string $password): bool {
        $sql = "SELECT * FROM $this->table_name WHERE Username = :username";
        $user = $this->db->fetchOne($sql, ['username' => $username]);

        if ($user && password_verify($password, $user['Password'])) {
            $this->session->setSession('UserID', $user['UserID']);
            $this->session->setSession('Username', $user['Username']);
            $this->session->setSession('RoleID', $user['RoleID']);
            return true;
        }
        return false;
    }

    /**
     * Log out the current user.
     *
     * @return void
     */
    public function logout(): void {
        $this->session->destroySession();
    }

    /**
     * <b>Session</b><br>
     * Check if a user is logged in.
     *
     * @return bool
     */
    public function isLoggedIn(): bool {
        return $this->session->getSession('UserID') !== null;
    }

    /**
     * Get the currently logged-in user's data.
     *
     * @return array|null
     */
    public function getCurrentUser(): ?array {
        $userId = $this->session->getSession('UserID');
        return $userId ? $this->getUserById($userId) : null;
    }
    public function getRoleID() : int {
        return $this->session->getSession('RoleID');
    }
    public function getUserID(): int {
        return $this->session->getSession('UserID');
    }
    public function getUsername(): string {
        return $this->session->getSession('Username');
    }
}
