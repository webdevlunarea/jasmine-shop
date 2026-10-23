package com.lunareafurniture.lunarea.notifications

import android.Manifest
import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Build
import androidx.core.app.NotificationCompat
import androidx.core.app.NotificationManagerCompat
import androidx.core.content.ContextCompat
import androidx.core.graphics.drawable.toBitmap
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage
import com.lunareafurniture.lunarea.MainActivity
import com.lunareafurniture.lunarea.data.security.SecureTokenStore

class LunareaMessagingService : FirebaseMessagingService() {
    override fun onNewToken(token: String) {
        SecureTokenStore(this).savePushToken(token)
        PushDeviceRegistration(this).markUnregistered()
        PushDeviceRegistration.enqueueRetry(this)
    }

    override fun onMessageReceived(message: RemoteMessage) {
        val title = message.notification?.title ?: message.data["title"] ?: "Pembaruan Lunarea"
        val body = message.notification?.body ?: message.data["body"] ?: return
        val imageUrl = message.notification?.imageUrl?.toString() ?: message.data["image_url"]
        showNotification(title, body, message.data, imageUrl)
    }

    private fun showNotification(title: String, body: String, data: Map<String, String>, imageUrl: String?) {
        // Do not surface account-specific updates after the user has signed out.
        val optedIn = getSharedPreferences("lunarea_notification_settings", MODE_PRIVATE)
            .getBoolean("push_opt_in", false)
        if (!optedIn || SecureTokenStore(this).read() == null) return
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU &&
            ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED
        ) return

        val manager = getSystemService(NotificationManager::class.java)
        val type = data["type"].orEmpty().lowercase()
        val channelId = channelFor(type)
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O && manager.getNotificationChannel(channelId) == null) {
            manager.createNotificationChannel(
                NotificationChannel(channelId, channelNameFor(channelId), importanceFor(channelId))
            )
        }

        val tapIntent = Intent(this, MainActivity::class.java)
            .addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP)
        // Preserve only routing metadata; never put credentials or personal data in an intent.
        listOf("type", "order_id", "product_id", "variant", "campaign_id").forEach { key ->
            data[key]?.takeIf(String::isNotBlank)?.let { tapIntent.putExtra("push_$key", it) }
        }
        val openApp = PendingIntent.getActivity(
            this,
            (data["campaign_id"] ?: data["order_id"] ?: data["product_id"] ?: System.currentTimeMillis().toString()).hashCode(),
            tapIntent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )
        val notification = NotificationCompat.Builder(this, channelId)
            .setSmallIcon(com.lunareafurniture.lunarea.R.drawable.ic_notification_small)
            .setLargeIcon(ContextCompat.getDrawable(this, com.lunareafurniture.lunarea.R.drawable.lunarea_splash_mark_legacy)?.toBitmap(96, 96))
            .setContentTitle(title)
            .setContentText(body)
            .setStyle(NotificationCompat.BigTextStyle().bigText(body))
            .setContentIntent(openApp)
            .setVisibility(NotificationCompat.VISIBILITY_PRIVATE)
            .setPublicVersion(
                NotificationCompat.Builder(this, channelId)
                    .setSmallIcon(com.lunareafurniture.lunarea.R.drawable.ic_notification_small)
                    .setContentTitle("Pembaruan Lunarea")
                    .setContentText("Buka aplikasi untuk melihat informasi terbaru.")
                    .setVisibility(NotificationCompat.VISIBILITY_PUBLIC)
                    .build()
            )
            .setGroup(groupKeyFor(channelId))
            .setAutoCancel(true)
            .build()
        val notificationKey = data["campaign_id"] ?: data["order_id"] ?: data["product_id"] ?: System.currentTimeMillis().toString()
        val notificationId = notificationKey.hashCode().let { if (it == 0) 1 else it }
        val notifications = NotificationManagerCompat.from(this)
        notifications.notify(notificationId, notification)
        updateGroupSummary(manager, notifications, channelId)
        if (type.startsWith("broadcast_") && !imageUrl.isNullOrBlank()) {
            NotificationImageWorker.enqueue(this, title, body, imageUrl, data, notificationId, channelId)
        }
    }

    private fun channelFor(type: String): String = when {
        type.startsWith("broadcast_") || type == "back_in_stock" -> PROMOTIONS_CHANNEL_ID
        type in setOf("admin_test", "account_security", "account_update") -> ACCOUNT_CHANNEL_ID
        else -> CHANNEL_ID
    }

    private fun channelNameFor(channelId: String): String = when (channelId) {
        PROMOTIONS_CHANNEL_ID -> "Promo dan produk"
        ACCOUNT_CHANNEL_ID -> "Akun dan keamanan"
        else -> "Pembaruan pesanan"
    }

    private fun importanceFor(channelId: String): Int =
        if (channelId == PROMOTIONS_CHANNEL_ID) NotificationManager.IMPORTANCE_LOW else NotificationManager.IMPORTANCE_DEFAULT

    private fun groupKeyFor(channelId: String): String = "lunarea_group_$channelId"

    private fun updateGroupSummary(
        manager: NotificationManager,
        notifications: NotificationManagerCompat,
        channelId: String
    ) {
        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.M) return
        val groupKey = groupKeyFor(channelId)
        val children = manager.activeNotifications.count {
            it.notification.group == groupKey &&
                (it.notification.flags and android.app.Notification.FLAG_GROUP_SUMMARY) == 0
        }
        val summaryId = groupKey.hashCode().let { if (it == 0) Int.MIN_VALUE else it }
        if (children < 2) {
            notifications.cancel(summaryId)
            return
        }
        val inboxIntent = Intent(this, MainActivity::class.java)
            .addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP)
            .putExtra("push_type", "notification_inbox")
        val inboxPendingIntent = PendingIntent.getActivity(
            this,
            summaryId,
            inboxIntent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )
        val summary = NotificationCompat.Builder(this, channelId)
            .setSmallIcon(com.lunareafurniture.lunarea.R.drawable.ic_notification_small)
            .setContentTitle("Pembaruan Lunarea")
            .setContentText("$children notifikasi baru")
            .setStyle(NotificationCompat.InboxStyle().setSummaryText("Ketuk untuk membuka kotak masuk"))
            .setContentIntent(inboxPendingIntent)
            .setGroup(groupKey)
            .setGroupSummary(true)
            .setVisibility(NotificationCompat.VISIBILITY_PRIVATE)
            .setPublicVersion(
                NotificationCompat.Builder(this, channelId)
                    .setSmallIcon(com.lunareafurniture.lunarea.R.drawable.ic_notification_small)
                    .setContentTitle("Pembaruan Lunarea")
                    .setContentText("Buka aplikasi untuk melihat informasi terbaru.")
                    .setVisibility(NotificationCompat.VISIBILITY_PUBLIC)
                    .build()
            )
            .setAutoCancel(true)
            .build()
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED) return
        notifications.notify(summaryId, summary)
    }

    companion object {
        const val CHANNEL_ID = "lunarea_order_updates"
        const val PROMOTIONS_CHANNEL_ID = "lunarea_promotions_v1"
        const val ACCOUNT_CHANNEL_ID = "lunarea_account_v1"
    }
}
