package com.bengkel.app.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey
import java.util.UUID

@Entity(tableName = "bookings")
data class BookingEntity(
    @PrimaryKey val localId: String = UUID.randomUUID().toString(),
    val serverId: Long? = null,
    val userId: Long,
    val userName: String? = null,
    val vehicleId: Long?,
    val vehiclePlate: String? = null,
    val serviceId: Long,
    val serviceName: String? = null,
    val bookingDate: String,
    val bookingTime: String = "09:00",
    val complaint: String = "-",
    val status: String = "pending", // "pending", "confirmed", "in_progress", "completed", "cancelled"
    val notes: String? = null,
    val isSynced: Boolean = false
)
