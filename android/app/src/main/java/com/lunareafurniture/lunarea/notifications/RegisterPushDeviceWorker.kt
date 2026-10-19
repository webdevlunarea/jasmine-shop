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
            onFailure = { error ->
                val status = (error as? PushDeviceRegistrationException)?.httpStatus
                if (status != null && status in 400..499 && status !in setOf(408, 429)) Result.failure()
                else Result.retry()
            }
        )
    }

    companion object { const val UNIQUE_NAME = "lunarea-register-fcm-device" }
}
