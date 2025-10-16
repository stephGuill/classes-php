<?php

/**
 * Classe Userpdo
 * Variante de la classe User utilisant PDO au lieu de mysqli.
 * PDO offre une API orientée objet, la possibilité de lier des paramètres
 * nommés et une gestion centralisée des erreurs via les exceptions.
 */
class Userpdo {
    private $id;
    public $login;
    public $email;
    public $firstname;
    public $lastname;

    // Instance PDO utilisée pour les requêtes
    private $pdo;

    public function __construct() {
        try {
            // Création de l'objet PDO (DSN, utilisateur, mot de passe)
            $this->pdo = new PDO("mysql:host=localhost;dbname=classes", "root", "");
            // Lever les exceptions en cas d'erreur pour pouvoir les attraper
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Initialisation des attributs
            $this->id = null;
            $this->login = "";
            $this->email = "";
            $this->firstname = "";
            $this->lastname = "";
        } catch (PDOException $e) {
            // En cas d'échec de connexion, arrêter et afficher l'erreur
            die("Connection failed: " . $e->getMessage());    
        }
    }

    public function register($login, $password, $email, $firstname, $lastname) {
        try {
            //  hachage du mot de passe pour la sécurité
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            // Préparation de la requête d'insertion (requête préparée avec paramètres nommés)
            // Les placeholders nommés (:login, :password, ...) améliorent la lisibilité
            $query = "INSERT INTO utilisateurs (login, password, email, firstname, lastname) VALUES (:login, :password, :email, :firstname, :lastname)";
            $stmt = $this->pdo->prepare($query);

            // Exécution avec un tableau associatif liant les paramètres nommés
            $result = $stmt->execute([
                ':login' => $login,
                ':password' => $hashedPassword,
                ':email' => $email,
                ':firstname' => $firstname,
                ':lastname' => $lastname
            ]);

            if ($result) {
                // Récupérer l'ID inséré (lastInsertId) et renvoyer les infos complètes
                $newId = $this->pdo->lastInsertId();

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
                // En cas d'erreur SQL, afficher un message utile (en dev)
                echo "Erreur lors de la mise à jour : " . $e->getMessage();
            }

        }

        return false;
    }

public function isConnected() {
    // Retourne true si l'objet représente un utilisateur connecté
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
        // Accesseurs simples pour exposer les attributs
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

// Test d'enregistrement (décommenter pour exécuter manuellement)
// $newUser = $user->register("Alice25", "motdepasse", "alice@example.com", "Alice", "MARTIN");
// if ($newUser) {
//     echo "Utilisateur créé avec succès !\n";
//     print_r($newUser);
// }

// Test de connexion
// $user2 = new Userpdo();
// if ($user2->connect("Alice25", "motdepasse")) {
//     echo "Connexion réussie !\n";
//     echo "Informations utilisateur : \n";
//     print_r($user2->getAllInfos());
// }
*/
?>