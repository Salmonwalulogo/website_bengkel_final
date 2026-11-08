package com.bengkel.app.data.remote.api

import com.bengkel.app.BuildConfig
import com.bengkel.app.data.local.SessionManager
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

class RetrofitClient(private val sessionManager: SessionManager) {

    private var currentBaseUrl: String = sessionManager.getBaseUrl()
    private var apiServiceInstance: ApiService? = null

    @Synchronized
    fun getApiService(): ApiService {
        val configuredUrl = sessionManager.getBaseUrl()
        if (apiServiceInstance == null || currentBaseUrl != configuredUrl) {
            currentBaseUrl = configuredUrl
            apiServiceInstance = createApiService(configuredUrl)
        }
        return apiServiceInstance!!
    }

    private fun createApiService(baseUrl: String): ApiService {
        val isHttps = baseUrl.startsWith("https://", ignoreCase = true)
        val isDebugHttp = BuildConfig.DEBUG && baseUrl.startsWith("http://", ignoreCase = true)
        require(isHttps || isDebugHttp) {
            "Alamat API wajib HTTPS. HTTP hanya diizinkan pada APK debug."
        }

        val okHttpClient = OkHttpClient.Builder()
            .addInterceptor(HttpLoggingInterceptor().apply {
                level = HttpLoggingInterceptor.Level.NONE
            })
            .addInterceptor { chain ->
                val requestBuilder = chain.request().newBuilder()
                    .header("Accept", "application/json")

                sessionManager.getAuthToken()
                    ?.takeIf { it.isNotBlank() }
                    ?.let { token ->
                        requestBuilder.header("Authorization", "Bearer " + token)
                    }

                chain.proceed(requestBuilder.build())
            }
            .connectTimeout(15, TimeUnit.SECONDS)
            .readTimeout(15, TimeUnit.SECONDS)
            .writeTimeout(15, TimeUnit.SECONDS)
            .build()

        return Retrofit.Builder()
            .baseUrl(baseUrl)
            .client(okHttpClient)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }
}
