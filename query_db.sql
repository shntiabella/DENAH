SHOW TABLES LIKE '%settlement%';
SHOW TABLES LIKE '%administrative%';
SHOW TABLES LIKE '%stat_permukiman%';

SELECT "=== cache keys (stats/filter) ===" AS '';
SELECT `key`, LENGTH(`value`) AS vlen, `expiration` FROM cache WHERE `key` LIKE '%stats%' OR `key` LIKE '%filter%' OR `key` LIKE '%available%' OR `key` LIKE '%province%' ORDER BY `key`;

SELECT "=== cache total ===" AS '';
SELECT COUNT(*) total FROM cache;

SELECT "=== administrative_areas kabupaten ===" AS '';
SELECT id, code, name, level, parent_id FROM administrative_areas WHERE level='kabupaten' ORDER BY name;

SELECT "=== administrative_areas kecamatan count ===" AS '';
SELECT COUNT(*) c FROM administrative_areas WHERE level='kecamatan';

SELECT "=== administrative_areas desa count ===" AS '';
SELECT COUNT(*) c FROM administrative_areas WHERE level='desa';

SELECT "=== stat_permukiman ===" AS '';
SELECT year, regency, district, village, luas_ha, jumlah_lokasi, jumlah_records FROM stat_permukiman ORDER BY year LIMIT 15;

SELECT "=== stat_permukiman total rows ===" AS '';
SELECT COUNT(*) c FROM stat_permukiman;

SELECT "=== distinct regency/district/village counts in settlement_areas ===" AS '';
SELECT COUNT(DISTINCT regency) rc, COUNT(DISTINCT district) dc, COUNT(DISTINCT village) vc FROM settlement_areas WHERE status='published';

