package com.lunareafurniture.lunarea.data.api

import com.google.gson.JsonParser
import retrofit2.Response

/** Reads the JSON error envelope for both successful and non-2xx Retrofit responses. */
fun <T> Response<ApiEnvelope<T>>.customerError(fallback: String): String {
    body()?.error?.message?.takeIf(String::isNotBlank)?.let { return it }
    val error = runCatching {
        errorBody()?.string()?.let { JsonParser.parseString(it).asJsonObject.getAsJsonObject("error") }
    }.getOrNull()
    val code = runCatching { error?.get("code")?.asString }.getOrNull()
    val message = runCatching { error?.get("message")?.asString }.getOrNull()
    return when (code) {
        "API_NOT_ENABLED" -> "Layanan aplikasi belum diaktifkan di server."
        "CHECKOUT_NOT_ENABLED" -> "Pembayaran melalui aplikasi belum tersedia. Silakan gunakan website untuk menyelesaikan pesanan."
        "SHIPPING_UNAVAILABLE" -> "Pilihan pengiriman belum tersedia. Periksa alamat atau coba lagi nanti."
        "CAPTCHA_REQUIRED" -> "Verifikasi keamanan untuk aplikasi belum tersedia. Silakan coba lagi nanti."
        "EMAIL_NOT_VERIFIED" -> message ?: "Verifikasi email diperlukan sebelum masuk."
        "RATE_LIMITED" -> message ?: "Terlalu banyak percobaan. Coba lagi nanti."
        else -> message?.takeIf(String::isNotBlank) ?: fallback
    }
}
