-- Migration V1 : Améliorations Client/Fournisseur
-- Date : 2026-03-03

ALTER TABLE clients
    ADD COLUMN payment_delay INT DEFAULT 30 COMMENT 'Délai de paiement en jours';

ALTER TABLE fournisseurs
    ADD COLUMN email VARCHAR(100) NULL,
    ADD COLUMN telephone VARCHAR(20) NULL;
