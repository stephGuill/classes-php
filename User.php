<?php

class User {
    private $id;
    public $login;
    public $email;
    public $firstname;
    public $lastname;

    private $connection;

    public function __construct() {
        // connexion à la base de données mySQL
        $this->connection = new mysqli("localhost", "root", "", "classes");

        // vérifie la connexion
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }

        // initialisation des attributs
        $this->id = null;
        $this->login = "";
        $this->email = "";
        $this->firstname = "";
        $this->lastname = "";
    }

    public function register($login, $password, $email, $firstname, $lastname) {
        // hachage du mot de passe pour la sécurité
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // préparation de la requête d'insertion
        $query = "INSERT INTO utilisateurs (login, password, email, firstname, lastname) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->connection->prepare($query);
        

    }
}