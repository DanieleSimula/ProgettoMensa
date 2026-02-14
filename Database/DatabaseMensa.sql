-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Feb 13, 2026 alle 16:20
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mensachevorrei`
--

DELIMITER $$
--
-- Procedure
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `aggiungi_menu` (IN `p_nome` VARCHAR(20), IN `p_piatti_csv` TEXT)   BEGIN
    DECLARE v_menu_id INT;
    DECLARE v_piatto_id INT;
    DECLARE v_pos INT;
    DECLARE v_remaining TEXT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    INSERT INTO menu (nome) VALUES (p_nome);
    SET v_menu_id = LAST_INSERT_ID();

    SET v_remaining = p_piatti_csv;

    WHILE LENGTH(v_remaining) > 0 DO
        SET v_pos = LOCATE(',', v_remaining);
        IF v_pos > 0 THEN
            SET v_piatto_id = CAST(SUBSTRING(v_remaining, 1, v_pos - 1) AS UNSIGNED);
            SET v_remaining = SUBSTRING(v_remaining, v_pos + 1);
        ELSE
            SET v_piatto_id = CAST(v_remaining AS UNSIGNED);
            SET v_remaining = '';
        END IF;

        INSERT INTO piatti_menu (id_menu, id_piatto) VALUES (v_menu_id, v_piatto_id);
    END WHILE;

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `aggiungi_operatore` (IN `p_nomeutente` VARCHAR(30), IN `p_email` VARCHAR(30), IN `p_passwordhash` VARCHAR(255))   BEGIN

    DECLARE v_ruolo VARCHAR(9) DEFAULT 'operator';

    INSERT INTO utente(
        nomeutente,
        email,
        passwordhash,
        ruolo
    )
    VALUES(
        p_nomeutente,
        p_email,
        p_passwordhash,
        v_ruolo
        );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `aggiungi_piatto` (IN `p_titolo` VARCHAR(20), IN `p_descrizione` VARCHAR(255))   BEGIN
    DECLARE v_esiste INT;
    
    SELECT COUNT(*) INTO v_esiste 
    FROM piatto 
    WHERE nome = p_titolo;
    
    IF v_esiste > 0 THEN 
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Titolo già esistente';
    END IF;
    
    INSERT INTO piatto (nome, descrizione) 
    VALUES (p_titolo, p_descrizione);
    
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `assegna_menu_giorno` (IN `p_data` DATE, IN `p_pranzo_cena` TINYINT, IN `p_menu_id` INT)   BEGIN
    DECLARE v_count INT;

    SELECT COUNT(*) INTO v_count
    FROM menudelgiorno
    WHERE DATE(data) = p_data AND pranzo_cena = p_pranzo_cena;

    IF v_count > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Esiste già un menu assegnato per questa data e tipo pasto';
    END IF;

    INSERT INTO menudelgiorno (data, pranzo_cena, menuID)
    VALUES (p_data, p_pranzo_cena, p_menu_id);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `elimina_menu` (IN `p_id` INT)   BEGIN
    DELETE FROM menu
    WHERE id = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `genera_token` (IN `p_studente` VARCHAR(16), IN `p_codice_segreto` VARCHAR(255))   BEGIN
    DECLARE v_token_esistente VARCHAR(255);
    DECLARE v_pasto_esistente INT;
    DECLARE v_scadenza_esistente TIMESTAMP;
    DECLARE v_pasto_id INT;
    DECLARE v_token_id VARCHAR(255);
    
    -- Controlla se esiste già un token attivo (non scaduto)
    SELECT id, pasto, data_scadenza 
    INTO v_token_esistente, v_pasto_esistente, v_scadenza_esistente
    FROM token
    WHERE studente = p_studente 
    AND data_scadenza > NOW()
    LIMIT 1;
    
    IF v_token_esistente IS NOT NULL THEN
        -- Restituisci il token esistente
        SELECT 
            v_token_esistente AS token, 
            v_pasto_esistente AS pasto, 
            v_scadenza_esistente AS scadenza,
            1 AS esistente;
    ELSE
        -- Elimina eventuali token scaduti dello studente
        DELETE FROM token 
        WHERE studente = p_studente 
        AND data_scadenza <= NOW();
        
        -- Cerca un pasto disponibile (non ancora utilizzato)
        SELECT id INTO v_pasto_id
        FROM pasto
        WHERE studente = p_studente 
        AND dataUtilizzo IS NULL
        LIMIT 1;
        
        IF v_pasto_id IS NULL THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Lo studente non ha pasti disponibili';
        ELSE
            -- Genera il token hashando codicesegreto+studente+pasto
            SET v_token_id = SHA2(CONCAT(p_codice_segreto, p_studente, v_pasto_id), 256);
            
            -- Inserisci il token
            INSERT INTO token (id, studente, pasto, data_scadenza)
            VALUES (v_token_id, p_studente, v_pasto_id, DATE_ADD(NOW(), INTERVAL 5 MINUTE));
            
            -- Restituisci il token creato
            SELECT 
                v_token_id AS token, 
                v_pasto_id AS pasto, 
                DATE_ADD(NOW(), INTERVAL 5 MINUTE) AS scadenza,
                0 AS esistente;
        END IF;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getInfoStudente` (IN `param_nomeutente` VARCHAR(30))   BEGIN
	SELECT cf, nome, cognome, sesso, datanascita, indirizzo, citta, pasti, fascia
    FROM info_studente
    WHERE nomeutente = param_nomeutente;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetMenuDelGiorno` (IN `dataRichiesta` DATE)   BEGIN
    SELECT 
        mdg.data,
        CASE 
            WHEN mdg.pranzo_cena = 0 THEN 'Pranzo'
            ELSE 'Cena'
        END AS tipo_pasto,
        m.nome AS menu_nome,
        p.id AS piatto_id,
        p.nome AS piatto_nome,
        p.descrizione AS piatto_descrizione,
        GROUP_CONCAT(a.nome SEPARATOR ', ') AS allergeni
    FROM menudelgiorno mdg
    INNER JOIN menu m ON mdg.menuID = m.id
    INNER JOIN piatti_menu pm ON m.id = pm.id_menu
    INNER JOIN piatto p ON pm.id_piatto = p.id
    LEFT JOIN piatti_allergeni pa ON p.id = pa.id_piatto
    LEFT JOIN allergene a ON pa.id_allergene = a.id
    WHERE DATE(mdg.data) = dataRichiesta
    GROUP BY mdg.data, mdg.pranzo_cena, m.nome, p.id, p.nome, p.descrizione
    ORDER BY mdg.pranzo_cena, p.id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetNotiziaById` (IN `p_id` INT)   BEGIN
    SELECT * FROM notizie WHERE id = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetNotizie` ()   SELECT *
FROM notizie
WHERE attiva = 1
ORDER BY dataPubblicazione DESC$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getTariffa` (IN `param_fascia` VARCHAR(30))   BEGIN
	SELECT costo FROM tariffa WHERE fascia = param_fascia;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getUserStudente` (IN `param_nomeutente` VARCHAR(30))   BEGIN
    SELECT passwordhash FROM utente
    WHERE nomeutente = param_nomeutente AND
    ruolo = 'studente';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_elenco_menu` ()   BEGIN
    SELECT id, nome FROM menu ORDER BY nome;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_menu_programmati` ()   BEGIN
    SELECT mdg.data, mdg.pranzo_cena, m.nome AS menu_nome
    FROM menudelgiorno mdg
    INNER JOIN menu m ON mdg.menuID = m.id
    WHERE DATE(mdg.data) >= CURDATE()
    ORDER BY mdg.data, mdg.pranzo_cena;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_notizie` (IN `p_titolo` VARCHAR(255), IN `p_stato` TINYINT)   BEGIN
    SELECT * FROM notizie 
    WHERE (p_titolo IS NULL OR titolo LIKE CONCAT('%', p_titolo, '%'))
      AND (p_stato IS NULL OR attiva = p_stato)
    ORDER BY dataPubblicazione DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_operatori` ()   BEGIN
    SELECT  
        nomeutente,  
        email
    FROM utente
    WHERE ruolo = 'operator'
    ORDER BY nomeutente ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_piatti` ()   BEGIN
    SELECT id, nome FROM piatto ORDER BY nome;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_piatti_menu` (IN `p_menu_id` INT)   BEGIN
    SELECT p.id, p.nome, p.descrizione
    FROM piatto p
    INNER JOIN piatti_menu pm ON p.id = pm.id_piatto
    WHERE pm.id_menu = p_menu_id
    ORDER BY p.nome;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `inserisci_consumazione` (IN `p_cf` VARCHAR(16), IN `p_fascia` INT, IN `p_quantit` INT)   BEGIN
    DECLARE v_esiste INT;
    DECLARE errore INT DEFAULT 0;
    DECLARE i INT DEFAULT 0;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
        BEGIN ROLLBACK; END;

       SELECT COUNT(*) INTO v_esiste FROM info_studente WHERE cf = p_cf;
    
    IF v_esiste = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Studente non trovato';
    END IF;

   
	START TRANSACTION;

	WHILE i < p_quantit DO
INSERT INTO pasto (id, dataAcquisto, dataUtilizzo, studente, menuID, fascia)
VALUES (NULL, NOW(), NULL, p_cf, NULL, p_fascia);
SET i = i + 1;
END WHILE;
COMMIT;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `inserisci_notizia` (IN `p_titolo` VARCHAR(255), IN `p_descrizione` VARCHAR(500), IN `p_contenuto` TEXT, IN `p_immagine` VARCHAR(255), IN `p_autore` VARCHAR(30))   BEGIN
       INSERT INTO notizie (
        titolo, 
        descrizione, 
        contenuto, 
        immagine, 
        autore,
        dataPubblicazione
    ) 
    VALUES (
        p_titolo, 
        p_descrizione, 
        p_contenuto, 
        p_immagine, 
        p_autore,
        NOW() 
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `login_operatore` (IN `p_nomeUtente` VARCHAR(255))   BEGIN
    SELECT utente.passwordhash, utente.ruolo
    FROM utente
    WHERE utente.nomeutente = p_nomeUtente
    AND (utente.ruolo = 'operator' OR utente.ruolo = 'admin');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `regNewStudente` (IN `param_nomeutente` VARCHAR(30), IN `param_email` VARCHAR(30), IN `param_passwordhash` VARCHAR(255), IN `param_cf` VARCHAR(16), IN `param_nome` VARCHAR(30), IN `param_cognome` VARCHAR(30), IN `param_sesso` ENUM('M','F','A'), IN `param_datanascita` DATE, IN `param_indirizzo` VARCHAR(80), IN `param_citta` VARCHAR(30), IN `param_fascia` INT)   BEGIN
    -- Dichiara handler per gestire gli errori SQL
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        -- In caso di errore, annulla tutte le modifiche
        ROLLBACK;
        -- Rilancia l'errore
        RESIGNAL;
    END;
    
    -- Inizia la transazione
    START TRANSACTION;
    
    -- Inserisce il nuovo utente nella tabella utente
    INSERT INTO utente (nomeutente, email, passwordhash, ruolo) 
    VALUES (param_nomeutente, param_email, param_passwordhash, 'studente');
    
    -- Inserisce le informazioni dello studente nella tabella info_studente
    INSERT INTO info_studente (cf, nomeutente, nome, cognome, sesso, datanascita, indirizzo, citta, fascia, pasti)
    VALUES (param_cf, param_nomeutente, param_nome, param_cognome, param_sesso, param_datanascita, param_indirizzo, param_citta, param_fascia, 0);
    
    -- Conferma tutte le modifiche
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ricerca_piatti` (IN `p_id` INT(11), IN `p_nome` VARCHAR(20))   BEGIN
    IF p_id IS NOT NULL THEN
        SELECT * FROM piatto WHERE id = p_id;
    
    ELSEIF p_nome IS NOT NULL THEN
        SELECT * FROM piatto WHERE nome LIKE CONCAT('%', p_nome, '%');
    
    ELSE
        SELECT * FROM piatto;
    END IF;
    
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ricerca_studenti` (IN `campo` VARCHAR(30), IN `valore` VARCHAR(100))   BEGIN

    IF campo = 'cf' THEN
        SELECT 
            i.cf,
            i.nomeutente,
            u.email,
            i.nome,
            i.cognome,
            i.sesso,
            i.datanascita,
            i.citta,
            i.fascia,
            i.pasti
        FROM info_studente i
        INNER JOIN utente u ON i.nomeutente = u.nomeutente
        WHERE i.cf LIKE CONCAT('%', valore, '%');
        
    ELSEIF campo = 'nomeutente' THEN
        SELECT 
            i.cf,
            i.nomeutente,
            u.email,
            i.nome,
            i.cognome,
            i.sesso,
            i.datanascita,
            i.citta,
            i.fascia,
            i.pasti
        FROM info_studente i
        INNER JOIN utente u ON i.nomeutente = u.nomeutente
        WHERE i.nomeutente LIKE CONCAT('%', valore, '%');
        
    ELSEIF campo = 'email' THEN
        SELECT 
            i.cf,
            i.nomeutente,
            u.email,
            i.nome,
            i.cognome,
            i.sesso,
            i.datanascita,
            i.citta,
            i.fascia,
            i.pasti
        FROM info_studente i
        INNER JOIN utente u ON i.nomeutente = u.nomeutente
        WHERE u.email LIKE CONCAT('%', valore, '%');
        
    ELSEIF campo = 'nome' THEN
        SELECT 
            i.cf,
            i.nomeutente,
            u.email,
            i.nome,
            i.cognome,
            i.sesso,
            i.datanascita,
            i.citta,
            i.fascia,
            i.pasti
        FROM info_studente i
        INNER JOIN utente u ON i.nomeutente = u.nomeutente
        WHERE i.nome LIKE CONCAT('%', valore, '%');
        
    ELSEIF campo = 'cognome' THEN
        SELECT 
            i.cf,
            i.nomeutente,
            u.email,
            i.nome,
            i.cognome,
            i.sesso,
            i.datanascita,
            i.citta,
            i.fascia,
            i.pasti
        FROM info_studente i
        INNER JOIN utente u ON i.nomeutente = u.nomeutente
        WHERE i.cognome LIKE CONCAT('%', valore, '%');
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `rimuovi_consumazione` (IN `p_cf` VARCHAR(16))   BEGIN
    DECLARE v_pasti INT;
    DECLARE v_esiste INT;
    DECLARE idPasto INT;

    
    SELECT COUNT(*) INTO v_esiste FROM info_studente WHERE cf = p_cf;
    
    IF v_esiste = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Studente non trovato';
    END IF;

   
    SELECT pasti INTO v_pasti FROM info_studente WHERE cf = p_cf;

    IF v_pasti > 0 THEN
        
        SELECT id INTO idPasto FROM pasto WHERE studente = p_cf AND dataUtilizzo IS NULL LIMIT 1;
        UPDATE pasto SET dataUtilizzo = CURRENT_TIMESTAMP
        WHERE id = idPasto;
    ELSE
        
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Credito pasti esaurito';
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `rimuovi_menu_giorno` (IN `p_data` TIMESTAMP, IN `p_pasto` TINYINT)   BEGIN
    DELETE FROM menudelgiorno
    WHERE data = p_data AND
          pranzo_cena = p_pasto;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `rimuovi_notizia` (IN `p_id_notizia` INT)   BEGIN
    DELETE FROM notizie 
    WHERE id = p_id_notizia;
    
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `rimuovi_operatore` (IN `p_nomeutente` VARCHAR(30))   BEGIN
    DELETE FROM utente 
    WHERE nomeutente = p_nomeutente 
    AND ruolo = 'operator';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `toggle_notizia` (IN `p_id` INT(11))   BEGIN
   UPDATE notizie 
    SET attiva = CASE 
        WHEN attiva = 1 THEN 0 
        ELSE 1 
    END
    WHERE id= p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `valida_token` (IN `p_token` VARCHAR(255))   BEGIN
    DECLARE v_pasto_id INT;
    DECLARE v_studente VARCHAR(16);
    DECLARE v_data_scadenza TIMESTAMP;
    DECLARE v_menu_id INT DEFAULT NULL;
    DECLARE v_pranzo_cena TINYINT;
    DECLARE v_ora_corrente TIME;
    
    -- Cerca il token
    SELECT pasto, studente, data_scadenza 
    INTO v_pasto_id, v_studente, v_data_scadenza
    FROM token
    WHERE id = p_token;
    
    -- Se il token non esiste
    IF v_pasto_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Token non valido';
    END IF;
    
    -- Se il token è scaduto
    IF v_data_scadenza < NOW() THEN
        -- Elimina il token scaduto
        DELETE FROM token WHERE id = p_token;
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Token scaduto';
    END IF;
    
    -- Controlla se il pasto è già stato utilizzato
    IF (SELECT dataUtilizzo FROM pasto WHERE id = v_pasto_id) IS NOT NULL THEN
        DELETE FROM token WHERE id = p_token;
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Pasto già utilizzato';
    END IF;
    
    -- Determina se è pranzo (0) o cena (1) in base all'orario
    SET v_ora_corrente = CURRENT_TIME();
    IF v_ora_corrente BETWEEN '11:00:00' AND '15:00:00' THEN
        SET v_pranzo_cena = 0; -- Pranzo
    ELSEIF v_ora_corrente BETWEEN '18:00:00' AND '22:00:00' THEN
        SET v_pranzo_cena = 1; -- Cena
    END IF;
    
    -- Cerca il menu del giorno (se esiste)
    SELECT menuID INTO v_menu_id
    FROM menudelgiorno
    WHERE DATE(data) = CURDATE() AND pranzo_cena = v_pranzo_cena
    LIMIT 1;
    
    -- Aggiorna il pasto con data utilizzo e menu
    UPDATE pasto
    SET dataUtilizzo = CURDATE(),
        menuID = v_menu_id
    WHERE id = v_pasto_id;
    
    -- Elimina il token usato
    DELETE FROM token WHERE id = p_token;
    
    -- Restituisci successo con info
    SELECT 
        'OK' AS esito,
        v_studente AS studente,
        v_pasto_id AS pasto,
        CASE v_pranzo_cena WHEN 0 THEN 'Pranzo' ELSE 'Cena' END AS tipo_pasto,
        v_menu_id AS menu_id,
        (SELECT CONCAT(nome, ' ', cognome) FROM info_studente WHERE cf = v_studente) AS nome_studente;
        
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `allergene`
--

CREATE TABLE `allergene` (
  `id` int(11) NOT NULL,
  `nome` varchar(20) NOT NULL,
  `descrizione` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `allergene`
--

INSERT INTO `allergene` (`id`, `nome`, `descrizione`) VALUES
(1, 'Glutine', 'Cereali contenenti glutine: grano, segale, orzo, avena, farro'),
(2, 'Lattosio', 'Latte e prodotti derivati, incluso il lattosio'),
(3, 'Uova', 'Uova e prodotti derivati'),
(4, 'Pesce', 'Pesce e prodotti derivati'),
(5, 'Arachidi', 'Arachidi e prodotti derivati'),
(6, 'Soia', 'Soia e prodotti derivati'),
(7, 'Frutta a guscio', 'Mandorle, nocciole, noci, pistacchi, anacardi'),
(8, 'Sedano', 'Sedano e prodotti derivati'),
(9, 'Senape', 'Senape e prodotti derivati'),
(10, 'Sesamo', 'Semi di sesamo e prodotti derivati'),
(11, 'Crostacei', 'Crostacei e prodotti derivati'),
(12, 'Molluschi', 'Molluschi e prodotti derivati'),
(13, 'Lupini', 'Lupini e prodotti derivati'),
(14, 'Solfiti', 'Anidride solforosa e solfiti in concentrazione superiore a 10mg/kg');

-- --------------------------------------------------------

--
-- Struttura della tabella `info_studente`
--

CREATE TABLE `info_studente` (
  `cf` varchar(16) NOT NULL,
  `nomeutente` varchar(30) NOT NULL,
  `nome` varchar(30) NOT NULL,
  `cognome` varchar(30) NOT NULL,
  `sesso` enum('M','F','A') NOT NULL,
  `datanascita` date NOT NULL,
  `indirizzo` varchar(80) NOT NULL,
  `citta` varchar(30) NOT NULL,
  `fascia` int(11) NOT NULL,
  `pasti` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `info_studente`
--

INSERT INTO `info_studente` (`cf`, `nomeutente`, `nome`, `cognome`, `sesso`, `datanascita`, `indirizzo`, `citta`, `fascia`, `pasti`) VALUES
('MRRFNC99A06C800I', 'Francesco', 'Francesco', 'Marrone', 'M', '1999-01-06', 'via principessa maria', 'Sassari', 3, 0),
('mrrss243', 'studente', 'Mario', 'Rossi', 'M', '2016-01-08', 'via brombeis 12', 'Napoli', 3, 0),
('qwerty', 'genny', 'Gennaro', 'Esposito', 'M', '2025-06-11', 'via napoli 11', 'Napoli', 1, 0),
('SMLDNL03T15I452R', 'Daniele', 'Daniele', 'Simula', 'M', '2003-12-15', 'VIA COMEILVENTO 67', 'OSSI', 2, 0),
('zzevni333', 'ezIvan', 'Ivan', 'Ezza', 'M', '2003-01-08', 'via genova 12', 'Alessandria', 2, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `nome` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `menu`
--

INSERT INTO `menu` (`id`, `nome`) VALUES
(1, 'Menu Classico'),
(2, 'Menu Leggero'),
(3, 'Menu Pesce');

-- --------------------------------------------------------

--
-- Struttura della tabella `menudelgiorno`
--

CREATE TABLE `menudelgiorno` (
  `data` timestamp NOT NULL DEFAULT current_timestamp(),
  `pranzo_cena` tinyint(1) NOT NULL,
  `menuID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `notizie`
--

CREATE TABLE `notizie` (
  `id` int(11) NOT NULL,
  `titolo` varchar(255) NOT NULL,
  `descrizione` varchar(500) DEFAULT NULL,
  `contenuto` text NOT NULL,
  `immagine` varchar(255) DEFAULT NULL,
  `dataPubblicazione` timestamp NOT NULL DEFAULT current_timestamp(),
  `autore` varchar(30) DEFAULT NULL,
  `attiva` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `notizie`
--

INSERT INTO `notizie` (`id`, `titolo`, `descrizione`, `contenuto`, `immagine`, `dataPubblicazione`, `autore`, `attiva`) VALUES
(2, 'Notizia esempio', 'Descrizione di esempio', '<b>Lorem ipsum dolor<\\b> sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.', 'risorse/notizie/servizio.jpg', '2026-01-20 14:31:15', 'operatore', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `pasto`
--

CREATE TABLE `pasto` (
  `id` int(11) NOT NULL,
  `dataAcquisto` timestamp NOT NULL DEFAULT current_timestamp(),
  `dataUtilizzo` date DEFAULT NULL,
  `studente` varchar(16) NOT NULL,
  `menuID` int(11) DEFAULT NULL,
  `fascia` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Trigger `pasto`
--
DELIMITER $$
CREATE TRIGGER `decrementa_pasti` AFTER UPDATE ON `pasto` FOR EACH ROW BEGIN
	IF new.dataUtilizzo IS NOT NULL THEN
		UPDATE info_studente SET pasti = pasti-1
		WHERE cf = new.studente;
	END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `decrementa_pasti_cancellazione` AFTER DELETE ON `pasto` FOR EACH ROW BEGIN
   IF OLD.dataUtilizzo IS NULL THEN
        UPDATE info_studente 
        SET pasti = pasti - 1
        WHERE cf = OLD.studente;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `incrementa_pasti` AFTER INSERT ON `pasto` FOR EACH ROW BEGIN
    UPDATE info_studente 
    SET pasti = pasti + 1
    WHERE cf = NEW.studente;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `piatti_allergeni`
--

CREATE TABLE `piatti_allergeni` (
  `id` int(11) NOT NULL,
  `id_piatto` int(11) NOT NULL,
  `id_allergene` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `piatti_allergeni`
--

INSERT INTO `piatti_allergeni` (`id`, `id_piatto`, `id_allergene`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 1),
(4, 3, 2),
(5, 3, 3),
(6, 4, 1),
(7, 4, 2),
(8, 4, 7),
(9, 5, 8),
(10, 6, 1),
(11, 6, 2),
(12, 7, 8),
(13, 8, 2),
(14, 9, 1),
(15, 9, 2),
(16, 9, 3),
(17, 10, 1),
(18, 10, 3),
(19, 12, 1),
(20, 12, 3),
(21, 13, 4),
(22, 14, 3),
(23, 16, 1),
(24, 16, 3),
(25, 18, 4),
(26, 18, 2),
(27, 18, 1),
(28, 19, 1),
(29, 20, 3),
(30, 27, 2),
(31, 33, 2),
(32, 34, 2),
(33, 34, 3);

-- --------------------------------------------------------

--
-- Struttura della tabella `piatti_menu`
--

CREATE TABLE `piatti_menu` (
  `id` int(11) NOT NULL,
  `id_menu` int(11) NOT NULL,
  `id_piatto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `piatti_menu`
--

INSERT INTO `piatti_menu` (`id`, `id_menu`, `id_piatto`) VALUES
(1, 1, 1),
(2, 1, 3),
(3, 1, 11),
(4, 1, 16),
(5, 1, 21),
(6, 1, 22),
(7, 1, 29),
(8, 1, 30),
(9, 2, 5),
(10, 2, 6),
(11, 2, 17),
(12, 2, 14),
(13, 2, 23),
(14, 2, 24),
(15, 2, 31),
(16, 2, 33),
(17, 3, 2),
(18, 3, 7),
(19, 3, 13),
(20, 3, 18),
(21, 3, 25),
(22, 3, 26),
(23, 3, 32),
(24, 3, 34);

-- --------------------------------------------------------

--
-- Struttura della tabella `piatto`
--

CREATE TABLE `piatto` (
  `id` int(11) NOT NULL,
  `nome` varchar(20) NOT NULL,
  `descrizione` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `piatto`
--

INSERT INTO `piatto` (`id`, `nome`, `descrizione`) VALUES
(1, 'Pasta al pomodoro', 'Pasta con salsa di pomodoro fresco e basilico'),
(2, 'Risotto ai funghi', 'Risotto mantecato con funghi porcini'),
(3, 'Lasagne alla bologne', 'Lasagne con ragù di carne e besciamella'),
(4, 'Pasta al pesto', 'Pasta con pesto genovese'),
(5, 'Minestrone', 'Zuppa di verdure miste di stagione'),
(6, 'Pasta in bianco', 'Pasta condita con olio e parmigiano'),
(7, 'Zuppa di legumi', 'Zuppa con fagioli, ceci e lenticchie'),
(8, 'Risotto alla milanes', 'Risotto allo zafferano'),
(9, 'Pasta carbonara', 'Pasta con uova, guanciale e pecorino'),
(10, 'Gnocchi al ragù', 'Gnocchi di patate con ragù di carne'),
(11, 'Pollo arrosto', 'Pollo arrosto con erbe aromatiche'),
(12, 'Cotoletta di pollo', 'Cotoletta di pollo impanata'),
(13, 'Merluzzo al forno', 'Filetto di merluzzo al forno con patate'),
(14, 'Frittata di verdure', 'Frittata con zucchine e cipolle'),
(15, 'Arrosto di maiale', 'Arrosto di maiale con rosmarino'),
(16, 'Polpette al sugo', 'Polpette di carne in salsa di pomodoro'),
(17, 'Petto di tacchino', 'Petto di tacchino alla griglia'),
(18, 'Sogliola alla mugnai', 'Sogliola in padella con burro e limone'),
(19, 'Hamburger di manzo', 'Hamburger di manzo alla piastra'),
(20, 'Uova sode', 'Uova sode con maionese'),
(21, 'Insalata mista', 'Insalata con lattuga, pomodori e carote'),
(22, 'Patate al forno', 'Patate arrosto con rosmarino'),
(23, 'Verdure grigliate', 'Zucchine, melanzane e peperoni grigliati'),
(24, 'Spinaci saltati', 'Spinaci saltati in padella con aglio'),
(25, 'Carote lesse', 'Carote lessate con prezzemolo'),
(26, 'Fagiolini', 'Fagiolini al vapore conditi con olio'),
(27, 'Puré di patate', 'Puré di patate con burro'),
(28, 'Insalata di riso', 'Riso freddo con verdure'),
(29, 'Mela', 'Mela fresca di stagione'),
(30, 'Banana', 'Banana fresca'),
(31, 'Arancia', 'Arancia fresca'),
(32, 'Frutta di stagione', 'Frutta mista di stagione'),
(33, 'Yogurt', 'Yogurt bianco o alla frutta'),
(34, 'Budino', 'Budino alla vaniglia o al cioccolato'),
(36, 'testpiatto', 'aaa');

-- --------------------------------------------------------

--
-- Struttura della tabella `ruolo`
--

CREATE TABLE `ruolo` (
  `ruolo` varchar(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `ruolo`
--

INSERT INTO `ruolo` (`ruolo`) VALUES
('admin'),
('operator'),
('studente');

-- --------------------------------------------------------

--
-- Struttura della tabella `tariffa`
--

CREATE TABLE `tariffa` (
  `fascia` int(11) NOT NULL,
  `costo` float(3,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `tariffa`
--

INSERT INTO `tariffa` (`fascia`, `costo`) VALUES
(1, 2.30),
(2, 4.00),
(3, 5.50);

-- --------------------------------------------------------

--
-- Struttura della tabella `token`
--

CREATE TABLE `token` (
  `id` varchar(255) NOT NULL,
  `studente` varchar(16) NOT NULL,
  `pasto` int(11) NOT NULL,
  `data_scadenza` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `utente`
--

CREATE TABLE `utente` (
  `nomeutente` varchar(30) NOT NULL,
  `email` varchar(30) NOT NULL,
  `passwordhash` varchar(255) NOT NULL,
  `ruolo` varchar(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `utente`
--

INSERT INTO `utente` (`nomeutente`, `email`, `passwordhash`, `ruolo`) VALUES
('admin', 'admin@mensachevorrei.it', '$2y$10$Rum2A5TJ6EvONht1i26Tg.KtEcXs6lk79.AzfMKO5mqqckD.MLSFO', 'admin'),
('Daniele', 'Daniele@mensachevorrei.it', '$2y$10$dm6PPAyBCS8nztoz.eUhSuu.4tNGUJj/SIKh8rg.CERHpQU4UoZBm', 'studente'),
('ezIvan', 'ivan@ivan', '$2y$10$r3gsygQIejz./nd48X4H2..sAWtzP1LDwcju5y6t3uKg3oq5JGvXW', 'studente'),
('Francesco', 'F.Marrone@mensachevorrei.it', '$2y$10$v/d.yMv6k3AxYPqrpC6BbOaayQ92ANBrKoYOSmr676sN3m9rMH5fO', 'studente'),
('genny', 'gennaro@mensachevorrei.it', '$2y$10$o7hsn9dfia8C4jJrYp9sAOYJcGp9qX51MYMJufhdPx0vHU.1VfOYK', 'studente'),
('operatore', 'operatore@mensachevorrei.it', '$2y$10$jzSefJwEQP7/pPGmtg3YTu4Pixms0Z98l09.dZo0bXVfLdOT8I4zO', 'operator'),
('studente', 'studente@mensachevorrei.it', '$2y$10$cQinNX7EpB4P5TWI253If.vVBHVE1BuvPVhkxanVOnE5xSnOcc9IW', 'studente');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `allergene`
--
ALTER TABLE `allergene`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `info_studente`
--
ALTER TABLE `info_studente`
  ADD PRIMARY KEY (`cf`),
  ADD KEY `nomeutente` (`nomeutente`),
  ADD KEY `fascia` (`fascia`);

--
-- Indici per le tabelle `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `menudelgiorno`
--
ALTER TABLE `menudelgiorno`
  ADD PRIMARY KEY (`data`,`pranzo_cena`),
  ADD KEY `menuID` (`menuID`);

--
-- Indici per le tabelle `notizie`
--
ALTER TABLE `notizie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `autore` (`autore`);

--
-- Indici per le tabelle `pasto`
--
ALTER TABLE `pasto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menuID` (`menuID`),
  ADD KEY `tariffa` (`fascia`),
  ADD KEY `studente` (`studente`);

--
-- Indici per le tabelle `piatti_allergeni`
--
ALTER TABLE `piatti_allergeni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_piatto` (`id_piatto`),
  ADD KEY `id_allergene` (`id_allergene`);

--
-- Indici per le tabelle `piatti_menu`
--
ALTER TABLE `piatti_menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_menu` (`id_menu`),
  ADD KEY `id_piatto` (`id_piatto`);

--
-- Indici per le tabelle `piatto`
--
ALTER TABLE `piatto`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `ruolo`
--
ALTER TABLE `ruolo`
  ADD PRIMARY KEY (`ruolo`);

--
-- Indici per le tabelle `tariffa`
--
ALTER TABLE `tariffa`
  ADD PRIMARY KEY (`fascia`);

--
-- Indici per le tabelle `token`
--
ALTER TABLE `token`
  ADD PRIMARY KEY (`id`),
  ADD KEY `studente` (`studente`),
  ADD KEY `pasto` (`pasto`);

--
-- Indici per le tabelle `utente`
--
ALTER TABLE `utente`
  ADD PRIMARY KEY (`nomeutente`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `ruolo` (`ruolo`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `allergene`
--
ALTER TABLE `allergene`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT per la tabella `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT per la tabella `notizie`
--
ALTER TABLE `notizie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT per la tabella `pasto`
--
ALTER TABLE `pasto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT per la tabella `piatti_allergeni`
--
ALTER TABLE `piatti_allergeni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT per la tabella `piatti_menu`
--
ALTER TABLE `piatti_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT per la tabella `piatto`
--
ALTER TABLE `piatto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `info_studente`
--
ALTER TABLE `info_studente`
  ADD CONSTRAINT `info_studente_ibfk_1` FOREIGN KEY (`nomeutente`) REFERENCES `utente` (`nomeutente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `info_studente_ibfk_2` FOREIGN KEY (`fascia`) REFERENCES `tariffa` (`fascia`) ON UPDATE CASCADE;

--
-- Limiti per la tabella `menudelgiorno`
--
ALTER TABLE `menudelgiorno`
  ADD CONSTRAINT `menudelgiorno_ibfk_1` FOREIGN KEY (`menuID`) REFERENCES `menu` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `notizie`
--
ALTER TABLE `notizie`
  ADD CONSTRAINT `notizie_ibfk_1` FOREIGN KEY (`autore`) REFERENCES `utente` (`nomeutente`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limiti per la tabella `pasto`
--
ALTER TABLE `pasto`
  ADD CONSTRAINT `pasto_ibfk_1` FOREIGN KEY (`menuID`) REFERENCES `menu` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `pasto_ibfk_2` FOREIGN KEY (`fascia`) REFERENCES `tariffa` (`fascia`) ON UPDATE CASCADE,
  ADD CONSTRAINT `studente` FOREIGN KEY (`studente`) REFERENCES `info_studente` (`cf`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `piatti_allergeni`
--
ALTER TABLE `piatti_allergeni`
  ADD CONSTRAINT `piatti_allergeni_ibfk_1` FOREIGN KEY (`id_piatto`) REFERENCES `piatto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `piatti_allergeni_ibfk_2` FOREIGN KEY (`id_allergene`) REFERENCES `allergene` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `piatti_menu`
--
ALTER TABLE `piatti_menu`
  ADD CONSTRAINT `piatti_menu_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `piatti_menu_ibfk_2` FOREIGN KEY (`id_piatto`) REFERENCES `piatto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `token`
--
ALTER TABLE `token`
  ADD CONSTRAINT `token_ibfk_1` FOREIGN KEY (`studente`) REFERENCES `info_studente` (`cf`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `token_ibfk_2` FOREIGN KEY (`pasto`) REFERENCES `pasto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `utente`
--
ALTER TABLE `utente`
  ADD CONSTRAINT `utente_ibfk_1` FOREIGN KEY (`ruolo`) REFERENCES `ruolo` (`ruolo`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
