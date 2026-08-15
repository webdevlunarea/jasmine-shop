INSERT INTO `konstanta` (`label`, `value`)
SELECT 'theme_warna_primary', '#243B6B'
WHERE NOT EXISTS (SELECT 1 FROM `konstanta` WHERE `label` = 'theme_warna_primary');

INSERT INTO `konstanta` (`label`, `value`)
SELECT 'theme_warna_primaryHover', '#1A2C52'
WHERE NOT EXISTS (SELECT 1 FROM `konstanta` WHERE `label` = 'theme_warna_primaryHover');

INSERT INTO `konstanta` (`label`, `value`)
SELECT 'theme_warna_soft', '#FAF7F2'
WHERE NOT EXISTS (SELECT 1 FROM `konstanta` WHERE `label` = 'theme_warna_soft');

INSERT INTO `konstanta` (`label`, `value`)
SELECT 'theme_warna_soft1', '#F3E8DF'
WHERE NOT EXISTS (SELECT 1 FROM `konstanta` WHERE `label` = 'theme_warna_soft1');

INSERT INTO `konstanta` (`label`, `value`)
SELECT 'theme_warna_soft2', '#E7D7C9'
WHERE NOT EXISTS (SELECT 1 FROM `konstanta` WHERE `label` = 'theme_warna_soft2');

INSERT INTO `konstanta` (`label`, `value`)
SELECT 'theme_warna_accent', '#D98C9A'
WHERE NOT EXISTS (SELECT 1 FROM `konstanta` WHERE `label` = 'theme_warna_accent');

INSERT INTO `konstanta` (`label`, `value`)
SELECT 'theme_warna_background', '#F7F0E8'
WHERE NOT EXISTS (SELECT 1 FROM `konstanta` WHERE `label` = 'theme_warna_background');

INSERT INTO `konstanta` (`label`, `value`)
SELECT 'theme_warna_sidebar', '#1F2A44'
WHERE NOT EXISTS (SELECT 1 FROM `konstanta` WHERE `label` = 'theme_warna_sidebar');
