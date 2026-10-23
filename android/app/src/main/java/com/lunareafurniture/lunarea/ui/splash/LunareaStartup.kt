package com.lunareafurniture.lunarea.ui.splash

import android.animation.ValueAnimator
import android.graphics.drawable.BitmapDrawable
import androidx.compose.animation.core.Animatable
import androidx.compose.animation.core.FastOutSlowInEasing
import androidx.compose.animation.core.LinearEasing
import androidx.compose.animation.core.tween
import androidx.compose.foundation.Canvas
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.BoxWithConstraints
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.aspectRatio
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.width
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.ImageBitmap
import androidx.compose.ui.graphics.asImageBitmap
import androidx.compose.ui.graphics.graphicsLayer
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.input.pointer.pointerInput
import androidx.compose.ui.platform.LocalContext
import androidx.core.graphics.drawable.toBitmap
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.IntOffset
import androidx.compose.ui.unit.IntSize
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.lunareafurniture.lunarea.R
import com.lunareafurniture.lunarea.ui.theme.recolorDarkWordmark
import coil.imageLoader
import coil.request.ImageRequest
import kotlinx.coroutines.coroutineScope
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import kotlin.math.roundToInt

private const val LogoAspectRatio = 9.407f

/** Keeps the app tree alive underneath the brand reveal so startup work can proceed. */
@Composable
fun LunareaStartup(content: @Composable (startupComplete: Boolean) -> Unit) {
    var splashVisible by rememberSaveable { mutableStateOf(true) }
    var startupComplete by rememberSaveable { mutableStateOf(false) }

    Box(Modifier.fillMaxSize()) {
        content(startupComplete)
        if (splashVisible) {
            LunareaBrandSplash {
                startupComplete = true
                splashVisible = false
            }
        }
    }
}
@Composable
private fun LunareaBrandSplash(onFinished: () -> Unit) {
    val context = LocalContext.current
    val isDarkTheme = isSystemInDarkTheme()
    val animationsEnabled = remember { ValueAnimator.areAnimatorsEnabled() }
    val markAlpha = remember { Animatable(1f) }
    val markScale = remember { Animatable(0.92f) }
    val wordmarkProgress = remember { Animatable(0f) }
    val taglineAlpha = remember { Animatable(0f) }
    val taglineOffset = remember { Animatable(5f) }
    val overlayAlpha = remember { Animatable(1f) }
    var logo by remember { mutableStateOf<ImageBitmap?>(null) }
    var ilenaLogo by remember { mutableStateOf<ImageBitmap?>(null) }
    var logoLoadFailed by remember { mutableStateOf(false) }

    // Decode a display-sized copy off the main thread instead of synchronously
    // decoding the 4713px source bitmap during first composition.
    LaunchedEffect(context, isDarkTheme) {
        val request = ImageRequest.Builder(context)
            .data(R.drawable.logo_lunarea)
            .size(960, 102)
            .allowHardware(false)
            .build()
        try {
            // Decode the small Ilena endorsement first. The N mark is already
            // visible, so this makes both brands appear together while the larger
            // Lunarea wordmark finishes loading.
            val ilenaRequest = ImageRequest.Builder(context)
                .data(R.drawable.ilena_wordmark)
                .size(240, 34)
                .allowHardware(false)
                .build()
            val ilenaResult = context.imageLoader.execute(ilenaRequest)
            val ilenaDrawable = ilenaResult.drawable as? BitmapDrawable
                ?: error("Ilena wordmark was not a bitmap")
            val ilenaBitmap = ilenaDrawable.toBitmap(width = 240, height = 34)
            ilenaLogo = withContext(Dispatchers.Default) {
                if (isDarkTheme) recolorDarkWordmark(ilenaBitmap, preserveMarkInterior = true) else ilenaBitmap
            }.asImageBitmap()

            val result = context.imageLoader.execute(request)
            val drawable = result.drawable as? BitmapDrawable
                ?: error("Logo drawable was not a bitmap")
            val brandBitmap = drawable.toBitmap(width = 960, height = 102)
            logo = withContext(Dispatchers.Default) {
                if (isDarkTheme) recolorDarkWordmark(brandBitmap, preserveMarkInterior = false) else brandBitmap
            }.asImageBitmap()
        } catch (cancelled: CancellationException) {
            throw cancelled
        } catch (_: Exception) {
            // Never leave users on an empty splash if a local asset cannot decode.
            logoLoadFailed = true
        }
    }

    LaunchedEffect(animationsEnabled, logo, ilenaLogo, logoLoadFailed) {
        if (!animationsEnabled) {
            onFinished()
            return@LaunchedEffect
        }
        if (logoLoadFailed) {
            onFinished()
            return@LaunchedEffect
        }
        // Start the branded reveal only after both wordmarks are ready so the
        // PART OF endorsement never appears without its matching Ilena logo.
        if (logo == null || ilenaLogo == null) return@LaunchedEffect

        // Phase 1: the original N mark, cropped directly from the supplied logo.
        coroutineScope {
            launch { markAlpha.animateTo(1f, tween(300, easing = FastOutSlowInEasing)) }
            launch { markScale.animateTo(1f, tween(320, easing = FastOutSlowInEasing)) }
        }

        // Phase 2: reveal the original wordmark in seven source-image slices.
        delay(50)
        coroutineScope {
            launch { markAlpha.animateTo(0f, tween(240, easing = FastOutSlowInEasing)) }
            // A single progress animation drives the staggered slices, avoiding
            // fourteen concurrently-running Animatables and excess recomposition.
            launch {
                wordmarkProgress.animateTo(7f, tween(520, easing = LinearEasing))
            }
        }

        // Phase 3: tagline rises gently into place.
        coroutineScope {
            launch { taglineAlpha.animateTo(1f, tween(220, easing = FastOutSlowInEasing)) }
            launch { taglineOffset.animateTo(0f, tween(220, easing = FastOutSlowInEasing)) }
        }

        // Phase 4 and 5: brief brand hold, then a seamless fade to the app.
        delay(200)
        overlayAlpha.animateTo(0f, tween(180, easing = FastOutSlowInEasing))
        onFinished()
    }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .graphicsLayer { alpha = overlayAlpha.value }
            .pointerInput(Unit) {
                awaitPointerEventScope {
                    while (true) awaitPointerEvent().changes.forEach { it.consume() }
                }
            },
        contentAlignment = Alignment.Center
    ) {
        BoxWithConstraints(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            val wordmarkWidth = minOf(maxWidth * 0.84f, 320.dp)
            Column(
                horizontalAlignment = Alignment.CenterHorizontally,
                verticalArrangement = Arrangement.spacedBy(14.dp)
            ) {
                // Reserve one stable logo area so the mark-to-wordmark transition does not jump.
                Box(
                    modifier = Modifier.width(wordmarkWidth).height(92.dp),
                    contentAlignment = Alignment.Center
                ) {
                    logo?.let { bitmap ->
                        WhiteBrandMark(markAlpha.value, markScale.value)
                        OriginalWordmark(bitmap, wordmarkProgress.value)
                    }
                    if (logo == null && !logoLoadFailed) {
                        WhiteBrandMark(1f, markScale.value)
                    }
                }
                Text(
                    text = "always your choice, always your furniture.",
                    modifier = Modifier.graphicsLayer {
                        alpha = taglineAlpha.value
                        translationY = taglineOffset.value * density
                    },
                    color = if (isDarkTheme) Color.White else MaterialTheme.colorScheme.onSurfaceVariant,
                    style = MaterialTheme.typography.bodySmall,
                    fontSize = 11.sp,
                    letterSpacing = 0.65.sp,
                    textAlign = TextAlign.Center
                )
            }
        }
        if (ilenaLogo != null) {
            Row(
                modifier = Modifier
                    .align(Alignment.BottomCenter)
                    .padding(bottom = 28.dp)
                    .graphicsLayer { alpha = overlayAlpha.value },
                horizontalArrangement = Arrangement.spacedBy(10.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = "Part Of",
                    color = if (isDarkTheme) Color.White else MaterialTheme.colorScheme.onSurfaceVariant,
                    style = MaterialTheme.typography.labelSmall,
                    fontSize = 10.sp,
                    letterSpacing = 1.1.sp
                )
                ilenaLogo?.let { image ->
                    Image(
                        bitmap = image,
                        contentDescription = "Ilena",
                        modifier = Modifier.width(74.dp).height(24.dp),
                        contentScale = ContentScale.Fit
                    )
                }
            }
        }
    }
}

/** Uses the same white-N launcher mark for a consistent native splash identity. */
@Composable
private fun WhiteBrandMark(alpha: Float, scale: Float) {
    Image(
        painter = painterResource(R.drawable.ic_launcher_foreground_white),
        contentDescription = "Logo Lunarea",
        modifier = Modifier
            .width(78.dp)
            .aspectRatio(1f)
            .graphicsLayer {
                this.alpha = alpha
                scaleX = scale
                scaleY = scale
            }
    )
}

/** Reveals the supplied wordmark as equal-width slices, preserving original pixels and proportions. */
@Composable
private fun OriginalWordmark(logo: ImageBitmap, progress: Float) {
    Canvas(Modifier.fillMaxWidth(0.96f).aspectRatio(LogoAspectRatio)) {
        val scale = size.width / logo.width
        val imageHeight = (logo.height * scale).roundToInt()
        val top = ((size.height - imageHeight) / 2f).roundToInt()

        repeat(7) { index ->
            val left = logo.width * index / 7
            val right = logo.width * (index + 1) / 7
            val sliceWidth = right - left
            val sliceProgress = (progress - index).coerceIn(0f, 1f)
            val alpha = FastOutSlowInEasing.transform(sliceProgress)
            val slidePx = ((1f - alpha) * 5.dp.toPx()).roundToInt()
            drawImage(
                image = logo,
                srcOffset = IntOffset(left, 0),
                srcSize = IntSize(sliceWidth, logo.height),
                dstOffset = IntOffset((left * scale).roundToInt() + slidePx, top),
                dstSize = IntSize((sliceWidth * scale).roundToInt(), imageHeight),
                alpha = alpha
            )
        }
    }
}
