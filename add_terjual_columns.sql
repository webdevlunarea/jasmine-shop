-- Migration: Add terjual columns to barang table
-- Run this SQL in phpMyAdmin or MySQL CLI

ALTER TABLE `barang` 
ADD COLUMN `terjual` INT(11) NOT NULL DEFAULT 0 AFTER `stok`,
ADD COLUMN `terjual_custom` INT(11) NOT NULL DEFAULT 0 AFTER `terjual`;
