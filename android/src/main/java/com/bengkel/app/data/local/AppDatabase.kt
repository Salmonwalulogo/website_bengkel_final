package com.bengkel.app.data.local

import android.content.Context
import androidx.room.Database
import androidx.room.Room
import androidx.room.RoomDatabase
import androidx.room.migration.Migration
import androidx.sqlite.db.SupportSQLiteDatabase
import com.bengkel.app.data.local.dao.BookingDao
import com.bengkel.app.data.local.dao.ProductDao
import com.bengkel.app.data.local.dao.ServiceDao
import com.bengkel.app.data.local.dao.SyncQueueDao
import com.bengkel.app.data.local.dao.TransactionDao
import com.bengkel.app.data.local.dao.UserDao
import com.bengkel.app.data.local.dao.VehicleDao
import com.bengkel.app.data.local.entity.BookingEntity
import com.bengkel.app.data.local.entity.ProductEntity
import com.bengkel.app.data.local.entity.ServiceEntity
import com.bengkel.app.data.local.entity.SyncQueueEntity
import com.bengkel.app.data.local.entity.TransactionDetailEntity
import com.bengkel.app.data.local.entity.TransactionEntity
import com.bengkel.app.data.local.entity.UserEntity
import com.bengkel.app.data.local.entity.VehicleEntity

@Database(
    entities = [
        UserEntity::class,
        VehicleEntity::class,
        ServiceEntity::class,
        ProductEntity::class,
        BookingEntity::class,
        TransactionEntity::class,
        TransactionDetailEntity::class,
        SyncQueueEntity::class
    ],
    version = 3,
    exportSchema = false
)
abstract class AppDatabase : RoomDatabase() {

    abstract fun userDao(): UserDao
    abstract fun vehicleDao(): VehicleDao
    abstract fun serviceDao(): ServiceDao
    abstract fun productDao(): ProductDao
    abstract fun bookingDao(): BookingDao
    abstract fun transactionDao(): TransactionDao
    abstract fun syncQueueDao(): SyncQueueDao

    companion object {
        @Volatile
        private var INSTANCE: AppDatabase? = null

        private val MIGRATION_1_2 = object : Migration(1, 2) {
            override fun migrate(database: SupportSQLiteDatabase) {
                database.execSQL("ALTER TABLE sync_queue ADD COLUMN ownerUserId INTEGER")
            }
        }

        private val MIGRATION_2_3 = object : Migration(2, 3) {
            override fun migrate(database: SupportSQLiteDatabase) {
                database.execSQL(
                    """
                    CREATE TABLE bookings_new (
                        localId TEXT NOT NULL PRIMARY KEY,
                        serverId INTEGER,
                        userId INTEGER NOT NULL,
                        userName TEXT,
                        vehicleId INTEGER,
                        vehiclePlate TEXT,
                        serviceId INTEGER NOT NULL,
                        serviceName TEXT,
                        bookingDate TEXT NOT NULL,
                        bookingTime TEXT NOT NULL,
                        complaint TEXT NOT NULL,
                        status TEXT NOT NULL,
                        notes TEXT,
                        isSynced INTEGER NOT NULL
                    )
                    """.trimIndent()
                )
                database.execSQL(
                    """
                    INSERT INTO bookings_new (
                        localId, serverId, userId, userName, vehicleId, vehiclePlate,
                        serviceId, serviceName, bookingDate, bookingTime, complaint,
                        status, notes, isSynced
                    )
                    SELECT localId, serverId, userId, userName, vehicleId, vehiclePlate,
                        serviceId, serviceName, bookingDate, bookingTime, complaint,
                        status, notes, isSynced
                    FROM bookings
                    """.trimIndent()
                )
                database.execSQL("DROP TABLE bookings")
                database.execSQL("ALTER TABLE bookings_new RENAME TO bookings")
            }
        }

        fun getDatabase(context: Context): AppDatabase {
            return INSTANCE ?: synchronized(this) {
                val instance = Room.databaseBuilder(
                    context.applicationContext,
                    AppDatabase::class.java,
                    "bengkel_database"
                )
                    .addMigrations(MIGRATION_1_2)
                    .addMigrations(MIGRATION_2_3)
                    .fallbackToDestructiveMigration()
                    .build()
                INSTANCE = instance
                instance
            }
        }
    }
}
