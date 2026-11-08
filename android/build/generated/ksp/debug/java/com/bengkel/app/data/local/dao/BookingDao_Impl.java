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
import androidx.room.util.CursorUtil;
import androidx.room.util.DBUtil;
import androidx.sqlite.db.SupportSQLiteStatement;
import com.bengkel.app.data.local.entity.BookingEntity;
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
public final class BookingDao_Impl implements BookingDao {
  private final RoomDatabase __db;

  private final EntityInsertionAdapter<BookingEntity> __insertionAdapterOfBookingEntity;

  private final EntityDeletionOrUpdateAdapter<BookingEntity> __updateAdapterOfBookingEntity;

  public BookingDao_Impl(@NonNull final RoomDatabase __db) {
    this.__db = __db;
    this.__insertionAdapterOfBookingEntity = new EntityInsertionAdapter<BookingEntity>(__db) {
      @Override
      @NonNull
      protected String createQuery() {
        return "INSERT OR REPLACE INTO `bookings` (`localId`,`serverId`,`userId`,`userName`,`vehicleId`,`vehiclePlate`,`serviceId`,`serviceName`,`bookingDate`,`bookingTime`,`complaint`,`status`,`notes`,`isSynced`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
      }

      @Override
      protected void bind(@NonNull final SupportSQLiteStatement statement,
          @NonNull final BookingEntity entity) {
        statement.bindString(1, entity.getLocalId());
        if (entity.getServerId() == null) {
          statement.bindNull(2);
        } else {
          statement.bindLong(2, entity.getServerId());
        }
        statement.bindLong(3, entity.getUserId());
        if (entity.getUserName() == null) {
          statement.bindNull(4);
        } else {
          statement.bindString(4, entity.getUserName());
        }
        if (entity.getVehicleId() == null) {
          statement.bindNull(5);
        } else {
          statement.bindLong(5, entity.getVehicleId());
        }
        if (entity.getVehiclePlate() == null) {
          statement.bindNull(6);
        } else {
          statement.bindString(6, entity.getVehiclePlate());
        }
        statement.bindLong(7, entity.getServiceId());
        if (entity.getServiceName() == null) {
          statement.bindNull(8);
        } else {
          statement.bindString(8, entity.getServiceName());
        }
        statement.bindString(9, entity.getBookingDate());
        statement.bindString(10, entity.getBookingTime());
        statement.bindString(11, entity.getComplaint());
        statement.bindString(12, entity.getStatus());
        if (entity.getNotes() == null) {
          statement.bindNull(13);
        } else {
          statement.bindString(13, entity.getNotes());
        }
        final int _tmp = entity.isSynced() ? 1 : 0;
        statement.bindLong(14, _tmp);
      }
    };
    this.__updateAdapterOfBookingEntity = new EntityDeletionOrUpdateAdapter<BookingEntity>(__db) {
      @Override
      @NonNull
      protected String createQuery() {
        return "UPDATE OR ABORT `bookings` SET `localId` = ?,`serverId` = ?,`userId` = ?,`userName` = ?,`vehicleId` = ?,`vehiclePlate` = ?,`serviceId` = ?,`serviceName` = ?,`bookingDate` = ?,`bookingTime` = ?,`complaint` = ?,`status` = ?,`notes` = ?,`isSynced` = ? WHERE `localId` = ?";
      }

      @Override
      protected void bind(@NonNull final SupportSQLiteStatement statement,
          @NonNull final BookingEntity entity) {
        statement.bindString(1, entity.getLocalId());
        if (entity.getServerId() == null) {
          statement.bindNull(2);
        } else {
          statement.bindLong(2, entity.getServerId());
        }
        statement.bindLong(3, entity.getUserId());
        if (entity.getUserName() == null) {
          statement.bindNull(4);
        } else {
          statement.bindString(4, entity.getUserName());
        }
        if (entity.getVehicleId() == null) {
          statement.bindNull(5);
        } else {
          statement.bindLong(5, entity.getVehicleId());
        }
        if (entity.getVehiclePlate() == null) {
          statement.bindNull(6);
        } else {
          statement.bindString(6, entity.getVehiclePlate());
        }
        statement.bindLong(7, entity.getServiceId());
        if (entity.getServiceName() == null) {
          statement.bindNull(8);
        } else {
          statement.bindString(8, entity.getServiceName());
        }
        statement.bindString(9, entity.getBookingDate());
        statement.bindString(10, entity.getBookingTime());
        statement.bindString(11, entity.getComplaint());
        statement.bindString(12, entity.getStatus());
        if (entity.getNotes() == null) {
          statement.bindNull(13);
        } else {
          statement.bindString(13, entity.getNotes());
        }
        final int _tmp = entity.isSynced() ? 1 : 0;
        statement.bindLong(14, _tmp);
        statement.bindString(15, entity.getLocalId());
      }
    };
  }

  @Override
  public Object insertBooking(final BookingEntity booking,
      final Continuation<? super Unit> $completion) {
    return CoroutinesRoom.execute(__db, true, new Callable<Unit>() {
      @Override
      @NonNull
      public Unit call() throws Exception {
        __db.beginTransaction();
        try {
          __insertionAdapterOfBookingEntity.insert(booking);
          __db.setTransactionSuccessful();
          return Unit.INSTANCE;
        } finally {
          __db.endTransaction();
        }
      }
    }, $completion);
  }

  @Override
  public Object insertBookings(final List<BookingEntity> bookings,
      final Continuation<? super Unit> $completion) {
    return CoroutinesRoom.execute(__db, true, new Callable<Unit>() {
      @Override
      @NonNull
      public Unit call() throws Exception {
        __db.beginTransaction();
        try {
          __insertionAdapterOfBookingEntity.insert(bookings);
          __db.setTransactionSuccessful();
          return Unit.INSTANCE;
        } finally {
          __db.endTransaction();
        }
      }
    }, $completion);
  }

  @Override
  public Object updateBooking(final BookingEntity booking,
      final Continuation<? super Unit> $completion) {
    return CoroutinesRoom.execute(__db, true, new Callable<Unit>() {
      @Override
      @NonNull
      public Unit call() throws Exception {
        __db.beginTransaction();
        try {
          __updateAdapterOfBookingEntity.handle(booking);
          __db.setTransactionSuccessful();
          return Unit.INSTANCE;
        } finally {
          __db.endTransaction();
        }
      }
    }, $completion);
  }

  @Override
  public Flow<List<BookingEntity>> getAllBookingsFlow() {
    final String _sql = "SELECT * FROM bookings ORDER BY bookingDate DESC, localId DESC";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 0);
    return CoroutinesRoom.createFlow(__db, false, new String[] {"bookings"}, new Callable<List<BookingEntity>>() {
      @Override
      @NonNull
      public List<BookingEntity> call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfLocalId = CursorUtil.getColumnIndexOrThrow(_cursor, "localId");
          final int _cursorIndexOfServerId = CursorUtil.getColumnIndexOrThrow(_cursor, "serverId");
          final int _cursorIndexOfUserId = CursorUtil.getColumnIndexOrThrow(_cursor, "userId");
          final int _cursorIndexOfUserName = CursorUtil.getColumnIndexOrThrow(_cursor, "userName");
          final int _cursorIndexOfVehicleId = CursorUtil.getColumnIndexOrThrow(_cursor, "vehicleId");
          final int _cursorIndexOfVehiclePlate = CursorUtil.getColumnIndexOrThrow(_cursor, "vehiclePlate");
          final int _cursorIndexOfServiceId = CursorUtil.getColumnIndexOrThrow(_cursor, "serviceId");
          final int _cursorIndexOfServiceName = CursorUtil.getColumnIndexOrThrow(_cursor, "serviceName");
          final int _cursorIndexOfBookingDate = CursorUtil.getColumnIndexOrThrow(_cursor, "bookingDate");
          final int _cursorIndexOfBookingTime = CursorUtil.getColumnIndexOrThrow(_cursor, "bookingTime");
          final int _cursorIndexOfComplaint = CursorUtil.getColumnIndexOrThrow(_cursor, "complaint");
          final int _cursorIndexOfStatus = CursorUtil.getColumnIndexOrThrow(_cursor, "status");
          final int _cursorIndexOfNotes = CursorUtil.getColumnIndexOrThrow(_cursor, "notes");
          final int _cursorIndexOfIsSynced = CursorUtil.getColumnIndexOrThrow(_cursor, "isSynced");
          final List<BookingEntity> _result = new ArrayList<BookingEntity>(_cursor.getCount());
          while (_cursor.moveToNext()) {
            final BookingEntity _item;
            final String _tmpLocalId;
            _tmpLocalId = _cursor.getString(_cursorIndexOfLocalId);
            final Long _tmpServerId;
            if (_cursor.isNull(_cursorIndexOfServerId)) {
              _tmpServerId = null;
            } else {
              _tmpServerId = _cursor.getLong(_cursorIndexOfServerId);
            }
            final long _tmpUserId;
            _tmpUserId = _cursor.getLong(_cursorIndexOfUserId);
            final String _tmpUserName;
            if (_cursor.isNull(_cursorIndexOfUserName)) {
              _tmpUserName = null;
            } else {
              _tmpUserName = _cursor.getString(_cursorIndexOfUserName);
            }
            final Long _tmpVehicleId;
            if (_cursor.isNull(_cursorIndexOfVehicleId)) {
              _tmpVehicleId = null;
            } else {
              _tmpVehicleId = _cursor.getLong(_cursorIndexOfVehicleId);
            }
            final String _tmpVehiclePlate;
            if (_cursor.isNull(_cursorIndexOfVehiclePlate)) {
              _tmpVehiclePlate = null;
            } else {
              _tmpVehiclePlate = _cursor.getString(_cursorIndexOfVehiclePlate);
            }
            final long _tmpServiceId;
            _tmpServiceId = _cursor.getLong(_cursorIndexOfServiceId);
            final String _tmpServiceName;
            if (_cursor.isNull(_cursorIndexOfServiceName)) {
              _tmpServiceName = null;
            } else {
              _tmpServiceName = _cursor.getString(_cursorIndexOfServiceName);
            }
            final String _tmpBookingDate;
            _tmpBookingDate = _cursor.getString(_cursorIndexOfBookingDate);
            final String _tmpBookingTime;
            _tmpBookingTime = _cursor.getString(_cursorIndexOfBookingTime);
            final String _tmpComplaint;
            _tmpComplaint = _cursor.getString(_cursorIndexOfComplaint);
            final String _tmpStatus;
            _tmpStatus = _cursor.getString(_cursorIndexOfStatus);
            final String _tmpNotes;
            if (_cursor.isNull(_cursorIndexOfNotes)) {
              _tmpNotes = null;
            } else {
              _tmpNotes = _cursor.getString(_cursorIndexOfNotes);
            }
            final boolean _tmpIsSynced;
            final int _tmp;
            _tmp = _cursor.getInt(_cursorIndexOfIsSynced);
            _tmpIsSynced = _tmp != 0;
            _item = new BookingEntity(_tmpLocalId,_tmpServerId,_tmpUserId,_tmpUserName,_tmpVehicleId,_tmpVehiclePlate,_tmpServiceId,_tmpServiceName,_tmpBookingDate,_tmpBookingTime,_tmpComplaint,_tmpStatus,_tmpNotes,_tmpIsSynced);
            _result.add(_item);
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

  @Override
  public Flow<List<BookingEntity>> getBookingsByUserIdFlow(final long userId) {
    final String _sql = "SELECT * FROM bookings WHERE userId = ? ORDER BY bookingDate DESC";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 1);
    int _argIndex = 1;
    _statement.bindLong(_argIndex, userId);
    return CoroutinesRoom.createFlow(__db, false, new String[] {"bookings"}, new Callable<List<BookingEntity>>() {
      @Override
      @NonNull
      public List<BookingEntity> call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfLocalId = CursorUtil.getColumnIndexOrThrow(_cursor, "localId");
          final int _cursorIndexOfServerId = CursorUtil.getColumnIndexOrThrow(_cursor, "serverId");
          final int _cursorIndexOfUserId = CursorUtil.getColumnIndexOrThrow(_cursor, "userId");
          final int _cursorIndexOfUserName = CursorUtil.getColumnIndexOrThrow(_cursor, "userName");
          final int _cursorIndexOfVehicleId = CursorUtil.getColumnIndexOrThrow(_cursor, "vehicleId");
          final int _cursorIndexOfVehiclePlate = CursorUtil.getColumnIndexOrThrow(_cursor, "vehiclePlate");
          final int _cursorIndexOfServiceId = CursorUtil.getColumnIndexOrThrow(_cursor, "serviceId");
          final int _cursorIndexOfServiceName = CursorUtil.getColumnIndexOrThrow(_cursor, "serviceName");
          final int _cursorIndexOfBookingDate = CursorUtil.getColumnIndexOrThrow(_cursor, "bookingDate");
          final int _cursorIndexOfBookingTime = CursorUtil.getColumnIndexOrThrow(_cursor, "bookingTime");
          final int _cursorIndexOfComplaint = CursorUtil.getColumnIndexOrThrow(_cursor, "complaint");
          final int _cursorIndexOfStatus = CursorUtil.getColumnIndexOrThrow(_cursor, "status");
          final int _cursorIndexOfNotes = CursorUtil.getColumnIndexOrThrow(_cursor, "notes");
          final int _cursorIndexOfIsSynced = CursorUtil.getColumnIndexOrThrow(_cursor, "isSynced");
          final List<BookingEntity> _result = new ArrayList<BookingEntity>(_cursor.getCount());
          while (_cursor.moveToNext()) {
            final BookingEntity _item;
            final String _tmpLocalId;
            _tmpLocalId = _cursor.getString(_cursorIndexOfLocalId);
            final Long _tmpServerId;
            if (_cursor.isNull(_cursorIndexOfServerId)) {
              _tmpServerId = null;
            } else {
              _tmpServerId = _cursor.getLong(_cursorIndexOfServerId);
            }
            final long _tmpUserId;
            _tmpUserId = _cursor.getLong(_cursorIndexOfUserId);
            final String _tmpUserName;
            if (_cursor.isNull(_cursorIndexOfUserName)) {
              _tmpUserName = null;
            } else {
              _tmpUserName = _cursor.getString(_cursorIndexOfUserName);
            }
            final Long _tmpVehicleId;
            if (_cursor.isNull(_cursorIndexOfVehicleId)) {
              _tmpVehicleId = null;
            } else {
              _tmpVehicleId = _cursor.getLong(_cursorIndexOfVehicleId);
            }
            final String _tmpVehiclePlate;
            if (_cursor.isNull(_cursorIndexOfVehiclePlate)) {
              _tmpVehiclePlate = null;
            } else {
              _tmpVehiclePlate = _cursor.getString(_cursorIndexOfVehiclePlate);
            }
            final long _tmpServiceId;
            _tmpServiceId = _cursor.getLong(_cursorIndexOfServiceId);
            final String _tmpServiceName;
            if (_cursor.isNull(_cursorIndexOfServiceName)) {
              _tmpServiceName = null;
            } else {
              _tmpServiceName = _cursor.getString(_cursorIndexOfServiceName);
            }
            final String _tmpBookingDate;
            _tmpBookingDate = _cursor.getString(_cursorIndexOfBookingDate);
            final String _tmpBookingTime;
            _tmpBookingTime = _cursor.getString(_cursorIndexOfBookingTime);
            final String _tmpComplaint;
            _tmpComplaint = _cursor.getString(_cursorIndexOfComplaint);
            final String _tmpStatus;
            _tmpStatus = _cursor.getString(_cursorIndexOfStatus);
            final String _tmpNotes;
            if (_cursor.isNull(_cursorIndexOfNotes)) {
              _tmpNotes = null;
            } else {
              _tmpNotes = _cursor.getString(_cursorIndexOfNotes);
            }
            final boolean _tmpIsSynced;
            final int _tmp;
            _tmp = _cursor.getInt(_cursorIndexOfIsSynced);
            _tmpIsSynced = _tmp != 0;
            _item = new BookingEntity(_tmpLocalId,_tmpServerId,_tmpUserId,_tmpUserName,_tmpVehicleId,_tmpVehiclePlate,_tmpServiceId,_tmpServiceName,_tmpBookingDate,_tmpBookingTime,_tmpComplaint,_tmpStatus,_tmpNotes,_tmpIsSynced);
            _result.add(_item);
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

  @Override
  public Object getBookingByLocalId(final String localId,
      final Continuation<? super BookingEntity> $completion) {
    final String _sql = "SELECT * FROM bookings WHERE localId = ? LIMIT 1";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 1);
    int _argIndex = 1;
    _statement.bindString(_argIndex, localId);
    final CancellationSignal _cancellationSignal = DBUtil.createCancellationSignal();
    return CoroutinesRoom.execute(__db, false, _cancellationSignal, new Callable<BookingEntity>() {
      @Override
      @Nullable
      public BookingEntity call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfLocalId = CursorUtil.getColumnIndexOrThrow(_cursor, "localId");
          final int _cursorIndexOfServerId = CursorUtil.getColumnIndexOrThrow(_cursor, "serverId");
          final int _cursorIndexOfUserId = CursorUtil.getColumnIndexOrThrow(_cursor, "userId");
          final int _cursorIndexOfUserName = CursorUtil.getColumnIndexOrThrow(_cursor, "userName");
          final int _cursorIndexOfVehicleId = CursorUtil.getColumnIndexOrThrow(_cursor, "vehicleId");
          final int _cursorIndexOfVehiclePlate = CursorUtil.getColumnIndexOrThrow(_cursor, "vehiclePlate");
          final int _cursorIndexOfServiceId = CursorUtil.getColumnIndexOrThrow(_cursor, "serviceId");
          final int _cursorIndexOfServiceName = CursorUtil.getColumnIndexOrThrow(_cursor, "serviceName");
          final int _cursorIndexOfBookingDate = CursorUtil.getColumnIndexOrThrow(_cursor, "bookingDate");
          final int _cursorIndexOfBookingTime = CursorUtil.getColumnIndexOrThrow(_cursor, "bookingTime");
          final int _cursorIndexOfComplaint = CursorUtil.getColumnIndexOrThrow(_cursor, "complaint");
          final int _cursorIndexOfStatus = CursorUtil.getColumnIndexOrThrow(_cursor, "status");
          final int _cursorIndexOfNotes = CursorUtil.getColumnIndexOrThrow(_cursor, "notes");
          final int _cursorIndexOfIsSynced = CursorUtil.getColumnIndexOrThrow(_cursor, "isSynced");
          final BookingEntity _result;
          if (_cursor.moveToFirst()) {
            final String _tmpLocalId;
            _tmpLocalId = _cursor.getString(_cursorIndexOfLocalId);
            final Long _tmpServerId;
            if (_cursor.isNull(_cursorIndexOfServerId)) {
              _tmpServerId = null;
            } else {
              _tmpServerId = _cursor.getLong(_cursorIndexOfServerId);
            }
            final long _tmpUserId;
            _tmpUserId = _cursor.getLong(_cursorIndexOfUserId);
            final String _tmpUserName;
            if (_cursor.isNull(_cursorIndexOfUserName)) {
              _tmpUserName = null;
            } else {
              _tmpUserName = _cursor.getString(_cursorIndexOfUserName);
            }
            final Long _tmpVehicleId;
            if (_cursor.isNull(_cursorIndexOfVehicleId)) {
              _tmpVehicleId = null;
            } else {
              _tmpVehicleId = _cursor.getLong(_cursorIndexOfVehicleId);
            }
            final String _tmpVehiclePlate;
            if (_cursor.isNull(_cursorIndexOfVehiclePlate)) {
              _tmpVehiclePlate = null;
            } else {
              _tmpVehiclePlate = _cursor.getString(_cursorIndexOfVehiclePlate);
            }
            final long _tmpServiceId;
            _tmpServiceId = _cursor.getLong(_cursorIndexOfServiceId);
            final String _tmpServiceName;
            if (_cursor.isNull(_cursorIndexOfServiceName)) {
              _tmpServiceName = null;
            } else {
              _tmpServiceName = _cursor.getString(_cursorIndexOfServiceName);
            }
            final String _tmpBookingDate;
            _tmpBookingDate = _cursor.getString(_cursorIndexOfBookingDate);
            final String _tmpBookingTime;
            _tmpBookingTime = _cursor.getString(_cursorIndexOfBookingTime);
            final String _tmpComplaint;
            _tmpComplaint = _cursor.getString(_cursorIndexOfComplaint);
            final String _tmpStatus;
            _tmpStatus = _cursor.getString(_cursorIndexOfStatus);
            final String _tmpNotes;
            if (_cursor.isNull(_cursorIndexOfNotes)) {
              _tmpNotes = null;
            } else {
              _tmpNotes = _cursor.getString(_cursorIndexOfNotes);
            }
            final boolean _tmpIsSynced;
            final int _tmp;
            _tmp = _cursor.getInt(_cursorIndexOfIsSynced);
            _tmpIsSynced = _tmp != 0;
            _result = new BookingEntity(_tmpLocalId,_tmpServerId,_tmpUserId,_tmpUserName,_tmpVehicleId,_tmpVehiclePlate,_tmpServiceId,_tmpServiceName,_tmpBookingDate,_tmpBookingTime,_tmpComplaint,_tmpStatus,_tmpNotes,_tmpIsSynced);
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
  public Object getBookingByServerId(final long serverId,
      final Continuation<? super BookingEntity> $completion) {
    final String _sql = "SELECT * FROM bookings WHERE serverId = ? LIMIT 1";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 1);
    int _argIndex = 1;
    _statement.bindLong(_argIndex, serverId);
    final CancellationSignal _cancellationSignal = DBUtil.createCancellationSignal();
    return CoroutinesRoom.execute(__db, false, _cancellationSignal, new Callable<BookingEntity>() {
      @Override
      @Nullable
      public BookingEntity call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfLocalId = CursorUtil.getColumnIndexOrThrow(_cursor, "localId");
          final int _cursorIndexOfServerId = CursorUtil.getColumnIndexOrThrow(_cursor, "serverId");
          final int _cursorIndexOfUserId = CursorUtil.getColumnIndexOrThrow(_cursor, "userId");
          final int _cursorIndexOfUserName = CursorUtil.getColumnIndexOrThrow(_cursor, "userName");
          final int _cursorIndexOfVehicleId = CursorUtil.getColumnIndexOrThrow(_cursor, "vehicleId");
          final int _cursorIndexOfVehiclePlate = CursorUtil.getColumnIndexOrThrow(_cursor, "vehiclePlate");
          final int _cursorIndexOfServiceId = CursorUtil.getColumnIndexOrThrow(_cursor, "serviceId");
          final int _cursorIndexOfServiceName = CursorUtil.getColumnIndexOrThrow(_cursor, "serviceName");
          final int _cursorIndexOfBookingDate = CursorUtil.getColumnIndexOrThrow(_cursor, "bookingDate");
          final int _cursorIndexOfBookingTime = CursorUtil.getColumnIndexOrThrow(_cursor, "bookingTime");
          final int _cursorIndexOfComplaint = CursorUtil.getColumnIndexOrThrow(_cursor, "complaint");
          final int _cursorIndexOfStatus = CursorUtil.getColumnIndexOrThrow(_cursor, "status");
          final int _cursorIndexOfNotes = CursorUtil.getColumnIndexOrThrow(_cursor, "notes");
          final int _cursorIndexOfIsSynced = CursorUtil.getColumnIndexOrThrow(_cursor, "isSynced");
          final BookingEntity _result;
          if (_cursor.moveToFirst()) {
            final String _tmpLocalId;
            _tmpLocalId = _cursor.getString(_cursorIndexOfLocalId);
            final Long _tmpServerId;
            if (_cursor.isNull(_cursorIndexOfServerId)) {
              _tmpServerId = null;
            } else {
              _tmpServerId = _cursor.getLong(_cursorIndexOfServerId);
            }
            final long _tmpUserId;
            _tmpUserId = _cursor.getLong(_cursorIndexOfUserId);
            final String _tmpUserName;
            if (_cursor.isNull(_cursorIndexOfUserName)) {
              _tmpUserName = null;
            } else {
              _tmpUserName = _cursor.getString(_cursorIndexOfUserName);
            }
            final Long _tmpVehicleId;
            if (_cursor.isNull(_cursorIndexOfVehicleId)) {
              _tmpVehicleId = null;
            } else {
              _tmpVehicleId = _cursor.getLong(_cursorIndexOfVehicleId);
            }
            final String _tmpVehiclePlate;
            if (_cursor.isNull(_cursorIndexOfVehiclePlate)) {
              _tmpVehiclePlate = null;
            } else {
              _tmpVehiclePlate = _cursor.getString(_cursorIndexOfVehiclePlate);
            }
            final long _tmpServiceId;
            _tmpServiceId = _cursor.getLong(_cursorIndexOfServiceId);
            final String _tmpServiceName;
            if (_cursor.isNull(_cursorIndexOfServiceName)) {
              _tmpServiceName = null;
            } else {
              _tmpServiceName = _cursor.getString(_cursorIndexOfServiceName);
            }
            final String _tmpBookingDate;
            _tmpBookingDate = _cursor.getString(_cursorIndexOfBookingDate);
            final String _tmpBookingTime;
            _tmpBookingTime = _cursor.getString(_cursorIndexOfBookingTime);
            final String _tmpComplaint;
            _tmpComplaint = _cursor.getString(_cursorIndexOfComplaint);
            final String _tmpStatus;
            _tmpStatus = _cursor.getString(_cursorIndexOfStatus);
            final String _tmpNotes;
            if (_cursor.isNull(_cursorIndexOfNotes)) {
              _tmpNotes = null;
            } else {
              _tmpNotes = _cursor.getString(_cursorIndexOfNotes);
            }
            final boolean _tmpIsSynced;
            final int _tmp;
            _tmp = _cursor.getInt(_cursorIndexOfIsSynced);
            _tmpIsSynced = _tmp != 0;
            _result = new BookingEntity(_tmpLocalId,_tmpServerId,_tmpUserId,_tmpUserName,_tmpVehicleId,_tmpVehiclePlate,_tmpServiceId,_tmpServiceName,_tmpBookingDate,_tmpBookingTime,_tmpComplaint,_tmpStatus,_tmpNotes,_tmpIsSynced);
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
  public Flow<Integer> getBookingCountFlow() {
    final String _sql = "SELECT COUNT(*) FROM bookings";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 0);
    return CoroutinesRoom.createFlow(__db, false, new String[] {"bookings"}, new Callable<Integer>() {
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

  @Override
  public Flow<Integer> getActiveBookingCountForUserFlow(final long userId) {
    final String _sql = "SELECT COUNT(*) FROM bookings WHERE userId = ? AND status IN ('pending', 'confirmed', 'in_progress')";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 1);
    int _argIndex = 1;
    _statement.bindLong(_argIndex, userId);
    return CoroutinesRoom.createFlow(__db, false, new String[] {"bookings"}, new Callable<Integer>() {
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

  @Override
  public Flow<Integer> getCompletedBookingCountForUserFlow(final long userId) {
    final String _sql = "SELECT COUNT(*) FROM bookings WHERE userId = ? AND status = 'completed'";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 1);
    int _argIndex = 1;
    _statement.bindLong(_argIndex, userId);
    return CoroutinesRoom.createFlow(__db, false, new String[] {"bookings"}, new Callable<Integer>() {
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

  @Override
  public Object getUnsyncedBookings(final Continuation<? super List<BookingEntity>> $completion) {
    final String _sql = "SELECT * FROM bookings WHERE isSynced = 0";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 0);
    final CancellationSignal _cancellationSignal = DBUtil.createCancellationSignal();
    return CoroutinesRoom.execute(__db, false, _cancellationSignal, new Callable<List<BookingEntity>>() {
      @Override
      @NonNull
      public List<BookingEntity> call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfLocalId = CursorUtil.getColumnIndexOrThrow(_cursor, "localId");
          final int _cursorIndexOfServerId = CursorUtil.getColumnIndexOrThrow(_cursor, "serverId");
          final int _cursorIndexOfUserId = CursorUtil.getColumnIndexOrThrow(_cursor, "userId");
          final int _cursorIndexOfUserName = CursorUtil.getColumnIndexOrThrow(_cursor, "userName");
          final int _cursorIndexOfVehicleId = CursorUtil.getColumnIndexOrThrow(_cursor, "vehicleId");
          final int _cursorIndexOfVehiclePlate = CursorUtil.getColumnIndexOrThrow(_cursor, "vehiclePlate");
          final int _cursorIndexOfServiceId = CursorUtil.getColumnIndexOrThrow(_cursor, "serviceId");
          final int _cursorIndexOfServiceName = CursorUtil.getColumnIndexOrThrow(_cursor, "serviceName");
          final int _cursorIndexOfBookingDate = CursorUtil.getColumnIndexOrThrow(_cursor, "bookingDate");
          final int _cursorIndexOfBookingTime = CursorUtil.getColumnIndexOrThrow(_cursor, "bookingTime");
          final int _cursorIndexOfComplaint = CursorUtil.getColumnIndexOrThrow(_cursor, "complaint");
          final int _cursorIndexOfStatus = CursorUtil.getColumnIndexOrThrow(_cursor, "status");
          final int _cursorIndexOfNotes = CursorUtil.getColumnIndexOrThrow(_cursor, "notes");
          final int _cursorIndexOfIsSynced = CursorUtil.getColumnIndexOrThrow(_cursor, "isSynced");
          final List<BookingEntity> _result = new ArrayList<BookingEntity>(_cursor.getCount());
          while (_cursor.moveToNext()) {
            final BookingEntity _item;
            final String _tmpLocalId;
            _tmpLocalId = _cursor.getString(_cursorIndexOfLocalId);
            final Long _tmpServerId;
            if (_cursor.isNull(_cursorIndexOfServerId)) {
              _tmpServerId = null;
            } else {
              _tmpServerId = _cursor.getLong(_cursorIndexOfServerId);
            }
            final long _tmpUserId;
            _tmpUserId = _cursor.getLong(_cursorIndexOfUserId);
            final String _tmpUserName;
            if (_cursor.isNull(_cursorIndexOfUserName)) {
              _tmpUserName = null;
            } else {
              _tmpUserName = _cursor.getString(_cursorIndexOfUserName);
            }
            final Long _tmpVehicleId;
            if (_cursor.isNull(_cursorIndexOfVehicleId)) {
              _tmpVehicleId = null;
            } else {
              _tmpVehicleId = _cursor.getLong(_cursorIndexOfVehicleId);
            }
            final String _tmpVehiclePlate;
            if (_cursor.isNull(_cursorIndexOfVehiclePlate)) {
              _tmpVehiclePlate = null;
            } else {
              _tmpVehiclePlate = _cursor.getString(_cursorIndexOfVehiclePlate);
            }
            final long _tmpServiceId;
            _tmpServiceId = _cursor.getLong(_cursorIndexOfServiceId);
            final String _tmpServiceName;
            if (_cursor.isNull(_cursorIndexOfServiceName)) {
              _tmpServiceName = null;
            } else {
              _tmpServiceName = _cursor.getString(_cursorIndexOfServiceName);
            }
            final String _tmpBookingDate;
            _tmpBookingDate = _cursor.getString(_cursorIndexOfBookingDate);
            final String _tmpBookingTime;
            _tmpBookingTime = _cursor.getString(_cursorIndexOfBookingTime);
            final String _tmpComplaint;
            _tmpComplaint = _cursor.getString(_cursorIndexOfComplaint);
            final String _tmpStatus;
            _tmpStatus = _cursor.getString(_cursorIndexOfStatus);
            final String _tmpNotes;
            if (_cursor.isNull(_cursorIndexOfNotes)) {
              _tmpNotes = null;
            } else {
              _tmpNotes = _cursor.getString(_cursorIndexOfNotes);
            }
            final boolean _tmpIsSynced;
            final int _tmp;
            _tmp = _cursor.getInt(_cursorIndexOfIsSynced);
            _tmpIsSynced = _tmp != 0;
            _item = new BookingEntity(_tmpLocalId,_tmpServerId,_tmpUserId,_tmpUserName,_tmpVehicleId,_tmpVehiclePlate,_tmpServiceId,_tmpServiceName,_tmpBookingDate,_tmpBookingTime,_tmpComplaint,_tmpStatus,_tmpNotes,_tmpIsSynced);
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

  @NonNull
  public static List<Class<?>> getRequiredConverters() {
    return Collections.emptyList();
  }
}
