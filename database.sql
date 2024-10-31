-- Table: tbl_lehrbetrieb
CREATE TABLE tbl_lehrbetrieb (
    id_lehrbetrieb INT PRIMARY KEY AUTO_INCREMENT,
    firma VARCHAR(255),
    strasse VARCHAR(255),
    plz VARCHAR(10),
    ort VARCHAR(255)
);

-- Table: tbl_countries
CREATE TABLE tbl_countries (
    id_countries INT PRIMARY KEY AUTO_INCREMENT,
    country VARCHAR(255)
);

-- Table: tbl_lernende
CREATE TABLE tbl_lernende (
    id_lernende INT PRIMARY KEY AUTO_INCREMENT,
    vorname VARCHAR(255),
    nachname VARCHAR(255),
    strasse VARCHAR(255),
    plz VARCHAR(10),
    ort VARCHAR(255),
    fk_land INT,
    geschlecht CHAR(1),
    telefon VARCHAR(20),
    handy VARCHAR(20),
    email VARCHAR(255),
    email_privat VARCHAR(255),
    birthdate DATE,
    FOREIGN KEY (fk_land) REFERENCES tbl_countries(id_countries)
);
-- Table: tbl_lernende (no ON DELETE CASCADE since it is a core table for individuals)

-- Table: tbl_dozenten
CREATE TABLE tbl_dozenten (
    id_dozent INT PRIMARY KEY AUTO_INCREMENT,
    vorname VARCHAR(255),
    nachname VARCHAR(255),
    strasse VARCHAR(255),
    plz VARCHAR(10),
    ort VARCHAR(255),
    fk_land INT,
    geschlecht CHAR(1),
    telefon VARCHAR(20),
    handy VARCHAR(20),
    email VARCHAR(255),
    birthdate DATE,
    FOREIGN KEY (fk_land) REFERENCES tbl_countries(id_countries)
);
-- Table: tbl_dozenten (no cascade since this table is a core reference for instructors)

-- Table: tbl_lehrbetrieb_lernende
CREATE TABLE tbl_lehrbetrieb_lernende (
    id_lehrbetrieb_lernende INT PRIMARY KEY AUTO_INCREMENT,
    fk_lehrbetrieb INT,
    fk_lernende INT,
    start DATE,
    ende DATE,
    beruf VARCHAR(255),
    FOREIGN KEY (fk_lehrbetrieb) REFERENCES tbl_lehrbetrieb(id_lehrbetrieb) ON DELETE CASCADE,
    FOREIGN KEY (fk_lernende) REFERENCES tbl_lernende(id_lernende) ON DELETE CASCADE
);

-- Table: tbl_kurse
CREATE TABLE tbl_kurse (
    id_kurs INT PRIMARY KEY AUTO_INCREMENT,
    kursnummer VARCHAR(50),
    kursthema VARCHAR(255),
    inhalt TEXT,
    fk_dozent INT,
    startdatum DATE,
    enddatum DATE,
    dauer INT,
    FOREIGN KEY (fk_dozent) REFERENCES tbl_dozenten(id_dozent) ON DELETE SET NULL
);

-- Table: tbl_kurse_lernende
CREATE TABLE tbl_kurse_lernende (
    id_kurs_lernende INT PRIMARY KEY AUTO_INCREMENT,
    fk_lernende INT,
    fk_kurs INT,
    role VARCHAR(255),
    FOREIGN KEY (fk_lernende) REFERENCES tbl_lernende(id_lernende) ON DELETE CASCADE,
    FOREIGN KEY (fk_kurs) REFERENCES tbl_kurse(id_kurs) ON DELETE CASCADE
);
