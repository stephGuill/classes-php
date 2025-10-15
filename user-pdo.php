<?php

class Userpdo {
    private $id;
    public $login;
    public $email;
    public $firstname;
    public $lastname;

    private $pdo;

    public function __construct() {
        try {
            // connexion à la base de données avec pdo
            $this->pdo = new PDO("mysql:host=localhost;dbname=classes", "root", "");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // initialisation des attributs
            $this->id = null;
            $this->login = "";
            $this->email = "";
            $this->firstname = "";
            $this->lastname = "";
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());    
        }
    }

    public function register($login, $password, $email, $firstname, $lastname) {
        try {
            //  hachage du mot de passe pour la sécurité
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            //  préparationde la requête d'insertion
            $query = "INSERT INTO utilisateurs (login, password, email, firstname, lastname) VALUES (:login, :password, :email, :firstname, :lastname";
            $stmt = $this->pdo->prepare($query);
            
        }
    }
}