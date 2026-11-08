package com.bengkel.app.data

import android.content.Context
import android.content.Context.MODE_PRIVATE
import com.bengkel.app.data.local.AppDatabase
import com.bengkel.app.data.local.SessionManager
import com.bengkel.app.data.local.entity.BookingEntity
import com.bengkel.app.data.local.entity.ProductEntity
import com.bengkel.app.data.local.entity.ServiceEntity
import com.bengkel.app.data.local.entity.SyncQueueEntity
import com.bengkel.app.data.local.entity.TransactionDetailEntity
import com.bengkel.app.data.local.entity.TransactionEntity
import com.bengkel.app.data.local.entity.UserEntity
import com.bengkel.app.data.local.entity.VehicleEntity
import com.bengkel.app.data.remote.api.ApiService
import com.bengkel.app.data.remote.api.RetrofitClient
import com.bengkel.app.data.remote.model.LoginRequest
import com.bengkel.app.data.remote.model.RegisterRequest
import com.bengkel.app.data.remote.model.BookingRequest
import com.bengkel.app.data.remote.model.CategoryDto
import com.bengkel.app.data.remote.model.CategoryRequest
import com.bengkel.app.data.remote.model.CustomerOrderItemRequest
import com.bengkel.app.data.remote.model.CustomerOrderRequest
import com.bengkel.app.data.remote.model.DashboardStatsResponse
import com.bengkel.app.data.remote.model.NotificationDto
import com.bengkel.app.data.remote.model.PaymentDto
import com.bengkel.app.data.remote.model.ProductDto
import com.bengkel.app.data.remote.model.ProductRequest
import com.bengkel.app.data.remote.model.ServiceRequest
import com.bengkel.app.data.remote.model.TransactionItemDto
import com.bengkel.app.data.remote.model.TransactionRequest
import com.bengkel.app.data.remote.model.VehicleRequest
import com.google.gson.Gson
import java.io.IOException
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale
import java.util.UUID
import com.google.gson.reflect.TypeToken
import kotlinx.coroutines.flow.Flow
import retrofit2.Response

data class CartLine(val product: ProductEntity, val quantity: Int)

data class CheckoutResult(val transactionId: String, val synced: Boolean)

private class ApiException(message: String) : Exception(message)

class BengkelRepository(context: Context) {
    val sessionManager = SessionManager(context.applicationContext)

    private val database = AppDatabase.getDatabase(context.applicationContext)
    private val retrofitClient = RetrofitClient(sessionManager)
    private val gson = Gson()
    private val cache = context.applicationContext.getSharedPreferences("bengkel_data_cache", MODE_PRIVATE)
    private val api: ApiService
        get() = retrofitClient.getApiService()

    fun products(includeInactive: Boolean = false): Flow<List<ProductEntity>> =
        if (includeInactive) database.productDao().getAllProductsFlow()
        else database.productDao().getActiveProductsFlow()
    fun services(): Flow<List<ServiceEntity>> = database.serviceDao().getActiveServicesFlow()
    fun vehicles(): Flow<List<VehicleEntity>> = database.vehicleDao().getAllVehiclesFlow()
    fun bookings(): Flow<List<BookingEntity>> = database.bookingDao().getAllBookingsFlow()
    fun transactions(): Flow<List<TransactionEntity>> = database.transactionDao().getAllTransactionsFlow()
    fun pendingCount(): Flow<Int> = database.syncQueueDao().getPendingCountFlow()

    suspend fun refreshPublicProducts() {
        val response = api.getProducts()
        val body = response.requireBody("Gagal memuat produk")
        database.productDao().insertProducts(
            body.data.map { product ->
                ProductEntity(
                    id = product.id,
                    categoryId = product.categoryId,
                    categoryName = product.category?.name,
                    name = product.name,
                    description = product.description,
                    harga = product.harga,
                    stok = product.stok,
                    status = product.status ?: "active",
                    photo = product.photo
                )
            }
        )
    }

    suspend fun login(identifier: String, password: String): UserEntity {
        val response = api.login(
            LoginRequest(
                email = identifier.takeIf { it.contains("@") },
                username = identifier.takeUnless { it.contains("@") },
                password = password
            )
        )
        val body = response.requireBody("Login gagal")
        val token = body.token ?: throw IOException("Server tidak mengirim token login.")
        val user = body.user ?: throw IOException("Server tidak mengirim data akun.")
        val localUser = user.toEntity()
        sessionManager.saveAuthToken(token)
        sessionManager.saveUser(localUser)
        return localUser
    }

    private fun ProductRequest.toProductEntity(id: Long, synced: Boolean) = ProductEntity(
        id = id,
        categoryId = categoryId,
        categoryName = fetchCategoriesCache().firstOrNull { it.id == categoryId }?.name,
        name = name,
        description = description,
        harga = harga,
        stok = stok,
        status = status,
        isSynced = synced
    )

    suspend fun register(
        name: String,
        email: String,
        username: String,
        phone: String,
        password: String
    ): UserEntity {
        val response = api.register(
            RegisterRequest(
                name = name.trim(),
                email = email.trim(),
                username = username.trim().ifBlank { null },
                noHp = phone.trim().ifBlank { null },
                password = password
            )
        )
        val body = response.requireBody("Pendaftaran gagal")
        val token = body.token ?: throw IOException("Server tidak mengirim token login.")
        val user = body.user ?: throw IOException("Server tidak mengirim data akun.")
        val localUser = user.toEntity()
        sessionManager.saveAuthToken(token)
        sessionManager.saveUser(localUser)
        return localUser
    }

    suspend fun refreshData(): List<String> {
        val errors = mutableListOf<String>()
        val user = sessionManager.getCurrentUser()

        try {
            val response = api.getProducts(includeInactive = user?.role == "admin")
            val body = response.requireBody("Gagal memuat produk")
            database.productDao().insertProducts(
                body.data.map { product ->
                    ProductEntity(
                        id = product.id,
                        categoryId = product.categoryId,
                        categoryName = product.category?.name,
                        name = product.name,
                        description = product.description,
                        harga = product.harga,
                        stok = product.stok,
                        status = product.status ?: "active",
                        photo = product.photo
                    )
                }
            )
        } catch (error: IOException) {
            errors += "Produk: ${error.message ?: "tidak dapat diperbarui"}"
        } catch (error: ApiException) {
            errors += "Produk: ${error.message}"
        }

        try {
            val response = api.getServices()
            val body = response.requireBody("Gagal memuat layanan")
            database.serviceDao().insertServices(
                body.data.map { service ->
                    ServiceEntity(
                        id = service.id,
                        name = service.name,
                        description = service.description,
                        harga = service.harga,
                        duration = service.duration,
                        status = service.status ?: "active"
                    )
                }
            )
        } catch (error: IOException) {
            errors += "Layanan: ${error.message ?: "tidak dapat diperbarui"}"
        } catch (error: ApiException) {
            errors += "Layanan: ${error.message}"
        }

        try {
            val response = api.getVehicles(user?.id.takeIf { user?.role == "customer" })
            val body = response.requireBody("Gagal memuat kendaraan")
            database.vehicleDao().insertVehicles(
                body.data.map { vehicle ->
                    VehicleEntity(
                        localId = vehicle.id.toString(),
                        serverId = vehicle.id,
                        userId = vehicle.userId,
                        ownerName = vehicle.user?.name,
                        plateNumber = vehicle.plateNumber,
                        brand = vehicle.brand,
                        model = vehicle.model,
                        year = vehicle.year ?: 2024,
                        color = vehicle.color ?: "-",
                        isSynced = true
                    )
                }
            )
        } catch (error: IOException) {
            errors += "Kendaraan: ${error.message ?: "tidak dapat diperbarui"}"
        } catch (error: ApiException) {
            errors += "Kendaraan: ${error.message}"
        }

        try {
            val response = api.getBookings()
            val body = response.requireBody("Gagal memuat pemesanan")
            database.bookingDao().insertBookings(
                body.data.map { booking ->
                    BookingEntity(
                        localId = "server-${booking.id}",
                        serverId = booking.id,
                        userId = booking.userId,
                        userName = booking.user?.name,
                        vehicleId = booking.vehicleId,
                        vehiclePlate = booking.vehicle?.plateNumber,
                        serviceId = booking.serviceId,
                        serviceName = booking.service?.name,
                        bookingDate = booking.bookingDate,
                        bookingTime = booking.bookingTime ?: "-",
                        complaint = booking.complaint ?: "-",
                        status = booking.status,
                        notes = booking.notes,
                        isSynced = true
                    )
                }
            )
        } catch (error: IOException) {
            errors += "Pemesanan: ${error.message ?: "tidak dapat diperbarui"}"
        } catch (error: ApiException) {
            errors += "Pemesanan: ${error.message}"
        }

        try {
            val response = api.getTransactions()
            val body = response.requireBody("Gagal memuat transaksi")
            database.transactionDao().insertTransactions(
                body.data.map { transaction ->
                    TransactionEntity(
                        localId = "server-${transaction.id}",
                        serverId = transaction.id,
                        userId = transaction.userId,
                        customerId = transaction.customerId,
                        customerName = transaction.customer?.name,
                        subtotal = transaction.subtotal,
                        discount = transaction.discount,
                        tax = transaction.tax,
                        totalPayment = transaction.totalPayment,
                        paidAmount = transaction.paidAmount,
                        changeAmount = transaction.changeAmount,
                        status = transaction.status,
                        paymentMethod = transaction.paymentMethod,
                        createdAt = transaction.createdAt.orEmpty(),
                        isSynced = true
                    )
                }
            )
        } catch (error: IOException) {
            errors += "Riwayat transaksi: ${error.message ?: "tidak dapat diperbarui"}"
        } catch (error: ApiException) {
            errors += "Riwayat transaksi: ${error.message}"
        }

        try {
            fetchAndCache("categories") { api.getCategories().requireBody("Gagal memuat kategori").data }
        } catch (error: IOException) {
            errors += "Kategori: ${error.message ?: "tidak dapat diperbarui"}"
        } catch (error: ApiException) {
            errors += "Kategori: ${error.message}"
        }
        if (user?.role == "admin" || user?.role == "kasir") {
            try {
                fetchAndCache("users_all") { api.getUsers().requireBody("Gagal memuat pengguna").data }
                fetchAndCache("users_admin") { api.getUsers("admin").requireBody("Gagal memuat admin").data }
                fetchAndCache("users_kasir") { api.getUsers("kasir").requireBody("Gagal memuat kasir").data }
                fetchAndCache("users_customer") { api.getUsers("customer").requireBody("Gagal memuat pelanggan").data }
            } catch (error: IOException) {
                errors += "Data admin: ${error.message ?: "tidak dapat diperbarui"}"
            } catch (error: ApiException) {
                errors += "Data admin: ${error.message}"
            }
            try {
                fetchAndCache("payments") { api.getPayments().requireBody("Gagal memuat pembayaran").data }
            } catch (error: IOException) {
                errors += "Pembayaran: ${error.message ?: "tidak dapat diperbarui"}"
            } catch (error: ApiException) {
                errors += "Pembayaran: ${error.message}"
            }
        }
        try {
            val stats = api.getDashboardStats().requireBody("Gagal memuat ringkasan")
            cache.edit().putString(cacheKey("dashboard"), gson.toJson(stats)).apply()
        } catch (error: IOException) {
            errors += "Ringkasan: ${error.message ?: "tidak dapat diperbarui"}"
        } catch (error: ApiException) {
            errors += "Ringkasan: ${error.message}"
        }
        return errors
    }

    suspend fun addVehicle(
        plateNumber: String,
        brand: String,
        model: String,
        year: Int?,
        color: String
    ) {
        val user = sessionManager.getCurrentUser()
            ?: throw IOException("Silakan masuk kembali.")
        val response = api.createVehicle(
            VehicleRequest(
                userId = user.id,
                plateNumber = plateNumber.trim().uppercase(),
                brand = brand.trim(),
                model = model.trim(),
                year = year,
                color = color.trim().ifBlank { "-" }
            )
        )
        val vehicle = response.requireBody("Gagal menyimpan kendaraan").data
            ?: throw ApiException("Server tidak mengirim data kendaraan.")
        database.vehicleDao().insertVehicle(
            VehicleEntity(
                localId = vehicle.id.toString(),
                serverId = vehicle.id,
                userId = vehicle.userId,
                ownerName = vehicle.user?.name ?: user.name,
                plateNumber = vehicle.plateNumber,
                brand = vehicle.brand,
                model = vehicle.model,
                year = vehicle.year ?: year ?: 2024,
                color = vehicle.color ?: color,
                isSynced = true
            )
        )
    }

    suspend fun addBooking(
        vehicle: VehicleEntity,
        service: ServiceEntity,
        date: String,
        time: String,
        complaint: String
    ) {
        val user = sessionManager.getCurrentUser()
            ?: throw IOException("Silakan masuk kembali.")
        val vehicleId = vehicle.serverId
            ?: throw IOException("Kendaraan harus disinkronkan sebelum membuat booking.")
        val response = api.createBooking(
            BookingRequest(
                userId = user.id,
                vehicleId = vehicleId,
                serviceId = service.id,
                bookingDate = date,
                bookingTime = time,
                complaint = complaint.trim().ifBlank { "-" }
            )
        )
        val booking = response.requireBody("Gagal membuat booking").data
            ?: throw ApiException("Server tidak mengirim data booking.")
        database.bookingDao().insertBooking(
            BookingEntity(
                localId = "server-${booking.id}",
                serverId = booking.id,
                userId = booking.userId,
                userName = booking.user?.name ?: user.name,
                vehicleId = booking.vehicleId ?: vehicleId,
                vehiclePlate = booking.vehicle?.plateNumber ?: vehicle.plateNumber,
                serviceId = booking.serviceId,
                serviceName = booking.service?.name ?: service.name,
                bookingDate = booking.bookingDate,
                bookingTime = booking.bookingTime ?: time,
                complaint = booking.complaint ?: complaint,
                status = booking.status,
                isSynced = true
            )
        )
    }

    suspend fun checkout(
        cart: List<CartLine>,
        paidAmount: Double,
        paymentMethod: String,
        customerId: Long? = null
    ): CheckoutResult {
        require(cart.isNotEmpty()) { "Keranjang masih kosong." }
        val subtotal = cart.sumOf { it.product.harga * it.quantity }
        require(paidAmount >= subtotal) { "Uang yang dibayar kurang dari total belanja." }
        cart.forEach { line ->
            require(line.quantity > 0 && line.quantity <= line.product.stok) {
                "Stok ${line.product.name} tidak mencukupi."
            }
            require(line.product.id > 0) {
                "Produk baru yang dibuat offline harus disinkronkan sebelum bisa dijual."
            }
        }

        val user = sessionManager.getCurrentUser()
            ?: throw IOException("Silakan masuk kembali untuk membuat transaksi.")
        val localId = UUID.randomUUID().toString()
        val request = TransactionRequest(
            customerId = customerId,
            items = cart.map { line ->
                TransactionItemDto(
                    productId = line.product.id,
                    itemName = line.product.name,
                    quantity = line.quantity,
                    price = line.product.harga,
                    subtotal = line.product.harga * line.quantity
                )
            },
            subtotal = subtotal,
            totalPayment = subtotal,
            paidAmount = paidAmount,
            paymentMethod = paymentMethod,
            clientUuid = localId
        )

        val transaction = TransactionEntity(
            localId = localId,
            userId = user.id,
            customerId = customerId,
            subtotal = subtotal,
            totalPayment = subtotal,
            paidAmount = paidAmount,
            changeAmount = paidAmount - subtotal,
            paymentMethod = paymentMethod,
            createdAt = SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss'Z'", Locale.US).format(Date())
        )
        database.transactionDao().insertTransaction(transaction)
        database.transactionDao().insertTransactionDetails(
            cart.map { line ->
                TransactionDetailEntity(
                    transactionLocalId = localId,
                    productId = line.product.id,
                    itemName = line.product.name,
                    quantity = line.quantity,
                    price = line.product.harga,
                    subtotal = line.product.harga * line.quantity
                )
            }
        )
        cart.forEach { line ->
            database.productDao().decrementStock(line.product.id, line.quantity)
        }

        return try {
            val response = api.createTransaction(request)
            val body = response.requireBody("Server menolak transaksi")
            database.transactionDao().updateTransaction(
                transaction.copy(serverId = body.data?.id, isSynced = true)
            )
            CheckoutResult(localId, synced = true)
        } catch (error: IOException) {
            database.syncQueueDao().insert(
                SyncQueueEntity(
                    entityType = "transaction",
                    action = "INSERT",
                    entityLocalId = localId,
                    payloadJson = gson.toJson(request),
                    ownerUserId = user.id
                )
            )
            CheckoutResult(localId, synced = false)
        }
    }

    suspend fun placeCustomerOrder(
        cart: List<CartLine>,
        paymentMethod: String,
        clientUuid: String
    ): Long {
        require(cart.isNotEmpty()) { "Keranjang masih kosong." }
        require(cart.all { it.quantity in 1..it.product.stok }) {
            "Jumlah produk melebihi stok yang tersedia."
        }
        val user = sessionManager.getCurrentUser()
            ?: throw IOException("Silakan masuk kembali untuk membuat pesanan.")
        require(user.role == "customer") { "Fitur ini hanya untuk akun pelanggan." }

        val response = api.createCustomerOrder(
            CustomerOrderRequest(
                items = cart.map { CustomerOrderItemRequest(it.product.id, it.quantity) },
                paymentMethod = paymentMethod,
                clientUuid = clientUuid
            )
        )
        val transaction = response.requireBody("Gagal mengirim pesanan").data
            ?: throw ApiException("Server tidak mengirim data pesanan.")
        refreshData()
        return transaction.id
    }

    suspend fun saveProduct(productId: Long?, request: ProductRequest) {
        val localId = productId ?: nextLocalId("products")
        if (productId != null && productId < 0) {
            val pending = database.syncQueueDao().findPending("product", localId.toString())
            if (pending?.action != "INSERT") throw IOException("Produk lokal belum memiliki antrean pembuatan.")
            database.syncQueueDao().update(pending.copy(payloadJson = gson.toJson(request)))
            database.productDao().insertProduct(request.toProductEntity(localId, false))
            return
        }
        try {
            val response = if (productId == null) api.createProduct(request)
            else api.updateProduct(productId, request)
            val product = response.requireBody("Gagal menyimpan produk").data
                ?: throw ApiException("Server tidak mengirim data produk.")
            database.productDao().insertProduct(product.toEntity())
        } catch (error: IOException) {
            val pending = database.syncQueueDao().findPending("product", localId.toString())
            if (pending?.action == "INSERT") database.syncQueueDao().update(pending.copy(payloadJson = gson.toJson(request)))
            else database.syncQueueDao().insert(
                SyncQueueEntity(
                    id = pending?.id ?: 0,
                    entityType = "product",
                    action = if (productId == null) "INSERT" else "UPDATE",
                    entityLocalId = localId.toString(),
                    payloadJson = gson.toJson(request),
                    ownerUserId = sessionManager.getCurrentUser()?.id
                )
            )
            database.productDao().insertProduct(request.toProductEntity(localId, false))
        }
    }

    suspend fun deactivateProduct(product: ProductEntity) {
        val pending = database.syncQueueDao().findPending("product", product.id.toString())
        if (product.id < 0 && pending?.action == "INSERT") {
            database.syncQueueDao().deleteById(pending.id)
            database.productDao().deleteProductById(product.id)
            return
        }
        try {
            api.deactivateProduct(product.id).requireBody("Gagal menonaktifkan produk")
            database.productDao().insertProduct(product.copy(status = "inactive", isSynced = true))
        } catch (error: IOException) {
            database.syncQueueDao().insert(
                SyncQueueEntity(
                    id = pending?.id ?: 0,
                    entityType = "product",
                    action = "DELETE",
                    entityLocalId = product.id.toString(),
                    payloadJson = "{}",
                    ownerUserId = sessionManager.getCurrentUser()?.id
                )
            )
            database.productDao().insertProduct(product.copy(status = "inactive", isSynced = false))
        }
    }

    suspend fun fetchCategories(): List<CategoryDto> = fetchCachedList("categories") {
        api.getCategories().requireBody("Gagal memuat kategori").data
    }

    suspend fun saveCategory(categoryId: Long?, request: CategoryRequest) {
        val localId = categoryId ?: nextLocalId("categories")
        if (categoryId != null && categoryId < 0) {
            val pending = database.syncQueueDao().findPending("category", localId.toString())
                ?: throw IOException("Kategori lokal belum memiliki antrean pembuatan.")
            database.syncQueueDao().update(pending.copy(payloadJson = gson.toJson(request)))
            saveCachedList("categories", fetchCategoriesCache().filterNot { it.id == localId } +
                CategoryDto(localId, request.name, request.description))
            return
        }
        try {
            val response = if (categoryId == null) api.createCategory(request)
            else api.updateCategory(categoryId, request)
            val saved = response.requireBody("Gagal menyimpan kategori").data
                ?: throw ApiException("Server tidak mengirim data kategori.")
            saveCachedList("categories", fetchCategoriesCache().filterNot { it.id == localId || it.id == categoryId } + saved)
        } catch (error: IOException) {
            val pending = database.syncQueueDao().findPending("category", localId.toString())
            if (pending?.action != "INSERT") {
                database.syncQueueDao().insert(
                    SyncQueueEntity(
                        id = pending?.id ?: 0,
                        entityType = "category",
                        action = if (categoryId == null) "INSERT" else "UPDATE",
                        entityLocalId = localId.toString(),
                        payloadJson = gson.toJson(request),
                        ownerUserId = sessionManager.getCurrentUser()?.id
                    )
                )
            } else {
                database.syncQueueDao().update(pending.copy(payloadJson = gson.toJson(request)))
            }
            val categories = fetchCategoriesCache().filterNot { it.id == categoryId || it.id == localId } +
                CategoryDto(localId, request.name, request.description)
            saveCachedList("categories", categories)
        }
    }

    suspend fun deleteCategory(categoryId: Long) {
        require(database.productDao().countActiveProductsInCategory(categoryId) == 0) {
            "Kategori masih digunakan produk aktif dan tidak dapat dihapus."
        }
        val queuedCreate = database.syncQueueDao().findPending("category", categoryId.toString())
        if (queuedCreate?.action == "INSERT") {
            database.syncQueueDao().deleteById(queuedCreate.id)
            saveCachedList("categories", fetchCategoriesCache().filterNot { it.id == categoryId })
            return
        }
        try {
            api.deleteCategory(categoryId).requireBody("Gagal menghapus kategori")
            saveCachedList("categories", fetchCategoriesCache().filterNot { it.id == categoryId })
        } catch (error: IOException) {
            val pending = database.syncQueueDao().findPending("category", categoryId.toString())
            if (pending?.action == "INSERT") database.syncQueueDao().deleteById(pending.id)
            else database.syncQueueDao().insert(
                SyncQueueEntity(
                    id = pending?.id ?: 0,
                    entityType = "category",
                    action = "DELETE",
                    entityLocalId = categoryId.toString(),
                    payloadJson = "{}",
                    ownerUserId = sessionManager.getCurrentUser()?.id
                )
            )
            saveCachedList("categories", fetchCategoriesCache().filterNot { it.id == categoryId })
        }
    }

    suspend fun saveService(serviceId: Long?, request: ServiceRequest) {
        val localId = serviceId ?: nextLocalId("services")
        if (serviceId != null && serviceId < 0) {
            val pending = database.syncQueueDao().findPending("service", localId.toString())
                ?: throw IOException("Layanan lokal belum memiliki antrean pembuatan.")
            database.syncQueueDao().update(pending.copy(payloadJson = gson.toJson(request)))
            database.serviceDao().insertService(
                ServiceEntity(localId, request.name, request.description, request.harga, request.duration, request.status ?: "active", false)
            )
            return
        }
        try {
            val response = if (serviceId == null) api.createService(request)
            else api.updateService(serviceId, request)
            val service = response.requireBody("Gagal menyimpan layanan").data
                ?: throw ApiException("Server tidak mengirim data layanan.")
            database.serviceDao().insertService(
                ServiceEntity(service.id, service.name, service.description, service.harga, service.duration, service.status ?: "active")
            )
        } catch (error: IOException) {
            val pending = database.syncQueueDao().findPending("service", localId.toString())
            val action = if (serviceId == null) "INSERT" else "UPDATE"
            if (pending?.action == "INSERT") database.syncQueueDao().update(pending.copy(payloadJson = gson.toJson(request)))
            else database.syncQueueDao().insert(
                SyncQueueEntity(
                    id = pending?.id ?: 0,
                    entityType = "service",
                    action = action,
                    entityLocalId = localId.toString(),
                    payloadJson = gson.toJson(request),
                    ownerUserId = sessionManager.getCurrentUser()?.id
                )
            )
            database.serviceDao().insertService(
                ServiceEntity(localId, request.name, request.description, request.harga, request.duration, request.status ?: "active", false)
            )
        }
    }

    suspend fun deleteService(service: ServiceEntity) {
        val queuedCreate = database.syncQueueDao().findPending("service", service.id.toString())
        if (queuedCreate?.action == "INSERT") {
            database.syncQueueDao().deleteById(queuedCreate.id)
            database.serviceDao().deleteServiceById(service.id)
            return
        }
        try {
            api.deleteService(service.id).requireBody("Gagal menghapus layanan")
            database.serviceDao().insertService(service.copy(status = "inactive"))
        } catch (error: IOException) {
            val pending = database.syncQueueDao().findPending("service", service.id.toString())
            if (pending?.action == "INSERT") {
                database.syncQueueDao().deleteById(pending.id)
                database.serviceDao().deleteServiceById(service.id)
            } else {
                database.syncQueueDao().insert(
                    SyncQueueEntity(
                        id = pending?.id ?: 0,
                        entityType = "service",
                        action = "DELETE",
                        entityLocalId = service.id.toString(),
                        payloadJson = "{}",
                        ownerUserId = sessionManager.getCurrentUser()?.id
                    )
                )
                database.serviceDao().insertService(service.copy(status = "inactive", isSynced = false))
            }
        }
    }

    suspend fun fetchDashboardStats(): DashboardStatsResponse = try {
        api.getDashboardStats().requireBody("Gagal memuat ringkasan dashboard")
            .also { cache.edit().putString(cacheKey("dashboard"), gson.toJson(it)).apply() }
    } catch (error: IOException) {
        cache.getString(cacheKey("dashboard"), null)?.let {
            gson.fromJson(it, DashboardStatsResponse::class.java)
        } ?: throw IOException("Belum ada ringkasan tersimpan. Sambungkan ke internet dan sinkronkan dahulu.", error)
    }

    suspend fun fetchUsers(role: String? = null): List<com.bengkel.app.data.remote.model.UserDto> {
        val key = "users_${role ?: "all"}"
        return fetchCachedList(key) { api.getUsers(role).requireBody("Gagal memuat pengguna").data }
    }

    suspend fun createUser(name: String, email: String, username: String, phone: String, role: String, password: String) {
        api.createUser(
            mapOf(
                "name" to name.trim(),
                "email" to email.trim(),
                "username" to username.trim(),
                "no_hp" to phone.trim(),
                "role" to role,
                "password" to password
            )
        ).requireBody("Gagal menambahkan pengguna")
    }

    suspend fun updateProfile(name: String, phone: String, password: String?): UserEntity {
        val body = mutableMapOf("name" to name.trim(), "no_hp" to phone.trim())
        password?.takeIf { it.isNotBlank() }?.let { body["password"] = it }
        val user = api.updateProfile(body).requireBody("Gagal memperbarui profil").user
            ?: throw ApiException("Server tidak mengirim data profil.")
        return user.toEntity().also(sessionManager::saveUser)
    }

    suspend fun fetchNotifications(): List<NotificationDto> =
        fetchCachedList("notifications") { api.getNotifications().requireBody("Gagal memuat notifikasi").data }

    suspend fun fetchPayments(): List<PaymentDto> =
        fetchCachedList("payments") { api.getPayments().requireBody("Gagal memuat pembayaran").data }

    suspend fun markNotificationAsRead(notificationId: Long) {
        api.markNotificationAsRead(notificationId).requireBody("Gagal memperbarui notifikasi")
    }

    suspend fun updateBookingStatus(booking: BookingEntity, status: String) {
        try {
            val id = booking.serverId ?: throw IOException("Booking belum tersinkron.")
            api.updateBookingStatus(id, com.bengkel.app.data.remote.model.BookingStatusRequest(status))
                .requireBody("Gagal memperbarui status booking")
            database.bookingDao().updateBooking(booking.copy(status = status, isSynced = true))
        } catch (error: IOException) {
            val id = booking.serverId ?: throw IOException(
                "Booking lokal belum mendukung perubahan status offline; sinkronkan booking terlebih dahulu.", error
            )
            database.bookingDao().updateBooking(booking.copy(status = status, isSynced = false))
            database.syncQueueDao().insert(
                SyncQueueEntity(
                    entityType = "booking_status",
                    action = "UPDATE",
                    entityLocalId = id.toString(),
                    payloadJson = gson.toJson(com.bengkel.app.data.remote.model.BookingStatusRequest(status)),
                    ownerUserId = sessionManager.getCurrentUser()?.id
                )
            )
        }
    }

    suspend fun syncPending(): Int {
        val queueDao = database.syncQueueDao()
        val transactionDao = database.transactionDao()
        val currentUser = sessionManager.getCurrentUser()
            ?: throw IOException("Silakan masuk sebelum menyinkronkan transaksi.")
        val pending = queueDao.getPendingSyncItems()
            .filter { it.ownerUserId == null || it.ownerUserId == currentUser.id }
        var syncedCount = 0

        pending.forEach { item ->
            when (item.entityType) {
                "transaction" -> {
                    val transaction = transactionDao.getTransactionByLocalId(item.entityLocalId)
                        ?: throw ApiException("Transaksi lokal ${item.entityLocalId} tidak ditemukan.")
                    if (transaction.userId != currentUser.id) {
                        throw ApiException("Ada transaksi offline milik akun lain. Masuk dengan akun pembuat transaksi untuk menyinkronkannya.")
                    }
                    val request = gson.fromJson(item.payloadJson, TransactionRequest::class.java)
                    val response = api.createTransaction(request)
                    val body = response.requireBody("Transaksi ${item.entityLocalId} gagal disinkronkan.")
                    transactionDao.updateTransaction(transaction.copy(serverId = body.data?.id, isSynced = true))
                }
                "category" -> {
                    val localId = item.entityLocalId.toLong()
                    if (item.action == "DELETE") api.deleteCategory(localId).requireBody("Kategori gagal dihapus saat sinkronisasi.")
                    else {
                        val request = gson.fromJson(item.payloadJson, CategoryRequest::class.java)
                        val response = if (item.action == "INSERT" || localId < 0) api.createCategory(request)
                        else api.updateCategory(localId, request)
                        val category = response.requireBody("Kategori gagal disinkronkan.").data
                            ?: throw ApiException("Server tidak mengirim kategori tersinkron.")
                        if (localId < 0) {
                            cache.edit().putLong(cacheKey("category_map_$localId"), category.id).apply()
                            cache.edit().putString(cacheKey("categories"), gson.toJson(fetchCategoriesCache().filterNot { it.id == localId } + category)).apply()
                            database.productDao().replaceCategoryId(localId, category.id)
                        } else {
                            saveCachedList("categories", fetchCategoriesCache().filterNot { it.id == localId } + category)
                        }
                    }
                }
                "service" -> {
                    val localId = item.entityLocalId.toLong()
                    if (item.action == "DELETE") {
                        api.deleteService(localId).requireBody("Layanan gagal dihapus saat sinkronisasi.")
                        database.serviceDao().deleteServiceById(localId)
                    } else {
                        val request = gson.fromJson(item.payloadJson, ServiceRequest::class.java)
                        val response = if (item.action == "INSERT" || localId < 0) api.createService(request)
                        else api.updateService(localId, request)
                        val service = response.requireBody("Layanan gagal disinkronkan.").data
                            ?: throw ApiException("Server tidak mengirim layanan tersinkron.")
                        database.serviceDao().deleteServiceById(localId)
                        database.serviceDao().insertService(
                            ServiceEntity(service.id, service.name, service.description, service.harga, service.duration, service.status ?: "active")
                        )
                    }
                }
                "product" -> {
                    val localId = item.entityLocalId.toLong()
                    if (item.action == "DELETE") {
                        api.deactivateProduct(localId).requireBody("Produk gagal dinonaktifkan saat sinkronisasi.")
                        database.productDao().getProductById(localId)?.let {
                            database.productDao().insertProduct(it.copy(status = "inactive", isSynced = true))
                        }
                    } else {
                        val original = gson.fromJson(item.payloadJson, ProductRequest::class.java)
                        val request = original.copy(categoryId = cache.getLong(cacheKey("category_map_${original.categoryId}"), original.categoryId))
                        val response = if (item.action == "INSERT" || localId < 0) api.createProduct(request)
                        else api.updateProduct(localId, request)
                        val product = response.requireBody("Produk gagal disinkronkan.").data
                            ?: throw ApiException("Server tidak mengirim produk tersinkron.")
                        database.productDao().deleteProductById(localId)
                        database.productDao().insertProduct(product.toEntity())
                    }
                }
                "booking_status" -> {
                    val bookingId = item.entityLocalId.toLong()
                    val request = gson.fromJson(item.payloadJson, com.bengkel.app.data.remote.model.BookingStatusRequest::class.java)
                    api.updateBookingStatus(bookingId, request).requireBody("Status booking gagal disinkronkan.")
                    database.bookingDao().getBookingByServerId(bookingId)?.let {
                        database.bookingDao().updateBooking(it.copy(status = request.status, isSynced = true))
                    }
                }
                else -> throw ApiException("Jenis perubahan offline '${item.entityType}' tidak dikenal.")
            }
            queueDao.deleteById(item.id)
            syncedCount++
        }
        return syncedCount
    }

    private suspend inline fun <reified T> fetchCachedList(
        key: String,
        crossinline fetch: suspend () -> List<T>
    ): List<T> = try {
        fetch().also { saveCachedList(key, it) }
    } catch (error: IOException) {
        val json = cache.getString(cacheKey(key), null)
            ?: throw IOException("Belum ada data $key tersimpan. Sinkronkan saat tersambung ke internet.", error)
        gson.fromJson<List<T>>(json, object : TypeToken<List<T>>() {}.type)
    }

    private suspend inline fun <reified T> fetchAndCache(
        key: String,
        crossinline fetch: suspend () -> List<T>
    ) {
        saveCachedList(key, fetch())
    }

    private fun <T> saveCachedList(key: String, values: List<T>) {
        cache.edit().putString(cacheKey(key), gson.toJson(values)).apply()
    }

    private fun fetchCategoriesCache(): List<CategoryDto> {
        val json = cache.getString(cacheKey("categories"), null) ?: return emptyList()
        return gson.fromJson(json, object : TypeToken<List<CategoryDto>>() {}.type)
    }

    private fun nextLocalId(kind: String): Long {
        val key = "next_local_$kind"
        val next = cache.getLong(key, 1L)
        cache.edit().putLong(key, next + 1).apply()
        return -next
    }

    private fun cacheKey(key: String): String =
        "user_${sessionManager.getCurrentUser()?.id ?: "anonymous"}_$key"

    private fun <T : Any> Response<T>.requireBody(message: String): T {
        if (!isSuccessful) {
            val detail = errorBody()?.string()?.takeIf { it.isNotBlank() }
            throw ApiException(detail ?: "$message (HTTP ${code()})")
        }
        return body() ?: throw ApiException("$message: respons server kosong.")
    }
}

private fun ProductDto.toEntity() = ProductEntity(
    id = id,
    categoryId = categoryId,
    categoryName = category?.name,
    name = name,
    description = description,
    harga = harga,
    stok = stok,
    status = status ?: "active",
    photo = photo
)

private fun com.bengkel.app.data.remote.model.UserDto.toEntity() = UserEntity(
    id = id,
    name = name,
    email = email,
    username = username,
    noHp = noHp,
    role = role,
    photo = photo
)
