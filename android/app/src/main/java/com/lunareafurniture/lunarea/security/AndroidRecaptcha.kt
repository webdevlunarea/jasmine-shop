package com.lunareafurniture.lunarea.security

import android.app.Application
import android.util.Log
import com.google.android.recaptcha.Recaptcha
import com.google.android.recaptcha.RecaptchaAction
import com.google.android.recaptcha.RecaptchaClient
import com.google.android.recaptcha.RecaptchaException
import com.lunareafurniture.lunarea.BuildConfig
import kotlinx.coroutines.sync.Mutex
import kotlinx.coroutines.sync.withLock

/** One process-wide client; tokens are short-lived and generated for each protected action. */
object AndroidRecaptcha {
    private val mutex = Mutex()
    @Volatile private var client: RecaptchaClient? = null

    suspend fun initialize(application: Application) {
        if (BuildConfig.RECAPTCHA_ANDROID_KEY_ID.isBlank()) return
        if (client != null) return
        mutex.withLock {
            if (client == null) {
                client = Recaptcha.fetchClient(application, BuildConfig.RECAPTCHA_ANDROID_KEY_ID)
            }
        }
    }

    suspend fun token(application: Application, action: String): String {
        require(action.matches(Regex("^[a-z][a-z0-9_]{1,99}$"))) { "Aksi verifikasi tidak valid." }
        check(BuildConfig.RECAPTCHA_ANDROID_KEY_ID.isNotBlank()) { "Verifikasi keamanan aplikasi belum dikonfigurasi." }
        val recaptchaClient = client ?: run {
            initialize(application)
            client ?: throw IllegalStateException("Layanan verifikasi keamanan belum siap. Periksa koneksi lalu coba lagi.")
        }
        return recaptchaClient.execute(RecaptchaAction.custom(action), 10_000L)
            .getOrElse { error ->
                val code = (error as? RecaptchaException)?.errorCode?.name ?: "UNKNOWN_ERROR"
                // Log only Google's non-sensitive error code; never include the key or token.
                Log.e("AndroidRecaptcha", "Token generation failed: $code")
                val message = when (code) {
                    "INVALID_KEYTYPE" -> "Konfigurasi verifikasi aplikasi belum sesuai. Periksa jenis kunci Android dan izin distribusi aplikasi."
                    "INVALID_PACKAGE_NAME" -> "Konfigurasi verifikasi belum mengizinkan aplikasi Lunarea."
                    "INVALID_SITEKEY" -> "Kunci verifikasi aplikasi tidak valid."
                    "NETWORK_ERROR", "NO_NETWORK_FOUND" -> "Layanan verifikasi tidak dapat dijangkau. Periksa koneksi lalu coba lagi."
                    else -> "Verifikasi keamanan gagal. Periksa konfigurasi atau koneksi lalu coba lagi."
                }
                throw IllegalStateException(message, error)
            }
            .takeIf(String::isNotBlank)
            ?: throw IllegalStateException("Token verifikasi keamanan kosong. Coba lagi.")
    }
}
