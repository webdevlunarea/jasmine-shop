package com.lunareafurniture.lunarea.data.api

import android.content.Context
import com.lunareafurniture.lunarea.BuildConfig
import com.lunareafurniture.lunarea.data.security.SecureTokenStore
import okhttp3.Interceptor
import okhttp3.OkHttpClient
import okhttp3.Response
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

object ApiClient {
    @Volatile private var api: LunareaApi? = null

    fun get(context: Context): LunareaApi = api ?: synchronized(this) {
        api ?: create(context.applicationContext).also { api = it }
    }

    private fun create(context: Context): LunareaApi {
        val tokenStore = SecureTokenStore(context)
        val authHeader = Interceptor { chain ->
            val original = chain.request()
            val access = tokenStore.read()?.accessToken
            val request = original.newBuilder()
                .header("Accept", "application/json")
                .apply { if (!access.isNullOrBlank()) header("Authorization", "Bearer $access") }
                .build()
            chain.proceed(request)
        }
        val client = OkHttpClient.Builder()
            .connectTimeout(15, TimeUnit.SECONDS)
            .readTimeout(25, TimeUnit.SECONDS)
            .writeTimeout(25, TimeUnit.SECONDS)
            .addInterceptor(authHeader)
            .authenticator(SessionAuthenticator(tokenStore))
            .build()
        return Retrofit.Builder()
            .baseUrl(BuildConfig.API_BASE_URL)
            .client(client)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(LunareaApi::class.java)
    }
}
