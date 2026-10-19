package com.lunareafurniture.lunarea.data

import android.content.Context
import android.location.Address
import android.location.Geocoder
import android.location.Location
import android.os.Build
import com.google.android.gms.location.LocationServices
import com.google.android.gms.location.Priority
import com.google.android.gms.tasks.CancellationTokenSource
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import kotlinx.coroutines.withTimeoutOrNull
import kotlinx.coroutines.tasks.await
import kotlin.coroutines.resume
import java.util.Locale

data class LocationAddressSuggestion(
    val province: String?,
    val cityCandidates: List<String>,
    val districtCandidates: List<String>,
    val villageCandidates: List<String>,
    val addressDetail: String
)

/** One-shot, foreground-only location lookup; coordinates are not persisted or sent to Lunarea. */
@Suppress("MissingPermission")
suspend fun resolveCurrentAddress(context: Context): LocationAddressSuggestion? {
    val cancellation = CancellationTokenSource()
    val location = try {
        withTimeoutOrNull(20_000) {
            LocationServices.getFusedLocationProviderClient(context.applicationContext)
                .getCurrentLocation(Priority.PRIORITY_HIGH_ACCURACY, cancellation.token)
                .await()
        }
    } finally {
        cancellation.cancel()
    }
    if (location == null) return null

    if (!Geocoder.isPresent()) throw IllegalStateException("Layanan pencarian alamat tidak tersedia pada perangkat ini.")
    val geocoder = Geocoder(context, Locale("id", "ID"))
    val address: Address = (if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
        withTimeoutOrNull(12_000) {
            kotlinx.coroutines.suspendCancellableCoroutine { continuation ->
                geocoder.getFromLocation(location.latitude, location.longitude, 1, object : Geocoder.GeocodeListener {
                    override fun onGeocode(addresses: MutableList<Address>) {
                        if (continuation.isActive) continuation.resume(addresses.firstOrNull())
                    }

                    override fun onError(errorMessage: String?) {
                        if (continuation.isActive) continuation.resume(null)
                    }
                })
            }
        }
    } else {
        withTimeoutOrNull(12_000) {
            withContext(Dispatchers.IO) { @Suppress("DEPRECATION") geocoder.getFromLocation(location.latitude, location.longitude, 1)?.firstOrNull() }
        }
    }) ?: throw IllegalStateException("Alamat lokasi belum dapat dikenali. Pilih wilayah secara manual.")

    fun distinctValues(vararg values: String?): List<String> = values.mapNotNull { it?.trim()?.takeIf(String::isNotEmpty) }.distinct()
    val detail = listOf(address.thoroughfare, address.subThoroughfare, address.premises, address.featureName)
        .mapNotNull { it?.trim()?.takeIf(String::isNotEmpty) }
        .distinct()
        .joinToString(", ")

    return LocationAddressSuggestion(
        province = address.adminArea?.trim()?.takeIf(String::isNotEmpty),
        cityCandidates = distinctValues(address.locality, address.subAdminArea),
        districtCandidates = distinctValues(address.subLocality, address.subAdminArea),
        villageCandidates = distinctValues(address.subLocality, address.subThoroughfare, address.featureName),
        addressDetail = detail.take(1000)
    )
}
