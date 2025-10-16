<?php

/**
 * Classe User
 * Représente un utilisateur et fournit des méthodes pour s'inscrire,
 * se connecter, mettre à jour, supprimer et obtenir des informations.
 * Utilise l'extension mysqli pour communiquer avec la base de données MySQL.
 */
class User {
    private $id;
    public $login;
    public $email;
    public $firstname;
    public $lastname;

    private $connection;

    public function __construct() {
        // connexion à la base de données MySQL via mysqli
        // (hôte, utilisateur, mot de passe, base)
        $this->connection = new mysqli("localhost", "root", "", "classes");

        // Si la connexion échoue, on stoppe l'exécution et affiche l'erreur
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }

        // Initialisation des attributs de l'objet
        // Avant connexion ils sont vides / null
        $this->id = null;
        $this->login = "";
        $this->email = "";
        $this->firstname = "";
        $this->lastname = "";
    }

    public function register($login, $password, $email, $firstname, $lastname) {
        // Hachage du mot de passe avec password_hash (algorithme bcrypt par défaut)
        // pour ne jamais stocker le mot de passe en clair.
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Vérifier si le login ou l'email existe déjà pour éviter l'erreur de clé dupliquée.
        // On utilise une requête préparée pour éviter les injections SQL.
        $checkQuery = "SELECT id FROM utilisateurs WHERE login = ? OR email = ? LIMIT 1";
        $checkStmt = $this->connection->prepare($checkQuery);
        if ($checkStmt) {
            // bind_param: 'ss' signifie deux paramètres de type string
            $checkStmt->bind_param("ss", $login, $email);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            // Si la requête retourne une ligne, le login ou l'email existe déjà
            if ($checkResult && $checkResult->fetch_assoc()) {
                return false; // signaler l'échec (login/email déjà utilisé)
            }
        }

        // Préparation de la requête d'insertion (requête préparée pour la sécurité)
        $query = "INSERT INTO utilisateurs (login, password, email, firstname, lastname) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->connection->prepare($query);
        if (!$stmt) {
            // Échec de préparation de la requête (problème SQL)
            return false;
        }
        // On attache les paramètres (5 strings)
        $stmt->bind_param("sssss", $login, $hashedPassword, $email, $firstname, $lastname);

        // Exécution de l'INSERT. Si succès, on récupère l'ID inséré puis on retourne
        // les informations complètes de l'utilisateur nouvellement créé.
        if ($stmt->execute()) {
            $newId = $this->connection->insert_id;

            // Récupération des informations pour renvoyer un tableau associatif
            $selectQuery = "SELECT * FROM utilisateurs WHERE id = ?";
            $selectStmt = $this->connection->prepare($selectQuery);
            $selectStmt->bind_param("i", $newId);
            $selectStmt->execute();
            $result = $selectStmt->get_result();

            if ($user = $result->fetch_assoc()) {
                // Retourner les informations utilisateur (id, login, email, ...)
                return $user;
            }
        }

        // Retour par défaut en cas d'échec
        return false;
    }

    public function connect($login, $password) {
        // Récupérer l'utilisateur par login
        $query = "SELECT * FROM utilisateurs WHERE login = ?";
        $stmt = $this->connection->prepare($query);
        $stmt ->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        // Si un utilisateur est trouvé, vérifier le mot de passe haché
         if ($user = $result->fetch_assoc()) {
            // password_verify compare le mot de passe fourni avec le hash stocké
            if (password_verify($password, $user['password'])) {
                // Si la vérification réussit, on remplit les attributs de l'objet
                // pour marquer l'utilisateur comme connecté
                $this->id = $user['id'];
                $this->login = $user['login'];
                $this->email = $user['email'];
                $this->firstname = $user['firstname'];
                $this->lastname = $user['lastname'];
                return true; // connexion réussie
            }
        }

        // connexion échouée
        return false;
    }

    public function disconnect() {
        // Réinitialisation des attributs pour marquer l'utilisateur comme déconnecté
        $this->id = null;
        $this->login = "";
        $this->email = "";
        $this->firstname = "";
        $this->lastname = "";
    }

    public function delete() {
        // Supprimer l'utilisateur connecté
        if ($this->isConnected()) {
            $query = "DELETE FROM utilisateurs WHERE id = ?";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("i", $this->id);

            if ($stmt->execute()) {
                // Après suppression, on déconnecte l'objet
                $this->disconnect();
                return true;
            }
        }

        return false;
    }

    public function update($login, $password, $email,$firstname, $lastname) {
        // Mettre à jour les informations de l'utilisateur connecté
        if ($this->isConnected()) {
            // Hachage du nouveau mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Vérifier si un autre utilisateur utilise déjà ce login ou cet email
            // Exclure l'utilisateur courant (id != current id)
            $checkQuery = "SELECT id FROM utilisateurs WHERE (login = ? OR email = ?) AND id != ? LIMIT 1";
            $checkStmt = $this->connection->prepare($checkQuery);
            if ($checkStmt) {
                $checkStmt->bind_param("ssi", $login, $email, $this->id);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                if ($checkResult && $checkResult->fetch_assoc()) {
                    // login ou email déjà utilisé par un autre utilisateur
                    return false; // éviter la violation de contrainte UNIQUE
                }
            }

            // Préparer et exécuter la requête UPDATE
            $query = "UPDATE utilisateurs SET login = ?, password = ?, email = ?, firstname = ?, lastname = ? WHERE id = ?";
            $stmt = $this->connection->prepare($query);
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param("sssssi", $login, $hashedPassword, $email, $firstname, $lastname, $this->id);

            if ($stmt->execute()) {
                // Mise à jour des attributs locaux de l'objet pour refléter la base
                $this->login = $login;
                $this->email = $email;
                $this->firstname = $firstname;
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

// Exemple d'utilisation / tests rapides
$user = new User();

// Test d'enregistrement: register() retourne soit un tableau associatif
// (informations de l'utilisateur créé) soit false en cas d'échec.
$newUser = $user->register("Tom13", "azerty", "thomas@gmail.com", "Thomas", "DUPONT");
if ($newUser) {
    echo "Utilisateur crée avec succès !\n";
    print_r($newUser);
}

// Test de connexion: on crée un nouvel objet User pour simuler une nouvelle session
$user2 = new User();
if ($user2->connect("Tom13", "azerty")) {
    echo "Connexion réussie !\n";
    echo "Informations utilisateur : \n";
    print_r($user2->getAllInfos());
}

?>