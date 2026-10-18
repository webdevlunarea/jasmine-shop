package com.lunareafurniture.lunarea

import android.os.Bundle
import android.os.Build
import android.text.Html
import android.net.Uri
import android.content.Intent
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.compose.BackHandler
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.background
import androidx.compose.foundation.Image
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.ui.draw.clip
import androidx.compose.foundation.horizontalScroll
import androidx.compose.ui.layout.ContentScale
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.lazy.grid.rememberLazyGridState
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.input.nestedscroll.NestedScrollConnection
import androidx.compose.ui.input.nestedscroll.NestedScrollSource
import androidx.compose.ui.input.nestedscroll.nestedScroll
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.foundation.pager.HorizontalPager
import androidx.compose.foundation.pager.rememberPagerState
import androidx.compose.foundation.selection.toggleable
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.text.KeyboardActions
import androidx.compose.material3.*
import androidx.compose.ui.window.Dialog
import androidx.compose.ui.window.DialogProperties
import androidx.compose.animation.animateColorAsState
import androidx.compose.animation.core.animateFloat
import androidx.compose.animation.core.LinearEasing
import androidx.compose.animation.core.RepeatMode
import androidx.compose.animation.core.infiniteRepeatable
import androidx.compose.animation.core.rememberInfiniteTransition
import androidx.compose.animation.core.tween
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.semantics.Role
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.compose.ui.unit.Velocity
import com.lunareafurniture.lunarea.data.api.ApiClient
import com.lunareafurniture.lunarea.data.api.AuthRepository
import com.lunareafurniture.lunarea.data.api.ProductSummary
import com.lunareafurniture.lunarea.data.api.customerError
import com.google.gson.JsonObject
import com.lunareafurniture.lunarea.notifications.PushDeviceRegistration
import com.lunareafurniture.lunarea.notifications.rememberNotificationPermissionRequester
import com.lunareafurniture.lunarea.ui.theme.LunareaTheme
import com.lunareafurniture.lunarea.ui.theme.LunareaGreenText
import com.lunareafurniture.lunarea.ui.theme.LunareaSpacing
import coil.compose.AsyncImage
import kotlinx.coroutines.launch
import kotlinx.coroutines.delay
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.toRequestBody
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import java.text.NumberFormat
import java.util.Locale

private enum class AppTab(val label: String) { HOME("Beranda"), CATALOG("Katalog"), CART("Keranjang"), ACCOUNT("Akun"), CHECKOUT("Checkout") }
private enum class AccountSection { MENU, ORDERS, FAVORITES, ADDRESSES, PROFILE, SECURITY, CHECKOUT, REGISTER, VERIFY_EMAIL, RECOVERY }
private data class RegionOption(val id: Int, val label: String, val postal: String = "")
private data class ShippingOption(val token: String, val courier: String, val service: String, val cost: Long, val eta: String?)
private data class CartLine(val id: String, val name: String, val variant: String, val quantity: Int, val price: Long, val image: String?)
private enum class ProductInfoSheet { DESCRIPTION, REVIEWS }
private val nativePaymentMethods = listOf(
    "bca" to "BCA Virtual Account",
    "bri" to "BRI Virtual Account",
    "bni" to "BNI Virtual Account",
    "cimb" to "CIMB Virtual Account",
    "permata" to "Permata Virtual Account",
    "mandiri" to "Mandiri Bill",
    "qris" to "QRIS",
    "gopay" to "GoPay",
    "shopeepay" to "ShopeePay"
)
private val homeBannerUrls = (1..5).map { index ->
    "${BuildConfig.API_BASE_URL.removeSuffix("/api/v1/")}/img/benner/$index.webp"
}

private fun readableProductDescription(raw: String): String {
    val decoded = Html.fromHtml(raw, Html.FROM_HTML_MODE_COMPACT).toString()
        .replace('\u00A0', ' ')
        .replace("\r\n", "\n")
        .replace('\r', '\n')
    return decoded.lineSequence()
        .map(String::trim)
        .fold(mutableListOf<String>()) { lines, line ->
            if (line.isNotEmpty() || lines.lastOrNull()?.isNotEmpty() == true) lines.add(line)
            lines
        }
        .joinToString("\n")
        .trim()
        .ifBlank { "Informasi produk belum tersedia." }
}

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent { LunareaTheme { LunareaApp() } }
    }
}

@Composable
private fun LunareaApp() {
    val context = LocalContext.current
    val scope = rememberCoroutineScope()
    val auth = remember { AuthRepository(context) }
    var tab by remember { mutableStateOf(AppTab.HOME) }
    var signedIn by remember { mutableStateOf(auth.isSignedIn()) }
    var products by remember { mutableStateOf<List<ProductSummary>>(emptyList()) }
    var categories by remember { mutableStateOf<List<String>>(emptyList()) }
    var selectedCategory by remember { mutableStateOf<String?>(null) }
    var catalogPage by remember { mutableIntStateOf(1) }
    var catalogPages by remember { mutableIntStateOf(1) }
    var catalogRequestGeneration by remember { mutableIntStateOf(0) }
    var loading by remember { mutableStateOf(false) }
    var message by remember { mutableStateOf<String?>(null) }
    var query by remember { mutableStateOf("") }
    var identifier by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var busy by remember { mutableStateOf(false) }
    var notificationMessage by remember { mutableStateOf<String?>(null) }
    var notificationsEnabled by remember { mutableStateOf(PushDeviceRegistration(context).isOptedIn()) }
    var showNotificationIntro by remember { mutableStateOf(false) }
    var notificationInboxOpen by remember { mutableStateOf(false) }
    var notificationsRefreshVersion by remember { mutableIntStateOf(0) }
    var unreadNotificationCount by remember { mutableIntStateOf(0) }
    var cartItems by remember { mutableStateOf<List<CartLine>>(emptyList()) }
    var cartSubtotal by remember { mutableLongStateOf(0L) }
    var cartLoading by remember { mutableStateOf(false) }
    var cartMessage by remember { mutableStateOf<String?>(null) }
    var cartRefreshVersion by remember { mutableIntStateOf(0) }
    var accountSection by remember { mutableStateOf(AccountSection.MENU) }
    var authFlowEmail by remember { mutableStateOf("") }
    var detailOpen by remember { mutableStateOf(false) }

    fun loadProducts(search: String = query, category: String? = selectedCategory, page: Int = 1, append: Boolean = false) {
        if (append && loading) return
        val requestGeneration = ++catalogRequestGeneration
        scope.launch {
            loading = true
            message = null
            try {
                // Fetch a smaller first batch so the first visible grid is ready sooner;
                // additional products remain available through the existing pagination.
                val response = ApiClient.get(context).products(
                    page = page,
                    perPage = 20,
                    query = search.trim().takeIf(String::isNotEmpty),
                    category = category?.trim()?.takeIf(String::isNotEmpty)
                )
                if (requestGeneration != catalogRequestGeneration) return@launch
                val body = response.body()
                val catalog = body?.data
                if (response.isSuccessful && body?.success == true && catalog != null) {
                    val incoming = catalog.products.filter { it.id.isNotBlank() }
                    products = (if (append) products + incoming else incoming).distinctBy { it.id }
                    catalogPage = page.coerceAtLeast(1)
                    catalogPages = runCatching { body.meta?.get("pages")?.asInt ?: 1 }
                        .getOrDefault(1).coerceAtLeast(catalogPage)
                    if (append && incoming.isEmpty()) catalogPages = catalogPage
                    if (products.isEmpty()) {
                        message = if (search.isNotBlank()) "Tidak ada produk untuk ‘${search.trim()}’. Coba kata kunci lain." else "Belum ada produk yang dapat ditampilkan."
                    }
                } else {
                    val errorPayload = runCatching {
                        response.errorBody()?.string()?.let { com.google.gson.JsonParser.parseString(it).asJsonObject }
                    }.getOrNull()
                    val errorCode = errorPayload?.getAsJsonObject("error")?.get("code")?.asString
                    message = body?.error?.message ?: when {
                        errorCode == "API_NOT_ENABLED" -> "Layanan aplikasi belum diaktifkan di server. Data katalog belum dapat dimuat; admin perlu mengaktifkan API Android setelah pengujian."
                        response.code() == 503 -> "Layanan katalog sedang belum tersedia. Coba lagi nanti."
                        response.code() == 401 -> "Sesi akun perlu diperbarui. Silakan masuk kembali."
                        response.code() == 404 -> "Alamat layanan katalog tidak ditemukan. Periksa konfigurasi API aplikasi."
                        response.code() >= 500 -> "Server katalog sedang bermasalah. Coba lagi beberapa saat."
                        else ->
                            errorPayload?.getAsJsonObject("error")?.get("message")?.asString
                                ?: "Katalog belum berhasil dimuat. Coba lagi sebentar."
                    }
                }
            } catch (_: Exception) {
                if (requestGeneration == catalogRequestGeneration) message = "Koneksi belum tersedia. Periksa internet lalu coba lagi."
            } finally {
                if (requestGeneration == catalogRequestGeneration) loading = false
            }
        }
    }

    LaunchedEffect(Unit) {
        loadProducts("")
        try {
            val result = ApiClient.get(context).categories()
            val rows = result.body()?.data?.getAsJsonArray("categories")
            if (result.isSuccessful && result.body()?.success == true && rows != null) {
                categories = rows.mapNotNull { raw ->
                    raw.takeIf { it.isJsonObject }?.asJsonObject?.get("name")?.asString?.trim()?.takeIf(String::isNotEmpty)
                }
            }
        } catch (_: Exception) { /* Kategori bukan penghalang katalog utama. */ }
    }
    LaunchedEffect(tab, signedIn, cartRefreshVersion) {
        if (tab == AppTab.CART && signedIn) {
            cartLoading = true
            cartMessage = null
            try {
                val response = ApiClient.get(context).cart()
                val body = response.body()
                val cart = body?.data?.getAsJsonObject("cart")
                val rows = cart?.getAsJsonArray("items")
                if (response.isSuccessful && body?.success == true && rows != null) {
                    cartItems = rows.mapNotNull { raw ->
                        val row = raw.takeIf { it.isJsonObject }?.asJsonObject ?: return@mapNotNull null
                        CartLine(
                            id = row.get("product_id")?.asString.orEmpty(), name = row.get("name")?.asString.orEmpty(),
                            variant = row.get("variant")?.asString.orEmpty(), quantity = row.get("quantity")?.asInt ?: 1,
                            price = row.get("unit_price")?.asLong ?: 0L, image = row.get("image_url")?.asString
                        )
                    }
                    cartSubtotal = cart.get("subtotal")?.asLong ?: 0L
                } else cartMessage = body?.error?.message ?: "Keranjang belum dapat dimuat."
            } catch (_: Exception) { cartMessage = "Koneksi belum tersedia. Coba muat ulang keranjang." }
            finally { cartLoading = false }
        }
    }
    val requestNotificationPermission = rememberNotificationPermissionRequester { granted ->
        if (granted) {
            if (signedIn) {
                scope.launch {
                    val registration = PushDeviceRegistration(context)
                    val result = registration.enableAndRegister()
                    notificationsEnabled = registration.isOptedIn()
                    if (result.isFailure) PushDeviceRegistration.enqueueRetry(context)
                    notificationMessage = if (result.isSuccess && result.getOrNull() == true) "Notifikasi pesanan telah diaktifkan." else result.exceptionOrNull()?.message ?: "Notifikasi belum dapat diaktifkan."
                }
            } else {
                val registration = PushDeviceRegistration(context)
                registration.rememberPermissionConsent()
                notificationsEnabled = registration.isOptedIn()
                notificationMessage = "Izin notifikasi sudah diberikan. Masuk ke akun lalu aktifkan pemberitahuan pesanan."
            }
        } else notificationMessage = "Izin notifikasi belum diberikan."
    }

    LaunchedEffect(Unit) {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            val preferences = context.getSharedPreferences("lunarea_notification_settings", android.content.Context.MODE_PRIVATE)
            if (!preferences.getBoolean("first_permission_prompted", false)) {
                preferences.edit().putBoolean("first_permission_prompted", true).apply()
                showNotificationIntro = true
            }
        }
    }

    if (showNotificationIntro) {
        AlertDialog(
            onDismissRequest = { showNotificationIntro = false },
            icon = { Icon(painterResource(R.drawable.ic_dialog_info), contentDescription = null) },
            title = { Text("Tetap terhubung dengan pesanan") },
            text = { Text("Izinkan notifikasi agar Anda mendapat kabar saat status pesanan atau pengiriman berubah. Pengaturan ini dapat diubah kapan saja.") },
            confirmButton = {
                Button(onClick = { showNotificationIntro = false; requestNotificationPermission() }, shape = RoundedCornerShape(12.dp)) {
                    Text("Lanjutkan")
                }
            },
            dismissButton = { TextButton(onClick = { showNotificationIntro = false }) { Text("Nanti saja") } },
            shape = RoundedCornerShape(24.dp)
        )
    }

    LaunchedEffect(signedIn) {
        val registration = PushDeviceRegistration(context)
        if (signedIn && Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU &&
            registration.notificationsAllowed() && !registration.isExplicitlyDisabled()
        ) {
            // Also repairs installs that granted Android permission in an earlier app build
            // but never completed server-side device registration.
            registration.rememberPermissionConsent()
            notificationsEnabled = registration.isOptedIn()
            val result = registration.registerAfterOptIn()
            if (result.isFailure) PushDeviceRegistration.enqueueRetry(context)
            else if (result.getOrNull() == true) notificationMessage = "Notifikasi pesanan telah terhubung ke perangkat ini."
        }
    }

    LaunchedEffect(signedIn, notificationsRefreshVersion) {
        if (!signedIn) {
            unreadNotificationCount = 0
            return@LaunchedEffect
        }
        runCatching { ApiClient.get(context).notifications() }
            .onSuccess { response ->
                val data = response.body()?.data
                if (response.isSuccessful && response.body()?.success == true && data != null) {
                    unreadNotificationCount = data.get("unread_count")?.asInt?.coerceAtLeast(0) ?: 0
                }
            }
    }

    BackHandler(enabled = notificationInboxOpen || tab != AppTab.HOME || accountSection != AccountSection.MENU) {
        when {
            notificationInboxOpen -> { notificationInboxOpen = false; notificationsRefreshVersion++ }
            tab == AppTab.CHECKOUT -> tab = AppTab.CART
            tab == AppTab.ACCOUNT && accountSection != AccountSection.MENU -> accountSection = AccountSection.MENU
            tab != AppTab.HOME -> { tab = AppTab.HOME; accountSection = AccountSection.MENU }
            else -> Unit
        }
    }

    Scaffold(
        containerColor = MaterialTheme.colorScheme.background,
        bottomBar = {
            if (!detailOpen) NavigationBar(
                containerColor = MaterialTheme.colorScheme.surface,
                tonalElevation = 0.dp
            ) {
                listOf(AppTab.HOME, AppTab.CATALOG, AppTab.CART, AppTab.ACCOUNT).forEach { item ->
                    NavigationBarItem(
                        selected = tab == item,
                        onClick = {
                            notificationInboxOpen = false
                            tab = item
                            // The checkout reachable from Account is a nested account
                            // section; selecting the already-active Account tab must
                            // return to the account menu instead of doing nothing.
                            if (item == AppTab.ACCOUNT) accountSection = AccountSection.MENU
                        },
                        colors = NavigationBarItemDefaults.colors(
                            indicatorColor = MaterialTheme.colorScheme.primaryContainer,
                            selectedIconColor = LunareaGreenText,
                            selectedTextColor = LunareaGreenText,
                            unselectedIconColor = MaterialTheme.colorScheme.onSurfaceVariant,
                            unselectedTextColor = MaterialTheme.colorScheme.onSurfaceVariant
                        ),
                        icon = {
                            BadgedBox(badge = {
                                if (item == AppTab.CART && cartItems.sumOf { it.quantity } > 0) {
                                    Badge(containerColor = MaterialTheme.colorScheme.secondary, contentColor = MaterialTheme.colorScheme.onSecondary) {
                                        Text(cartItems.sumOf { it.quantity }.coerceAtMost(99).toString())
                                    }
                                }
                            }) {
                                Icon(
                                    painter = painterResource(when (item) {
                                        AppTab.HOME -> R.drawable.ic_nav_home
                                        AppTab.CATALOG -> R.drawable.ic_nav_catalog
                                        AppTab.CART -> R.drawable.ic_nav_cart
                                        else -> R.drawable.ic_nav_account
                                    }),
                                    contentDescription = item.label,
                                    modifier = Modifier.size(20.dp)
                                )
                            }
                        },
                        label = { Text(item.label, maxLines = 1, fontSize = 10.sp, lineHeight = 12.sp, fontWeight = FontWeight.Medium) },
                        alwaysShowLabel = true
                    )
                }
            }
        }
    ) { insets ->
        if (notificationInboxOpen) {
            NotificationInboxScreen(
                modifier = Modifier.padding(insets),
                signedIn = signedIn,
                refreshVersion = notificationsRefreshVersion,
                onRefresh = { notificationsRefreshVersion++ },
                onBack = { notificationInboxOpen = false; notificationsRefreshVersion++ },
                onLogin = { notificationInboxOpen = false; tab = AppTab.ACCOUNT; accountSection = AccountSection.MENU },
                onUnreadCount = { unreadNotificationCount = it }
            )
        } else when (tab) {
            AppTab.HOME, AppTab.CATALOG -> CatalogScreen(
                modifier = Modifier.padding(insets), tab = tab, query = query, onQueryChange = { query = it.take(120) },
                signedIn = signedIn,
                unreadNotificationCount = unreadNotificationCount,
                onOpenNotifications = { notificationInboxOpen = true },
                onLoginRequired = { tab = AppTab.ACCOUNT; accountSection = AccountSection.MENU; message = "Masuk terlebih dahulu untuk menggunakan keranjang dan favorit." },
                categories = categories, selectedCategory = selectedCategory,
                onCategoryChange = { selectedCategory = it; loadProducts(query, it, 1, false) },
                products = products, loading = loading, message = message,
                canLoadMore = catalogPage < catalogPages,
                onSearch = { loadProducts(query.trim(), selectedCategory, 1, false) },
                onLoadMore = { loadProducts(query, selectedCategory, catalogPage + 1, true) },
                onRefresh = { loadProducts(query, selectedCategory, 1, false) },
                onDetailVisibility = { detailOpen = it }
            )
            AppTab.CART -> CartScreen(
                modifier = Modifier.padding(insets), signedIn = signedIn, items = cartItems,
                subtotal = cartSubtotal, loading = cartLoading, message = cartMessage,
                onLogin = { tab = AppTab.ACCOUNT; accountSection = AccountSection.MENU; message = "Masuk untuk melihat keranjang akun Anda." },
                onReload = { cartRefreshVersion++ },
                onRemove = { item ->
                    scope.launch {
                        try {
                            val payload = JsonObject().apply { addProperty("product_id", item.id); addProperty("variant", item.variant) }
                            val response = ApiClient.get(context).deleteCartItem(payload)
                            if (response.isSuccessful && response.body()?.success == true) cartRefreshVersion++
                            else cartMessage = response.body()?.error?.message ?: "Produk belum dapat dihapus."
                        } catch (_: Exception) { cartMessage = "Koneksi belum tersedia. Coba lagi." }
                    }
                },
                onUpdateQuantity = { item, quantity ->
                    scope.launch {
                        try {
                            val payload = JsonObject().apply { addProperty("product_id", item.id); addProperty("variant", item.variant); addProperty("quantity", quantity) }
                            val response = ApiClient.get(context).updateCartItem(payload)
                            if (response.isSuccessful && response.body()?.success == true) cartRefreshVersion++
                            else cartMessage = response.body()?.error?.message ?: "Jumlah belum dapat diperbarui."
                        } catch (_: Exception) { cartMessage = "Koneksi belum tersedia. Coba lagi." }
                    }
                }, onBrowse = { tab = AppTab.CATALOG }, onCheckout = {
                    if (signedIn) { tab = AppTab.ACCOUNT; accountSection = AccountSection.CHECKOUT }
                    else { tab = AppTab.ACCOUNT; accountSection = AccountSection.MENU; message = "Masuk terlebih dahulu sebelum melanjutkan ke pembayaran." }
                }
            )
            AppTab.CHECKOUT -> CheckoutScreen(Modifier.padding(insets), onBack = { tab = AppTab.CART })
            AppTab.ACCOUNT -> when (accountSection) {
                AccountSection.MENU -> AccountScreen(
                modifier = Modifier.padding(insets), signedIn = signedIn, busy = busy,
                identifier = identifier, password = password,
                onIdentifier = { identifier = it }, onPassword = { password = it },
                onLogin = {
                    scope.launch {
                        busy = true
                        val result = auth.login(identifier, password)
                        signedIn = auth.isSignedIn()
                        message = result.exceptionOrNull()?.message ?: if (result.isSuccess) "Berhasil masuk." else "Login belum berhasil."
                        if (result.isSuccess) { password = ""; loadProducts("") }
                        busy = false
                    }
                },
                onLogout = {
                    if (busy) return@AccountScreen
                    // Tear down account-specific UI immediately; network revocation
                    // continues in the background and the user sees clear feedback.
                    PushDeviceRegistration(context).stopForSignOut()
                    signedIn = false
                    unreadNotificationCount = 0
                    accountSection = AccountSection.MENU
                    cartItems = emptyList()
                    cartSubtotal = 0L
                    cartMessage = null
                    notificationMessage = null
                    notificationsEnabled = false
                    message = "Sedang keluar dari akun…"
                    scope.launch {
                        busy = true
                        val result = auth.logout()
                        message = if (result.isSuccess) "Anda telah keluar dari akun." else "Anda telah keluar. Sinkronisasi notifikasi perangkat akan diperbarui saat koneksi tersedia."
                        busy = false
                    }
                },
                onEnableNotifications = requestNotificationPermission,
                notificationsEnabled = notificationsEnabled,
                onDisableNotifications = {
                    scope.launch {
                        val result = PushDeviceRegistration(context).disableAndUnregister()
                        notificationsEnabled = false
                        notificationMessage = if (result.isSuccess) "Notifikasi pesanan dinonaktifkan." else result.exceptionOrNull()?.message ?: "Pengaturan notifikasi diperbarui di perangkat."
                    }
                },
                onOpenOrders = { accountSection = AccountSection.ORDERS },
                onOpenFavorites = { accountSection = AccountSection.FAVORITES },
                onOpenAddresses = { accountSection = AccountSection.ADDRESSES },
                onOpenProfile = { accountSection = AccountSection.PROFILE },
                onOpenSecurity = { accountSection = AccountSection.SECURITY },
                onOpenRegistration = { accountSection = AccountSection.REGISTER },
                onOpenRecovery = { accountSection = AccountSection.RECOVERY },
                onOpenVerification = { authFlowEmail = identifier; accountSection = AccountSection.VERIFY_EMAIL },
                notificationMessage = notificationMessage,
                message = message
                )
                AccountSection.ORDERS -> OrdersScreen(Modifier.padding(insets), onBack = { accountSection = AccountSection.MENU })
                AccountSection.FAVORITES -> FavoritesScreen(Modifier.padding(insets), onBack = { accountSection = AccountSection.MENU })
                AccountSection.ADDRESSES -> AddressesScreen(Modifier.padding(insets), onBack = { accountSection = AccountSection.MENU })
                AccountSection.PROFILE -> ProfileScreen(Modifier.padding(insets), onBack = { accountSection = AccountSection.MENU })
                AccountSection.SECURITY -> SecurityScreen(Modifier.padding(insets), onBack = { accountSection = AccountSection.MENU }, onSessionEnded = { accountSection = AccountSection.MENU; signedIn = false })
                AccountSection.CHECKOUT -> CheckoutScreen(Modifier.padding(insets), onBack = { accountSection = AccountSection.MENU; tab = AppTab.CART })
                AccountSection.REGISTER -> NativeRegistrationScreen(
                    Modifier.padding(insets), onBack = { accountSection = AccountSection.MENU },
                    onRegistered = { email -> authFlowEmail = email; accountSection = AccountSection.VERIFY_EMAIL }
                )
                AccountSection.VERIFY_EMAIL -> NativeVerificationScreen(
                    Modifier.padding(insets), initialEmail = authFlowEmail, onBack = { accountSection = AccountSection.MENU },
                    onVerified = { email -> identifier = email; password = ""; accountSection = AccountSection.MENU; message = "Email terverifikasi. Silakan masuk dengan akun Anda." }
                )
                AccountSection.RECOVERY -> NativeRecoveryScreen(
                    Modifier.padding(insets), onBack = { accountSection = AccountSection.MENU },
                    onCompleted = { identity -> identifier = identity; password = ""; accountSection = AccountSection.MENU; message = "Kata sandi berhasil diperbarui. Silakan masuk." }
                )
            }
        }
    }
}

@Composable
private fun NotificationInboxScreen(
    modifier: Modifier,
    signedIn: Boolean,
    refreshVersion: Int,
    onRefresh: () -> Unit,
    onBack: () -> Unit,
    onLogin: () -> Unit,
    onUnreadCount: (Int) -> Unit
) {
    val context = LocalContext.current
    var notifications by remember { mutableStateOf<List<JsonObject>>(emptyList()) }
    var loading by remember { mutableStateOf(false) }
    var error by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(signedIn, refreshVersion) {
        if (!signedIn) {
            notifications = emptyList()
            error = null
            loading = false
            onUnreadCount(0)
            return@LaunchedEffect
        }
        loading = true
        error = null
        try {
            val api = ApiClient.get(context)
            val response = api.notifications()
            val body = response.body()
            val data = body?.data
            if (!response.isSuccessful || body?.success != true || data == null) {
                error = body?.error?.message ?: "Pemberitahuan belum dapat dimuat. Coba lagi."
                return@LaunchedEffect
            }
            val rows = data.getAsJsonArray("notifications")?.mapNotNull { raw ->
                raw.takeIf { it.isJsonObject }?.asJsonObject
            }.orEmpty()
            notifications = rows
            onUnreadCount(data.get("unread_count")?.asInt?.coerceAtLeast(0) ?: 0)

            // Tandai pemberitahuan yang sudah berhasil dikirim sebagai dibaca
            // setelah inbox dibuka. Status antrean yang belum terkirim tidak diubah.
            if (rows.any { it.get("status")?.asString == "sent" }) {
                val marked = api.markNotificationsRead(JsonObject())
                if (marked.isSuccessful && marked.body()?.success == true) {
                    notifications = rows.map { row ->
                        if (row.get("status")?.asString == "sent") row.deepCopy().apply { addProperty("status", "read") } else row
                    }
                    onUnreadCount(0)
                }
            }
        } catch (_: Exception) {
            error = "Koneksi belum tersedia. Periksa internet lalu coba lagi."
        } finally {
            loading = false
        }
    }

    Column(modifier.fillMaxSize().background(MaterialTheme.colorScheme.background)) {
        Row(
            Modifier.fillMaxWidth().background(MaterialTheme.colorScheme.surface).padding(horizontal = 16.dp, vertical = 8.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            IconButton(onClick = onBack, modifier = Modifier.sizeIn(minWidth = 48.dp, minHeight = 48.dp)) {
                Icon(painterResource(R.drawable.ic_arrow_back), contentDescription = "Kembali", tint = MaterialTheme.colorScheme.onSurface)
            }
            Spacer(Modifier.width(8.dp))
            Column(Modifier.weight(1f)) {
                Text("Pemberitahuan", style = MaterialTheme.typography.titleLarge, fontWeight = FontWeight.SemiBold)
                Text("Kabar terbaru tentang akun dan pesanan Anda", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
            }
            IconButton(onClick = onRefresh, enabled = !loading, modifier = Modifier.sizeIn(minWidth = 48.dp, minHeight = 48.dp)) {
                Icon(painterResource(R.drawable.ic_refresh), contentDescription = "Muat ulang", tint = LunareaGreenText, modifier = Modifier.size(20.dp))
            }
        }

        when {
            !signedIn -> Column(
                Modifier.fillMaxSize().padding(28.dp),
                horizontalAlignment = Alignment.CenterHorizontally,
                verticalArrangement = Arrangement.Center
            ) {
                Icon(painterResource(R.drawable.ic_notifications), contentDescription = null, tint = LunareaGreenText, modifier = Modifier.size(36.dp))
                Spacer(Modifier.height(14.dp))
                Text("Masuk untuk melihat pemberitahuan", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
                Spacer(Modifier.height(6.dp))
                Text("Pembaruan pesanan dan akun akan tampil di sini.", style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                Spacer(Modifier.height(18.dp))
                Button(onClick = onLogin, shape = RoundedCornerShape(14.dp), modifier = Modifier.heightIn(min = 48.dp)) { Text("Masuk ke akun") }
            }
            loading && notifications.isEmpty() -> Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                CircularProgressIndicator(color = LunareaGreenText)
            }
            error != null && notifications.isEmpty() -> Column(
                Modifier.fillMaxSize().padding(24.dp), horizontalAlignment = Alignment.CenterHorizontally,
                verticalArrangement = Arrangement.Center
            ) {
                Text(error.orEmpty(), color = MaterialTheme.colorScheme.onSurfaceVariant)
                Spacer(Modifier.height(12.dp))
                OutlinedButton(onClick = onRefresh, modifier = Modifier.heightIn(min = 48.dp)) { Text("Coba lagi") }
            }
            notifications.isEmpty() -> Column(
                Modifier.fillMaxSize().padding(28.dp), horizontalAlignment = Alignment.CenterHorizontally,
                verticalArrangement = Arrangement.Center
            ) {
                Icon(painterResource(R.drawable.ic_notifications), contentDescription = null, tint = MaterialTheme.colorScheme.onSurfaceVariant, modifier = Modifier.size(34.dp))
                Spacer(Modifier.height(12.dp))
                Text("Belum ada pemberitahuan", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
                Spacer(Modifier.height(4.dp))
                Text("Pembaruan pesanan Anda akan muncul di sini.", style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
            }
            else -> LazyColumn(
                modifier = Modifier.fillMaxSize(),
                contentPadding = PaddingValues(horizontal = 16.dp, vertical = 14.dp),
                verticalArrangement = Arrangement.spacedBy(10.dp)
            ) {
                if (error != null) item { Text(error.orEmpty(), color = MaterialTheme.colorScheme.error, style = MaterialTheme.typography.bodySmall) }
                items(notifications, key = { it.get("id")?.asString ?: it.hashCode().toString() }) { item ->
                    val status = item.get("status")?.asString.orEmpty()
                    Row(
                        Modifier.fillMaxWidth()
                            .background(MaterialTheme.colorScheme.surface, RoundedCornerShape(16.dp))
                            .border(1.dp, MaterialTheme.colorScheme.outlineVariant.copy(alpha = 0.65f), RoundedCornerShape(16.dp))
                            .padding(16.dp),
                        verticalAlignment = Alignment.Top
                    ) {
                        Box(
                            Modifier.size(38.dp).background(MaterialTheme.colorScheme.primaryContainer, CircleShape),
                            contentAlignment = Alignment.Center
                        ) {
                            Icon(painterResource(R.drawable.ic_notifications), contentDescription = null, tint = LunareaGreenText, modifier = Modifier.size(19.dp))
                        }
                        Spacer(Modifier.width(12.dp))
                        Column(Modifier.weight(1f)) {
                            Text(item.get("title")?.asString.orEmpty().ifBlank { "Pembaruan Lunarea" }, style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.SemiBold)
                            Spacer(Modifier.height(4.dp))
                            Text(item.get("body")?.asString.orEmpty(), style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                            Spacer(Modifier.height(8.dp))
                            Text(
                                if (status == "awaiting_device") "Tersimpan di akun" else item.get("created_at")?.asString.orEmpty(),
                                style = MaterialTheme.typography.labelSmall,
                                color = MaterialTheme.colorScheme.onSurfaceVariant
                            )
                        }
                        if (status == "sent") Box(Modifier.padding(start = 8.dp, top = 6.dp).size(8.dp).background(LunareaGreenText, CircleShape))
                    }
                }
            }
        }
    }
}

@Composable
private fun AuthFlowHeader(title: String, subtitle: String, onBack: () -> Unit) {
    Column(verticalArrangement = Arrangement.spacedBy(LunareaSpacing.xs)) {
        Image(
            painter = painterResource(R.drawable.logo_lunarea),
            contentDescription = "Lunarea Furniture",
            modifier = Modifier.width(136.dp).height(16.dp),
            contentScale = ContentScale.Fit
        )
        TextButton(onClick = onBack, contentPadding = PaddingValues(horizontal = 0.dp, vertical = 2.dp)) {
            Icon(painterResource(R.drawable.ic_arrow_back), contentDescription = null, modifier = Modifier.size(18.dp))
            Spacer(Modifier.width(LunareaSpacing.xs))
            Text("Kembali", color = LunareaGreenText)
        }
        Text(title, style = MaterialTheme.typography.headlineMedium, color = MaterialTheme.colorScheme.onBackground)
        Text(subtitle, style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
    }
}

@Composable
private fun PasswordInputField(
    value: String,
    onValueChange: (String) -> Unit,
    label: String,
    modifier: Modifier = Modifier.fillMaxWidth(),
    imeAction: ImeAction = ImeAction.Done,
    supportingText: String? = null,
    isError: Boolean = false
) {
    var visible by remember(label) { mutableStateOf(false) }
    OutlinedTextField(
        value = value,
        onValueChange = onValueChange,
        modifier = modifier,
        label = { Text(label) },
        singleLine = true,
        shape = MaterialTheme.shapes.medium,
        visualTransformation = if (visible) VisualTransformation.None else PasswordVisualTransformation(),
        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password, imeAction = imeAction),
        isError = isError,
        supportingText = supportingText?.let { helper -> { Text(helper) } },
        trailingIcon = {
            TextButton(
                onClick = { visible = !visible },
                contentPadding = PaddingValues(horizontal = 8.dp, vertical = 0.dp),
                modifier = Modifier.heightIn(min = 44.dp)
            ) { Text(if (visible) "Sembunyikan" else "Lihat", style = MaterialTheme.typography.labelSmall) }
        }
    )
}

@Composable
private fun LunareaScreenHeader(title: String, onBack: () -> Unit, subtitle: String? = null) {
    Row(
        modifier = Modifier.fillMaxWidth().padding(bottom = LunareaSpacing.md),
        verticalAlignment = Alignment.CenterVertically
    ) {
        IconButton(onClick = onBack, modifier = Modifier.sizeIn(minWidth = 48.dp, minHeight = 48.dp)) {
            Icon(painterResource(R.drawable.ic_arrow_back), contentDescription = "Kembali", tint = LunareaGreenText)
        }
        Column(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(2.dp)) {
            Text(title, style = MaterialTheme.typography.headlineSmall, color = MaterialTheme.colorScheme.onBackground)
            subtitle?.let { Text(it, style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant) }
        }
    }
}

@Composable
private fun LunareaSectionTitle(title: String, subtitle: String? = null) {
    Column(verticalArrangement = Arrangement.spacedBy(LunareaSpacing.xxs)) {
        Text(title, style = MaterialTheme.typography.headlineSmall, color = MaterialTheme.colorScheme.onBackground)
        subtitle?.let { Text(it, style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant) }
    }
}

@Composable
private fun CheckoutSectionHeading(title: String, subtitle: String? = null) {
    Column(verticalArrangement = Arrangement.spacedBy(2.dp)) {
        Text(title, style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold, color = MaterialTheme.colorScheme.onBackground)
        subtitle?.let { Text(it, style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant) }
    }
}

@Composable
private fun rememberLunareaShimmerBrush(): Brush {
    val transition = rememberInfiniteTransition(label = "shimmer")
    val progress by transition.animateFloat(
        initialValue = -1f,
        targetValue = 2f,
        animationSpec = infiniteRepeatable(
            animation = tween(durationMillis = 1_150, easing = LinearEasing),
            repeatMode = RepeatMode.Restart
        ),
        label = "shimmer-progress"
    )
    val base = MaterialTheme.colorScheme.surfaceVariant.copy(alpha = .72f)
    val highlight = MaterialTheme.colorScheme.surface.copy(alpha = .95f)
    return Brush.linearGradient(
        colors = listOf(base, highlight, base),
        start = Offset(progress * 360f, 0f),
        end = Offset((progress + 1f) * 360f, 240f)
    )
}

@Composable
private fun ProductCardSkeleton() {
    val shimmer = rememberLunareaShimmerBrush()
    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(15.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        border = androidx.compose.foundation.BorderStroke(1.dp, MaterialTheme.colorScheme.outlineVariant.copy(alpha = .48f))
    ) {
        Column(Modifier.fillMaxWidth()) {
            Box(Modifier.fillMaxWidth().aspectRatio(.94f).background(MaterialTheme.colorScheme.surface)) {
                Box(Modifier.fillMaxSize().background(shimmer))
            }
            Column(Modifier.fillMaxWidth().padding(horizontal = 10.dp, vertical = 9.dp), verticalArrangement = Arrangement.spacedBy(5.dp)) {
                Box(Modifier.fillMaxWidth(.38f).height(9.dp).background(shimmer, MaterialTheme.shapes.extraSmall))
                Box(Modifier.fillMaxWidth(.86f).height(12.dp).background(shimmer, MaterialTheme.shapes.extraSmall))
                Box(Modifier.fillMaxWidth(.64f).height(12.dp).background(shimmer, MaterialTheme.shapes.extraSmall))
                Box(Modifier.fillMaxWidth(.58f).height(14.dp).background(shimmer, MaterialTheme.shapes.extraSmall))
            }
        }
    }
}

@Composable
private fun NativeRegistrationScreen(modifier: Modifier, onBack: () -> Unit, onRegistered: (String) -> Unit) {
    val context = LocalContext.current
    val scope = rememberCoroutineScope()
    var name by remember { mutableStateOf("") }
    var email by remember { mutableStateOf("") }
    var phone by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var confirmation by remember { mutableStateOf("") }
    var consent by remember { mutableStateOf(false) }
    var busy by remember { mutableStateOf(false) }
    var message by remember { mutableStateOf<String?>(null) }
    val emailValid = android.util.Patterns.EMAIL_ADDRESS.matcher(email.trim()).matches()
    val passwordValid = password.length >= 8 && password.toByteArray().size <= 72 && password.any(Char::isDigit)
    val formValid = name.trim().isNotEmpty() && emailValid && phone.filter(Char::isDigit).length >= 10 && passwordValid && confirmation == password && consent

    Column(modifier.fillMaxSize().imePadding().verticalScroll(rememberScrollState()).padding(horizontal = 20.dp, vertical = 18.dp), verticalArrangement = Arrangement.spacedBy(14.dp)) {
        AuthFlowHeader("Buat akun Lunarea", "Daftar langsung di aplikasi untuk menyimpan alamat dan memantau pesanan.", onBack)
        Card(shape = MaterialTheme.shapes.large, colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)) {
          Column(Modifier.fillMaxWidth().padding(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
            OutlinedTextField(name, { name = it.take(120) }, modifier = Modifier.fillMaxWidth(), label = { Text("Nama lengkap") }, singleLine = true, shape = MaterialTheme.shapes.medium, keyboardOptions = KeyboardOptions(capitalization = androidx.compose.ui.text.input.KeyboardCapitalization.Words, imeAction = androidx.compose.ui.text.input.ImeAction.Next))
            OutlinedTextField(email, { email = it.take(254) }, modifier = Modifier.fillMaxWidth(), label = { Text("Email aktif") }, singleLine = true, isError = email.isNotBlank() && !emailValid, supportingText = if (email.isNotBlank() && !emailValid) ({ Text("Masukkan alamat email yang valid.") }) else null, shape = MaterialTheme.shapes.medium, keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Email, imeAction = ImeAction.Next))
            OutlinedTextField(phone, { phone = it.filter { ch -> ch.isDigit() || ch == '+' }.take(18) }, modifier = Modifier.fillMaxWidth(), label = { Text("Nomor ponsel Indonesia") }, singleLine = true, supportingText = { Text("Gunakan nomor aktif, minimal 10 digit.") }, shape = MaterialTheme.shapes.medium, keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Phone, imeAction = ImeAction.Next))
            PasswordInputField(password, { password = it }, "Kata sandi", imeAction = ImeAction.Next, supportingText = "Minimal 8 karakter, mengandung angka, maksimal 72 byte.", isError = password.isNotEmpty() && !passwordValid)
            PasswordInputField(confirmation, { confirmation = it }, "Ulangi kata sandi", imeAction = ImeAction.Done, supportingText = if (confirmation.isNotEmpty() && confirmation != password) "Kata sandi belum sama." else null, isError = confirmation.isNotEmpty() && confirmation != password)
            Row(
                modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp).toggleable(value = consent, role = Role.Checkbox, onValueChange = { consent = it }),
                verticalAlignment = Alignment.CenterVertically,
                horizontalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                Checkbox(checked = consent, onCheckedChange = null)
                Text("Saya menyetujui Syarat & Ketentuan serta Kebijakan Privasi Lunarea.", style = MaterialTheme.typography.bodySmall)
            }
            message?.let { Text(it, style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.error) }
            Button(
            onClick = {
                scope.launch {
                    busy = true; message = null
                    try {
                        val payload = JsonObject().apply {
                            addProperty("name", name.trim()); addProperty("email", email.trim().lowercase(Locale.ROOT))
                            addProperty("phone", phone.trim()); addProperty("password", password)
                            addProperty("password_confirmation", confirmation); addProperty("consent", true)
                        }
                        val response = ApiClient.get(context).register(payload)
                        val body = response.body()
                        if (response.isSuccessful && body?.success == true) onRegistered(email.trim().lowercase(Locale.ROOT))
                        else message = response.customerError("Pendaftaran belum berhasil. Periksa kembali data Anda.")
                    } catch (_: Exception) { message = "Koneksi belum tersedia. Data belum terkirim; coba lagi." }
                    finally { busy = false }
                }
            },
            enabled = formValid && !busy,
            modifier = Modifier.fillMaxWidth().height(52.dp),
            shape = MaterialTheme.shapes.medium
            ) { if (busy) CircularProgressIndicator(Modifier.size(19.dp), strokeWidth = 2.dp) else Text("Daftar dan kirim kode") }
          }
        }
        Text("Kode verifikasi dikirim ke email dan berlaku selama 10 menit.", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
    }
}

@Composable
private fun NativeVerificationScreen(modifier: Modifier, initialEmail: String, onBack: () -> Unit, onVerified: (String) -> Unit) {
    val context = LocalContext.current
    val scope = rememberCoroutineScope()
    var email by remember(initialEmail) { mutableStateOf(initialEmail) }
    var code by remember { mutableStateOf("") }
    var busy by remember { mutableStateOf(false) }
    var message by remember { mutableStateOf<String?>(null) }

    Column(modifier.fillMaxSize().imePadding().verticalScroll(rememberScrollState()).padding(horizontal = 20.dp, vertical = 18.dp), verticalArrangement = Arrangement.spacedBy(14.dp)) {
        AuthFlowHeader("Verifikasi email", "Masukkan kode 6 digit yang dikirim ke email pendaftaran Anda.", onBack)
        OutlinedTextField(email, { email = it.take(254) }, modifier = Modifier.fillMaxWidth(), label = { Text("Alamat email") }, singleLine = true, shape = MaterialTheme.shapes.medium, keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Email))
        OutlinedTextField(code, { code = it.filter(Char::isDigit).take(6) }, modifier = Modifier.fillMaxWidth(), label = { Text("Kode verifikasi") }, singleLine = true, shape = MaterialTheme.shapes.medium, keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number), supportingText = { Text("Kode berlaku 10 menit.") })
        message?.let { Text(it, style = MaterialTheme.typography.bodySmall, color = if (it.contains("berhasil", true)) LunareaGreenText else MaterialTheme.colorScheme.error) }
        Button(onClick = {
            scope.launch {
                busy = true; message = null
                try {
                    val payload = JsonObject().apply { addProperty("email", email.trim().lowercase(Locale.ROOT)); addProperty("code", code) }
                    val response = ApiClient.get(context).verifyEmail(payload)
                    if (response.isSuccessful && response.body()?.success == true) onVerified(email.trim().lowercase(Locale.ROOT))
                    else message = response.customerError("Kode tidak valid atau sudah kedaluwarsa.")
                } catch (_: Exception) { message = "Verifikasi belum berhasil karena koneksi terganggu." }
                finally { busy = false }
            }
        }, enabled = !busy && email.contains('@') && code.length == 6, modifier = Modifier.fillMaxWidth().height(50.dp), shape = MaterialTheme.shapes.medium) {
            if (busy) CircularProgressIndicator(Modifier.size(18.dp), strokeWidth = 2.dp) else Text("Verifikasi email")
        }
        OutlinedButton(onClick = {
            scope.launch {
                busy = true; message = null
                try {
                    val payload = JsonObject().apply { addProperty("email", email.trim().lowercase(Locale.ROOT)) }
                    val response = ApiClient.get(context).resendVerification(payload)
                    message = if (response.isSuccessful && response.body()?.success == true) "Jika akun menunggu verifikasi dan jeda terpenuhi, kode baru akan dikirim." else response.customerError("Kode belum dapat dikirim ulang.")
                } catch (_: Exception) { message = "Koneksi belum tersedia. Coba kirim ulang nanti." }
                finally { busy = false }
            }
        }, enabled = !busy && email.contains('@'), modifier = Modifier.fillMaxWidth().height(48.dp), shape = MaterialTheme.shapes.medium) { Text("Kirim ulang kode") }
    }
}

@Composable
private fun NativeRecoveryScreen(modifier: Modifier, onBack: () -> Unit, onCompleted: (String) -> Unit) {
    val context = LocalContext.current
    val scope = rememberCoroutineScope()
    var identity by remember { mutableStateOf("") }
    var code by remember { mutableStateOf("") }
    var newPassword by remember { mutableStateOf("") }
    var confirmation by remember { mutableStateOf("") }
    var stage by remember { mutableIntStateOf(0) }
    var busy by remember { mutableStateOf(false) }
    var message by remember { mutableStateOf<String?>(null) }
    val newPasswordValid = newPassword.length >= 8 && newPassword.toByteArray().size <= 72 && newPassword.any(Char::isDigit)

    Column(modifier.fillMaxSize().imePadding().verticalScroll(rememberScrollState()).padding(horizontal = 20.dp, vertical = 18.dp), verticalArrangement = Arrangement.spacedBy(14.dp)) {
        AuthFlowHeader(if (stage == 0) "Pulihkan akun" else "Buat kata sandi baru", if (stage == 0) "Kami akan mengirim kode ke email terdaftar jika data akun cocok." else "Masukkan kode dari email dan tentukan kata sandi baru.", onBack)
        if (stage == 0) {
            OutlinedTextField(identity, { identity = it.take(254) }, modifier = Modifier.fillMaxWidth(), label = { Text("Email atau username") }, singleLine = true, shape = MaterialTheme.shapes.medium, keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Email, imeAction = androidx.compose.ui.text.input.ImeAction.Done))
        } else {
            Text("Kode dikirim untuk akun ${maskIdentity(identity)}", style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
            OutlinedTextField(code, { code = it.filter(Char::isDigit).take(6) }, modifier = Modifier.fillMaxWidth(), label = { Text("Kode verifikasi") }, singleLine = true, shape = MaterialTheme.shapes.medium, keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number))
            PasswordInputField(newPassword, { newPassword = it }, "Kata sandi baru", imeAction = ImeAction.Next, supportingText = "Minimal 8 karakter, mengandung angka, maksimal 72 byte.", isError = newPassword.isNotEmpty() && !newPasswordValid)
            PasswordInputField(confirmation, { confirmation = it }, "Ulangi kata sandi baru", supportingText = if (confirmation.isNotEmpty() && confirmation != newPassword) "Kata sandi belum sama." else null, isError = confirmation.isNotEmpty() && confirmation != newPassword)
        }
        message?.let { Text(it, style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.error) }
        Button(onClick = {
            scope.launch {
                busy = true; message = null
                try {
                    val api = ApiClient.get(context)
                    if (stage == 0) {
                        val payload = JsonObject().apply { addProperty("identity", identity.trim()) }
                        val response = api.requestPasswordReset(payload)
                        if (response.isSuccessful && response.body()?.success == true) {
                            stage = 1
                            message = "Jika data cocok dan batas permintaan terpenuhi, kode akan dikirim ke email terdaftar."
                        } else message = response.customerError("Permintaan pemulihan belum dapat diproses.")
                    } else {
                        val payload = JsonObject().apply {
                            addProperty("identity", identity.trim()); addProperty("code", code)
                            addProperty("password", newPassword); addProperty("password_confirmation", confirmation)
                        }
                        val response = api.completePasswordReset(payload)
                        if (response.isSuccessful && response.body()?.success == true) onCompleted(identity.trim())
                        else message = response.customerError("Kode tidak valid atau sandi belum dapat diubah.")
                    }
                } catch (_: Exception) { message = "Koneksi belum tersedia. Data belum terkirim; coba lagi." }
                finally { busy = false }
            }
        }, enabled = !busy && if (stage == 0) identity.trim().isNotEmpty() else code.length == 6 && newPasswordValid && confirmation == newPassword,
            modifier = Modifier.fillMaxWidth().height(50.dp), shape = MaterialTheme.shapes.medium) {
            if (busy) CircularProgressIndicator(Modifier.size(18.dp), strokeWidth = 2.dp) else Text(if (stage == 0) "Kirim kode pemulihan" else "Simpan kata sandi baru")
        }
        if (stage == 1) TextButton(enabled = !busy, onClick = {
            scope.launch {
                busy = true
                try {
                    val response = ApiClient.get(context).requestPasswordReset(JsonObject().apply { addProperty("identity", identity.trim()) })
                    message = if (response.isSuccessful && response.body()?.success == true) "Jika data cocok dan jeda permintaan terpenuhi, kode baru akan dikirim." else response.customerError("Kode belum dapat dikirim ulang.")
                } catch (_: Exception) { message = "Koneksi belum tersedia. Coba lagi nanti." }
                finally { busy = false }
            }
        }) { Text("Kirim ulang kode") }
    }
}

private fun maskIdentity(value: String): String {
    val email = value.trim()
    val at = email.indexOf('@')
    if (at <= 0) return "akun Anda"
    val name = email.substring(0, at)
    val masked = if (name.length <= 1) "*" else name.take(1) + "*".repeat(minOf(name.length - 1, 8))
    return masked + email.substring(at)
}

@Composable
private fun OrdersScreen(modifier: Modifier, onBack: () -> Unit) {
    val context = LocalContext.current
    val scope = rememberCoroutineScope()
    var rows by remember { mutableStateOf<List<com.google.gson.JsonObject>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var message by remember { mutableStateOf<String?>(null) }
    var selectedTracking by remember { mutableStateOf<com.google.gson.JsonObject?>(null) }
    var selectedOrder by remember { mutableStateOf<com.google.gson.JsonObject?>(null) }
    var selectedOrderId by remember { mutableStateOf<String?>(null) }
    var cancelOrderId by remember { mutableStateOf<String?>(null) }
    var refresh by remember { mutableIntStateOf(0) }
    var orderPage by remember { mutableIntStateOf(1) }
    var orderPages by remember { mutableIntStateOf(1) }
    val proofPicker = rememberLauncherForActivityResult(ActivityResultContracts.GetContent()) { uri: Uri? ->
        val id = selectedOrderId
        if (uri != null && id != null) scope.launch {
            try {
                val bytes = context.contentResolver.openInputStream(uri)?.use { it.readBytes() }
                if (bytes == null || bytes.size > 5 * 1024 * 1024) {
                    message = "Berkas bukti tidak terbaca atau melebihi 5 MB."
                } else {
                    val mime = context.contentResolver.getType(uri) ?: "image/jpeg"
                    val part = MultipartBody.Part.createFormData("proof", "bukti-pembayaran", bytes.toRequestBody(mime.toMediaTypeOrNull()))
                    val result = ApiClient.get(context).uploadPaymentProof(id, part)
                    message = result.body()?.error?.message ?: if (result.isSuccessful && result.body()?.success == true) "Bukti pembayaran berhasil dikirim." else "Bukti pembayaran belum dapat dikirim."
                    if (result.isSuccessful) { orderPage = 1; refresh++ }
                }
            } catch (_: Exception) { message = "Bukti pembayaran belum dapat dikirim. Coba lagi." }
        }
    }
    LaunchedEffect(refresh, orderPage) {
        loading = true
        try {
            val response = ApiClient.get(context).orders(page = orderPage, perPage = 30)
            val body = response.body()
            val list = body?.data?.getAsJsonArray("orders")
            if (response.isSuccessful && body?.success == true && list != null) {
                val loaded = list.mapNotNull { it.takeIf { item -> item.isJsonObject }?.asJsonObject }
                rows = if (orderPage == 1) loaded else rows + loaded
                orderPages = body.meta?.get("pages")?.asInt ?: orderPage
                message = if (rows.isEmpty()) "Belum ada pesanan." else null
            } else message = body?.error?.message ?: "Pesanan belum dapat dimuat."
        } catch (_: Exception) { message = "Koneksi belum tersedia. Coba muat ulang." }
        finally { loading = false }
    }
    selectedTracking?.let { tracking ->
        val live = tracking.getAsJsonObject("tracking") ?: tracking
        AlertDialog(
            onDismissRequest = { selectedTracking = null },
            icon = { Icon(painterResource(R.drawable.ic_dialog_info), contentDescription = null) },
            title = { Text("Informasi pengiriman") },
            text = {
                Column(verticalArrangement = Arrangement.spacedBy(6.dp)) {
                    Text("Status: ${live.get("status")?.asString ?: "Belum tersedia"}")
                    Text("Kurir: ${live.get("courier")?.asString?.ifBlank { "Belum tersedia" } ?: "Belum tersedia"}")
                    Text("Nomor resi: ${live.get("tracking_number")?.asString?.ifBlank { "Belum tersedia" } ?: "Belum tersedia"}")
                    live.get("live_tracking")?.takeIf { it.isJsonObject }?.asJsonObject?.get("summary")?.let { Text(it.toString()) }
                }
            }, confirmButton = { TextButton(onClick = { selectedTracking = null }) { Text("Tutup") } }, shape = RoundedCornerShape(22.dp)
        )
    }
    cancelOrderId?.let { id ->
        AlertDialog(
            onDismissRequest = { cancelOrderId = null },
            icon = { Icon(painterResource(R.drawable.ic_dialog_warning), contentDescription = null) },
            title = { Text("Batalkan pesanan?") },
            text = { Text("Kami akan memeriksa status pembayaran terlebih dahulu sebelum membatalkan pesanan $id.") },
            confirmButton = {
                TextButton(enabled = !loading, onClick = {
                    scope.launch {
                        loading = true
                        try {
                            val result = ApiClient.get(context).cancelOrder(id)
                            if (result.isSuccessful && result.body()?.success == true) {
                                message = "Pesanan berhasil dibatalkan."
                                cancelOrderId = null
                                selectedOrder = null
                                orderPage = 1
                                refresh++
                            } else message = result.body()?.error?.message ?: "Pesanan belum dapat dibatalkan. Muat ulang statusnya."
                        } catch (_: Exception) { message = "Pembatalan belum dapat dipastikan. Muat ulang pesanan." }
                        finally { loading = false }
                    }
                }) { Text("Ya, batalkan", color = MaterialTheme.colorScheme.error) }
            },
            dismissButton = { TextButton(onClick = { cancelOrderId = null }) { Text("Kembali") } },
            shape = RoundedCornerShape(22.dp)
        )
    }
    selectedOrder?.let { order ->
        val paymentInfo = order.getAsJsonObject("payment")
        val canCancel = order.get("status")?.asString.orEmpty().let(::isUnpaidOrderStatus)
        val va = paymentInfo?.getAsJsonArray("va_numbers")?.firstOrNull()?.asJsonObject
        AlertDialog(
            onDismissRequest = { selectedOrder = null },
            icon = { Icon(painterResource(R.drawable.ic_dialog_info), contentDescription = null) },
            title = { Text("Detail pesanan") },
            text = {
                Column(Modifier.heightIn(max = 400.dp).verticalScroll(rememberScrollState()), verticalArrangement = Arrangement.spacedBy(7.dp)) {
                    Text("Nomor: ${order.get("id")?.asString.orEmpty()}")
                    Text("Status pesanan: ${order.get("status")?.asString.orEmpty()}")
                    Text("Total: ${formatRupiah(order.get("amount")?.asLong ?: 0L)}", fontWeight = FontWeight.Bold)
                    Text("Pembayaran: ${paymentInfo?.get("type")?.asString ?: "Belum tersedia"}")
                    Text("Status pembayaran: ${paymentInfo?.get("status")?.asString ?: "Belum tersedia"}")
                    if (va != null) Text("${va.get("bank")?.asString.orEmpty()} · ${va.get("number")?.asString.orEmpty()}")
                    order.getAsJsonArray("items")?.forEach { item ->
                        val line = item.asJsonObject
                        Text("${line.get("name")?.asString.orEmpty()} × ${line.get("quantity")?.asInt ?: 1}")
                    }
                }
            },
            confirmButton = { TextButton(onClick = { selectedOrder = null }) { Text("Tutup") } },
            dismissButton = {
                Row {
                    TextButton(onClick = {
                        val id = order.get("id")?.asString ?: return@TextButton
                        scope.launch {
                            try {
                                val result = ApiClient.get(context).paymentStatus(id)
                                message = result.body()?.error?.message ?: "Status pembayaran diperbarui dari server."
                                if (result.isSuccessful && result.body()?.success == true) {
                                    val updated = ApiClient.get(context).order(id).body()?.data?.getAsJsonObject("order")
                                    if (updated != null) selectedOrder = updated
                                }
                            } catch (_: Exception) { message = "Status pembayaran belum dapat diperiksa." }
                        }
                    }) { Text("Periksa pembayaran") }
                    if (canCancel) TextButton(onClick = { cancelOrderId = order.get("id")?.asString }) { Text("Batalkan") }
                    TextButton(onClick = { selectedOrderId = order.get("id")?.asString; proofPicker.launch("image/*") }) { Text("Kirim bukti") }
                }
            }, shape = RoundedCornerShape(22.dp)
        )
    }
    Column(modifier.fillMaxSize().padding(horizontal = 20.dp, vertical = 18.dp)) {
        LunareaScreenHeader("Pesanan saya", onBack, "Pembayaran dan perkembangan pengiriman")
        if (loading) LinearProgressIndicator(modifier = Modifier.fillMaxWidth())
        message?.let { EmptyState(it, { orderPage = 1; refresh++ }, "Muat ulang") }
        LazyColumn(verticalArrangement = Arrangement.spacedBy(10.dp), contentPadding = PaddingValues(bottom = 20.dp)) {
            items(rows, key = { it.get("id")?.asString.orEmpty() }) { order ->
                val id = order.get("id")?.asString.orEmpty()
                Card(shape = RoundedCornerShape(16.dp), colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)) {
                    Column(Modifier.fillMaxWidth().padding(16.dp), verticalArrangement = Arrangement.spacedBy(6.dp)) {
                        Text("Pesanan $id", fontWeight = FontWeight.SemiBold)
                        Text(order.get("status")?.asString ?: "Status diperbarui", color = MaterialTheme.colorScheme.onSurfaceVariant)
                        Text(formatRupiah(order.get("amount")?.asLong ?: 0L), color = LunareaGreenText, fontWeight = FontWeight.Bold)
                        TextButton(onClick = {
                            scope.launch {
                                try {
                                    val response = ApiClient.get(context).order(id)
                                    selectedOrder = response.body()?.data?.getAsJsonObject("order")
                                    if (selectedOrder == null) message = response.body()?.error?.message ?: "Detail pesanan belum dapat dimuat."
                                } catch (_: Exception) { message = "Detail pesanan belum dapat dimuat." }
                            }
                        }) { Text("Detail & pembayaran") }
                        TextButton(onClick = {
                            scope.launch {
                                try {
                                    val result = ApiClient.get(context).tracking(id).body()?.data
                                    selectedTracking = result
                                } catch (_: Exception) { message = "Informasi pengiriman belum dapat dimuat." }
                            }
                        }) { Text("Lihat pengiriman") }
                    }
                }
            }
            if (orderPage < orderPages) item {
                OutlinedButton(
                    onClick = { if (!loading && orderPage < orderPages) orderPage++ },
                    enabled = !loading,
                    modifier = Modifier.fillMaxWidth(),
                    shape = RoundedCornerShape(14.dp)
                ) { Text(if (loading) "Memuat…" else "Tampilkan pesanan lainnya") }
            }
        }
    }
}

@Composable
private fun FavoritesScreen(modifier: Modifier, onBack: () -> Unit) {
    val context = LocalContext.current
    val scope = rememberCoroutineScope()
    var rows by remember { mutableStateOf<List<com.google.gson.JsonObject>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var message by remember { mutableStateOf<String?>(null) }
    var refresh by remember { mutableIntStateOf(0) }
    LaunchedEffect(refresh) {
        loading = true
        try {
            val response = ApiClient.get(context).wishlist()
            val body = response.body()
            val list = body?.data?.getAsJsonArray("items")
            if (response.isSuccessful && body?.success == true && list != null) {
                rows = list.mapNotNull { it.takeIf { item -> item.isJsonObject }?.asJsonObject }
                message = if (rows.isEmpty()) "Belum ada produk favorit." else null
            } else message = body?.error?.message ?: "Daftar favorit belum dapat dimuat."
        } catch (_: Exception) { message = "Koneksi belum tersedia. Coba muat ulang." }
        finally { loading = false }
    }
    Column(modifier.fillMaxSize().padding(horizontal = 20.dp, vertical = 18.dp)) {
        LunareaScreenHeader("Produk favorit", onBack, "Koleksi furnitur yang Anda simpan")
        if (loading) LinearProgressIndicator(modifier = Modifier.fillMaxWidth())
        message?.let { EmptyState(it, { refresh++ }, "Muat ulang") }
        LazyColumn(verticalArrangement = Arrangement.spacedBy(10.dp)) {
            items(rows, key = { it.get("product_id")?.asString.orEmpty() + it.get("variant")?.asString.orEmpty() }) { product ->
                val id = product.get("product_id")?.asString.orEmpty()
                val variant = product.get("variant")?.asString.orEmpty()
                Card(shape = RoundedCornerShape(16.dp), colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)) {
                    Row(Modifier.fillMaxWidth().padding(14.dp), verticalAlignment = Alignment.CenterVertically) {
                        if (product.has("image_url")) AsyncImage(model = product.get("image_url").asString, contentDescription = product.get("name")?.asString, modifier = Modifier.size(66.dp), contentScale = ContentScale.Crop)
                        Column(Modifier.weight(1f).padding(start = 10.dp)) {
                            Text(product.get("name")?.asString ?: "Produk Lunarea", fontWeight = FontWeight.SemiBold)
                            Text(formatRupiah(product.get("price")?.asLong ?: 0L), color = LunareaGreenText)
                        }
                        TextButton(onClick = {
                            scope.launch {
                                try {
                                    val payload = JsonObject().apply { addProperty("product_id", id); addProperty("variant", variant) }
                                    val response = ApiClient.get(context).deleteWishlistItem(payload)
                                    if (response.isSuccessful && response.body()?.success == true) refresh++
                                    else message = response.body()?.error?.message ?: "Produk belum dapat dihapus dari favorit."
                                } catch (_: Exception) { message = "Koneksi belum tersedia. Coba lagi." }
                            }
                        }) { Text("Hapus") }
                    }
                }
            }
        }
    }
}

@Composable
private fun AddressesScreen(modifier: Modifier, onBack: () -> Unit) {
    val context = LocalContext.current
    val scope = rememberCoroutineScope()
    var addresses by remember { mutableStateOf<List<com.google.gson.JsonObject>>(emptyList()) }
    var provinces by remember { mutableStateOf<List<RegionOption>>(emptyList()) }
    var cities by remember { mutableStateOf<List<RegionOption>>(emptyList()) }
    var districts by remember { mutableStateOf<List<RegionOption>>(emptyList()) }
    var villages by remember { mutableStateOf<List<RegionOption>>(emptyList()) }
    var province by remember { mutableStateOf<RegionOption?>(null) }
    var city by remember { mutableStateOf<RegionOption?>(null) }
    var district by remember { mutableStateOf<RegionOption?>(null) }
    var village by remember { mutableStateOf<RegionOption?>(null) }
    var loadingCities by remember { mutableStateOf(false) }
    var loadingDistricts by remember { mutableStateOf(false) }
    var loadingVillages by remember { mutableStateOf(false) }
    var cityError by remember { mutableStateOf<String?>(null) }
    var districtError by remember { mutableStateOf<String?>(null) }
    var villageError by remember { mutableStateOf<String?>(null) }
    var cityRequest by remember { mutableIntStateOf(0) }
    var districtRequest by remember { mutableIntStateOf(0) }
    var villageRequest by remember { mutableIntStateOf(0) }
    var label by remember { mutableStateOf("") }
    var recipient by remember { mutableStateOf("") }
    var phone by remember { mutableStateOf("") }
    var detail by remember { mutableStateOf("") }
    var showForm by remember { mutableStateOf(false) }
    var editingAddressId by remember { mutableStateOf<Long?>(null) }
    var addressPendingDelete by remember { mutableStateOf<com.google.gson.JsonObject?>(null) }
    var busy by remember { mutableStateOf(false) }
    var message by remember { mutableStateOf<String?>(null) }
    var refresh by remember { mutableIntStateOf(0) }

    suspend fun loadAddressRows() {
        val response = ApiClient.get(context).addresses()
        val data = response.body()?.data
        val values = data?.getAsJsonArray("addresses")
        if (response.isSuccessful && response.body()?.success == true && values != null) {
            addresses = values.mapNotNull { it.takeIf { v -> v.isJsonObject }?.asJsonObject }
        } else message = response.body()?.error?.message ?: "Alamat belum dapat dimuat."
    }
    fun options(json: com.google.gson.JsonObject?): List<RegionOption> = json?.getAsJsonArray("regions")?.mapNotNull { item ->
        item.takeIf { it.isJsonObject }?.asJsonObject?.let { row ->
            RegionOption(row.get("id")?.asInt ?: return@let null, row.get("label")?.asString.orEmpty(), row.get("kodepos")?.asString.orEmpty())
        }
    } ?: emptyList()
    fun loadCities(forProvince: RegionOption) {
        val request = ++cityRequest
        loadingCities = true; cityError = null; cities = emptyList()
        scope.launch {
            try {
                val response = ApiClient.get(context).cities(forProvince.id)
                if (request != cityRequest || province?.id != forProvince.id) return@launch
                if (response.isSuccessful && response.body()?.success == true) {
                    cities = options(response.body()?.data)
                    if (cities.isEmpty()) cityError = "Daftar kota untuk provinsi ini belum tersedia. Coba muat ulang."
                } else cityError = response.customerError("Daftar kota belum dapat dimuat. Coba muat ulang.")
            } catch (_: Exception) {
                if (request == cityRequest && province?.id == forProvince.id) cityError = "Koneksi kota belum tersedia. Periksa internet lalu coba lagi."
            } finally { if (request == cityRequest) loadingCities = false }
        }
    }
    fun loadDistricts(forCity: RegionOption) {
        val request = ++districtRequest
        loadingDistricts = true; districtError = null; districts = emptyList()
        scope.launch {
            try {
                val response = ApiClient.get(context).districts(forCity.id)
                if (request != districtRequest || city?.id != forCity.id) return@launch
                if (response.isSuccessful && response.body()?.success == true) {
                    districts = options(response.body()?.data)
                    if (districts.isEmpty()) districtError = "Daftar kecamatan belum tersedia. Coba muat ulang."
                } else districtError = response.customerError("Daftar kecamatan belum dapat dimuat. Coba muat ulang.")
            } catch (_: Exception) {
                if (request == districtRequest && city?.id == forCity.id) districtError = "Koneksi kecamatan belum tersedia. Periksa internet lalu coba lagi."
            } finally { if (request == districtRequest) loadingDistricts = false }
        }
    }
    fun loadVillages(forDistrict: RegionOption) {
        val request = ++villageRequest
        loadingVillages = true; villageError = null; villages = emptyList()
        scope.launch {
            try {
                val response = ApiClient.get(context).villages(forDistrict.id)
                if (request != villageRequest || district?.id != forDistrict.id) return@launch
                if (response.isSuccessful && response.body()?.success == true) {
                    villages = options(response.body()?.data)
                    if (villages.isEmpty()) villageError = "Daftar kelurahan belum tersedia. Coba muat ulang."
                } else villageError = response.customerError("Daftar kelurahan belum dapat dimuat. Coba muat ulang.")
            } catch (_: Exception) {
                if (request == villageRequest && district?.id == forDistrict.id) villageError = "Koneksi kelurahan belum tersedia. Periksa internet lalu coba lagi."
            } finally { if (request == villageRequest) loadingVillages = false }
        }
    }
    LaunchedEffect(refresh) {
        busy = true
        try {
            loadAddressRows()
            val response = ApiClient.get(context).provinces()
            if (response.isSuccessful && response.body()?.success == true) provinces = options(response.body()?.data)
            else message = response.customerError("Daftar provinsi belum dapat dimuat.")
        } catch (_: Exception) { message = "Koneksi belum tersedia. Coba muat ulang alamat." }
        finally { busy = false }
    }

    addressPendingDelete?.let { address ->
        AlertDialog(
            onDismissRequest = { addressPendingDelete = null },
            icon = { Icon(painterResource(R.drawable.ic_dialog_warning), contentDescription = null) },
            title = { Text("Hapus alamat?") },
            text = { Text("Alamat ${address.get("label")?.asString.orEmpty()} akan dihapus dari daftar tersimpan.") },
            confirmButton = {
                TextButton(enabled = !busy, onClick = {
                    val id = address.get("id")?.asLong ?: return@TextButton
                    scope.launch {
                        busy = true
                        try {
                            val response = ApiClient.get(context).deleteAddress(id)
                            if (response.isSuccessful && response.body()?.success == true) {
                                message = "Alamat berhasil dihapus."
                                addressPendingDelete = null
                                refresh++
                            } else message = response.body()?.error?.message ?: "Alamat belum dapat dihapus."
                        } catch (_: Exception) { message = "Koneksi belum tersedia. Coba lagi." }
                        finally { busy = false }
                    }
                }) { Text("Hapus", color = MaterialTheme.colorScheme.error) }
            },
            dismissButton = { TextButton(onClick = { addressPendingDelete = null }) { Text("Batal") } },
            shape = RoundedCornerShape(22.dp)
        )
    }

    Column(modifier.fillMaxSize().padding(horizontal = 20.dp, vertical = 16.dp)) {
        LunareaScreenHeader("Alamat tersimpan", onBack, "Atur alamat tujuan pengiriman")
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.End, verticalAlignment = Alignment.CenterVertically) {
            TextButton(onClick = { showForm = !showForm }) { Text(if (showForm) "Tutup" else "+ Tambah") }
        }
        if (busy) LinearProgressIndicator(modifier = Modifier.fillMaxWidth())
        message?.let { Text(it, color = LunareaGreenText, fontSize = 13.sp) }
        if (showForm) {
            Card(Modifier.fillMaxWidth().weight(1f, fill = false), shape = RoundedCornerShape(18.dp), colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)) {
                Column(Modifier.fillMaxWidth().padding(16.dp).verticalScroll(rememberScrollState()), verticalArrangement = Arrangement.spacedBy(9.dp)) {
                    Text(if (editingAddressId == null) "Alamat baru" else "Ubah alamat", fontWeight = FontWeight.SemiBold)
                    OutlinedTextField(label, { label = it }, modifier = Modifier.fillMaxWidth(), label = { Text("Label alamat · Rumah/Kantor") }, singleLine = true)
                    OutlinedTextField(recipient, { recipient = it }, modifier = Modifier.fillMaxWidth(), label = { Text("Nama penerima") }, singleLine = true)
                    OutlinedTextField(phone, { phone = it }, modifier = Modifier.fillMaxWidth(), label = { Text("Nomor ponsel") }, keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Phone), singleLine = true)
                    RegionSelector("Provinsi", province, provinces, provinces.isNotEmpty()) { selected ->
                        province = selected; city = null; district = null; village = null; cities = emptyList(); districts = emptyList(); villages = emptyList()
                        cityError = null; districtError = null; villageError = null
                        loadCities(selected)
                    }
                    RegionSelector("Kota/Kabupaten", city, cities, province != null && !loadingCities && cities.isNotEmpty(), loadingCities) { selected ->
                        city = selected; district = null; village = null; districts = emptyList(); villages = emptyList()
                        districtError = null; villageError = null
                        loadDistricts(selected)
                    }
                    cityError?.let { error ->
                        Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(4.dp)) {
                            Text(error, Modifier.weight(1f), color = MaterialTheme.colorScheme.error, style = MaterialTheme.typography.bodySmall)
                            TextButton(onClick = { province?.let(::loadCities) }, enabled = province != null && !loadingCities) { Text("Muat ulang") }
                        }
                    }
                    RegionSelector("Kecamatan", district, districts, city != null && !loadingDistricts && districts.isNotEmpty(), loadingDistricts) { selected ->
                        district = selected; village = null; villages = emptyList()
                        villageError = null
                        loadVillages(selected)
                    }
                    districtError?.let { error ->
                        Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(4.dp)) {
                            Text(error, Modifier.weight(1f), color = MaterialTheme.colorScheme.error, style = MaterialTheme.typography.bodySmall)
                            TextButton(onClick = { city?.let(::loadDistricts) }, enabled = city != null && !loadingDistricts) { Text("Muat ulang") }
                        }
                    }
                    RegionSelector("Kelurahan", village, villages, district != null && !loadingVillages && villages.isNotEmpty(), loadingVillages) { village = it }
                    villageError?.let { error ->
                        Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(4.dp)) {
                            Text(error, Modifier.weight(1f), color = MaterialTheme.colorScheme.error, style = MaterialTheme.typography.bodySmall)
                            TextButton(onClick = { district?.let(::loadVillages) }, enabled = district != null && !loadingVillages) { Text("Muat ulang") }
                        }
                    }
                    OutlinedTextField(detail, { detail = it }, modifier = Modifier.fillMaxWidth(), label = { Text("Nama jalan, nomor, patokan") }, minLines = 2)
                    Button(
                        enabled = !busy && label.isNotBlank() && recipient.isNotBlank() && phone.isNotBlank() && detail.isNotBlank() && province != null && city != null && district != null && village != null,
                        onClick = {
                            scope.launch {
                                busy = true
                                try {
                                    val payload = JsonObject().apply {
                                        addProperty("label", label.trim()); addProperty("recipient_name", recipient.trim())
                                        addProperty("recipient_phone", phone.trim()); addProperty("address_detail", detail.trim())
                                        addProperty("province_id", province!!.id); addProperty("city_id", city!!.id)
                                        addProperty("district_id", district!!.id); addProperty("village_id", village!!.id)
                                    }
                                    val response = if (editingAddressId == null) ApiClient.get(context).createAddress(payload)
                                    else {
                                        payload.addProperty("id", editingAddressId)
                                        ApiClient.get(context).updateAddress(payload)
                                    }
                                    if (response.isSuccessful && response.body()?.success == true) {
                                        message = if (editingAddressId == null) "Alamat berhasil disimpan." else "Perubahan alamat berhasil disimpan."
                                        showForm = false; editingAddressId = null; label = ""; recipient = ""; phone = ""; detail = ""; province = null; city = null; district = null; village = null; refresh++
                                    } else message = response.body()?.error?.message ?: "Alamat belum tersimpan."
                                } catch (_: Exception) { message = "Koneksi belum tersedia. Coba lagi." }
                                finally { busy = false }
                            }
                        }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(12.dp)
                    ) { Text(if (editingAddressId == null) "Simpan alamat" else "Simpan perubahan") }
                }
            }
        }
        LazyColumn(Modifier.weight(1f), contentPadding = PaddingValues(vertical = 10.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
            items(addresses, key = { it.get("id")?.asLong ?: 0L }) { address ->
                val id = address.get("id")?.asLong ?: 0L
                val nested = address.getAsJsonObject("address")
                Card(shape = RoundedCornerShape(16.dp), colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)) {
                    Column(Modifier.fillMaxWidth().padding(15.dp), verticalArrangement = Arrangement.spacedBy(5.dp)) {
                        Text(address.get("label")?.asString.orEmpty() + if (address.get("is_default")?.asBoolean == true) " · Utama" else "", fontWeight = FontWeight.SemiBold)
                        Text(address.get("recipient_name")?.asString.orEmpty() + " · " + address.get("recipient_phone")?.asString.orEmpty())
                        Text(nested?.get("alamat")?.asString.orEmpty(), color = MaterialTheme.colorScheme.onSurfaceVariant, fontSize = 13.sp)
                        Row {
                            TextButton(onClick = {
                                scope.launch {
                                    busy = true
                                    try {
                                        val addressData = address.getAsJsonObject("address")
                                        val pId = addressData?.get("prov_id")?.asInt ?: 0
                                        val cId = addressData?.get("kab_id")?.asInt ?: 0
                                        val dId = addressData?.get("kec_id")?.asInt ?: 0
                                        val api = ApiClient.get(context)
                                        cities = options(api.cities(pId).body()?.data)
                                        districts = options(api.districts(cId).body()?.data)
                                        villages = options(api.villages(dId).body()?.data)
                                        province = provinces.firstOrNull { it.id == pId } ?: RegionOption(pId, addressData?.get("prov")?.asString.orEmpty())
                                        city = cities.firstOrNull { it.id == cId } ?: RegionOption(cId, addressData?.get("kab")?.asString.orEmpty())
                                        district = districts.firstOrNull { it.id == dId } ?: RegionOption(dId, addressData?.get("kec")?.asString.orEmpty())
                                        val savedVillage = addressData?.get("desa")?.asString.orEmpty()
                                        val savedPostal = addressData?.get("kodepos")?.asString.orEmpty()
                                        village = villages.firstOrNull { it.label.equals(savedVillage, ignoreCase = true) && (savedPostal.isBlank() || it.postal == savedPostal) }
                                        label = address.get("label")?.asString.orEmpty()
                                        recipient = address.get("recipient_name")?.asString.orEmpty()
                                        phone = address.get("recipient_phone")?.asString.orEmpty()
                                        detail = addressData?.get("add")?.asString.orEmpty()
                                        editingAddressId = id
                                        showForm = true
                                    } catch (_: Exception) { message = "Data alamat belum dapat dimuat untuk diedit." }
                                    finally { busy = false }
                                }
                            }) { Text("Ubah") }
                            if (address.get("is_default")?.asBoolean != true) TextButton(onClick = {
                                scope.launch { try { val r = ApiClient.get(context).setDefaultAddress(id); message = r.body()?.error?.message ?: "Alamat utama diperbarui."; refresh++ } catch (_: Exception) { message = "Alamat belum dapat diperbarui." } }
                            }) { Text("Jadikan utama") }
                            TextButton(onClick = { addressPendingDelete = address }) { Text("Hapus") }
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun CheckoutScreen(modifier: Modifier, onBack: () -> Unit) {
    val context = LocalContext.current
    val scope = rememberCoroutineScope()
    var addresses by remember { mutableStateOf<List<com.google.gson.JsonObject>>(emptyList()) }
    var addressId by remember { mutableLongStateOf(0L) }
    var quotes by remember { mutableStateOf<List<ShippingOption>>(emptyList()) }
    var quote by remember { mutableStateOf<ShippingOption?>(null) }
    var shippingWeightGrams by remember { mutableIntStateOf(0) }
    var payment by remember { mutableStateOf("bca") }
    var paymentMenu by remember { mutableStateOf(false) }
    var quoteMenu by remember { mutableStateOf(false) }
    var usePoints by remember { mutableStateOf(false) }
    var availableVouchers by remember { mutableStateOf<List<com.google.gson.JsonObject>>(emptyList()) }
    var voucherClaimId by remember { mutableLongStateOf(0L) }
    var voucherMenu by remember { mutableStateOf(false) }
    var includeGlass by remember { mutableStateOf(false) }
    var preview by remember { mutableStateOf<com.google.gson.JsonObject?>(null) }
    var message by remember { mutableStateOf<String?>(null) }
    var busy by remember { mutableStateOf(false) }
    var quoteLoading by remember { mutableStateOf(false) }
    var previewLoading by remember { mutableStateOf(false) }
    var refreshQuotes by remember { mutableIntStateOf(0) }
    var quoteRequestGeneration by remember { mutableIntStateOf(0) }
    var previewRequestGeneration by remember { mutableIntStateOf(0) }
    val checkoutPrefs = remember { context.getSharedPreferences("lunarea_checkout_pending", android.content.Context.MODE_PRIVATE) }
    var idempotencyKey by remember { mutableStateOf(checkoutPrefs.getString("idempotency_key", "").orEmpty()) }
    var orderResult by remember { mutableStateOf<String?>(null) }
    var paymentInstructions by remember { mutableStateOf<com.google.gson.JsonObject?>(null) }
    var checkoutOrderId by remember { mutableStateOf<String?>(null) }
    var refreshAddress by remember { mutableIntStateOf(0) }

    LaunchedEffect(refreshAddress) {
        busy = true
        try {
            val response = ApiClient.get(context).addresses()
            val data = response.body()?.data
            val list = data?.getAsJsonArray("addresses")
            if (response.isSuccessful && response.body()?.success == true && list != null) {
                addresses = list.mapNotNull { it.takeIf { row -> row.isJsonObject }?.asJsonObject }
                val selectedStillExists = addresses.any { it.get("id")?.takeUnless { value -> value.isJsonNull }?.asLong == addressId }
                if (!selectedStillExists) {
                    addressId = addresses.firstOrNull { it.get("is_default")?.takeUnless { value -> value.isJsonNull }?.asBoolean == true }
                        ?.get("id")?.takeUnless { it.isJsonNull }?.asLong
                        ?: addresses.firstOrNull()?.get("id")?.takeUnless { it.isJsonNull }?.asLong
                        ?: 0L
                }
                if (addresses.isEmpty()) message = "Tambahkan alamat pengiriman terlebih dahulu melalui menu Akun."
            } else message = response.body()?.error?.message ?: "Alamat belum dapat dimuat."
        } catch (_: Exception) { message = "Koneksi belum tersedia. Coba lagi." }
        finally { busy = false }
    }

    LaunchedEffect(Unit) {
        try {
            val response = ApiClient.get(context).availableVouchers()
            val rows = response.body()?.data?.getAsJsonArray("vouchers")
            if (response.isSuccessful && response.body()?.success == true && rows != null) {
                availableVouchers = rows.mapNotNull { item -> if (item.isJsonObject) item.asJsonObject else null }
            }
        } catch (_: Exception) { /* Voucher remains optional; checkout still validates server-side. */ }
    }

    LaunchedEffect(addressId, refreshQuotes) {
        val requestId = ++quoteRequestGeneration
        quotes = emptyList()
        quote = null
        preview = null
        shippingWeightGrams = 0
        if (addressId <= 0) {
            quoteLoading = false
            message = null
            return@LaunchedEffect
        }
        quoteLoading = true; quotes = emptyList(); quote = null; preview = null; idempotencyKey = ""; message = null
        try {
            val response = ApiClient.get(context).shippingQuotes(JsonObject().apply { addProperty("address_id", addressId) })
            if (requestId != quoteRequestGeneration) return@LaunchedEffect
            val data = response.body()?.data
            val list = data?.getAsJsonArray("quotes")
            if (response.isSuccessful && response.body()?.success == true && list != null) {
                shippingWeightGrams = runCatching { data.get("weight_grams")?.asInt ?: 0 }.getOrDefault(0).coerceAtLeast(0)
                quotes = list.mapNotNull { item ->
                    val row = item.takeIf { it.isJsonObject }?.asJsonObject ?: return@mapNotNull null
                    val token = row.stringOrNull("token").orEmpty()
                    val cost = row.longOrNull("cost") ?: return@mapNotNull null
                    if (token.isBlank() || cost < 0L) null else ShippingOption(
                        token = token,
                        courier = row.stringOrNull("courier_name").orEmpty().ifBlank { "Pengiriman" },
                        service = row.stringOrNull("service_name").orEmpty().ifBlank { "Layanan reguler" },
                        cost = cost,
                        eta = row.stringOrNull("etd")
                    )
                }
                if (quotes.isEmpty()) message = "Pilihan pengiriman belum tersedia untuk alamat ini."
            } else message = response.customerError("Pilihan pengiriman belum dapat dimuat.")
        } catch (_: Exception) { if (requestId == quoteRequestGeneration) message = "Koneksi pengiriman belum tersedia. Periksa internet lalu coba muat ulang." }
        finally { if (requestId == quoteRequestGeneration) quoteLoading = false }
    }

    LaunchedEffect(addressId, quote?.token, payment, usePoints, includeGlass, voucherClaimId) {
        val requestId = ++previewRequestGeneration
        val selected = quote
        if (selected == null || addressId <= 0) {
            preview = null
            previewLoading = false
            return@LaunchedEffect
        }
        preview = null
        previewLoading = true
        message = null
        try {
            delay(180)
            val payload = JsonObject().apply {
                addProperty("address_id", addressId); addProperty("shipping_quote_token", selected.token); addProperty("payment_method", payment)
                addProperty("use_points", usePoints); addProperty("include_glass", includeGlass)
                if (voucherClaimId > 0) addProperty("voucher_claim_id", voucherClaimId)
            }
            val response = ApiClient.get(context).checkoutPreview(payload)
            if (requestId != previewRequestGeneration) return@LaunchedEffect
            val data = response.body()?.data?.getAsJsonObject("checkout")
            if (response.isSuccessful && response.body()?.success == true && data != null) preview = data
            else message = response.customerError("Ringkasan pembayaran belum tersedia.")
        } catch (_: Exception) { if (requestId == previewRequestGeneration) message = "Ringkasan pembayaran belum dapat dimuat. Periksa koneksi lalu coba lagi." }
        finally { if (requestId == previewRequestGeneration) previewLoading = false }
    }

    paymentInstructions?.let { instructions ->
        val virtualAccounts = instructions.get("va_numbers")?.takeIf { it.isJsonArray }?.asJsonArray
        val paymentActions = instructions.get("actions")?.takeIf { it.isJsonArray }?.asJsonArray
        AlertDialog(
            onDismissRequest = { paymentInstructions = null },
            icon = { Icon(painterResource(R.drawable.ic_dialog_info), contentDescription = null) },
            title = { Text("Pesanan berhasil dibuat") },
            text = {
                Column(Modifier.heightIn(max = 420.dp).verticalScroll(rememberScrollState()), verticalArrangement = Arrangement.spacedBy(10.dp)) {
                    Text("Nomor pesanan: ${checkoutOrderId.orEmpty()}", fontWeight = FontWeight.SemiBold)
                    Text("Metode: ${instructions.get("type")?.asString?.uppercase().orEmpty()}")
                    Text("Status pembayaran: ${instructions.get("transaction_status")?.asString ?: "Menunggu pembayaran"}")
                    virtualAccounts?.forEach { raw ->
                        if (raw.isJsonObject) {
                            val va = raw.asJsonObject
                            Text("${va.get("bank")?.asString?.uppercase().orEmpty()} · ${va.get("va_number")?.asString.orEmpty()}", fontWeight = FontWeight.Medium)
                        }
                    }
                    listOf("payment_code", "bill_key", "biller_code").forEach { field ->
                        instructions.get(field)?.takeIf { !it.isJsonNull }?.asString?.takeIf(String::isNotBlank)?.let { value ->
                            Text("${field.replace('_', ' ').replaceFirstChar(Char::uppercase)}: $value", fontWeight = FontWeight.Medium)
                        }
                    }
                    instructions.get("expiry_time")?.takeIf { !it.isJsonNull }?.asString?.let { Text("Batas pembayaran: $it", color = MaterialTheme.colorScheme.onSurfaceVariant, fontSize = 12.sp) }
                    paymentActions?.forEach { raw ->
                        if (raw.isJsonObject) {
                            val action = raw.asJsonObject
                            val url = action.get("url")?.asString.orEmpty()
                            val label = action.get("name")?.asString?.replace('_', ' ')?.replaceFirstChar(Char::uppercase) ?: "Buka pembayaran"
                            if (url.startsWith("https://", ignoreCase = true)) {
                                OutlinedButton(onClick = { openWebsite(context, url) }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(12.dp)) { Text(label) }
                            }
                        }
                    }
                    Text("Instruksi lengkap dapat dilihat kembali pada menu Pesanan saya.", color = MaterialTheme.colorScheme.onSurfaceVariant, fontSize = 12.sp)
                }
            },
            confirmButton = { TextButton(onClick = { paymentInstructions = null }) { Text("Mengerti") } },
            shape = RoundedCornerShape(22.dp)
        )
    }

    Column(modifier.fillMaxSize().background(MaterialTheme.colorScheme.background)) {
        Column(Modifier.fillMaxWidth().background(MaterialTheme.colorScheme.surface).padding(horizontal = 20.dp, vertical = 10.dp)) {
            LunareaScreenHeader("Pengiriman & pembayaran", onBack, "Tinjau alamat, ongkir, dan total pesanan")
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp), verticalAlignment = Alignment.CenterVertically) {
                Surface(shape = CircleShape, color = MaterialTheme.colorScheme.primaryContainer) {
                    Text("1", Modifier.padding(horizontal = 10.dp, vertical = 5.dp), color = MaterialTheme.colorScheme.onPrimaryContainer, style = MaterialTheme.typography.labelMedium)
                }
                Text("Alamat", style = MaterialTheme.typography.labelMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                HorizontalDivider(Modifier.weight(1f), color = MaterialTheme.colorScheme.outlineVariant)
                Surface(shape = CircleShape, color = MaterialTheme.colorScheme.primaryContainer) {
                    Text("2", Modifier.padding(horizontal = 10.dp, vertical = 5.dp), color = MaterialTheme.colorScheme.onPrimaryContainer, style = MaterialTheme.typography.labelMedium)
                }
                Text("Pengiriman", style = MaterialTheme.typography.labelMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                HorizontalDivider(Modifier.weight(1f), color = MaterialTheme.colorScheme.outlineVariant)
                Surface(shape = CircleShape, color = MaterialTheme.colorScheme.primaryContainer) {
                    Text("3", Modifier.padding(horizontal = 10.dp, vertical = 5.dp), color = MaterialTheme.colorScheme.onPrimaryContainer, style = MaterialTheme.typography.labelMedium)
                }
                Text("Bayar", style = MaterialTheme.typography.labelMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
            }
        }
        LazyColumn(Modifier.weight(1f).padding(horizontal = 16.dp), verticalArrangement = Arrangement.spacedBy(10.dp), contentPadding = PaddingValues(top = 14.dp, bottom = 16.dp)) {
            item {
                CheckoutSectionHeading("Alamat penerima")
                if (addresses.isEmpty()) {
                    Surface(shape = RoundedCornerShape(16.dp), color = MaterialTheme.colorScheme.surface) {
                        Column(Modifier.fillMaxWidth().padding(16.dp), verticalArrangement = Arrangement.spacedBy(4.dp)) {
                            Text(if (busy) "Memuat alamat…" else "Belum ada alamat pengiriman", style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.SemiBold)
                            Text("Tambahkan alamat tersimpan melalui menu Akun untuk melihat pilihan pengiriman.", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                            TextButton(onClick = { refreshAddress++ }, enabled = !busy) { Text("Muat ulang alamat") }
                        }
                    }
                }
                else {
                    val selectedAddress = addresses.firstOrNull { (it.get("id")?.asLong ?: 0L) == addressId }
                    Box {
                        Surface(
                            modifier = Modifier.fillMaxWidth().clickable { quoteMenu = true },
                            shape = RoundedCornerShape(16.dp), color = MaterialTheme.colorScheme.surface,
                            border = androidx.compose.foundation.BorderStroke(1.dp, MaterialTheme.colorScheme.outlineVariant)
                        ) {
                            Row(Modifier.padding(15.dp), verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                                Surface(shape = CircleShape, color = MaterialTheme.colorScheme.secondaryContainer, modifier = Modifier.size(40.dp)) {
                                    Box(contentAlignment = Alignment.Center) { Text("⌖", color = MaterialTheme.colorScheme.onSecondaryContainer, style = MaterialTheme.typography.titleMedium) }
                                }
                                Column(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(3.dp)) {
                                    Text(selectedAddress?.get("label")?.asString?.ifBlank { "Alamat pengiriman" } ?: "Pilih alamat", style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.SemiBold)
                                    Text(selectedAddress?.let { "${it.get("recipient_name")?.asString.orEmpty()} · ${it.get("recipient_phone")?.asString.orEmpty()}" } ?: "Pilih alamat tersimpan", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                                    val savedRegion = selectedAddress?.getAsJsonObject("address")
                                    Text(savedRegion?.let { listOf("add", "desa", "kec", "kab", "prov", "kodepos").mapNotNull { key -> it.get(key)?.takeUnless { value -> value.isJsonNull }?.asString?.takeIf(String::isNotBlank) }.joinToString(", ") }?.takeIf(String::isNotBlank) ?: "Ketuk untuk memilih alamat", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant, maxLines = 2)
                                }
                                Text("Ubah", color = MaterialTheme.colorScheme.primary, style = MaterialTheme.typography.labelLarge, fontWeight = FontWeight.SemiBold)
                            }
                        }
                        DropdownMenu(expanded = quoteMenu, onDismissRequest = { quoteMenu = false }, modifier = Modifier.fillMaxWidth(.92f).heightIn(max = 360.dp)) {
                            addresses.forEach { addr -> DropdownMenuItem(text = { Text("${addr.get("label")?.asString.orEmpty()} · ${addr.get("recipient_name")?.asString.orEmpty()}") }, onClick = { addressId = addr.get("id")?.asLong ?: 0L; quoteMenu = false }) }
                        }
                    }
                }
            }
            item {
                CheckoutSectionHeading("Metode pengiriman", "Tarif mengikuti keranjang dan alamat yang dipilih")
                if (quoteLoading) {
                    LinearProgressIndicator(Modifier.fillMaxWidth(), color = MaterialTheme.colorScheme.primary)
                    Text("Menghitung pilihan pengiriman…", Modifier.padding(top = 6.dp), style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                }
                if (quotes.isNotEmpty()) {
                    if (shippingWeightGrams > 0) {
                        Text(
                            "Berat dasar perhitungan ongkir: ${"%.2f".format(Locale("id", "ID"), shippingWeightGrams / 1000.0)} kg",
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                        Text(
                            "Tarif otomatis menggunakan berat. Ukuran produk ditampilkan sebagai informasi dan belum menjadi dasar tarif.",
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    }
                    Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
                        quotes.forEach { option ->
                            val selected = quote?.token == option.token
                            val selectionColor by animateColorAsState(
                                targetValue = if (selected) MaterialTheme.colorScheme.primaryContainer.copy(alpha = .48f) else MaterialTheme.colorScheme.surface,
                                animationSpec = tween(durationMillis = 180),
                                label = "shipping-option-selection"
                            )
                            Surface(
                                onClick = { quote = option },
                                shape = RoundedCornerShape(14.dp), color = selectionColor,
                                border = androidx.compose.foundation.BorderStroke(
                                    1.dp,
                                    if (selected) MaterialTheme.colorScheme.primary.copy(alpha = .6f) else MaterialTheme.colorScheme.outlineVariant
                                )
                            ) {
                                Row(Modifier.fillMaxWidth().padding(horizontal = 14.dp, vertical = 13.dp), horizontalArrangement = Arrangement.spacedBy(12.dp), verticalAlignment = Alignment.CenterVertically) {
                                    RadioButton(selected = selected, onClick = { quote = option }, modifier = Modifier.size(24.dp))
                                    Column(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(3.dp)) {
                                        Text(option.courier.ifBlank { "Pengiriman" }, style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.SemiBold)
                                        Text(option.service + (option.eta?.takeIf { it.isNotBlank() }?.let { " · $it" } ?: ""), style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                                    }
                                    Text(if (option.cost == 0L) "Gratis" else formatRupiah(option.cost), style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.Bold, color = if (option.cost == 0L) MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.onSurface)
                                }
                            }
                        }
                    }
                    TextButton(
                        onClick = { refreshQuotes++ },
                        enabled = !quoteLoading,
                        modifier = Modifier.fillMaxWidth().heightIn(min = 44.dp)
                    ) { Text("Perbarui pilihan pengiriman", fontSize = 12.sp) }
                } else if (!quoteLoading) {
                    Surface(shape = RoundedCornerShape(14.dp), color = MaterialTheme.colorScheme.surface) {
                        Column(Modifier.fillMaxWidth().padding(15.dp), verticalArrangement = Arrangement.spacedBy(5.dp)) {
                            Text(if (addressId <= 0) "Pilih alamat untuk melihat tarif" else "Pilihan pengiriman belum tersedia", style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.SemiBold)
                            Text(message ?: "Periksa alamat dan coba perbarui pilihan pengiriman.", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                            TextButton(onClick = { refreshQuotes++ }, enabled = addressId > 0, modifier = Modifier.heightIn(min = 44.dp)) { Text("Coba lagi") }
                        }
                    }
                }
            }
            item {
                CheckoutSectionHeading("Metode pembayaran")
                Box {
                    OutlinedButton(onClick = { paymentMenu = true }, modifier = Modifier.fillMaxWidth().heightIn(min = 52.dp), shape = RoundedCornerShape(12.dp)) {
                        Text(nativePaymentMethods.firstOrNull { it.first == payment }?.second ?: "Pilih metode pembayaran", modifier = Modifier.weight(1f))
                        Icon(painterResource(R.drawable.ic_expand_more), contentDescription = null)
                    }
                    DropdownMenu(expanded = paymentMenu, onDismissRequest = { paymentMenu = false }) {
                        nativePaymentMethods.forEach { (method, label) ->
                            DropdownMenuItem(
                                text = { Text(label) },
                                onClick = { payment = method; paymentMenu = false }
                            )
                        }
                    }
                }
            }
            if (availableVouchers.isNotEmpty()) item {
                CheckoutSectionHeading("Voucher", "Opsional")
                Box {
                    OutlinedButton(onClick = { voucherMenu = true }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(12.dp)) {
                        val selectedVoucher = availableVouchers.firstOrNull { (it.get("claim_id")?.asLong ?: 0L) == voucherClaimId }
                        Text(selectedVoucher?.get("name")?.asString ?: "Pilih voucher (opsional)", modifier = Modifier.weight(1f))
                        Icon(painterResource(R.drawable.ic_expand_more), contentDescription = null)
                    }
                    DropdownMenu(expanded = voucherMenu, onDismissRequest = { voucherMenu = false }, modifier = Modifier.fillMaxWidth(.9f).heightIn(max = 320.dp)) {
                        DropdownMenuItem(text = { Text("Tanpa voucher") }, onClick = { voucherClaimId = 0L; voucherMenu = false })
                        availableVouchers.forEach { voucher ->
                            val claimId = voucher.get("claim_id")?.asLong ?: 0L
                            val nominal = voucher.get("nominal")?.asLong ?: 0L
                            val unit = voucher.get("unit")?.asString.orEmpty()
                            val amount = if (unit == "persen") "$nominal%" else formatRupiah(nominal)
                            DropdownMenuItem(text = { Text("${voucher.get("name")?.asString.orEmpty()} · $amount") }, onClick = { voucherClaimId = claimId; voucherMenu = false })
                        }
                    }
                }
            }
            item { Row(verticalAlignment = Alignment.CenterVertically) { Checkbox(usePoints, { usePoints = it }); Text("Gunakan Luna Point") } }
            item { Row(verticalAlignment = Alignment.CenterVertically) { Checkbox(includeGlass, { includeGlass = it }); Text("Tambahkan proteksi kaca jika tersedia") } }
            item {
                if (previewLoading) {
                    Row(Modifier.fillMaxWidth().padding(vertical = 10.dp), horizontalArrangement = Arrangement.spacedBy(10.dp), verticalAlignment = Alignment.CenterVertically) {
                        CircularProgressIndicator(Modifier.size(18.dp), strokeWidth = 2.dp)
                        Text("Memperbarui total pesanan…", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                    }
                }
                preview?.let { summary ->
                    Surface(shape = RoundedCornerShape(16.dp), color = MaterialTheme.colorScheme.surface) {
                        Column(Modifier.fillMaxWidth().padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                            CheckoutSectionHeading("Ringkasan pesanan")
                            summary.getAsJsonArray("items")?.forEach { raw ->
                                if (raw.isJsonObject) {
                                    val item = raw.asJsonObject
                                    val itemName = item.get("name")?.takeUnless { it.isJsonNull }?.asString.orEmpty()
                                    val variant = item.get("variant")?.takeUnless { it.isJsonNull }?.asString.orEmpty()
                                    val qty = item.get("quantity")?.takeUnless { it.isJsonNull }?.asInt ?: 1
                                    val weight = item.get("weight_kg")?.takeUnless { it.isJsonNull }?.asDouble ?: 0.0
                                    val dimensions = item.get("dimensions")?.takeUnless { it.isJsonNull }?.asString.orEmpty()
                                    Column(verticalArrangement = Arrangement.spacedBy(2.dp)) {
                                        Text("$itemName${variant.takeIf(String::isNotBlank)?.let { " · $it" }.orEmpty()} × $qty", style = MaterialTheme.typography.bodyMedium, fontWeight = FontWeight.Medium)
                                        Text("Berat ${"%.2f".format(Locale("id", "ID"), weight)} kg/unit${dimensions.takeIf(String::isNotBlank)?.let { " · Ukuran $it cm" }.orEmpty()}", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                                    }
                                }
                            }
                            HorizontalDivider(color = MaterialTheme.colorScheme.outlineVariant)
                            Text("Subtotal produk: ${formatRupiah(summary.get("subtotal")?.asLong ?: 0L)}")
                            summary.get("voucher_discount")?.asLong?.takeIf { it > 0 }?.let { Text("Diskon voucher: −${formatRupiah(it)}") }
                            summary.get("points_applied")?.asLong?.takeIf { it > 0 }?.let { Text("Luna Point: −${formatRupiah(it)}") }
                            summary.getAsJsonObject("glass_service")?.get("amount")?.asLong?.takeIf { it > 0 }?.let { Text("Proteksi kaca: ${formatRupiah(it)}") }
                            val shippingSummary = summary.getAsJsonObject("shipping")
                            Text("Pengiriman${shippingSummary?.get("courier")?.takeUnless { it.isJsonNull }?.asString?.takeIf(String::isNotBlank)?.let { " · $it" }.orEmpty()}: ${formatRupiah(shippingSummary?.get("cost")?.asLong ?: 0L)}")
                            Text("Biaya administrasi: ${formatRupiah(summary.get("admin_fee")?.asLong ?: 0L)}")
                            HorizontalDivider()
                            Text("Total: ${formatRupiah(summary.get("total")?.asLong ?: 0L)}", fontSize = 18.sp, fontWeight = FontWeight.Bold, color = LunareaGreenText)
                        }
                    }
                }
                message?.let { Text(it, color = MaterialTheme.colorScheme.error, fontSize = 13.sp) }
                orderResult?.let { Text(it, color = LunareaGreenText, fontWeight = FontWeight.SemiBold) }
            }
        }
        Surface(color = MaterialTheme.colorScheme.surface, shadowElevation = 4.dp) {
          Column(Modifier.fillMaxWidth().navigationBarsPadding().padding(horizontal = 16.dp, vertical = 10.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
            preview?.let { summary ->
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                    Text("Total pembayaran", style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                    Text(formatRupiah(summary.get("total")?.asLong ?: 0L), style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.Bold, color = MaterialTheme.colorScheme.primary)
                }
            }
            Button(
            enabled = preview != null && !previewLoading && !busy && quote != null,
            onClick = {
                if (preview == null) return@Button
                val signature = checkoutRequestSignature(addressId, quote!!.token, payment, usePoints, includeGlass, voucherClaimId)
                val savedSignature = checkoutPrefs.getString("request_signature", "")
                if (idempotencyKey.isBlank() || savedSignature != signature) idempotencyKey = java.util.UUID.randomUUID().toString()
                val key = idempotencyKey
                checkoutPrefs.edit().putString("idempotency_key", key).putString("request_signature", signature).apply()
                busy = true; orderResult = null
                scope.launch {
                    try {
                        val payload = JsonObject().apply {
                            addProperty("address_id", addressId); addProperty("shipping_quote_token", quote!!.token); addProperty("payment_method", payment)
                            addProperty("use_points", usePoints); addProperty("include_glass", includeGlass)
                            if (voucherClaimId > 0) addProperty("voucher_claim_id", voucherClaimId)
                        }
                        val response = ApiClient.get(context).createOrder(payload, key)
                        val data = response.body()?.data?.getAsJsonObject("checkout")
                        if (response.isSuccessful && response.body()?.success == true) {
                            val order = data?.getAsJsonObject("order")
                            val orderId = order?.get("id")?.asString ?: data?.get("order_id")?.asString
                            checkoutOrderId = orderId
                            orderResult = if (response.code() == 202) "Pesanan $orderId sedang dipastikan. Periksa menu Pesanan saya; jangan buat pembayaran baru." else "Pesanan $orderId berhasil dibuat. Lihat instruksi pembayaran pada detail pesanan."
                            if (response.code() != 202) {
                                paymentInstructions = order?.get("payment")?.takeIf { it.isJsonObject }?.asJsonObject
                                checkoutPrefs.edit().remove("idempotency_key").remove("request_signature").apply()
                                idempotencyKey = ""
                            }
                        } else {
                            val errorMessage = response.customerError("Pesanan belum berhasil dibuat. Periksa Pesanan saya sebelum mencoba lagi.")
                            message = errorMessage
                            if (response.code() == 422 && Regex("ongkir|pengiriman|quote|alamat", RegexOption.IGNORE_CASE).containsMatchIn(errorMessage)) {
                                // Refresh stale delivery data before allowing another payment attempt.
                                preview = null
                                quote = null
                                refreshQuotes++
                            }
                            if (response.code() in 400..499) {
                                checkoutPrefs.edit().remove("idempotency_key").remove("request_signature").apply()
                                idempotencyKey = ""
                            }
                        }
                    } catch (_: Exception) {
                        orderResult = "Respons belum dapat dipastikan. Gunakan tombol yang sama agar kunci permintaan tetap sama; jangan buat permintaan baru."
                    } finally { busy = false }
                }
            }, modifier = Modifier.fillMaxWidth().heightIn(min = 52.dp), shape = RoundedCornerShape(14.dp)
            ) { if (busy) CircularProgressIndicator(Modifier.size(18.dp), strokeWidth = 2.dp) else Text("Buat pesanan") }
          }
        }
    }
}

@Composable
private fun RegionSelector(
    title: String,
    selected: RegionOption?,
    options: List<RegionOption>,
    enabled: Boolean,
    loading: Boolean = false,
    onSelect: (RegionOption) -> Unit
) {
    var expanded by remember { mutableStateOf(false) }
    Box(Modifier.fillMaxWidth()) {
        OutlinedButton(onClick = { expanded = true }, enabled = enabled, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(12.dp)) {
            Text(
                selected?.label ?: if (loading) "Memuat $title…" else "Pilih $title",
                modifier = Modifier.weight(1f),
                maxLines = 1,
                overflow = TextOverflow.Ellipsis
            )
            if (loading) CircularProgressIndicator(Modifier.size(16.dp), strokeWidth = 2.dp)
            else Icon(painterResource(R.drawable.ic_expand_more), contentDescription = null)
        }
        DropdownMenu(expanded = expanded, onDismissRequest = { expanded = false }, modifier = Modifier.fillMaxWidth(0.9f).heightIn(max = 300.dp)) {
            options.forEach { option -> DropdownMenuItem(text = { Text(option.label) }, onClick = { onSelect(option); expanded = false }) }
        }
    }
}

@Composable
private fun ProfileScreen(modifier: Modifier, onBack: () -> Unit) {
    val context = LocalContext.current
    val scope = rememberCoroutineScope()
    var name by remember { mutableStateOf("") }
    var phone by remember { mutableStateOf("") }
    var email by remember { mutableStateOf("") }
    var busy by remember { mutableStateOf(true) }
    var message by remember { mutableStateOf<String?>(null) }
    LaunchedEffect(Unit) {
        try {
            val response = ApiClient.get(context).profile()
            val customer = response.body()?.data?.getAsJsonObject("customer")
            if (response.isSuccessful && response.body()?.success == true && customer != null) {
                name = customer.get("name")?.asString.orEmpty(); phone = customer.get("phone")?.asString.orEmpty(); email = customer.get("email")?.asString.orEmpty()
            } else message = response.body()?.error?.message ?: "Profil belum dapat dimuat."
        } catch (_: Exception) { message = "Koneksi belum tersedia. Coba lagi." }
        finally { busy = false }
    }
    Column(modifier.fillMaxSize().padding(22.dp), verticalArrangement = Arrangement.spacedBy(13.dp)) {
        LunareaScreenHeader("Profil saya", onBack, "Kelola informasi akun Anda")
        if (busy) LinearProgressIndicator(modifier = Modifier.fillMaxWidth())
        OutlinedTextField(email, {}, modifier = Modifier.fillMaxWidth(), label = { Text("Email") }, enabled = false, singleLine = true)
        OutlinedTextField(name, { name = it }, modifier = Modifier.fillMaxWidth(), label = { Text("Nama") }, singleLine = true)
        OutlinedTextField(phone, { phone = it }, modifier = Modifier.fillMaxWidth(), label = { Text("Nomor ponsel") }, keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Phone), singleLine = true)
        Button(enabled = !busy && name.isNotBlank() && phone.isNotBlank(), onClick = {
            scope.launch {
                busy = true
                try {
                    val payload = JsonObject().apply { addProperty("name", name.trim()); addProperty("phone", phone.trim()) }
                    val response = ApiClient.get(context).updateProfile(payload)
                    message = response.body()?.error?.message ?: if (response.isSuccessful && response.body()?.success == true) "Profil berhasil diperbarui." else "Profil belum dapat disimpan."
                } catch (_: Exception) { message = "Koneksi belum tersedia. Coba lagi." }
                finally { busy = false }
            }
        }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(14.dp)) { Text("Simpan perubahan") }
        message?.let { Text(it, color = LunareaGreenText, fontSize = 13.sp) }
    }
}

@Composable
private fun SecurityScreen(modifier: Modifier, onBack: () -> Unit, onSessionEnded: () -> Unit) {
    val context = LocalContext.current
    val scope = rememberCoroutineScope()
    val auth = remember { AuthRepository(context) }
    var mode by remember { mutableStateOf("password") }
    var currentPassword by remember { mutableStateOf("") }
    var newPassword by remember { mutableStateOf("") }
    var confirmPassword by remember { mutableStateOf("") }
    var newEmail by remember { mutableStateOf("") }
    var code by remember { mutableStateOf("") }
    var emailStep by remember { mutableStateOf(false) }
    var busy by remember { mutableStateOf(false) }
    var message by remember { mutableStateOf<String?>(null) }
    Column(modifier.fillMaxSize().imePadding().verticalScroll(rememberScrollState()).padding(22.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
        LunareaScreenHeader("Keamanan akun", onBack, "Atur kata sandi atau email akun")
        Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            FilterChip(selected = mode == "password", onClick = { mode = "password"; message = null }, label = { Text("Kata sandi") })
            FilterChip(selected = mode == "email", onClick = { mode = "email"; message = null }, label = { Text("Email") })
        }
        PasswordInputField(currentPassword, { currentPassword = it }, "Kata sandi saat ini", imeAction = ImeAction.Next)
        if (mode == "password") {
            PasswordInputField(newPassword, { newPassword = it }, "Kata sandi baru", imeAction = ImeAction.Next)
            PasswordInputField(confirmPassword, { confirmPassword = it }, "Ulangi kata sandi baru", supportingText = if (confirmPassword.isNotEmpty() && confirmPassword != newPassword) "Kata sandi belum sama." else null, isError = confirmPassword.isNotEmpty() && confirmPassword != newPassword)
            Button(enabled = !busy && currentPassword.isNotBlank() && newPassword.isNotBlank() && newPassword == confirmPassword, onClick = {
                scope.launch {
                    busy = true
                    try {
                        val payload = JsonObject().apply { addProperty("current_password", currentPassword); addProperty("password", newPassword); addProperty("password_confirmation", confirmPassword) }
                        val response = ApiClient.get(context).changePassword(payload)
                        if (response.isSuccessful && response.body()?.success == true) { auth.clearLocalSession(); message = "Kata sandi diperbarui. Silakan masuk kembali."; onSessionEnded() }
                        else message = response.body()?.error?.message ?: "Kata sandi belum dapat diperbarui."
                    } catch (_: Exception) { message = "Koneksi belum tersedia. Coba lagi." }
                    finally { busy = false }
                }
            }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(14.dp)) { Text("Perbarui kata sandi") }
        } else if (!emailStep) {
            OutlinedTextField(newEmail, { newEmail = it }, modifier = Modifier.fillMaxWidth(), label = { Text("Email baru") }, keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Email), singleLine = true)
            Button(enabled = !busy && currentPassword.isNotBlank() && newEmail.contains("@"), onClick = {
                scope.launch {
                    busy = true
                    try {
                        val response = ApiClient.get(context).requestEmailChange(JsonObject().apply { addProperty("current_password", currentPassword); addProperty("new_email", newEmail.trim()) })
                        if (response.isSuccessful && response.body()?.success == true) { emailStep = true; message = "Kode konfirmasi dikirim ke email baru." }
                        else message = response.body()?.error?.message ?: "Permintaan perubahan email belum berhasil."
                    } catch (_: Exception) { message = "Koneksi belum tersedia. Coba lagi." }
                    finally { busy = false }
                }
            }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(14.dp)) { Text("Kirim kode konfirmasi") }
        } else {
            Text("Masukkan kode yang dikirim ke ${newEmail.take(1)}***${newEmail.substringAfter('@', "")}", color = MaterialTheme.colorScheme.onSurfaceVariant)
            OutlinedTextField(code, { code = it.filter(Char::isDigit).take(6) }, modifier = Modifier.fillMaxWidth(), label = { Text("Kode verifikasi") }, keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number), singleLine = true)
            Button(enabled = !busy && code.length == 6 && currentPassword.isNotBlank(), onClick = {
                scope.launch {
                    busy = true
                    try {
                        val response = ApiClient.get(context).confirmEmailChange(JsonObject().apply { addProperty("current_password", currentPassword); addProperty("verification_code", code) })
                        if (response.isSuccessful && response.body()?.success == true) { auth.clearLocalSession(); message = "Email berhasil diperbarui. Silakan masuk kembali."; onSessionEnded() }
                        else message = response.body()?.error?.message ?: "Kode belum berhasil dikonfirmasi."
                    } catch (_: Exception) { message = "Koneksi belum tersedia. Coba lagi." }
                    finally { busy = false }
                }
            }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(14.dp)) { Text("Konfirmasi perubahan email") }
        }
        if (busy) LinearProgressIndicator(modifier = Modifier.fillMaxWidth())
        message?.let { Text(it, color = LunareaGreenText, fontSize = 13.sp) }
    }
}

@Composable
private fun CartScreen(
    modifier: Modifier, signedIn: Boolean, items: List<CartLine>, subtotal: Long,
    loading: Boolean, message: String?, onReload: () -> Unit, onRemove: (CartLine) -> Unit,
    onUpdateQuantity: (CartLine, Int) -> Unit, onBrowse: () -> Unit, onCheckout: () -> Unit, onLogin: () -> Unit
) {
    Column(modifier.fillMaxSize().padding(horizontal = 20.dp, vertical = 18.dp)) {
        LunareaSectionTitle("Keranjang", "Periksa pilihan furnitur Anda sebelum melanjutkan.")
        Spacer(Modifier.height(14.dp))
        if (!signedIn) {
            Column(
                    Modifier.fillMaxWidth().padding(horizontal = 18.dp, vertical = 42.dp),
                    horizontalAlignment = Alignment.CenterHorizontally,
                    verticalArrangement = Arrangement.spacedBy(14.dp)
                ) {
                    Icon(painterResource(R.drawable.ic_nav_cart), contentDescription = null, tint = LunareaGreenText, modifier = Modifier.size(34.dp))
                    Text("Keranjang Anda", style = MaterialTheme.typography.headlineSmall, fontWeight = FontWeight.SemiBold)
                    Text(
                        "Masuk ke akun untuk melihat isi keranjang dan melanjutkan belanja.",
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        textAlign = androidx.compose.ui.text.style.TextAlign.Center,
                        lineHeight = 23.sp
                    )
                    Spacer(Modifier.height(2.dp))
                    Button(onClick = onLogin, modifier = Modifier.fillMaxWidth().heightIn(min = 52.dp), shape = RoundedCornerShape(14.dp)) { Text("Masuk") }
                    TextButton(onClick = onBrowse, modifier = Modifier.heightIn(min = 44.dp)) { Text("Jelajahi katalog", color = LunareaGreenText) }
                }
        } else if (loading) {
            LinearProgressIndicator(modifier = Modifier.fillMaxWidth(), color = MaterialTheme.colorScheme.secondary)
        } else if (message != null) {
            EmptyState(message, onReload, "Muat ulang")
        } else if (items.isEmpty()) {
            EmptyState("Keranjang Anda masih kosong.", onBrowse, "Jelajahi katalog")
        } else {
            LazyColumn(Modifier.weight(1f), contentPadding = PaddingValues(vertical = 8.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
                items(items, key = { it.id + it.variant }) { item ->
                    Card(
                        shape = MaterialTheme.shapes.medium,
                        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                        border = androidx.compose.foundation.BorderStroke(1.dp, MaterialTheme.colorScheme.outlineVariant.copy(alpha = .7f))
                    ) {
                        Column(Modifier.fillMaxWidth().padding(12.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
                            Row(verticalAlignment = Alignment.CenterVertically) {
                                Box(Modifier.size(84.dp).background(MaterialTheme.colorScheme.background, MaterialTheme.shapes.small), contentAlignment = Alignment.Center) {
                                    if (!item.image.isNullOrBlank()) AsyncImage(
                                        model = item.image, contentDescription = "Foto ${item.name}",
                                        modifier = Modifier.fillMaxSize().padding(5.dp), contentScale = ContentScale.Fit
                                    ) else Icon(painterResource(R.drawable.ic_nav_catalog), contentDescription = null, tint = LunareaGreenText)
                                }
                                Column(Modifier.weight(1f).padding(start = 12.dp), verticalArrangement = Arrangement.spacedBy(4.dp)) {
                                    Text(item.name, style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.SemiBold, maxLines = 2)
                                    Text(item.variant.ifBlank { "Varian standar" }, color = MaterialTheme.colorScheme.onSurfaceVariant, style = MaterialTheme.typography.bodySmall, maxLines = 1)
                                    Text(formatRupiah(item.price), color = LunareaGreenText, style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.Bold)
                                }
                            }
                            HorizontalDivider(color = MaterialTheme.colorScheme.outlineVariant.copy(alpha = .65f))
                            Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
                                Text("Jumlah", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                                Spacer(Modifier.weight(1f))
                                Surface(shape = MaterialTheme.shapes.small, color = MaterialTheme.colorScheme.background) {
                                    Row(verticalAlignment = Alignment.CenterVertically) {
                                        IconButton(
                                            enabled = item.quantity > 1,
                                            onClick = { onUpdateQuantity(item, item.quantity - 1) },
                                            modifier = Modifier.size(48.dp)
                                        ) { Text("−", style = MaterialTheme.typography.titleLarge, color = LunareaGreenText) }
                                        Text(item.quantity.toString(), modifier = Modifier.widthIn(min = 24.dp), style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.SemiBold)
                                        IconButton(
                                            enabled = item.quantity < 99,
                                            onClick = { if (item.quantity < 99) onUpdateQuantity(item, item.quantity + 1) },
                                            modifier = Modifier.size(48.dp)
                                        ) { Text("+", style = MaterialTheme.typography.titleLarge, color = LunareaGreenText) }
                                    }
                                }
                                Spacer(Modifier.width(6.dp))
                                TextButton(onClick = { onRemove(item) }, modifier = Modifier.heightIn(min = 48.dp)) { Text("Hapus", color = MaterialTheme.colorScheme.error) }
                            }
                        }
                    }
                }
            }
            Card(shape = MaterialTheme.shapes.medium, colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)) {
                Column(Modifier.fillMaxWidth().padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                        Text("Subtotal", style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                        Text(formatRupiah(subtotal), style = MaterialTheme.typography.titleMedium, color = LunareaGreenText, fontWeight = FontWeight.Bold)
                    }
                    Text("Ongkir dan total akhir dihitung saat checkout.", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                    Button(onClick = onCheckout, modifier = Modifier.fillMaxWidth().heightIn(min = 50.dp), shape = MaterialTheme.shapes.medium) { Text("Lanjut ke pengiriman") }
                }
            }
        }
    }
}

@Composable
private fun EmptyState(text: String, onAction: () -> Unit, action: String) {
    Card(
        modifier = Modifier.fillMaxWidth().padding(vertical = 20.dp),
        shape = MaterialTheme.shapes.large,
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)
    ) {
        Column(
            Modifier.fillMaxWidth().padding(horizontal = 24.dp, vertical = 30.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            Surface(shape = CircleShape, color = MaterialTheme.colorScheme.primaryContainer, modifier = Modifier.size(56.dp)) {
                Box(contentAlignment = Alignment.Center) {
                    Icon(painterResource(R.drawable.ic_dialog_info), contentDescription = null, tint = LunareaGreenText, modifier = Modifier.size(24.dp))
                }
            }
            Text(text, color = MaterialTheme.colorScheme.onSurfaceVariant, style = MaterialTheme.typography.bodyMedium, textAlign = androidx.compose.ui.text.style.TextAlign.Center)
            Button(onClick = onAction, modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp), shape = MaterialTheme.shapes.medium) { Text(action) }
        }
    }
}

@Composable
private fun CatalogScreen(
    modifier: Modifier, tab: AppTab, query: String, onQueryChange: (String) -> Unit,
    signedIn: Boolean, onLoginRequired: () -> Unit,
    unreadNotificationCount: Int, onOpenNotifications: () -> Unit,
    categories: List<String>, selectedCategory: String?, onCategoryChange: (String?) -> Unit,
    products: List<ProductSummary>, loading: Boolean, message: String?, canLoadMore: Boolean,
    onSearch: () -> Unit, onLoadMore: () -> Unit, onRefresh: () -> Unit,
    onDetailVisibility: (Boolean) -> Unit
) {
    val context = LocalContext.current
    val scope = rememberCoroutineScope()
    val gridState = rememberLazyGridState()
    val density = androidx.compose.ui.platform.LocalDensity.current
    val refreshThresholdPx = with(density) { 88.dp.toPx() }
    var pullDistance by remember { mutableFloatStateOf(0f) }
    var refreshTriggered by remember { mutableStateOf(false) }
    val latestLoading by rememberUpdatedState(loading)
    val latestOnRefresh by rememberUpdatedState(onRefresh)
    val pullRefreshConnection = remember(gridState, refreshThresholdPx) {
        object : NestedScrollConnection {
            override fun onPreScroll(available: Offset, source: NestedScrollSource): Offset {
                val atTop = gridState.firstVisibleItemIndex == 0 && gridState.firstVisibleItemScrollOffset == 0
                if (available.y > 0f && atTop) {
                    pullDistance = (pullDistance + available.y).coerceAtMost(refreshThresholdPx * 1.5f)
                    if (pullDistance >= refreshThresholdPx && !refreshTriggered && !latestLoading) {
                        refreshTriggered = true
                        latestOnRefresh()
                    }
                } else if (available.y < 0f) {
                    pullDistance = 0f
                    refreshTriggered = false
                }
                return Offset.Zero
            }

            override suspend fun onPostFling(consumed: Velocity, available: Velocity): Velocity {
                pullDistance = 0f
                if (!latestLoading) refreshTriggered = false
                return Velocity.Zero
            }
        }
    }
    LaunchedEffect(loading) {
        if (!loading) {
            pullDistance = 0f
            refreshTriggered = false
        }
    }
    var actionMessage by remember { mutableStateOf<String?>(null) }
    var actionBusy by remember { mutableStateOf(false) }
    var productDetail by remember { mutableStateOf<com.google.gson.JsonObject?>(null) }
    var detailProductSummary by remember { mutableStateOf<ProductSummary?>(null) }
    var selectedDetailVariant by remember { mutableStateOf<String?>(null) }
    var detailLoading by remember { mutableStateOf(false) }
    var detailError by remember { mutableStateOf<String?>(null) }

    fun mutateProduct(product: ProductSummary, variant: String, wishlist: Boolean) {
        if (!signedIn) { actionMessage = null; onLoginRequired(); return }
        scope.launch {
            actionBusy = true
            actionMessage = null
            try {
                val api = ApiClient.get(context)
                val payload = JsonObject().apply {
                    addProperty("product_id", product.id)
                    addProperty("variant", variant)
                    if (!wishlist) addProperty("quantity", 1)
                }
                val response = if (wishlist) api.addWishlistItem(payload) else api.addCartItem(payload)
                val result = response.body()
                actionMessage = if (response.isSuccessful && result?.success == true) {
                    if (wishlist) "Produk ditambahkan ke favorit." else "Produk ditambahkan ke keranjang."
                } else result?.error?.message ?: "Permintaan belum berhasil. Periksa koneksi dan coba lagi."
            } catch (_: Exception) {
                actionMessage = "Koneksi belum tersedia. Silakan coba lagi."
            } finally { actionBusy = false }
        }
    }

    Box(modifier.fillMaxSize()) {
      Column(Modifier.fillMaxSize()) {
        Column(Modifier.fillMaxWidth().background(MaterialTheme.colorScheme.surface).padding(horizontal = 18.dp, vertical = 10.dp)) {
            Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
                Image(
                    painter = painterResource(R.drawable.logo_lunarea),
                    contentDescription = "Lunarea Furniture",
                    modifier = Modifier.width(126.dp).height(16.dp),
                    contentScale = ContentScale.Fit
                )
                Spacer(Modifier.weight(1f))
                BadgedBox(badge = {
                    if (unreadNotificationCount > 0) Badge {
                        Text(if (unreadNotificationCount > 99) "99+" else unreadNotificationCount.toString())
                    }
                }) {
                    IconButton(
                        onClick = onOpenNotifications,
                        modifier = Modifier.sizeIn(minWidth = 48.dp, minHeight = 48.dp)
                    ) {
                        Icon(
                            painter = painterResource(R.drawable.ic_notifications),
                            contentDescription = "Pemberitahuan${if (unreadNotificationCount > 0) ", $unreadNotificationCount belum dibaca" else ""}",
                            tint = LunareaGreenText,
                            modifier = Modifier.size(23.dp)
                        )
                    }
                }
            }
            Spacer(Modifier.height(8.dp))
            OutlinedTextField(
                value = query, onValueChange = onQueryChange, modifier = Modifier.fillMaxWidth(),
                placeholder = { Text("Cari furnitur...") }, singleLine = true,
                shape = RoundedCornerShape(14.dp), keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Text, imeAction = ImeAction.Search),
                keyboardActions = KeyboardActions(onSearch = { onSearch() }),
                leadingIcon = { Icon(painterResource(R.drawable.ic_search), contentDescription = null, tint = LunareaGreenText) },
                trailingIcon = { TextButton(onClick = onSearch) { Text("Cari") } }
            )
            if (tab == AppTab.HOME) {
                Spacer(Modifier.height(10.dp))
                val bannerPager = rememberPagerState(pageCount = { homeBannerUrls.size })
                val bannerAspectRatios = remember { mutableStateMapOf<String, Float>() }
                val loadedBannerUrls = remember { mutableStateListOf<String>() }
                LaunchedEffect(tab) {
                    if (tab == AppTab.HOME) {
                        while (true) {
                            delay(4_500)
                            if (!bannerPager.isScrollInProgress && homeBannerUrls.size > 1) {
                                bannerPager.animateScrollToPage((bannerPager.currentPage + 1) % homeBannerUrls.size)
                            }
                        }
                    }
                }
                val activeBanner = homeBannerUrls[bannerPager.currentPage]
                HorizontalPager(
                    state = bannerPager,
                    modifier = Modifier.fillMaxWidth()
                        .aspectRatio(bannerAspectRatios[activeBanner] ?: 3.45f)
                        .clip(RoundedCornerShape(14.dp))
                        .background(MaterialTheme.colorScheme.surfaceVariant),
                    pageSpacing = 8.dp
                ) { page ->
                    val bannerUrl = homeBannerUrls[page]
                    if (bannerUrl !in loadedBannerUrls) {
                        Box(Modifier.fillMaxSize().background(rememberLunareaShimmerBrush()))
                    }
                    AsyncImage(
                        model = bannerUrl,
                        contentDescription = "Banner Lunarea ${page + 1}",
                        modifier = Modifier.fillMaxSize().clip(RoundedCornerShape(16.dp)),
                        contentScale = ContentScale.Fit,
                        onSuccess = { result ->
                            if (bannerUrl !in loadedBannerUrls) loadedBannerUrls.add(bannerUrl)
                            val drawable = result.result.drawable
                            if (drawable.intrinsicWidth > 0 && drawable.intrinsicHeight > 0) {
                                bannerAspectRatios[bannerUrl] =
                                    (drawable.intrinsicWidth.toFloat() / drawable.intrinsicHeight).coerceIn(1.2f, 5f)
                            }
                        },
                        onError = { if (bannerUrl !in loadedBannerUrls) loadedBannerUrls.add(bannerUrl) }
                    )
                }
            }
            if (categories.isNotEmpty()) {
                Spacer(Modifier.height(8.dp))
                Row(Modifier.fillMaxWidth().horizontalScroll(rememberScrollState()), horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                    FilterChip(selected = selectedCategory == null, onClick = { onCategoryChange(null) }, label = { Text("Semua") })
                    categories.forEach { category ->
                        FilterChip(selected = selectedCategory == category, onClick = { onCategoryChange(category) }, label = { Text(category, maxLines = 1) })
                    }
                }
            }
        }
        if (loading) LinearProgressIndicator(modifier = Modifier.fillMaxWidth(), color = MaterialTheme.colorScheme.secondary)
        if (message != null) {
            Column(Modifier.fillMaxWidth().padding(24.dp), horizontalAlignment = Alignment.CenterHorizontally) {
                Text(message, color = MaterialTheme.colorScheme.onSurfaceVariant)
                Spacer(Modifier.height(8.dp))
                TextButton(onClick = onRefresh) { Text("Muat ulang") }
            }
        }
        if (detailLoading) LinearProgressIndicator(modifier = Modifier.fillMaxWidth(), color = MaterialTheme.colorScheme.secondary)
        (actionMessage ?: detailError)?.let { Text(it, modifier = Modifier.fillMaxWidth().padding(horizontal = 20.dp, vertical = 8.dp), color = LunareaGreenText, fontSize = 13.sp) }
        BoxWithConstraints(Modifier.weight(1f).fillMaxWidth()) {
          LazyVerticalGrid(
            columns = if (maxWidth < 600.dp) GridCells.Fixed(2) else GridCells.Adaptive(200.dp),
            state = gridState,
            modifier = Modifier.fillMaxSize().nestedScroll(pullRefreshConnection),
            contentPadding = PaddingValues(horizontal = 16.dp, vertical = 12.dp),
            horizontalArrangement = Arrangement.spacedBy(8.dp),
            verticalArrangement = Arrangement.spacedBy(10.dp)
          ) {
            if (loading && products.isEmpty() && message == null) {
                items(4) { ProductCardSkeleton() }
            }
            items(products, key = { it.id }) { product ->
                ProductCard(
                    product = product,
                    busy = actionBusy,
                    onFavorite = {
                        if (product.variants.mapNotNull { it.name?.takeIf(String::isNotBlank) }.distinct().size > 1) {
                            detailProductSummary = product; selectedDetailVariant = null
                            scope.launch { detailLoading = true; try { val response = ApiClient.get(context).product(product.id); val data = response.body()?.data; if (response.isSuccessful && response.body()?.success == true && data != null) { productDetail = data; onDetailVisibility(true) } else detailError = "Detail produk belum dapat dimuat." } catch (_: Exception) { detailError = "Koneksi belum tersedia. Coba lagi." } finally { detailLoading = false } }
                        } else mutateProduct(product, product.variants.firstOrNull()?.name?.takeIf(String::isNotBlank) ?: "Default", true)
                    },
                    onDetail = {
                        detailProductSummary = product
                        selectedDetailVariant = product.variants.mapNotNull { it.name?.takeIf(String::isNotBlank) }.distinct().singleOrNull()
                        scope.launch {
                            detailLoading = true
                            detailError = null
                            try {
                                val response = ApiClient.get(context).product(product.id)
                                val data = response.body()?.data
                                if (response.isSuccessful && response.body()?.success == true && data != null) { productDetail = data; onDetailVisibility(true) }
                                else detailError = response.body()?.error?.message ?: "Detail produk belum dapat dimuat."
                            } catch (_: Exception) { detailError = "Koneksi belum tersedia. Coba lagi." }
                            finally { detailLoading = false }
                        }
                    }
                )
            }
            if (canLoadMore) item(span = { androidx.compose.foundation.lazy.grid.GridItemSpan(maxLineSpan) }) {
                TextButton(
                    onClick = onLoadMore,
                    enabled = !loading,
                    modifier = Modifier.fillMaxWidth().padding(vertical = 2.dp).heightIn(min = 44.dp)
                ) {
                    Text(if (loading) "Memuat produk…" else "Lihat produk lainnya", fontSize = 13.sp, fontWeight = FontWeight.SemiBold)
                }
            }
          }
          if (pullDistance > 0f && !loading) {
              Surface(
                  modifier = Modifier.align(Alignment.TopCenter).padding(top = 8.dp),
                  shape = RoundedCornerShape(50),
                  color = MaterialTheme.colorScheme.surface,
                  shadowElevation = 4.dp
              ) {
                  Text(
                      if (refreshTriggered) "Memperbarui katalog…" else "Tarik untuk memperbarui",
                      modifier = Modifier.padding(horizontal = 14.dp, vertical = 8.dp),
                      style = MaterialTheme.typography.labelMedium,
                      color = LunareaGreenText
                  )
              }
          }
        }
      }
      productDetail?.let { response ->
          ProductDetailScreen(
              modifier = Modifier.fillMaxSize(),
              detail = response.getAsJsonObject("product") ?: response,
              summary = detailProductSummary,
              loading = detailLoading,
              busy = actionBusy,
              selectedVariant = selectedDetailVariant,
              actionMessage = actionMessage,
              onVariantChange = { selectedDetailVariant = it },
              onBack = { productDetail = null; onDetailVisibility(false) },
              onAddCart = { detailProductSummary?.let { product ->
                  val hasVariants = product.variants.any { !it.name.isNullOrBlank() }
                  val variant = selectedDetailVariant ?: if (!hasVariants) "Default" else null
                  if (variant == null) actionMessage = "Pilih varian terlebih dahulu." else mutateProduct(product, variant, false)
              } },
              onFavorite = { detailProductSummary?.let { product ->
                  val hasVariants = product.variants.any { !it.name.isNullOrBlank() }
                  val variant = selectedDetailVariant ?: if (!hasVariants) "Default" else null
                  if (variant == null) actionMessage = "Pilih varian terlebih dahulu." else mutateProduct(product, variant, true)
              } }
          )
      }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
private fun ProductDetailScreen(
    modifier: Modifier,
    detail: JsonObject,
    summary: ProductSummary?,
    loading: Boolean,
    busy: Boolean,
    selectedVariant: String?,
    actionMessage: String?,
    onVariantChange: (String) -> Unit,
    onBack: () -> Unit,
    onAddCart: () -> Unit,
    onFavorite: () -> Unit
) {
    BackHandler(onBack = onBack)
    val name = detail.get("name")?.takeUnless { it.isJsonNull }?.asString ?: summary?.name ?: "Detail produk"
    val image = detail.get("image_url")?.takeUnless { it.isJsonNull }?.asString ?: summary?.image_url
    val productId = detail.get("id")?.takeUnless { it.isJsonNull }?.asString ?: summary?.id.orEmpty()
    val galleryCandidates = remember(productId, image, detail.get("images")?.toString()) {
        val apiImages = runCatching {
            detail.getAsJsonArray("images")?.mapNotNull { item ->
                runCatching { item.asString.takeIf(String::isNotBlank) }.getOrNull()
            }.orEmpty()
        }.getOrDefault(emptyList())
        if (apiImages.isNotEmpty()) apiImages.distinct() else buildList {
            if (!image.isNullOrBlank()) add(image)
            if (productId.isNotBlank() && !image.isNullOrBlank()) {
                val imageUri = Uri.parse(image)
                if (!imageUri.scheme.isNullOrBlank() && !imageUri.authority.isNullOrBlank()) {
                    (2..6).forEach { slot ->
                        add("${imageUri.scheme}://${imageUri.authority}/imgproduct/${Uri.encode(productId)}/$slot")
                    }
                }
            }
        }
    }
    val galleryFailed = remember(productId, image) { mutableStateListOf<String>() }
    val galleryAspectRatios = remember(productId) { mutableStateMapOf<String, Float>() }
    val availableGallery = galleryCandidates.filterNot { it in galleryFailed }
    val pagerState = rememberPagerState(pageCount = { availableGallery.size })
    val activePhoto = availableGallery.getOrNull(pagerState.currentPage)
    val activePhotoRatio = activePhoto?.let { galleryAspectRatios[it] } ?: 1f
    var galleryFullscreen by remember(productId) { mutableStateOf(false) }
    val fullscreenPagerState = rememberPagerState(initialPage = pagerState.currentPage, pageCount = { availableGallery.size })
    val galleryScope = rememberCoroutineScope()
    LaunchedEffect(galleryCandidates) {
        galleryFailed.clear()
    }
    LaunchedEffect(availableGallery.size) {
        if (availableGallery.isNotEmpty() && pagerState.currentPage >= availableGallery.size) {
            pagerState.scrollToPage(availableGallery.lastIndex)
        }
    }
    LaunchedEffect(galleryFullscreen) {
        if (galleryFullscreen && availableGallery.isNotEmpty()) fullscreenPagerState.scrollToPage(pagerState.currentPage)
    }
    LaunchedEffect(galleryFullscreen, fullscreenPagerState.currentPage) {
        if (galleryFullscreen && availableGallery.isNotEmpty()) pagerState.scrollToPage(fullscreenPagerState.currentPage)
    }
    val price = runCatching { detail.get("price")?.takeUnless { it.isJsonNull }?.asLong }.getOrNull() ?: summary?.price
    val description = readableProductDescription(
        detail.get("description")?.takeUnless { it.isJsonNull }?.asString.orEmpty()
    )
    var infoSheet by remember(productId) { mutableStateOf<ProductInfoSheet?>(null) }
    Column(modifier.background(MaterialTheme.colorScheme.background)) {
        Row(Modifier.fillMaxWidth().background(MaterialTheme.colorScheme.surface).padding(horizontal = 8.dp, vertical = 4.dp), verticalAlignment = Alignment.CenterVertically) {
            IconButton(onClick = onBack, modifier = Modifier.size(48.dp)) {
                Icon(painterResource(R.drawable.ic_arrow_back), contentDescription = "Kembali", tint = LunareaGreenText)
            }
            Column(Modifier.weight(1f), horizontalAlignment = Alignment.CenterHorizontally) {
                Image(
                    painter = painterResource(R.drawable.logo_lunarea),
                    contentDescription = "Lunarea Furniture",
                    modifier = Modifier.width(112.dp).height(14.dp),
                    contentScale = ContentScale.Fit
                )
                Text("Detail produk", style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.Medium)
            }
            Spacer(Modifier.size(48.dp))
        }
        if (loading) LinearProgressIndicator(Modifier.fillMaxWidth(), color = MaterialTheme.colorScheme.secondary)
        Column(Modifier.weight(1f).fillMaxWidth().verticalScroll(rememberScrollState())) {
            Column(Modifier.fillMaxWidth().padding(horizontal = 16.dp, vertical = 12.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
                    Box(Modifier.fillMaxWidth().aspectRatio(activePhotoRatio), contentAlignment = Alignment.Center) {
                    if (availableGallery.isNotEmpty()) HorizontalPager(
                        state = pagerState,
                        key = { page -> availableGallery[page] },
                        modifier = Modifier.fillMaxSize(),
                        pageSpacing = 12.dp
                    ) { page ->
                        val photo = availableGallery[page]
                        AsyncImage(
                            model = photo,
                            contentDescription = "$name, foto ${page + 1} dari ${availableGallery.size}",
                            modifier = Modifier.fillMaxSize().clickable { galleryFullscreen = true },
                            contentScale = ContentScale.Fit,
                            onError = { if (photo !in galleryFailed) galleryFailed.add(photo) },
                            onSuccess = { result ->
                                val drawable = result.result.drawable
                                val width = drawable.intrinsicWidth
                                val height = drawable.intrinsicHeight
                                if (width > 0 && height > 0) galleryAspectRatios[photo] = width.toFloat() / height.toFloat()
                            }
                        )
                    } else Image(
                        painter = painterResource(R.drawable.logo_lunarea),
                        contentDescription = "Lunarea Furniture",
                        modifier = Modifier.width(150.dp).height(18.dp),
                        contentScale = ContentScale.Fit
                    )
                    }
                if (availableGallery.isNotEmpty()) Text(
                    "Foto ${pagerState.currentPage + 1} dari ${availableGallery.size}",
                    modifier = Modifier.fillMaxWidth(),
                    style = MaterialTheme.typography.labelSmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    textAlign = androidx.compose.ui.text.style.TextAlign.End
                )
                val thumbnails = availableGallery
                if (thumbnails.size > 1) {
                    LazyRow(
                        modifier = Modifier.fillMaxWidth(),
                        contentPadding = PaddingValues(horizontal = 2.dp),
                        horizontalArrangement = Arrangement.spacedBy(9.dp)
                    ) {
                        items(thumbnails.size, key = { thumbnails[it] }) { index ->
                            val photo = thumbnails[index]
                            val selected = pagerState.currentPage == index
                            AsyncImage(
                                model = photo,
                                contentDescription = "Tampilkan foto ${index + 1} produk",
                                modifier = Modifier.size(60.dp).clip(RoundedCornerShape(10.dp))
                                    .then(if (selected) Modifier.border(2.dp, LunareaGreenText, RoundedCornerShape(10.dp)) else Modifier)
                                    .clickable { galleryScope.launch { pagerState.animateScrollToPage(index) } },
                                contentScale = ContentScale.Fit,
                                onError = { if (photo !in galleryFailed) galleryFailed.add(photo) }
                            )
                        }
                    }
                } else if (galleryCandidates.size > 1 && galleryFailed.isEmpty()) {
                    LinearProgressIndicator(Modifier.fillMaxWidth())
                }
            }
            Column(Modifier.fillMaxWidth().padding(horizontal = 20.dp, vertical = 18.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                val categoryName = detail.get("category")?.takeUnless { it.isJsonNull }?.asString ?: summary?.category ?: "LUNAREA FURNITURE"
                Text(categoryName.uppercase(Locale.ROOT), color = LunareaGreenText, style = MaterialTheme.typography.labelSmall, letterSpacing = 1.sp)
                Text(name, style = MaterialTheme.typography.headlineSmall, fontWeight = FontWeight.SemiBold, lineHeight = 32.sp)
                val stock = runCatching { detail.get("stock")?.takeUnless { it.isJsonNull }?.asInt }.getOrNull() ?: summary?.stock
                Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
                    Column(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(2.dp)) {
                        Text("Harga", style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                        Text(price?.let(::formatRupiah) ?: "Hubungi kami untuk informasi harga", style = MaterialTheme.typography.titleLarge, color = LunareaGreenText, fontWeight = FontWeight.Bold)
                    }
                    val stockLabel = if (stock == null) "Cek stok" else if (stock > 0) "Tersedia" else "Stok habis"
                    Text(stockLabel, color = if (stock != null && stock <= 0) MaterialTheme.colorScheme.error else LunareaGreenText, style = MaterialTheme.typography.labelMedium, fontWeight = FontWeight.Medium)
                }
                HorizontalDivider(color = MaterialTheme.colorScheme.outlineVariant.copy(alpha = .75f))
                val weight = detail.get("weight_kg")?.takeUnless { it.isJsonNull }?.toString()?.trim('"') ?: summary?.weight_kg?.toString()
                Text("Tentang produk", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
                var descriptionOverflows by remember(description) { mutableStateOf(false) }
                Text(
                    text = description,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    style = MaterialTheme.typography.bodyMedium,
                    lineHeight = 24.sp,
                    maxLines = 4,
                    onTextLayout = { layout ->
                        descriptionOverflows = layout.hasVisualOverflow
                    }
                )
                if (descriptionOverflows) {
                    TextButton(
                        onClick = { infoSheet = ProductInfoSheet.DESCRIPTION },
                        contentPadding = PaddingValues(horizontal = 0.dp, vertical = 2.dp),
                        modifier = Modifier.heightIn(min = 40.dp)
                    ) {
                        Text("Selengkapnya")
                    }
                }
                TextButton(
                    onClick = { infoSheet = ProductInfoSheet.REVIEWS },
                    contentPadding = PaddingValues(horizontal = 0.dp, vertical = 2.dp),
                    modifier = Modifier.heightIn(min = 40.dp)
                ) {
                    Text("Lihat ulasan produk")
                }
                if (!weight.isNullOrBlank()) Text("Berat produk  ·  $weight kg", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                val variants = summary?.variants.orEmpty().filter { !it.name.isNullOrBlank() }.distinctBy { it.name }
                if (variants.isNotEmpty()) {
                    Text("Pilih varian", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
                    Text("Pilih warna atau model yang tersedia", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                    LazyRow(horizontalArrangement = Arrangement.spacedBy(8.dp), contentPadding = PaddingValues(vertical = 2.dp)) {
                      items(variants.size, key = { variants[it].name.orEmpty() }) { index ->
                        val option = variants[index]
                        val unavailable = option.stock != null && option.stock <= 0
                        FilterChip(
                            selected = selectedVariant == option.name,
                            onClick = { if (!unavailable) onVariantChange(option.name.orEmpty()) },
                            enabled = !unavailable,
                            label = { Text(option.name.orEmpty() + if (unavailable) " · Habis" else "", maxLines = 1) },
                            shape = CircleShape
                        )
                      }
                    }
                    val selectedStock = variants.firstOrNull { it.name == selectedVariant }?.stock
                    Text(
                        when { selectedVariant == null -> "Varian belum dipilih"; selectedStock == null -> "Ketersediaan varian dapat dikonfirmasi"; selectedStock <= 0 -> "Varian ini sedang habis"; else -> "Stok varian tersedia" },
                        color = if (selectedVariant == null || (selectedStock != null && selectedStock <= 0)) MaterialTheme.colorScheme.error else MaterialTheme.colorScheme.onSurfaceVariant,
                        style = MaterialTheme.typography.bodySmall
                    )
                } else {
                    Text("Varian standar", style = MaterialTheme.typography.titleSmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                }
                actionMessage?.let { Text(it, color = LunareaGreenText, style = MaterialTheme.typography.bodySmall) }
            }
        }
        Row(Modifier.fillMaxWidth().background(MaterialTheme.colorScheme.surface).padding(horizontal = 16.dp, vertical = 12.dp), horizontalArrangement = Arrangement.spacedBy(12.dp), verticalAlignment = Alignment.CenterVertically) {
            Column(Modifier.weight(1f)) {
                Text("Harga", style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                Text(price?.let(::formatRupiah) ?: "Hubungi kami", style = MaterialTheme.typography.titleMedium, color = LunareaGreenText, fontWeight = FontWeight.Bold, maxLines = 1)
            }
            OutlinedButton(onClick = onFavorite, enabled = !busy, modifier = Modifier.sizeIn(minWidth = 48.dp, minHeight = 48.dp), contentPadding = PaddingValues(0.dp), shape = RoundedCornerShape(14.dp)) {
                Icon(painterResource(R.drawable.ic_favorite_outline), contentDescription = "Simpan ke favorit", tint = LunareaGreenText)
            }
            val variants = summary?.variants.orEmpty().filter { !it.name.isNullOrBlank() }.distinctBy { it.name }
            val selectedStock = variants.firstOrNull { it.name == selectedVariant }?.stock
            val stockAvailable = if (variants.isNotEmpty()) selectedStock == null || selectedStock > 0 else summary?.stock == null || summary.stock > 0
            Button(onClick = onAddCart, enabled = !busy && stockAvailable && (variants.isEmpty() || selectedVariant != null), modifier = Modifier.weight(1f).height(50.dp), shape = RoundedCornerShape(14.dp)) {
                if (busy) CircularProgressIndicator(Modifier.size(18.dp), strokeWidth = 2.dp) else Text("Tambah ke keranjang")
            }
        }
        infoSheet?.let { sheet ->
            ModalBottomSheet(
                onDismissRequest = { infoSheet = null },
                containerColor = MaterialTheme.colorScheme.surface,
                dragHandle = { BottomSheetDefaults.DragHandle(color = MaterialTheme.colorScheme.outlineVariant) }
            ) {
                Column(
                    Modifier.fillMaxWidth()
                        .heightIn(max = 560.dp)
                        .navigationBarsPadding()
                        .padding(horizontal = 24.dp)
                        .padding(bottom = 24.dp),
                    verticalArrangement = Arrangement.spacedBy(14.dp)
                ) {
                    Text(
                        if (sheet == ProductInfoSheet.DESCRIPTION) "Deskripsi produk" else "Ulasan produk",
                        style = MaterialTheme.typography.titleLarge,
                        fontWeight = FontWeight.SemiBold
                    )
                    HorizontalDivider(color = MaterialTheme.colorScheme.outlineVariant.copy(alpha = .7f))
                    if (sheet == ProductInfoSheet.DESCRIPTION) {
                        Column(
                            Modifier.fillMaxWidth().weight(1f, fill = false).verticalScroll(rememberScrollState()),
                            verticalArrangement = Arrangement.spacedBy(8.dp)
                        ) {
                            Text(name, style = MaterialTheme.typography.titleSmall, color = LunareaGreenText, fontWeight = FontWeight.Medium)
                            Text(description, style = MaterialTheme.typography.bodyLarge, lineHeight = 26.sp, color = MaterialTheme.colorScheme.onSurfaceVariant)
                        }
                    } else {
                        val reviewItems = sequenceOf("reviews", "ratings", "rating_list")
                            .mapNotNull { key -> runCatching { detail.getAsJsonArray(key) }.getOrNull() }
                            .firstOrNull()
                        if (reviewItems != null && reviewItems.size() > 0) {
                            Column(
                                Modifier.fillMaxWidth().weight(1f, fill = false).verticalScroll(rememberScrollState()),
                                verticalArrangement = Arrangement.spacedBy(12.dp)
                            ) {
                                reviewItems.forEach { item ->
                                    val review = item.takeIf { it.isJsonObject }?.asJsonObject
                                    if (review != null) {
                                        Column(Modifier.fillMaxWidth().padding(vertical = 4.dp), verticalArrangement = Arrangement.spacedBy(6.dp)) {
                                            val stars = review.get("rating")?.takeUnless { it.isJsonNull }?.toString()?.trim('"')
                                            if (!stars.isNullOrBlank()) Text("★ $stars / 5", color = LunareaGreenText, style = MaterialTheme.typography.labelLarge)
                                            val reviewer = review.get("nama")?.takeUnless { it.isJsonNull }?.asString
                                                ?: review.get("name")?.takeUnless { it.isJsonNull }?.asString
                                            if (!reviewer.isNullOrBlank()) Text(reviewer, style = MaterialTheme.typography.labelMedium, fontWeight = FontWeight.Medium)
                                            val comment = review.get("ulasan")?.takeUnless { it.isJsonNull }?.asString
                                                ?: review.get("comment")?.takeUnless { it.isJsonNull }?.asString
                                                ?: review.get("komentar")?.takeUnless { it.isJsonNull }?.asString
                                            if (!comment.isNullOrBlank()) Text(comment, style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant, lineHeight = 22.sp)
                                            HorizontalDivider(color = MaterialTheme.colorScheme.outlineVariant.copy(alpha = .55f))
                                        }
                                    }
                                }
                            }
                        } else {
                            Column(
                                Modifier.fillMaxWidth().heightIn(min = 150.dp).padding(vertical = 12.dp),
                                horizontalAlignment = Alignment.CenterHorizontally,
                                verticalArrangement = Arrangement.Center
                            ) {
                                Text("Ulasan belum tersedia di aplikasi", style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.SemiBold)
                                Text(
                                    "Informasi ulasan belum dikirim oleh layanan katalog.",
                                    style = MaterialTheme.typography.bodyMedium,
                                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                                    textAlign = androidx.compose.ui.text.style.TextAlign.Center
                                )
                            }
                        }
                    }
                    TextButton(onClick = { infoSheet = null }, modifier = Modifier.align(Alignment.End).heightIn(min = 44.dp)) {
                        Text("Tutup")
                    }
                }
            }
        }
        if (galleryFullscreen && availableGallery.isNotEmpty()) {
            Dialog(
                onDismissRequest = { galleryFullscreen = false },
                properties = DialogProperties(usePlatformDefaultWidth = false)
            ) {
                Box(
                    Modifier.fillMaxSize().background(MaterialTheme.colorScheme.background)
                ) {
                    Column(Modifier.fillMaxSize().padding(horizontal = 20.dp, vertical = 12.dp)) {
                        Row(
                            Modifier.fillMaxWidth().heightIn(min = 52.dp),
                            verticalAlignment = Alignment.CenterVertically
                        ) {
                            IconButton(onClick = { galleryFullscreen = false }, modifier = Modifier.size(48.dp)) {
                                Icon(painterResource(R.drawable.ic_arrow_back), contentDescription = "Tutup foto", tint = LunareaGreenText)
                            }
                            Column(Modifier.weight(1f).padding(start = 8.dp), verticalArrangement = Arrangement.spacedBy(2.dp)) {
                                Text("Foto produk", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
                                Text(name, style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant, maxLines = 1)
                            }
                            Text(
                                "${fullscreenPagerState.currentPage + 1} / ${availableGallery.size}",
                                style = MaterialTheme.typography.labelMedium,
                                color = LunareaGreenText
                            )
                        }
                        HorizontalPager(
                            state = fullscreenPagerState,
                            key = { page -> "full-${availableGallery[page]}" },
                            modifier = Modifier.weight(1f).fillMaxWidth(),
                            pageSpacing = 12.dp
                        ) { page ->
                            AsyncImage(
                                model = availableGallery[page],
                                contentDescription = "$name, foto ${page + 1} dari ${availableGallery.size}",
                                modifier = Modifier.fillMaxSize(),
                                contentScale = ContentScale.Fit
                            )
                        }
                        Row(
                            Modifier.fillMaxWidth().heightIn(min = 52.dp),
                            horizontalArrangement = Arrangement.Center,
                            verticalAlignment = Alignment.CenterVertically
                        ) {
                            repeat(availableGallery.size) { index ->
                                val selected = fullscreenPagerState.currentPage == index
                                Box(
                                    Modifier.padding(horizontal = 4.dp).size(width = if (selected) 18.dp else 6.dp, height = 6.dp)
                                        .clip(CircleShape)
                                        .background(if (selected) LunareaGreenText else MaterialTheme.colorScheme.outlineVariant)
                                )
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun ProductCard(product: ProductSummary, busy: Boolean, onFavorite: () -> Unit, onDetail: () -> Unit) {
    val variants = product.variants.mapNotNull { it.name?.takeIf(String::isNotBlank) }.distinct()
    var productImageReady by remember(product.image_url) { mutableStateOf(false) }
    val discount = product.discount_percent?.coerceIn(0, 100) ?: 0
    Card(
        onClick = onDetail,
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        border = androidx.compose.foundation.BorderStroke(1.dp, MaterialTheme.colorScheme.outlineVariant.copy(alpha = .55f))
    ) {
        Column(Modifier.fillMaxWidth()) {
            Box(Modifier.fillMaxWidth().aspectRatio(.96f).background(MaterialTheme.colorScheme.surfaceVariant)) {
                if (!product.image_url.isNullOrBlank()) {
                    if (!productImageReady) {
                        Box(Modifier.fillMaxSize().background(rememberLunareaShimmerBrush()))
                    }
                    AsyncImage(
                        model = product.image_url,
                        contentDescription = "${product.name}, gambar produk",
                        modifier = Modifier.fillMaxSize().padding(4.dp),
                        contentScale = ContentScale.Fit,
                        onSuccess = { productImageReady = true },
                        onError = { productImageReady = true }
                    )
                } else {
                    Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                        Text("LUNAREA", color = LunareaGreenText, style = MaterialTheme.typography.labelLarge, letterSpacing = 2.sp)
                    }
                }
                if (discount > 0) {
                    Surface(
                        Modifier.align(Alignment.TopStart).padding(8.dp),
                        shape = RoundedCornerShape(7.dp),
                        color = MaterialTheme.colorScheme.primaryContainer
                    ) {
                        Text(
                            "-$discount%",
                            Modifier.padding(horizontal = 7.dp, vertical = 4.dp),
                            color = MaterialTheme.colorScheme.onPrimaryContainer,
                            fontSize = 10.sp,
                            lineHeight = 12.sp,
                            fontWeight = FontWeight.Bold
                        )
                    }
                }
                IconButton(
                    onClick = onFavorite,
                    enabled = !busy,
                    modifier = Modifier.align(Alignment.TopEnd).padding(3.dp).size(48.dp)
                        .background(MaterialTheme.colorScheme.surface.copy(alpha = .96f), CircleShape)
                ) {
                    Icon(
                        painter = painterResource(R.drawable.ic_favorite_outline),
                        contentDescription = "Simpan ${product.name} ke favorit",
                        tint = LunareaGreenText,
                        modifier = Modifier.size(19.dp)
                    )
                }
                if (product.stock == 0) {
                    Surface(
                        Modifier.align(Alignment.BottomStart).padding(10.dp),
                        shape = MaterialTheme.shapes.extraSmall,
                        color = MaterialTheme.colorScheme.errorContainer
                    ) {
                        Text("Stok habis", Modifier.padding(horizontal = 8.dp, vertical = 5.dp), color = MaterialTheme.colorScheme.onErrorContainer, style = MaterialTheme.typography.labelSmall)
                    }
                }
            }
            Column(
                Modifier.fillMaxWidth().padding(horizontal = 10.dp, vertical = 10.dp),
                verticalArrangement = Arrangement.spacedBy(5.dp)
            ) {
                Text(
                    product.category?.takeIf(String::isNotBlank) ?: "LUNAREA FURNITURE",
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    fontSize = 9.sp,
                    lineHeight = 12.sp,
                    maxLines = 1,
                    letterSpacing = .35.sp
                )
                Text(
                    product.name,
                    style = MaterialTheme.typography.titleSmall,
                    color = MaterialTheme.colorScheme.onSurface,
                    fontSize = 12.sp,
                    lineHeight = 16.sp,
                    fontWeight = FontWeight.SemiBold,
                    maxLines = 2,
                    minLines = 2
                )
                if (variants.isNotEmpty()) {
                    Text(
                        "${variants.size} pilihan varian · ${variants.take(1).joinToString()}${if (variants.size > 1) " +${variants.size - 1}" else ""}",
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        fontSize = 10.sp,
                        lineHeight = 13.sp,
                        maxLines = 1
                    )
                } else {
                    Spacer(Modifier.height(13.dp))
                }
                Text(
                    product.price?.let(::formatRupiah) ?: "Hubungi kami",
                    color = LunareaGreenText,
                    fontSize = 13.sp,
                    lineHeight = 17.sp,
                    fontWeight = FontWeight.Bold,
                    maxLines = 1
                )
                Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(5.dp)) {
                    Box(
                        Modifier.size(6.dp).background(
                            color = if (product.stock == 0) MaterialTheme.colorScheme.error else LunareaGreenText,
                            shape = CircleShape
                        )
                    )
                    Text(
                        when (product.stock) {
                            0 -> "Stok habis"
                            null -> "Cek ketersediaan"
                            else -> "Tersedia"
                        },
                        color = if (product.stock == 0) MaterialTheme.colorScheme.error else MaterialTheme.colorScheme.onSurfaceVariant,
                        fontSize = 10.sp,
                        lineHeight = 13.sp,
                        maxLines = 1
                    )
                }
            }
        }
    }
}

@Composable
private fun AccountScreen(
    modifier: Modifier, signedIn: Boolean, busy: Boolean, identifier: String, password: String,
    onIdentifier: (String) -> Unit, onPassword: (String) -> Unit, onLogin: () -> Unit, onLogout: () -> Unit,
    onEnableNotifications: () -> Unit, notificationsEnabled: Boolean, onDisableNotifications: () -> Unit,
    onOpenOrders: () -> Unit, onOpenFavorites: () -> Unit, onOpenAddresses: () -> Unit,
    onOpenProfile: () -> Unit, onOpenSecurity: () -> Unit, onOpenRegistration: () -> Unit, onOpenRecovery: () -> Unit, onOpenVerification: () -> Unit,
    notificationMessage: String?, message: String?
) {
    Column(modifier.fillMaxSize().imePadding().verticalScroll(rememberScrollState()).padding(20.dp), verticalArrangement = Arrangement.spacedBy(14.dp)) {
        LunareaSectionTitle("Akun Anda", if (signedIn) "Kelola profil, pesanan, dan preferensi" else "Masuk untuk melihat layanan dan pesanan akun Anda")
        if (!signedIn) {
            Card(shape = MaterialTheme.shapes.large, colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)) {
                Column(Modifier.fillMaxWidth().padding(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                    OutlinedTextField(identifier, onIdentifier, modifier = Modifier.fillMaxWidth(), label = { Text("Email atau username") }, singleLine = true, shape = MaterialTheme.shapes.medium, keyboardOptions = KeyboardOptions(imeAction = ImeAction.Next))
                    PasswordInputField(password, onPassword, "Kata sandi")
                    Button(onClick = onLogin, enabled = !busy && identifier.isNotBlank() && password.isNotBlank(), modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp), shape = MaterialTheme.shapes.medium) {
                        if (busy) CircularProgressIndicator(Modifier.size(18.dp), strokeWidth = 2.dp) else Text("Masuk")
                    }
                    TextButton(onClick = onOpenRegistration, modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp)) { Text("Buat akun baru") }
                    TextButton(onClick = onOpenRecovery, modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp)) { Text("Lupa kata sandi?") }
                    TextButton(onClick = onOpenVerification, modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp)) { Text("Verifikasi email atau kirim ulang kode") }
                }
            }
        } else {
            Card(shape = RoundedCornerShape(18.dp), colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)) {
                Column(Modifier.fillMaxWidth().padding(18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
                    Text("Layanan akun", fontWeight = FontWeight.SemiBold)
                    AccountActionRow("Profil saya", "Nama dan nomor ponsel", onOpenProfile)
                    AccountActionRow("Keamanan akun", "Kata sandi dan email", onOpenSecurity)
                    AccountActionRow("Pesanan saya", "Pembayaran dan status pengiriman", onOpenOrders)
                    AccountActionRow("Produk favorit", "Daftar furnitur pilihan", onOpenFavorites)
                    AccountActionRow("Alamat tersimpan", "Atur alamat pengiriman", onOpenAddresses)
                    AccountActionRow(
                        if (notificationsEnabled) "Matikan notifikasi" else "Aktifkan notifikasi pesanan",
                        if (notificationsEnabled) "Preferensi pemberitahuan perangkat" else "Dapatkan kabar terbaru pesanan",
                        if (notificationsEnabled) onDisableNotifications else onEnableNotifications
                    )
                    HorizontalDivider(color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.08f))
                    TextButton(onClick = onLogout, enabled = !busy, modifier = Modifier.fillMaxWidth()) { Text("Keluar dari akun", color = MaterialTheme.colorScheme.error) }
                }
            }
        }
        message?.let { Text(it, color = LunareaGreenText, fontSize = 13.sp) }
        notificationMessage?.let { status ->
            Surface(
                modifier = Modifier.fillMaxWidth(),
                shape = RoundedCornerShape(14.dp),
                color = MaterialTheme.colorScheme.secondaryContainer
            ) {
                Row(
                    Modifier.padding(horizontal = 14.dp, vertical = 12.dp),
                    horizontalArrangement = Arrangement.spacedBy(10.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Icon(painterResource(R.drawable.ic_dialog_info), contentDescription = null, modifier = Modifier.size(20.dp))
                    Text(status, color = MaterialTheme.colorScheme.onSecondaryContainer, fontSize = 13.sp, lineHeight = 18.sp)
                }
            }
        }
    }
}

@Composable
private fun AccountActionRow(title: String, subtitle: String, onClick: () -> Unit) {
    Card(
        onClick = onClick,
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(14.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.background)
    ) {
        Row(Modifier.fillMaxWidth().padding(horizontal = 14.dp, vertical = 12.dp), verticalAlignment = Alignment.CenterVertically) {
            Column(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(3.dp)) {
                Text(title, fontWeight = FontWeight.Medium, fontSize = 14.sp)
                Text(subtitle, color = MaterialTheme.colorScheme.onSurfaceVariant, fontSize = 12.sp)
            }
            Icon(
                painter = painterResource(R.drawable.ic_chevron_right),
                contentDescription = null,
                tint = LunareaGreenText,
                modifier = Modifier.size(20.dp)
            )
        }
    }
}

private fun JsonObject.stringOrNull(key: String): String? =
    get(key)?.takeUnless { it.isJsonNull }?.let { runCatching { it.asString }.getOrNull() }

private fun JsonObject.longOrNull(key: String): Long? =
    get(key)?.takeUnless { it.isJsonNull }?.let { runCatching { it.asLong }.getOrNull() }

private fun formatRupiah(amount: Long): String = NumberFormat.getCurrencyInstance(Locale("id", "ID")).format(amount).replace(",00", "")

private fun isUnpaidOrderStatus(status: String): Boolean {
    val normalized = status.trim().lowercase(Locale.ROOT)
    return normalized.contains("pending") || normalized.contains("menunggu pembayaran") || normalized.contains("belum dibayar") || normalized.contains("unpaid")
}

private fun checkoutRequestSignature(addressId: Long, quote: String, payment: String, points: Boolean, glass: Boolean, voucherClaimId: Long = 0L): String {
    val raw = listOf(addressId.toString(), quote, payment, points.toString(), glass.toString(), voucherClaimId.toString()).joinToString("|")
    return java.security.MessageDigest.getInstance("SHA-256").digest(raw.toByteArray()).joinToString("") { "%02x".format(it) }
}

private fun openWebsite(context: android.content.Context, url: String) {
    context.startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url)))
}
