# Account deletion release steps

## User-facing entry points

- Android: Account → Security → Request account deletion.
- Public Play Console resource: `https://lunareafurniture.com/hapus-akun`.
- Admin queue: `/customeradmin` → Account deletion requests.

## Production setup

1. Back up the production database.
2. Run `docs/sql/customer_account_deletion_requests.sql` in phpMyAdmin.
3. Deploy the backend and Android build containing the request action.
4. Add this daily job to `crontab -u nobody -e` to purge completed request records 180 days after completion:

   ```cron
   17 3 * * * cd /mine/lunareafurniture.com/jasmine-shop && /usr/local/lsws/lsphp83/bin/php spark privacy:purge-deletion-requests >> writable/logs/privacy-purge.log 2>&1
   ```

5. Set the Play Console account-deletion URL to `https://lunareafurniture.com/hapus-akun`.

## Request handling

The application stores an authenticated request, deactivates the customer's push devices, and revokes their known sessions. An administrator must review the request and perform deletion or de-identification before marking it complete. Preserve transaction records only where required for legal, accounting, security, dispute, or reconciliation purposes; do not use the expired-unverified-account cleanup on an account with transaction history.

The request queue itself contains the account email and is purged 180 days after an administrator marks it complete. The public page states a 30-day maximum processing target; the business must ensure the queue is reviewed within that period.
