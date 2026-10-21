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
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.BoxWithConstraints
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.aspectRatio
import androidx.compose.foundation.layout.fillMaxSize
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
import coil.imageLoader
import coil.request.ImageRequest
import kotlinx.coroutines.coroutineScope
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import kotlin.math.roundToInt

private val LunareaWarmIvory = Color(0xFFFAF9F6)
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
    val animationsEnabled = remember { ValueAnimator.areAnimatorsEnabled() }
    val markAlpha = remember { Animatable(0f) }
    val markScale = remember { Animatable(0.92f) }
    val wordmarkProgress = remember { Animatable(0f) }
    val taglineAlpha = remember { Animatable(0f) }
    val taglineOffset = remember { Animatable(5f) }
    val overlayAlpha = remember { Animatable(1f) }
    var logo by remember { mutableStateOf<ImageBitmap?>(null) }
    var logoLoadFailed by remember { mutableStateOf(false) }

    // Decode a display-sized copy off the main thread instead of synchronously
    // decoding the 4713px source bitmap during first composition.
    LaunchedEffect(context) {
        val request = ImageRequest.Builder(context)
            .data(R.drawable.logo_lunarea)
            .size(960, 102)
            .allowHardware(false)
            .build()
        try {
            val result = context.imageLoader.execute(request)
            val drawable = result.drawable as? BitmapDrawable
                ?: error("Logo drawable was not a bitmap")
            logo = drawable.toBitmap(width = 960, height = 102).asImageBitmap()
        } catch (cancelled: CancellationException) {
            throw cancelled
        } catch (_: Exception) {
            // Never leave users on an empty splash if a local asset cannot decode.
            logoLoadFailed = true
        }
    }

    LaunchedEffect(animationsEnabled, logo, logoLoadFailed) {
        if (!animationsEnabled) {
            onFinished()
            return@LaunchedEffect
        }
        if (logoLoadFailed) {
            onFinished()
            return@LaunchedEffect
        }
        if (logo == null) return@LaunchedEffect

        // Phase 1: the original N mark, cropped directly from the supplied logo.
        coroutineScope {
            launch { markAlpha.animateTo(1f, tween(460, easing = FastOutSlowInEasing)) }
            launch { markScale.animateTo(1f, tween(520, easing = FastOutSlowInEasing)) }
        }

        // Phase 2: reveal the original wordmark in seven source-image slices.
        delay(80)
        coroutineScope {
            launch { markAlpha.animateTo(0f, tween(240, easing = FastOutSlowInEasing)) }
            // A single progress animation drives the staggered slices, avoiding
            // fourteen concurrently-running Animatables and excess recomposition.
            launch {
                wordmarkProgress.animateTo(7f, tween(685, easing = LinearEasing))
            }
        }

        // Phase 3: tagline rises gently into place.
        coroutineScope {
            launch { taglineAlpha.animateTo(1f, tween(320, easing = FastOutSlowInEasing)) }
            launch { taglineOffset.animateTo(0f, tween(320, easing = FastOutSlowInEasing)) }
        }

        // Phase 4 and 5: brief brand hold, then a seamless fade to the app.
        delay(460)
        overlayAlpha.animateTo(0f, tween(320, easing = FastOutSlowInEasing))
        onFinished()
    }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(LunareaWarmIvory)
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
                }
                Text(
                    text = "always your choice, always your furniture.",
                    modifier = Modifier.graphicsLayer {
                        alpha = taglineAlpha.value
                        translationY = taglineOffset.value * density
                    },
                    color = Color(0xFF6B716D),
                    style = MaterialTheme.typography.bodySmall,
                    fontSize = 11.sp,
                    letterSpacing = 0.65.sp,
                    textAlign = TextAlign.Center
                )
            }
        }
        Row(
            modifier = Modifier
                .align(Alignment.BottomCenter)
                .padding(bottom = 28.dp)
                .graphicsLayer { alpha = overlayAlpha.value },
            horizontalArrangement = Arrangement.spacedBy(5.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                text = "Part of",
                color = Color(0xFF6B716D),
                style = MaterialTheme.typography.labelSmall,
                fontSize = 9.sp,
                letterSpacing = 0.35.sp
            )
            Image(
                painter = painterResource(R.drawable.ilena_wordmark),
                contentDescription = "Ilena",
                modifier = Modifier.width(36.dp).height(11.dp),
                contentScale = ContentScale.Fit
            )
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
    Canvas(Modifier.fillMaxSize().aspectRatio(LogoAspectRatio)) {
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
