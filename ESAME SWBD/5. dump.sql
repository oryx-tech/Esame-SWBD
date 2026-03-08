DROP DATABASE IF EXISTS associazione_micologica;
CREATE DATABASE associazione_micologica;
USE associazione_micologica;


CREATE TABLE UTENTE (
    Id_User INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(50) NOT NULL,
    Cognome VARCHAR(50) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Ruolo ENUM('Utente Esterno', 'Utente Premium', 'Socio Cercatore', 'Micologo Esperto', 'Amministratore') DEFAULT 'Utente Esterno'
) ENGINE=InnoDB;


CREATE TABLE PAGAMENTO (
    Id_Pagamento INT AUTO_INCREMENT PRIMARY KEY,
    Data_Transazione DATE NOT NULL,
    Importo DECIMAL(6,2) NOT NULL,
    Tipo_Servizio ENUM('Abbonamento Annuale', 'Consulenza Singola') NOT NULL,
    Id_User_Cliente INT NOT NULL,
    FOREIGN KEY (Id_User_Cliente) REFERENCES UTENTE(Id_User) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE SPECIE_FUNGINA (
    Id_Specie INT AUTO_INCREMENT PRIMARY KEY,
    Nome_Scientifico VARCHAR(100) NOT NULL,
    Nome_Volgare VARCHAR(100) NOT NULL,
    Categoria ENUM('Commestibile', 'Velenosa', 'Medicinale', 'Ornamentale') NOT NULL,
    Descrizione TEXT,
    Stato_Approvazione ENUM('In attesa', 'Approvata', 'Rifiutata') DEFAULT 'In attesa'
) ENGINE=InnoDB;


CREATE TABLE CONSULENZA_PRIVATA (
    Id_Consulenza INT AUTO_INCREMENT PRIMARY KEY,
    Data_Richiesta DATE NOT NULL,
    URL_Foto VARCHAR(255) NOT NULL,
    Colore_Cappello VARCHAR(50) NOT NULL,
    Colore_Fusto VARCHAR(50) NOT NULL,
    Habitat_Alberi VARCHAR(100) NOT NULL,
    Esito_Risposta TEXT,
    Id_User_Premium INT NOT NULL,
    Id_User_Micologo INT DEFAULT NULL,
    FOREIGN KEY (Id_User_Premium) REFERENCES UTENTE(Id_User) ON DELETE CASCADE,
    FOREIGN KEY (Id_User_Micologo) REFERENCES UTENTE(Id_User) ON DELETE SET NULL
) ENGINE=InnoDB;


CREATE TABLE RACCOLTA (
    Id_Raccolta INT AUTO_INCREMENT PRIMARY KEY,
    Data_Raccolta DATE NOT NULL,
    Luogo VARCHAR(100) NOT NULL,
    Quantita_Peso DECIMAL(5,2) NOT NULL,
    URL_Foto VARCHAR(255) NOT NULL,
    Stato_Lavorazione ENUM('In attesa', 'Inoltrata all Admin', 'Certificata', 'Bocciata') DEFAULT 'In attesa',
    Id_User_Socio INT NOT NULL,
    Id_Specie_Presunta INT NOT NULL,
    FOREIGN KEY (Id_User_Socio) REFERENCES UTENTE(Id_User) ON DELETE CASCADE,
    FOREIGN KEY (Id_Specie_Presunta) REFERENCES SPECIE_FUNGINA(Id_Specie) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE CERTIFICAZIONE (
    Id_Certificato INT AUTO_INCREMENT PRIMARY KEY,
    Data_Emissione DATE NOT NULL,
    Esito VARCHAR(100) NOT NULL,
    Motivazione_Note TEXT NOT NULL,
    Id_User_Micologo INT NOT NULL,
    Id_Specie_Reale INT NOT NULL,
    Id_Raccolta INT NOT NULL UNIQUE,
    FOREIGN KEY (Id_User_Micologo) REFERENCES UTENTE(Id_User) ON DELETE RESTRICT,
    FOREIGN KEY (Id_Specie_Reale) REFERENCES SPECIE_FUNGINA(Id_Specie) ON DELETE RESTRICT,
    FOREIGN KEY (Id_Raccolta) REFERENCES RACCOLTA(Id_Raccolta) ON DELETE CASCADE
) ENGINE=InnoDB;


INSERT INTO UTENTE (Nome, Cognome, Email, Password, Ruolo) VALUES
('Admin', 'Supremo', 'admin@micologia.it', '$2y$10$J7dquPeAeramnWYLLG7fy.8Rw8F41KCF1yb4J6NNugN8d355.fcKG', 'Amministratore'),
('Luigi', 'De verdi', 'luigi@micologo.it', '$2y$10$J7dquPeAeramnWYLLG7fy.8Rw8F41KCF1yb4J6NNugN8d355.fcKG', 'Micologo Esperto'),
('Giovanni', 'Pio', 'giovanni@socio.it', '$2y$10$J7dquPeAeramnWYLLG7fy.8Rw8F41KCF1yb4J6NNugN8d355.fcKG', 'Socio Cercatore'),
('Chiara', 'Di lorenzo', 'chiara@premium.it', '$2y$10$J7dquPeAeramnWYLLG7fy.8Rw8F41KCF1yb4J6NNugN8d355.fcKG', 'Utente Premium'),
('Alfredo', 'Rossi', 'alfredo@esterno.it', '$2y$10$J7dquPeAeramnWYLLG7fy.8Rw8F41KCF1yb4J6NNugN8d355.fcKG', 'Utente Esterno'),
('Utente', 'Cancellato', 'cancellato@micologia.it', '***ACCOUNT_ELIMINATO***', 'Utente Esterno');


INSERT INTO PAGAMENTO (Data_Transazione, Importo, Tipo_Servizio, Id_User_Cliente) VALUES
('2026-01-10', 100.00, 'Abbonamento Annuale', 4),
('2026-02-15', 15.00, 'Consulenza Singola', 5);

INSERT INTO SPECIE_FUNGINA (Nome_Scientifico, Nome_Volgare, Categoria, Descrizione, Stato_Approvazione) VALUES
('Boletus edulis', 'Porcino', 'Commestibile', 'Fungo pregiato dal cappello marrone.', 'Approvata'),
('Amanita phalloides', 'Tignosa verdognola', 'Velenosa', 'Fungo mortale, spesso confuso.', 'Approvata'),
('Ganoderma lucidum', 'Reishi', 'Medicinale', 'Fungo per infusi tradizionali.', 'Approvata'),
('Russula aurea', 'Colombina dorata', 'Commestibile', 'Fungo giallo-arancio.', 'In attesa');

INSERT INTO CONSULENZA_PRIVATA (Data_Richiesta, URL_Foto, Colore_Cappello, Colore_Fusto, Habitat_Alberi, Id_User_Premium) VALUES
('2026-03-01', 'uploads/premium/fungo_chiara_1.jpg', 'Rosso vivo', 'Bianco', 'Sotto un pino', 4);

INSERT INTO CONSULENZA_PRIVATA (Data_Richiesta, URL_Foto, Colore_Cappello, Colore_Fusto, Habitat_Alberi, Esito_Risposta, Id_User_Premium, Id_User_Micologo) VALUES
('2026-03-05', 'uploads/premium/fungo_chiara_2.jpg', 'Marrone scuro', 'Tozzo e reticolato', 'Bosco di querce', 'Gentile Chiara, è un bellissimo Porcino!', 4, 2);

INSERT INTO RACCOLTA (Data_Raccolta, Luogo, Quantita_Peso, URL_Foto, Stato_Lavorazione, Id_User_Socio, Id_Specie_Presunta) VALUES
('2026-03-10', 'Appennino Tosco-Emiliano', 2.50, 'uploads/raccolte/socio_1.jpg', 'Certificata', 3, 1),
('2026-03-12', 'Trentino', 1.00, 'uploads/raccolte/socio_2_sfocata.jpg', 'Bocciata', 3, 1),
('2026-03-20', 'Sila', 0.80, 'uploads/raccolte/socio_3.jpg', 'In attesa', 3, 2);

INSERT INTO CERTIFICAZIONE (Data_Emissione, Esito, Motivazione_Note, Id_User_Micologo, Id_Specie_Reale, Id_Raccolta) VALUES
('2026-03-11', 'Idoneo al consumo', 'Esemplare perfetto.', 2, 1, 1),
('2026-03-13', 'Bocciata', 'Foto sfocata, impossibile procedere.', 2, 1, 2);