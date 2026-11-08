package com.bengkel.app.data.remote.model

import com.google.gson.annotations.SerializedName

data class LoginRequest(
    val email: String? = null,
    val username: String? = null,
    val password: String
)

data class RegisterRequest(
    val name: String,
    val email: String,
    val username: String? = null,
    @SerializedName("no_hp") val noHp: String? = null,
    val password: String
)

data class AuthResponse(
    val message: String,
    val token: String? = null,
    val user: UserDto? = null
)

data class UserDto(
    val id: Long,
    val name: String,
    val email: String,
    val username: String,
    val role: String,
    @SerializedName("no_hp") val noHp: String? = null,
    val photo: String? = null
)

data class VehicleDto(
    val id: Long,
    @SerializedName("user_id") val userId: Long,
    @SerializedName("plate_number") val plateNumber: String,
    val brand: String,
    val model: String,
    val year: Int? = null,
    val color: String? = null,
    val user: UserDto? = null
)

data class VehicleRequest(
    @SerializedName("user_id") val userId: Long? = null,
    @SerializedName("plate_number") val plateNumber: String,
    val brand: String,
    val model: String,
    val year: Int? = null,
    val color: String? = null
)

data class ServiceDto(
    val id: Long,
    val name: String,
    val description: String? = null,
    val harga: Double,
    val duration: String? = null,
    val status: String? = "active"
)

data class ServiceRequest(
    val name: String,
    val description: String? = null,
    val harga: Double,
    val duration: String? = null,
    val status: String? = "active"
)

data class ProductDto(
    val id: Long,
    @SerializedName("category_id") val categoryId: Long? = null,
    val name: String,
    @SerializedName("product_code") val productCode: String? = null,
    val description: String? = null,
    val harga: Double,
    val stok: Int,
    val status: String? = "active",
    @SerializedName("is_active") val isActive: Boolean? = true,
    @SerializedName(value = "foto", alternate = ["photo"]) val photo: String? = null,
    val category: CategoryDto? = null
)

data class ProductPaginatedResponse(
    val data: List<ProductDto>,
    val meta: Map<String, Any>? = null
)

data class ProductRequest(
    @SerializedName("category_id") val categoryId: Long,
    val name: String,
    @SerializedName("product_code") val productCode: String? = null,
    val description: String? = null,
    val harga: Double,
    val stok: Int,
    val status: String = "active"
)

data class CustomerOrderItemRequest(
    @SerializedName("product_id") val productId: Long,
    val quantity: Int
)

data class CustomerOrderRequest(
    val items: List<CustomerOrderItemRequest>,
    @SerializedName("payment_method") val paymentMethod: String,
    @SerializedName("client_uuid") val clientUuid: String
)

data class CategoryDto(val id: Long, val name: String, val description: String? = null)

data class CategoryRequest(val name: String, val description: String? = null)

data class NotificationDto(
    val id: Long,
    val title: String,
    val message: String,
    val type: String? = null,
    @SerializedName("is_read") val isRead: Boolean,
    @SerializedName("created_at") val createdAt: String? = null
)

data class PaymentDto(
    val id: Long,
    @SerializedName("transaction_id") val transactionId: Long,
    @SerializedName("invoice_number") val invoiceNumber: String? = null,
    @SerializedName("payment_code") val paymentCode: String,
    val amount: Double,
    val status: String,
    @SerializedName("payment_date") val paymentDate: String? = null
)

data class BookingDto(
    val id: Long,
    @SerializedName("user_id") val userId: Long,
    @SerializedName("vehicle_id") val vehicleId: Long?,
    @SerializedName("service_id") val serviceId: Long,
    @SerializedName("booking_date") val bookingDate: String,
    @SerializedName("booking_time") val bookingTime: String? = null,
    @SerializedName(value = "request_details", alternate = ["complaint"])
    val complaint: String? = null,
    val status: String,
    val notes: String? = null,
    val user: UserDto? = null,
    val vehicle: VehicleDto? = null,
    val service: ServiceDto? = null
)

data class BookingRequest(
    @SerializedName("user_id") val userId: Long? = null,
    @SerializedName("vehicle_id") val vehicleId: Long? = null,
    @SerializedName("service_id") val serviceId: Long,
    @SerializedName("booking_date") val bookingDate: String,
    @SerializedName("booking_time") val bookingTime: String? = null,
    val complaint: String? = null
)

data class BookingStatusRequest(
    val status: String,
    val notes: String? = null
)

data class TransactionItemDto(
    @SerializedName("product_id") val productId: Long? = null,
    @SerializedName(value = "item_name", alternate = ["product_name", "name"])
    val itemName: String,
    val quantity: Int,
    val price: Double,
    val subtotal: Double? = null
)

data class TransactionDto(
    val id: Long,
    @SerializedName("user_id") val userId: Long,
    @SerializedName("customer_id") val customerId: Long? = null,
    @SerializedName("checkout_token") val checkoutToken: String? = null,
    val subtotal: Double,
    val discount: Double = 0.0,
    val tax: Double = 0.0,
    @SerializedName("total_payment") val totalPayment: Double,
    @SerializedName("paid_amount") val paidAmount: Double,
    @SerializedName("change_amount") val changeAmount: Double = 0.0,
    val status: String,
    @SerializedName("payment_method") val paymentMethod: String,
    @SerializedName("created_at") val createdAt: String? = null,
    val details: List<TransactionItemDto>? = null,
    val user: UserDto? = null,
    val customer: UserDto? = null
)

data class TransactionRequest(
    @SerializedName("customer_id") val customerId: Long? = null,
    val items: List<TransactionItemDto>,
    val subtotal: Double,
    val discount: Double = 0.0,
    val tax: Double = 0.0,
    @SerializedName("total_payment") val totalPayment: Double,
    @SerializedName("paid_amount") val paidAmount: Double,
    @SerializedName("payment_method") val paymentMethod: String,
    @SerializedName("client_uuid") val clientUuid: String? = null
)

data class BatchSyncRequest(
    val vehicles: List<Map<String, Any?>> = emptyList(),
    val bookings: List<Map<String, Any?>> = emptyList(),
    val transactions: List<Map<String, Any?>> = emptyList()
)

data class SyncedMappingDto(
    @SerializedName("client_id") val clientId: String? = null,
    @SerializedName("server_id") val serverId: Long
)

data class SyncedItemsDto(
    val vehicles: List<SyncedMappingDto> = emptyList(),
    val bookings: List<SyncedMappingDto> = emptyList(),
    val transactions: List<SyncedMappingDto> = emptyList()
)

data class BatchSyncResponse(
    val message: String,
    val synced: SyncedItemsDto? = null
)

data class GenericResponse(
    val message: String? = null,
    val data: Any? = null
)

data class DashboardStatsResponse(
    @SerializedName("total_customers") val totalCustomers: Int? = null,
    @SerializedName("total_vehicles") val totalVehicles: Int? = null,
    @SerializedName("total_bookings") val totalBookings: Int? = null,
    @SerializedName("total_transactions") val totalTransactions: Int? = null,
    @SerializedName("total_income") val totalIncome: Double? = null,
    @SerializedName("vehicles_count") val vehiclesCount: Int? = null,
    @SerializedName("active_bookings") val activeBookings: Int? = null,
    @SerializedName("completed_bookings") val completedBookings: Int? = null,
    @SerializedName("pending_orders") val pendingOrders: Int? = null,
    @SerializedName("today_payments") val todayPayments: Int? = null
)

data class ListResponse<T>(
    val data: List<T>
)

data class SingleResponse<T>(
    val message: String? = null,
    val data: T? = null,
    val user: UserDto? = null
)
