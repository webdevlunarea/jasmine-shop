package com.lunareafurniture.lunarea.data.api

import com.google.gson.JsonObject

data class ApiEnvelope<T>(
    val success: Boolean,
    val data: T? = null,
    val meta: JsonObject? = null,
    val error: ApiError? = null
)

data class ApiError(val code: String? = null, val message: String? = null, val request_id: String? = null)

data class TokenPair(
    val access_token: String,
    val refresh_token: String,
    val token_type: String,
    val expires_in: Long,
    val refresh_expires_in: Long
)

data class AuthTokensResponse(val tokens: TokenPair)

data class LoginRequest(
    val identifier: String,
    val password: String,
    val device_name: String = "Android",
    val captcha_token: String,
    val captcha_platform: String = "android"
)
data class GoogleLoginRequest(val id_token: String, val device_name: String = "Lunarea Android")
data class RefreshRequest(val refresh_token: String)
data class LogoutResponse(val logged_out: Boolean? = null)

data class ProductPage(
    val products: List<ProductSummary> = emptyList()
)

data class HomeBannerPage(
    val banners: List<HomeBanner> = emptyList()
)

data class HomeBanner(
    val id: String,
    val title: String? = null,
    val alt: String? = null,
    val image_url: String,
    val link: String? = null,
    val order: Int? = null
)

data class FlashSalePage(val flash_sale: FlashSale? = null)

data class FlashSale(
    val enabled: Boolean = false,
    val mode: String = "auto",
    val title: String = "",
    val subtitle: String = "",
    val kicker: String = "",
    val end_time: String = "",
    val ends_at: String = "",
    val remaining_seconds: Long = 0,
    val products: List<ProductSummary> = emptyList()
)

data class ArticleListPage(val articles: List<ArticleSummary> = emptyList())

data class ArticleDetailPage(val article: ArticleDetail? = null)

data class ArticleSummary(
    val id: String = "",
    val title: String = "",
    val slug: String = "",
    val author: String = "",
    val published_at: String = "",
    val category: String = "",
    val excerpt: String = "",
    val image_url: String? = null,
    val likes: Int = 0,
    val shares: Int = 0
)

data class ArticleDetail(
    val id: String = "",
    val title: String = "",
    val slug: String = "",
    val author: String = "",
    val published_at: String = "",
    val category: String = "",
    val excerpt: String = "",
    val image_url: String? = null,
    val content: String = "",
    val keywords: String = "",
    val likes: Int = 0,
    val shares: Int = 0
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
    val sold_count: Int? = null,
    val variants: List<ProductVariant> = emptyList()
)

data class ProductVariant(
    val name: String? = null,
    val stock: Int? = null
)
