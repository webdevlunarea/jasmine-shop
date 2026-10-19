package com.lunareafurniture.lunarea.notifications

import android.content.Context
import android.content.pm.PackageManager
import android.Manifest
import android.os.Build
import androidx.core.content.ContextCompat
import androidx.work.Constraints
import androidx.work.ExistingWorkPolicy
import androidx.work.NetworkType
import androidx.work.OneTimeWorkRequestBuilder
import androidx.work.WorkManager
import com.google.firebase.messaging.FirebaseMessaging
import com.google.gson.JsonObject
import com.lunareafurniture.lunarea.data.api.ApiClient
import com.lunareafurniture.lunarea.data.api.customerError
import com.lunareafurniture.lunarea.data.security.SecureTokenStore
import kotlinx.coroutines.tasks.await

/** Call after sign-in and after the customer opts in to notifications. */
class PushDeviceRegistration(context: Context) {
    private val appContext = context.applicationContext
    private val consentStore = appContext.getSharedPreferences("lunarea_notification_settings", Context.MODE_PRIVATE)

    fun isOptedIn(): Boolean = consentStore.getBoolean("push_opt_in", false)
    /** True only after the API confirms this installation is persisted for the signed-in account. */
    fun isRegistered(): Boolean = consentStore.getBoolean("push_device_registered", false)
    fun isExplicitlyDisabled(): Boolean = consentStore.getBoolean("push_explicitly_disabled", false)
    fun shouldRefreshRegistration(now: Long = System.currentTimeMillis()): Boolean {
        val lastSuccess = consentStore.getLong("push_registration_success_at", 0L)
        return lastSuccess <= 0L || now - lastSuccess >= REGISTRATION_REFRESH_INTERVAL_MS
    }

    fun markUnregistered() {
        consentStore.edit().putBoolean("push_device_registered", false).apply()
    }

    /** Stop account-scoped pushes locally as soon as the customer signs out. */
    fun stopForSignOut() {
        consentStore.edit().putBoolean("push_opt_in", false).putBoolean("push_explicitly_disabled", true)
            .putBoolean("push_device_registered", false).apply()
        WorkManager.getInstance(appContext).cancelUniqueWork(RegisterPushDeviceWorker.UNIQUE_NAME)
        androidx.core.app.NotificationManagerCompat.from(appContext).cancelAll()
    }

    /** System permission approval is the customer's explicit consent on Android 13+. */
    fun rememberPermissionConsent() {
        if (notificationsAllowed()) consentStore.edit()
            .putBoolean("push_opt_in", true)
            .putBoolean("push_explicitly_disabled", false)
            .apply()
    }

    suspend fun enableAndRegister(): Result<Boolean> {
        consentStore.edit().putBoolean("push_opt_in", true).putBoolean("push_explicitly_disabled", false)
            .putBoolean("push_device_registered", false).apply()
        // Keep consent after transient network/API failures so login or WorkManager can retry.
        val result = registerAfterOptIn()
        if (result.isFailure) enqueueRetry(appContext)
        return result
    }

    suspend fun disableAndUnregister(): Result<Unit> {
        consentStore.edit().putBoolean("push_opt_in", false).putBoolean("push_explicitly_disabled", true)
            .putBoolean("push_device_registered", false).apply()
        return unregisterBeforeSignOut()
    }

    suspend fun registerAfterOptIn(): Result<Boolean> = runCatching {
        if (!isOptedIn()) return@runCatching false
        if (!notificationsAllowed()) return@runCatching false
        check(SecureTokenStore(appContext).read() != null) { "Masuk diperlukan sebelum mengaktifkan notifikasi akun." }
        markUnregistered()
        val token = FirebaseMessaging.getInstance().token.await()
        require(token.length in 40..4096) { "Token Firebase belum valid." }
        val tokenStore = SecureTokenStore(appContext)
        tokenStore.savePushToken(token)

        val body = JsonObject().apply {
            addProperty("device_token", token)
            addProperty("platform", "android")
            addProperty("device_name", "Android ${Build.VERSION.RELEASE}")
        }
        val response = ApiClient.get(appContext).registerPushDevice(body)
        val payload = response.body()
        if (!response.isSuccessful) {
            throw PushDeviceRegistrationException(response.code(), response.customerError("Pendaftaran notifikasi gagal (HTTP ${response.code()}). Coba lagi."))
        }
        check(payload?.success == true && payload.data?.get("registered")?.asBoolean == true) {
            payload?.error?.message ?: "Server belum mengonfirmasi pendaftaran perangkat. Coba lagi."
        }
        consentStore.edit()
            .putBoolean("push_device_registered", true)
            .putLong("push_registration_success_at", System.currentTimeMillis())
            .apply()
        true
    }

    suspend fun unregisterBeforeSignOut(): Result<Unit> = runCatching {
        val tokenStore = SecureTokenStore(appContext)
        val token = tokenStore.readPushToken()
        if (token != null) {
            val response = ApiClient.get(appContext).unregisterPushDevice(JsonObject().apply {
                addProperty("device_token", token)
            })
            check(response.isSuccessful && response.body()?.success == true) {
                response.body()?.error?.message ?: "Perangkat belum dapat dilepas dari notifikasi."
            }
            tokenStore.clearPushToken()
        }
        markUnregistered()
    }

    fun notificationsAllowed(): Boolean = Build.VERSION.SDK_INT < Build.VERSION_CODES.TIRAMISU ||
        ContextCompat.checkSelfPermission(appContext, Manifest.permission.POST_NOTIFICATIONS) == PackageManager.PERMISSION_GRANTED

    companion object {
        private const val REGISTRATION_REFRESH_INTERVAL_MS = 24L * 60L * 60L * 1000L

        fun enqueueRetry(context: Context) {
            val constraints = Constraints.Builder().setRequiredNetworkType(NetworkType.CONNECTED).build()
            val work = OneTimeWorkRequestBuilder<RegisterPushDeviceWorker>().setConstraints(constraints).build()
            WorkManager.getInstance(context.applicationContext).enqueueUniqueWork(
                RegisterPushDeviceWorker.UNIQUE_NAME,
                ExistingWorkPolicy.REPLACE,
                work
            )
        }
    }
}

class PushDeviceRegistrationException(val httpStatus: Int, message: String) : IllegalStateException(message)
