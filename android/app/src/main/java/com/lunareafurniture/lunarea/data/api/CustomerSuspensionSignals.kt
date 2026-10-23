package com.lunareafurniture.lunarea.data.api

import kotlinx.coroutines.flow.MutableSharedFlow
import kotlinx.coroutines.flow.asSharedFlow

/** One-shot signal used to return the UI to signed-out state after a server suspension. */
object CustomerSuspensionSignals {
    private val mutableSuspended = MutableSharedFlow<String>(extraBufferCapacity = 1)
    val suspended = mutableSuspended.asSharedFlow()
    fun notifySuspended(message: String) { mutableSuspended.tryEmit(message) }
}
