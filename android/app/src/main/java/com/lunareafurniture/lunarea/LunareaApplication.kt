package com.lunareafurniture.lunarea

import android.app.Application
import android.app.NotificationChannel
import android.app.NotificationManager
import android.os.Build
import com.lunareafurniture.lunarea.notifications.LunareaMessagingService
import com.lunareafurniture.lunarea.notifications.PushDeviceRegistration
import com.lunareafurniture.lunarea.data.security.SecureTokenStore
import com.lunareafurniture.lunarea.security.AndroidRecaptcha
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.launch

class LunareaApplication : Application() {
    private val appScope = CoroutineScope(SupervisorJob() + Dispatchers.IO)

    override fun onCreate() {
        super.onCreate()
        appScope.launch { runCatching { AndroidRecaptcha.initialize(this@LunareaApplication) } }
        // Recover a previously opted-in device registration after process/app restart.
        // WorkManager waits for network and retries transient API/server failures.
        val pushRegistration = PushDeviceRegistration(this)
        if (pushRegistration.isOptedIn() && pushRegistration.shouldRefreshRegistration() && SecureTokenStore(this).read() != null) {
            PushDeviceRegistration.enqueueRetry(this)
        }
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val manager = getSystemService(NotificationManager::class.java)
            manager.createNotificationChannels(
                listOf(
                    // Keep this ID stable so existing users retain their channel preferences.
                    NotificationChannel(
                        LunareaMessagingService.CHANNEL_ID,
                        "Pembaruan pesanan",
                        NotificationManager.IMPORTANCE_DEFAULT
                    ),
                    NotificationChannel(
                        LunareaMessagingService.PROMOTIONS_CHANNEL_ID,
                        "Promo dan produk",
                        NotificationManager.IMPORTANCE_LOW
                    ).apply { description = "Info voucher, acara, dan produk favorit." },
                    NotificationChannel(
                        LunareaMessagingService.ACCOUNT_CHANNEL_ID,
                        "Akun dan keamanan",
                        NotificationManager.IMPORTANCE_DEFAULT
                    ).apply { description = "Info keamanan dan pembaruan akun Lunarea." }
                )
            )
        }
    }
}
