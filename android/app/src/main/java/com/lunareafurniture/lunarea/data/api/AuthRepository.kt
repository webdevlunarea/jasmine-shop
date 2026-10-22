package com.lunareafurniture.lunarea.data.api

import android.content.Context
import android.app.Activity
import android.os.Build
import androidx.credentials.ClearCredentialStateRequest
import androidx.credentials.CredentialManager
import androidx.credentials.CustomCredential
import androidx.credentials.GetCredentialRequest
import androidx.credentials.exceptions.GetCredentialCancellationException
import androidx.credentials.exceptions.NoCredentialException
import com.google.android.libraries.identity.googleid.GetSignInWithGoogleOption
import com.google.android.libraries.identity.googleid.GoogleIdTokenCredential
import com.google.gson.JsonObject
import com.lunareafurniture.lunarea.BuildConfig
import com.lunareafurniture.lunarea.data.security.SecureTokenStore
import com.lunareafurniture.lunarea.notifications.PushDeviceRegistration
import com.lunareafurniture.lunarea.security.AndroidRecaptcha
import kotlinx.coroutines.async
import kotlinx.coroutines.coroutineScope

class AuthRepository(context: Context) {
    private val appContext = context.applicationContext
    private val tokenStore = SecureTokenStore(appContext)
    private val api get() = ApiClient.get(appContext)
    var pendingMfaChallenge: String? = null
        private set
    private val deviceName: String by lazy {
        listOf(Build.MANUFACTURER, Build.MODEL)
            .map { it.trim() }.filter { it.isNotBlank() }.distinct().joinToString(" ")
            .take(100).ifBlank { "Android" }
    }

    suspend fun login(identifier: String, password: String): Result<Unit> = runCatching {
        val captchaToken = AndroidRecaptcha.token(appContext as android.app.Application, "login")
        val response = api.login(LoginRequest(identifier.trim(), password, device_name = deviceName, captcha_token = captchaToken))
        val body = response.body()
        if (response.isSuccessful && body?.success == true && body.data?.mfa_required == true) {
            pendingMfaChallenge = body.data.challenge_token?.takeIf(String::isNotBlank)
            check(pendingMfaChallenge != null) { "Tantangan verifikasi dua langkah tidak valid. Silakan masuk kembali." }
            throw IllegalStateException("Masukkan kode dari aplikasi autentikator untuk menyelesaikan login.")
        }
        val pair = body?.data?.tokens
        check(response.isSuccessful && body?.success == true && pair != null) {
            response.customerError("Login belum berhasil. Periksa email atau kata sandi.")
        }
        tokenStore.save(pair.access_token, pair.refresh_token)
        // Push registration is optional; authentication must still succeed if FCM is unavailable.
        val pushRegistration = PushDeviceRegistration(appContext).registerAfterOptIn()
        if (pushRegistration.isFailure) PushDeviceRegistration.enqueueRetry(appContext)
    }

    suspend fun loginWithGoogle(activity: Activity): Result<Unit> = runCatching {
        check(BuildConfig.GOOGLE_WEB_CLIENT_ID.isNotBlank()) { "Login Google belum dikonfigurasi di aplikasi." }
        val option = GetSignInWithGoogleOption.Builder(BuildConfig.GOOGLE_WEB_CLIENT_ID).build()
        val request = GetCredentialRequest.Builder().addCredentialOption(option).build()
        val credentialResponse = try {
            CredentialManager.create(activity).getCredential(activity, request)
        } catch (_: GetCredentialCancellationException) {
            throw IllegalStateException("Proses masuk Google dibatalkan.")
        } catch (_: NoCredentialException) {
            throw IllegalStateException("Akun Google belum tersedia di perangkat ini. Tambahkan akun Google lalu coba lagi.")
        }
        val credential = credentialResponse.credential as? CustomCredential
            ?: throw IllegalStateException("Respons akun Google tidak dikenali.")
        check(credential.type == GoogleIdTokenCredential.TYPE_GOOGLE_ID_TOKEN_CREDENTIAL) {
            "Jenis kredensial Google tidak didukung."
        }
        val idToken = GoogleIdTokenCredential.createFrom(credential.data).idToken
        check(idToken.isNotBlank()) { "Token Google tidak diterima. Coba lagi." }

        val response = api.googleLogin(GoogleLoginRequest(idToken, device_name = deviceName))
        val body = response.body()
        if (response.isSuccessful && body?.success == true && body.data?.mfa_required == true) {
            pendingMfaChallenge = body.data.challenge_token?.takeIf(String::isNotBlank)
            check(pendingMfaChallenge != null) { "Tantangan verifikasi dua langkah tidak valid. Silakan masuk kembali." }
            throw IllegalStateException("Masukkan kode dari aplikasi autentikator untuk menyelesaikan login.")
        }
        val pair = body?.data?.tokens
        check(response.isSuccessful && body?.success == true && pair != null) {
            response.customerError("Masuk dengan Google belum berhasil. Coba lagi.")
        }
        tokenStore.save(pair.access_token, pair.refresh_token)
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
            runCatching {
                CredentialManager.create(appContext).clearCredentialState(ClearCredentialStateRequest())
            }
        }
    }

    suspend fun completeMfaLogin(code: String): Result<Unit> = runCatching {
        val challenge = pendingMfaChallenge ?: throw IllegalStateException("Sesi verifikasi berakhir. Silakan masuk kembali.")
        val response = api.completeMfaLogin(JsonObject().apply {
            addProperty("challenge_token", challenge)
            addProperty("code", code.trim().uppercase().take(10))
        })
        val body = response.body()
        val pair = body?.data?.tokens
        check(response.isSuccessful && body?.success == true && pair != null) {
            response.customerError("Kode verifikasi belum berhasil.")
        }
        tokenStore.save(pair.access_token, pair.refresh_token)
        pendingMfaChallenge = null
        val registration = PushDeviceRegistration(appContext).registerAfterOptIn()
        if (registration.isFailure) PushDeviceRegistration.enqueueRetry(appContext)
    }

    fun clearPendingMfa() { pendingMfaChallenge = null }

    fun isSignedIn(): Boolean = tokenStore.read() != null
    fun clearLocalSession() = tokenStore.clear()
}
