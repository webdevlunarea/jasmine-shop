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
import com.lunareafurniture.lunarea.data.security.SecureTokenStore
import kotlinx.coroutines.tasks.await

/** Call after sign-in and after the customer opts in to notifications. */
class PushDeviceRegistration(context: Context) {
    private val appContext = context.applicationContext
    private val consentStore = appContext.getSharedPreferences("lunarea_notification_settings", Context.MODE_PRIVATE)

    fun isOptedIn(): Boolean = consentStore.getBoolean("push_opt_in", false)
    fun isExplicitlyDisabled(): Boolean = consentStore.getBoolean("push_explicitly_disabled", false)

    /** Stop account-scoped pushes locally as soon as the customer signs out. */
    fun stopForSignOut() {
        consentStore.edit().putBoolean("push_opt_in", false).putBoolean("push_explicitly_disabled", true).apply()
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
        consentStore.edit().putBoolean("push_opt_in", true).putBoolean("push_explicitly_disabled", false).apply()
        // Keep consent after transient network/API failures so login or WorkManager can retry.
        return registerAfterOptIn()
    }

    suspend fun disableAndUnregister(): Result<Unit> {
        consentStore.edit().putBoolean("push_opt_in", false).putBoolean("push_explicitly_disabled", true).apply()
        return unregisterBeforeSignOut()
    }

    suspend fun registerAfterOptIn(): Result<Boolean> = runCatching {
        if (!isOptedIn()) return@runCatching false
        if (!notificationsAllowed()) return@runCatching false
        check(SecureTokenStore(appContext).read() != null) { "Masuk diperlukan sebelum mengaktifkan notifikasi akun." }
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
        check(response.isSuccessful && payload?.success == true) {
            payload?.error?.message ?: "Perangkat belum terdaftar untuk notifikasi."
        }
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
    }

    fun notificationsAllowed(): Boolean = Build.VERSION.SDK_INT < Build.VERSION_CODES.TIRAMISU ||
        ContextCompat.checkSelfPermission(appContext, Manifest.permission.POST_NOTIFICATIONS) == PackageManager.PERMISSION_GRANTED

    companion object {
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
