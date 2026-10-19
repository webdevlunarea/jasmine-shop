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
        showNotification(title, body, message.data)
    }

    private fun showNotification(title: String, body: String, data: Map<String, String>) {
        // Do not surface account-specific updates after the user has signed out.
        val optedIn = getSharedPreferences("lunarea_notification_settings", MODE_PRIVATE)
            .getBoolean("push_opt_in", false)
        if (!optedIn || SecureTokenStore(this).read() == null) return
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU &&
            ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED
        ) return

        val manager = getSystemService(NotificationManager::class.java)
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            manager.createNotificationChannel(
                NotificationChannel(CHANNEL_ID, "Pembaruan pesanan", NotificationManager.IMPORTANCE_DEFAULT)
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
        val notification = NotificationCompat.Builder(this, CHANNEL_ID)
            .setSmallIcon(com.lunareafurniture.lunarea.R.drawable.ic_notification_small)
            .setContentTitle(title)
            .setContentText(body)
            .setStyle(NotificationCompat.BigTextStyle().bigText(body))
            .setContentIntent(openApp)
            .setAutoCancel(true)
            .build()
        val notificationId = (data["campaign_id"] ?: data["order_id"] ?: data["product_id"] ?: System.currentTimeMillis().toString()).hashCode()
        NotificationManagerCompat.from(this).notify(notificationId, notification)
    }

    companion object {
        const val CHANNEL_ID = "lunarea_order_updates"
    }
}
