package com.lunareafurniture.lunarea.ui.theme

import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Shapes
import androidx.compose.material3.Typography
import androidx.compose.material3.darkColorScheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.runtime.remember
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalConfiguration
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.font.FontFamily
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.sp
import androidx.compose.ui.unit.TextUnit
import androidx.compose.ui.unit.dp
import androidx.compose.foundation.shape.RoundedCornerShape

private val LunareaColors = lightColorScheme(
    primary = LunareaGreen,
    onPrimary = LunareaWhite,
    primaryContainer = LunareaSoftOne,
    onPrimaryContainer = LunareaInk,
    secondary = LunareaAccent,
    onSecondary = LunareaWhite,
    secondaryContainer = LunareaSoftTwo,
    onSecondaryContainer = LunareaInk,
    tertiary = LunareaGreen,
    onTertiary = LunareaWhite,
    background = LunareaBackground,
    onBackground = LunareaInk,
    surface = LunareaWhite,
    onSurface = LunareaInk,
    surfaceVariant = LunareaSoftOne,
    onSurfaceVariant = LunareaTextSecondary,
    outline = LunareaSoftTwo,
    outlineVariant = LunareaSoftTwo.copy(alpha = 0.72f)
)

private val LunareaDarkColors = darkColorScheme(
    primary = LunareaDarkGreen,
    onPrimary = LunareaDarkOnPrimary,
    primaryContainer = LunareaDarkSoftOne,
    onPrimaryContainer = LunareaDarkOnSurface,
    secondary = LunareaDarkSecondary,
    onSecondary = LunareaDarkOnPrimary,
    secondaryContainer = LunareaDarkSoftTwo,
    onSecondaryContainer = LunareaDarkOnSurface,
    tertiary = LunareaDarkGreen,
    onTertiary = LunareaDarkOnPrimary,
    background = LunareaDarkBackground,
    onBackground = LunareaDarkOnSurface,
    surface = LunareaDarkSurface,
    onSurface = LunareaDarkOnSurface,
    surfaceVariant = LunareaDarkSoftOne,
    onSurfaceVariant = LunareaDarkTextSecondary,
    outline = LunareaDarkOutline,
    outlineVariant = LunareaDarkOutline,
    error = LunareaDarkError,
    onError = LunareaDarkOnPrimary,
    errorContainer = Color(0xFF5A2F2B),
    onErrorContainer = Color(0xFFFFDAD5)
)

private val LunareaTypography = Typography(
    displayLarge = TextStyle(fontFamily = FontFamily.SansSerif, fontWeight = FontWeight.Normal, fontSize = 34.sp, lineHeight = 42.sp, letterSpacing = (-0.8).sp),
    headlineLarge = TextStyle(fontFamily = FontFamily.SansSerif, fontWeight = FontWeight.Medium, fontSize = 28.sp, lineHeight = 36.sp, letterSpacing = (-0.5).sp),
    headlineMedium = TextStyle(fontFamily = FontFamily.SansSerif, fontWeight = FontWeight.Medium, fontSize = 23.sp, lineHeight = 30.sp, letterSpacing = (-0.45).sp),
    headlineSmall = TextStyle(fontFamily = FontFamily.SansSerif, fontWeight = FontWeight.Medium, fontSize = 20.sp, lineHeight = 27.sp, letterSpacing = (-0.25).sp),
    titleLarge = TextStyle(fontFamily = FontFamily.SansSerif, fontWeight = FontWeight.Medium, fontSize = 18.sp, lineHeight = 25.sp, letterSpacing = (-0.2).sp),
    titleMedium = TextStyle(fontFamily = FontFamily.SansSerif, fontWeight = FontWeight.Medium, fontSize = 15.sp, lineHeight = 22.sp, letterSpacing = 0.sp),
    titleSmall = TextStyle(fontFamily = FontFamily.SansSerif, fontWeight = FontWeight.Medium, fontSize = 13.sp, lineHeight = 19.sp, letterSpacing = 0.sp),
    bodyLarge = TextStyle(fontFamily = FontFamily.SansSerif, fontWeight = FontWeight.Normal, fontSize = 15.sp, lineHeight = 23.sp, letterSpacing = 0.sp),
    bodyMedium = TextStyle(fontFamily = FontFamily.SansSerif, fontWeight = FontWeight.Normal, fontSize = 14.sp, lineHeight = 21.sp, letterSpacing = 0.sp),
    bodySmall = TextStyle(fontFamily = FontFamily.SansSerif, fontWeight = FontWeight.Normal, fontSize = 13.sp, lineHeight = 19.sp, letterSpacing = 0.sp),
    labelLarge = TextStyle(fontFamily = FontFamily.SansSerif, fontWeight = FontWeight.Medium, fontSize = 13.sp, lineHeight = 18.sp, letterSpacing = 0.1.sp),
    labelMedium = TextStyle(fontFamily = FontFamily.SansSerif, fontWeight = FontWeight.Medium, fontSize = 12.sp, lineHeight = 17.sp, letterSpacing = 0.1.sp),
    labelSmall = TextStyle(fontFamily = FontFamily.SansSerif, fontWeight = FontWeight.Medium, fontSize = 11.sp, lineHeight = 15.sp, letterSpacing = 0.1.sp)
)

private val LunareaShapes = Shapes(
    extraSmall = RoundedCornerShape(8.dp),
    small = RoundedCornerShape(12.dp),
    medium = RoundedCornerShape(16.dp),
    large = RoundedCornerShape(20.dp),
    extraLarge = RoundedCornerShape(24.dp)
)

@Composable
fun LunareaTheme(content: @Composable () -> Unit) {
    val isDarkTheme = androidx.compose.foundation.isSystemInDarkTheme()
    val screenWidth = LocalConfiguration.current.screenWidthDp
    val fontScale = responsiveFontScale(screenWidth)
    val typography = remember(fontScale) { scaleTypography(LunareaTypography, fontScale) }
    MaterialTheme(
        colorScheme = if (isDarkTheme) LunareaDarkColors else LunareaColors,
        typography = typography,
        shapes = LunareaShapes,
        content = content
    )
}

/** Scale presentation typography by available width while retaining system font scaling. */
@Composable
fun lunareaResponsiveFontSize(size: TextUnit): TextUnit = size * responsiveFontScale(LocalConfiguration.current.screenWidthDp)

private fun responsiveFontScale(widthDp: Int): Float = when {
    widthDp < 360 -> 0.90f
    widthDp < 400 -> 0.93f
    widthDp < 600 -> 0.96f
    widthDp < 840 -> 1f
    else -> 1.04f
}

private fun scaleTypography(base: Typography, scale: Float): Typography {
    fun scale(style: TextStyle): TextStyle = if (style.fontSize.value >= 13f) {
        style.copy(
            fontSize = style.fontSize * scale,
            lineHeight = style.lineHeight * scale,
            letterSpacing = style.letterSpacing * scale
        )
    } else style

    return Typography(
        displayLarge = scale(base.displayLarge),
        displayMedium = scale(base.displayMedium),
        displaySmall = scale(base.displaySmall),
        headlineLarge = scale(base.headlineLarge),
        headlineMedium = scale(base.headlineMedium),
        headlineSmall = scale(base.headlineSmall),
        titleLarge = scale(base.titleLarge),
        titleMedium = scale(base.titleMedium),
        titleSmall = scale(base.titleSmall),
        bodyLarge = scale(base.bodyLarge),
        bodyMedium = scale(base.bodyMedium),
        bodySmall = scale(base.bodySmall),
        labelLarge = scale(base.labelLarge),
        labelMedium = scale(base.labelMedium),
        labelSmall = scale(base.labelSmall)
    )
}
