package com.lunareafurniture.lunarea.data.security

import android.content.Context
import android.security.keystore.KeyGenParameterSpec
import android.security.keystore.KeyProperties
import android.util.Base64
import java.security.KeyStore
import javax.crypto.Cipher
import javax.crypto.KeyGenerator
import javax.crypto.SecretKey
import javax.crypto.spec.GCMParameterSpec

/** Stores only encrypted token bytes in app-private SharedPreferences. */
class SecureTokenStore(context: Context) {
    private val preferences = context.applicationContext.getSharedPreferences("lunarea_secure", Context.MODE_PRIVATE)
    private val keyAlias = "lunarea_api_token_key_v1"

    @Synchronized
    fun save(accessToken: String, refreshToken: String) {
        val payload = "$accessToken\n$refreshToken".toByteArray(Charsets.UTF_8)
        val cipher = Cipher.getInstance(TRANSFORMATION).apply {
            init(Cipher.ENCRYPT_MODE, getOrCreateKey())
        }
        val encrypted = cipher.doFinal(payload)
        val encoded = Base64.encodeToString(cipher.iv + encrypted, Base64.NO_WRAP)
        check(preferences.edit().putString(TOKEN_ENTRY, encoded).commit()) { "Token tidak berhasil disimpan" }
    }

    @Synchronized
    fun read(): StoredTokens? {
        val encoded = preferences.getString(TOKEN_ENTRY, null) ?: return null
        return try {
            val bytes = Base64.decode(encoded, Base64.NO_WRAP)
            require(bytes.size > IV_LENGTH)
            val iv = bytes.copyOfRange(0, IV_LENGTH)
            val encrypted = bytes.copyOfRange(IV_LENGTH, bytes.size)
            val cipher = Cipher.getInstance(TRANSFORMATION).apply {
                init(Cipher.DECRYPT_MODE, getOrCreateKey(), GCMParameterSpec(TAG_LENGTH_BITS, iv))
            }
            val values = cipher.doFinal(encrypted).toString(Charsets.UTF_8).split('\n', limit = 2)
            if (values.size != 2 || values.any(String::isBlank)) null else StoredTokens(values[0], values[1])
        } catch (_: Exception) {
            clear()
            null
        }
    }

    @Synchronized
    fun clear() {
        preferences.edit().remove(TOKEN_ENTRY).commit()
    }

    @Synchronized
    fun savePushToken(token: String) {
        val cipher = Cipher.getInstance(TRANSFORMATION).apply { init(Cipher.ENCRYPT_MODE, getOrCreateKey()) }
        val encoded = Base64.encodeToString(cipher.iv + cipher.doFinal(token.toByteArray(Charsets.UTF_8)), Base64.NO_WRAP)
        check(preferences.edit().putString(PUSH_TOKEN_ENTRY, encoded).commit()) { "Token notifikasi tidak berhasil disimpan" }
    }

    @Synchronized
    fun readPushToken(): String? {
        val encoded = preferences.getString(PUSH_TOKEN_ENTRY, null) ?: return null
        return try {
            val bytes = Base64.decode(encoded, Base64.NO_WRAP)
            require(bytes.size > IV_LENGTH)
            val cipher = Cipher.getInstance(TRANSFORMATION).apply {
                init(Cipher.DECRYPT_MODE, getOrCreateKey(), GCMParameterSpec(TAG_LENGTH_BITS, bytes.copyOfRange(0, IV_LENGTH)))
            }
            cipher.doFinal(bytes.copyOfRange(IV_LENGTH, bytes.size)).toString(Charsets.UTF_8).takeIf(String::isNotBlank)
        } catch (_: Exception) {
            preferences.edit().remove(PUSH_TOKEN_ENTRY).commit()
            null
        }
    }

    @Synchronized
    fun clearPushToken() {
        preferences.edit().remove(PUSH_TOKEN_ENTRY).commit()
    }

    private fun getOrCreateKey(): SecretKey {
        val keyStore = KeyStore.getInstance(ANDROID_KEY_STORE).apply { load(null) }
        (keyStore.getKey(keyAlias, null) as? SecretKey)?.let { return it }
        val generator = KeyGenerator.getInstance(KeyProperties.KEY_ALGORITHM_AES, ANDROID_KEY_STORE)
        generator.init(
            KeyGenParameterSpec.Builder(keyAlias, KeyProperties.PURPOSE_ENCRYPT or KeyProperties.PURPOSE_DECRYPT)
                .setBlockModes(KeyProperties.BLOCK_MODE_GCM)
                .setEncryptionPaddings(KeyProperties.ENCRYPTION_PADDING_NONE)
                .setRandomizedEncryptionRequired(true)
                .build()
        )
        return generator.generateKey()
    }

    data class StoredTokens(val accessToken: String, val refreshToken: String)

    private companion object {
        const val ANDROID_KEY_STORE = "AndroidKeyStore"
        const val TRANSFORMATION = "AES/GCM/NoPadding"
        const val TOKEN_ENTRY = "customer_tokens_v1"
        const val PUSH_TOKEN_ENTRY = "push_token_v1"
        const val IV_LENGTH = 12
        const val TAG_LENGTH_BITS = 128
    }
}
