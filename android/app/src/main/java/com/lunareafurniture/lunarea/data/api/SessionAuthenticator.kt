package com.lunareafurniture.lunarea.data.api

import com.lunareafurniture.lunarea.BuildConfig
import com.lunareafurniture.lunarea.data.security.SecureTokenStore
import okhttp3.Authenticator
import okhttp3.OkHttpClient
import okhttp3.Request
import okhttp3.Response
import okhttp3.Route
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory

/** Rotates the one-time refresh token after a 401; concurrent 401s reuse the rotated access token. */
class SessionAuthenticator(private val tokenStore: SecureTokenStore) : Authenticator {
    private val refreshApi: LunareaApi = Retrofit.Builder()
        .baseUrl(BuildConfig.API_BASE_URL)
        .client(OkHttpClient.Builder().build())
        .addConverterFactory(GsonConverterFactory.create())
        .build()
        .create(LunareaApi::class.java)

    @Synchronized
    override fun authenticate(route: Route?, response: Response): Request? {
        if (responseCount(response) >= 2) return null
        val failedAccess = response.request.header("Authorization")?.removePrefix("Bearer ")
        val stored = tokenStore.read() ?: return null

        if (!failedAccess.isNullOrBlank() && failedAccess != stored.accessToken) {
            return response.request.newBuilder().header("Authorization", "Bearer ${stored.accessToken}").build()
        }

        return try {
            val refreshed = refreshApi.refreshBlocking(RefreshRequest(stored.refreshToken)).execute()
            val pair = if (refreshed.isSuccessful) refreshed.body()?.data?.tokens else null
            if (pair == null) {
                if (refreshed.code() == 401) tokenStore.clear()
                null
            } else {
                tokenStore.save(pair.access_token, pair.refresh_token)
                response.request.newBuilder().header("Authorization", "Bearer ${pair.access_token}").build()
            }
        } catch (_: Exception) {
            // Leave the session intact for a transient network error; the next request can retry.
            null
        }
    }

    private fun responseCount(response: Response): Int {
        var count = 1
        var prior = response.priorResponse
        while (prior != null) { count++; prior = prior.priorResponse }
        return count
    }
}
