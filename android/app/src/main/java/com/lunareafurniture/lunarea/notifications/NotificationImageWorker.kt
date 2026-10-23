package com.lunareafurniture.lunarea.notifications

import android.Manifest
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.content.pm.PackageManager
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.os.Build
import androidx.core.app.NotificationCompat
import androidx.core.app.NotificationManagerCompat
import androidx.core.content.ContextCompat
import androidx.work.CoroutineWorker
import androidx.work.Data
import androidx.work.OneTimeWorkRequestBuilder
import androidx.work.WorkManager
import androidx.work.WorkerParameters
import com.lunareafurniture.lunarea.MainActivity
import com.lunareafurniture.lunarea.data.security.SecureTokenStore
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import java.net.HttpURLConnection
import java.net.URL

/** Fetches a small, first-party campaign image off the FCM callback, then enriches its existing notification. */
class NotificationImageWorker(context: Context, parameters: WorkerParameters) : CoroutineWorker(context, parameters) {
    override suspend fun doWork(): Result = withContext(Dispatchers.IO) {
        val url = inputData.getString(KEY_URL).orEmpty()
        val title = inputData.getString(KEY_TITLE).orEmpty()
        val body = inputData.getString(KEY_BODY).orEmpty()
        val notificationId = inputData.getInt(KEY_NOTIFICATION_ID, 0)
        val channelId = inputData.getString(KEY_CHANNEL).orEmpty()
        if (!isAllowedImageUrl(url) || title.isBlank() || body.isBlank() || notificationId == 0 || channelId.isBlank()) {
            return@withContext Result.failure()
        }
        if (!isStillEligible(applicationContext)) return@withContext Result.success()

        val bitmap = runCatching { downloadBitmap(url) }.getOrNull() ?: return@withContext Result.success()
        if (!isStillEligible(applicationContext)) return@withContext Result.success()

        val data = inputData.keyValueMap
        val tapIntent = Intent(applicationContext, MainActivity::class.java)
            .addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP)
        listOf("type", "order_id", "product_id", "variant", "campaign_id", "notification_id").forEach { key ->
            data[key]?.toString()?.takeIf(String::isNotBlank)?.let { tapIntent.putExtra("push_$key", it) }
        }
        val pendingIntent = PendingIntent.getActivity(
            applicationContext,
            notificationId,
            tapIntent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )
        val group = "lunarea_group_$channelId"
        val privateVersion = NotificationCompat.Builder(applicationContext, channelId)
            .setSmallIcon(com.lunareafurniture.lunarea.R.drawable.ic_notification_small)
            .setContentTitle("Pembaruan Lunarea")
            .setContentText("Buka aplikasi untuk melihat informasi terbaru.")
            .setVisibility(NotificationCompat.VISIBILITY_PUBLIC)
            .build()
        val notification = NotificationCompat.Builder(applicationContext, channelId)
            .setSmallIcon(com.lunareafurniture.lunarea.R.drawable.ic_notification_small)
            .setContentTitle(title)
            .setContentText(body)
            .setStyle(NotificationCompat.BigPictureStyle().bigPicture(bitmap))
            .setContentIntent(pendingIntent)
            .setVisibility(NotificationCompat.VISIBILITY_PRIVATE)
            .setPublicVersion(privateVersion)
            .setGroup(group)
            .setAutoCancel(true)
            .build()
        if (ContextCompat.checkSelfPermission(applicationContext, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED) {
            return@withContext Result.success()
        }
        NotificationManagerCompat.from(applicationContext).notify(notificationId, notification)
        Result.success()
    }

    private fun isStillEligible(context: Context): Boolean {
        val optedIn = context.getSharedPreferences("lunarea_notification_settings", Context.MODE_PRIVATE)
            .getBoolean("push_opt_in", false)
        if (!optedIn || SecureTokenStore(context).read() == null) return false
        return Build.VERSION.SDK_INT < Build.VERSION_CODES.TIRAMISU ||
            ContextCompat.checkSelfPermission(context, Manifest.permission.POST_NOTIFICATIONS) == PackageManager.PERMISSION_GRANTED
    }

    private fun isAllowedImageUrl(value: String): Boolean = runCatching {
        val url = URL(value)
        url.protocol.equals("https", ignoreCase = true) &&
            url.host.equals(ALLOWED_HOST, ignoreCase = true) &&
            url.port in listOf(-1, 443) &&
            url.userInfo == null &&
            url.path.startsWith(ALLOWED_PATH)
    }.getOrDefault(false)

    private fun downloadBitmap(value: String): Bitmap? {
        val connection = (URL(value).openConnection() as HttpURLConnection).apply {
            connectTimeout = 4_000
            readTimeout = 4_000
            instanceFollowRedirects = false
            requestMethod = "GET"
            setRequestProperty("Accept", "image/jpeg,image/png,image/webp")
        }
        return try {
            if (connection.responseCode != HttpURLConnection.HTTP_OK) return null
            val contentType = connection.contentType.orEmpty().substringBefore(';').trim().lowercase()
            if (contentType !in setOf("image/jpeg", "image/png", "image/webp")) return null
            val declaredSize = connection.contentLengthLong
            if (declaredSize >= MAX_IMAGE_BYTES) return null
            val bytes = connection.inputStream.use { input ->
                val output = java.io.ByteArrayOutputStream()
                val buffer = ByteArray(8 * 1024)
                var total = 0
                while (true) {
                    val count = input.read(buffer)
                    if (count < 0) break
                    total += count
                    if (total >= MAX_IMAGE_BYTES) return null
                    output.write(buffer, 0, count)
                }
                output.toByteArray()
            }
            if (bytes.isEmpty()) return null
            val bounds = BitmapFactory.Options().apply { inJustDecodeBounds = true }
            BitmapFactory.decodeByteArray(bytes, 0, bytes.size, bounds)
            if (bounds.outWidth <= 0 || bounds.outHeight <= 0 ||
                bounds.outWidth > 1920 || bounds.outHeight > 1920 ||
                bounds.outWidth.toLong() * bounds.outHeight.toLong() > 4_000_000L
            ) return null
            val sample = maxOf(1, maxOf(bounds.outWidth, bounds.outHeight) / MAX_BITMAP_EDGE)
            BitmapFactory.decodeByteArray(bytes, 0, bytes.size, BitmapFactory.Options().apply { inSampleSize = sample })
        } finally {
            connection.disconnect()
        }
    }

    companion object {
        private const val WORK_NAME_PREFIX = "lunarea-notification-image-"
        private const val KEY_URL = "image_url"
        private const val KEY_TITLE = "notification_title"
        private const val KEY_BODY = "notification_body"
        private const val KEY_NOTIFICATION_ID = "notification_id"
        private const val KEY_CHANNEL = "notification_channel"
        private const val ALLOWED_HOST = "lunareafurniture.com"
        private const val ALLOWED_PATH = "/uploads/push-broadcasts/"
        private const val MAX_IMAGE_BYTES = 512 * 1024
        private const val MAX_BITMAP_EDGE = 1024

        fun enqueue(
            context: Context,
            title: String,
            body: String,
            url: String,
            pushData: Map<String, String>,
            notificationId: Int,
            channelId: String
        ) {
            if (!runCatching {
                    val parsed = URL(url)
                    parsed.protocol.equals("https", true) && parsed.host.equals(ALLOWED_HOST, true) &&
                        parsed.port in listOf(-1, 443) && parsed.userInfo == null && parsed.path.startsWith(ALLOWED_PATH)
                }.getOrDefault(false)) return

            val builder = Data.Builder()
                .putString(KEY_URL, url)
                .putString(KEY_TITLE, title.take(100))
                .putString(KEY_BODY, body.take(240))
                .putInt(KEY_NOTIFICATION_ID, notificationId)
                .putString(KEY_CHANNEL, channelId)
            listOf("type", "order_id", "product_id", "variant", "campaign_id").forEach { key ->
                pushData[key]?.take(500)?.let { builder.putString(key, it) }
            }
            val request = OneTimeWorkRequestBuilder<NotificationImageWorker>().setInputData(builder.build()).build()
            WorkManager.getInstance(context).enqueueUniqueWork(
                WORK_NAME_PREFIX + notificationId,
                androidx.work.ExistingWorkPolicy.REPLACE,
                request
            )
        }
    }
}
