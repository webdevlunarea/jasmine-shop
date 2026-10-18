package com.lunareafurniture.lunarea.data.api

import com.google.gson.JsonObject
import retrofit2.Response
import retrofit2.Call
import okhttp3.MultipartBody
import okhttp3.RequestBody
import retrofit2.http.Body
import retrofit2.http.DELETE
import retrofit2.http.GET
import retrofit2.http.HTTP
import retrofit2.http.Multipart
import retrofit2.http.PATCH
import retrofit2.http.POST
import retrofit2.http.PUT
import retrofit2.http.Part
import retrofit2.http.Path
import retrofit2.http.Query

/** Paths follow docs/ANDROID_API_V1_CONTRACT.md. Do not send client-calculated prices. */
interface LunareaApi {
    @POST("auth/login")
    suspend fun login(@Body request: LoginRequest): Response<ApiEnvelope<AuthTokensResponse>>

    @POST("auth/register")
    suspend fun register(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @POST("auth/verify-email")
    suspend fun verifyEmail(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @POST("auth/verification/resend")
    suspend fun resendVerification(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @POST("auth/password-reset/request")
    suspend fun requestPasswordReset(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @POST("auth/password-reset/complete")
    suspend fun completePasswordReset(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @POST("auth/refresh")
    suspend fun refresh(@Body request: RefreshRequest): Response<ApiEnvelope<AuthTokensResponse>>

    @POST("auth/refresh")
    fun refreshBlocking(@Body request: RefreshRequest): Call<ApiEnvelope<AuthTokensResponse>>

    @POST("auth/logout")
    suspend fun logout(): Response<ApiEnvelope<LogoutResponse>>

    @GET("catalog/products")
    suspend fun products(
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 20,
        @Query("q") query: String? = null,
        @Query("category") category: String? = null
    ): Response<ApiEnvelope<ProductPage>>

    @GET("catalog/categories")
    suspend fun categories(): Response<ApiEnvelope<JsonObject>>

    @GET("catalog/products/{id}")
    suspend fun product(@Path("id") id: String): Response<ApiEnvelope<JsonObject>>

    @GET("regions/provinces")
    suspend fun provinces(): Response<ApiEnvelope<JsonObject>>

    @GET("regions/cities")
    suspend fun cities(@Query("province_id") provinceId: Int): Response<ApiEnvelope<JsonObject>>

    @GET("regions/districts")
    suspend fun districts(@Query("city_id") cityId: Int): Response<ApiEnvelope<JsonObject>>

    @GET("regions/villages")
    suspend fun villages(@Query("district_id") districtId: Int): Response<ApiEnvelope<JsonObject>>

    @GET("me")
    suspend fun profile(): Response<ApiEnvelope<JsonObject>>

    @PATCH("me")
    suspend fun updateProfile(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @GET("addresses")
    suspend fun addresses(): Response<ApiEnvelope<JsonObject>>

    @POST("addresses")
    suspend fun createAddress(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @PUT("addresses")
    suspend fun updateAddress(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @DELETE("addresses/{id}")
    suspend fun deleteAddress(@Path("id") id: Long): Response<ApiEnvelope<JsonObject>>

    @POST("addresses/{id}/default")
    suspend fun setDefaultAddress(@Path("id") id: Long): Response<ApiEnvelope<JsonObject>>

    @GET("cart")
    suspend fun cart(): Response<ApiEnvelope<JsonObject>>

    @POST("cart/items")
    suspend fun addCartItem(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @PATCH("cart/items")
    suspend fun updateCartItem(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @HTTP(method = "DELETE", path = "cart/items", hasBody = true)
    suspend fun deleteCartItem(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @GET("wishlist")
    suspend fun wishlist(): Response<ApiEnvelope<JsonObject>>

    @POST("wishlist/items")
    suspend fun addWishlistItem(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @HTTP(method = "DELETE", path = "wishlist/items", hasBody = true)
    suspend fun deleteWishlistItem(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @POST("shipping/quotes")
    suspend fun shippingQuotes(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @GET("vouchers/available")
    suspend fun availableVouchers(): Response<ApiEnvelope<JsonObject>>

    @POST("checkout/preview")
    suspend fun checkoutPreview(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @POST("checkout/orders")
    suspend fun createOrder(@Body body: JsonObject, @retrofit2.http.Header("Idempotency-Key") idempotencyKey: String): Response<ApiEnvelope<JsonObject>>

    @GET("orders")
    suspend fun orders(@Query("page") page: Int = 1, @Query("per_page") perPage: Int = 20): Response<ApiEnvelope<JsonObject>>

    @GET("orders/{id}")
    suspend fun order(@Path("id") id: String): Response<ApiEnvelope<JsonObject>>

    @GET("orders/{id}/payment-status")
    suspend fun paymentStatus(@Path("id") id: String): Response<ApiEnvelope<JsonObject>>

    @GET("orders/{id}/tracking")
    suspend fun tracking(@Path("id") id: String): Response<ApiEnvelope<JsonObject>>

    @POST("orders/{id}/cancel")
    suspend fun cancelOrder(@Path("id") id: String): Response<ApiEnvelope<JsonObject>>

    @Multipart
    @POST("orders/{id}/payment-proof")
    suspend fun uploadPaymentProof(@Path("id") id: String, @Part proof: MultipartBody.Part, @Part("note") note: RequestBody? = null): Response<ApiEnvelope<JsonObject>>

    @POST("auth/password/change")
    suspend fun changePassword(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @POST("auth/email/change/request")
    suspend fun requestEmailChange(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @POST("auth/email/change/confirm")
    suspend fun confirmEmailChange(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @POST("push/devices")
    suspend fun registerPushDevice(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>

    @HTTP(method = "DELETE", path = "push/devices", hasBody = true)
    suspend fun unregisterPushDevice(@Body body: JsonObject): Response<ApiEnvelope<JsonObject>>
}
