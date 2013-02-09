SET NAMES UTF8;


CREATE TABLE IF NOT EXISTS material
(
	id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255),
	description TEXT
);

CREATE TABLE IF NOT EXISTS product_to_material
(
	prodid INTEGER NOT NULL,
	matid INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS product
(
	id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255),
	price INTEGER,
	worktime DATETIME,
	description TEXT
);

CREATE TABLE IF NOT EXISTS product_images
(
	prodid INTEGER NOT NULL,
	path VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS product_to_master
(
	prodid INTEGER NOT NULL,
	mastid INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS master
(
	id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
	login VARCHAR(255),
	password VARCHAR(255),
	description TEXT
);

