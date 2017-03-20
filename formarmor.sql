-- phpMyAdmin SQL Dump
-- version 4.7.0-beta1
-- https://www.phpmyadmin.net/
--
-- Client :  localhost
-- Généré le :  Lun 20 Mars 2017 à 15:30
-- Version du serveur :  5.7.14
-- Version de PHP :  5.6.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `formarmor`
--

DELIMITER $$
--
-- Procédures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sessions_autorisees` (IN `pIdClient` INT)  BEGIN
    SELECT s.*, f.libelle, f.niveau, f.type_form, f.description, f.diplomante, f.duree, f.coutrevient, n.nbPlacesDispos
    FROM session_formation s, formation f, plan_formation p, nbPlacesRestantes n
    WHERE s.id NOT IN (SELECT session_formation_id FROM inscription WHERE client_id = pIdClient)
    AND p.client_id = pIdClient
    AND p.formation_id = s.formation_id
    AND p.effectue = 0
    AND s.id = n.numSession
    AND s.close = 0
    AND s.formation_id = f.id
    AND n.nbPlacesDispos > 0
    AND s.date_debut > CURRENT_DATE
    ORDER BY s.id ASC;
END$$

--
-- Fonctions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `nbPlacesRestantes` (`numSession` INT) RETURNS INT(11) BEGIN
    RETURN (SELECT s.nb_places - COUNT(i.id)
    FROM session_formation s, inscription i
    WHERE i.session_formation_id = s.id
    AND s.id = numSession);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

CREATE TABLE `client` (
  `id` int(11) NOT NULL,
  `statut_id` int(11) NOT NULL,
  `nom` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `adresse` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `cp` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `ville` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `nbhcpta` smallint(6) NOT NULL,
  `nbhbur` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `client`
--

INSERT INTO `client` (`id`, `statut_id`, `nom`, `password`, `adresse`, `cp`, `ville`, `email`, `nbhcpta`, `nbhbur`) VALUES
(1, 1, 'DUPONT Alain', 'dupal', '3 rue de la gare', '22 200', 'Guingamp', 'dupont.alain127@gmail.com', 70, 175),
(2, 2, 'LAMBERT Alain', 'lamal', '17 rue de la ville', '22 200', 'Guingamp', 'lambert.alain12@gmail.com', 0, 105),
(3, 3, 'SARGES Annie', 'saran', '125 boulevard de Nantes', '35 000', 'Rennes', 'sarges.annie@laposte.net', 160, 70),
(4, 4, 'CHARLES Patrick', 'chapa', '27 Bd Lamartine', '22 000', 'Saint Brieuc', 'charles.patrick@hotmail.fr', 120, 105),
(5, 5, 'DUMAS Serge', 'dumse', 'Pors Braz', '22 200', 'Plouisy', 'dumas.serge@hotmail.com', 160, 175),
(6, 1, 'SYLVESTRE Marc', 'sylma', '17 rue des ursulines', '22 300', 'Lannion', 'sylvestre_marc3@gmail.com', 0, 70),
(7, 1, 'DEJOUE Valentin', 'harry20013', '7 RUE ROGER VERCEL', '22960', 'PLEDRAN', 'valentin_dejoue@live.fr', 70, 175);

-- --------------------------------------------------------

--
-- Structure de la table `formation`
--

CREATE TABLE `formation` (
  `id` int(11) NOT NULL,
  `libelle` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `niveau` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `type_form` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `diplomante` tinyint(1) NOT NULL,
  `duree` int(11) NOT NULL,
  `coutrevient` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `formation`
--

INSERT INTO `formation` (`id`, `libelle`, `niveau`, `type_form`, `description`, `diplomante`, `duree`, `coutrevient`) VALUES
(1, 'Access', 'Initiation', 'Bureautique', 'Decouverte du logiciel Access', 0, 35, 140),
(2, 'Access', 'Perfectionnement', 'Bureautique', 'Fonctions avancees du logiciel Access', 0, 35, 100),
(3, 'Compta1', 'Initiation', 'Compta', 'Decouverte des principes d ecriture comptable', 0, 70, 180),
(4, 'Compta2', 'perfectionnement', 'Bureautique', 'Bilan et compte de r?sultat', 0, 70, 180),
(5, 'Compta3', 'Perfectionnement', 'Compta', 'Analyse du bilan', 0, 70, 100),
(6, 'Compta4', 'Perfectionnement', 'Bureautique', 'Operations d inventaire', 1, 70, 140),
(7, 'Excel', 'Initiation', 'Bureautique', 'Decouverte du logiciel Excel', 0, 35, 100),
(8, 'Excel', 'Perfectionnement', 'Bureautique', 'Fonctions avancees du logiciel Excel', 0, 35, 110),
(9, 'Word', 'Initiation', 'Bureautique', 'Decouverte du logiciel Word', 0, 35, 100),
(10, 'Word', 'Perfectionnement', 'Bureautique', 'Fonctions avancees du logiciel Word', 0, 35, 110);

-- --------------------------------------------------------

--
-- Structure de la table `inscription`
--

CREATE TABLE `inscription` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `session_formation_id` int(11) NOT NULL,
  `date_inscription` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `inscription`
--

INSERT INTO `inscription` (`id`, `client_id`, `session_formation_id`, `date_inscription`) VALUES
(2, 1, 5, '2017-02-27'),
(4, 7, 1, '2017-03-20'),
(5, 7, 5, '2017-03-20'),
(6, 1, 1, '2017-03-22');

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `nbplacesrestantes`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `nbplacesrestantes` (
`numSession` int(11)
,`nbPlacesDispos` int(11)
);

-- --------------------------------------------------------

--
-- Structure de la table `plan_formation`
--

CREATE TABLE `plan_formation` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `formation_id` int(11) NOT NULL,
  `effectue` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `plan_formation`
--

INSERT INTO `plan_formation` (`id`, `client_id`, `formation_id`, `effectue`) VALUES
(1, 1, 7, 0),
(2, 1, 10, 0),
(3, 2, 1, 0),
(4, 3, 1, 0),
(7, 7, 7, 0),
(8, 7, 10, 0);

-- --------------------------------------------------------

--
-- Structure de la table `session_formation`
--

CREATE TABLE `session_formation` (
  `id` int(11) NOT NULL,
  `formation_id` int(11) NOT NULL,
  `date_debut` date NOT NULL,
  `nb_places` smallint(6) NOT NULL,
  `nb_inscrits` smallint(6) NOT NULL,
  `close` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `session_formation`
--

INSERT INTO `session_formation` (`id`, `formation_id`, `date_debut`, `nb_places`, `nb_inscrits`, `close`) VALUES
(1, 7, '2017-03-30', 18, 1, 1),
(2, 1, '2016-02-01', 16, 0, 0),
(3, 2, '2016-02-15', 16, 0, 0),
(4, 9, '2016-01-18', 18, 0, 0),
(5, 10, '2017-03-23', 18, 2, 1),
(6, 8, '2016-02-08', 18, 0, 0),
(7, 3, '2017-03-24', 25, 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `statut`
--

CREATE TABLE `statut` (
  `id` int(11) NOT NULL,
  `type` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `taux_horaire` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `statut`
--

INSERT INTO `statut` (`id`, `type`, `taux_horaire`) VALUES
(1, '1%', 12),
(2, 'Autre', 8),
(3, 'CIF', 6),
(4, 'SIFE_collectif', 10),
(5, 'SIFE_individuel', 11);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `viewdetailsession`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `viewdetailsession` (
`id` int(11)
,`formation_id` int(11)
,`date_debut` date
,`nb_places` smallint(6)
,`nb_inscrits` smallint(6)
,`close` tinyint(1)
,`libelle` varchar(50)
,`niveau` varchar(40)
,`type_form` varchar(50)
,`description` varchar(255)
,`diplomante` tinyint(1)
,`duree` int(11)
,`coutrevient` double
,`nbPlacesDispos` int(11)
);

-- --------------------------------------------------------

--
-- Structure de la vue `nbplacesrestantes`
--
DROP TABLE IF EXISTS `nbplacesrestantes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `nbplacesrestantes`  AS  select `session_formation`.`id` AS `numSession`,`nbPlacesRestantes`(`session_formation`.`id`) AS `nbPlacesDispos` from `session_formation` order by `session_formation`.`id` ;

-- --------------------------------------------------------

--
-- Structure de la vue `viewdetailsession`
--
DROP TABLE IF EXISTS `viewdetailsession`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `viewdetailsession`  AS  select `s`.`id` AS `id`,`s`.`formation_id` AS `formation_id`,`s`.`date_debut` AS `date_debut`,`s`.`nb_places` AS `nb_places`,`s`.`nb_inscrits` AS `nb_inscrits`,`s`.`close` AS `close`,`f`.`libelle` AS `libelle`,`f`.`niveau` AS `niveau`,`f`.`type_form` AS `type_form`,`f`.`description` AS `description`,`f`.`diplomante` AS `diplomante`,`f`.`duree` AS `duree`,`f`.`coutrevient` AS `coutrevient`,`n`.`nbPlacesDispos` AS `nbPlacesDispos` from ((`session_formation` `s` join `formation` `f`) join `nbplacesrestantes` `n`) where ((`s`.`formation_id` = `f`.`id`) and (`s`.`id` = `n`.`numSession`)) ;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_C7440455F6203804` (`statut_id`);

--
-- Index pour la table `formation`
--
ALTER TABLE `formation`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `inscription`
--
ALTER TABLE `inscription`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_5E90F6D619EB6921` (`client_id`),
  ADD KEY `IDX_5E90F6D69C9D95AF` (`session_formation_id`);

--
-- Index pour la table `plan_formation`
--
ALTER TABLE `plan_formation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_F09EDCAA19EB6921` (`client_id`),
  ADD KEY `IDX_F09EDCAA5200282E` (`formation_id`);

--
-- Index pour la table `session_formation`
--
ALTER TABLE `session_formation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_3A264B55200282E` (`formation_id`);

--
-- Index pour la table `statut`
--
ALTER TABLE `statut`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `client`
--
ALTER TABLE `client`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT pour la table `formation`
--
ALTER TABLE `formation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT pour la table `inscription`
--
ALTER TABLE `inscription`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT pour la table `plan_formation`
--
ALTER TABLE `plan_formation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT pour la table `session_formation`
--
ALTER TABLE `session_formation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT pour la table `statut`
--
ALTER TABLE `statut`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `client`
--
ALTER TABLE `client`
  ADD CONSTRAINT `FK_C7440455F6203804` FOREIGN KEY (`statut_id`) REFERENCES `statut` (`id`);

--
-- Contraintes pour la table `inscription`
--
ALTER TABLE `inscription`
  ADD CONSTRAINT `FK_5E90F6D619EB6921` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`),
  ADD CONSTRAINT `FK_5E90F6D69C9D95AF` FOREIGN KEY (`session_formation_id`) REFERENCES `session_formation` (`id`);

--
-- Contraintes pour la table `plan_formation`
--
ALTER TABLE `plan_formation`
  ADD CONSTRAINT `FK_F09EDCAA19EB6921` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`),
  ADD CONSTRAINT `FK_F09EDCAA5200282E` FOREIGN KEY (`formation_id`) REFERENCES `formation` (`id`);

--
-- Contraintes pour la table `session_formation`
--
ALTER TABLE `session_formation`
  ADD CONSTRAINT `FK_3A264B55200282E` FOREIGN KEY (`formation_id`) REFERENCES `formation` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
