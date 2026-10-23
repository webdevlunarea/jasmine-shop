# Marketing Broadcast: setup and operations

## Safe rollout

1. Back up the production database.
2. Deploy the application files.
3. Apply the CodeIgniter migration from the project root using the same PHP binary and service account as production:

   ```sh
   cd /mine/lunareafurniture.com/jasmine-shop
   runuser -u nobody -- /usr/local/lsws/lsphp83/bin/php spark migrate
   ```

   This creates email unsubscribe preferences, the durable voucher email outbox, and the atomic global campaign guard. Existing explicit negative email choices from the former opt-in setting are preserved as unsubscribes; accounts without a saved preference remain subscribed until they unsubscribe.

4. In `crontab -u nobody -e`, replace the existing `notifications:dispatch` line with the locked version below (do not keep both), and add the email worker line. The email worker is deliberately capped at two messages per run:

   ```cron
   * * * * * cd /mine/lunareafurniture.com/jasmine-shop && /usr/bin/flock -n writable/notification-dispatch.lock /usr/local/lsws/lsphp83/bin/php spark notifications:dispatch --limit 100 >> writable/logs/notifications-dispatch.log 2>&1
   */5 * * * * cd /mine/lunareafurniture.com/jasmine-shop && /usr/bin/flock -n writable/voucher-marketing-email.lock /usr/local/lsws/lsphp83/bin/php spark marketing:voucher-email-dispatch --limit 2 >> writable/logs/voucher-marketing-email.log 2>&1
   ```

   Keep the existing `notifications:dispatch` job for FCM. The email worker and FCM worker share the 24-hour per-customer promotion cap.

## Verify without sending a campaign

```sh
cd /mine/lunareafurniture.com/jasmine-shop
runuser -u nobody -- /usr/local/lsws/lsphp83/bin/php spark list | grep marketing
runuser -u nobody -- test -w writable/logs && echo LOG_DIR_WRITABLE
crontab -u nobody -l | grep voucher-marketing-email
```

Monitor `writable/logs/voucher-marketing-email.log`. The dispatch command sends real email; do not run it manually as a smoke test while a live campaign is queued.

## Safeguards

- Email voucher broadcast is queued for active customer accounts listed on that voucher, except accounts that explicitly unsubscribed.
- Promotional FCM requires an active Android device registration, which is created only when notification permission is enabled in the app/device. There is no extra promotional opt-in toggle.
- A database-atomic global guard allows at most one campaign per minute and five campaigns per UTC day across admin sessions and channels.
- A customer receives no more than one marketing email/FCM promotion in a rolling 24-hour window. Email campaign sends are processed in small retryable batches, capped at 100 total promo emails per rolling 24 hours; repeated voucher campaign clicks are deduplicated. The configured cron processes at most 24 per hour, while the daily cap leaves headroom for transactional email on the existing SMTP account.
- Marketing email includes an unsubscribe confirmation link and account setting. Unsubscribing affects promotional email only, not transactional/security email.
- Monitor campaign counts on `/listvoucher`; the FCM page displays the current eligible device audience.
