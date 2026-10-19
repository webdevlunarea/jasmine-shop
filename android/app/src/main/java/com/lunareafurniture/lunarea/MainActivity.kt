package com.lunareafurniture.lunarea

import android.os.Bundle
import android.os.Build
import android.app.Activity
import android.text.Html
import android.net.Uri
import android.content.Intent
import android.content.ClipData
import android.content.ClipboardManager
import android.graphics.Bitmap
import android.content.pm.PackageManager
import android.widget.Toast
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.compose.BackHandler
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.core.splashscreen.SplashScreen.Companion.installSplashScreen
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
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.asImageBitmap
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
import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.animation.slideInHorizontally
import androidx.compose.animation.slideOutHorizontally
import androidx.compose.animation.slideInVertically
import androidx.compose.animation.slideOutVertically
import androidx.compose.animation.core.animateFloat
import androidx.compose.animation.core.LinearEasing
import androidx.compose.animation.core.RepeatMode
import androidx.compose.animation.core.infiniteRepeatable
import androidx.compose.animation.core.rememberInfiniteTransition
import androidx.compose.animation.core.tween
import androidx.compose.runtime.*
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.res.painterResource
import androidx.core.content.ContextCompat
import androidx.compose.ui.semantics.Role
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.semantics.liveRegion
import androidx.compose.ui.semantics.LiveRegionMode
import androidx.compose.ui.zIndex
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
import com.lunareafurniture.lunarea.data.resolveCurrentAddress
import com.google.gson.JsonObject
import com.lunareafurniture.lunarea.notifications.PushDeviceRegistration
import com.lunareafurniture.lunarea.notifications.rememberNotificationPermissionRequester
import com.lunareafurniture.lunarea.security.AndroidRecaptcha
import com.lunareafurniture.lunarea.ui.theme.LunareaTheme
import com.lunareafurniture.lunarea.ui.theme.LunareaGreenText
import com.lunareafurniture.lunarea.ui.theme.LunareaSpacing
import com.lunareafurniture.lunarea.ui.splash.LunareaStartup
import coil.compose.AsyncImage
import kotlinx.coroutines.launch
import kotlinx.coroutines.delay
import kotlinx.coroutines.async
import kotlinx.coroutines.coroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import kotlinx.coroutines.flow.collect
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.toRequestBody
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import java.text.NumberFormat
import java.text.Normalizer
import java.util.Locale
import com.google.zxing.BarcodeFormat
import com.google.zxing.EncodeHintType
import com.google.zxing.MultiFormatWriter
import com.google.zxing.qrcode.decoder.ErrorCorrectionLevel

private enum class AppTab(val label: String) { HOME("Beranda"), CATALOG("Katalog"), CART("Keranjang"), ACCOUNT("Akun"), CHECKOUT("Checkout") }
private enum class AccountSection { MENU, ORDERS, FAVORITES, ADDRESSES, PROFILE, SECURITY, POINTS, CHECKOUT, REGISTER, VERIFY_EMAIL, RECOVERY }
private data class PushTap(val type: String, val orderId: String, val productId: String, val campaignId: String) {
    companion object {
        fun from(intent: Intent?): PushTap? {
            if (intent == null) return null
            val type = intent.getStringExtra("push_type") ?: intent.getStringExtra("type") ?: return null
            return PushTap(
                type = type,
                orderId = intent.getStringExtra("push_order_id") ?: intent.getStringExtra("order_id").orEmpty(),
                productId = intent.getStringExtra("push_product_id") ?: intent.getStringExtra("product_id").orEmpty(),
                campaignId = intent.getStringExtra("push_campaign_id") ?: intent.getStringExtra("campaign_id").orEmpty()
            )
        }
    }
}
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

private fun favoriteKey(productId: String, variant: String): String =
    productId.trim() + "\u001f" + variant.trim().lowercase(Locale.ROOT)

private fun normalizeRegionLabel(value: String): String = Normalizer.normalize(value, Normalizer.Form.NFD)
    .replace("\\p{M}+".toRegex(), "")
    .lowercase(Locale.ROOT)
    .replace("\\b(provinsi|province|kabupaten|kota administratif|kota|kecamatan|kelurahan|desa)\\b".toRegex(), "")
    .replace("[^a-z0-9]+".toRegex(), " ")
    .trim()

class MainActivity : ComponentActivity() {
    private var pendingPushTap by mutableStateOf<PushTap?>(null)

    override fun onCreate(savedInstanceState: Bundle?) {
        installSplashScreen()
        super.onCreate(savedInstanceState)
        pendingPushTap = PushTap.from(intent)
        setContent {
            LunareaTheme {
                LunareaStartup { startupComplete ->
                    LunareaApp(startupComplete, pendingPushTap) { pendingPushTap = null }
                }
            }
        }
    }

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        setIntent(intent)
        pendingPushTap = PushTap.from(intent)
    }
}

@Composable
private fun LunareaApp(startupComplete: Boolean, pushTap: PushTap?, onPushTapHandled: () -> Unit) {
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
    var authPromptMessage by remember { mutableStateOf<String?>(null) }
    var notificationsEnabled by remember { mutableStateOf(PushDeviceRegistration(context).isRegistered()) }
    var showNotificationIntro by remember { mutableStateOf(false) }
    var showLogoutConfirmation by remember { mutableStateOf(false) }
    var pendingCartRemoval by remember { mutableStateOf<CartLine?>(null) }
    var notificationInboxOpen by remember { mutableStateOf(false) }
    var notificationsRefreshVersion by remember { mutableIntStateOf(0) }
    var unreadNotificationCount by remember { mutableIntStateOf(0) }
    var cartItems by remember { mutableStateOf<List<CartLine>>(emptyList()) }
    var cartSubtotal by remember { mutableLongStateOf(0L) }
    var cartLoading by remember { mutableStateOf(false) }

    var cartMessage by remember { mutableStateOf<String?>(null) }
    var cartRefreshVersion by remember { mutableIntStateOf(0) }
    var favoriteKeys by remember { mutableStateOf<Set<String>>(emptySet()) }
    var accountSection by remember { mutableStateOf(AccountSection.MENU) }
    var authFlowEmail by remember { mutableStateOf("") }
    var detailOpen by remember { mutableStateOf(false) }
    var chromeVisible by remember { mutableStateOf(true) }
    LaunchedEffect(pushTap) {
        val tap = pushTap ?: return@LaunchedEffect
        notificationInboxOpen = false
        when {
            tap.orderId.isNotBlank() -> {
                tab = AppTab.ACCOUNT
                accountSection = AccountSection.ORDERS
                notificationsRefreshVersion++
            }
            tap.type == "back_in_stock" && tap.productId.isNotBlank() -> {
                tab = AppTab.ACCOUNT
                accountSection = AccountSection.FAVORITES
            }
            tap.productId.isNotBlank() -> {
                tab = AppTab.CATALOG
                query = tap.productId
            }
            else -> {
                // Campaign and account-wide announcements open the inbox so the
                // exact title and message remain available after dismissing the push.
                notificationInboxOpen = true
                notificationsRefreshVersion++
            }
        }
        onPushTapHandled()
    }
    LaunchedEffect(tab, detailOpen) { chromeVisible = true }

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
        if (signedIn && (tab == AppTab.CART || cartRefreshVersion > 0)) {
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
                    notificationsEnabled = registration.isRegistered()
                    if (result.isFailure) PushDeviceRegistration.enqueueRetry(context)
                    notificationMessage = if (result.isSuccess && result.getOrNull() == true) "Notifikasi pesanan telah diaktifkan." else result.exceptionOrNull()?.message ?: "Notifikasi belum dapat diaktifkan."
                }
            } else {
                val registration = PushDeviceRegistration(context)
                registration.rememberPermissionConsent()
                notificationsEnabled = registration.isRegistered()
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

    authPromptMessage?.let { prompt ->
        Dialog(onDismissRequest = { authPromptMessage = null }) {
            Surface(
                modifier = Modifier.fillMaxWidth(.92f).widthIn(max = 360.dp),
                shape = RoundedCornerShape(24.dp),
                color = MaterialTheme.colorScheme.surface,
                tonalElevation = 3.dp,
                shadowElevation = 12.dp
            ) {
                Column(
                    Modifier.padding(horizontal = 24.dp, vertical = 22.dp),
                    verticalArrangement = Arrangement.spacedBy(16.dp)
                ) {
                    Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                        Surface(shape = RoundedCornerShape(15.dp), color = MaterialTheme.colorScheme.primaryContainer, modifier = Modifier.size(46.dp)) {
                            Box(contentAlignment = Alignment.Center) {
                                Icon(painterResource(R.drawable.ic_nav_account), contentDescription = null, tint = LunareaGreenText, modifier = Modifier.size(22.dp))
                            }
                        }
                        Column(verticalArrangement = Arrangement.spacedBy(2.dp)) {
                            Text("AKUN LUNAREA", style = MaterialTheme.typography.labelSmall, color = LunareaGreenText, fontWeight = FontWeight.Bold, letterSpacing = .8.sp)
                            Text("Lanjutkan dengan akun", style = MaterialTheme.typography.titleMedium, color = MaterialTheme.colorScheme.onSurface, fontWeight = FontWeight.SemiBold)
                        }
                    }
                    Text(prompt, style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant, lineHeight = 22.sp)
                    Column(verticalArrangement = Arrangement.spacedBy(4.dp)) {
                        Button(
                            onClick = {
                                authPromptMessage = null
                                message = null
                                accountSection = AccountSection.MENU
                                tab = AppTab.ACCOUNT
                            },
                            modifier = Modifier.fillMaxWidth().heightIn(min = 52.dp),
                            shape = RoundedCornerShape(14.dp)
                        ) { Text("Masuk atau daftar", fontWeight = FontWeight.SemiBold) }
                        TextButton(
                            onClick = { authPromptMessage = null },
                            modifier = Modifier.align(Alignment.CenterHorizontally).heightIn(min = 44.dp)
                        ) { Text("Jelajahi dulu", color = MaterialTheme.colorScheme.onSurfaceVariant) }
                    }
                }
            }
        }
    }

    if (showLogoutConfirmation) {
        AlertDialog(
            onDismissRequest = { showLogoutConfirmation = false },
            icon = { Icon(painterResource(R.drawable.ic_dialog_warning), contentDescription = null) },
            title = { Text("Keluar dari akun?") },
            text = { Text("Anda akan keluar dari akun Lunarea di perangkat ini dan perlu masuk kembali untuk melihat data akun." ) },
            confirmButton = {
                TextButton(enabled = !busy, onClick = {
                    showLogoutConfirmation = false
                    if (busy) return@TextButton
                    PushDeviceRegistration(context).stopForSignOut()
                    signedIn = false
                    favoriteKeys = emptySet()
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
                }) { Text("Ya, keluar", color = MaterialTheme.colorScheme.error) }
            },
            dismissButton = { TextButton(onClick = { showLogoutConfirmation = false }) { Text("Tetap masuk") } },
            shape = RoundedCornerShape(22.dp)
        )
    }

    pendingCartRemoval?.let { item ->
        AlertDialog(
            onDismissRequest = { pendingCartRemoval = null },
            icon = { Icon(painterResource(R.drawable.ic_dialog_warning), contentDescription = null) },
            title = { Text("Hapus dari keranjang?") },
            text = { Text("${item.name}${item.variant.takeIf(String::isNotBlank)?.let { " · $it" }.orEmpty()} akan dihapus dari keranjang.") },
            confirmButton = {
                TextButton(enabled = !cartLoading, onClick = {
                    pendingCartRemoval = null
                    scope.launch {
                        cartLoading = true
                        try {
                            val payload = JsonObject().apply { addProperty("product_id", item.id); addProperty("variant", item.variant) }
                            val response = ApiClient.get(context).deleteCartItem(payload)
                            if (response.isSuccessful && response.body()?.success == true) cartRefreshVersion++
                            else cartMessage = response.body()?.error?.message ?: "Produk belum dapat dihapus."
                        } catch (_: Exception) { cartMessage = "Koneksi belum tersedia. Coba lagi." }
                        finally { cartLoading = false }
                    }
                }) { Text("Hapus", color = MaterialTheme.colorScheme.error) }
            },
            dismissButton = { TextButton(onClick = { pendingCartRemoval = null }) { Text("Kembali") } },
            shape = RoundedCornerShape(22.dp)
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
            notificationsEnabled = registration.isRegistered()
            val result = registration.registerAfterOptIn()
            if (result.isFailure) {
                PushDeviceRegistration.enqueueRetry(context)
                notificationMessage = result.exceptionOrNull()?.message
                    ?: "Notifikasi belum terhubung. Periksa koneksi dan coba lagi."
            } else if (result.getOrNull() == true) {
                notificationsEnabled = true
                notificationMessage = "Notifikasi pesanan telah terhubung ke perangkat ini."
            }
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

    val accountNotice = if (tab == AppTab.ACCOUNT && accountSection == AccountSection.MENU) {
        message ?: notificationMessage
    } else null
    LaunchedEffect(accountNotice) {
        val shownNotice = accountNotice ?: return@LaunchedEffect
        delay(3_200)
        if (message == shownNotice) message = null
        if (notificationMessage == shownNotice) notificationMessage = null
    }

    Scaffold(
        containerColor = MaterialTheme.colorScheme.background,
        bottomBar = {
            AnimatedVisibility(
                visible = !detailOpen && chromeVisible,
                enter = slideInVertically(initialOffsetY = { it / 2 }, animationSpec = tween(150)) + fadeIn(animationSpec = tween(130)),
                exit = slideOutVertically(targetOffsetY = { it / 2 }, animationSpec = tween(130)) + fadeOut(animationSpec = tween(110))
            ) {
              NavigationBar(
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
                        alwaysShowLabel = false
                    )
                }
              }
            }
        }
    ) { insets ->
        Box(Modifier.fillMaxSize()) {
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
                startupComplete = startupComplete,
                modifier = Modifier.padding(insets), tab = tab, query = query, onQueryChange = { query = it.take(120) },
                signedIn = signedIn,
                favoriteKeys = favoriteKeys,
                cartItemCount = cartItems.sumOf { it.quantity },
                onFavoriteKeysChange = { favoriteKeys = it },
                onCartChanged = { cartRefreshVersion++ },
                onOpenCart = { detailOpen = false; tab = AppTab.CART },
                unreadNotificationCount = unreadNotificationCount,
                onOpenNotifications = { notificationInboxOpen = true },
                chromeVisible = chromeVisible,
                onChromeVisibilityChange = { chromeVisible = it },
                onPromoAction = { tab = AppTab.CATALOG },
                onLoginRequired = { reason -> authPromptMessage = reason },
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
                    pendingCartRemoval = item
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
            AppTab.CHECKOUT -> CheckoutScreen(Modifier.padding(insets), onBack = { tab = AppTab.CART }, onOpenOrders = { tab = AppTab.ACCOUNT; accountSection = AccountSection.ORDERS }, onCheckoutConfirmed = { cartRefreshVersion++ })
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
                onGoogleLogin = {
                    val activity = context as? Activity
                    if (activity == null) {
                        message = "Layar masuk Google belum dapat dibuka. Coba lagi."
                    } else scope.launch {
                        busy = true
                        val result = auth.loginWithGoogle(activity)
                        signedIn = auth.isSignedIn()
                        message = result.exceptionOrNull()?.message ?: if (result.isSuccess) "Berhasil masuk dengan Google." else "Masuk dengan Google belum berhasil."
                        if (result.isSuccess) { password = ""; loadProducts("") }
                        busy = false
                    }
                },
                onLogout = {
                    if (!busy) showLogoutConfirmation = true
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
                onOpenPoints = { accountSection = AccountSection.POINTS },
                onOpenRegistration = { accountSection = AccountSection.REGISTER },
                onOpenRecovery = { accountSection = AccountSection.RECOVERY },
                onOpenVerification = { authFlowEmail = identifier; accountSection = AccountSection.VERIFY_EMAIL },
                message = message
                )
                AccountSection.ORDERS -> OrdersScreen(Modifier.padding(insets), onBack = { accountSection = AccountSection.MENU })
                AccountSection.FAVORITES -> FavoritesScreen(Modifier.padding(insets), onBack = { accountSection = AccountSection.MENU })
                AccountSection.ADDRESSES -> AddressesScreen(Modifier.padding(insets), onBack = { accountSection = AccountSection.MENU })
                AccountSection.PROFILE -> ProfileScreen(Modifier.padding(insets), onBack = { accountSection = AccountSection.MENU })
                AccountSection.SECURITY -> SecurityScreen(Modifier.padding(insets), onBack = { accountSection = AccountSection.MENU }, onSessionEnded = { accountSection = AccountSection.MENU; signedIn = false })
                AccountSection.POINTS -> PointsScreen(Modifier.padding(insets), onBack = { accountSection = AccountSection.MENU })
                AccountSection.CHECKOUT -> CheckoutScreen(Modifier.padding(insets), onBack = { accountSection = AccountSection.MENU; tab = AppTab.CART }, onOpenOrders = { accountSection = AccountSection.ORDERS }, onCheckoutConfirmed = { cartRefreshVersion++ })
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
            AnimatedVisibility(
                visible = accountNotice != null,
                enter = slideInHorizontally(initialOffsetX = { it }, animationSpec = tween(240)) + fadeIn(tween(180)),
                exit = slideOutHorizontally(targetOffsetX = { it / 2 }, animationSpec = tween(180)) + fadeOut(tween(140)),
                modifier = Modifier.align(Alignment.TopEnd).padding(top = 12.dp, start = 16.dp, end = 12.dp).zIndex(20f)
            ) {
                accountNotice?.let { notice ->
                    val isError = listOf("gagal", "belum", "tidak", "periksa", "gangguan", "error")
                        .any { notice.contains(it, ignoreCase = true) }
                    val container = if (isError) MaterialTheme.colorScheme.errorContainer else MaterialTheme.colorScheme.inverseSurface
                    val content = if (isError) MaterialTheme.colorScheme.onErrorContainer else MaterialTheme.colorScheme.inverseOnSurface
                    Surface(
                        modifier = Modifier.fillMaxWidth().widthIn(max = 420.dp)
                            .clickable { if (message == notice) message = null; if (notificationMessage == notice) notificationMessage = null }
                            .semantics { liveRegion = LiveRegionMode.Polite },
                        shape = RoundedCornerShape(16.dp),
                        color = container,
                        shadowElevation = 4.dp
                    ) {
                        Row(
                            Modifier.padding(horizontal = 16.dp, vertical = 13.dp),
                            horizontalArrangement = Arrangement.spacedBy(12.dp),
                            verticalAlignment = Alignment.CenterVertically
                        ) {
                            Text(if (isError) "!" else "✓", color = content, fontWeight = FontWeight.Bold, fontSize = 18.sp)
                            Text(notice, color = content, style = MaterialTheme.typography.bodyMedium, lineHeight = 20.sp)
                        }
                    }
                }
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
            Spacer(Modifier.width(8.dp))
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
                                if (status == "awaiting_device") "Tersimpan di akun" else formatLunareaDateTime(item.get("created_at")?.asString.orEmpty()),
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
private fun AuthFlowHeader(title: String, subtitle: String, onBack: () -> Unit, showBack: Boolean = true) {
    Column(verticalArrangement = Arrangement.spacedBy(LunareaSpacing.xs)) {
        Image(
            painter = painterResource(R.drawable.logo_lunarea),
            contentDescription = "Lunarea Furniture",
            modifier = Modifier.width(136.dp).height(16.dp),
            contentScale = ContentScale.Fit
        )
        if (showBack) {
            TextButton(onClick = onBack, contentPadding = PaddingValues(horizontal = 0.dp, vertical = 2.dp)) {
                Icon(painterResource(R.drawable.ic_arrow_back), contentDescription = null, modifier = Modifier.size(18.dp))
                Spacer(Modifier.width(LunareaSpacing.xs))
                Text("Kembali", color = LunareaGreenText)
            }
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
private fun ProductCardSkeleton(modifier: Modifier = Modifier.fillMaxWidth()) {
    val shimmer = rememberLunareaShimmerBrush()
    Card(
        modifier = modifier,
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

    Column(modifier.fillMaxSize().imePadding().verticalScroll(rememberScrollState()).padding(horizontal = 20.dp, vertical = 18.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
        AuthFlowHeader("Buat akun Lunarea", "Daftar langsung di aplikasi untuk menyimpan alamat dan memantau pesanan.", onBack)
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(6.dp)) {
            repeat(2) { index ->
                Box(Modifier.weight(1f).height(4.dp).background(if (index == 0) MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.surfaceVariant, RoundedCornerShape(50)))
            }
        }
        Text("1 dari 2 · Informasi akun", style = MaterialTheme.typography.labelMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
        Column(Modifier.fillMaxWidth(), verticalArrangement = Arrangement.spacedBy(11.dp)) {
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
                        val captchaToken = AndroidRecaptcha.token(context.applicationContext as android.app.Application, "signup")
                        val payload = JsonObject().apply {
                            addProperty("name", name.trim()); addProperty("email", email.trim().lowercase(Locale.ROOT))
                            addProperty("phone", phone.trim()); addProperty("password", password)
                            addProperty("password_confirmation", confirmation); addProperty("consent", true)
                            addProperty("captcha_token", captchaToken); addProperty("captcha_platform", "android")
                        }
                        val response = ApiClient.get(context).register(payload)
                        val body = response.body()
                        if (response.isSuccessful && body?.success == true) onRegistered(email.trim().lowercase(Locale.ROOT))
                        else message = response.customerError("Pendaftaran belum berhasil. Periksa kembali data Anda.")
                    } catch (error: Exception) { message = error.message ?: "Koneksi belum tersedia. Data belum terkirim; coba lagi." }
                    finally { busy = false }
                }
            },
            enabled = formValid && !busy,
            modifier = Modifier.fillMaxWidth().height(52.dp),
            shape = MaterialTheme.shapes.medium
            ) { if (busy) CircularProgressIndicator(Modifier.size(19.dp), strokeWidth = 2.dp) else Text("Daftar dan kirim kode") }
        }
        Surface(color = MaterialTheme.colorScheme.surfaceVariant.copy(alpha = .55f), shape = RoundedCornerShape(14.dp)) {
            Text("Kode verifikasi akan dikirim ke email Anda dan berlaku selama 10 menit.", Modifier.fillMaxWidth().padding(14.dp), style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
        }
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
                    val captchaToken = AndroidRecaptcha.token(context.applicationContext as android.app.Application, "verification_resend")
                    val payload = JsonObject().apply {
                        addProperty("email", email.trim().lowercase(Locale.ROOT))
                        addProperty("captcha_token", captchaToken); addProperty("captcha_platform", "android")
                    }
                    val response = ApiClient.get(context).resendVerification(payload)
                    message = if (response.isSuccessful && response.body()?.success == true) "Jika akun menunggu verifikasi dan jeda terpenuhi, kode baru akan dikirim." else response.customerError("Kode belum dapat dikirim ulang.")
                } catch (error: Exception) { message = error.message ?: "Koneksi belum tersedia. Coba kirim ulang nanti." }
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
    var resendAvailableAt by rememberSaveable { mutableLongStateOf(0L) }
    var countdownNow by rememberSaveable { mutableLongStateOf(System.currentTimeMillis()) }
    val resendSeconds = ((resendAvailableAt - countdownNow).coerceAtLeast(0L) / 1000L).toInt()
    val newPasswordValid = newPassword.length >= 8 && newPassword.toByteArray().size <= 72 && newPassword.any(Char::isDigit)

    LaunchedEffect(stage, resendAvailableAt) {
        while (stage == 1 && System.currentTimeMillis() < resendAvailableAt) {
            delay(1_000)
            countdownNow = System.currentTimeMillis()
        }
        countdownNow = System.currentTimeMillis()
    }

    Column(modifier.fillMaxSize().imePadding().verticalScroll(rememberScrollState()).padding(horizontal = 20.dp, vertical = 18.dp), verticalArrangement = Arrangement.spacedBy(14.dp)) {
        AuthFlowHeader(if (stage == 0) "Pulihkan akun" else "Buat kata sandi baru", if (stage == 0) "Kami akan mengirim kode ke email terdaftar jika data akun cocok." else "Masukkan kode dari email dan tentukan kata sandi baru.", onBack)
        if (stage == 0) {
            OutlinedTextField(identity, { identity = it.take(254) }, modifier = Modifier.fillMaxWidth(), label = { Text("Email atau username") }, singleLine = true, shape = MaterialTheme.shapes.medium, keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Email, imeAction = androidx.compose.ui.text.input.ImeAction.Done))
        } else {
            Text("Kode dikirim untuk akun ${maskIdentity(identity)}", style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
            Text("Periksa kotak masuk dan folder spam. Kode berlaku selama 10 menit.", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
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
                        val captchaToken = AndroidRecaptcha.token(context.applicationContext as android.app.Application, "password_reset_request")
                        val payload = JsonObject().apply {
                            addProperty("identity", identity.trim())
                            addProperty("captcha_token", captchaToken); addProperty("captcha_platform", "android")
                        }
                        val response = api.requestPasswordReset(payload)
                        if (response.isSuccessful && response.body()?.success == true) {
                            stage = 1
                            resendAvailableAt = System.currentTimeMillis() + 60_000L
                            countdownNow = System.currentTimeMillis()
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
                } catch (error: Exception) { message = error.message ?: "Koneksi belum tersedia. Data belum terkirim; coba lagi." }
                finally { busy = false }
            }
        }, enabled = !busy && if (stage == 0) identity.trim().isNotEmpty() else code.length == 6 && newPasswordValid && confirmation == newPassword,
            modifier = Modifier.fillMaxWidth().height(50.dp), shape = MaterialTheme.shapes.medium) {
            if (busy) CircularProgressIndicator(Modifier.size(18.dp), strokeWidth = 2.dp) else Text(if (stage == 0) "Kirim kode pemulihan" else "Simpan kata sandi baru")
        }
        if (stage == 1) {
            TextButton(enabled = !busy && resendSeconds == 0, onClick = {
                scope.launch {
                    busy = true
                    try {
                        val captchaToken = AndroidRecaptcha.token(context.applicationContext as android.app.Application, "password_reset_request")
                        val response = ApiClient.get(context).requestPasswordReset(JsonObject().apply {
                            addProperty("identity", identity.trim())
                            addProperty("captcha_token", captchaToken); addProperty("captcha_platform", "android")
                        })
                        if (response.isSuccessful && response.body()?.success == true) {
                            resendAvailableAt = System.currentTimeMillis() + 60_000L
                            countdownNow = System.currentTimeMillis()
                            message = "Jika data cocok dan jeda permintaan terpenuhi, kode baru akan dikirim."
                        } else message = response.customerError("Kode belum dapat dikirim ulang.")
                    } catch (error: Exception) { message = error.message ?: "Koneksi belum tersedia. Coba kirim lagi nanti." }
                    finally { busy = false }
                }
            }) {
                Text(if (resendSeconds > 0) "Kirim ulang dalam ${resendSeconds / 60}:${(resendSeconds % 60).toString().padStart(2, '0')}" else "Kirim ulang kode")
            }
        }
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
private fun CopyablePaymentValue(label: String, value: String) {
    val context = LocalContext.current
    Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(8.dp)) {
        Column(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(3.dp)) {
            Text(label, style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
            Text(value, style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
        }
        OutlinedButton(
            onClick = {
                val clipboard = context.getSystemService(android.content.Context.CLIPBOARD_SERVICE) as ClipboardManager
                clipboard.setPrimaryClip(ClipData.newPlainText(label, value))
                Toast.makeText(context, "$label berhasil disalin", Toast.LENGTH_SHORT).show()
            },
            shape = RoundedCornerShape(12.dp),
            contentPadding = PaddingValues(horizontal = 12.dp, vertical = 8.dp)
        ) {
            Icon(painterResource(R.drawable.ic_payment_copy), contentDescription = null, modifier = Modifier.size(17.dp))
            Spacer(Modifier.width(6.dp))
            Text("Salin")
        }
    }
}

private fun createPaymentQrBitmap(contents: String, size: Int = 840): Bitmap? = runCatching {
    val hints = mapOf(
        EncodeHintType.ERROR_CORRECTION to ErrorCorrectionLevel.M,
        EncodeHintType.MARGIN to 2,
        EncodeHintType.CHARACTER_SET to "UTF-8"
    )
    val matrix = MultiFormatWriter().encode(contents, BarcodeFormat.QR_CODE, size, size, hints)
    val pixels = IntArray(size * size) { index ->
        if (matrix[index % size, index / size]) android.graphics.Color.BLACK else android.graphics.Color.WHITE
    }
    Bitmap.createBitmap(pixels, size, size, Bitmap.Config.ARGB_8888)
}.getOrNull()

@Composable
private fun PaymentQrCard(qrString: String) {
    val context = LocalContext.current
    val scope = rememberCoroutineScope()
    val qrBitmap by produceState<Bitmap?>(null, qrString) {
        value = withContext(Dispatchers.Default) { createPaymentQrBitmap(qrString) }
    }
    val saveLauncher = rememberLauncherForActivityResult(ActivityResultContracts.CreateDocument("image/png")) { uri ->
        val bitmap = qrBitmap
        if (uri != null && bitmap != null) scope.launch {
            val saved = withContext(Dispatchers.IO) {
                runCatching {
                    context.contentResolver.openOutputStream(uri)?.use { output ->
                        if (!bitmap.compress(Bitmap.CompressFormat.PNG, 100, output)) error("PNG encoding failed")
                    } ?: error("Destination unavailable")
                }.isSuccess
            }
            Toast.makeText(context, if (saved) "QRIS berhasil disimpan" else "QRIS belum berhasil disimpan", Toast.LENGTH_SHORT).show()
        }
    }
    Surface(shape = RoundedCornerShape(20.dp), color = MaterialTheme.colorScheme.surface) {
        Column(Modifier.fillMaxWidth().padding(18.dp), horizontalAlignment = Alignment.CenterHorizontally, verticalArrangement = Arrangement.spacedBy(10.dp)) {
            Text("Pindai QRIS", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold, modifier = Modifier.align(Alignment.Start))
            if (qrBitmap == null) {
                CircularProgressIndicator(Modifier.size(28.dp), strokeWidth = 2.dp)
            } else {
                Image(
                    bitmap = qrBitmap!!.asImageBitmap(), contentDescription = "Kode QRIS untuk pembayaran",
                    modifier = Modifier.size(224.dp).background(Color.White, RoundedCornerShape(12.dp)).padding(8.dp)
                )
                OutlinedButton(
                    onClick = { saveLauncher.launch("Lunarea-QRIS.png") },
                    modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp), shape = RoundedCornerShape(13.dp)
                ) {
                    Icon(painterResource(R.drawable.ic_payment_download), contentDescription = null, modifier = Modifier.size(18.dp))
                    Spacer(Modifier.width(8.dp)); Text("Simpan QRIS")
                }
            }
            Text("Buka aplikasi pembayaran yang mendukung QRIS, lalu pindai kode ini.", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
        }
    }
}

private fun paymentGuidance(method: String, bank: String = ""): String {
    if (method == "rekening") return "Transfer tepat sesuai total ke rekening Lunarea. Setelah transfer, buka detail pesanan dan unggah bukti pembayaran untuk diperiksa admin."
    return when (method.lowercase()) {
        "qris" -> "Bayar dengan memindai QRIS melalui aplikasi mobile banking atau dompet digital yang mendukung QRIS. Pastikan nominal sesuai sebelum mengonfirmasi."
        "echannel" -> "Gunakan Mandiri Online atau ATM Mandiri, pilih menu pembayaran Multipayment, lalu masukkan kode perusahaan dan kode tagihan yang tertera."
        "cstore" -> "Tunjukkan kode pembayaran kepada kasir gerai yang dipilih dan selesaikan pembayaran sebelum batas waktu."
        "bank_transfer" -> if (bank.isNotBlank()) "Buka aplikasi atau ATM bank ${bank.uppercase()}, pilih transfer ke Virtual Account, masukkan nomor VA, lalu konfirmasi nominal." else "Buka mobile banking atau ATM, pilih pembayaran Virtual Account, masukkan nomor VA, lalu konfirmasi nominal."
        "gopay" -> "Selesaikan pembayaran melalui aplikasi GoPay sesuai instruksi yang muncul setelah tombol pembayaran dibuka."
        else -> "Ikuti petunjuk pada metode pembayaran yang dipilih. Pastikan nominal dan batas waktu pembayaran sesuai informasi pesanan."
    }
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
    var detailPage by remember { mutableStateOf("list") }
    var checkingPayment by remember { mutableStateOf(false) }
    var trackingLoading by remember { mutableStateOf(false) }
    var cancelOrderId by remember { mutableStateOf<String?>(null) }
    var cancellingOrderId by remember { mutableStateOf<String?>(null) }
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
    cancelOrderId?.let { id ->
        AlertDialog(
            onDismissRequest = { cancelOrderId = null },
            icon = { Icon(painterResource(R.drawable.ic_dialog_warning), contentDescription = null) },
            title = { Text("Batalkan pesanan?") },
            text = { Text("Status pembayaran akan diperiksa terlebih dahulu. Jika pembayaran sudah berhasil atau bukti transfer sedang diverifikasi, pesanan tidak dapat dibatalkan dari aplikasi.") },
            confirmButton = {
                TextButton(enabled = cancellingOrderId != id, onClick = {
                    scope.launch {
                        cancellingOrderId = id
                        message = null
                        try {
                            val result = ApiClient.get(context).cancelOrder(id)
                            if (result.isSuccessful && result.body()?.success == true) {
                                message = "Pesanan berhasil dibatalkan."
                                selectedOrder = null
                                orderPage = 1
                                refresh++
                            } else message = result.body()?.error?.message ?: "Pesanan belum dapat dibatalkan. Periksa status pembayaran dan coba lagi."
                        } catch (_: Exception) { message = "Koneksi terputus. Status pembatalan belum dapat dipastikan; muat ulang pesanan sebelum mencoba lagi." }
                        finally { cancellingOrderId = null; cancelOrderId = null }
                    }
                }) {
                    if (cancellingOrderId == id) CircularProgressIndicator(Modifier.size(18.dp), strokeWidth = 2.dp)
                    else Text("Ya, batalkan", color = MaterialTheme.colorScheme.error)
                }
            },
            dismissButton = { TextButton(onClick = { cancelOrderId = null }) { Text("Kembali") } },
            shape = RoundedCornerShape(22.dp)
        )
    }
    BackHandler(enabled = detailPage != "list") {
        detailPage = "list"
        selectedOrder = null
        selectedTracking = null
    }

    val order = selectedOrder
    val paymentCheckInProgress by rememberUpdatedState(checkingPayment)
    LaunchedEffect(detailPage, order?.get("id")?.asString) {
        val id = order?.get("id")?.asString?.takeIf(String::isNotBlank)
        if (detailPage != "detail" || id == null) return@LaunchedEffect

        // Poll only while a pending gateway payment's detail is visible. The API
        // limits checks to four per order per minute, so keep this to two/minute.
        while (true) {
            delay(30_000)
            val current = selectedOrder ?: break
            if (paymentCheckInProgress || current.get("id")?.asString != id) continue
            val currentPayment = current.objectOrNull("payment")
            if (!isUnpaidOrderStatus(current.stringOrNull("status").orEmpty())) break

            try {
                if (currentPayment?.stringOrNull("type") == "rekening") {
                    // Manual transfers are confirmed by the store, not Midtrans.
                    val latest = ApiClient.get(context).order(id)
                        .takeIf { it.isSuccessful && it.body()?.success == true }
                        ?.body()?.data?.objectOrNull("order")
                    if (latest != null) selectedOrder = latest
                    if (latest != null && !isUnpaidOrderStatus(latest.stringOrNull("status").orEmpty())) break
                    continue
                }
                val result = ApiClient.get(context).paymentStatus(id)
                val snapshot = result.body()?.data?.objectOrNull("payment")
                if (!result.isSuccessful || result.body()?.success != true || snapshot == null) continue

                val latestOrder = runCatching {
                    ApiClient.get(context).order(id).takeIf { it.isSuccessful }
                        ?.body()?.data?.objectOrNull("order")
                }.getOrNull()
                val refreshed = (latestOrder ?: current).deepCopy()
                snapshot.stringOrNull("status")?.let { refreshed.addProperty("status", it) }
                val payment = refreshed.objectOrNull("payment") ?: JsonObject().also { refreshed.add("payment", it) }
                snapshot.stringOrNull("payment_status")?.let { payment.addProperty("status", it) }
                snapshot.stringOrNull("payment_type")?.let { payment.addProperty("type", it) }
                snapshot.stringOrNull("expiry_time")?.let { payment.addProperty("expiry_time", it) }
                snapshot.longOrNull("amount")?.let { payment.addProperty("gross_amount", it) }
                listOf("va_numbers", "payment_code", "bill_key", "biller_code", "qr_string").forEach { key ->
                    snapshot.get(key)?.takeUnless { it.isJsonNull }?.let { payment.add(key, it.deepCopy()) }
                }
                selectedOrder = refreshed
                if (isUnpaidOrderStatus(snapshot.stringOrNull("payment_status").orEmpty())) continue
                break
            } catch (_: Exception) {
                // Keep the existing order visible and try again on the next interval.
            }
        }
    }
    if (detailPage == "detail" && order != null) {
        val paymentInfo = order.getAsJsonObject("payment")
        val vaNumbers = paymentInfo?.getAsJsonArray("va_numbers")
        val paymentType = paymentInfo?.get("type")?.asString.orEmpty()
        val hasPaymentProof = order.get("has_payment_proof")?.takeUnless { it.isJsonNull }?.asBoolean == true
        val canCancel = order.get("status")?.asString.orEmpty().let(::isUnpaidOrderStatus) && !(paymentType == "rekening" && hasPaymentProof)
        Column(modifier.fillMaxSize().background(MaterialTheme.colorScheme.background)) {
            Column(Modifier.fillMaxWidth().background(MaterialTheme.colorScheme.surface).padding(horizontal = 20.dp, vertical = 10.dp)) {
                LunareaScreenHeader("Detail pesanan", { detailPage = "list"; selectedOrder = null }, order.get("id")?.asString.orEmpty())
            }
            LazyColumn(Modifier.fillMaxSize(), contentPadding = PaddingValues(start = 18.dp, top = 16.dp, end = 18.dp, bottom = 28.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                item {
                    Surface(shape = RoundedCornerShape(18.dp), color = MaterialTheme.colorScheme.surface) {
                        Column(Modifier.fillMaxWidth().padding(18.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                            Text("Status pesanan", style = MaterialTheme.typography.labelLarge, color = MaterialTheme.colorScheme.onSurfaceVariant)
                            Text(order.get("status")?.asString?.ifBlank { "Belum tersedia" } ?: "Belum tersedia", style = MaterialTheme.typography.titleLarge, fontWeight = FontWeight.Bold, color = LunareaGreenText)
                            Text("Total pembayaran", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                            Text(formatRupiah(order.get("amount")?.asLong ?: 0L), style = MaterialTheme.typography.headlineSmall, fontWeight = FontWeight.Bold)
                        }
                    }
                }
                item {
                    Surface(shape = RoundedCornerShape(18.dp), color = MaterialTheme.colorScheme.surface) {
                        Column(Modifier.fillMaxWidth().padding(18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
                            Text("Pembayaran", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
                            OrderInfoRow("Metode", when (paymentType) {
                                "rekening" -> "Transfer manual · BRI"
                                "bank_transfer" -> "Transfer bank · Virtual Account"
                                "echannel" -> "Mandiri Bill"
                                else -> paymentType.replace('_', ' ').uppercase().ifBlank { "Belum tersedia" }
                            })
                            OrderInfoRow("Status", paymentStatusLabel(paymentInfo?.stringOrNull("status")))
                            Text(
                                paymentGuidance(paymentType, vaNumbers?.firstOrNull()?.takeIf { it.isJsonObject }?.asJsonObject?.get("bank")?.asString.orEmpty()),
                                style = MaterialTheme.typography.bodySmall,
                                color = MaterialTheme.colorScheme.onSurfaceVariant
                            )
                            vaNumbers?.forEach { raw ->
                                if (raw.isJsonObject) {
                                    val va = raw.asJsonObject
                                    val label = if (paymentType == "rekening") "Rekening BRI Lunarea" else va.get("bank")?.asString?.uppercase()?.ifBlank { "Virtual Account" } ?: "Virtual Account"
                                    CopyablePaymentValue(label, va.get("number")?.asString?.ifBlank { "Belum tersedia" } ?: "Belum tersedia")
                                }
                            }
                            if (paymentType == "rekening") {
                                paymentInfo?.get("account_name")?.takeUnless { it.isJsonNull }?.asString?.takeIf(String::isNotBlank)?.let { OrderInfoRow("Atas nama", it) }
                                Text(if (hasPaymentProof) "Bukti transfer sudah diterima dan menunggu pemeriksaan admin." else "Transfer tepat sesuai total, lalu unggah bukti transfer melalui tombol di bawah.", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                            }
                            listOf("payment_code" to "Kode pembayaran", "bill_key" to "Kode tagihan", "biller_code" to "Kode penyedia").forEach { (field, label) ->
                                paymentInfo?.get(field)?.takeIf { !it.isJsonNull }?.asString?.takeIf(String::isNotBlank)?.let { CopyablePaymentValue(label, it) }
                            }
                            paymentInfo?.get("expiry_time")?.takeIf { !it.isJsonNull }?.asString?.takeIf(String::isNotBlank)?.let { OrderInfoRow("Batas pembayaran", formatLunareaDateTime(it)) }
                            paymentInfo?.stringOrNull("qr_string")?.takeIf { paymentType == "qris" && it.isNotBlank() }?.let { PaymentQrCard(it) }
                            val actions = paymentInfo?.getAsJsonArray("actions")
                            actions?.forEach { raw ->
                                if (raw.isJsonObject) {
                                    val action = raw.asJsonObject
                                    val url = action.get("url")?.asString.orEmpty()
                                    if (url.startsWith("https://", ignoreCase = true)) OutlinedButton(
                                        onClick = { openWebsite(context, url) }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(12.dp)
                                    ) { Text(action.get("name")?.asString?.replace('_', ' ')?.replaceFirstChar(Char::uppercase) ?: "Buka pembayaran") }
                                }
                            }
                            if ((paymentInfo?.get("type")?.asString.orEmpty() == "" || paymentInfo?.get("status")?.asString.orEmpty() == "pending") && (vaNumbers == null || vaNumbers.size() == 0)) {
                                Text("Instruksi pembayaran belum tersedia. Periksa status pembayaran untuk memuat data terbaru.", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                            }
                        }
                    }
                }
                item {
                    Surface(shape = RoundedCornerShape(18.dp), color = MaterialTheme.colorScheme.surface) {
                        Column(Modifier.fillMaxWidth().padding(18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
                            Text("Produk dalam pesanan", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
                            order.getAsJsonArray("items")?.forEach { raw ->
                                if (raw.isJsonObject) {
                                    val line = raw.asJsonObject
                                    Column(verticalArrangement = Arrangement.spacedBy(3.dp)) {
                                        Text(line.get("name")?.asString?.ifBlank { "Produk" } ?: "Produk", style = MaterialTheme.typography.bodyMedium, fontWeight = FontWeight.Medium)
                                        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                            Text("${line.get("quantity")?.asInt ?: 1} × ${formatRupiah(line.get("unit_price")?.asLong ?: 0L)}", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                                            Text(formatRupiah((line.get("quantity")?.asLong ?: 1L) * (line.get("unit_price")?.asLong ?: 0L)), style = MaterialTheme.typography.bodySmall, fontWeight = FontWeight.SemiBold)
                                        }
                                    }
                                    HorizontalDivider(color = MaterialTheme.colorScheme.outlineVariant.copy(alpha = .6f))
                                }
                            }
                        }
                    }
                }
                item {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                        OutlinedButton(
                            onClick = {
                                val id = order.get("id")?.asString ?: return@OutlinedButton
                                scope.launch {
                                    checkingPayment = true
                                    try {
                                        val result = ApiClient.get(context).paymentStatus(id)
                                        if (result.isSuccessful && result.body()?.success == true) {
                                            val snapshot = result.body()?.data?.objectOrNull("payment")
                                            if (snapshot == null) {
                                                message = "Status pembayaran belum dapat dibaca. Coba lagi."
                                            } else {
                                                // The status endpoint returns a verified provider snapshot. The order
                                                // endpoint may still contain the older webhook-backed DB state.
                                                val latestOrder = runCatching {
                                                    ApiClient.get(context).order(id).takeIf { it.isSuccessful }
                                                        ?.body()?.data?.objectOrNull("order")
                                                }.getOrNull()
                                                val refreshed = (latestOrder ?: order).deepCopy()
                                                snapshot.stringOrNull("status")?.let { refreshed.addProperty("status", it) }
                                                val payment = refreshed.objectOrNull("payment") ?: JsonObject().also { refreshed.add("payment", it) }
                                                snapshot.stringOrNull("payment_status")?.let { payment.addProperty("status", it) }
                                                snapshot.stringOrNull("payment_type")?.let { payment.addProperty("type", it) }
                                                snapshot.stringOrNull("expiry_time")?.let { payment.addProperty("expiry_time", it) }
                                                snapshot.longOrNull("amount")?.let { payment.addProperty("gross_amount", it) }
                                                listOf("va_numbers", "payment_code", "bill_key", "biller_code", "qr_string").forEach { key ->
                                                    snapshot.get(key)?.takeUnless { it.isJsonNull }?.let { payment.add(key, it.deepCopy()) }
                                                }
                                                selectedOrder = refreshed
                                                val label = paymentStatusLabel(snapshot.stringOrNull("payment_status"))
                                                message = if (snapshot.stringOrNull("source") == "gateway") {
                                                    "Status pembayaran berhasil diperiksa: $label."
                                                } else "Status pembayaran: $label."
                                            }
                                        } else message = result.body()?.error?.message ?: "Status pembayaran belum dapat diperiksa."
                                    } catch (_: Exception) { message = "Koneksi belum tersedia. Status pesanan tidak berubah." }
                                    finally { checkingPayment = false }
                                }
                            }, enabled = !checkingPayment, modifier = Modifier.weight(1f).heightIn(min = 48.dp), shape = RoundedCornerShape(13.dp)
                        ) { if (checkingPayment) CircularProgressIndicator(Modifier.size(18.dp), strokeWidth = 2.dp) else Text("Periksa pembayaran") }
                        if (canCancel) OutlinedButton(onClick = { cancelOrderId = order.get("id")?.asString }, modifier = Modifier.weight(1f).heightIn(min = 48.dp), shape = RoundedCornerShape(13.dp)) { Text("Batalkan") }
                    }
                    if (paymentType == "rekening" && canCancel) {
                        TextButton(onClick = { selectedOrderId = order.get("id")?.asString; proofPicker.launch("image/*") }, modifier = Modifier.fillMaxWidth()) { Text("Kirim bukti transfer") }
                    }
                    if (paymentType == "rekening" && hasPaymentProof) Text("Pembatalan dinonaktifkan selama bukti pembayaran diperiksa.", modifier = Modifier.padding(top = 6.dp), style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                    message?.let { Text(it, modifier = Modifier.padding(top = 8.dp), style = MaterialTheme.typography.bodySmall, color = if (it.contains("diperbarui", true)) LunareaGreenText else MaterialTheme.colorScheme.error) }
                }
            }
        }
        return
    }

    if (detailPage == "tracking" && selectedTracking != null) {
        val tracking = selectedTracking!!
        // API snapshots intentionally return JSON null for fields that have not
        // been assigned yet (especially live_tracking before courier handoff).
        // Gson's getAsJsonObject/getAsJsonArray throw on JsonNull, so guard types.
        val live = tracking.objectOrNull("tracking") ?: tracking
        val liveData = live.objectOrNull("live_tracking")
        Column(modifier.fillMaxSize().background(MaterialTheme.colorScheme.background)) {
            Column(Modifier.fillMaxWidth().background(MaterialTheme.colorScheme.surface).padding(horizontal = 20.dp, vertical = 10.dp)) {
                LunareaScreenHeader("Lacak pesanan", { detailPage = "list"; selectedTracking = null }, "Pesanan ${live.stringOrNull("order_id").orEmpty()}")
            }
            LazyColumn(Modifier.fillMaxSize(), contentPadding = PaddingValues(start = 18.dp, top = 16.dp, end = 18.dp, bottom = 28.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                item {
                    Surface(shape = RoundedCornerShape(18.dp), color = MaterialTheme.colorScheme.surface) {
                        Column(Modifier.fillMaxWidth().padding(20.dp), verticalArrangement = Arrangement.spacedBy(9.dp)) {
                            Text("Status pengiriman", style = MaterialTheme.typography.labelLarge, color = MaterialTheme.colorScheme.onSurfaceVariant)
                            Text(live.stringOrNull("status")?.ifBlank { "Menunggu informasi pengiriman" } ?: "Menunggu informasi pengiriman", style = MaterialTheme.typography.titleLarge, fontWeight = FontWeight.Bold, color = LunareaGreenText)
                            OrderInfoRow("Kurir", live.stringOrNull("courier")?.ifBlank { "Belum ditentukan" } ?: "Belum ditentukan")
                            OrderInfoRow("Nomor resi", live.stringOrNull("tracking_number")?.ifBlank { "Belum tersedia" } ?: "Belum tersedia")
                            OrderInfoRow("Status pesanan", live.stringOrNull("order_status")?.ifBlank { "Belum tersedia" } ?: "Belum tersedia")
                            live.stringOrNull("updated_at")?.takeIf(String::isNotBlank)?.let { OrderInfoRow("Pembaruan terakhir", formatLunareaDateTime(it)) }
                        }
                    }
                }
                item {
                    Surface(shape = RoundedCornerShape(18.dp), color = MaterialTheme.colorScheme.surface) {
                        Column(Modifier.fillMaxWidth().padding(18.dp), verticalArrangement = Arrangement.spacedBy(9.dp)) {
                            Text("Perkembangan", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
                            val summaryObject = liveData?.objectOrNull("summary")
                            val summary = summaryObject?.entrySet()?.mapNotNull { (key, value) ->
                                if (value.isJsonNull || value.isJsonObject || value.isJsonArray) null
                                else value.asString.takeIf(String::isNotBlank)?.let { "${key.replace('_', ' ').replaceFirstChar(Char::uppercase)}: $it" }
                            }?.joinToString(" · ").orEmpty()
                            when {
                                trackingLoading -> Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(10.dp)) { CircularProgressIndicator(Modifier.size(18.dp), strokeWidth = 2.dp); Text("Memeriksa pembaruan…", style = MaterialTheme.typography.bodySmall) }
                                summary.isNotBlank() -> Text(summary, style = MaterialTheme.typography.bodyMedium)
                                live.stringOrNull("tracking_number").isNullOrBlank() -> Text("Informasi kurir dan resi akan muncul setelah pesanan diproses dan dikirim.", style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                                liveData?.get("success")?.takeUnless { it.isJsonNull }?.let { runCatching { it.asBoolean }.getOrNull() } == false -> Text(liveData.stringOrNull("message") ?: "Pembaruan langsung belum tersedia. Informasi tersimpan tetap ditampilkan.", style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                                else -> Text("Belum ada pembaruan perjalanan dari kurir.", style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                            }
                            val history = liveData?.arrayOrNull("history")
                            history?.forEach { event ->
                                if (event.isJsonObject) {
                                    val entry = event.asJsonObject
                                    val description = listOf("description", "status", "activity", "manifest_description")
                                        .firstNotNullOfOrNull { key -> entry.get(key)?.takeIf { !it.isJsonNull }?.toString()?.trim('"')?.takeIf(String::isNotBlank) }
                                    Column(Modifier.fillMaxWidth().padding(vertical = 4.dp), verticalArrangement = Arrangement.spacedBy(3.dp)) {
                                        Text(description ?: "Pembaruan pengiriman", style = MaterialTheme.typography.bodyMedium, fontWeight = FontWeight.Medium)
                                        val eventDate = listOf("datetime", "manifest_date").firstNotNullOfOrNull { key ->
                                            entry.get(key)?.takeIf { !it.isJsonNull }?.toString()?.trim('"')?.takeIf(String::isNotBlank)
                                        } ?: run {
                                            val date = entry.get("date")?.takeIf { !it.isJsonNull }?.toString()?.trim('"')?.takeIf(String::isNotBlank)
                                            val time = entry.get("time")?.takeIf { !it.isJsonNull }?.toString()?.trim('"')?.takeIf(String::isNotBlank)
                                            listOfNotNull(date, time).joinToString(" ").ifBlank { null }
                                        }
                                        eventDate?.let { Text(formatLunareaDateTime(it), style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant) }
                                    }
                                    HorizontalDivider(color = MaterialTheme.colorScheme.outlineVariant.copy(alpha = .6f))
                                }
                            }
                            val id = live.stringOrNull("order_id").orEmpty()
                            OutlinedButton(onClick = {
                                if (id.isBlank()) return@OutlinedButton
                                scope.launch {
                                    trackingLoading = true
                                    try {
                                        val response = ApiClient.get(context).tracking(id)
                                        val updated = response.body()?.data
                                        if (response.isSuccessful && response.body()?.success == true && updated != null) {
                                            selectedTracking = updated
                                            message = null
                                        }
                                        else message = response.body()?.error?.message ?: "Status pengiriman belum dapat diperbarui."
                                    } catch (_: Exception) { message = "Koneksi belum tersedia. Coba lagi nanti." }
                                    finally { trackingLoading = false }
                                }
                            }, enabled = !trackingLoading, modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp), shape = RoundedCornerShape(13.dp)) { Text("Perbarui status") }
                            message?.let { Text(it, style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.error) }
                        }
                    }
                }
            }
        }
        return
    }

    Column(modifier.fillMaxSize().background(MaterialTheme.colorScheme.background).padding(horizontal = 18.dp, vertical = 14.dp)) {
        LunareaScreenHeader("Pesanan saya", onBack, "Pantau pembayaran dan pengiriman")
        if (loading) LinearProgressIndicator(modifier = Modifier.fillMaxWidth())
        message?.let { EmptyState(it, {}, "") }
        LazyColumn(verticalArrangement = Arrangement.spacedBy(10.dp), contentPadding = PaddingValues(bottom = 20.dp)) {
            items(rows, key = { it.get("id")?.asString.orEmpty() }) { order ->
                val id = order.get("id")?.asString.orEmpty()
                val status = order.get("status")?.asString ?: "Status diperbarui"
                val statusLower = status.lowercase(Locale.ROOT)
                val statusTint = when {
                    statusLower.contains("batal") || statusLower.contains("gagal") || statusLower.contains("kedaluwarsa") -> MaterialTheme.colorScheme.error
                    statusLower.contains("selesai") || statusLower.contains("diterima") || statusLower.contains("proses") -> LunareaGreenText
                    else -> MaterialTheme.colorScheme.tertiary
                }
                Surface(shape = RoundedCornerShape(18.dp), color = MaterialTheme.colorScheme.surface, tonalElevation = 1.dp, modifier = Modifier.fillMaxWidth()) {
                    Column(Modifier.fillMaxWidth().padding(16.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
                        Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
                            Column(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(3.dp)) {
                                Text("Nomor pesanan", style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                                Text(id, style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.SemiBold, maxLines = 1, overflow = TextOverflow.Ellipsis)
                            }
                            Surface(shape = RoundedCornerShape(50), color = statusTint.copy(alpha = .10f)) {
                                Text(status, Modifier.padding(horizontal = 10.dp, vertical = 6.dp), color = statusTint, style = MaterialTheme.typography.labelSmall, fontWeight = FontWeight.SemiBold, maxLines = 1)
                            }
                        }
                        HorizontalDivider(color = MaterialTheme.colorScheme.outlineVariant.copy(alpha = .7f))
                        Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.Bottom) {
                            Column(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(3.dp)) {
                                Text("Total pesanan", style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                                Text(formatRupiah(order.get("amount")?.asLong ?: 0L), style = MaterialTheme.typography.titleMedium, color = LunareaGreenText, fontWeight = FontWeight.Bold)
                            }
                            TextButton(onClick = {
                            scope.launch {
                                try {
                                    val response = ApiClient.get(context).order(id)
                                    val loaded = response.body()?.data?.getAsJsonObject("order")
                                    if (response.isSuccessful && response.body()?.success == true && loaded != null) {
                                        selectedOrder = loaded
                                        selectedOrderId = id
                                        detailPage = "detail"
                                        message = null
                                    } else message = response.body()?.error?.message ?: "Detail pesanan belum dapat dimuat."
                                } catch (_: Exception) { message = "Detail pesanan belum dapat dimuat." }
                            }
                            }) { Text("Detail") }
                        }
                        OutlinedButton(onClick = {
                            scope.launch {
                                trackingLoading = true
                                try {
                                    val response = ApiClient.get(context).tracking(id)
                                    val result = response.body()?.data
                                    if (response.isSuccessful && response.body()?.success == true && result != null) {
                                        selectedTracking = result
                                        detailPage = "tracking"
                                        message = null
                                    } else message = response.body()?.error?.message ?: "Informasi pengiriman belum dapat dimuat."
                                } catch (_: Exception) { message = "Informasi pengiriman belum dapat dimuat." }
                                finally { trackingLoading = false }
                            }
                        }, enabled = !trackingLoading, modifier = Modifier.fillMaxWidth().heightIn(min = 44.dp), shape = RoundedCornerShape(12.dp)) { Text(if (trackingLoading) "Memuat pengiriman…" else "Lihat pengiriman") }
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
private fun OrderInfoRow(label: String, value: String) {
    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.Top) {
        Text(label, style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant, modifier = Modifier.weight(0.9f))
        Text(value, style = MaterialTheme.typography.bodySmall, fontWeight = FontWeight.Medium, modifier = Modifier.weight(1.1f), textAlign = androidx.compose.ui.text.style.TextAlign.End)
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
    Column(modifier.fillMaxSize().background(MaterialTheme.colorScheme.background).padding(horizontal = 18.dp, vertical = 14.dp)) {
        LunareaScreenHeader("Produk favorit", onBack, "Koleksi furnitur yang Anda simpan")
        if (loading) LinearProgressIndicator(modifier = Modifier.fillMaxWidth())
        message?.let { EmptyState(it, {}, "") }
        LazyColumn(verticalArrangement = Arrangement.spacedBy(10.dp)) {
            items(rows, key = { it.get("product_id")?.asString.orEmpty() + it.get("variant")?.asString.orEmpty() }) { product ->
                val id = product.get("product_id")?.asString.orEmpty()
                val variant = product.get("variant")?.asString.orEmpty()
                Surface(shape = RoundedCornerShape(18.dp), color = MaterialTheme.colorScheme.surface, tonalElevation = 1.dp, modifier = Modifier.fillMaxWidth()) {
                    Row(Modifier.fillMaxWidth().padding(12.dp), verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                        Surface(shape = RoundedCornerShape(13.dp), color = MaterialTheme.colorScheme.surfaceVariant, modifier = Modifier.size(82.dp)) {
                            if (product.has("image_url") && !product.get("image_url").isJsonNull) {
                                AsyncImage(model = product.get("image_url").asString, contentDescription = product.get("name")?.asString, modifier = Modifier.fillMaxSize(), contentScale = ContentScale.Crop)
                            } else Box(contentAlignment = Alignment.Center) { Text("L", style = MaterialTheme.typography.titleLarge, color = LunareaGreenText, fontWeight = FontWeight.Bold) }
                        }
                        Column(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(5.dp)) {
                            Text(product.get("name")?.asString ?: "Produk Lunarea", style = MaterialTheme.typography.bodyMedium, fontWeight = FontWeight.SemiBold, maxLines = 2, overflow = TextOverflow.Ellipsis)
                            if (variant.isNotBlank()) Text(variant, style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.onSurfaceVariant, maxLines = 1, overflow = TextOverflow.Ellipsis)
                            Text(formatRupiah(product.get("price")?.asLong ?: 0L), style = MaterialTheme.typography.titleSmall, color = LunareaGreenText, fontWeight = FontWeight.Bold)
                            TextButton(onClick = {
                            scope.launch {
                                try {
                                    val payload = JsonObject().apply { addProperty("product_id", id); addProperty("variant", variant) }
                                    val response = ApiClient.get(context).deleteWishlistItem(payload)
                                    if (response.isSuccessful && response.body()?.success == true) refresh++
                                    else message = response.body()?.error?.message ?: "Produk belum dapat dihapus dari favorit."
                                } catch (_: Exception) { message = "Koneksi belum tersedia. Coba lagi." }
                            }
                            }, contentPadding = PaddingValues(horizontal = 0.dp, vertical = 0.dp), modifier = Modifier.heightIn(min = 36.dp)) { Text("Hapus dari favorit", color = MaterialTheme.colorScheme.error, style = MaterialTheme.typography.labelLarge) }
                        }
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
    var addressesLoadError by remember { mutableStateOf(false) }
    var locationBusy by remember { mutableStateOf(false) }
    var message by remember { mutableStateOf<String?>(null) }
    var messageIsError by remember { mutableStateOf(false) }
    var refresh by remember { mutableIntStateOf(0) }

    suspend fun loadAddressRows() {
        val response = ApiClient.get(context).addresses()
        val data = response.body()?.data
        val values = data?.getAsJsonArray("addresses")
        if (response.isSuccessful && response.body()?.success == true && values != null) {
            addresses = values.mapNotNull { it.takeIf { v -> v.isJsonObject }?.asJsonObject }
            addressesLoadError = false
        } else {
            addressesLoadError = true
            message = response.customerError("Alamat belum dapat dimuat."); messageIsError = true
        }
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

    suspend fun fillFromCurrentLocation() {
        locationBusy = true
        message = null
        messageIsError = false
        try {
            val suggestion = resolveCurrentAddress(context)
                ?: throw IllegalStateException("Lokasi belum berhasil didapat. Pastikan lokasi perangkat aktif lalu coba lagi.")
            val api = ApiClient.get(context)
            val provinceRows = if (provinces.isNotEmpty()) provinces else {
                val response = api.provinces()
                if (!response.isSuccessful || response.body()?.success != true) {
                    throw IllegalStateException(response.customerError("Daftar provinsi belum dapat dimuat."))
                }
                options(response.body()?.data).also { provinces = it }
            }
            fun exactMatch(rows: List<RegionOption>, candidates: List<String>): RegionOption? {
                val normalized = candidates.map(::normalizeRegionLabel).filter(String::isNotBlank).toSet()
                return rows.firstOrNull { normalizeRegionLabel(it.label) in normalized }
            }

            val selectedProvince = suggestion.province?.let { exactMatch(provinceRows, listOf(it)) }
            var cityRows = emptyList<RegionOption>()
            var districtRows = emptyList<RegionOption>()
            var villageRows = emptyList<RegionOption>()
            var selectedCity: RegionOption? = null
            var selectedDistrict: RegionOption? = null
            var selectedVillage: RegionOption? = null

            if (selectedProvince != null) {
                val response = api.cities(selectedProvince.id)
                if (response.isSuccessful && response.body()?.success == true) {
                    cityRows = options(response.body()?.data)
                    selectedCity = exactMatch(cityRows, suggestion.cityCandidates)
                }
            }
            if (selectedCity != null) {
                val response = api.districts(selectedCity!!.id)
                if (response.isSuccessful && response.body()?.success == true) {
                    districtRows = options(response.body()?.data)
                    selectedDistrict = exactMatch(districtRows, suggestion.districtCandidates)
                }
            }
            if (selectedDistrict != null) {
                val response = api.villages(selectedDistrict!!.id)
                if (response.isSuccessful && response.body()?.success == true) {
                    villageRows = options(response.body()?.data)
                    selectedVillage = exactMatch(villageRows, suggestion.villageCandidates)
                }
            }

            province = selectedProvince
            cities = cityRows
            city = selectedCity
            districts = districtRows
            district = selectedDistrict
            villages = villageRows
            village = selectedVillage
            if (suggestion.addressDetail.isNotBlank()) detail = suggestion.addressDetail
            message = if (selectedProvince != null && selectedCity != null && selectedDistrict != null && selectedVillage != null) {
                "Lokasi ditemukan. Periksa kembali alamat sebelum disimpan."
            } else {
                "Lokasi jalan terisi. Pilih atau periksa wilayah yang belum cocok secara otomatis."
            }
        } catch (error: Exception) {
            message = error.message?.takeIf(String::isNotBlank) ?: "Lokasi belum berhasil digunakan. Silakan pilih alamat secara manual."
            messageIsError = true
        } finally {
            locationBusy = false
        }
    }

    val locationPermissionLauncher = rememberLauncherForActivityResult(
        ActivityResultContracts.RequestMultiplePermissions()
    ) { result ->
        if (result[android.Manifest.permission.ACCESS_FINE_LOCATION] == true ||
            result[android.Manifest.permission.ACCESS_COARSE_LOCATION] == true
        ) scope.launch { fillFromCurrentLocation() }
        else { message = "Izin lokasi belum diberikan. Anda tetap dapat mengisi alamat secara manual."; messageIsError = true }
    }

    LaunchedEffect(refresh) {
        busy = true
        try {
            loadAddressRows()
            val response = ApiClient.get(context).provinces()
            if (response.isSuccessful && response.body()?.success == true) provinces = options(response.body()?.data)
            else { message = response.customerError("Daftar provinsi belum dapat dimuat."); messageIsError = true }
        } catch (_: Exception) {
            addressesLoadError = addresses.isEmpty()
            message = "Koneksi belum tersedia. Coba muat ulang alamat."; messageIsError = true
        }
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
                                messageIsError = false
                                addressPendingDelete = null
                                refresh++
                            } else { message = response.customerError("Alamat belum dapat dihapus."); messageIsError = true }
                        } catch (_: Exception) { message = "Koneksi belum tersedia. Coba lagi."; messageIsError = true }
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
        if (message != null) {
            Surface(
                modifier = Modifier.fillMaxWidth().padding(top = 8.dp, bottom = 4.dp),
                shape = RoundedCornerShape(12.dp),
                color = if (messageIsError) MaterialTheme.colorScheme.errorContainer else MaterialTheme.colorScheme.primaryContainer
            ) {
                Row(Modifier.padding(horizontal = 12.dp, vertical = 10.dp), verticalAlignment = Alignment.CenterVertically) {
                    Icon(
                        painterResource(if (messageIsError) R.drawable.ic_dialog_warning else R.drawable.ic_dialog_info),
                        contentDescription = null,
                        tint = if (messageIsError) MaterialTheme.colorScheme.onErrorContainer else MaterialTheme.colorScheme.onPrimaryContainer,
                        modifier = Modifier.size(18.dp)
                    )
                    Spacer(Modifier.width(8.dp))
                    Text(
                        message.orEmpty(),
                        modifier = Modifier.weight(1f),
                        color = if (messageIsError) MaterialTheme.colorScheme.onErrorContainer else MaterialTheme.colorScheme.onPrimaryContainer,
                        style = MaterialTheme.typography.bodySmall
                    )
                    TextButton(onClick = { message = null }) { Text("Tutup") }
                }
            }
        }
        if (showForm) {
            Row(Modifier.fillMaxWidth().padding(top = 8.dp, bottom = 4.dp), verticalAlignment = Alignment.CenterVertically) {
                Column(Modifier.weight(1f)) {
                    Text(if (editingAddressId == null) "Tambah alamat" else "Ubah alamat", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
                    Text("Lengkapi penerima dan wilayah pengiriman.", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                }
                TextButton(enabled = !busy && !locationBusy, onClick = {
                    showForm = false
                    editingAddressId = null
                    label = ""; recipient = ""; phone = ""; detail = ""
                    province = null; city = null; district = null; village = null
                    cities = emptyList(); districts = emptyList(); villages = emptyList()
                    cityError = null; districtError = null; villageError = null
                    message = null
                }) { Text("Batal") }
            }
            Column(
                Modifier.weight(1f).fillMaxWidth().verticalScroll(rememberScrollState()),
                verticalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                    OutlinedTextField(label, { label = it }, modifier = Modifier.fillMaxWidth(), label = { Text("Label alamat *") }, supportingText = { Text("Contoh: Rumah atau Kantor") }, singleLine = true, enabled = !busy)
                    OutlinedTextField(recipient, { recipient = it }, modifier = Modifier.fillMaxWidth(), label = { Text("Nama penerima *") }, singleLine = true, enabled = !busy)
                    OutlinedTextField(phone, { phone = it }, modifier = Modifier.fillMaxWidth(), label = { Text("Nomor ponsel *") }, keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Phone), singleLine = true, enabled = !busy)
                    OutlinedButton(
                        onClick = {
                            val fineGranted = ContextCompat.checkSelfPermission(context, android.Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED
                            val coarseGranted = ContextCompat.checkSelfPermission(context, android.Manifest.permission.ACCESS_COARSE_LOCATION) == PackageManager.PERMISSION_GRANTED
                            if (fineGranted || coarseGranted) scope.launch { fillFromCurrentLocation() }
                            else locationPermissionLauncher.launch(arrayOf(
                                android.Manifest.permission.ACCESS_COARSE_LOCATION,
                                android.Manifest.permission.ACCESS_FINE_LOCATION
                            ))
                        },
                        enabled = !locationBusy && !busy,
                        modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp),
                        shape = RoundedCornerShape(12.dp)
                    ) {
                        if (locationBusy) CircularProgressIndicator(Modifier.size(18.dp), strokeWidth = 2.dp)
                        else Icon(painterResource(R.drawable.ic_my_location), contentDescription = null, modifier = Modifier.size(18.dp))
                        Spacer(Modifier.width(8.dp))
                        Text(if (locationBusy) "Mencari lokasi…" else "Gunakan lokasi saat ini")
                    }
                    Text(
                        "Lokasi hanya dipakai sekali untuk membantu mengisi alamat. Periksa kembali hasilnya sebelum disimpan.",
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
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
                        }
                    }
                    RegionSelector("Kelurahan", village, villages, district != null && !loadingVillages && villages.isNotEmpty(), loadingVillages) { village = it }
                    villageError?.let { error ->
                        Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(4.dp)) {
                            Text(error, Modifier.weight(1f), color = MaterialTheme.colorScheme.error, style = MaterialTheme.typography.bodySmall)
                        }
                    }
                    OutlinedTextField(detail, { detail = it }, modifier = Modifier.fillMaxWidth(), label = { Text("Jalan, nomor rumah, dan patokan *") }, minLines = 2, enabled = !busy)
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
                                        messageIsError = false
                                        showForm = false; editingAddressId = null; label = ""; recipient = ""; phone = ""; detail = ""; province = null; city = null; district = null; village = null; refresh++
                                    } else { message = response.customerError("Alamat belum tersimpan."); messageIsError = true }
                                } catch (_: Exception) { message = "Koneksi belum tersedia. Coba lagi."; messageIsError = true }
                                finally { busy = false }
                            }
                        }, modifier = Modifier.fillMaxWidth().heightIn(min = 52.dp), shape = RoundedCornerShape(14.dp)
                    ) {
                        if (busy) CircularProgressIndicator(Modifier.size(18.dp), strokeWidth = 2.dp)
                        else Text(if (editingAddressId == null) "Simpan alamat" else "Simpan perubahan")
                    }
            }
        } else {
            Row(Modifier.fillMaxWidth().padding(top = 10.dp, bottom = 8.dp), verticalAlignment = Alignment.CenterVertically) {
                Column(Modifier.weight(1f)) {
                    Text("Buku alamat", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
                    Text("${addresses.size} alamat tersimpan", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                }
                Button(
                    enabled = !busy,
                    onClick = {
                        message = null
                        label = ""; recipient = ""; phone = ""; detail = ""
                        province = null; city = null; district = null; village = null
                        cities = emptyList(); districts = emptyList(); villages = emptyList()
                        cityError = null; districtError = null; villageError = null
                        editingAddressId = null
                        showForm = true
                    },
                    contentPadding = PaddingValues(horizontal = 16.dp, vertical = 10.dp),
                    shape = RoundedCornerShape(14.dp)
                ) { Text("+ Tambah") }
            }
            if (busy && addresses.isEmpty()) {
                Box(Modifier.weight(1f).fillMaxWidth(), contentAlignment = Alignment.Center) {
                    Column(horizontalAlignment = Alignment.CenterHorizontally, verticalArrangement = Arrangement.spacedBy(12.dp)) {
                        CircularProgressIndicator()
                        Text("Memuat alamat…", style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                    }
                }
            } else if (!busy && addresses.isEmpty() && addressesLoadError) {
                Column(Modifier.weight(1f).fillMaxWidth().padding(horizontal = 18.dp), horizontalAlignment = Alignment.CenterHorizontally, verticalArrangement = Arrangement.Center) {
                    Surface(shape = CircleShape, color = MaterialTheme.colorScheme.errorContainer, modifier = Modifier.size(64.dp)) {
                        Box(contentAlignment = Alignment.Center) {
                            Icon(painterResource(R.drawable.ic_refresh), contentDescription = null, tint = MaterialTheme.colorScheme.onErrorContainer, modifier = Modifier.size(26.dp))
                        }
                    }
                    Spacer(Modifier.height(16.dp))
                    Text("Alamat belum dapat dimuat", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
                    Spacer(Modifier.height(6.dp))
                    Text("Periksa koneksi lalu coba lagi. Alamat Anda belum dihapus.", style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant, textAlign = androidx.compose.ui.text.style.TextAlign.Center)
                    Spacer(Modifier.height(16.dp))
                    Button(onClick = { message = null; addressesLoadError = false; refresh++ }, shape = RoundedCornerShape(14.dp)) { Text("Coba lagi") }
                }
            } else if (!busy && addresses.isEmpty()) {
                Column(Modifier.weight(1f).fillMaxWidth().padding(horizontal = 18.dp), horizontalAlignment = Alignment.CenterHorizontally, verticalArrangement = Arrangement.Center) {
                    Surface(shape = CircleShape, color = MaterialTheme.colorScheme.primaryContainer, modifier = Modifier.size(64.dp)) {
                        Box(contentAlignment = Alignment.Center) {
                            Icon(painterResource(R.drawable.ic_my_location), contentDescription = null, tint = LunareaGreenText, modifier = Modifier.size(28.dp))
                        }
                    }
                    Spacer(Modifier.height(16.dp))
                    Text("Belum ada alamat tersimpan", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
                    Spacer(Modifier.height(6.dp))
                    Text("Tambahkan alamat agar proses checkout lebih mudah.", style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant, textAlign = androidx.compose.ui.text.style.TextAlign.Center)
                    Spacer(Modifier.height(16.dp))
                    Button(onClick = { showForm = true }, shape = RoundedCornerShape(14.dp)) { Text("Tambah alamat pertama") }
                }
            } else {
                LazyColumn(Modifier.weight(1f), contentPadding = PaddingValues(top = 8.dp, bottom = 20.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
            items(addresses, key = { it.get("id")?.asLong ?: 0L }) { address ->
                val id = address.get("id")?.asLong ?: 0L
                val nested = address.getAsJsonObject("address")
                val isDefault = address.get("is_default")?.asBoolean == true
                Surface(
                    shape = RoundedCornerShape(18.dp),
                    color = MaterialTheme.colorScheme.surface,
                    border = androidx.compose.foundation.BorderStroke(1.dp, if (isDefault) MaterialTheme.colorScheme.primary.copy(alpha = .45f) else MaterialTheme.colorScheme.outlineVariant.copy(alpha = .7f))
                ) {
                    Column(Modifier.fillMaxWidth().padding(16.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Text(address.get("label")?.asString?.ifBlank { "Alamat" } ?: "Alamat", modifier = Modifier.weight(1f), style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.SemiBold)
                            if (isDefault) {
                                Surface(shape = RoundedCornerShape(50), color = MaterialTheme.colorScheme.primaryContainer) {
                                    Text("UTAMA", modifier = Modifier.padding(horizontal = 10.dp, vertical = 5.dp), style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.onPrimaryContainer, fontWeight = FontWeight.SemiBold)
                                }
                            }
                        }
                        Text(address.get("recipient_name")?.asString.orEmpty(), style = MaterialTheme.typography.bodyMedium, fontWeight = FontWeight.Medium)
                        Text(address.get("recipient_phone")?.asString.orEmpty(), style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                        Text(nested?.get("alamat")?.asString?.ifBlank { "Detail alamat belum tersedia." } ?: "Detail alamat belum tersedia.", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant, lineHeight = 19.sp)
                        HorizontalDivider(color = MaterialTheme.colorScheme.outlineVariant.copy(alpha = .65f))
                        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(4.dp), verticalAlignment = Alignment.CenterVertically) {
                            TextButton(enabled = !busy, modifier = Modifier.weight(1f).heightIn(min = 44.dp), onClick = {
                                scope.launch {
                                    busy = true
                                    try {
                                        val addressData = address.getAsJsonObject("address")
                                        val pId = addressData?.get("prov_id")?.asInt ?: 0
                                        val cId = addressData?.get("kab_id")?.asInt ?: 0
                                        val dId = addressData?.get("kec_id")?.asInt ?: 0
                                        if (minOf(pId, cId, dId) <= 0) throw IllegalStateException("Data wilayah alamat lama belum lengkap. Periksa alamat sebelum mengubahnya.")
                                        val api = ApiClient.get(context)
                                        val cityResponse = api.cities(pId)
                                        if (!cityResponse.isSuccessful || cityResponse.body()?.success != true) throw IllegalStateException(cityResponse.customerError("Daftar kota belum dapat dimuat."))
                                        val districtResponse = api.districts(cId)
                                        if (!districtResponse.isSuccessful || districtResponse.body()?.success != true) throw IllegalStateException(districtResponse.customerError("Daftar kecamatan belum dapat dimuat."))
                                        val villageResponse = api.villages(dId)
                                        if (!villageResponse.isSuccessful || villageResponse.body()?.success != true) throw IllegalStateException(villageResponse.customerError("Daftar kelurahan belum dapat dimuat."))
                                        cities = options(cityResponse.body()?.data)
                                        districts = options(districtResponse.body()?.data)
                                        villages = options(villageResponse.body()?.data)
                                        province = provinces.firstOrNull { it.id == pId } ?: RegionOption(pId, addressData?.get("prov")?.asString.orEmpty())
                                        city = cities.firstOrNull { it.id == cId } ?: RegionOption(cId, addressData?.get("kab")?.asString.orEmpty())
                                        district = districts.firstOrNull { it.id == dId } ?: RegionOption(dId, addressData?.get("kec")?.asString.orEmpty())
                                        val savedVillage = addressData?.get("desa")?.asString.orEmpty()
                                        val savedPostal = addressData?.get("kodepos")?.asString.orEmpty()
                                        village = villages.firstOrNull { it.label.equals(savedVillage, ignoreCase = true) && (savedPostal.isBlank() || it.postal == savedPostal) }
                                        villageError = if (village == null) "Kelurahan alamat ini tidak cocok dengan data wilayah terbaru. Pilih kelurahan kembali." else null
                                        label = address.get("label")?.asString.orEmpty()
                                        recipient = address.get("recipient_name")?.asString.orEmpty()
                                        phone = address.get("recipient_phone")?.asString.orEmpty()
                                        detail = addressData?.get("add")?.asString.orEmpty()
                                        editingAddressId = id
                                        showForm = true
                                    } catch (error: Exception) { message = error.message?.takeIf(String::isNotBlank) ?: "Data alamat belum dapat dimuat untuk diedit."; messageIsError = true }
                                    finally { busy = false }
                                }
                            }) { Text("Ubah") }
                            if (!isDefault) TextButton(enabled = !busy, modifier = Modifier.weight(1.4f).heightIn(min = 44.dp), onClick = {
                                scope.launch {
                                    busy = true
                                    try {
                                        val response = ApiClient.get(context).setDefaultAddress(id)
                                        if (response.isSuccessful && response.body()?.success == true) {
                                            message = "Alamat utama diperbarui."
                                            messageIsError = false
                                            refresh++
                                        } else { message = response.customerError("Alamat utama belum dapat diperbarui."); messageIsError = true }
                                    } catch (_: Exception) { message = "Koneksi belum tersedia. Coba lagi."; messageIsError = true }
                                    finally { busy = false }
                                }
                            }) { Text("Jadikan utama") }
                            TextButton(enabled = !busy, modifier = Modifier.weight(.8f).heightIn(min = 44.dp), onClick = { addressPendingDelete = address }) { Text("Hapus", color = MaterialTheme.colorScheme.error) }
                        }
                    }
                }
            }
        }
            }
        }
    }
}

@Composable
private fun CheckoutScreen(modifier: Modifier, onBack: () -> Unit, onOpenOrders: () -> Unit, onCheckoutConfirmed: () -> Unit) {
    val context = LocalContext.current
    val scope = rememberCoroutineScope()
    var addresses by remember { mutableStateOf<List<com.google.gson.JsonObject>>(emptyList()) }
    var addressId by remember { mutableLongStateOf(0L) }
    var quotes by remember { mutableStateOf<List<ShippingOption>>(emptyList()) }
    var quote by remember { mutableStateOf<ShippingOption?>(null) }
    var shippingWeightGrams by remember { mutableIntStateOf(0) }
    var payment by remember { mutableStateOf("bca") }
    var lastGatewayPayment by remember { mutableStateOf("bca") }
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
    var checkoutNeedsReview by remember { mutableStateOf(false) }
    var checkoutStatusUnknown by remember { mutableStateOf(false) }
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

    BackHandler(enabled = paymentInstructions != null) { paymentInstructions = null }
    paymentInstructions?.let { instructions ->
        val virtualAccounts = instructions.get("va_numbers")?.takeIf { it.isJsonArray }?.asJsonArray
        val paymentActions = instructions.get("actions")?.takeIf { it.isJsonArray }?.asJsonArray
        val manualPayment = instructions.get("type")?.asString.orEmpty() == "rekening"
        Column(modifier.fillMaxSize().background(MaterialTheme.colorScheme.background)) {
            Column(Modifier.fillMaxWidth().background(MaterialTheme.colorScheme.surface).padding(horizontal = 20.dp, vertical = 10.dp)) {
                LunareaScreenHeader("Instruksi pembayaran", { paymentInstructions = null }, "Pesanan ${checkoutOrderId.orEmpty()}")
            }
            LazyColumn(Modifier.fillMaxSize(), contentPadding = PaddingValues(start = 18.dp, top = 18.dp, end = 18.dp, bottom = 28.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                item {
                    Surface(shape = RoundedCornerShape(20.dp), color = MaterialTheme.colorScheme.surface) {
                        Column(Modifier.fillMaxWidth().padding(20.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                            Text(if (manualPayment) "Pesanan transfer manual dibuat" else "Pesanan berhasil dibuat", style = MaterialTheme.typography.titleLarge, fontWeight = FontWeight.Bold, color = LunareaGreenText)
                            Text(if (manualPayment) "Transfer sesuai nominal ke rekening berikut. Setelah itu, kirim bukti melalui detail pesanan." else "Selesaikan pembayaran sesuai informasi berikut.", style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                            Text(formatRupiah(instructions.get("gross_amount")?.asLong ?: preview?.get("total")?.asLong ?: 0L), style = MaterialTheme.typography.headlineSmall, fontWeight = FontWeight.Bold)
                            OrderInfoRow("Nomor pesanan", checkoutOrderId.orEmpty())
                            OrderInfoRow("Metode", if (manualPayment) "Transfer manual · BRI" else instructions.get("type")?.asString?.uppercase()?.ifBlank { "Belum tersedia" } ?: "Belum tersedia")
                            OrderInfoRow("Status", instructions.get("transaction_status")?.asString?.ifBlank { "Menunggu pembayaran" } ?: "Menunggu pembayaran")
                            instructions.get("expiry_time")?.takeIf { !it.isJsonNull }?.asString?.takeIf(String::isNotBlank)?.let { OrderInfoRow("Batas pembayaran", formatLunareaDateTime(it)) }
                        }
                    }
                }
                if (virtualAccounts != null && virtualAccounts.size() > 0) item {
                    Surface(shape = RoundedCornerShape(20.dp), color = MaterialTheme.colorScheme.surface) {
                        Column(Modifier.fillMaxWidth().padding(18.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                            Text("Nomor pembayaran", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
                            virtualAccounts?.forEach { raw -> if (raw.isJsonObject) {
                                val va = raw.asJsonObject
                                CopyablePaymentValue(if (manualPayment) "Nomor rekening Lunarea" else va.get("bank")?.asString?.uppercase()?.ifBlank { "Virtual Account" } ?: "Virtual Account", va.get("va_number")?.asString?.ifBlank { "Belum tersedia" } ?: "Belum tersedia")
                            } }
                            if (manualPayment) instructions.get("account_name")?.takeUnless { it.isJsonNull }?.asString?.takeIf(String::isNotBlank)?.let { OrderInfoRow("Atas nama", it) }
                        }
                    }
                }
                item {
                    val method = instructions.get("type")?.takeUnless { it.isJsonNull }?.asString.orEmpty()
                    instructions.stringOrNull("qr_string")?.takeIf { method == "qris" && it.isNotBlank() }?.let { PaymentQrCard(it) }
                }
                item {
                    Surface(shape = RoundedCornerShape(20.dp), color = MaterialTheme.colorScheme.surface) {
                        Column(Modifier.fillMaxWidth().padding(18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
                            Text("Petunjuk pembayaran", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
                            val method = instructions.get("type")?.takeUnless { it.isJsonNull }?.asString.orEmpty()
                            val bank = virtualAccounts?.firstOrNull()?.takeIf { it.isJsonObject }?.asJsonObject?.get("bank")?.asString.orEmpty()
                            Text(paymentGuidance(method, bank), style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                            listOf("payment_code" to "Kode pembayaran", "bill_key" to "Kode tagihan", "biller_code" to "Kode penyedia").forEach { (field, label) ->
                                instructions.get(field)?.takeIf { !it.isJsonNull }?.asString?.takeIf(String::isNotBlank)?.let { CopyablePaymentValue(label, it) }
                            }
                            paymentActions?.forEach { raw -> if (raw.isJsonObject) {
                                val action = raw.asJsonObject
                                val url = action.get("url")?.asString.orEmpty()
                                val label = action.get("name")?.asString?.replace('_', ' ')?.replaceFirstChar(Char::uppercase) ?: "Buka pembayaran"
                                if (url.startsWith("https://", ignoreCase = true)) OutlinedButton(onClick = { openWebsite(context, url) }, modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp), shape = RoundedCornerShape(13.dp)) { Text(label) }
                            } }
                            Text(if (manualPayment) "Batas pembayaran 1 jam. Bukti transfer diunggah dari detail pesanan dan akan diperiksa admin." else "Informasi pembayaran juga tersedia kembali pada detail pesanan.", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                        }
                    }
                }
                item { Button(onClick = onOpenOrders, modifier = Modifier.fillMaxWidth().heightIn(min = 52.dp), shape = RoundedCornerShape(14.dp)) { Text(if (manualPayment) "Buka pesanan & kirim bukti" else "Buka pesanan saya") } }
            }
        }
        return
    }

    if (checkoutNeedsReview) {
        BackHandler { onBack() }
        Column(Modifier.fillMaxSize().background(MaterialTheme.colorScheme.background)) {
            Column(Modifier.fillMaxWidth().background(MaterialTheme.colorScheme.surface).padding(horizontal = 20.dp, vertical = 10.dp)) {
                LunareaScreenHeader("Konfirmasi pesanan", onBack, checkoutOrderId?.let { "Pesanan $it" } ?: "Status permintaan")
            }
            Column(
                Modifier.fillMaxSize().verticalScroll(rememberScrollState()).padding(horizontal = 20.dp, vertical = 24.dp),
                verticalArrangement = Arrangement.spacedBy(18.dp),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                Surface(
                    shape = CircleShape,
                    color = MaterialTheme.colorScheme.secondaryContainer,
                    modifier = Modifier.size(72.dp)
                ) {
                    Box(contentAlignment = Alignment.Center) {
                        CircularProgressIndicator(Modifier.size(30.dp), strokeWidth = 3.dp, color = MaterialTheme.colorScheme.primary)
                    }
                }
                Text(
                    if (checkoutStatusUnknown) "Kami sedang memastikan status pesanan" else "Pesanan sedang diproses",
                    style = MaterialTheme.typography.headlineSmall,
                    fontWeight = FontWeight.SemiBold,
                    textAlign = androidx.compose.ui.text.style.TextAlign.Center
                )
                Text(
                    if (checkoutStatusUnknown)
                        "Respons sistem belum lengkap. Pesanan mungkin sudah tercatat. Periksa daftar pesanan terlebih dahulu sebelum mencoba lagi."
                    else
                        "Permintaan pesanan sudah diterima, tetapi konfirmasi pembayaran belum tersedia. Buka daftar pesanan untuk melihat pembaruan dan instruksi berikutnya.",
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    textAlign = androidx.compose.ui.text.style.TextAlign.Center,
                    lineHeight = 23.sp
                )
                checkoutOrderId?.takeIf(String::isNotBlank)?.let { orderId ->
                    Surface(
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(16.dp),
                        color = MaterialTheme.colorScheme.surface,
                        border = androidx.compose.foundation.BorderStroke(1.dp, MaterialTheme.colorScheme.outlineVariant)
                    ) {
                        Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(5.dp)) {
                            Text("Nomor referensi", style = MaterialTheme.typography.labelMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                            Text(orderId, style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
                        }
                    }
                }
                Surface(shape = RoundedCornerShape(14.dp), color = MaterialTheme.colorScheme.errorContainer.copy(alpha = .45f)) {
                    Text(
                        "Jangan membuat pesanan atau pembayaran baru sampai status ini diperiksa.",
                        Modifier.fillMaxWidth().padding(14.dp),
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onErrorContainer,
                        textAlign = androidx.compose.ui.text.style.TextAlign.Center
                    )
                }
                Button(onClick = onOpenOrders, modifier = Modifier.fillMaxWidth().heightIn(min = 52.dp), shape = RoundedCornerShape(14.dp)) {
                    Text("Lihat Pesanan Saya")
                }
                TextButton(onClick = onBack, modifier = Modifier.heightIn(min = 44.dp)) {
                    Text("Kembali tanpa membuat pesanan baru")
                }
            }
        }
        return
    }

    Column(modifier.fillMaxSize().background(MaterialTheme.colorScheme.background)) {
        Column(Modifier.fillMaxWidth().background(MaterialTheme.colorScheme.surface).padding(horizontal = 20.dp, vertical = 10.dp)) {
            LunareaScreenHeader("Pengiriman & pembayaran", onBack, "Periksa pilihan dan total pesanan")
        }
        LazyColumn(Modifier.weight(1f).padding(horizontal = 18.dp), verticalArrangement = Arrangement.spacedBy(8.dp), contentPadding = PaddingValues(top = 10.dp, bottom = 14.dp)) {
            item {
                CheckoutSectionHeading("Alamat penerima")
                if (addresses.isEmpty()) {
                    Surface(shape = RoundedCornerShape(16.dp), color = MaterialTheme.colorScheme.surface) {
                        Column(Modifier.fillMaxWidth().padding(16.dp), verticalArrangement = Arrangement.spacedBy(4.dp)) {
                            Text(if (busy) "Memuat alamat…" else "Belum ada alamat pengiriman", style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.SemiBold)
                            Text("Tambahkan alamat tersimpan melalui menu Akun untuk melihat pilihan pengiriman.", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
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
                            Row(Modifier.padding(horizontal = 14.dp, vertical = 12.dp), verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                                Column(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(2.dp)) {
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
                CheckoutSectionHeading("Pengiriman", "Pilih layanan dan periksa tarifnya")
                if (quoteLoading) {
                    LinearProgressIndicator(Modifier.fillMaxWidth(), color = MaterialTheme.colorScheme.primary)
                    Text("Menghitung pilihan pengiriman…", Modifier.padding(top = 6.dp), style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                }
                if (quotes.isNotEmpty()) {
                    if (shippingWeightGrams > 0) {
                        Text(
                            "Berat produk ${"%.2f".format(Locale("id", "ID"), shippingWeightGrams / 1000.0)} kg",
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    }
                    Column(verticalArrangement = Arrangement.spacedBy(6.dp)) {
                        quotes.forEach { option ->
                            val selected = quote?.token == option.token
                            val selectionColor by animateColorAsState(
                                targetValue = if (selected) MaterialTheme.colorScheme.primaryContainer.copy(alpha = .48f) else MaterialTheme.colorScheme.surface,
                                animationSpec = tween(durationMillis = 180),
                                label = "shipping-option-selection"
                            )
                            Surface(
                                onClick = { quote = option },
                                shape = RoundedCornerShape(12.dp), color = selectionColor,
                                border = androidx.compose.foundation.BorderStroke(
                                    1.dp,
                                    if (selected) MaterialTheme.colorScheme.primary.copy(alpha = .6f) else MaterialTheme.colorScheme.outlineVariant
                                )
                            ) {
                                Row(Modifier.fillMaxWidth().padding(horizontal = 12.dp, vertical = 9.dp), horizontalArrangement = Arrangement.spacedBy(9.dp), verticalAlignment = Alignment.CenterVertically) {
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
                    TextButton(onClick = { refreshQuotes++ }, enabled = !quoteLoading, modifier = Modifier.heightIn(min = 40.dp)) {
                        Text("Perbarui tarif", fontSize = 12.sp)
                    }
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
                val manualSelected = payment == "rekening"
                listOf(
                    Triple("rekening", "Transfer manual", "Unggah bukti setelah transfer"),
                    Triple("midtrans", "Pembayaran otomatis", "VA, QRIS, dan dompet digital")
                ).forEach { (mode, title, description) ->
                    val selected = if (mode == "rekening") manualSelected else !manualSelected
                    Surface(
                        onClick = {
                            if (mode == "rekening") {
                                if (!manualSelected) lastGatewayPayment = payment
                                payment = "rekening"
                            } else if (manualSelected) payment = lastGatewayPayment
                        },
                        shape = RoundedCornerShape(13.dp),
                        color = if (selected) MaterialTheme.colorScheme.primaryContainer.copy(alpha = .42f) else MaterialTheme.colorScheme.surface,
                        border = androidx.compose.foundation.BorderStroke(
                            1.dp,
                            if (selected) MaterialTheme.colorScheme.primary.copy(alpha = .65f) else MaterialTheme.colorScheme.outlineVariant
                        )
                    ) {
                        Row(Modifier.fillMaxWidth().padding(horizontal = 10.dp, vertical = 6.dp), verticalAlignment = Alignment.CenterVertically) {
                            RadioButton(selected = selected, onClick = {
                                if (mode == "rekening") {
                                    if (!manualSelected) lastGatewayPayment = payment
                                    payment = "rekening"
                                } else if (manualSelected) payment = lastGatewayPayment
                            })
                            Column(Modifier.weight(1f).padding(start = 8.dp), verticalArrangement = Arrangement.spacedBy(3.dp)) {
                                Text(title, style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.SemiBold)
                                Text(description, style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                            }
                        }
                    }
                }
                if (!manualSelected) {
                    Box {
                        OutlinedButton(onClick = { paymentMenu = true }, modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp), shape = RoundedCornerShape(12.dp)) {
                            Text(nativePaymentMethods.firstOrNull { it.first == payment }?.second ?: "Pilih metode otomatis", modifier = Modifier.weight(1f))
                            Icon(painterResource(R.drawable.ic_expand_more), contentDescription = null)
                        }
                        DropdownMenu(expanded = paymentMenu, onDismissRequest = { paymentMenu = false }) {
                            nativePaymentMethods.forEach { (method, label) ->
                                DropdownMenuItem(text = { Text(label) }, onClick = { payment = method; lastGatewayPayment = method; paymentMenu = false })
                            }
                        }
                    }
                } else {
                    Text("Bukti transfer akan dikirim dari halaman detail pesanan. Pesanan dikonfirmasi setelah admin memeriksa pembayaran.", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
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
            item {
                val availablePoints = preview?.get("points_available")?.takeUnless { it.isJsonNull }?.asLong ?: 0L
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Checkbox(checked = usePoints, onCheckedChange = { usePoints = it }, enabled = availablePoints > 0 && !previewLoading)
                    Column {
                        Text("Gunakan Luna Poin")
                        Text("Saldo tersedia ${formatRupiah(availablePoints)}", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                    }
                }
            }
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
                            val shippingSummary = summary.getAsJsonObject("shipping")
                            Text(
                                "Ongkir${shippingSummary?.get("courier")?.takeUnless { it.isJsonNull }?.asString?.takeIf(String::isNotBlank)?.let { " · $it" }.orEmpty()}: ${formatRupiah(shippingSummary?.get("cost")?.asLong ?: 0L)}",
                                style = MaterialTheme.typography.bodyMedium,
                                fontWeight = FontWeight.Medium
                            )
                            summary.get("voucher_discount")?.asLong?.takeIf { it > 0 }?.let { Text("Diskon voucher: −${formatRupiah(it)}") }
                            summary.get("points_applied")?.asLong?.takeIf { it > 0 }?.let { Text("Luna Point: −${formatRupiah(it)}") }
                            summary.getAsJsonObject("glass_service")?.get("amount")?.asLong?.takeIf { it > 0 }?.let { Text("Proteksi kaca: ${formatRupiah(it)}") }
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
                busy = true; orderResult = null; checkoutNeedsReview = false; checkoutStatusUnknown = false
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
                            orderResult = if (response.code() == 202) "Pesanan sedang diproses. Silakan lihat statusnya di Pesanan Saya." else "Pesanan berhasil dibuat. Lihat instruksi pembayaran pada halaman berikutnya."
                            if (response.code() == 202) {
                                checkoutNeedsReview = true
                                checkoutStatusUnknown = false
                            }
                            if (response.code() != 202) {
                                onCheckoutConfirmed()
                                paymentInstructions = order?.get("payment")?.takeIf { it.isJsonObject }?.asJsonObject
                                checkoutPrefs.edit().remove("idempotency_key").remove("request_signature").apply()
                                idempotencyKey = ""
                            }
                        } else {
                            val errorMessage = response.customerError("Pesanan belum berhasil dibuat. Periksa Pesanan saya sebelum mencoba lagi.")
                            val errorCode = response.body()?.error?.code
                            val incident = response.body()?.error?.request_id?.takeIf { it.matches(Regex("^[a-f0-9]{16}$")) }
                            message = if (incident != null) "$errorMessage (Kode: $incident)" else errorMessage
                            if (response.code() == 202 || errorCode == "CHECKOUT_UNAVAILABLE") {
                                checkoutNeedsReview = true
                                checkoutStatusUnknown = true
                                checkoutOrderId = null
                                orderResult = "Status permintaan belum dapat dipastikan. Periksa Pesanan Saya sebelum mencoba kembali."
                            }
                            if (errorCode == "CHECKOUT_NOT_ENABLED") {
                                checkoutPrefs.edit().remove("idempotency_key").remove("request_signature").apply()
                                idempotencyKey = ""
                            }
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
                        checkoutNeedsReview = true
                        checkoutStatusUnknown = true
                        checkoutOrderId = null
                        orderResult = "Koneksi terputus sebelum status pesanan terkonfirmasi. Periksa Pesanan Saya terlebih dahulu."
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
    var photoUrl by remember { mutableStateOf<String?>(null) }
    var busy by remember { mutableStateOf(true) }
    var photoBusy by remember { mutableStateOf(false) }
    var message by remember { mutableStateOf<String?>(null) }
    val photoPicker = rememberLauncherForActivityResult(ActivityResultContracts.GetContent()) { uri: Uri? ->
        if (uri != null) scope.launch {
            photoBusy = true
            message = null
            try {
                val mime = context.contentResolver.getType(uri) ?: "image/jpeg"
                require(mime in setOf("image/jpeg", "image/png", "image/webp")) { "Pilih foto JPG, PNG, atau WebP." }
                val bytes = context.contentResolver.openInputStream(uri)?.use { it.readBytes() }
                    ?: throw IllegalStateException("Foto belum dapat dibaca.")
                require(bytes.size <= 2 * 1024 * 1024) { "Ukuran foto maksimal 2 MB." }
                val request = bytes.toRequestBody(mime.toMediaTypeOrNull())
                val part = MultipartBody.Part.createFormData("photo", "foto-profil", request)
                val response = ApiClient.get(context).uploadProfilePhoto(part)
                val body = response.body()
                val customer = body?.data?.getAsJsonObject("customer")
                if (response.isSuccessful && body?.success == true) {
                    photoUrl = customer?.get("photo_url")?.asString?.let { "$it?v=${System.currentTimeMillis()}" }
                    message = "Foto profil berhasil diperbarui."
                } else message = response.customerError("Foto profil belum dapat disimpan.")
            } catch (e: Exception) {
                message = e.message?.takeIf { it.isNotBlank() } ?: "Foto profil belum dapat diperbarui. Coba lagi."
            } finally { photoBusy = false }
        }
    }
    LaunchedEffect(Unit) {
        try {
            val response = ApiClient.get(context).profile()
            val customer = response.body()?.data?.getAsJsonObject("customer")
            if (response.isSuccessful && response.body()?.success == true && customer != null) {
                name = customer.get("name")?.asString.orEmpty(); phone = customer.get("phone")?.asString.orEmpty(); email = customer.get("email")?.asString.orEmpty()
                photoUrl = customer.get("photo_url")?.asString?.takeIf { it.isNotBlank() }
            } else message = response.body()?.error?.message ?: "Profil belum dapat dimuat."
        } catch (_: Exception) { message = "Koneksi belum tersedia. Coba lagi." }
        finally { busy = false }
    }
    Column(modifier.fillMaxSize().background(MaterialTheme.colorScheme.background).verticalScroll(rememberScrollState()).padding(horizontal = 18.dp, vertical = 14.dp), verticalArrangement = Arrangement.spacedBy(14.dp)) {
        LunareaScreenHeader("Profil saya", onBack, "Kelola informasi akun Anda")
        if (busy) LinearProgressIndicator(modifier = Modifier.fillMaxWidth())
        Surface(shape = RoundedCornerShape(20.dp), color = MaterialTheme.colorScheme.surface, tonalElevation = 1.dp, modifier = Modifier.fillMaxWidth()) {
          Row(Modifier.fillMaxWidth().padding(16.dp), verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(14.dp)) {
            Surface(shape = CircleShape, color = MaterialTheme.colorScheme.primaryContainer, modifier = Modifier.size(76.dp)) {
                Box(contentAlignment = Alignment.Center) {
                    Text(name.trim().firstOrNull()?.uppercaseChar()?.toString() ?: "L", style = MaterialTheme.typography.headlineMedium, color = LunareaGreenText, fontWeight = FontWeight.Bold)
                    photoUrl?.let { url ->
                        AsyncImage(model = url, contentDescription = "Foto profil", modifier = Modifier.fillMaxSize().clip(CircleShape), contentScale = ContentScale.Crop)
                    }
                }
            }
            Column(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(4.dp)) {
                Text(name.ifBlank { "Akun Lunarea" }, style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold, maxLines = 1, overflow = TextOverflow.Ellipsis)
                Text("Foto membantu Anda mengenali akun", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                TextButton(onClick = { photoPicker.launch("image/*") }, enabled = !photoBusy, contentPadding = PaddingValues(horizontal = 0.dp, vertical = 2.dp)) {
                    if (photoBusy) CircularProgressIndicator(Modifier.size(16.dp), strokeWidth = 2.dp)
                    else Text(if (photoUrl.isNullOrBlank()) "Tambah foto profil" else "Ganti foto profil")
                }
            }
          }
        }
        Text("JPG, PNG, atau WebP · Maksimal 2 MB", style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.onSurfaceVariant, modifier = Modifier.padding(start = 4.dp))
        Text("Informasi pribadi", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold, modifier = Modifier.padding(top = 4.dp, start = 4.dp))
        Surface(shape = RoundedCornerShape(18.dp), color = MaterialTheme.colorScheme.surface, modifier = Modifier.fillMaxWidth()) {
          Column(Modifier.fillMaxWidth().padding(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
            OutlinedTextField(email, {}, modifier = Modifier.fillMaxWidth(), label = { Text("Email") }, enabled = false, singleLine = true, shape = RoundedCornerShape(12.dp))
            OutlinedTextField(name, { name = it }, modifier = Modifier.fillMaxWidth(), label = { Text("Nama") }, singleLine = true, shape = RoundedCornerShape(12.dp))
            OutlinedTextField(phone, { phone = it }, modifier = Modifier.fillMaxWidth(), label = { Text("Nomor ponsel") }, keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Phone), singleLine = true, shape = RoundedCornerShape(12.dp))
          }
        }
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
        }, modifier = Modifier.fillMaxWidth().heightIn(min = 50.dp), shape = RoundedCornerShape(14.dp)) { Text("Simpan perubahan") }
        message?.let { Text(it, color = if (it.contains("berhasil", true)) LunareaGreenText else MaterialTheme.colorScheme.error, fontSize = 13.sp) }
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
            EmptyState(message, {}, "")
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
            if (action.isNotBlank()) {
                Button(onClick = onAction, modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp), shape = MaterialTheme.shapes.medium) { Text(action) }
            }
        }
    }
}

@Composable
private fun VoucherCampaignPopup(voucher: com.google.gson.JsonObject, onDismiss: () -> Unit, onAction: () -> Unit) {
    val name = voucher.get("name")?.takeUnless { it.isJsonNull }?.asString.orEmpty().ifBlank { "Voucher pilihan" }
    val nominal = runCatching { voucher.get("nominal")?.takeUnless { it.isJsonNull }?.asLong ?: 0L }.getOrDefault(0L)
    val unit = voucher.get("unit")?.takeUnless { it.isJsonNull }?.asString.orEmpty()
    val discount = if (unit.equals("persen", true) || unit.equals("percent", true)) "${nominal}%" else formatRupiah(nominal)
    val description = voucher.get("description")?.takeUnless { it.isJsonNull }?.asString.orEmpty()
    val cap = runCatching { voucher.get("max_discount")?.takeUnless { it.isJsonNull }?.asLong ?: 0L }.getOrDefault(0L)
    val posterUrl = voucher.get("poster_url")?.takeUnless { it.isJsonNull }?.asString?.takeIf { it.isNotBlank() }
    var posterFailed by remember(posterUrl) { mutableStateOf(false) }
    var posterAspect by remember(posterUrl) { mutableFloatStateOf(1.2f) }

    Dialog(onDismissRequest = onDismiss, properties = DialogProperties(usePlatformDefaultWidth = false)) {
        Surface(modifier = Modifier.fillMaxWidth(.91f), shape = RoundedCornerShape(26.dp), color = MaterialTheme.colorScheme.surface, shadowElevation = 12.dp) {
            Column {
                if (!posterUrl.isNullOrBlank() && !posterFailed) {
                    Box(
                        Modifier.fillMaxWidth()
                            .background(MaterialTheme.colorScheme.surfaceVariant)
                            .clickable(onClick = onAction)
                            .semantics { contentDescription = "Pilih promo $name" }
                    ) {
                        Box(Modifier.fillMaxWidth().aspectRatio(posterAspect).background(rememberLunareaShimmerBrush()))
                        AsyncImage(
                            model = posterUrl,
                            contentDescription = "Poster promo: $name",
                            modifier = Modifier.fillMaxWidth().aspectRatio(posterAspect),
                            contentScale = ContentScale.Fit,
                            onSuccess = { result ->
                                val drawable = result.result.drawable
                                if (drawable.intrinsicWidth > 0 && drawable.intrinsicHeight > 0) {
                                    posterAspect = (drawable.intrinsicWidth.toFloat() / drawable.intrinsicHeight).coerceIn(.62f, 2.2f)
                                }
                            },
                            onError = { posterFailed = true }
                        )
                        Surface(Modifier.align(Alignment.TopEnd).padding(10.dp), shape = CircleShape, color = MaterialTheme.colorScheme.surface.copy(alpha = .92f)) {
                            IconButton(onClick = onDismiss, modifier = Modifier.size(44.dp)) { Text("×", color = MaterialTheme.colorScheme.onSurface, style = MaterialTheme.typography.headlineSmall) }
                        }
                    }
                } else {
                    Box(
                        Modifier.fillMaxWidth()
                            .background(Brush.linearGradient(listOf(LunareaGreenText, MaterialTheme.colorScheme.primary)))
                            .clickable(onClick = onAction)
                            .semantics { contentDescription = "Pilih promo $name" }
                            .padding(start = 22.dp, top = 20.dp, end = 58.dp, bottom = 22.dp)
                    ) {
                        Column(verticalArrangement = Arrangement.spacedBy(9.dp)) {
                            Surface(shape = RoundedCornerShape(50), color = MaterialTheme.colorScheme.onPrimary.copy(alpha = .14f)) {
                                Text("PROMO LUNAREA", Modifier.padding(horizontal = 11.dp, vertical = 6.dp), color = MaterialTheme.colorScheme.onPrimary, style = MaterialTheme.typography.labelSmall, fontWeight = FontWeight.Bold, letterSpacing = .7.sp)
                            }
                            Text(name, style = MaterialTheme.typography.headlineSmall, color = MaterialTheme.colorScheme.onPrimary, fontWeight = FontWeight.Bold, lineHeight = 30.sp)
                            if (description.isNotBlank()) Text(description, style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onPrimary.copy(alpha = .9f))
                        }
                        IconButton(onClick = onDismiss, modifier = Modifier.align(Alignment.TopEnd).size(44.dp)) {
                            Text("×", color = MaterialTheme.colorScheme.onPrimary, style = MaterialTheme.typography.headlineSmall)
                        }
                    }
                }
                Column(Modifier.padding(20.dp), verticalArrangement = Arrangement.spacedBy(14.dp)) {
                    if (!posterUrl.isNullOrBlank() && !posterFailed) Text(name, style = MaterialTheme.typography.titleMedium, color = MaterialTheme.colorScheme.onSurface, fontWeight = FontWeight.SemiBold)
                    Surface(shape = RoundedCornerShape(18.dp), color = MaterialTheme.colorScheme.surfaceVariant.copy(alpha = .58f), border = androidx.compose.foundation.BorderStroke(1.dp, MaterialTheme.colorScheme.outlineVariant.copy(alpha = .55f))) {
                        Row(Modifier.fillMaxWidth().padding(14.dp), verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(14.dp)) {
                            Surface(shape = RoundedCornerShape(15.dp), color = MaterialTheme.colorScheme.primaryContainer, modifier = Modifier.size(72.dp)) {
                                Column(Modifier.fillMaxSize(), horizontalAlignment = Alignment.CenterHorizontally, verticalArrangement = Arrangement.Center) {
                                    Text("HEMAT", style = MaterialTheme.typography.labelSmall, color = LunareaGreenText, fontWeight = FontWeight.Bold)
                                    Text(discount, style = MaterialTheme.typography.titleMedium, color = LunareaGreenText, fontWeight = FontWeight.ExtraBold, maxLines = 1)
                                }
                            }
                            Column(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(3.dp)) {
                                Text(name, style = MaterialTheme.typography.titleSmall, color = MaterialTheme.colorScheme.onSurface, fontWeight = FontWeight.SemiBold, maxLines = 2, overflow = TextOverflow.Ellipsis)
                                if (description.isNotBlank()) Text(description, style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant, maxLines = 2, overflow = TextOverflow.Ellipsis)
                                if (cap > 0) Text("Potongan hingga ${formatRupiah(cap)}", style = MaterialTheme.typography.labelSmall, color = LunareaGreenText, fontWeight = FontWeight.Medium)
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun CatalogScreen(
    startupComplete: Boolean, modifier: Modifier, tab: AppTab, query: String, onQueryChange: (String) -> Unit,
    signedIn: Boolean, favoriteKeys: Set<String>, cartItemCount: Int,
    onFavoriteKeysChange: (Set<String>) -> Unit, onCartChanged: () -> Unit,
    onOpenCart: () -> Unit, onLoginRequired: (String) -> Unit,
    unreadNotificationCount: Int, onOpenNotifications: () -> Unit,
    chromeVisible: Boolean, onChromeVisibilityChange: (Boolean) -> Unit,
    onPromoAction: () -> Unit,
    categories: List<String>, selectedCategory: String?, onCategoryChange: (String?) -> Unit,
    products: List<ProductSummary>, loading: Boolean, message: String?, canLoadMore: Boolean,
    onSearch: () -> Unit, onLoadMore: () -> Unit, onRefresh: () -> Unit,
    onDetailVisibility: (Boolean) -> Unit
) {
    val context = LocalContext.current
    val scope = rememberCoroutineScope()
    val gridState = rememberLazyGridState()
    val density = androidx.compose.ui.platform.LocalDensity.current
    // Keep every catalog/home search compact by default; users open it on demand.
    var searchExpanded by remember(tab) { mutableStateOf(false) }
    val refreshThresholdPx = with(density) { 88.dp.toPx() }
    var pullDistance by remember { mutableFloatStateOf(0f) }
    var refreshTriggered by remember { mutableStateOf(false) }
    var popularHomeProducts by remember { mutableStateOf<List<ProductSummary>>(emptyList()) }
    var newestHomeProducts by remember { mutableStateOf<List<ProductSummary>>(emptyList()) }
    var homeHighlightsLoading by remember { mutableStateOf(false) }
    var launchVouchers by remember { mutableStateOf<List<com.google.gson.JsonObject>>(emptyList()) }
    var showLaunchVouchers by remember { mutableStateOf(false) }
    var launchVoucherCheckDone by remember { mutableStateOf(false) }
    val latestLoading by rememberUpdatedState(loading)
    val latestOnRefresh by rememberUpdatedState(onRefresh)
    val latestOnChromeVisibilityChange by rememberUpdatedState(onChromeVisibilityChange)
    val latestChromeVisible by rememberUpdatedState(chromeVisible)
    LaunchedEffect(gridState) {
        var previousIndex = gridState.firstVisibleItemIndex
        var previousOffset = gridState.firstVisibleItemScrollOffset
        snapshotFlow { gridState.firstVisibleItemIndex to gridState.firstVisibleItemScrollOffset }
            .collect { (index, offset) ->
                val movedDown = index > previousIndex || (index == previousIndex && offset > previousOffset)
                if (movedDown) searchExpanded = false
                previousIndex = index
                previousOffset = offset
            }
    }
    LaunchedEffect(gridState) {
        snapshotFlow { gridState.isScrollInProgress }.collect { scrolling ->
            if (scrolling) {
                // Close the expanded search field as soon as catalog scrolling starts;
                // the compact search icon remains available in the header.
                searchExpanded = false
            } else {
                latestOnChromeVisibilityChange(true)
            }
        }
    }
    val pullRefreshConnection = remember(gridState, refreshThresholdPx) {
        var chromeScroll = 0f
        val chromeThreshold = with(density) { 56.dp.toPx() }
        val revealThreshold = with(density) { 8.dp.toPx() }
        object : NestedScrollConnection {
            override fun onPreScroll(available: Offset, source: NestedScrollSource): Offset {
                if (available.y != 0f) searchExpanded = false
                val atTop = gridState.firstVisibleItemIndex == 0 && gridState.firstVisibleItemScrollOffset == 0
                if (available.y < 0f) {
                    if (latestChromeVisible) {
                        chromeScroll += -available.y
                        if (chromeScroll >= chromeThreshold) {
                            latestOnChromeVisibilityChange(false)
                            chromeScroll = 0f
                        }
                    } else {
                        chromeScroll = 0f
                    }
                } else if (available.y > 0f) {
                    if (atTop) {
                        if (!latestChromeVisible) latestOnChromeVisibilityChange(true)
                        chromeScroll = 0f
                    } else if (!latestChromeVisible) {
                        chromeScroll += available.y
                        if (chromeScroll >= revealThreshold) {
                            latestOnChromeVisibilityChange(true)
                            chromeScroll = 0f
                        }
                    } else {
                        chromeScroll = 0f
                    }
                }
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
    var toastEvent by remember { mutableStateOf<Pair<Int, String>?>(null) }
    var toastSequence by remember { mutableIntStateOf(0) }

    fun showToast(text: String) {
        toastSequence++
        toastEvent = toastSequence to text
    }

    LaunchedEffect(toastEvent) {
        val event = toastEvent ?: return@LaunchedEffect
        delay(2_300)
        if (toastEvent == event) toastEvent = null
    }

    LaunchedEffect(signedIn) {
        if (!signedIn) {
            onFavoriteKeysChange(emptySet())
            return@LaunchedEffect
        }
        runCatching {
            val response = ApiClient.get(context).wishlist()
            val rows = response.body()?.data?.getAsJsonArray("items")
            if (response.isSuccessful && response.body()?.success == true && rows != null) {
                onFavoriteKeysChange(rows.mapNotNull { raw ->
                    val item = raw.takeIf { it.isJsonObject }?.asJsonObject ?: return@mapNotNull null
                    val id = item.get("product_id")?.asString.orEmpty()
                    val variant = item.get("variant")?.asString.orEmpty()
                    if (id.isBlank()) null else favoriteKey(id, variant)
                }.toSet())
            }
        }
    }

    // Show only server-published, globally available promotions; members also see their claimed vouchers.
    LaunchedEffect(signedIn, tab, startupComplete) {
        if (!startupComplete || tab != AppTab.HOME || launchVoucherCheckDone) return@LaunchedEffect
        launchVoucherCheckDone = true
        try {
            val api = ApiClient.get(context)
            val publicResponse = api.voucherPromotions()
            val publicRows = if (publicResponse.isSuccessful && publicResponse.body()?.success == true) {
                publicResponse.body()?.data?.getAsJsonArray("promotions")?.mapNotNull { item ->
                    item.takeIf { it.isJsonObject }?.asJsonObject
                }.orEmpty()
            } else emptyList()
            val claimedRows = if (signedIn) {
                val response = api.availableVouchers()
                if (response.isSuccessful && response.body()?.success == true) {
                    response.body()?.data?.getAsJsonArray("vouchers")?.mapNotNull { item ->
                        item.takeIf { it.isJsonObject }?.asJsonObject
                    }.orEmpty()
                } else emptyList()
            } else emptyList()
            launchVouchers = (publicRows + claimedRows).distinctBy { voucher ->
                listOf(
                    voucher.get("name")?.takeUnless { it.isJsonNull }?.asString.orEmpty().trim().lowercase(Locale.ROOT),
                    voucher.get("unit")?.takeUnless { it.isJsonNull }?.asString.orEmpty().lowercase(Locale.ROOT),
                    voucher.get("nominal")?.takeUnless { it.isJsonNull }?.asString.orEmpty()
                ).joinToString("|")
            }
            showLaunchVouchers = launchVouchers.isNotEmpty()
        } catch (_: Exception) {
            // Promotions are optional and must never block the home screen.
        }
    }

    LaunchedEffect(tab, query, selectedCategory) {
        if (tab != AppTab.HOME || query.isNotBlank()) {
            popularHomeProducts = emptyList()
            newestHomeProducts = emptyList()
            homeHighlightsLoading = false
            return@LaunchedEffect
        }
        homeHighlightsLoading = true
        try {
            val api = ApiClient.get(context)
            coroutineScope {
                val category = selectedCategory?.takeIf(String::isNotBlank)
                val popularRequest = async { api.products(page = 1, perPage = 10, category = category, sort = "popular") }
                val newestRequest = async { api.products(page = 1, perPage = 10, category = category, sort = "newest") }
                val popularResponse = popularRequest.await()
                val newestResponse = newestRequest.await()
                popularHomeProducts = if (popularResponse.isSuccessful && popularResponse.body()?.success == true) {
                    popularResponse.body()?.data?.products.orEmpty().distinctBy { it.id }
                } else emptyList()
                newestHomeProducts = if (newestResponse.isSuccessful && newestResponse.body()?.success == true) {
                    newestResponse.body()?.data?.products.orEmpty().distinctBy { it.id }
                } else emptyList()
            }
        } catch (_: Exception) {
            popularHomeProducts = emptyList()
            newestHomeProducts = emptyList()
        } finally {
            homeHighlightsLoading = false
        }
    }

    fun mutateProduct(product: ProductSummary, variant: String, wishlist: Boolean, add: Boolean = true) {
        if (!signedIn) {
            actionMessage = null
            onLoginRequired(if (wishlist) "Masuk atau buat akun untuk menyimpan produk ke favorit." else "Masuk atau buat akun untuk menambahkan produk ke keranjang.")
            return
        }
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
                val response = if (wishlist) {
                    if (add) api.addWishlistItem(payload) else api.deleteWishlistItem(payload)
                } else api.addCartItem(payload)
                val result = response.body()
                actionMessage = if (response.isSuccessful && result?.success == true) {
                    if (wishlist) {
                        val key = favoriteKey(product.id, variant)
                        onFavoriteKeysChange(if (add) favoriteKeys + key else favoriteKeys - key)
                        showToast(if (add) "Ditambahkan ke Favorit" else "Dihapus dari Favorit")
                        null
                    } else {
                        onCartChanged()
                        showToast("Produk ditambahkan ke Keranjang")
                        null
                    }
                } else result?.error?.message ?: "Permintaan belum berhasil. Periksa koneksi dan coba lagi."
            } catch (_: Exception) {
                actionMessage = "Koneksi belum tersedia. Silakan coba lagi."
            } finally { actionBusy = false }
        }
    }

    fun openProductDetail(product: ProductSummary) {
        detailProductSummary = product
        selectedDetailVariant = product.variants.mapNotNull { it.name?.takeIf(String::isNotBlank) }.distinct().singleOrNull()
        onFavoriteKeysChange(emptySet())
        if (signedIn) onCartChanged()
        scope.launch {
            detailLoading = true
            detailError = null
            try {
                val response = ApiClient.get(context).product(product.id)
                val data = response.body()?.data?.getAsJsonObject("product")
                if (response.isSuccessful && response.body()?.success == true && data != null) {
                    productDetail = data
                    onDetailVisibility(true)
                } else detailError = response.body()?.error?.message ?: "Detail produk belum dapat dimuat."
            } catch (_: Exception) { detailError = "Koneksi belum tersedia. Coba lagi." }
            finally { detailLoading = false }
        }
    }

    fun favoriteProduct(product: ProductSummary) {
        if (!signedIn) {
            actionMessage = null
            onLoginRequired("Masuk atau buat akun untuk menyimpan produk ke favorit.")
            return
        }

        val productPrefix = product.id + "\u001f"
        val savedVariantKeys = favoriteKeys
            .filter { it.startsWith(productPrefix) }
            .map { it.substringAfter(productPrefix) }
            .distinct()

        // The card heart represents the product, so tapping an already-filled heart
        // removes the saved variant(s) instead of always adding another favorite.
        if (savedVariantKeys.isNotEmpty()) {
            scope.launch {
                actionBusy = true
                actionMessage = null
                var remainingKeys = favoriteKeys
                var failed = false
                try {
                    val api = ApiClient.get(context)
                    for (savedKey in savedVariantKeys) {
                        val exactVariant = product.variants
                            .mapNotNull { it.name?.takeIf(String::isNotBlank) }
                            .firstOrNull { it.trim().lowercase(Locale.ROOT) == savedKey }
                            ?: when (savedKey) {
                                "default" -> "Default"
                                else -> savedKey
                            }
                        val payload = JsonObject().apply {
                            addProperty("product_id", product.id)
                            addProperty("variant", exactVariant)
                        }
                        val response = api.deleteWishlistItem(payload)
                        if (response.isSuccessful && response.body()?.success == true) {
                            remainingKeys = remainingKeys - favoriteKey(product.id, savedKey)
                            onFavoriteKeysChange(remainingKeys)
                        } else {
                            failed = true
                            actionMessage = response.body()?.error?.message
                                ?: "Favorit belum berhasil dihapus. Coba lagi."
                            break
                        }
                    }
                    if (!failed) showToast("Dihapus dari Favorit")
                } catch (_: Exception) {
                    actionMessage = "Koneksi belum tersedia. Favorit belum berhasil diubah."
                } finally {
                    actionBusy = false
                }
            }
            return
        }

        val variantNames = product.variants.mapNotNull { it.name?.takeIf(String::isNotBlank) }.distinct()
        if (variantNames.size > 1) {
            detailProductSummary = product
            selectedDetailVariant = null
            scope.launch {
                detailLoading = true
                detailError = null
                try {
                    val response = ApiClient.get(context).product(product.id)
                    val data = response.body()?.data?.getAsJsonObject("product")
                    if (response.isSuccessful && response.body()?.success == true && data != null) {
                        productDetail = data
                        onDetailVisibility(true)
                    } else detailError = "Detail produk belum dapat dimuat."
                } catch (_: Exception) { detailError = "Koneksi belum tersedia. Coba lagi." }
                finally { detailLoading = false }
            }
        } else mutateProduct(product, variantNames.firstOrNull() ?: "Default", true)
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
                if (!searchExpanded) {
                    IconButton(
                        onClick = {
                            searchExpanded = true
                            onChromeVisibilityChange(true)
                        },
                        modifier = Modifier.sizeIn(minWidth = 48.dp, minHeight = 48.dp)
                    ) {
                        Icon(painterResource(R.drawable.ic_search), contentDescription = "Tampilkan pencarian", tint = LunareaGreenText, modifier = Modifier.size(22.dp))
                    }
                }
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
            AnimatedVisibility(
                visible = searchExpanded,
                enter = fadeIn(animationSpec = tween(130)),
                exit = fadeOut(animationSpec = tween(110))
            ) {
                Column {
                    Spacer(Modifier.height(8.dp))
                    OutlinedTextField(
                        value = query, onValueChange = onQueryChange, modifier = Modifier.fillMaxWidth(),
                        placeholder = { Text("Cari furnitur...") }, singleLine = true,
                        shape = RoundedCornerShape(14.dp), keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Text, imeAction = ImeAction.Search),
                        keyboardActions = KeyboardActions(onSearch = { onSearch() }),
                        leadingIcon = { Icon(painterResource(R.drawable.ic_search), contentDescription = null, tint = LunareaGreenText) },
                        trailingIcon = { TextButton(onClick = onSearch) { Text("Cari") } }
                    )
                }
            }
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
            if (tab == AppTab.HOME && query.isBlank()) {
                item(span = { androidx.compose.foundation.lazy.grid.GridItemSpan(maxLineSpan) }) {
                    HomeProductSection(
                        title = "Produk Terlaris",
                        products = popularHomeProducts,
                        loading = homeHighlightsLoading,
                        busy = actionBusy,
                        favoriteKeys = favoriteKeys,
                        onFavorite = ::favoriteProduct,
                        onDetail = ::openProductDetail
                    )
                }
                item(span = { androidx.compose.foundation.lazy.grid.GridItemSpan(maxLineSpan) }) {
                    HomeProductSection(
                        title = "Produk Terbaru",
                        products = newestHomeProducts,
                        loading = homeHighlightsLoading,
                        busy = actionBusy,
                        favoriteKeys = favoriteKeys,
                        onFavorite = ::favoriteProduct,
                        onDetail = ::openProductDetail
                    )
                }
                item(span = { androidx.compose.foundation.lazy.grid.GridItemSpan(maxLineSpan) }) {
                    Text(
                        "Jelajahi semua produk",
                        modifier = Modifier.fillMaxWidth().padding(start = 2.dp, top = 8.dp, bottom = 2.dp),
                        style = MaterialTheme.typography.titleMedium,
                        fontWeight = FontWeight.SemiBold
                    )
                }
            }
            if (loading && products.isEmpty() && message == null) {
                items(4) { ProductCardSkeleton() }
            }
            items(products, key = { it.id }) { product ->
                ProductCard(
                    product = product,
                    busy = actionBusy,
                    favoriteSelected = favoriteKeys.any { it.substringBefore("\u001f") == product.id },
                    onFavorite = { favoriteProduct(product) },
                    onDetail = { openProductDetail(product) }
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
      AnimatedVisibility(
          visible = toastEvent != null,
          modifier = Modifier.align(Alignment.TopEnd).padding(top = 12.dp, end = 12.dp).zIndex(5f),
          enter = slideInHorizontally(initialOffsetX = { it }) + fadeIn(animationSpec = tween(180)),
          exit = slideOutHorizontally(targetOffsetX = { it }) + fadeOut(animationSpec = tween(150))
      ) {
          Surface(
              shape = RoundedCornerShape(16.dp),
              color = MaterialTheme.colorScheme.surface,
              shadowElevation = 6.dp,
              border = androidx.compose.foundation.BorderStroke(1.dp, MaterialTheme.colorScheme.outlineVariant.copy(alpha = .65f)),
              modifier = Modifier.fillMaxWidth(.84f).semantics { liveRegion = LiveRegionMode.Polite }
          ) {
              Row(
                  Modifier.padding(horizontal = 14.dp, vertical = 12.dp),
                  verticalAlignment = Alignment.CenterVertically,
                  horizontalArrangement = Arrangement.spacedBy(10.dp)
              ) {
                  Surface(shape = CircleShape, color = MaterialTheme.colorScheme.primaryContainer, modifier = Modifier.size(30.dp)) {
                      Box(contentAlignment = Alignment.Center) {
                          Text("✓", color = LunareaGreenText, style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.Bold)
                      }
                  }
                  Text(toastEvent?.second.orEmpty(), style = MaterialTheme.typography.labelLarge, color = MaterialTheme.colorScheme.onSurface, fontWeight = FontWeight.Medium)
              }
          }
      }
      if (showLaunchVouchers && tab == AppTab.HOME && launchVouchers.isNotEmpty()) {
          VoucherCampaignPopup(launchVouchers.first(), onDismiss = { showLaunchVouchers = false }) {
              showLaunchVouchers = false
              onPromoAction()
          }
      }
      productDetail?.let { response ->
          val currentVariant = selectedDetailVariant ?: "Default"
          ProductDetailScreen(
              modifier = Modifier.fillMaxSize(),
              detail = response.getAsJsonObject("product") ?: response,
              summary = detailProductSummary,
              loading = detailLoading,
              busy = actionBusy,
              selectedVariant = selectedDetailVariant,
              favoriteSelected = favoriteKey(detailProductSummary?.id.orEmpty(), currentVariant) in favoriteKeys,
              cartItemCount = cartItemCount,
              actionMessage = actionMessage,
              onVariantChange = { selectedDetailVariant = it },
              onBack = { productDetail = null; onDetailVisibility(false) },
              onOpenCart = { productDetail = null; onDetailVisibility(false); onOpenCart() },
              onAddCart = { detailProductSummary?.let { product ->
                  val hasVariants = product.variants.any { !it.name.isNullOrBlank() }
                  val variant = selectedDetailVariant ?: if (!hasVariants) "Default" else null
                  if (variant == null) actionMessage = "Pilih varian terlebih dahulu." else mutateProduct(product, variant, false)
              } },
              onFavorite = { detailProductSummary?.let { product ->
                  val hasVariants = product.variants.any { !it.name.isNullOrBlank() }
                  val variant = selectedDetailVariant ?: if (!hasVariants) "Default" else null
                  if (variant == null) actionMessage = "Pilih varian terlebih dahulu." else {
                      val isFavorite = favoriteKey(product.id, variant) in favoriteKeys
                      mutateProduct(product, variant, true, add = !isFavorite)
                  }
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
    favoriteSelected: Boolean,
    cartItemCount: Int,
    actionMessage: String?,
    onVariantChange: (String) -> Unit,
    onBack: () -> Unit,
    onOpenCart: () -> Unit,
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
    val galleryLoaded = remember(productId, image) { mutableStateListOf<String>() }
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
        galleryLoaded.clear()
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
            BadgedBox(badge = {
                if (cartItemCount > 0) Badge { Text(if (cartItemCount > 99) "99+" else cartItemCount.toString()) }
            }) {
                IconButton(onClick = onOpenCart, modifier = Modifier.size(48.dp)) {
                    Icon(painterResource(R.drawable.ic_nav_cart), contentDescription = "Buka keranjang, $cartItemCount barang", tint = LunareaGreenText, modifier = Modifier.size(23.dp))
                }
            }
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
                        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                            if (photo !in galleryLoaded) {
                                Box(Modifier.fillMaxSize().background(rememberLunareaShimmerBrush()))
                            }
                            AsyncImage(
                                model = photo,
                                contentDescription = "$name, foto ${page + 1} dari ${availableGallery.size}",
                                modifier = Modifier.fillMaxSize().clickable { galleryFullscreen = true },
                                contentScale = ContentScale.Fit,
                                onError = {
                                    galleryLoaded.add(photo)
                                    if (photo !in galleryFailed) galleryFailed.add(photo)
                                },
                                onSuccess = { result ->
                                    galleryLoaded.add(photo)
                                    val drawable = result.result.drawable
                                    val width = drawable.intrinsicWidth
                                    val height = drawable.intrinsicHeight
                                    if (width > 0 && height > 0) galleryAspectRatios[photo] = width.toFloat() / height.toFloat()
                                }
                            )
                        }
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
                            onClick = { onVariantChange(option.name.orEmpty()) },
                            enabled = true,
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
                    if (selectedVariant != null && selectedStock != null && selectedStock <= 0) {
                        Text(
                            if (favoriteSelected) "Favorit tersimpan. Kami akan memberi kabar saat stok tersedia kembali." else "Simpan varian ini ke favorit untuk mendapat kabar saat stok tersedia kembali.",
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                            style = MaterialTheme.typography.bodySmall,
                            lineHeight = 19.sp
                        )
                    }
                } else {
                    Text("Varian standar", style = MaterialTheme.typography.titleSmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                    if (stock != null && stock <= 0) {
                        Text(
                            if (favoriteSelected) "Favorit tersimpan. Kami akan memberi kabar saat stok tersedia kembali." else "Simpan produk ini ke favorit untuk mendapat kabar saat stok tersedia kembali.",
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                            style = MaterialTheme.typography.bodySmall,
                            lineHeight = 19.sp
                        )
                    }
                }
                actionMessage?.takeIf(String::isNotBlank)?.let { Text(it, color = if (it.contains("belum", true) || it.contains("pilih", true) || it.contains("gagal", true)) MaterialTheme.colorScheme.error else LunareaGreenText, style = MaterialTheme.typography.bodySmall) }
            }
        }
        Row(Modifier.fillMaxWidth().background(MaterialTheme.colorScheme.surface).padding(horizontal = 16.dp, vertical = 12.dp), horizontalArrangement = Arrangement.spacedBy(12.dp), verticalAlignment = Alignment.CenterVertically) {
            Column(Modifier.weight(1f)) {
                Text("Harga", style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                Text(price?.let(::formatRupiah) ?: "Hubungi kami", style = MaterialTheme.typography.titleMedium, color = LunareaGreenText, fontWeight = FontWeight.Bold, maxLines = 1)
            }
            OutlinedButton(onClick = onFavorite, enabled = !busy, modifier = Modifier.sizeIn(minWidth = 48.dp, minHeight = 48.dp), contentPadding = PaddingValues(0.dp), shape = RoundedCornerShape(14.dp)) {
                Icon(painterResource(if (favoriteSelected) R.drawable.ic_favorite_filled else R.drawable.ic_favorite_outline), contentDescription = if (favoriteSelected) "Hapus dari favorit" else "Simpan ke favorit", tint = if (favoriteSelected) MaterialTheme.colorScheme.error else LunareaGreenText)
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
                    if (sheet == ProductInfoSheet.REVIEWS) {
                        val summary = runCatching { detail.getAsJsonObject("review_summary") }.getOrNull()
                        val average = summary?.get("avg")?.takeUnless { it.isJsonNull }?.toString()?.trim('"') ?: "0"
                        val count = summary?.get("count")?.takeUnless { it.isJsonNull }?.toString()?.trim('"')?.toIntOrNull() ?: 0
                        Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                            Text("★ $average", color = LunareaGreenText, style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.Bold)
                            Text("·  $count ulasan", style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                        }
                    }
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
                                            val reviewer = review.get("name")?.takeUnless { it.isJsonNull }?.asString
                                                ?: review.get("nama")?.takeUnless { it.isJsonNull }?.asString
                                            if (!reviewer.isNullOrBlank()) Text(reviewer, style = MaterialTheme.typography.labelMedium, fontWeight = FontWeight.Medium)
                                            val comment = review.get("comment")?.takeUnless { it.isJsonNull }?.asString
                                                ?: review.get("ulasan")?.takeUnless { it.isJsonNull }?.asString
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
                                Text("Belum ada ulasan", style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.SemiBold)
                                Text(
                                    "Jadilah yang pertama membagikan pengalaman setelah berbelanja.",
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
private fun HomeProductSection(
    title: String,
    products: List<ProductSummary>,
    loading: Boolean,
    busy: Boolean,
    favoriteKeys: Set<String>,
    onFavorite: (ProductSummary) -> Unit,
    onDetail: (ProductSummary) -> Unit
) {
    if (products.isEmpty() && !loading) return
    Column(Modifier.fillMaxWidth(), verticalArrangement = Arrangement.spacedBy(8.dp)) {
        Row(Modifier.fillMaxWidth().padding(horizontal = 2.dp), verticalAlignment = Alignment.CenterVertically) {
            Text(title, modifier = Modifier.weight(1f), style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
            if (loading && products.isEmpty()) CircularProgressIndicator(Modifier.size(18.dp), strokeWidth = 2.dp)
        }
        if (products.isEmpty()) {
            LazyRow(contentPadding = PaddingValues(horizontal = 1.dp), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                items(3) { ProductCardSkeleton(Modifier.width(168.dp)) }
            }
        } else {
            LazyRow(contentPadding = PaddingValues(horizontal = 1.dp), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                items(products, key = { it.id }) { product ->
                    ProductCard(
                        product = product,
                        busy = busy,
                        favoriteSelected = favoriteKeys.any { it.substringBefore("\u001f") == product.id },
                        onFavorite = { onFavorite(product) },
                        onDetail = { onDetail(product) },
                        modifier = Modifier.width(168.dp)
                    )
                }
            }
        }
    }
}

@Composable
private fun ProductCard(product: ProductSummary, busy: Boolean, favoriteSelected: Boolean, onFavorite: () -> Unit, onDetail: () -> Unit, modifier: Modifier = Modifier.fillMaxWidth()) {
    val variants = product.variants.mapNotNull { it.name?.takeIf(String::isNotBlank) }.distinct()
    var productImageReady by remember(product.image_url) { mutableStateOf(false) }
    val discount = product.discount_percent?.coerceIn(0, 100) ?: 0
    Card(
        onClick = onDetail,
        modifier = modifier.fillMaxWidth(),
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
                ) {
                    Box(Modifier.size(32.dp).background(MaterialTheme.colorScheme.surface.copy(alpha = .96f), CircleShape), contentAlignment = Alignment.Center) {
                        Icon(
                            painter = painterResource(if (favoriteSelected) R.drawable.ic_favorite_filled else R.drawable.ic_favorite_outline),
                            contentDescription = if (favoriteSelected) "${product.name} tersimpan di favorit. Hapus dari favorit" else "Simpan ${product.name} ke favorit",
                            tint = if (favoriteSelected) MaterialTheme.colorScheme.error else LunareaGreenText,
                            modifier = Modifier.size(17.dp)
                        )
                    }
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
                product.sold_count?.takeIf { it > 0 }?.let { sold ->
                    Surface(
                        Modifier.align(Alignment.BottomEnd).padding(10.dp),
                        shape = RoundedCornerShape(7.dp),
                        color = MaterialTheme.colorScheme.surface.copy(alpha = .95f)
                    ) {
                        Text(
                            "$sold terjual",
                            Modifier.padding(horizontal = 7.dp, vertical = 4.dp),
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                            fontSize = 9.sp,
                            lineHeight = 11.sp,
                            fontWeight = FontWeight.Medium
                        )
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
private fun PointsScreen(modifier: Modifier, onBack: () -> Unit) {
    val context = LocalContext.current
    var refreshKey by remember { mutableIntStateOf(0) }
    var page by remember { mutableIntStateOf(1) }
    var data by remember { mutableStateOf<JsonObject?>(null) }
    var history by remember { mutableStateOf<List<com.google.gson.JsonElement>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(refreshKey, page) {
        loading = true
        error = null
        try {
            val response = ApiClient.get(context).points(page)
            if (response.isSuccessful && response.body()?.success == true) {
                val payload = response.body()?.data
                data = payload
                val rows = payload?.arrayOrNull("history")?.toList().orEmpty()
                history = if (page == 1) rows else (history + rows).distinctBy { it.asJsonObject.get("id")?.asString }
            } else {
                error = response.customerError("Informasi Luna Poin belum dapat dimuat.")
            }
        } catch (_: Exception) {
            error = "Koneksi belum tersedia. Periksa internet lalu coba lagi."
        } finally {
            loading = false
        }
    }

    Column(modifier.fillMaxSize().padding(horizontal = 20.dp, vertical = 12.dp)) {
        Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
            TextButton(onClick = onBack, contentPadding = PaddingValues(horizontal = 4.dp)) { Text("‹  Akun") }
            Spacer(Modifier.weight(1f))
        }
        Text("Luna Poin", style = MaterialTheme.typography.headlineSmall, fontWeight = FontWeight.SemiBold)
        Text("Pantau saldo, masa berlaku, dan aktivitas poin Anda.", color = MaterialTheme.colorScheme.onSurfaceVariant, style = MaterialTheme.typography.bodyMedium)
        Spacer(Modifier.height(18.dp))

        if (loading && data == null) {
            Box(Modifier.fillMaxWidth().weight(1f), contentAlignment = Alignment.Center) { CircularProgressIndicator() }
        } else if (error != null && data == null) {
            Column(Modifier.fillMaxWidth().weight(1f), horizontalAlignment = Alignment.CenterHorizontally, verticalArrangement = Arrangement.Center) {
                Text(error.orEmpty(), color = MaterialTheme.colorScheme.onSurfaceVariant)
                TextButton(onClick = { refreshKey++ }) { Text("Coba lagi") }
            }
        } else {
            val balance = data?.longOrNull("balance") ?: 0L
            Surface(shape = RoundedCornerShape(20.dp), color = MaterialTheme.colorScheme.primaryContainer, modifier = Modifier.fillMaxWidth()) {
                Column(Modifier.padding(20.dp), verticalArrangement = Arrangement.spacedBy(6.dp)) {
                    Text("SALDO TERSEDIA", style = MaterialTheme.typography.labelMedium, color = MaterialTheme.colorScheme.onPrimaryContainer)
                    Text(formatRupiah(balance), style = MaterialTheme.typography.headlineMedium, fontWeight = FontWeight.Bold, color = MaterialTheme.colorScheme.onPrimaryContainer)
                    data?.stringOrNull("next_expiry")?.takeIf(String::isNotBlank)?.let {
                        Text("Poin terdekat kedaluwarsa: $it", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onPrimaryContainer)
                    }
                }
            }
            val tier = data?.objectOrNull("tier")
            if (tier != null) {
                Spacer(Modifier.height(12.dp))
                Surface(shape = RoundedCornerShape(18.dp), color = MaterialTheme.colorScheme.surface, modifier = Modifier.fillMaxWidth()) {
                    Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                        Text("Luna Rewards · ${tier.stringOrNull("label") ?: "Bronze"}", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
                        val progress = ((tier.longOrNull("progress") ?: 0L).toFloat() / 100f).coerceIn(0f, 1f)
                        LinearProgressIndicator(progress = { progress }, modifier = Modifier.fillMaxWidth())
                        val spend = tier.longOrNull("spend") ?: 0L
                        val next = tier.longOrNull("next_minimum")
                        Text(if (next == null) "Total belanja tier: ${formatRupiah(spend)} · Tier tertinggi" else "Total belanja tier: ${formatRupiah(spend)} dari ${formatRupiah(next)}", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                        data?.arrayOrNull("benefits")?.takeIf { it.size() > 0 }?.let { benefits ->
                            benefits.forEach { benefit ->
                                val item = benefit.takeIf { it.isJsonObject }?.asJsonObject ?: return@forEach
                                Text("• ${item.stringOrNull("name").orEmpty()} — ${item.stringOrNull("description").orEmpty()}", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                            }
                        }
                        if (data?.get("birthday_registered")?.takeUnless { it.isJsonNull }?.asBoolean == false) {
                            Text("Lengkapi tanggal lahir di profil untuk benefit ulang tahun.", style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.tertiary)
                        }
                    }
                }
            }
            Spacer(Modifier.height(20.dp))
            Text("Riwayat poin", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
            Spacer(Modifier.height(8.dp))
            if (history.isEmpty()) {
                Surface(Modifier.fillMaxWidth(), shape = RoundedCornerShape(16.dp), color = MaterialTheme.colorScheme.surface) {
                    Text("Belum ada aktivitas Luna Poin.", Modifier.padding(16.dp), color = MaterialTheme.colorScheme.onSurfaceVariant)
                }
            } else {
                LazyColumn(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(8.dp), contentPadding = PaddingValues(bottom = 16.dp)) {
                    items(history) { item ->
                        val row = item.takeIf { it.isJsonObject }?.asJsonObject
                        val amount = row?.longOrNull("amount") ?: 0L
                        Surface(Modifier.fillMaxWidth(), shape = RoundedCornerShape(14.dp), color = MaterialTheme.colorScheme.surface) {
                            Row(Modifier.padding(14.dp), verticalAlignment = Alignment.CenterVertically) {
                                Column(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(3.dp)) {
                                    Text(row?.stringOrNull("label").orEmpty().ifBlank { "Perubahan poin" }, fontWeight = FontWeight.Medium)
                                    row?.stringOrNull("description")?.takeIf(String::isNotBlank)?.let { Text(it, style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant) }
                                    row?.stringOrNull("date")?.takeIf(String::isNotBlank)?.let { Text(formatLunareaDateTime(it), style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.onSurfaceVariant) }
                                }
                                Text("${if (amount > 0) "+" else ""}${formatRupiah(amount)}", fontWeight = FontWeight.SemiBold,
                                    color = if (amount < 0) MaterialTheme.colorScheme.error else LunareaGreenText)
                            }
                        }
                    }
                    if (history.size < (data?.longOrNull("total_history") ?: 0L)) {
                        item {
                            TextButton(onClick = { if (!loading) page++ }, enabled = !loading, modifier = Modifier.fillMaxWidth()) {
                                if (loading) CircularProgressIndicator(Modifier.size(18.dp), strokeWidth = 2.dp) else Text("Muat riwayat sebelumnya")
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun AccountScreen(
    modifier: Modifier, signedIn: Boolean, busy: Boolean, identifier: String, password: String,
    onIdentifier: (String) -> Unit, onPassword: (String) -> Unit, onLogin: () -> Unit, onGoogleLogin: () -> Unit, onLogout: () -> Unit,
    onEnableNotifications: () -> Unit, notificationsEnabled: Boolean, onDisableNotifications: () -> Unit,
    onOpenOrders: () -> Unit, onOpenFavorites: () -> Unit, onOpenAddresses: () -> Unit,
    onOpenProfile: () -> Unit, onOpenSecurity: () -> Unit, onOpenPoints: () -> Unit, onOpenRegistration: () -> Unit, onOpenRecovery: () -> Unit, onOpenVerification: () -> Unit,
    message: String?
) {
    Column(modifier.fillMaxSize().imePadding().verticalScroll(rememberScrollState()).padding(20.dp), verticalArrangement = Arrangement.spacedBy(14.dp)) {
        if (signedIn) LunareaSectionTitle("Akun Anda", "Kelola profil, pesanan, dan preferensi")
        if (!signedIn) {
            AuthFlowHeader("Selamat datang", "Masuk untuk melanjutkan belanja dan melihat pesanan Anda.", onOpenRegistration, showBack = false)
                Column(Modifier.fillMaxWidth(), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                    OutlinedTextField(identifier, onIdentifier, modifier = Modifier.fillMaxWidth(), label = { Text("Email atau username") }, singleLine = true, shape = RoundedCornerShape(12.dp), keyboardOptions = KeyboardOptions(imeAction = ImeAction.Next))
                    PasswordInputField(password, onPassword, "Kata sandi")
                    Button(onClick = onLogin, enabled = !busy && identifier.isNotBlank() && password.isNotBlank(), modifier = Modifier.fillMaxWidth().heightIn(min = 52.dp), shape = RoundedCornerShape(12.dp)) {
                        if (busy) CircularProgressIndicator(Modifier.size(18.dp), strokeWidth = 2.dp) else Text("Masuk")
                    }
                    Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                        HorizontalDivider(Modifier.weight(1f), color = MaterialTheme.colorScheme.outlineVariant)
                        Text("atau", color = MaterialTheme.colorScheme.onSurfaceVariant, style = MaterialTheme.typography.labelSmall)
                        HorizontalDivider(Modifier.weight(1f), color = MaterialTheme.colorScheme.outlineVariant)
                    }
                    OutlinedButton(
                        onClick = onGoogleLogin,
                        enabled = !busy,
                        modifier = Modifier.fillMaxWidth().heightIn(min = 50.dp),
                        shape = RoundedCornerShape(12.dp),
                        colors = androidx.compose.material3.ButtonDefaults.outlinedButtonColors(
                            containerColor = androidx.compose.ui.graphics.Color.White,
                            contentColor = androidx.compose.ui.graphics.Color(0xFF3C4043)
                        ),
                        border = androidx.compose.foundation.BorderStroke(1.dp, androidx.compose.ui.graphics.Color(0xFFDADCE0))
                    ) {
                        Image(
                            painter = painterResource(R.drawable.google_logo),
                            contentDescription = null,
                            modifier = Modifier.size(20.dp)
                        )
                        Spacer(Modifier.width(12.dp))
                        Text("Lanjutkan dengan Google", color = androidx.compose.ui.graphics.Color(0xFF3C4043), fontWeight = FontWeight.Medium)
                    }
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.Center, verticalAlignment = Alignment.CenterVertically) {
                        Text("Belum punya akun?", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                        TextButton(onClick = onOpenRegistration, contentPadding = PaddingValues(horizontal = 6.dp)) { Text("Daftar") }
                    }
                }
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                TextButton(onClick = onOpenRecovery, contentPadding = PaddingValues(horizontal = 4.dp)) { Text("Lupa kata sandi?") }
                TextButton(onClick = onOpenVerification, contentPadding = PaddingValues(horizontal = 4.dp)) { Text("Verifikasi email") }
            }
        } else {
            Card(shape = RoundedCornerShape(18.dp), colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)) {
                Column(Modifier.fillMaxWidth().padding(18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
                    Text("Layanan akun", fontWeight = FontWeight.SemiBold)
                    AccountActionRow("Profil saya", "Nama dan nomor ponsel", onOpenProfile)
                    AccountActionRow("Keamanan akun", "Kata sandi dan email", onOpenSecurity)
                    AccountActionRow("Pesanan saya", "Pembayaran dan status pengiriman", onOpenOrders)
                    AccountActionRow("Luna Poin", "Saldo, masa berlaku, dan riwayat poin", onOpenPoints)
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

private fun JsonObject.objectOrNull(key: String): JsonObject? =
    get(key)?.takeIf { it.isJsonObject }?.asJsonObject

private fun JsonObject.arrayOrNull(key: String): com.google.gson.JsonArray? =
    get(key)?.takeIf { it.isJsonArray }?.asJsonArray

private fun JsonObject.longOrNull(key: String): Long? =
    get(key)?.takeUnless { it.isJsonNull }?.let { runCatching { it.asLong }.getOrNull() }

private fun formatLunareaDateTime(raw: String): String {
    val value = raw.trim()
    if (value.isEmpty()) return value

    val localeId = Locale("id", "ID")
    val jakarta = java.util.TimeZone.getTimeZone("Asia/Jakarta")
    val dateOnly = Regex("^\\d{4}-\\d{2}-\\d{2}$").matches(value)
    val epoch = value.toLongOrNull()?.let { timestamp ->
        when (value.length) {
            10 -> java.util.Date(timestamp * 1_000L)
            13 -> java.util.Date(timestamp)
            else -> null
        }
    }
    val parsed = epoch ?: run {
        val patterns = listOf(
            "yyyy-MM-dd'T'HH:mm:ss.SSSXXX",
            "yyyy-MM-dd'T'HH:mm:ssXXX",
            "yyyy-MM-dd'T'HH:mm:ss.SSS'Z'",
            "yyyy-MM-dd'T'HH:mm:ss'Z'",
            "yyyy-MM-dd'T'HH:mm:ss.SSS",
            "yyyy-MM-dd'T'HH:mm:ss",
            "yyyy-MM-dd HH:mm:ss.SSS",
            "yyyy-MM-dd HH:mm:ss",
            "yyyy-MM-dd HH:mm",
            "dd/MM/yyyy HH:mm:ss",
            "dd/MM/yyyy HH:mm",
            "yyyy-MM-dd"
        )
        patterns.firstNotNullOfOrNull { pattern ->
            runCatching {
                val parser = java.text.SimpleDateFormat(pattern, Locale.US).apply {
                    isLenient = false
                    timeZone = if (value.endsWith("Z", ignoreCase = true)) java.util.TimeZone.getTimeZone("UTC") else jakarta
                }
                val position = java.text.ParsePosition(0)
                parser.parse(value, position)?.takeIf { position.index == value.length }
            }.getOrNull()
        }
    } ?: return value

    val outputPattern = if (dateOnly) "dd MMMM yyyy" else "dd MMMM yyyy HH:mm 'WIB'"
    return java.text.SimpleDateFormat(outputPattern, localeId).apply { timeZone = jakarta }.format(parsed)
}

private fun formatRupiah(amount: Long): String = NumberFormat.getCurrencyInstance(Locale("id", "ID")).format(amount).replace(",00", "")

private fun isUnpaidOrderStatus(status: String): Boolean {
    val normalized = status.trim().lowercase(Locale.ROOT)
    return normalized.contains("pending") || normalized.contains("menunggu pembayaran") || normalized.contains("belum dibayar") || normalized.contains("unpaid")
}

private fun paymentStatusLabel(status: String?): String = when (status?.trim()?.lowercase(Locale.ROOT)) {
    "settlement", "capture", "proses", "paid" -> "Pembayaran berhasil"
    "pending", "pending-rekening", "menunggu pembayaran", "unpaid" -> "Menunggu pembayaran"
    "expire", "expired", "kadaluarsa" -> "Kedaluwarsa"
    "cancel", "cancelled", "canceled", "dibatalkan" -> "Dibatalkan"
    "deny", "denied", "ditolak" -> "Ditolak"
    "failure", "failed", "gagal" -> "Pembayaran gagal"
    null, "" -> "Belum tersedia"
    else -> status.replace('_', ' ').replace('-', ' ').replaceFirstChar(Char::uppercase)
}

private fun checkoutRequestSignature(addressId: Long, quote: String, payment: String, points: Boolean, glass: Boolean, voucherClaimId: Long = 0L): String {
    val raw = listOf(addressId.toString(), quote, payment, points.toString(), glass.toString(), voucherClaimId.toString()).joinToString("|")
    return java.security.MessageDigest.getInstance("SHA-256").digest(raw.toByteArray()).joinToString("") { "%02x".format(it) }
}

private fun openWebsite(context: android.content.Context, url: String) {
    context.startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url)))
}
