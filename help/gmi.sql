-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 18 oct. 2025 à 17:50
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gmi`
--

-- --------------------------------------------------------

--
-- Structure de la table `bons`
--

CREATE TABLE `bons` (
  `id` int(11) NOT NULL,
  `id_bon_intervention` int(11) NOT NULL,
  `date` date NOT NULL,
  `versement` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `bons_details`
--

CREATE TABLE `bons_details` (
  `id` int(11) NOT NULL,
  `id_bon` int(11) NOT NULL,
  `id_piece` int(11) NOT NULL,
  `prix_vente` decimal(12,2) NOT NULL,
  `quantite` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `bons_intervention`
--

CREATE TABLE `bons_intervention` (
  `id` int(11) NOT NULL,
  `num_bon` varchar(50) NOT NULL,
  `id_client` int(11) NOT NULL,
  `id_intervention` int(11) DEFAULT NULL,
  `date_bon` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `bons_intervention_details`
--

CREATE TABLE `bons_intervention_details` (
  `id` int(11) NOT NULL,
  `id_bon_intervention` int(11) NOT NULL,
  `id_piece` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 1,
  `prix_vente` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `rc` varchar(255) NOT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `nif` varchar(20) DEFAULT NULL,
  `nis` varchar(20) DEFAULT NULL,
  `ia` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `interventions`
--

CREATE TABLE `interventions` (
  `id` int(11) NOT NULL,
  `id_vehicule` int(11) NOT NULL,
  `date_intervention` datetime NOT NULL,
  `km` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pieces`
--

CREATE TABLE `pieces` (
  `id` int(11) NOT NULL,
  `ref` varchar(50) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `prix_achat_ht` decimal(10,2) DEFAULT NULL,
  `prix_vente_ht` decimal(10,2) NOT NULL,
  `quantite` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rank` enum('admin','user','manager') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `vehicules`
--

CREATE TABLE `vehicules` (
  `id` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `matricule` varchar(50) NOT NULL,
  `marque` varchar(50) DEFAULT NULL,
  `modele` varchar(50) DEFAULT NULL,
  `num_chassis` varchar(100) DEFAULT NULL,
  `km_initial` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `bons`
--
ALTER TABLE `bons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_bon_intervention` (`id_bon_intervention`);

--
-- Index pour la table `bons_details`
--
ALTER TABLE `bons_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_bon` (`id_bon`),
  ADD KEY `id_piece` (`id_piece`);

--
-- Index pour la table `bons_intervention`
--
ALTER TABLE `bons_intervention`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_client` (`id_client`),
  ADD KEY `fk_bon_intervention` (`id_intervention`);

--
-- Index pour la table `bons_intervention_details`
--
ALTER TABLE `bons_intervention_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_bon_intervention` (`id_bon_intervention`),
  ADD KEY `id_piece` (`id_piece`);

--
-- Index pour la table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `interventions`
--
ALTER TABLE `interventions`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `pieces`
--
ALTER TABLE `pieces`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ref` (`ref`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Index pour la table `vehicules`
--
ALTER TABLE `vehicules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_client` (`id_client`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `bons`
--
ALTER TABLE `bons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `bons_details`
--
ALTER TABLE `bons_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `bons_intervention`
--
ALTER TABLE `bons_intervention`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `bons_intervention_details`
--
ALTER TABLE `bons_intervention_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `interventions`
--
ALTER TABLE `interventions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `pieces`
--
ALTER TABLE `pieces`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `vehicules`
--
ALTER TABLE `vehicules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `bons`
--
ALTER TABLE `bons`
  ADD CONSTRAINT `bons_ibfk_1` FOREIGN KEY (`id_bon_intervention`) REFERENCES `bons_intervention` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `bons_details`
--
ALTER TABLE `bons_details`
  ADD CONSTRAINT `bons_details_ibfk_1` FOREIGN KEY (`id_bon`) REFERENCES `bons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bons_details_ibfk_2` FOREIGN KEY (`id_piece`) REFERENCES `pieces` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `bons_intervention`
--
ALTER TABLE `bons_intervention`
  ADD CONSTRAINT `bons_intervention_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bon_intervention` FOREIGN KEY (`id_intervention`) REFERENCES `interventions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `bons_intervention_details`
--
ALTER TABLE `bons_intervention_details`
  ADD CONSTRAINT `bons_intervention_details_ibfk_1` FOREIGN KEY (`id_bon_intervention`) REFERENCES `bons_intervention` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bons_intervention_details_ibfk_2` FOREIGN KEY (`id_piece`) REFERENCES `pieces` (`id`);

--
-- Contraintes pour la table `vehicules`
--
ALTER TABLE `vehicules`
  ADD CONSTRAINT `vehicules_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `clients` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
