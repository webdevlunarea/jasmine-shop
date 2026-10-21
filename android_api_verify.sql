SELECT
  expected.table_name,
  CASE WHEN actual.TABLE_NAME IS NULL THEN 'BELUM ADA' ELSE 'SUDAH ADA' END AS status,
  actual.ENGINE AS engine
FROM (
  SELECT 'customer_api_tokens' AS table_name
  UNION ALL SELECT 'customer_push_devices'
  UNION ALL SELECT 'customer_push_notifications'
  UNION ALL SELECT 'customer_checkout_requests'
  UNION ALL SELECT 'migrations'
) AS expected
LEFT JOIN information_schema.TABLES AS actual
  ON actual.TABLE_SCHEMA = DATABASE()
 AND actual.TABLE_NAME = expected.table_name
ORDER BY expected.table_name;
