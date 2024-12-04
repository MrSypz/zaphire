<?php

namespace Api;

use Zernite\element\User;
use Zernite\util\ResponseHandler;

class UserApi extends BaseApi {
    private User $user;

    public function __construct() {
        parent::__construct();
        $this->user = new User();
    }

    public function login(): void {
        $this->validateRequiredFields(['username', 'password']);

        $username = $this->requestData['username'];
        $password = $this->requestData['password'];
        if ($this->user->isLoggedIn()) {
            ResponseHandler::sendSuccess('User is already logged in.');
        } else {
            if ($this->user->login($username, $password)) {
                ResponseHandler::sendSuccess('Login successful!');
            } else {
                ResponseHandler::sendError('Invalid username/email or password.');
            }
        }
    }

    public function logout(): void {
        if (!$this->user->isLoggedIn())
            ResponseHandler::sendError('User is not logged in.');
        else {
            $this->user->logout();
            ResponseHandler::sendSuccess('Logged out successfully!');
        }
    }

    public function createUser(): void {
        $this->validateRequiredFields(['username', 'password', 'email', 'confirm_password']);
        if ($this->requestData['password'] !== $this->requestData['confirm_password']) {
            ResponseHandler::sendError('Passwords do not match.');

            return;
        }

        $userData = [
                'Name' => $this->requestData['name'] == null ? strtolower($this->requestData['username']) : strtolower($this->requestData['name']),
                'Username' => $this->requestData['username'],
                'Password' => password_hash($this->requestData['password'], PASSWORD_DEFAULT),
                'Email' => $this->requestData['email'],
        ];

        if ($this->user->getUserByUsernameOrEmail($userData['Username'], $userData['Email'])) {
            ResponseHandler::sendError('A user with this username or email already exists.');

            return;
        }

        if ($this->user->createUser($userData)) {
            ResponseHandler::sendSuccess('User created successfully!');
        } else {
            ResponseHandler::sendError('Failed to create user. Please try again later.');
        }
    }

    public function updateUser(): void{
        $this->validateRequiredFields(['name']);
        $userData = [
            'Name' => $this->requestData['name'],
        ];
        if (!$this->user->isLoggedIn())
            ResponseHandler::sendError('User is not logged in.');
        else if ($this->user->updateUser($this->user->getUserID(),$userData)) {
            ResponseHandler::sendSuccess('User updated successfully!');
        } else ResponseHandler::sendError('Failed to update user.');
    }

    public function deleteUser(): void {
        $this->validateRequiredFields(['userId']);

        $userId = $this->requestData['userId'];

        if ($this->user->deleteUser($userId)) {
            ResponseHandler::sendSuccess('User deleted successfully!');
        } else {
            ResponseHandler::sendError('Failed to delete user.');
        }
    }

    public function isLogin(): void {
        if ($this->user->isLoggedIn())
            ResponseHandler::sendSuccess('User is logged in.', $this->user->isLoggedIn());
         else ResponseHandler::sendError('User is not logged in.');
    }

    /**
     * Get by database
     * @return void
     */
    public function getUserInfo(): void {
        if ($this->user->isLoggedIn())
            ResponseHandler::sendSuccess('User Info: ' ,$this->user->getCurrentUser());
        else ResponseHandler::sendError('User is not logged in.');
    }

    /**
     * Get by session
     * @return void
     */
    public function getRoleId(): void {
        if ($this->user->isLoggedIn())
            ResponseHandler::sendSuccess('User role id is:', $this->user->getRoleId());
        else ResponseHandler::sendError('User is not logged in.');
    }

    public function getUserID(): void {
        if ($this->user->isLoggedIn())
            ResponseHandler::sendSuccess('User id is: ', $this->user->getUserID());
        else ResponseHandler::sendError('User is not logged in.');
    }
    public function getUsername(): void {
        if ($this->user->isLoggedIn())
            ResponseHandler::sendSuccess('User username is: ', $this->user->getUsername());
        else ResponseHandler::sendError('User is not logged in.');
    }
}