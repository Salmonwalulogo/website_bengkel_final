package com.bengkel.app.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import androidx.room.Update
import com.bengkel.app.data.local.entity.SyncQueueEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface SyncQueueDao {
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insert(syncItem: SyncQueueEntity)

    @Update
    suspend fun update(syncItem: SyncQueueEntity)

    @Query("SELECT * FROM sync_queue WHERE status = 'pending' ORDER BY createdAt ASC")
    suspend fun getPendingSyncItems(): List<SyncQueueEntity>

    @Query("SELECT * FROM sync_queue WHERE entityType = :entityType AND entityLocalId = :entityLocalId AND status = 'pending' ORDER BY createdAt ASC LIMIT 1")
    suspend fun findPending(entityType: String, entityLocalId: String): SyncQueueEntity?

    @Query("SELECT COUNT(*) FROM sync_queue WHERE status = 'pending'")
    fun getPendingCountFlow(): Flow<Int>

    @Query("DELETE FROM sync_queue WHERE id = :id")
    suspend fun deleteById(id: Long)

    @Query("DELETE FROM sync_queue WHERE status = 'synced'")
    suspend fun deleteSynced()
}
