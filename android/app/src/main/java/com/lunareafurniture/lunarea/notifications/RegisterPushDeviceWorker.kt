package com.lunareafurniture.lunarea.notifications

import android.content.Context
import androidx.work.CoroutineWorker
import androidx.work.WorkerParameters
import com.lunareafurniture.lunarea.data.security.SecureTokenStore

class RegisterPushDeviceWorker(context: Context, parameters: WorkerParameters) : CoroutineWorker(context, parameters) {
    override suspend fun doWork(): Result {
        if (SecureTokenStore(applicationContext).read() == null) return Result.success()
        val registration = PushDeviceRegistration(applicationContext)
        if (!registration.notificationsAllowed()) return Result.success()
        return registration.registerAfterOptIn().fold(
            onSuccess = { Result.success() },
            onFailure = { Result.retry() }
        )
    }

    companion object { const val UNIQUE_NAME = "lunarea-register-fcm-device" }
}
