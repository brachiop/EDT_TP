-- Peuplement des tables avec des données d'exemple

-- Sections
INSERT INTO sections (id, nom) VALUES
(1, 'A'),
(2, 'B');

-- Groupes TD
INSERT INTO groupes_td (id, nom, section_id) VALUES
(1, 'A1', 1),
(2, 'A2', 1),
(3, 'B1', 2),
(4, 'B2', 2);

-- Groupes TP
INSERT INTO groupes_tp (id, nom, groupe_td_id) VALUES
(1, 'A1a', 1),
(2, 'A1b', 1),
(3, 'A2a', 2),
(4, 'A2b', 2),
(5, 'B1a', 3),
(6, 'B1b', 3),
(7, 'B2a', 4),
(8, 'B2b', 4);

-- Créneaux horaires
INSERT INTO creneaux (id, jour, heure_debut, heure_fin) VALUES
(1, 'Lundi', '08:00:00', '10:00:00'),
(2, 'Lundi', '10:15:00', '12:15:00'),
(3, 'Lundi', '13:30:00', '15:30:00'),
(4, 'Lundi', '15:45:00', '17:45:00'),
(5, 'Mardi', '08:00:00', '10:00:00'),
(6, 'Mardi', '10:15:00', '12:15:00'),
(7, 'Mardi', '13:30:00', '15:30:00'),
(8, 'Mardi', '15:45:00', '17:45:00'),
(9, 'Mercredi', '08:00:00', '10:00:00'),
(10, 'Mercredi', '10:15:00', '12:15:00'),
(11, 'Mercredi', '13:30:00', '15:30:00'),
(12, 'Mercredi', '15:45:00', '17:45:00'),
(13, 'Jeudi', '08:00:00', '10:00:00'),
(14, 'Jeudi', '10:15:00', '12:15:00'),
(15, 'Jeudi', '13:30:00', '15:30:00'),
(16, 'Jeudi', '15:45:00', '17:45:00'),
(17, 'Vendredi', '08:00:00', '10:00:00'),
(18, 'Vendredi', '10:15:00', '12:15:00'),
(19, 'Vendredi', '13:30:00', '15:30:00'),
(20, 'Vendredi', '15:45:00', '17:45:00');

-- EDT existants (CM et TD)
INSERT INTO edt_existants (id, type, groupe_td_id, section_id, creneau_id, matiere) VALUES
-- CM pour la section A
(1, 'CM', NULL, 1, 1, 'Mathématiques'),
(2, 'CM', NULL, 1, 5, 'Physique'),

-- CM pour la section B
(3, 'CM', NULL, 2, 2, 'Mathématiques'),
(4, 'CM', NULL, 2, 6, 'Physique'),

-- TD pour les groupes
(5, 'TD', 1, NULL, 3, 'TD Mathématiques A1'),
(6, 'TD', 2, NULL, 7, 'TD Mathématiques A2'),
(7, 'TD', 3, NULL, 11, 'TD Mathématiques B1'),
(8, 'TD', 4, NULL, 15, 'TD Mathématiques B2'),

(9, 'TD', 1, NULL, 4, 'TD Physique A1'),
(10, 'TD', 2, NULL, 8, 'TD Physique A2'),
(11, 'TD', 3, NULL, 12, 'TD Physique B1'),
(12, 'TD', 4, NULL, 16, 'TD Physique B2');

-- Quelques TP déjà planifiés
INSERT INTO edt_tp (id, groupe_tp_id, creneau_id, matiere, salle) VALUES
(1, 1, 9, 'TP Informatique', 'Salle 101'),
(2, 2, 13, 'TP Chimie', 'Labo A'),
(3, 5, 17, 'TP Électronique', 'Labo B');