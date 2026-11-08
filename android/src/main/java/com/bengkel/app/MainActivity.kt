package com.bengkel.app

import android.annotation.SuppressLint
import android.app.AlertDialog
import android.content.ActivityNotFoundException
import android.content.Intent
import android.graphics.Color
import android.graphics.drawable.GradientDrawable
import android.graphics.Typeface
import android.net.Uri
import android.os.Bundle
import android.print.PrintAttributes
import android.print.PrintManager
import android.view.Gravity
import android.view.View
import android.view.ViewGroup
import android.webkit.ValueCallback
import android.webkit.WebChromeClient
import android.webkit.WebResourceRequest
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.Button
import android.widget.EditText
import android.widget.FrameLayout
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.ProgressBar
import android.widget.TextView
import android.widget.Toast
import androidx.activity.ComponentActivity
import androidx.activity.result.contract.ActivityResultContracts
import java.text.NumberFormat
import java.util.Locale
import org.json.JSONObject

private data class AppMenuItem(val title: String, val path: String)
private data class AppMenuGroup(val title: String, val items: List<AppMenuItem>)

internal fun rupiah(amount: Double): String =
    NumberFormat.getCurrencyInstance(Locale("id", "ID")).format(amount)

class MainActivity : ComponentActivity() {
    private lateinit var webView: WebView
    private lateinit var progressBar: ProgressBar
    private lateinit var splashView: View
    private lateinit var offlineView: View
    private lateinit var bottomNavigation: LinearLayout
    private var fileUploadCallback: ValueCallback<Array<Uri>>? = null
    private var role = "guest"
    private var authenticated = false
    private var syncingNavigation = false

    private val fileChooserLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        val results = if (result.resultCode == RESULT_OK) {
            val data = result.data
            when {
                data?.clipData != null -> Array(data.clipData!!.itemCount) { index ->
                    data.clipData!!.getItemAt(index).uri
                }
                data?.data != null -> arrayOf(data.data!!)
                else -> null
            }
        } else {
            null
        }
        fileUploadCallback?.onReceiveValue(results)
        fileUploadCallback = null
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        window.statusBarColor = Color.rgb(248, 248, 246)
        window.navigationBarColor = Color.rgb(248, 248, 246)
        window.decorView.systemUiVisibility = View.SYSTEM_UI_FLAG_LIGHT_STATUS_BAR or
            View.SYSTEM_UI_FLAG_LIGHT_NAVIGATION_BAR
        setContentView(createLayout())
        configureWebView()
        if (savedInstanceState == null || webView.restoreState(savedInstanceState) == null) {
            loadWebsite()
        } else {
            hideSplash()
        }
        renderNavigation()
    }

    private fun createLayout(): View {
        val root = FrameLayout(this).apply {
            setBackgroundColor(Color.rgb(248, 248, 246))
        }
        val page = LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL
        }
        val toolbar = LinearLayout(this).apply {
            orientation = LinearLayout.HORIZONTAL
            gravity = Gravity.CENTER_VERTICAL
            setPadding(dp(14), 0, dp(8), 0)
            setBackgroundColor(Color.WHITE)
        }
        val logo = ImageView(this).apply {
            setImageResource(R.drawable.rumah_daun_logo)
            scaleType = ImageView.ScaleType.FIT_CENTER
            contentDescription = "Logo Rumah Daun"
        }
        toolbar.addView(logo, LinearLayout.LayoutParams(dp(42), dp(42)))
        val title = TextView(this).apply {
            text = "UD Rumah Daun"
            textSize = 17f
            setTextColor(Color.rgb(34, 57, 45))
            setTypeface(typeface, Typeface.BOLD)
            setPadding(dp(10), 0, 0, 0)
        }
        toolbar.addView(title, LinearLayout.LayoutParams(0, dp(56), 1f))
        toolbar.addView(toolbarButton("⟳", "Muat ulang") { webView.reload() })
        toolbar.addView(toolbarButton("⚙", "Pengaturan server") { showServerSettings() })
        page.addView(toolbar, LinearLayout.LayoutParams(-1, dp(56)))

        val content = FrameLayout(this)
        webView = WebView(this)
        content.addView(webView, FrameLayout.LayoutParams(-1, -1))
        progressBar = ProgressBar(this, null, android.R.attr.progressBarStyleHorizontal).apply {
            isIndeterminate = false
            max = 100
            progressTintList = android.content.res.ColorStateList.valueOf(Color.rgb(53, 106, 73))
            progressBackgroundTintList = android.content.res.ColorStateList.valueOf(Color.rgb(223, 234, 225))
            visibility = View.GONE
        }
        content.addView(
            progressBar,
            FrameLayout.LayoutParams(-1, dp(4), Gravity.TOP)
        )
        offlineView = createOfflineView()
        content.addView(offlineView, FrameLayout.LayoutParams(-1, -1))
        page.addView(content, LinearLayout.LayoutParams(-1, 0, 1f))

        bottomNavigation = LinearLayout(this).apply {
            orientation = LinearLayout.HORIZONTAL
            gravity = Gravity.CENTER
            setPadding(dp(4), dp(5), dp(4), dp(5))
            setBackgroundColor(Color.WHITE)
            elevation = dp(8).toFloat()
        }
        page.addView(bottomNavigation, LinearLayout.LayoutParams(-1, dp(64)))
        root.addView(page, FrameLayout.LayoutParams(-1, -1))
        splashView = createSplashView()
        root.addView(splashView, FrameLayout.LayoutParams(-1, -1))
        return root
    }

    private fun toolbarButton(symbol: String, description: String, action: () -> Unit): View =
        TextView(this).apply {
            text = symbol
            textSize = 25f
            gravity = Gravity.CENTER
            contentDescription = description
            setTextColor(Color.rgb(53, 106, 73))
            setOnClickListener { action() }
            isFocusable = true
        }.also { it.layoutParams = LinearLayout.LayoutParams(dp(44), dp(56)) }

    private fun createSplashView(): View {
        val splash = LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL
            gravity = Gravity.CENTER
            setPadding(dp(36), dp(24), dp(36), dp(24))
            setBackgroundColor(Color.rgb(248, 248, 246))
        }
        val logo = ImageView(this).apply {
            setImageResource(R.drawable.rumah_daun_logo)
            scaleType = ImageView.ScaleType.FIT_CENTER
            contentDescription = "Logo Rumah Daun"
        }
        splash.addView(logo, LinearLayout.LayoutParams(dp(210), dp(150)))
        splash.addView(
            TextView(this).apply {
                text = "RUMAH DAUN"
                textSize = 23f
                gravity = Gravity.CENTER
                setTextColor(Color.rgb(21, 45, 67))
                setTypeface(typeface, Typeface.BOLD)
                setPadding(0, dp(12), 0, dp(4))
            },
            LinearLayout.LayoutParams(-1, -2)
        )
        splash.addView(
            TextView(this).apply {
                text = "LAYANAN & USAHA"
                textSize = 13f
                gravity = Gravity.CENTER
                setTextColor(Color.rgb(53, 106, 73))
            },
            LinearLayout.LayoutParams(-1, -2)
        )
        splash.addView(
            ProgressBar(this).apply {
                indeterminateTintList = android.content.res.ColorStateList.valueOf(Color.rgb(53, 106, 73))
            },
            LinearLayout.LayoutParams(dp(42), dp(42)).apply {
                gravity = Gravity.CENTER
                topMargin = dp(24)
            }
        )
        splash.addView(
            TextView(this).apply {
                text = "Memuat halaman UD Rumah Daun..."
                textSize = 14f
                gravity = Gravity.CENTER
                setTextColor(Color.DKGRAY)
                setPadding(0, dp(12), 0, 0)
            },
            LinearLayout.LayoutParams(-1, -2)
        )
        return splash
    }

    private fun createOfflineView(): View {
        val panel = LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL
            gravity = Gravity.CENTER
            setPadding(dp(30), dp(24), dp(30), dp(24))
            setBackgroundColor(Color.rgb(248, 248, 246))
            visibility = View.GONE
        }
        val logo = ImageView(this).apply {
            setImageResource(R.drawable.rumah_daun_logo)
            scaleType = ImageView.ScaleType.FIT_CENTER
            contentDescription = "Logo Rumah Daun"
        }
        panel.addView(logo, LinearLayout.LayoutParams(dp(130), dp(100)))
        panel.addView(
            TextView(this).apply {
                text = "Server belum dapat dihubungi"
                textSize = 20f
                gravity = Gravity.CENTER
                setTypeface(typeface, Typeface.BOLD)
                setTextColor(Color.rgb(34, 57, 45))
                setPadding(0, dp(14), 0, dp(8))
            },
            LinearLayout.LayoutParams(-1, -2)
        )
        panel.addView(
            TextView(this).apply {
                text = "Periksa koneksi HP dan alamat website. Pastikan server sedang berjalan."
                textSize = 15f
                gravity = Gravity.CENTER
                setTextColor(Color.DKGRAY)
            },
            LinearLayout.LayoutParams(-1, -2)
        )
        panel.addView(
            Button(this).apply {
                text = "Coba lagi"
                setOnClickListener { loadWebsite() }
            },
            LinearLayout.LayoutParams(-1, -2).apply { topMargin = dp(20) }
        )
        panel.addView(
            Button(this).apply {
                text = "Atur alamat website"
                setOnClickListener { showServerSettings() }
            },
            LinearLayout.LayoutParams(-1, -2)
        )
        return panel
    }

    @SuppressLint("SetJavaScriptEnabled")
    private fun configureWebView() {
        webView.settings.apply {
            javaScriptEnabled = true
            domStorageEnabled = true
            useWideViewPort = true
            loadWithOverviewMode = true
            allowFileAccess = false
            allowContentAccess = true
            mixedContentMode = WebSettings.MIXED_CONTENT_NEVER_ALLOW
            javaScriptCanOpenWindowsAutomatically = false
            setSupportMultipleWindows(false)
            if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
                safeBrowsingEnabled = true
            }
            userAgentString = "$userAgentString RumahDaunWebsiteApp/1.0"
        }
        webView.webViewClient = object : WebViewClient() {
            override fun shouldOverrideUrlLoading(view: WebView?, request: WebResourceRequest?): Boolean {
                if (request == null || !request.isForMainFrame) return false
                val target = request.url
                if (target.scheme == "bengkelprint" && target.host == "receipt") {
                    printReceipt()
                    return true
                }
                if (isTrustedServer(target)) return false
                if (target.scheme == "https" || target.scheme == "tel" || target.scheme == "mailto") {
                    runCatching { startActivity(Intent(Intent.ACTION_VIEW, target)) }
                        .onFailure {
                            Toast.makeText(
                                this@MainActivity,
                                "Tidak ada aplikasi untuk membuka tautan ini.",
                                Toast.LENGTH_LONG
                            ).show()
                        }
                }
                return true
            }

            override fun onPageStarted(view: WebView?, url: String?, favicon: android.graphics.Bitmap?) {
                offlineView.visibility = View.GONE
                progressBar.visibility = View.VISIBLE
            }

            override fun onPageFinished(view: WebView?, url: String?) {
                progressBar.visibility = View.GONE
                hideSplash()
                updateRoleFromPage(url)
            }

            override fun onReceivedError(
                view: WebView?,
                request: WebResourceRequest?,
                error: android.webkit.WebResourceError?
            ) {
                if (request?.isForMainFrame == true) {
                    progressBar.visibility = View.GONE
                    hideSplash()
                    webView.visibility = View.GONE
                    offlineView.visibility = View.VISIBLE
                }
            }
        }
        webView.webChromeClient = object : WebChromeClient() {
            override fun onProgressChanged(view: WebView?, newProgress: Int) {
                progressBar.progress = newProgress
            }

            override fun onShowFileChooser(
                webView: WebView?,
                filePathCallback: ValueCallback<Array<Uri>>?,
                fileChooserParams: FileChooserParams?
            ): Boolean {
                fileUploadCallback?.onReceiveValue(null)
                fileUploadCallback = filePathCallback
                return try {
                    val intent = fileChooserParams?.createIntent()
                        ?: Intent(Intent.ACTION_GET_CONTENT).apply { type = "*/*" }
                    fileChooserLauncher.launch(intent)
                    true
                } catch (exception: ActivityNotFoundException) {
                    fileUploadCallback = null
                    Toast.makeText(this@MainActivity, "Pemilih file tidak tersedia.", Toast.LENGTH_LONG)
                        .show()
                    false
                }
            }
        }
    }

    private fun hideSplash() {
        splashView.visibility = View.GONE
        webView.visibility = View.VISIBLE
    }

    private fun loadWebsite() {
        webView.visibility = View.VISIBLE
        offlineView.visibility = View.GONE
        splashView.visibility = View.VISIBLE
        webView.loadUrl(getServerUrl())
    }

    private fun getServerUrl(): String {
        val saved = getSharedPreferences(PREFS_NAME, MODE_PRIVATE)
            .getString(KEY_SERVER_URL, null)
            ?.trim()
            ?.trimEnd('/')
        return saved?.takeIf { it.isNotEmpty() } ?: BuildConfig.DEFAULT_WEBSITE_URL.trimEnd('/')
    }

    private fun showServerSettings() {
        val input = EditText(this).apply {
            setSingleLine(true)
            hint = "http://alamat-server/website-bengkel/public"
            setText(getServerUrl())
            setSelection(text.length)
        }
        val container = LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL
            setPadding(dp(24), dp(8), dp(24), 0)
            addView(input, LinearLayout.LayoutParams(-1, -2))
            addView(
                TextView(this@MainActivity).apply {
                    text = "Untuk HP yang tersambung ke PC, gunakan alamat IP PC pada jaringan yang sama."
                    textSize = 13f
                    setTextColor(Color.DKGRAY)
                    setPadding(0, dp(8), 0, 0)
                },
                LinearLayout.LayoutParams(-1, -2)
            )
        }
        AlertDialog.Builder(this)
            .setTitle("Alamat website UD Rumah Daun")
            .setView(container)
            .setPositiveButton("Simpan") { _, _ ->
                val entered = input.text.toString().trim().trimEnd('/')
                val uri = Uri.parse(entered)
                if (uri.host.isNullOrBlank() ||
                    uri.userInfo != null ||
                    uri.query != null ||
                    uri.fragment != null ||
                    (uri.scheme != "https" && !(BuildConfig.DEBUG && uri.scheme == "http"))
                ) {
                    Toast.makeText(
                        this,
                        "Masukkan alamat HTTPS yang valid. HTTP hanya diizinkan untuk APK debug.",
                        Toast.LENGTH_LONG
                    ).show()
                    return@setPositiveButton
                }
                getSharedPreferences(PREFS_NAME, MODE_PRIVATE)
                    .edit()
                    .putString(KEY_SERVER_URL, entered)
                    .apply()
                loadWebsite()
            }
            .setNeutralButton("Default") { _, _ ->
                getSharedPreferences(PREFS_NAME, MODE_PRIVATE).edit().remove(KEY_SERVER_URL).apply()
                loadWebsite()
            }
            .setNegativeButton("Batal", null)
            .show()
    }

    private fun isTrustedServer(url: Uri): Boolean {
        val server = Uri.parse(getServerUrl())
        return url.scheme.equals(server.scheme, ignoreCase = true) &&
            url.host.equals(server.host, ignoreCase = true) &&
            effectivePort(url) == effectivePort(server)
    }

    private fun effectivePort(uri: Uri): Int =
        if (uri.port != -1) uri.port else if (uri.scheme == "https") 443 else 80

    private fun updateRoleFromPage(pageUrl: String?) {
        webView.evaluateJavascript(
            "JSON.stringify({role:document.body.dataset.userRole||'',authenticated:document.body.dataset.authenticated==='true'})"
        ) { result ->
            val pageRole = runCatching {
                val decoded = JSONObject(result?.removeSurrounding("\"")?.replace("\\\"", "\"").orEmpty())
                decoded.optString("role")
            }.getOrDefault("")
            val pageAuthenticated = runCatching {
                val decoded = JSONObject(result?.removeSurrounding("\"")?.replace("\\\"", "\"").orEmpty())
                decoded.optBoolean("authenticated")
            }.getOrDefault(false)

            val pathRole = Uri.parse(pageUrl.orEmpty()).path.orEmpty().split('/').firstOrNull {
                it in setOf("admin", "kasir", "customer")
            }.orEmpty()
            role = pageRole.ifBlank { pathRole.ifBlank { "guest" } }
            authenticated = pageAuthenticated || role != "guest"
            renderNavigation(pageUrl)
        }
    }

    private fun renderNavigation(pageUrl: String? = webView.url) {
        if (!::bottomNavigation.isInitialized) return
        bottomNavigation.removeAllViews()
        val items = when {
            !authenticated || role == "guest" -> listOf(
                AppMenuItem("Beranda", ""),
                AppMenuItem("Layanan", "layanan"),
                AppMenuItem("Produk", "produk"),
                AppMenuItem("Tentang", "tentang"),
                AppMenuItem("Kontak", "kontak")
            )
            role == "admin" -> listOf(
                AppMenuItem("Dashboard", "admin/dashboard"),
                AppMenuItem("Booking", "admin/bookings"),
                AppMenuItem("Produk", "admin/products"),
                AppMenuItem("Menu", "")
            )
            role == "kasir" -> listOf(
                AppMenuItem("Dashboard", "kasir/dashboard"),
                AppMenuItem("Pesanan", "kasir/pesanan"),
                AppMenuItem("Kasir", "kasir/transaksi"),
                AppMenuItem("Menu", "")
            )
            else -> listOf(
                AppMenuItem("Dashboard", "customer/dashboard"),
                AppMenuItem("Produk", "customer/produk"),
                AppMenuItem("Keranjang", "keranjang"),
                AppMenuItem("Booking", "customer/booking"),
                AppMenuItem("Menu", "")
            )
        }
        items.forEachIndexed { index, item ->
            val selected = isSelected(pageUrl, item.path, item.title)
            val label = TextView(this).apply {
                text = item.title
                textSize = 11f
                gravity = Gravity.CENTER
                setPadding(dp(2), dp(7), dp(2), dp(6))
                setTextColor(
                    if (selected) Color.rgb(53, 106, 73) else Color.rgb(86, 86, 91)
                )
                if (selected) setTypeface(typeface, Typeface.BOLD)
                background = GradientDrawable().apply {
                    setColor(if (selected) Color.rgb(239, 232, 248) else Color.WHITE)
                    cornerRadius = dp(24).toFloat()
                }
                isFocusable = true
                contentDescription = item.title
                setOnClickListener {
                    if (item.title == "Menu") {
                        showRoleMenu()
                    } else if (!syncingNavigation) {
                        syncingNavigation = true
                        val destination = if (item.path.isBlank()) getServerUrl() else urlFor(item.path)
                        webView.loadUrl(destination)
                        syncingNavigation = false
                    }
                }
            }
            bottomNavigation.addView(
                label,
                LinearLayout.LayoutParams(0, -1, 1f).apply {
                    marginStart = dp(2)
                    marginEnd = dp(2)
                }
            )
        }
    }

    private fun isSelected(pageUrl: String?, path: String, title: String): Boolean {
        val currentPath = Uri.parse(pageUrl.orEmpty()).path.orEmpty().trimEnd('/')
        if (path.isEmpty()) return currentPath.isEmpty() || currentPath == Uri.parse(getServerUrl()).path.orEmpty().trimEnd('/')
        return currentPath.endsWith("/${path.trimStart('/')}") ||
            (title == "Produk" && currentPath == "/produk")
    }

    private fun showRoleMenu() {
        val groups = when (role) {
            "admin" -> listOf(
                AppMenuGroup(
                    "MASTER DATA",
                    listOf(
                        AppMenuItem("Kategori", "admin/categories"),
                        AppMenuItem("Layanan", "admin/services"),
                        AppMenuItem("Kendaraan", "admin/vehicles")
                    )
                ),
                AppMenuGroup(
                    "TRANSAKSI",
                    listOf(
                        AppMenuItem("Transaksi", "admin/transactions"),
                        AppMenuItem("Pembayaran", "admin/payments")
                    )
                ),
                AppMenuGroup(
                    "ADMINISTRASI",
                    listOf(
                        AppMenuItem("Pengguna", "admin/users"),
                        AppMenuItem("Laporan", "admin/reports"),
                        AppMenuItem("Notifikasi", "admin/notifications"),
                        AppMenuItem("Pesan kontak", "admin/contact-messages"),
                        AppMenuItem("Pengaturan usaha", "admin/settings/general"),
                        AppMenuItem("Profil & keamanan", "profile")
                    )
                )
            )
            "kasir" -> listOf(
                AppMenuGroup(
                    "OPERASIONAL",
                    listOf(
                        AppMenuItem("Penjualan produk", "kasir/transaksi"),
                        AppMenuItem("Pembayaran", "kasir/pembayaran"),
                        AppMenuItem("Invoice", "kasir/invoice")
                    )
                ),
                AppMenuGroup(
                    "AKUN",
                    listOf(AppMenuItem("Pengaturan profil", "profile"))
                )
            )
            else -> listOf(
                AppMenuGroup(
                    "AKUN SAYA",
                    listOf(
                        AppMenuItem("Kendaraan saya", "customer/kendaraan"),
                        AppMenuItem("Transaksi", "customer/transaksi"),
                        AppMenuItem("Notifikasi", "customer/notifikasi"),
                        AppMenuItem("Pengaturan profil", "profile")
                    )
                )
            )
        }
        val content = LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL
            setPadding(dp(20), dp(8), dp(20), dp(8))
        }
        var menuDialog: AlertDialog? = null
        groups.forEach { group ->
            content.addView(
                TextView(this).apply {
                    text = group.title
                    textSize = 12f
                    setTextColor(Color.rgb(53, 106, 73))
                    setTypeface(typeface, Typeface.BOLD)
                    setPadding(0, dp(12), 0, dp(4))
                },
                LinearLayout.LayoutParams(-1, -2)
            )
            group.items.forEach { item ->
                content.addView(
                    TextView(this).apply {
                        text = item.title
                        textSize = 16f
                        setTextColor(Color.rgb(38, 45, 40))
                        gravity = Gravity.CENTER_VERTICAL
                        setPadding(dp(8), dp(10), dp(8), dp(10))
                        setOnClickListener {
                            webView.loadUrl(urlFor(item.path))
                            menuDialog?.dismiss()
                        }
                    },
                    LinearLayout.LayoutParams(-1, -2)
                )
            }
        }
        val scrollView = android.widget.ScrollView(this).apply { addView(content) }
        menuDialog = AlertDialog.Builder(this)
            .setTitle(when (role) {
                "admin" -> "Menu Admin"
                "kasir" -> "Menu Kasir"
                else -> "Menu Pelanggan"
            })
            .setView(scrollView)
            .setNegativeButton("Tutup", null)
            .create()
        menuDialog?.show()
    }

    private fun urlFor(path: String): String =
        "${getServerUrl().trimEnd('/')}/${path.trimStart('/')}"

    private fun printReceipt() {
        val manager = getSystemService(PRINT_SERVICE) as? PrintManager
        if (manager == null) {
            Toast.makeText(this, "Layanan cetak Android tidak tersedia.", Toast.LENGTH_LONG).show()
            return
        }
        val jobName = "Nota-Rumah-Daun"
        val attributes = PrintAttributes.Builder()
            .setMediaSize(PrintAttributes.MediaSize.UNKNOWN_PORTRAIT)
            .setColorMode(PrintAttributes.COLOR_MODE_MONOCHROME)
            .build()
        manager.print(jobName, webView.createPrintDocumentAdapter(jobName), attributes)
    }

    override fun onSaveInstanceState(outState: Bundle) {
        webView.saveState(outState)
        super.onSaveInstanceState(outState)
    }

    @Deprecated("Deprecated in Java")
    override fun onBackPressed() {
        if (::webView.isInitialized && webView.canGoBack()) webView.goBack()
        else super.onBackPressed()
    }

    override fun onDestroy() {
        fileUploadCallback?.onReceiveValue(null)
        fileUploadCallback = null
        if (::webView.isInitialized) {
            webView.stopLoading()
            webView.destroy()
        }
        super.onDestroy()
    }

    private fun dp(value: Int): Int = (value * resources.displayMetrics.density).toInt()

    companion object {
        private const val PREFS_NAME = "website_bengkel_settings"
        private const val KEY_SERVER_URL = "website_url"
    }
}
