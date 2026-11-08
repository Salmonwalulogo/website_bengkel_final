package com.bengkel.app.data.local.dao;

import android.database.Cursor;
import android.os.CancellationSignal;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.room.CoroutinesRoom;
import androidx.room.EntityDeletionOrUpdateAdapter;
import androidx.room.EntityInsertionAdapter;
import androidx.room.RoomDatabase;
import androidx.room.RoomSQLiteQuery;
import androidx.room.SharedSQLiteStatement;
import androidx.room.util.CursorUtil;
import androidx.room.util.DBUtil;
import androidx.sqlite.db.SupportSQLiteStatement;
import com.bengkel.app.data.local.entity.SyncQueueEntity;
import java.lang.Class;
import java.lang.Exception;
import java.lang.Integer;
import java.lang.Long;
import java.lang.Object;
import java.lang.Override;
import java.lang.String;
import java.lang.SuppressWarnings;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.concurrent.Callable;
import javax.annotation.processing.Generated;
import kotlin.Unit;
import kotlin.coroutines.Continuation;
import kotlinx.coroutines.flow.Flow;

@Generated("androidx.room.RoomProcessor")
@SuppressWarnings({"unchecked", "deprecation"})
public final class SyncQueueDao_Impl implements SyncQueueDao {
  private final RoomDatabase __db;

  private final EntityInsertionAdapter<SyncQueueEntity> __insertionAdapterOfSyncQueueEntity;

  private final EntityDeletionOrUpdateAdapter<SyncQueueEntity> __updateAdapterOfSyncQueueEntity;

  private final SharedSQLiteStatement __preparedStmtOfDeleteById;

  private final SharedSQLiteStatement __preparedStmtOfDeleteSynced;

  public SyncQueueDao_Impl(@NonNull final RoomDatabase __db) {
    this.__db = __db;
    this.__insertionAdapterOfSyncQueueEntity = new EntityInsertionAdapter<SyncQueueEntity>(__db) {
      @Override
      @NonNull
      protected String createQuery() {
        return "INSERT OR REPLACE INTO `sync_queue` (`id`,`entityType`,`action`,`entityLocalId`,`payloadJson`,`status`,`attempts`,`errorMessage`,`createdAt`,`ownerUserId`) VALUES (nullif(?, 0),?,?,?,?,?,?,?,?,?)";
      }

      @Override
      protected void bind(@NonNull final SupportSQLiteStatement statement,
          @NonNull final SyncQueueEntity entity) {
        statement.bindLong(1, entity.getId());
        statement.bindString(2, entity.getEntityType());
        statement.bindString(3, entity.getAction());
        statement.bindString(4, entity.getEntityLocalId());
        statement.bindString(5, entity.getPayloadJson());
        statement.bindString(6, entity.getStatus());
        statement.bindLong(7, entity.getAttempts());
        if (entity.getErrorMessage() == null) {
          statement.bindNull(8);
        } else {
          statement.bindString(8, entity.getErrorMessage());
        }
        statement.bindLong(9, entity.getCreatedAt());
        if (entity.getOwnerUserId() == null) {
          statement.bindNull(10);
        } else {
          statement.bindLong(10, entity.getOwnerUserId());
        }
      }
    };
    this.__updateAdapterOfSyncQueueEntity = new EntityDeletionOrUpdateAdapter<SyncQueueEntity>(__db) {
      @Override
      @NonNull
      protected String createQuery() {
        return "UPDATE OR ABORT `sync_queue` SET `id` = ?,`entityType` = ?,`action` = ?,`entityLocalId` = ?,`payloadJson` = ?,`status` = ?,`attempts` = ?,`errorMessage` = ?,`createdAt` = ?,`ownerUserId` = ? WHERE `id` = ?";
      }

      @Override
      protected void bind(@NonNull final SupportSQLiteStatement statement,
          @NonNull final SyncQueueEntity entity) {
        statement.bindLong(1, entity.getId());
        statement.bindString(2, entity.getEntityType());
        statement.bindString(3, entity.getAction());
        statement.bindString(4, entity.getEntityLocalId());
        statement.bindString(5, entity.getPayloadJson());
        statement.bindString(6, entity.getStatus());
        statement.bindLong(7, entity.getAttempts());
        if (entity.getErrorMessage() == null) {
          statement.bindNull(8);
        } else {
          statement.bindString(8, entity.getErrorMessage());
        }
        statement.bindLong(9, entity.getCreatedAt());
        if (entity.getOwnerUserId() == null) {
          statement.bindNull(10);
        } else {
          statement.bindLong(10, entity.getOwnerUserId());
        }
        statement.bindLong(11, entity.getId());
      }
    };
    this.__preparedStmtOfDeleteById = new SharedSQLiteStatement(__db) {
      @Override
      @NonNull
      public String createQuery() {
        final String _query = "DELETE FROM sync_queue WHERE id = ?";
        return _query;
      }
    };
    this.__preparedStmtOfDeleteSynced = new SharedSQLiteStatement(__db) {
      @Override
      @NonNull
      public String createQuery() {
        final String _query = "DELETE FROM sync_queue WHERE status = 'synced'";
        return _query;
      }
    };
  }

  @Override
  public Object insert(final SyncQueueEntity syncItem,
      final Continuation<? super Unit> $completion) {
    return CoroutinesRoom.execute(__db, true, new Callable<Unit>() {
      @Override
      @NonNull
      public Unit call() throws Exception {
        __db.beginTransaction();
        try {
          __insertionAdapterOfSyncQueueEntity.insert(syncItem);
          __db.setTransactionSuccessful();
          return Unit.INSTANCE;
        } finally {
          __db.endTransaction();
        }
      }
    }, $completion);
  }

  @Override
  public Object update(final SyncQueueEntity syncItem,
      final Continuation<? super Unit> $completion) {
    return CoroutinesRoom.execute(__db, true, new Callable<Unit>() {
      @Override
      @NonNull
      public Unit call() throws Exception {
        __db.beginTransaction();
        try {
          __updateAdapterOfSyncQueueEntity.handle(syncItem);
          __db.setTransactionSuccessful();
          return Unit.INSTANCE;
        } finally {
          __db.endTransaction();
        }
      }
    }, $completion);
  }

  @Override
  public Object deleteById(final long id, final Continuation<? super Unit> $completion) {
    return CoroutinesRoom.execute(__db, true, new Callable<Unit>() {
      @Override
      @NonNull
      public Unit call() throws Exception {
        final SupportSQLiteStatement _stmt = __preparedStmtOfDeleteById.acquire();
        int _argIndex = 1;
        _stmt.bindLong(_argIndex, id);
        try {
          __db.beginTransaction();
          try {
            _stmt.executeUpdateDelete();
            __db.setTransactionSuccessful();
            return Unit.INSTANCE;
          } finally {
            __db.endTransaction();
          }
        } finally {
          __preparedStmtOfDeleteById.release(_stmt);
        }
      }
    }, $completion);
  }

  @Override
  public Object deleteSynced(final Continuation<? super Unit> $completion) {
    return CoroutinesRoom.execute(__db, true, new Callable<Unit>() {
      @Override
      @NonNull
      public Unit call() throws Exception {
        final SupportSQLiteStatement _stmt = __preparedStmtOfDeleteSynced.acquire();
        try {
          __db.beginTransaction();
          try {
            _stmt.executeUpdateDelete();
            __db.setTransactionSuccessful();
            return Unit.INSTANCE;
          } finally {
            __db.endTransaction();
          }
        } finally {
          __preparedStmtOfDeleteSynced.release(_stmt);
        }
      }
    }, $completion);
  }

  @Override
  public Object getPendingSyncItems(final Continuation<? super List<SyncQueueEntity>> $completion) {
    final String _sql = "SELECT * FROM sync_queue WHERE status = 'pending' ORDER BY createdAt ASC";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 0);
    final CancellationSignal _cancellationSignal = DBUtil.createCancellationSignal();
    return CoroutinesRoom.execute(__db, false, _cancellationSignal, new Callable<List<SyncQueueEntity>>() {
      @Override
      @NonNull
      public List<SyncQueueEntity> call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfId = CursorUtil.getColumnIndexOrThrow(_cursor, "id");
          final int _cursorIndexOfEntityType = CursorUtil.getColumnIndexOrThrow(_cursor, "entityType");
          final int _cursorIndexOfAction = CursorUtil.getColumnIndexOrThrow(_cursor, "action");
          final int _cursorIndexOfEntityLocalId = CursorUtil.getColumnIndexOrThrow(_cursor, "entityLocalId");
          final int _cursorIndexOfPayloadJson = CursorUtil.getColumnIndexOrThrow(_cursor, "payloadJson");
          final int _cursorIndexOfStatus = CursorUtil.getColumnIndexOrThrow(_cursor, "status");
          final int _cursorIndexOfAttempts = CursorUtil.getColumnIndexOrThrow(_cursor, "attempts");
          final int _cursorIndexOfErrorMessage = CursorUtil.getColumnIndexOrThrow(_cursor, "errorMessage");
          final int _cursorIndexOfCreatedAt = CursorUtil.getColumnIndexOrThrow(_cursor, "createdAt");
          final int _cursorIndexOfOwnerUserId = CursorUtil.getColumnIndexOrThrow(_cursor, "ownerUserId");
          final List<SyncQueueEntity> _result = new ArrayList<SyncQueueEntity>(_cursor.getCount());
          while (_cursor.moveToNext()) {
            final SyncQueueEntity _item;
            final long _tmpId;
            _tmpId = _cursor.getLong(_cursorIndexOfId);
            final String _tmpEntityType;
            _tmpEntityType = _cursor.getString(_cursorIndexOfEntityType);
            final String _tmpAction;
            _tmpAction = _cursor.getString(_cursorIndexOfAction);
            final String _tmpEntityLocalId;
            _tmpEntityLocalId = _cursor.getString(_cursorIndexOfEntityLocalId);
            final String _tmpPayloadJson;
            _tmpPayloadJson = _cursor.getString(_cursorIndexOfPayloadJson);
            final String _tmpStatus;
            _tmpStatus = _cursor.getString(_cursorIndexOfStatus);
            final int _tmpAttempts;
            _tmpAttempts = _cursor.getInt(_cursorIndexOfAttempts);
            final String _tmpErrorMessage;
            if (_cursor.isNull(_cursorIndexOfErrorMessage)) {
              _tmpErrorMessage = null;
            } else {
              _tmpErrorMessage = _cursor.getString(_cursorIndexOfErrorMessage);
            }
            final long _tmpCreatedAt;
            _tmpCreatedAt = _cursor.getLong(_cursorIndexOfCreatedAt);
            final Long _tmpOwnerUserId;
            if (_cursor.isNull(_cursorIndexOfOwnerUserId)) {
              _tmpOwnerUserId = null;
            } else {
              _tmpOwnerUserId = _cursor.getLong(_cursorIndexOfOwnerUserId);
            }
            _item = new SyncQueueEntity(_tmpId,_tmpEntityType,_tmpAction,_tmpEntityLocalId,_tmpPayloadJson,_tmpStatus,_tmpAttempts,_tmpErrorMessage,_tmpCreatedAt,_tmpOwnerUserId);
            _result.add(_item);
          }
          return _result;
        } finally {
          _cursor.close();
          _statement.release();
        }
      }
    }, $completion);
  }

  @Override
  public Object findPending(final String entityType, final String entityLocalId,
      final Continuation<? super SyncQueueEntity> $completion) {
    final String _sql = "SELECT * FROM sync_queue WHERE entityType = ? AND entityLocalId = ? AND status = 'pending' ORDER BY createdAt ASC LIMIT 1";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 2);
    int _argIndex = 1;
    _statement.bindString(_argIndex, entityType);
    _argIndex = 2;
    _statement.bindString(_argIndex, entityLocalId);
    final CancellationSignal _cancellationSignal = DBUtil.createCancellationSignal();
    return CoroutinesRoom.execute(__db, false, _cancellationSignal, new Callable<SyncQueueEntity>() {
      @Override
      @Nullable
      public SyncQueueEntity call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfId = CursorUtil.getColumnIndexOrThrow(_cursor, "id");
          final int _cursorIndexOfEntityType = CursorUtil.getColumnIndexOrThrow(_cursor, "entityType");
          final int _cursorIndexOfAction = CursorUtil.getColumnIndexOrThrow(_cursor, "action");
          final int _cursorIndexOfEntityLocalId = CursorUtil.getColumnIndexOrThrow(_cursor, "entityLocalId");
          final int _cursorIndexOfPayloadJson = CursorUtil.getColumnIndexOrThrow(_cursor, "payloadJson");
          final int _cursorIndexOfStatus = CursorUtil.getColumnIndexOrThrow(_cursor, "status");
          final int _cursorIndexOfAttempts = CursorUtil.getColumnIndexOrThrow(_cursor, "attempts");
          final int _cursorIndexOfErrorMessage = CursorUtil.getColumnIndexOrThrow(_cursor, "errorMessage");
          final int _cursorIndexOfCreatedAt = CursorUtil.getColumnIndexOrThrow(_cursor, "createdAt");
          final int _cursorIndexOfOwnerUserId = CursorUtil.getColumnIndexOrThrow(_cursor, "ownerUserId");
          final SyncQueueEntity _result;
          if (_cursor.moveToFirst()) {
            final long _tmpId;
            _tmpId = _cursor.getLong(_cursorIndexOfId);
            final String _tmpEntityType;
            _tmpEntityType = _cursor.getString(_cursorIndexOfEntityType);
            final String _tmpAction;
            _tmpAction = _cursor.getString(_cursorIndexOfAction);
            final String _tmpEntityLocalId;
            _tmpEntityLocalId = _cursor.getString(_cursorIndexOfEntityLocalId);
            final String _tmpPayloadJson;
            _tmpPayloadJson = _cursor.getString(_cursorIndexOfPayloadJson);
            final String _tmpStatus;
            _tmpStatus = _cursor.getString(_cursorIndexOfStatus);
            final int _tmpAttempts;
            _tmpAttempts = _cursor.getInt(_cursorIndexOfAttempts);
            final String _tmpErrorMessage;
            if (_cursor.isNull(_cursorIndexOfErrorMessage)) {
              _tmpErrorMessage = null;
            } else {
              _tmpErrorMessage = _cursor.getString(_cursorIndexOfErrorMessage);
            }
            final long _tmpCreatedAt;
            _tmpCreatedAt = _cursor.getLong(_cursorIndexOfCreatedAt);
            final Long _tmpOwnerUserId;
            if (_cursor.isNull(_cursorIndexOfOwnerUserId)) {
              _tmpOwnerUserId = null;
            } else {
              _tmpOwnerUserId = _cursor.getLong(_cursorIndexOfOwnerUserId);
            }
            _result = new SyncQueueEntity(_tmpId,_tmpEntityType,_tmpAction,_tmpEntityLocalId,_tmpPayloadJson,_tmpStatus,_tmpAttempts,_tmpErrorMessage,_tmpCreatedAt,_tmpOwnerUserId);
          } else {
            _result = null;
          }
          return _result;
        } finally {
          _cursor.close();
          _statement.release();
        }
      }
    }, $completion);
  }

  @Override
  public Flow<Integer> getPendingCountFlow() {
    final String _sql = "SELECT COUNT(*) FROM sync_queue WHERE status = 'pending'";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 0);
    return CoroutinesRoom.createFlow(__db, false, new String[] {"sync_queue"}, new Callable<Integer>() {
      @Override
      @NonNull
      public Integer call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final Integer _result;
          if (_cursor.moveToFirst()) {
            final int _tmp;
            _tmp = _cursor.getInt(0);
            _result = _tmp;
          } else {
            _result = 0;
          }
          return _result;
        } finally {
          _cursor.close();
        }
      }

      @Override
      protected void finalize() {
        _statement.release();
      }
    });
  }

  @NonNull
  public static List<Class<?>> getRequiredConverters() {
    return Collections.emptyList();
  }
}
