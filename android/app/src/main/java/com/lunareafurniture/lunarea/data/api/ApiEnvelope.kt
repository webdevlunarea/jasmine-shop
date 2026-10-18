package com.lunareafurniture.lunarea.data.api

import com.google.gson.JsonObject

data class ApiEnvelope<T>(
    val success: Boolean,
    val data: T? = null,
    val meta: JsonObject? = null,
    val error: ApiError? = null
)

data class ApiError(val code: String? = null, val message: String? = null)

data class TokenPair(
    val access_token: String,
    val refresh_token: String,
    val token_type: String,
    val expires_in: Long,
    val refresh_expires_in: Long
)

data class AuthTokensResponse(val tokens: TokenPair)

data class LoginRequest(val identifier: String, val password: String, val device_name: String = "Android")
data class RefreshRequest(val refresh_token: String)
data class LogoutResponse(val logged_out: Boolean? = null)

data class ProductPage(
    val products: List<ProductSummary> = emptyList()
)

data class ProductSummary(
    val id: String,
    val name: String,
    val slug: String? = null,
    val price: Long? = null,
    val image_url: String? = null,
    val discount_percent: Int? = null,
    val category: String? = null,
    val weight_kg: Double? = null,
    val stock: Int? = null,
    val variants: List<ProductVariant> = emptyList()
)

data class ProductVariant(
    val name: String? = null,
    val stock: Int? = null
)
