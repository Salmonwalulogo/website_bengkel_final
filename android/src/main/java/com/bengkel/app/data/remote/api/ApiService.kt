package com.bengkel.app.data.remote.api

import com.bengkel.app.data.remote.model.AuthResponse
import com.bengkel.app.data.remote.model.BatchSyncRequest
import com.bengkel.app.data.remote.model.BatchSyncResponse
import com.bengkel.app.data.remote.model.BookingDto
import com.bengkel.app.data.remote.model.BookingRequest
import com.bengkel.app.data.remote.model.BookingStatusRequest
import com.bengkel.app.data.remote.model.CategoryDto
import com.bengkel.app.data.remote.model.CategoryRequest
import com.bengkel.app.data.remote.model.CustomerOrderRequest
import com.bengkel.app.data.remote.model.DashboardStatsResponse
import com.bengkel.app.data.remote.model.GenericResponse
import com.bengkel.app.data.remote.model.ListResponse
import com.bengkel.app.data.remote.model.LoginRequest
import com.bengkel.app.data.remote.model.NotificationDto
import com.bengkel.app.data.remote.model.PaymentDto
import com.bengkel.app.data.remote.model.ProductPaginatedResponse
import com.bengkel.app.data.remote.model.ProductDto
import com.bengkel.app.data.remote.model.ProductRequest
import com.bengkel.app.data.remote.model.RegisterRequest
import com.bengkel.app.data.remote.model.ServiceDto
import com.bengkel.app.data.remote.model.ServiceRequest
import com.bengkel.app.data.remote.model.SingleResponse
import com.bengkel.app.data.remote.model.TransactionDto
import com.bengkel.app.data.remote.model.TransactionRequest
import com.bengkel.app.data.remote.model.UserDto
import com.bengkel.app.data.remote.model.VehicleDto
import com.bengkel.app.data.remote.model.VehicleRequest
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.DELETE
import retrofit2.http.GET
import retrofit2.http.PATCH
import retrofit2.http.POST
import retrofit2.http.PUT
import retrofit2.http.Path
import retrofit2.http.Query

interface ApiService {

    @POST("login")
    suspend fun login(@Body request: LoginRequest): Response<AuthResponse>

    @POST("register")
    suspend fun register(@Body request: RegisterRequest): Response<AuthResponse>

    @GET("me")
    suspend fun me(): Response<SingleResponse<UserDto>>

    @POST("logout")
    suspend fun logout(): Response<GenericResponse>

    @PUT("profile")
    suspend fun updateProfile(@Body body: Map<String, String>): Response<SingleResponse<UserDto>>

    @GET("dashboard/stats")
    suspend fun getDashboardStats(): Response<DashboardStatsResponse>

    // Vehicles
    @GET("vehicles")
    suspend fun getVehicles(@Query("user_id") userId: Long? = null): Response<ListResponse<VehicleDto>>

    @POST("vehicles")
    suspend fun createVehicle(@Body request: VehicleRequest): Response<SingleResponse<VehicleDto>>

    @PUT("vehicles/{id}")
    suspend fun updateVehicle(@Path("id") id: Long, @Body request: VehicleRequest): Response<SingleResponse<VehicleDto>>

    @DELETE("vehicles/{id}")
    suspend fun deleteVehicle(@Path("id") id: Long): Response<GenericResponse>

    // Services
    @GET("services")
    suspend fun getServices(): Response<ListResponse<ServiceDto>>

    @POST("services")
    suspend fun createService(@Body request: ServiceRequest): Response<SingleResponse<ServiceDto>>

    @PUT("services/{id}")
    suspend fun updateService(@Path("id") id: Long, @Body request: ServiceRequest): Response<SingleResponse<ServiceDto>>

    @DELETE("services/{id}")
    suspend fun deleteService(@Path("id") id: Long): Response<GenericResponse>

    // Products
    @GET("products")
    suspend fun getProducts(@Query("all") includeInactive: Boolean = false): Response<ProductPaginatedResponse>

    @GET("categories")
    suspend fun getCategories(): Response<ListResponse<CategoryDto>>

    @POST("categories")
    suspend fun createCategory(@Body request: CategoryRequest): Response<SingleResponse<CategoryDto>>

    @PUT("categories/{id}")
    suspend fun updateCategory(@Path("id") id: Long, @Body request: CategoryRequest): Response<SingleResponse<CategoryDto>>

    @DELETE("categories/{id}")
    suspend fun deleteCategory(@Path("id") id: Long): Response<GenericResponse>

    @POST("products")
    suspend fun createProduct(@Body request: ProductRequest): Response<SingleResponse<ProductDto>>

    @PUT("products/{id}")
    suspend fun updateProduct(@Path("id") id: Long, @Body request: ProductRequest): Response<SingleResponse<ProductDto>>

    @DELETE("products/{id}")
    suspend fun deactivateProduct(@Path("id") id: Long): Response<GenericResponse>

    // Bookings
    @GET("bookings")
    suspend fun getBookings(): Response<ListResponse<BookingDto>>

    @POST("bookings")
    suspend fun createBooking(@Body request: BookingRequest): Response<SingleResponse<BookingDto>>

    @PATCH("bookings/{id}/status")
    suspend fun updateBookingStatus(@Path("id") id: Long, @Body request: BookingStatusRequest): Response<SingleResponse<BookingDto>>

    // Transactions
    @GET("transactions")
    suspend fun getTransactions(): Response<ListResponse<TransactionDto>>

    @GET("transactions/{id}")
    suspend fun getTransaction(@Path("id") id: Long): Response<SingleResponse<TransactionDto>>

    @POST("transactions")
    suspend fun createTransaction(@Body request: TransactionRequest): Response<SingleResponse<TransactionDto>>

    @POST("transactions")
    suspend fun createCustomerOrder(@Body request: CustomerOrderRequest): Response<SingleResponse<TransactionDto>>

    // Users
    @GET("users")
    suspend fun getUsers(@Query("role") role: String? = null): Response<ListResponse<UserDto>>

    @POST("users")
    suspend fun createUser(@Body body: Map<String, String>): Response<SingleResponse<UserDto>>

    @GET("notifications")
    suspend fun getNotifications(): Response<ListResponse<NotificationDto>>

    @POST("notifications/{id}/read")
    suspend fun markNotificationAsRead(@Path("id") id: Long): Response<GenericResponse>

    @GET("payments")
    suspend fun getPayments(): Response<ListResponse<PaymentDto>>

    // Batch Sync
    @POST("sync")
    suspend fun batchSync(@Body request: BatchSyncRequest): Response<BatchSyncResponse>
}
