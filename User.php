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
        $stmt->bind_param("sssss", $login, $hashedPassword, $email, $firstname, $lastname);

        if ($stmt->execute()) {
            // récupération de l'ID du nouvel utilisateur
             $newId = $this->connection->insert_id;

             // Récupération des informations complètes de l'utilisateur
            $selectQuery = "SELECT * FROM utilisateurs WHERE id = ?";
            $selectStmt = $this->connection->prepare($selectQuery);
            $selectStmt->bind_param("i", $newId);
            $selectStmt->execute();
            $result = $selectStmt->get_result();

            if ($user = $result->fetch_assoc()) {
                return $user;
            }
        }
        return false;
    }

    public function connect($login, $password) {
        $query = "SELECT * FROM utilisateurs WHERE login = ?";
        $stmt = $this->connection->prepare($query);
        $stmt ->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

         if ($user = $result->fetch_assoc()) {
            // Vérification du mot de passe
            if (password_verify($password, $user['password'])) {
                // Attribution des valeurs aux attributs de la classe
                $this->id = $user['id'];
                $this->login = $user['login'];
                $this->email = $user['email'];
                $this->firstname = $user['firstname'];
                $this->lastname = $user['lastname'];
                return true;
            }
        }

        return false;
    }

    public function disconnect() {
        // Réinitialisation des attributs
        $this->id = null;
        $this->login = "";
        $this->email = "";
        $this->firstname = "";
        $this->lastname = "";
    }

    public function delete() {
        if ($this->isConnected()) {
            $query = "DELETE FROM utilisateurs WHERE id = ?";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("i", $this->id);

            if ($stmt->execute()) {
                // déconnexion après suppression
                $this->disconnect();
                return true;
            }
        }

        return false;
    }

    public function update($login, $password, $email,$firstname, $lastname) {
        if ($this->isConnected()) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE utilisateurs SET login = ?, password = ?, email = ?, firstname = ?, lastname = ? WHERE id = ?";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("sssssi", $login, $hashedPassword, $email, $firstname, $lastname, $this->id);

            if ($stmt->execute()) {
                // mise à jour des attributs de l'objet
                $this->login = $login;
                $this->email =$email;
                $this->firstname =$firstname;
                $this->lastname = $lastname;
                return true;
            }
        }

        return false;     
    }

    public function isConnected() {
        return $this->id !== null;
    }

    public function getAllInfos() {
        if ($this->isConnected()) {
            return [
              'id' => $this->id,
                'login' => $this->login,
                'email' => $this->email,
                'firstname' => $this->firstname,
                'lastname' => $this->lastname
            ];    
        }

        return null;
    }

    public function getLogin() {
        return $this->login;
    }

    public function getEmail() {
        return $this->email;
    }

     public function getFirstname() {
        return $this->firstname;
    }
    
    public function getLastname() {
        return $this->lastname;
    }

    // destruction pour fermer la session
    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

// exemple de test des méthodes