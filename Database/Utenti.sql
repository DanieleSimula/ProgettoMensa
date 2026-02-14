-- ============================================
-- Script di importazione utenti database
-- Database: mensachevorrei
-- Data creazione: 2026-02-10
-- ============================================


-- Utente guest (senza password)
CREATE USER IF NOT EXISTS 'guest'@'localhost' IDENTIFIED BY '';

-- Utente studente
CREATE USER IF NOT EXISTS 'studente'@'localhost' IDENTIFIED BY 'password';

-- Utente operatore
CREATE USER IF NOT EXISTS 'operatore'@'localhost' IDENTIFIED BY 'password';

-- Utente admin
CREATE USER IF NOT EXISTS 'admin'@'localhost' IDENTIFIED BY 'password';


-- Assegnazione privilegi
-- per comodita grant ALL PRIVILEGES i grant corretti sono elencati nel
-- report
-- ============================================

-- Privilegi per guest
GRANT ALL PRIVILEGES ON mensachevorrei.* TO 'guest'@'localhost';

-- Privilegi per studente
GRANT ALL PRIVILEGES ON mensachevorrei.* TO 'studente'@'localhost';

-- Privilegi per operatore
GRANT ALL PRIVILEGES ON mensachevorrei.* TO 'operatore'@'localhost';

-- Privilegi per admin
GRANT ALL PRIVILEGES ON mensachevorrei.* TO 'admin'@'localhost';

FLUSH PRIVILEGES;
