package com.lunareafurniture.lunarea

import android.app.Application
import android.app.NotificationChannel
import android.app.NotificationManager
import android.os.Build
import com.lunareafurniture.lunarea.notifications.LunareaMessagingService

class LunareaApplication : Application() {
    override fun onCreate() {
        super.onCreate()
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
