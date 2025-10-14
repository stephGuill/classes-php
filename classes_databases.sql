-- Script SQL pour créer la base de données et la table utilisateurs

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS `classes` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utilisation de la base de données
USE `classes`;

-- Création de la table utilisateurs
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour améliorer les performances de recherche
CREATE INDEX idx_login ON utilisateurs(login);
CREATE INDEX idx_email ON utilisateurs(email);