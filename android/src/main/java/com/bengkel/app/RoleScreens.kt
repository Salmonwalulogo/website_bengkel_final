package com.bengkel.app

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Visibility
import androidx.compose.material.icons.filled.VisibilityOff
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Button
import androidx.compose.material3.Card
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.DropdownMenu
import androidx.compose.material3.DropdownMenuItem
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.unit.dp
import com.bengkel.app.data.BengkelRepository
import com.bengkel.app.data.local.entity.ServiceEntity
import com.bengkel.app.data.local.entity.UserEntity
import com.bengkel.app.data.remote.model.CategoryDto
import com.bengkel.app.data.remote.model.CategoryRequest
import com.bengkel.app.data.remote.model.PaymentDto
import com.bengkel.app.data.remote.model.ServiceRequest
import com.bengkel.app.data.remote.model.UserDto
import kotlinx.coroutines.launch

@Composable
internal fun ProfileEditorDialog(
    repository: BengkelRepository,
    user: UserEntity,
    onDismiss: () -> Unit,
    onSaved: (UserEntity) -> Unit
) {
    val scope = rememberCoroutineScope()
    var name by remember(user.id) { mutableStateOf(user.name) }
    var phone by remember(user.id) { mutableStateOf(user.noHp.orEmpty()) }
    var password by remember { mutableStateOf("") }
    var passwordVisible by remember { mutableStateOf(false) }
    var error by remember { mutableStateOf<String?>(null) }
    var saving by remember { mutableStateOf(false) }

    AlertDialog(
        onDismissRequest = onDismiss,
        title = { Text("Ubah profil") },
        text = {
            Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
                OutlinedTextField(name, { name = it }, label = { Text("Nama") }, modifier = Modifier.fillMaxWidth())
                OutlinedTextField(phone, { phone = it }, label = { Text("Nomor HP") }, modifier = Modifier.fillMaxWidth())
                OutlinedTextField(
                    password,
                    { password = it },
                    label = { Text("Kata sandi baru (opsional)") },
                    visualTransformation = if (passwordVisible) VisualTransformation.None else PasswordVisualTransformation(),
                    trailingIcon = {
                        IconButton(onClick = { passwordVisible = !passwordVisible }) {
                            Icon(
                                imageVector = if (passwordVisible) Icons.Default.VisibilityOff else Icons.Default.Visibility,
                                contentDescription = if (passwordVisible) "Sembunyikan kata sandi" else "Tampilkan kata sandi"
                            )
                        }
                    },
                    modifier = Modifier.fillMaxWidth()
                )
                error?.let { Text(it, color = MaterialTheme.colorScheme.error) }
            }
        },
        confirmButton = {
            Button(
                enabled = !saving && name.isNotBlank() && (password.isBlank() || password.length >= 8),
                onClick = {
                    scope.launch {
                        saving = true
                        error = null
                        try {
                            onSaved(repository.updateProfile(name, phone, password))
                        } catch (exception: Exception) {
                            error = exception.message ?: "Profil gagal diperbarui."
                        } finally {
                            saving = false
                        }
                    }
                }
            ) { Text(if (saving) "Menyimpan..." else "Simpan") }
        },
        dismissButton = { TextButton(onClick = onDismiss) { Text("Batal") } }
    )
}

@Composable
internal fun DashboardScreen(
    repository: BengkelRepository,
    user: UserEntity,
    isReport: Boolean = false,
    cartItemCount: Int = 0
) {
    var revision by remember { mutableIntStateOf(0) }
    var stats by remember { mutableStateOf<com.bengkel.app.data.remote.model.DashboardStatsResponse?>(null) }
    var error by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(user.id, revision) {
        error = null
        try {
            stats = repository.fetchDashboardStats()
        } catch (exception: Exception) {
            error = exception.message ?: "Ringkasan gagal dimuat."
        }
    }

    val metrics = when (user.role) {
        "customer" -> listOf(
            "Kendaraan" to (stats?.vehiclesCount ?: 0).toString(),
            "Booking aktif" to (stats?.activeBookings ?: 0).toString(),
            "Booking selesai" to (stats?.completedBookings ?: 0).toString(),
            "Transaksi" to (stats?.totalTransactions ?: 0).toString(),
            "Item keranjang" to cartItemCount.toString()
        )
        "kasir" -> listOf(
            "Pesanan menunggu" to (stats?.pendingOrders ?: 0).toString(),
            "Pembayaran hari ini" to (stats?.todayPayments ?: 0).toString(),
            "Total transaksi" to (stats?.totalTransactions ?: 0).toString()
        )
        else -> listOf(
            "Pelanggan" to (stats?.totalCustomers ?: 0).toString(),
            "Kendaraan" to (stats?.totalVehicles ?: 0).toString(),
            "Booking" to (stats?.totalBookings ?: 0).toString(),
            "Transaksi" to (stats?.totalTransactions ?: 0).toString(),
            "Pendapatan" to rupiah(stats?.totalIncome ?: 0.0)
        )
    }

    LazyColumn(
        modifier = Modifier.fillMaxSize(),
        contentPadding = androidx.compose.foundation.layout.PaddingValues(16.dp),
        verticalArrangement = Arrangement.spacedBy(10.dp)
    ) {
        item {
            Text(
                if (isReport) "Laporan ringkas" else "Selamat datang, ${user.name}",
                style = MaterialTheme.typography.headlineSmall
            )
            Text("Ringkasan akun ${user.role}. Data diperbarui dari server.")
            error?.let { Text(it, color = MaterialTheme.colorScheme.error) }
            if (stats == null && error == null) {
                CircularProgressIndicator(Modifier.padding(top = 12.dp))
            }
        }
        items(metrics, key = { it.first }) { (label, value) ->
            Card(Modifier.fillMaxWidth()) {
                Column(Modifier.padding(16.dp)) {
                    Text(label, style = MaterialTheme.typography.labelLarge)
                    Text(value, style = MaterialTheme.typography.headlineSmall)
                }
            }
        }
        item {
            OutlinedButton(onClick = { revision++ }, modifier = Modifier.fillMaxWidth()) {
                Text("Muat ulang")
            }
        }
    }
}

@Composable
internal fun CategoriesScreen(repository: BengkelRepository, showMessage: (String) -> Unit) {
    val scope = rememberCoroutineScope()
    var categories by remember { mutableStateOf(emptyList<CategoryDto>()) }
    var revision by remember { mutableIntStateOf(0) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }
    var editing by remember { mutableStateOf<CategoryDto?>(null) }
    var creating by remember { mutableStateOf(false) }
    var deleting by remember { mutableStateOf<CategoryDto?>(null) }

    LaunchedEffect(revision) {
        loading = true
        error = null
        try {
            categories = repository.fetchCategories()
        } catch (exception: Exception) {
            error = exception.message ?: "Kategori gagal dimuat."
        } finally {
            loading = false
        }
    }

    Column(Modifier.fillMaxSize()) {
        Button(
            onClick = { creating = true },
            modifier = Modifier.fillMaxWidth().padding(horizontal = 16.dp, vertical = 8.dp)
        ) { Text("Tambah kategori") }
        error?.let { Text(it, color = MaterialTheme.colorScheme.error, modifier = Modifier.padding(horizontal = 16.dp)) }
        if (loading) CircularProgressIndicator(Modifier.padding(16.dp))
        LazyColumn(
            modifier = Modifier.weight(1f),
            contentPadding = androidx.compose.foundation.layout.PaddingValues(16.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            if (categories.isEmpty() && !loading) item { Text("Belum ada kategori.") }
            items(categories, key = { it.id }) { category ->
                Card(Modifier.fillMaxWidth()) {
                    Row(
                        Modifier.fillMaxWidth().padding(12.dp),
                        horizontalArrangement = Arrangement.SpaceBetween
                    ) {
                        Text(category.name, Modifier.weight(1f), style = MaterialTheme.typography.titleMedium)
                        OutlinedButton(onClick = { editing = category }) { Text("Ubah") }
                        OutlinedButton(onClick = { deleting = category }) { Text("Hapus") }
                    }
                }
            }
        }
    }

    if (creating || editing != null) {
        CategoryEditorDialog(
            category = editing,
            onDismiss = { creating = false; editing = null },
            onSave = { id, request ->
                scope.launch {
                    try {
                        repository.saveCategory(id, request)
                        showMessage("Kategori berhasil disimpan.")
                        creating = false
                        editing = null
                        revision++
                    } catch (exception: Exception) {
                        showMessage(exception.message ?: "Kategori gagal disimpan.")
                    }
                }
            }
        )
    }
    deleting?.let { category ->
        AlertDialog(
            onDismissRequest = { deleting = null },
            title = { Text("Hapus kategori?") },
            text = { Text("Kategori ${category.name} akan dihapus dari katalog.") },
            confirmButton = {
                Button(onClick = {
                    scope.launch {
                        try {
                            repository.deleteCategory(category.id)
                            showMessage("Kategori dihapus.")
                            revision++
                        } catch (exception: Exception) {
                            showMessage(exception.message ?: "Kategori gagal dihapus.")
                        } finally {
                            deleting = null
                        }
                    }
                }) { Text("Hapus") }
            },
            dismissButton = { OutlinedButton(onClick = { deleting = null }) { Text("Batal") } }
        )
    }
}

@Composable
private fun CategoryEditorDialog(
    category: CategoryDto?,
    onDismiss: () -> Unit,
    onSave: (Long?, CategoryRequest) -> Unit
) {
    var name by remember(category?.id) { mutableStateOf(category?.name.orEmpty()) }
    var description by remember(category?.id) { mutableStateOf(category?.description.orEmpty()) }
    AlertDialog(
        onDismissRequest = onDismiss,
        title = { Text(if (category == null) "Tambah kategori" else "Ubah kategori") },
        text = {
            Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
                OutlinedTextField(name, { name = it }, label = { Text("Nama kategori") }, modifier = Modifier.fillMaxWidth())
                OutlinedTextField(
                    description,
                    { description = it },
                    label = { Text("Deskripsi") },
                    modifier = Modifier.fillMaxWidth()
                )
            }
        },
        confirmButton = {
            Button(
                enabled = name.isNotBlank(),
                onClick = { onSave(category?.id, CategoryRequest(name.trim(), description.trim().ifBlank { null })) }
            ) { Text("Simpan") }
        },
        dismissButton = { OutlinedButton(onClick = onDismiss) { Text("Batal") } }
    )
}

@Composable
internal fun UsersScreen(repository: BengkelRepository, showMessage: (String) -> Unit) {
    val scope = rememberCoroutineScope()
    var users by remember { mutableStateOf(emptyList<UserDto>()) }
    var revision by remember { mutableIntStateOf(0) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }
    var showEditor by remember { mutableStateOf(false) }

    LaunchedEffect(revision) {
        loading = true
        error = null
        try {
            users = repository.fetchUsers()
        } catch (exception: Exception) {
            error = exception.message ?: "Pengguna gagal dimuat."
        } finally {
            loading = false
        }
    }

    Column(Modifier.fillMaxSize()) {
        Button(
            onClick = { showEditor = true },
            modifier = Modifier.fillMaxWidth().padding(horizontal = 16.dp, vertical = 8.dp)
        ) { Text("Tambah pengguna") }
        error?.let { Text(it, color = MaterialTheme.colorScheme.error, modifier = Modifier.padding(horizontal = 16.dp)) }
        if (loading) CircularProgressIndicator(Modifier.padding(16.dp))
        LazyColumn(
            modifier = Modifier.weight(1f),
            contentPadding = androidx.compose.foundation.layout.PaddingValues(16.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            if (users.isEmpty() && !loading) item { Text("Belum ada pengguna.") }
            items(users, key = { it.id }) { user ->
                Card(Modifier.fillMaxWidth()) {
                    Column(Modifier.padding(16.dp)) {
                        Text(user.name, style = MaterialTheme.typography.titleMedium)
                        Text(user.email)
                        user.noHp?.takeIf { it.isNotBlank() }?.let { Text(it) }
                        Text("${user.role} • ${user.username}")
                    }
                }
            }
        }
    }
    if (showEditor) {
        UserEditorDialog(
            onDismiss = { showEditor = false },
            onSave = { name, email, username, phone, role, password ->
                scope.launch {
                    try {
                        repository.createUser(name, email, username, phone, role, password)
                        showMessage("Pengguna berhasil ditambahkan.")
                        showEditor = false
                        revision++
                    } catch (exception: Exception) {
                        showMessage(exception.message ?: "Pengguna gagal ditambahkan.")
                    }
                }
            }
        )
    }
}

@Composable
private fun UserEditorDialog(
    onDismiss: () -> Unit,
    onSave: (String, String, String, String, String, String) -> Unit
) {
    var name by remember { mutableStateOf("") }
    var email by remember { mutableStateOf("") }
    var username by remember { mutableStateOf("") }
    var phone by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var passwordVisible by remember { mutableStateOf(false) }
    var role by remember { mutableStateOf("customer") }
    var roleMenu by remember { mutableStateOf(false) }
    AlertDialog(
        onDismissRequest = onDismiss,
        title = { Text("Tambah pengguna") },
        text = {
            Column(
                Modifier.verticalScroll(rememberScrollState()),
                verticalArrangement = Arrangement.spacedBy(6.dp)
            ) {
                OutlinedTextField(name, { name = it }, label = { Text("Nama") }, modifier = Modifier.fillMaxWidth())
                OutlinedTextField(email, { email = it }, label = { Text("Email") }, modifier = Modifier.fillMaxWidth())
                OutlinedTextField(username, { username = it }, label = { Text("Username") }, modifier = Modifier.fillMaxWidth())
                OutlinedTextField(phone, { phone = it }, label = { Text("Nomor HP") }, modifier = Modifier.fillMaxWidth())
                Box {
                    OutlinedButton(onClick = { roleMenu = true }, modifier = Modifier.fillMaxWidth()) {
                        Text("Role: $role")
                    }
                    DropdownMenu(expanded = roleMenu, onDismissRequest = { roleMenu = false }) {
                        listOf("admin", "kasir", "customer").forEach { option ->
                            DropdownMenuItem(
                                text = { Text(option) },
                                onClick = { role = option; roleMenu = false }
                            )
                        }
                    }
                }
                OutlinedTextField(
                    password,
                    { password = it },
                    label = { Text("Kata sandi (min. 8 karakter)") },
                    visualTransformation = if (passwordVisible) VisualTransformation.None else PasswordVisualTransformation(),
                    trailingIcon = {
                        IconButton(onClick = { passwordVisible = !passwordVisible }) {
                            Icon(
                                imageVector = if (passwordVisible) Icons.Default.VisibilityOff else Icons.Default.Visibility,
                                contentDescription = if (passwordVisible) "Sembunyikan kata sandi" else "Tampilkan kata sandi"
                            )
                        }
                    },
                    modifier = Modifier.fillMaxWidth()
                )
            }
        },
        confirmButton = {
            Button(
                enabled = name.isNotBlank() && email.contains("@") &&
                    username.isNotBlank() && password.length >= 8,
                onClick = { onSave(name, email, username, phone, role, password) }
            ) { Text("Simpan") }
        },
        dismissButton = { OutlinedButton(onClick = onDismiss) { Text("Batal") } }
    )
}

@Composable
internal fun ServicesScreen(repository: BengkelRepository, showMessage: (String) -> Unit) {
    val services by repository.services().collectAsState(initial = emptyList())
    val scope = rememberCoroutineScope()
    var editing by remember { mutableStateOf<ServiceEntity?>(null) }
    var creating by remember { mutableStateOf(false) }
    var deleting by remember { mutableStateOf<ServiceEntity?>(null) }

    Column(Modifier.fillMaxSize()) {
        Button(
            onClick = { creating = true },
            modifier = Modifier.fillMaxWidth().padding(horizontal = 16.dp, vertical = 8.dp)
        ) { Text("Tambah layanan") }
        LazyColumn(
            modifier = Modifier.weight(1f),
            contentPadding = androidx.compose.foundation.layout.PaddingValues(16.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            if (services.isEmpty()) item { Text("Belum ada layanan.") }
            items(services, key = { it.id }) { service ->
                Card(Modifier.fillMaxWidth()) {
                    Column(Modifier.padding(16.dp)) {
                        Text(service.name, style = MaterialTheme.typography.titleMedium)
                        Text("${rupiah(service.harga)} • ${service.duration ?: "Durasi belum diatur"}")
                        service.description?.takeIf { it.isNotBlank() }?.let { Text(it) }
                        Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                            OutlinedButton(onClick = { editing = service }) { Text("Ubah") }
                            OutlinedButton(onClick = { deleting = service }) { Text("Hapus") }
                        }
                    }
                }
            }
        }
    }
    if (creating || editing != null) {
        ServiceEditorDialog(
            service = editing,
            onDismiss = { creating = false; editing = null },
            onSave = { id, request ->
                scope.launch {
                    try {
                        repository.saveService(id, request)
                        showMessage("Layanan berhasil disimpan.")
                        creating = false
                        editing = null
                    } catch (exception: Exception) {
                        showMessage(exception.message ?: "Layanan gagal disimpan.")
                    }
                }
            }
        )
    }
    deleting?.let { service ->
        AlertDialog(
            onDismissRequest = { deleting = null },
            title = { Text("Hapus layanan?") },
            text = { Text("Layanan ${service.name} akan dihapus.") },
            confirmButton = {
                Button(onClick = {
                    scope.launch {
                        try {
                            repository.deleteService(service)
                            showMessage("Layanan dihapus.")
                        } catch (exception: Exception) {
                            showMessage(exception.message ?: "Layanan gagal dihapus.")
                        } finally {
                            deleting = null
                        }
                    }
                }) { Text("Hapus") }
            },
            dismissButton = { OutlinedButton(onClick = { deleting = null }) { Text("Batal") } }
        )
    }
}

@Composable
private fun ServiceEditorDialog(
    service: ServiceEntity?,
    onDismiss: () -> Unit,
    onSave: (Long?, ServiceRequest) -> Unit
) {
    var name by remember(service?.id) { mutableStateOf(service?.name.orEmpty()) }
    var description by remember(service?.id) { mutableStateOf(service?.description.orEmpty()) }
    var price by remember(service?.id) { mutableStateOf(service?.harga?.toLong()?.toString().orEmpty()) }
    var duration by remember(service?.id) { mutableStateOf(service?.duration.orEmpty()) }
    AlertDialog(
        onDismissRequest = onDismiss,
        title = { Text(if (service == null) "Tambah layanan" else "Ubah layanan") },
        text = {
            Column(
                Modifier.verticalScroll(rememberScrollState()),
                verticalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                OutlinedTextField(name, { name = it }, label = { Text("Nama layanan") }, modifier = Modifier.fillMaxWidth())
                OutlinedTextField(description, { description = it }, label = { Text("Deskripsi") }, modifier = Modifier.fillMaxWidth())
                OutlinedTextField(
                    price,
                    { price = it.filter(Char::isDigit) },
                    label = { Text("Harga") },
                    keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
                    modifier = Modifier.fillMaxWidth()
                )
                OutlinedTextField(duration, { duration = it }, label = { Text("Durasi") }, modifier = Modifier.fillMaxWidth())
            }
        },
        confirmButton = {
            Button(
                enabled = name.isNotBlank() && price.toDoubleOrNull() != null,
                onClick = {
                    onSave(
                        service?.id,
                        ServiceRequest(
                            name = name.trim(),
                            description = description.trim().ifBlank { null },
                            harga = price.toDouble(),
                            duration = duration.trim().ifBlank { null }
                        )
                    )
                }
            ) { Text("Simpan") }
        },
        dismissButton = { OutlinedButton(onClick = onDismiss) { Text("Batal") } }
    )
}

@Composable
internal fun NotificationsScreen(repository: BengkelRepository, showMessage: (String) -> Unit) {
    val scope = rememberCoroutineScope()
    var notifications by remember { mutableStateOf(emptyList<com.bengkel.app.data.remote.model.NotificationDto>()) }
    var revision by remember { mutableIntStateOf(0) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(revision) {
        loading = true
        error = null
        try {
            notifications = repository.fetchNotifications()
        } catch (exception: Exception) {
            error = exception.message ?: "Notifikasi gagal dimuat."
        } finally {
            loading = false
        }
    }

    Column(Modifier.fillMaxSize()) {
        error?.let { Text(it, color = MaterialTheme.colorScheme.error, modifier = Modifier.padding(16.dp)) }
        if (loading) CircularProgressIndicator(Modifier.padding(16.dp))
        LazyColumn(
            modifier = Modifier.fillMaxSize(),
            contentPadding = androidx.compose.foundation.layout.PaddingValues(16.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            if (notifications.isEmpty() && !loading && error == null) item { Text("Belum ada notifikasi.") }
            items(notifications, key = { it.id }) { notification ->
                Card(Modifier.fillMaxWidth()) {
                    Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(6.dp)) {
                        Text(notification.title, style = MaterialTheme.typography.titleMedium)
                        Text(notification.message)
                        notification.createdAt?.let { Text(it.take(16).replace('T', ' ')) }
                        if (!notification.isRead) {
                            OutlinedButton(onClick = {
                                scope.launch {
                                    try {
                                        repository.markNotificationAsRead(notification.id)
                                        revision++
                                    } catch (exception: Exception) {
                                        showMessage(exception.message ?: "Notifikasi gagal diperbarui.")
                                    }
                                }
                            }) { Text("Tandai sudah dibaca") }
                        } else {
                            Text("Sudah dibaca", style = MaterialTheme.typography.labelSmall)
                        }
                    }
                }
            }
        }
    }
}

@Composable
internal fun PaymentsScreen(repository: BengkelRepository) {
    var payments by remember { mutableStateOf(emptyList<PaymentDto>()) }
    var revision by remember { mutableIntStateOf(0) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(revision) {
        loading = true
        error = null
        try {
            payments = repository.fetchPayments()
        } catch (exception: Exception) {
            error = exception.message ?: "Pembayaran gagal dimuat."
        } finally {
            loading = false
        }
    }

    Column(Modifier.fillMaxSize()) {
        error?.let { Text(it, color = MaterialTheme.colorScheme.error, modifier = Modifier.padding(16.dp)) }
        if (loading) CircularProgressIndicator(Modifier.padding(16.dp))
        OutlinedButton(
            onClick = { revision++ },
            modifier = Modifier.fillMaxWidth().padding(horizontal = 16.dp)
        ) { Text("Muat ulang") }
        LazyColumn(
            modifier = Modifier.weight(1f),
            contentPadding = androidx.compose.foundation.layout.PaddingValues(16.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            if (payments.isEmpty() && !loading && error == null) item { Text("Belum ada pembayaran.") }
            items(payments, key = { it.id }) { payment ->
                Card(Modifier.fillMaxWidth()) {
                    Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(4.dp)) {
                        Text(payment.paymentCode, style = MaterialTheme.typography.titleMedium)
                        Text("Invoice: ${payment.invoiceNumber ?: "#${payment.transactionId}"}")
                        Text("Jumlah: ${rupiah(payment.amount)}")
                        Text("Status: ${payment.status.replaceFirstChar(Char::uppercase)}")
                        payment.paymentDate?.let { Text(it.take(16).replace('T', ' ')) }
                    }
                }
            }
        }
    }
}
