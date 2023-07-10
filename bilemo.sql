-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 10 juil. 2023 à 16:37
-- Version du serveur : 5.7.36
-- Version de PHP : 7.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `bilemo`
--

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

DROP TABLE IF EXISTS `client`;
CREATE TABLE IF NOT EXISTS `client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C7440455E7927C74` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`id`, `email`, `roles`, `password`, `name`) VALUES
(25, 'Orage@gmail.com', '[\"ROLE_ADMIN\"]', '$2y$13$WDgrR1Ca.OZ9gWNRaSjbVuxB1kMHYzmUMQFNsln4frn87u2ShvnAW', 'Orage'),
(26, 'adminBilemo@admin.fr', '[\"ROLE_ADMIN\"]', '$2y$13$Sr.DQPod606dyZH5WbtVHu6DU4swW2TBxrzKzfLNS4PMmv54TWPJG', 'Admin'),
(27, 'tony@gmail.com', '[\"ROLE_ADMIN\"]', '$2y$13$Vm9dZM9ENTversMHqCPQvuaX3KtKsD/9RKv.p0/J0oT7Z8b35H2IS', 'tony'),
(28, 'ffr@gmail.com', '[\"ROLE_USER\"]', '$2y$13$d.ti1iMv8G4YVamy0MFZ7OIOHdU.bMn28w8BBZMSKr/BeJChg710W', 'FFR'),
(29, 'string@ff.fr', '[\"ROLE_USER\"]', '$2y$13$g1DRfZ.Yrw33PSnFdpuSPumtdFcoNev0eyQjFFzebFciSM6YDugae', 'string'),
(30, 'strinfg@ff.fr', '[\"ROLE_USER\"]', '$2y$13$uNzGq9C38XO1sKu3D5WX8uxLiD5la/4OO1V0EHVOiyYv5oz8wkwPy', 'string'),
(31, 'strdinfg@ff.fr', '[\"ROLE_USER\"]', '$2y$13$4OB5erDidXb./RT8nouRn.x5AfLgKvsdF7EOUw7h5Ij/TL/N0Dmki', 'string'),
(32, 'sg@gma.com', '[\"ROLE_USER\"]', '$2y$13$ClN0IMV1hmjYo5eHiffFkeniQnlr9eeL5Y3GQgLBMeGePWhjIxJdC', 'strinddg'),
(33, 'sg@gmfa.com', '[\"ROLE_USER\"]', '$2y$13$j.nrINq655cxdB4FFfNjX.p9CjfL./IxNkSkyIo2Y6xJ6tbcN9aY2', 'strinfgddg');

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20230605154043', '2023-06-05 17:42:02', 83);

-- --------------------------------------------------------

--
-- Structure de la table `product`
--

DROP TABLE IF EXISTS `product`;
CREATE TABLE IF NOT EXISTS `product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `product`
--

INSERT INTO `product` (`id`, `brand`, `name`, `description`) VALUES
(121, 'Marque0', 'Téléphone0', 'descritpion mobile0'),
(122, 'Marque1', 'Téléphone1', 'descritpion mobile1'),
(123, 'Marque2', 'Téléphone2', 'descritpion mobile2'),
(124, 'Marque3', 'Téléphone3', 'descritpion mobile3'),
(125, 'Marque4', 'Téléphone4', 'descritpion mobile4'),
(126, 'Marque5', 'Téléphone5', 'descritpion mobile5'),
(127, 'Marque6', 'Téléphone6', 'descritpion mobile6'),
(128, 'Marque7', 'Téléphone7', 'descritpion mobile7'),
(129, 'Marque8', 'Téléphone8', 'descritpion mobile8'),
(130, 'Marque9', 'Téléphone9', 'descritpion mobile9');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  KEY `IDX_8D93D64919EB6921` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `client_id`, `name`, `email`, `password`, `roles`) VALUES
(102, 26, 'user9', 'email9@gmail.com', '$2y$13$aMx/txZ4NTVZ4fsNgazeyOztwzT99xbihi7tL7qplEt4q4EhgvdUe', '[\"ROLE_USER\"]'),
(119, 25, 'user20', 'user20@gmail.com', 'password', '[\"ROLE_USER\"]'),
(120, 25, 'creatuesertTest', 'createUere@gmail.com', '$2y$13$7XAN6JD29NLFac1Lp0zm/eoBqL0Bd/QranyS7emphWwlmBnrqtcUa', '[\"ROLE_USER\"]'),
(122, 25, 'userh', 'fg@gmail.com', '$2y$13$vLR37W1psdE2916Z0wDvK.B80mLRJqcgZC5Py.ZOPHhkQVST9DhSm', '[\"ROLE_USER\"]'),
(123, 25, 'OpenClassRoom', 'OpenClassRoom@gmail.com', '$2y$13$mg65TJjKl5w1vRQ2CftazeB1RPs5TN8rN5.cNZkyZAE3DNUNYNnfu', '[\"ROLE_USER\"]');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `FK_8D93D64919EB6921` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
