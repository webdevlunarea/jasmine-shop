package com.lunareafurniture.lunarea.data.api

import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow

/** Retained until consumed so suspension notices are not lost during UI transitions. */
object CustomerSuspensionSignals {
    private val mutableSuspended = MutableStateFlow<String?>(null)
    val suspended = mutableSuspended.asStateFlow()

    fun notifySuspended(message: String) {
        mutableSuspended.value = message
    }

    fun consumeSuspended(message: String) {
        if (mutableSuspended.value == message) mutableSuspended.value = null
    }
}
