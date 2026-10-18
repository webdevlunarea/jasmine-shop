package com.lunareafurniture.lunarea.data.api

import android.content.Context
import com.lunareafurniture.lunarea.data.security.SecureTokenStore
import com.lunareafurniture.lunarea.notifications.PushDeviceRegistration
import kotlinx.coroutines.async
import kotlinx.coroutines.coroutineScope

class AuthRepository(context: Context) {
    private val appContext = context.applicationContext
    private val tokenStore = SecureTokenStore(appContext)
    private val api get() = ApiClient.get(appContext)

    suspend fun login(identifier: String, password: String): Result<Unit> = runCatching {
        val response = api.login(LoginRequest(identifier.trim(), password))
        val body = response.body()
        val pair = body?.data?.tokens
        check(response.isSuccessful && body?.success == true && pair != null) {
            response.customerError("Login belum berhasil. Periksa email atau kata sandi.")
        }
        tokenStore.save(pair.access_token, pair.refresh_token)
        // Push registration is optional; authentication must still succeed if FCM is unavailable.
        val pushRegistration = PushDeviceRegistration(appContext).registerAfterOptIn()
        if (pushRegistration.isFailure) PushDeviceRegistration.enqueueRetry(appContext)
    }

    suspend fun logout(): Result<Unit> = runCatching {
        try {
            // Both calls need the current bearer token. Run them concurrently so a
            // slow push endpoint does not add its latency to the logout request.
            coroutineScope {
                val unregister = async { PushDeviceRegistration(appContext).unregisterBeforeSignOut() }
                val revokeSession = async {
                    runCatching {
                        val response = api.logout()
                        check(response.isSuccessful && response.body()?.success == true) {
                            response.customerError("Sesi lokal sudah ditutup; server akan menyelaraskan sesi saat tersedia.")
                        }
                    }
                }
                unregister.await()
                revokeSession.await().getOrThrow()
            }
        } finally {
            tokenStore.clear()
        }
    }

    fun isSignedIn(): Boolean = tokenStore.read() != null
    fun clearLocalSession() = tokenStore.clear()
}
