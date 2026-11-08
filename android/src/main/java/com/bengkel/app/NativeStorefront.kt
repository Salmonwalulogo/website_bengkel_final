package com.bengkel.app

import android.content.Intent
import android.content.ActivityNotFoundException
import android.net.Uri
import android.widget.Toast
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.Image
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Build
import androidx.compose.material.icons.filled.Call
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.Info
import androidx.compose.material.icons.filled.Inventory2
import androidx.compose.material.icons.filled.LocationOn
import androidx.compose.material.icons.filled.Mail
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material.icons.filled.ShoppingCart
import androidx.compose.material.icons.filled.Star
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.NavigationBar
import androidx.compose.material3.NavigationBarItem
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import com.bengkel.app.data.BengkelRepository
import com.bengkel.app.data.local.entity.ServiceEntity

private data class StorefrontTab(
    val title: String,
    val icon: androidx.compose.ui.graphics.vector.ImageVector
)

private data class ServicePreview(val name: String, val description: String)

@OptIn(ExperimentalMaterial3Api::class)
@Composable
internal fun NativeStorefront(
    repository: BengkelRepository,
    isSignedIn: Boolean,
    onLogin: () -> Unit
) {
    val tabs = listOf(
        StorefrontTab("Beranda", Icons.Default.Home),
        StorefrontTab("Layanan", Icons.Default.Build),
        StorefrontTab("Produk", Icons.Default.Inventory2),
        StorefrontTab("Tentang", Icons.Default.Info),
        StorefrontTab("Kontak", Icons.Default.Call)
    )
    var selectedTab by remember { mutableIntStateOf(0) }
    val products by repository.products().collectAsState(initial = emptyList())
    val services by repository.services().collectAsState(initial = emptyList())
    val scope = rememberCoroutineScope()
    var refreshing by remember { mutableStateOf(false) }
    var loadError by remember { mutableStateOf<String?>(null) }
    var refreshKey by remember { mutableIntStateOf(0) }

    LaunchedEffect(refreshKey) {
        refreshing = true
        loadError = null
        try {
            repository.refreshPublicProducts()
        } catch (exception: Exception) {
            loadError = exception.message ?: "Produk terbaru belum dapat dimuat."
        } finally {
            refreshing = false
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Row(
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.spacedBy(10.dp)
                    ) {
                        Image(
                            painter = painterResource(R.drawable.rumah_daun_logo),
                            contentDescription = "Logo Rumah Daun Bengkel Otomotif",
                            modifier = Modifier.size(width = 56.dp, height = 42.dp),
                            contentScale = ContentScale.Fit
                        )
                        Column {
                            Text("RUMAH DAUN", fontWeight = FontWeight.Bold)
                            Text("BENGKEL OTOMOTIF", style = MaterialTheme.typography.labelSmall)
                        }
                    }
                },
                actions = {
                    IconButton(
                        onClick = {
                            if (!refreshing) refreshKey++
                        }
                    ) {
                        Icon(Icons.Default.Refresh, contentDescription = "Muat ulang produk")
                    }
                    TextButton(onClick = onLogin) {
                        Text(if (isSignedIn) "Dashboard" else "Masuk")
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = Color(0xFF356A49),
                    titleContentColor = Color.White,
                    actionIconContentColor = Color.White
                )
            )
        },
        bottomBar = {
            NavigationBar {
                tabs.forEachIndexed { index, tab ->
                    NavigationBarItem(
                        selected = selectedTab == index,
                        onClick = { selectedTab = index },
                        icon = { Icon(tab.icon, contentDescription = tab.title) },
                        label = { Text(tab.title) }
                    )
                }
            }
        }
    ) { innerPadding ->
        when (selectedTab) {
            0 -> HomeContent(
                modifier = Modifier.padding(innerPadding),
                products = products.take(4),
                services = services,
                loadError = loadError,
                refreshing = refreshing,
                onRefresh = { refreshKey++ },
                onBrowseProducts = { selectedTab = 2 },
                onBrowseServices = { selectedTab = 1 },
                onLogin = onLogin
            )
            1 -> ServicesContent(
                modifier = Modifier.padding(innerPadding),
                services = services,
                onLogin = onLogin
            )
            2 -> ProductsContent(
                modifier = Modifier.padding(innerPadding),
                products = products,
                loadError = loadError,
                refreshing = refreshing,
                onRefresh = { refreshKey++ },
                onLogin = onLogin
            )
            3 -> AboutContent(Modifier.padding(innerPadding))
            else -> ContactContent(Modifier.padding(innerPadding))
        }
    }
}

@Composable
private fun HomeContent(
    modifier: Modifier,
    products: List<com.bengkel.app.data.local.entity.ProductEntity>,
    services: List<ServiceEntity>,
    loadError: String?,
    refreshing: Boolean,
    onRefresh: () -> Unit,
    onBrowseProducts: () -> Unit,
    onBrowseServices: () -> Unit,
    onLogin: () -> Unit
) {
    LazyColumn(
        modifier = modifier.fillMaxSize(),
        contentPadding = PaddingValues(20.dp),
        verticalArrangement = Arrangement.spacedBy(16.dp)
    ) {
        item {
            Card(
                colors = CardDefaults.cardColors(containerColor = Color(0xFF356A49)),
                shape = RoundedCornerShape(24.dp)
            ) {
                Column(
                    Modifier.padding(24.dp),
                    verticalArrangement = Arrangement.spacedBy(12.dp)
                ) {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Icon(Icons.Default.Star, contentDescription = null, tint = Color(0xFFFFD27D))
                        Text(
                            "Bengkel terpercaya",
                            color = Color.White,
                            modifier = Modifier.padding(start = 8.dp)
                        )
                    }
                    Text(
                        "Servis Motor Profesional",
                        style = MaterialTheme.typography.headlineMedium,
                        fontWeight = FontWeight.Bold,
                        color = Color.White
                    )
                    Text(
                        "Perawatan, tune up, servis rutin, dan perbaikan motor dengan teknisi berpengalaman dan suku cadang berkualitas.",
                        color = Color.White
                    )
                    Button(onClick = onLogin, modifier = Modifier.fillMaxWidth()) {
                        Icon(Icons.Default.ShoppingCart, contentDescription = null)
                        Text("  Booking servis")
                    }
                    OutlinedButton(
                        onClick = onBrowseProducts,
                        modifier = Modifier.fillMaxWidth(),
                        colors = ButtonDefaults.outlinedButtonColors(contentColor = Color.White),
                        border = BorderStroke(1.dp, Color.White)
                    ) {
                        Text("Lihat produk")
                    }
                }
            }
        }
        item {
            Row(
                Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceEvenly
            ) {
                StatItem("5.000+", "Pelanggan puas")
                StatItem("10+", "Tahun pengalaman")
                StatItem("50+", "Produk terbaik")
            }
        }
        item {
            SectionHeading("Layanan kami", "Solusi lengkap untuk motor Anda")
        }
        items(services.take(3), key = { "home-service-${it.id}" }) { service ->
            ServiceCard(service)
        }
        if (services.isEmpty()) {
            item {
                Text(
                    "Ganti oli, tune up, servis rem, ganti ban, servis kelistrikan, dan overhaul mesin.",
                    style = MaterialTheme.typography.bodyLarge
                )
            }
        }
        item {
            TextButton(onClick = onBrowseServices) {
                Text("Jelajahi semua layanan")
            }
        }
        item {
            SectionHeading("Pilihan produk", "Produk untuk kebutuhan kendaraan Anda")
        }
        if (refreshing && products.isEmpty()) {
            item { CircularProgressIndicator() }
        }
        if (products.isEmpty() && !refreshing) {
            item {
                Text(loadError ?: "Produk belum tersedia. Periksa koneksi server lalu muat ulang.")
            }
        }
        items(products, key = { "home-product-${it.id}" }) { product ->
            ProductCard(product = product, onLogin = onLogin)
        }
        item {
            TextButton(onClick = onBrowseProducts) {
                Text("Lihat semua produk")
            }
        }
        item {
            Button(onClick = onRefresh, enabled = !refreshing, modifier = Modifier.fillMaxWidth()) {
                Text(if (refreshing) "Memuat..." else "Muat ulang katalog")
            }
        }
    }
}

@Composable
private fun ProductsContent(
    modifier: Modifier,
    products: List<com.bengkel.app.data.local.entity.ProductEntity>,
    loadError: String?,
    refreshing: Boolean,
    onRefresh: () -> Unit,
    onLogin: () -> Unit
) {
    LazyColumn(
        modifier = modifier.fillMaxSize(),
        contentPadding = PaddingValues(20.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        item { SectionHeading("Produk", "Suku cadang dan produk pilihan Rumah Daun") }
        if (refreshing && products.isEmpty()) {
            item { CircularProgressIndicator() }
        }
        if (products.isEmpty() && !refreshing) {
            item { Text(loadError ?: "Belum ada produk. Coba muat ulang katalog.") }
        }
        items(products, key = { it.id }) { product ->
            ProductCard(product = product, onLogin = onLogin)
        }
        item {
            OutlinedButton(onClick = onRefresh, enabled = !refreshing, modifier = Modifier.fillMaxWidth()) {
                Text(if (refreshing) "Memuat..." else "Muat ulang")
            }
        }
    }
}

@Composable
private fun ServicesContent(
    modifier: Modifier,
    services: List<ServiceEntity>,
    onLogin: () -> Unit
) {
    val serviceDescriptions = listOf(
        ServicePreview("Ganti Oli", "Ganti oli dengan produk berkualitas untuk menjaga mesin tetap terlumasi dan awet."),
        ServicePreview("Tune Up", "Pemeriksaan sistem pengapian, bahan bakar, dan pendingin untuk mengembalikan performa mesin."),
        ServicePreview("Servis Rem", "Pemeriksaan dan perbaikan sistem rem untuk keselamatan berkendara."),
        ServicePreview("Ganti Ban", "Pilihan ban berkualitas dengan pemasangan menggunakan peralatan bengkel."),
        ServicePreview("Servis Kelistrikan", "Pemeriksaan aki, lampu, dan komponen kelistrikan motor."),
        ServicePreview("Overhaul Mesin", "Perbaikan menyeluruh mesin yang ditangani teknisi berpengalaman.")
    )
    LazyColumn(
        modifier = modifier.fillMaxSize(),
        contentPadding = PaddingValues(20.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        item { SectionHeading("Layanan kami", "Perawatan profesional untuk kendaraan Anda") }
        if (services.isNotEmpty()) {
            items(services, key = { "service-${it.id}" }) { service ->
                ServiceCard(service)
            }
        } else {
            items(serviceDescriptions, key = { it.name }) { service ->
                ServiceDescriptionCard(service)
            }
        }
        item {
            Button(onClick = onLogin, modifier = Modifier.fillMaxWidth()) {
                Text("Masuk untuk booking servis")
            }
        }
    }
}

@Composable
private fun AboutContent(modifier: Modifier) {
    LazyColumn(
        modifier = modifier.fillMaxSize(),
        contentPadding = PaddingValues(20.dp),
        verticalArrangement = Arrangement.spacedBy(16.dp)
    ) {
        item {
            SectionHeading("Tentang kami", "Mengenal Rumah Daun Bengkel Otomotif")
        }
        item {
            Card {
                Column(Modifier.padding(20.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                    Text(
                        "Mengapa memilih Rumah Daun?",
                        style = MaterialTheme.typography.titleLarge,
                        fontWeight = FontWeight.Bold
                    )
                    Text(
                        "Rumah Daun Bengkel Otomotif telah berpengalaman lebih dari 10 tahun dalam melayani kebutuhan perawatan dan perbaikan motor."
                    )
                    Text(
                        "Dengan lebih dari 5000 pelanggan puas, kami terus memberikan pengalaman servis yang nyaman dan terpercaya."
                    )
                    Text(
                        "Setiap layanan mengutamakan kualitas, keselamatan, teknisi profesional, dan suku cadang berkualitas."
                    )
                }
            }
        }
        item {
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceEvenly) {
                StatItem("5.000+", "Pelanggan puas")
                StatItem("10+", "Tahun pengalaman")
                StatItem("50+", "Produk terbaik")
            }
        }
    }
}

@Composable
private fun ContactContent(modifier: Modifier) {
    val context = LocalContext.current
    LazyColumn(
        modifier = modifier.fillMaxSize(),
        contentPadding = PaddingValues(20.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        item {
            SectionHeading("Hubungi kami", "Kami siap melayani kebutuhan servis motor Anda")
        }
        item {
            ContactCard(
                icon = Icons.Default.LocationOn,
                title = "Alamat",
                detail = "Desa Menia, Kecamatan Sabu Barat, Kabupaten Sabu Raijua, Nusa Tenggara Timur"
            ) {
                openExternalIntent(
                    context,
                    Intent(
                        Intent.ACTION_VIEW,
                        Uri.parse("geo:0,0?q=${Uri.encode("Desa Menia, Sabu Barat, Sabu Raijua")}")
                    )
                )
            }
        }
        item {
            ContactCard(
                icon = Icons.Default.Call,
                title = "Telepon / WhatsApp",
                detail = "0821-2462-6248\nSenin–Sabtu, 08:00–17:00"
            ) {
                openExternalIntent(context, Intent(Intent.ACTION_DIAL, Uri.parse("tel:082124626248")))
            }
        }
        item {
            ContactCard(
                icon = Icons.Default.Mail,
                title = "Email",
                detail = "info@rumahdaun.com"
            ) {
                openExternalIntent(
                    context,
                    Intent(Intent.ACTION_SENDTO, Uri.parse("mailto:info@rumahdaun.com"))
                )
            }
        }
        item {
            ContactCard(
                icon = Icons.Default.Build,
                title = "Jam operasional",
                detail = "Senin–Sabtu: 08:00–17:00\nMinggu: tutup"
            )
        }
    }
}

@Composable
private fun SectionHeading(title: String, subtitle: String) {
    Column(verticalArrangement = Arrangement.spacedBy(4.dp)) {
        Text(title, style = MaterialTheme.typography.headlineSmall, fontWeight = FontWeight.Bold)
        Text(subtitle, style = MaterialTheme.typography.bodyMedium, color = Color(0xFF65716A))
    }
}

@Composable
private fun StatItem(value: String, label: String) {
    Column(horizontalAlignment = Alignment.CenterHorizontally) {
        Text(value, style = MaterialTheme.typography.titleLarge, fontWeight = FontWeight.Bold)
        Text(label, style = MaterialTheme.typography.labelSmall)
    }
}

@Composable
private fun ProductCard(
    product: com.bengkel.app.data.local.entity.ProductEntity,
    onLogin: () -> Unit
) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(18.dp)
    ) {
        Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(6.dp)) {
            product.categoryName?.let {
                Text(it, style = MaterialTheme.typography.labelMedium, color = Color(0xFF356A49))
            }
            Text(product.name, style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
            Text(rupiah(product.harga), style = MaterialTheme.typography.titleSmall, color = Color(0xFF356A49))
            Text("Stok tersedia: ${product.stok}")
            product.description?.takeIf { it.isNotBlank() }?.let { Text(it) }
            Button(
                onClick = onLogin,
                enabled = product.stok > 0,
                modifier = Modifier.fillMaxWidth()
            ) {
                Text(if (product.stok > 0) "Masuk untuk membeli" else "Stok habis")
            }
        }
    }
}

@Composable
private fun ServiceCard(service: ServiceEntity) {
    Card(modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(18.dp)) {
        Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(6.dp)) {
            Text(service.name, style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
            Text(
                "${rupiah(service.harga)}${service.duration?.let { " • $it" } ?: ""}",
                color = Color(0xFF356A49)
            )
            service.description?.takeIf { it.isNotBlank() }?.let { Text(it) }
        }
    }
}

@Composable
private fun ServiceDescriptionCard(service: ServicePreview) {
    Card(modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(18.dp)) {
        Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(6.dp)) {
            Text(service.name, style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
            Text(service.description)
        }
    }
}

@Composable
private fun ContactCard(
    icon: androidx.compose.ui.graphics.vector.ImageVector,
    title: String,
    detail: String,
    onClick: (() -> Unit)? = null
) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        onClick = onClick ?: {},
        enabled = onClick != null
    ) {
        Row(
            Modifier.padding(16.dp),
            horizontalArrangement = Arrangement.spacedBy(14.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Icon(icon, contentDescription = null, tint = Color(0xFF356A49), modifier = Modifier.size(26.dp))
            Column(verticalArrangement = Arrangement.spacedBy(4.dp)) {
                Text(title, style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.Bold)
                Text(detail)
            }
        }
    }
}

private fun openExternalIntent(context: android.content.Context, intent: Intent) {
    try {
        context.startActivity(intent)
    } catch (exception: ActivityNotFoundException) {
        Toast.makeText(
            context,
            "Tidak ada aplikasi yang dapat membuka fitur ini.",
            Toast.LENGTH_LONG
        ).show()
    }
}
