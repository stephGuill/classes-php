<?php

// Script de test qui exécute une série de checks sur les deux classes User et Userpdo
// Affiche le résultat de chaque étape via des echo pour pouvoir exécuter ce fichier en CLI

echo "=== Test des classes User ===\n\n";

// Test de la classe User (MySQLi)
echo "--- Test de la classe User (MySQLi) ---\n";
require_once 'User.php';

// Instanciation de la classe User
$user = new User();

// Test d'enregistrement (register)
// register retourne soit un tableau (informations utilisateur) soit false
echo "1. Test d'enregistrement ...\n";
$newUser = $user->register("Tom13", "azerty", "thomas@gmail.com", "Thomas", "DUPONT");
if ($newUser) {
     echo "✓ Utilisateur créé avec succès !\n";
    echo "ID: " . $newUser['id'] . ", Login: " . $newUser['login'] . "\n\n";
} else {
    // Ici on peut avoir false pour différentes raisons : login existant, erreur SQL, etc.
    echo "✗ Erreur lors de la création\n\n";
}

// Test de connexion (connect)
// connect retourne true si les identifiants sont valides et remplit l'objet
echo "2. Test de connexion...\n";
$user2 = new User();
if ($user2->connect("Tom13", "azerty")) {
    echo "✓ Connexion réussie !\n";
    // getLogin/getEmail sont des getters simples qui renvoient les attributs
    echo "Login connecté: " . $user2->getLogin() . "\n";
    echo "Email: " . $user2->getEmail() . "\n";
    echo "Prénom: " . $user2->getFirstname() . "\n";
    echo "Nom: " . $user2->getLastname() . "\n\n";
} else {
    echo "✗ Erreur de connexion\n\n";
}

// Test isConnected (conditionnelle)
echo "3. Test isConnected...\n";
echo $user2->isConnected() ? "✓ Utilisateur connecté\n\n" : "✗ Utilisateur non connecté\n\n";

// Test getAllInfos (renvoie tableau associatif si connecté)
echo "4. Test getAllInfos...\n";
$infos = $user2->getAllInfos();
if ($infos) {
    echo "✓ Informations récupérées:\n";
    print_r($infos);
    echo "\n";
}

// Test update (met à jour l'utilisateur connecté)
echo "5. Test de mise à jour...\n";
if ($user2->update("Tom13_updated", "newpassword", "thomas.updated@gmail.com", "Thomas", "DUPONT-MARTIN")) {
    echo "✓ Mise à jour réussie !\n";
    echo "Nouveau login: " . $user2->getLogin() . "\n";
    echo "Nouvel email: " . $user2->getEmail() . "\n\n";
} else {
    echo "✗ Erreur lors de la mise à jour\n\n";
}

echo "=== Fin des tests User (MySQLi) ===\n\n";

//  Test de la classe Userpdo (PDO)
echo "--- Test de la classe Userpdo (PDO) ---\n";
require_once 'user-pdo.php';

// Instanciation de la classe PDO
$userPdo = new Userpdo();

//  Test d'enregistrement
echo "1. Test d'enregistrement ...\n";
$newUserPdo = $userPdo->register("Alice25", "motdepasse", "alice@example.com", "Alice", "MARTIN");
if ($newUserPdo) {
    echo "✓ Utilisateur créé avec succès !\n";
    echo "ID: " . $newUserPdo['id'] . ", Login: " . $newUserPdo['login'] . "\n\n";
} else {
    echo "✗ Erreur lors de la création\n\n";
}

//  Test de connexion
echo "2. Test de connexion...\n";
$userPdo2 = new Userpdo();
if ($userPdo2->connect("Alice25", "motdepasse")) {
    echo "✓ Connexion réussie !\n";
    echo "Login connecté: " . $userPdo2->getLogin() . "\n";
    echo "Email: " . $userPdo2->getEmail() . "\n";
    echo "Prénom: " . $userPdo2->getFirstname() . "\n";
    echo "Nom: " . $userPdo2->getLastname() . "\n\n";
} else {
    echo "✗ Erreur de connexion\n\n";
}

// Test isConnected
echo "3. Test isConnected ...\n";
echo $userPdo2->isConnected() ? "✓ Utilisateur connecté\n\n" : "✗ Utilisateur non connecté\n\n";

// Test getAllInfos
echo "4. Test getAllInfos...\n";
$infosPdo = $userPdo2->getAllInfos();
if ($infosPdo) {
    echo "✓ Informations récupérées:\n";
    print_r($infosPdo);
    echo "\n";
}

// Test update
echo "5. Test de mise à jour...\n";
if ($userPdo2->update("Alice25_updated", "newpassword", "alice.updated@example.com", "Alice", "MARTIN-BERNARD")) {
    echo "✓ Mise à jour réussie !\n";
    echo "Nouveau login: " . $userPdo2->getLogin() . "\n";
    echo "Nouvel email: " . $userPdo2->getEmail() . "\n\n";
} else {
    echo "✗ Erreur lors de la mise à jour\n\n";
}

echo "=== Fin des tests Userpdo (PDO) ===\n\n";

echo "=== Tests terminés ===\n";

?>