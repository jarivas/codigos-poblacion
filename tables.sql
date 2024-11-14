-- municipio definition
DROP TABLE IF EXISTS municipio;

CREATE TABLE "municipio" (
	"id"	INTEGER,
	"codigo"	TEXT NOT NULL,
	"codigo_provincia"	TEXT NOT NULL,
	"codigo_postal"	TEXT NOT NULL,
	"nombre"	TEXT NOT NULL,
	"fullText"	TEXT NOT NULL,
	PRIMARY KEY("id" AUTOINCREMENT)
);

-- provincia definition

DROP TABLE IF EXISTS provincia;

CREATE TABLE "provincia" (
	"id"	INTEGER NOT NULL,
	"codigo"	TEXT NOT NULL,
	"nombre"	TEXT NOT NULL,
	"fullText"	TEXT NOT NULL,
	PRIMARY KEY("id")
);

CREATE INDEX provincia_codigo_IDX ON provincia (codigo);