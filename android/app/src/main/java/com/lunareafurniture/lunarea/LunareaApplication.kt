package com.lunareafurniture.lunarea

import android.app.Application
import android.app.NotificationChannel
import android.app.NotificationManager
import android.os.Build
import com.lunareafurniture.lunarea.notifications.LunareaMessagingService
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
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            getSystemService(NotificationManager::class.java).createNotificationChannel(
                NotificationChannel(
                    LunareaMessagingService.CHANNEL_ID,
                    "Pembaruan pesanan",
                    NotificationManager.IMPORTANCE_DEFAULT
                )
            )
        }
    }
}
