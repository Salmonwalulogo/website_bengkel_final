package com.bengkel.app.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import androidx.room.Update
import com.bengkel.app.data.local.entity.BookingEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface BookingDao {
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertBooking(booking: BookingEntity)

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertBookings(bookings: List<BookingEntity>)

    @Update
    suspend fun updateBooking(booking: BookingEntity)

    @Query("SELECT * FROM bookings ORDER BY bookingDate DESC, localId DESC")
    fun getAllBookingsFlow(): Flow<List<BookingEntity>>

    @Query("SELECT * FROM bookings WHERE userId = :userId ORDER BY bookingDate DESC")
    fun getBookingsByUserIdFlow(userId: Long): Flow<List<BookingEntity>>

    @Query("SELECT * FROM bookings WHERE localId = :localId LIMIT 1")
    suspend fun getBookingByLocalId(localId: String): BookingEntity?

    @Query("SELECT * FROM bookings WHERE serverId = :serverId LIMIT 1")
    suspend fun getBookingByServerId(serverId: Long): BookingEntity?

    @Query("SELECT COUNT(*) FROM bookings")
    fun getBookingCountFlow(): Flow<Int>

    @Query("SELECT COUNT(*) FROM bookings WHERE userId = :userId AND status IN ('pending', 'confirmed', 'in_progress')")
    fun getActiveBookingCountForUserFlow(userId: Long): Flow<Int>

    @Query("SELECT COUNT(*) FROM bookings WHERE userId = :userId AND status = 'completed'")
    fun getCompletedBookingCountForUserFlow(userId: Long): Flow<Int>

    @Query("SELECT * FROM bookings WHERE isSynced = 0")
    suspend fun getUnsyncedBookings(): List<BookingEntity>
}
