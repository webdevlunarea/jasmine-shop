package com.lunareafurniture.lunarea.ui.theme

import androidx.compose.ui.graphics.Color
import androidx.compose.ui.unit.dp
import androidx.compose.material3.MaterialTheme
import androidx.compose.runtime.Composable

val LunareaGreen = Color(0xFF365B46)
val LunareaGreenHover = Color(0xFF294936)
val LunareaDarkGreen = Color(0xFF80AB8E)
val LunareaDarkGreenHover = Color(0xFFA0C2A9)
// Adaptive foreground token used by existing Compose controls and labels.
val LunareaGreenText: Color
    @Composable get() = MaterialTheme.colorScheme.primary
val LunareaLightGreenText = Color(0xFF027A08)
val LunareaBackground = Color(0xFFFAF9F6)
val LunareaSoftOne = Color(0xFFF5F3EE)
val LunareaSoftTwo = Color(0xFFEBE8DF)
val LunareaAccent = Color(0xFFA65A50)
val LunareaWhite = Color(0xFFFEFEFF)
val LunareaInk = Color(0xFF262B27)
val LunareaTextSecondary = Color(0xFF626863)

val LunareaDarkBackground = Color(0xFF121212)
val LunareaDarkSoftOne = Color(0xFF1E1E1E)
val LunareaDarkSoftTwo = Color(0xFF242424)
val LunareaDarkSurface = Color(0xFF1B1B1B)
val LunareaDarkOnSurface = Color(0xFFF4F4F4)
val LunareaDarkTextSecondary = Color(0xFFB0B0B0)
val LunareaDarkOnPrimary = Color(0xFF17251B)
val LunareaDarkSecondary = Color(0xFFC58E82)
val LunareaDarkOutline = Color(0xFF333333)
val LunareaDarkError = Color(0xFFE59A92)

object LunareaSpacing {
    val xxs = 4.dp
    val xs = 8.dp
    val sm = 12.dp
    val md = 16.dp
    val lg = 24.dp
    val xl = 32.dp
}
