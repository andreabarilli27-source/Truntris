create database if not exists my_truntris;
use my_truntris;

CREATE TABLE `Users` (
  `ID` int PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `nome` varchar(255) NOT NULL,
  `cognome` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `data_nascita` date NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL
);

INSERT INTO `Users` (`id`, `nome`, `cognome`, `username`, `data_nascita`, `password`, `email`) VALUES
(1, 'Andrea', 'Barilli', 'barillia', '2006-10-27', '$2y$10$ZzDGIwAXbeUWgZHHaKt3pOCVZ8Jmp6DEmOFAOGx6JruH8Q4I1mt.O', 'andreabarilli27@gmai.com');

CREATE TABLE `Predefiniti` (
  `ID` int AUTO_INCREMENT PRIMARY KEY NOT NULL,
  `nome` varchar(255) NOT NULL,
  `bagagliaglio` varchar(255) NOT NULL,
  `descrizione` varchar(255) NOT NULL,
  `Valigie` text NOT NULL,
  `posizioni` text NOT NULL,
  `data_creazione` timestamp DEFAULT CURRENT_TIMESTAMP,
  `ultima_modifica` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_utente` int NOT NULL,
  CONSTRAINT `fk_predefiniti_users` 
    FOREIGN KEY (`id_utente`) 
    REFERENCES `Users`(`ID`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
);
