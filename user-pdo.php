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

            //  préparation de la requête d'insertion
            $query = "INSERT INTO utilisateurs (login, password, email, firstname, lastname) VALUES (:login, :password, :email, :firstname, :lastname)";
            $stmt = $this->pdo->prepare($query);
            
            // execution avec les paramètres
            $result = $stmt->execute([
                ':login' => $login,
                ':password' => $hashedPassword,
                ':email' => $email,
                ':firstname' => $firstname,
                ':lastname' => $lastname
            ]);

            if ($result) {
                // récupération de l'ID du nouvel utilisateur
                $newId = $this->pdo->lastInsertId();

                // récupération des informations complètes de l'utilisateur
                $selectQuery = "SELECT * FROM utilisateurs WHERE id = :id";
                $selectStmt = $this->pdo->prepare($selectQuery);
                $selectStmt->execute([':id' => $newId]);

                return $selectStmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            echo "Erreur lors de l'enregistrement : " . $e->getMessage();
        }

        return false;
    }

    public function connect($login, $password) {
        try {
            $query = "SELECT * FROM utilisateurs WHERE login = :login";
             $stmt = $this->pdo->prepare($query);
            $stmt->execute([':login' => $login]);
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Attribution des valeurs aux attributs de la classe
                $this->id = $user['id'];
                $this->login = $user['login'];
                $this->email = $user['email'];
                $this->firstname = $user['firstname'];
                $this->lastname = $user['lastname'];
                return true;
        }
        } catch (PDOException $e) {
            echo "Erreur lors de la connexion : " . $e->getMessage();
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
            try {
                $query = "DELETE FROM utilisateurs WHERE id = :id";
                $stmt = $this->pdo->prepare($query);
                $result = $stmt->execute([':id' => $this->id]);

                if ($result) {
                    // Déconnexion après suppression
                    $this->disconnect();
                    return true;
                }
            } catch (PDOException $e) {
                echo "Erreur lors de la suppression : " . $e->getMessage();
            }
        }

        return false;
    }

     public function update($login, $password, $email, $firstname, $lastname) {
        if ($this->isConnected()) {
            try {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                $query = "UPDATE utilisateurs SET login = :login, password = :password, email = :email, firstname = :firstname, lastname = :lastname WHERE id = :id";
                $stmt = $this->pdo->prepare($query);
                
                $result = $stmt->execute([
                    ':login' => $login,
                    ':password' => $hashedPassword,
                    ':email' => $email,
                    ':firstname' => $firstname,
                    ':lastname' => $lastname,
                    ':id' => $this->id
                ]);

                if ($result) {
                    // Mise à jour des attributs de l'objet
                    $this->login = $login;
                    $this->email = $email;
                    $this->firstname = $firstname;
                    $this->lastname = $lastname;
                    return true;
                }
} catch (PDOException $e) {
    echo "Erreur lors de la mise à jour : " . $e->getMessage();
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
}

// Exemple de test des méthodes
/*
$user = new Userpdo();

// Test d'enregistrement
$newUser = $user->register("Alice25", "motdepasse", "alice@example.com", "Alice", "MARTIN");
if ($newUser) {
    echo "Utilisateur créé avec succès !\n";
    print_r($newUser);
}

// Test de connexion
$user2 = new Userpdo();
if ($user2->connect("Alice25", "motdepasse")) {
    echo "Connexion réussie !\n";
    echo "Informations utilisateur : \n";
    print_r($user2->getAllInfos());
}
*/
?>