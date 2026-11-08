package com.bengkel.app.data.local

import android.content.Context
import android.content.SharedPreferences
import android.net.Uri
import com.bengkel.app.BuildConfig
import com.bengkel.app.data.local.entity.UserEntity
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow

class SessionManager(context: Context) {

    private val prefs: SharedPreferences =
        context.getSharedPreferences("bengkel_session_prefs", Context.MODE_PRIVATE)

    companion object {
        private const val KEY_TOKEN = "auth_token"
        private const val KEY_USER_ID = "user_id"
        private const val KEY_USER_NAME = "user_name"
        private const val KEY_USER_EMAIL = "user_email"
        private const val KEY_USER_USERNAME = "user_username"
        private const val KEY_USER_ROLE = "user_role"
        private const val KEY_USER_PHONE = "user_phone"
        private const val KEY_BASE_URL = "base_url"
        val DEFAULT_BASE_URL = BuildConfig.DEFAULT_WEBSITE_URL
    }

    private val _currentUserFlow = MutableStateFlow(getCurrentUser())
    val currentUserFlow: StateFlow<UserEntity?> = _currentUserFlow.asStateFlow()

    fun saveAuthToken(token: String) {
        prefs.edit().putString(KEY_TOKEN, token).apply()
    }

    fun getAuthToken(): String? {
        return prefs.getString(KEY_TOKEN, null)
    }

    fun saveUser(user: UserEntity) {
        prefs.edit().apply {
            putLong(KEY_USER_ID, user.id)
            putString(KEY_USER_NAME, user.name)
            putString(KEY_USER_EMAIL, user.email)
            putString(KEY_USER_USERNAME, user.username)
            putString(KEY_USER_ROLE, user.role)
            putString(KEY_USER_PHONE, user.noHp)
            apply()
        }
        _currentUserFlow.value = user
    }

    fun getCurrentUser(): UserEntity? {
        val id = prefs.getLong(KEY_USER_ID, -1L)
        if (id == -1L) return null
        val name = prefs.getString(KEY_USER_NAME, "") ?: ""
        val email = prefs.getString(KEY_USER_EMAIL, "") ?: ""
        val username = prefs.getString(KEY_USER_USERNAME, "") ?: ""
        val role = prefs.getString(KEY_USER_ROLE, "customer") ?: "customer"
        val phone = prefs.getString(KEY_USER_PHONE, null)

        return UserEntity(
            id = id,
            name = name,
            email = email,
            username = username,
            role = role,
            noHp = phone,
            isSynced = true
        )
    }

    fun getBaseUrl(): String {
        return prefs.getString(KEY_BASE_URL, DEFAULT_BASE_URL) ?: DEFAULT_BASE_URL
    }

    fun setBaseUrl(url: String) {
        var formatted = url.trim()
        val parsed = Uri.parse(formatted)
        val isHttps = parsed.scheme.equals("https", ignoreCase = true)
        val isDemoHttp = BuildConfig.DEBUG && parsed.scheme.equals("http", ignoreCase = true)
        require((isHttps || isDemoHttp) && !parsed.host.isNullOrBlank()) {
            "Gunakan alamat API HTTPS, atau HTTP hanya untuk APK debug di jaringan demo lokal."
        }

        if (!formatted.endsWith("/")) {
            formatted += "/"
        }
        if (!formatted.contains("/api/")) {
            formatted = if (formatted.endsWith("/")) "${formatted}api/" else "$formatted/api/"
        }
        prefs.edit().putString(KEY_BASE_URL, formatted).apply()
    }

    fun isLoggedIn(): Boolean {
        return getCurrentUser() != null
    }

    fun clearSession() {
        prefs.edit().apply {
            remove(KEY_TOKEN)
            remove(KEY_USER_ID)
            remove(KEY_USER_NAME)
            remove(KEY_USER_EMAIL)
            remove(KEY_USER_USERNAME)
            remove(KEY_USER_ROLE)
            remove(KEY_USER_PHONE)
            apply()
        }
        _currentUserFlow.value = null
    }
}
