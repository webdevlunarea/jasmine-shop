package com.lunareafurniture.lunarea.ui.theme

import android.graphics.Bitmap
import java.util.ArrayDeque

/** Preserves the source mark and transparency while turning dark wordmark lettering white. */
internal fun recolorDarkWordmark(source: Bitmap, preserveMarkInterior: Boolean = false): Bitmap {
    val output = source.copy(Bitmap.Config.ARGB_8888, true) ?: return source
    val pixels = IntArray(output.width * output.height)
    output.getPixels(pixels, 0, output.width, 0, 0, output.width, output.height)
    var minRedX = output.width
    var minRedY = output.height
    var maxRedX = -1
    var maxRedY = -1

    for (y in 0 until output.height) {
        for (x in 0 until output.width) {
            val pixel = pixels[y * output.width + x]
            val red = android.graphics.Color.red(pixel)
            val green = android.graphics.Color.green(pixel)
            val blue = android.graphics.Color.blue(pixel)
            if (android.graphics.Color.alpha(pixel) > 0 && red > 150 && green < 115 && blue < 125 && red - green > 55) {
                minRedX = minOf(minRedX, x)
                minRedY = minOf(minRedY, y)
                maxRedX = maxOf(maxRedX, x)
                maxRedY = maxOf(maxRedY, y)
            }
        }
    }

    // The Lunarea N in the source artwork is a transparent knockout inside the
    // red circle. On dark surfaces that knockout exposes the dark background,
    // so fill only transparent regions enclosed by the red mark with white.
    // Exterior transparency is flood-filled from the bitmap edges and remains
    // untouched; the Ilena mark keeps its black N through preserveMarkInterior.
    val hasRedMark = maxRedX >= minRedX && maxRedY >= minRedY
    if (!preserveMarkInterior && hasRedMark) {
        val exteriorTransparent = BooleanArray(pixels.size)
        val queue = ArrayDeque<Int>()
        fun enqueueExterior(index: Int) {
            if (!exteriorTransparent[index] && android.graphics.Color.alpha(pixels[index]) == 0) {
                exteriorTransparent[index] = true
                queue.addLast(index)
            }
        }
        for (x in 0 until output.width) {
            enqueueExterior(x)
            enqueueExterior((output.height - 1) * output.width + x)
        }
        for (y in 0 until output.height) {
            enqueueExterior(y * output.width)
            enqueueExterior(y * output.width + output.width - 1)
        }
        while (queue.isNotEmpty()) {
            val index = queue.removeFirst()
            val x = index % output.width
            val y = index / output.width
            if (x > 0) enqueueExterior(index - 1)
            if (x + 1 < output.width) enqueueExterior(index + 1)
            if (y > 0) enqueueExterior(index - output.width)
            if (y + 1 < output.height) enqueueExterior(index + output.width)
        }
        for (y in minRedY..maxRedY) {
            for (x in minRedX..maxRedX) {
                val index = y * output.width + x
                if (android.graphics.Color.alpha(pixels[index]) == 0 && !exteriorTransparent[index]) {
                    pixels[index] = android.graphics.Color.WHITE
                }
            }
        }
    }

    for (y in 0 until output.height) {
        for (x in 0 until output.width) {
            val index = y * output.width + x
            val pixel = pixels[index]
            val alpha = android.graphics.Color.alpha(pixel)
            val red = android.graphics.Color.red(pixel)
            val green = android.graphics.Color.green(pixel)
            val blue = android.graphics.Color.blue(pixel)
            val insideRedMark = maxRedX >= minRedX && x in minRedX..maxRedX && y in minRedY..maxRedY
            if (alpha > 0 && maxOf(red, green, blue) < 150 && !(preserveMarkInterior && insideRedMark)) {
                pixels[index] = android.graphics.Color.argb(alpha, 255, 255, 255)
            }
        }
    }
    output.setPixels(pixels, 0, output.width, 0, 0, output.width, output.height)
    return output
}
