<?php
require_once __DIR__ . '/../sqlconnect.php';

class User {
    private $idUser;
    private $username;
    private $email;
    private $password_hash;
    private $role;

    public function __construct($username, $email, $password_hash, $role = 'user') {
        $this->username = $username;
        $this->email = $email;
        $this->password_hash = $password_hash;
        $this->role = $role;
    }

    // Getters
    public function getUserId() {
        return $this->idUser;
    }

    public function getUserName() {
        return $this->username;
    }

    public function getUserEmail() {
        return $this->email;
    }

    public function getUserPasswordHash() {
        return $this->password_hash;
    }

    public function getUserRole() {
        return $this->role;
    }

    public function setUserId($idUser) {
        $this->idUser = $idUser;
    }

    // Setters
    public function setUserName($username) {
        $this->username = $username;
    }

    public function setUserEmail($email) {
        $this->email = $email;
    }

    public function setUserPasswordHash($password_hash) {
        $this->password_hash = $password_hash;
    }

    public function setUserRole($role) {
        $this->role = $role;
    }
}








