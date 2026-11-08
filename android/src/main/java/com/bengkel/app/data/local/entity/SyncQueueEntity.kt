package com.bengkel.app.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "sync_queue")
data class SyncQueueEntity(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val entityType: String, // "vehicle", "booking", "transaction", "service", "user"
    val action: String, // "INSERT", "UPDATE", "DELETE"
    val entityLocalId: String,
    val payloadJson: String,
    val status: String = "pending", // "pending", "syncing", "synced", "failed"
    val attempts: Int = 0,
    val errorMessage: String? = null,
    val createdAt: Long = System.currentTimeMillis(),
    val ownerUserId: Long? = null
)
