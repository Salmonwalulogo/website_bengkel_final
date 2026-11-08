package com.bengkel.app.data.local;

import androidx.annotation.NonNull;
import androidx.room.DatabaseConfiguration;
import androidx.room.InvalidationTracker;
import androidx.room.RoomDatabase;
import androidx.room.RoomOpenHelper;
import androidx.room.migration.AutoMigrationSpec;
import androidx.room.migration.Migration;
import androidx.room.util.DBUtil;
import androidx.room.util.TableInfo;
import androidx.sqlite.db.SupportSQLiteDatabase;
import androidx.sqlite.db.SupportSQLiteOpenHelper;
import com.bengkel.app.data.local.dao.BookingDao;
import com.bengkel.app.data.local.dao.BookingDao_Impl;
import com.bengkel.app.data.local.dao.ProductDao;
import com.bengkel.app.data.local.dao.ProductDao_Impl;
import com.bengkel.app.data.local.dao.ServiceDao;
import com.bengkel.app.data.local.dao.ServiceDao_Impl;
import com.bengkel.app.data.local.dao.SyncQueueDao;
import com.bengkel.app.data.local.dao.SyncQueueDao_Impl;
import com.bengkel.app.data.local.dao.TransactionDao;
import com.bengkel.app.data.local.dao.TransactionDao_Impl;
import com.bengkel.app.data.local.dao.UserDao;
import com.bengkel.app.data.local.dao.UserDao_Impl;
import com.bengkel.app.data.local.dao.VehicleDao;
import com.bengkel.app.data.local.dao.VehicleDao_Impl;
import java.lang.Class;
import java.lang.Override;
import java.lang.String;
import java.lang.SuppressWarnings;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;
import javax.annotation.processing.Generated;

@Generated("androidx.room.RoomProcessor")
@SuppressWarnings({"unchecked", "deprecation"})
public final class AppDatabase_Impl extends AppDatabase {
  private volatile UserDao _userDao;

  private volatile VehicleDao _vehicleDao;

  private volatile ServiceDao _serviceDao;

  private volatile ProductDao _productDao;

  private volatile BookingDao _bookingDao;

  private volatile TransactionDao _transactionDao;

  private volatile SyncQueueDao _syncQueueDao;

  @Override
  @NonNull
  protected SupportSQLiteOpenHelper createOpenHelper(@NonNull final DatabaseConfiguration config) {
    final SupportSQLiteOpenHelper.Callback _openCallback = new RoomOpenHelper(config, new RoomOpenHelper.Delegate(3) {
      @Override
      public void createAllTables(@NonNull final SupportSQLiteDatabase db) {
        db.execSQL("CREATE TABLE IF NOT EXISTS `users` (`id` INTEGER NOT NULL, `name` TEXT NOT NULL, `email` TEXT NOT NULL, `username` TEXT NOT NULL, `noHp` TEXT, `role` TEXT NOT NULL, `photo` TEXT, `isSynced` INTEGER NOT NULL, PRIMARY KEY(`id`))");
        db.execSQL("CREATE TABLE IF NOT EXISTS `vehicles` (`localId` TEXT NOT NULL, `serverId` INTEGER, `userId` INTEGER NOT NULL, `ownerName` TEXT, `plateNumber` TEXT NOT NULL, `brand` TEXT NOT NULL, `model` TEXT NOT NULL, `year` INTEGER NOT NULL, `color` TEXT NOT NULL, `photoPath` TEXT, `isSynced` INTEGER NOT NULL, PRIMARY KEY(`localId`))");
        db.execSQL("CREATE TABLE IF NOT EXISTS `services` (`id` INTEGER NOT NULL, `name` TEXT NOT NULL, `description` TEXT, `harga` REAL NOT NULL, `duration` TEXT, `status` TEXT NOT NULL, `isSynced` INTEGER NOT NULL, PRIMARY KEY(`id`))");
        db.execSQL("CREATE TABLE IF NOT EXISTS `products` (`id` INTEGER NOT NULL, `categoryId` INTEGER, `categoryName` TEXT, `name` TEXT NOT NULL, `description` TEXT, `harga` REAL NOT NULL, `stok` INTEGER NOT NULL, `status` TEXT NOT NULL, `photo` TEXT, `isSynced` INTEGER NOT NULL, PRIMARY KEY(`id`))");
        db.execSQL("CREATE TABLE IF NOT EXISTS `bookings` (`localId` TEXT NOT NULL, `serverId` INTEGER, `userId` INTEGER NOT NULL, `userName` TEXT, `vehicleId` INTEGER, `vehiclePlate` TEXT, `serviceId` INTEGER NOT NULL, `serviceName` TEXT, `bookingDate` TEXT NOT NULL, `bookingTime` TEXT NOT NULL, `complaint` TEXT NOT NULL, `status` TEXT NOT NULL, `notes` TEXT, `isSynced` INTEGER NOT NULL, PRIMARY KEY(`localId`))");
        db.execSQL("CREATE TABLE IF NOT EXISTS `transactions` (`localId` TEXT NOT NULL, `serverId` INTEGER, `userId` INTEGER NOT NULL, `customerId` INTEGER, `customerName` TEXT, `subtotal` REAL NOT NULL, `discount` REAL NOT NULL, `tax` REAL NOT NULL, `totalPayment` REAL NOT NULL, `paidAmount` REAL NOT NULL, `changeAmount` REAL NOT NULL, `status` TEXT NOT NULL, `paymentMethod` TEXT NOT NULL, `createdAt` TEXT NOT NULL, `isSynced` INTEGER NOT NULL, PRIMARY KEY(`localId`))");
        db.execSQL("CREATE TABLE IF NOT EXISTS `transaction_details` (`localId` TEXT NOT NULL, `transactionLocalId` TEXT NOT NULL, `productId` INTEGER, `itemName` TEXT NOT NULL, `quantity` INTEGER NOT NULL, `price` REAL NOT NULL, `subtotal` REAL NOT NULL, PRIMARY KEY(`localId`))");
        db.execSQL("CREATE TABLE IF NOT EXISTS `sync_queue` (`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, `entityType` TEXT NOT NULL, `action` TEXT NOT NULL, `entityLocalId` TEXT NOT NULL, `payloadJson` TEXT NOT NULL, `status` TEXT NOT NULL, `attempts` INTEGER NOT NULL, `errorMessage` TEXT, `createdAt` INTEGER NOT NULL, `ownerUserId` INTEGER)");
        db.execSQL("CREATE TABLE IF NOT EXISTS room_master_table (id INTEGER PRIMARY KEY,identity_hash TEXT)");
        db.execSQL("INSERT OR REPLACE INTO room_master_table (id,identity_hash) VALUES(42, '79cdfd6af68a7bf1726104eccd7eb8f8')");
      }

      @Override
      public void dropAllTables(@NonNull final SupportSQLiteDatabase db) {
        db.execSQL("DROP TABLE IF EXISTS `users`");
        db.execSQL("DROP TABLE IF EXISTS `vehicles`");
        db.execSQL("DROP TABLE IF EXISTS `services`");
        db.execSQL("DROP TABLE IF EXISTS `products`");
        db.execSQL("DROP TABLE IF EXISTS `bookings`");
        db.execSQL("DROP TABLE IF EXISTS `transactions`");
        db.execSQL("DROP TABLE IF EXISTS `transaction_details`");
        db.execSQL("DROP TABLE IF EXISTS `sync_queue`");
        final List<? extends RoomDatabase.Callback> _callbacks = mCallbacks;
        if (_callbacks != null) {
          for (RoomDatabase.Callback _callback : _callbacks) {
            _callback.onDestructiveMigration(db);
          }
        }
      }

      @Override
      public void onCreate(@NonNull final SupportSQLiteDatabase db) {
        final List<? extends RoomDatabase.Callback> _callbacks = mCallbacks;
        if (_callbacks != null) {
          for (RoomDatabase.Callback _callback : _callbacks) {
            _callback.onCreate(db);
          }
        }
      }

      @Override
      public void onOpen(@NonNull final SupportSQLiteDatabase db) {
        mDatabase = db;
        internalInitInvalidationTracker(db);
        final List<? extends RoomDatabase.Callback> _callbacks = mCallbacks;
        if (_callbacks != null) {
          for (RoomDatabase.Callback _callback : _callbacks) {
            _callback.onOpen(db);
          }
        }
      }

      @Override
      public void onPreMigrate(@NonNull final SupportSQLiteDatabase db) {
        DBUtil.dropFtsSyncTriggers(db);
      }

      @Override
      public void onPostMigrate(@NonNull final SupportSQLiteDatabase db) {
      }

      @Override
      @NonNull
      public RoomOpenHelper.ValidationResult onValidateSchema(
          @NonNull final SupportSQLiteDatabase db) {
        final HashMap<String, TableInfo.Column> _columnsUsers = new HashMap<String, TableInfo.Column>(8);
        _columnsUsers.put("id", new TableInfo.Column("id", "INTEGER", true, 1, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsUsers.put("name", new TableInfo.Column("name", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsUsers.put("email", new TableInfo.Column("email", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsUsers.put("username", new TableInfo.Column("username", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsUsers.put("noHp", new TableInfo.Column("noHp", "TEXT", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsUsers.put("role", new TableInfo.Column("role", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsUsers.put("photo", new TableInfo.Column("photo", "TEXT", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsUsers.put("isSynced", new TableInfo.Column("isSynced", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        final HashSet<TableInfo.ForeignKey> _foreignKeysUsers = new HashSet<TableInfo.ForeignKey>(0);
        final HashSet<TableInfo.Index> _indicesUsers = new HashSet<TableInfo.Index>(0);
        final TableInfo _infoUsers = new TableInfo("users", _columnsUsers, _foreignKeysUsers, _indicesUsers);
        final TableInfo _existingUsers = TableInfo.read(db, "users");
        if (!_infoUsers.equals(_existingUsers)) {
          return new RoomOpenHelper.ValidationResult(false, "users(com.bengkel.app.data.local.entity.UserEntity).\n"
                  + " Expected:\n" + _infoUsers + "\n"
                  + " Found:\n" + _existingUsers);
        }
        final HashMap<String, TableInfo.Column> _columnsVehicles = new HashMap<String, TableInfo.Column>(11);
        _columnsVehicles.put("localId", new TableInfo.Column("localId", "TEXT", true, 1, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsVehicles.put("serverId", new TableInfo.Column("serverId", "INTEGER", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsVehicles.put("userId", new TableInfo.Column("userId", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsVehicles.put("ownerName", new TableInfo.Column("ownerName", "TEXT", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsVehicles.put("plateNumber", new TableInfo.Column("plateNumber", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsVehicles.put("brand", new TableInfo.Column("brand", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsVehicles.put("model", new TableInfo.Column("model", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsVehicles.put("year", new TableInfo.Column("year", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsVehicles.put("color", new TableInfo.Column("color", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsVehicles.put("photoPath", new TableInfo.Column("photoPath", "TEXT", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsVehicles.put("isSynced", new TableInfo.Column("isSynced", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        final HashSet<TableInfo.ForeignKey> _foreignKeysVehicles = new HashSet<TableInfo.ForeignKey>(0);
        final HashSet<TableInfo.Index> _indicesVehicles = new HashSet<TableInfo.Index>(0);
        final TableInfo _infoVehicles = new TableInfo("vehicles", _columnsVehicles, _foreignKeysVehicles, _indicesVehicles);
        final TableInfo _existingVehicles = TableInfo.read(db, "vehicles");
        if (!_infoVehicles.equals(_existingVehicles)) {
          return new RoomOpenHelper.ValidationResult(false, "vehicles(com.bengkel.app.data.local.entity.VehicleEntity).\n"
                  + " Expected:\n" + _infoVehicles + "\n"
                  + " Found:\n" + _existingVehicles);
        }
        final HashMap<String, TableInfo.Column> _columnsServices = new HashMap<String, TableInfo.Column>(7);
        _columnsServices.put("id", new TableInfo.Column("id", "INTEGER", true, 1, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsServices.put("name", new TableInfo.Column("name", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsServices.put("description", new TableInfo.Column("description", "TEXT", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsServices.put("harga", new TableInfo.Column("harga", "REAL", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsServices.put("duration", new TableInfo.Column("duration", "TEXT", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsServices.put("status", new TableInfo.Column("status", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsServices.put("isSynced", new TableInfo.Column("isSynced", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        final HashSet<TableInfo.ForeignKey> _foreignKeysServices = new HashSet<TableInfo.ForeignKey>(0);
        final HashSet<TableInfo.Index> _indicesServices = new HashSet<TableInfo.Index>(0);
        final TableInfo _infoServices = new TableInfo("services", _columnsServices, _foreignKeysServices, _indicesServices);
        final TableInfo _existingServices = TableInfo.read(db, "services");
        if (!_infoServices.equals(_existingServices)) {
          return new RoomOpenHelper.ValidationResult(false, "services(com.bengkel.app.data.local.entity.ServiceEntity).\n"
                  + " Expected:\n" + _infoServices + "\n"
                  + " Found:\n" + _existingServices);
        }
        final HashMap<String, TableInfo.Column> _columnsProducts = new HashMap<String, TableInfo.Column>(10);
        _columnsProducts.put("id", new TableInfo.Column("id", "INTEGER", true, 1, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsProducts.put("categoryId", new TableInfo.Column("categoryId", "INTEGER", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsProducts.put("categoryName", new TableInfo.Column("categoryName", "TEXT", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsProducts.put("name", new TableInfo.Column("name", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsProducts.put("description", new TableInfo.Column("description", "TEXT", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsProducts.put("harga", new TableInfo.Column("harga", "REAL", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsProducts.put("stok", new TableInfo.Column("stok", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsProducts.put("status", new TableInfo.Column("status", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsProducts.put("photo", new TableInfo.Column("photo", "TEXT", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsProducts.put("isSynced", new TableInfo.Column("isSynced", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        final HashSet<TableInfo.ForeignKey> _foreignKeysProducts = new HashSet<TableInfo.ForeignKey>(0);
        final HashSet<TableInfo.Index> _indicesProducts = new HashSet<TableInfo.Index>(0);
        final TableInfo _infoProducts = new TableInfo("products", _columnsProducts, _foreignKeysProducts, _indicesProducts);
        final TableInfo _existingProducts = TableInfo.read(db, "products");
        if (!_infoProducts.equals(_existingProducts)) {
          return new RoomOpenHelper.ValidationResult(false, "products(com.bengkel.app.data.local.entity.ProductEntity).\n"
                  + " Expected:\n" + _infoProducts + "\n"
                  + " Found:\n" + _existingProducts);
        }
        final HashMap<String, TableInfo.Column> _columnsBookings = new HashMap<String, TableInfo.Column>(14);
        _columnsBookings.put("localId", new TableInfo.Column("localId", "TEXT", true, 1, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsBookings.put("serverId", new TableInfo.Column("serverId", "INTEGER", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsBookings.put("userId", new TableInfo.Column("userId", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsBookings.put("userName", new TableInfo.Column("userName", "TEXT", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsBookings.put("vehicleId", new TableInfo.Column("vehicleId", "INTEGER", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsBookings.put("vehiclePlate", new TableInfo.Column("vehiclePlate", "TEXT", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsBookings.put("serviceId", new TableInfo.Column("serviceId", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsBookings.put("serviceName", new TableInfo.Column("serviceName", "TEXT", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsBookings.put("bookingDate", new TableInfo.Column("bookingDate", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsBookings.put("bookingTime", new TableInfo.Column("bookingTime", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsBookings.put("complaint", new TableInfo.Column("complaint", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsBookings.put("status", new TableInfo.Column("status", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsBookings.put("notes", new TableInfo.Column("notes", "TEXT", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsBookings.put("isSynced", new TableInfo.Column("isSynced", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        final HashSet<TableInfo.ForeignKey> _foreignKeysBookings = new HashSet<TableInfo.ForeignKey>(0);
        final HashSet<TableInfo.Index> _indicesBookings = new HashSet<TableInfo.Index>(0);
        final TableInfo _infoBookings = new TableInfo("bookings", _columnsBookings, _foreignKeysBookings, _indicesBookings);
        final TableInfo _existingBookings = TableInfo.read(db, "bookings");
        if (!_infoBookings.equals(_existingBookings)) {
          return new RoomOpenHelper.ValidationResult(false, "bookings(com.bengkel.app.data.local.entity.BookingEntity).\n"
                  + " Expected:\n" + _infoBookings + "\n"
                  + " Found:\n" + _existingBookings);
        }
        final HashMap<String, TableInfo.Column> _columnsTransactions = new HashMap<String, TableInfo.Column>(15);
        _columnsTransactions.put("localId", new TableInfo.Column("localId", "TEXT", true, 1, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactions.put("serverId", new TableInfo.Column("serverId", "INTEGER", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactions.put("userId", new TableInfo.Column("userId", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactions.put("customerId", new TableInfo.Column("customerId", "INTEGER", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactions.put("customerName", new TableInfo.Column("customerName", "TEXT", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactions.put("subtotal", new TableInfo.Column("subtotal", "REAL", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactions.put("discount", new TableInfo.Column("discount", "REAL", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactions.put("tax", new TableInfo.Column("tax", "REAL", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactions.put("totalPayment", new TableInfo.Column("totalPayment", "REAL", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactions.put("paidAmount", new TableInfo.Column("paidAmount", "REAL", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactions.put("changeAmount", new TableInfo.Column("changeAmount", "REAL", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactions.put("status", new TableInfo.Column("status", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactions.put("paymentMethod", new TableInfo.Column("paymentMethod", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactions.put("createdAt", new TableInfo.Column("createdAt", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactions.put("isSynced", new TableInfo.Column("isSynced", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        final HashSet<TableInfo.ForeignKey> _foreignKeysTransactions = new HashSet<TableInfo.ForeignKey>(0);
        final HashSet<TableInfo.Index> _indicesTransactions = new HashSet<TableInfo.Index>(0);
        final TableInfo _infoTransactions = new TableInfo("transactions", _columnsTransactions, _foreignKeysTransactions, _indicesTransactions);
        final TableInfo _existingTransactions = TableInfo.read(db, "transactions");
        if (!_infoTransactions.equals(_existingTransactions)) {
          return new RoomOpenHelper.ValidationResult(false, "transactions(com.bengkel.app.data.local.entity.TransactionEntity).\n"
                  + " Expected:\n" + _infoTransactions + "\n"
                  + " Found:\n" + _existingTransactions);
        }
        final HashMap<String, TableInfo.Column> _columnsTransactionDetails = new HashMap<String, TableInfo.Column>(7);
        _columnsTransactionDetails.put("localId", new TableInfo.Column("localId", "TEXT", true, 1, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactionDetails.put("transactionLocalId", new TableInfo.Column("transactionLocalId", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactionDetails.put("productId", new TableInfo.Column("productId", "INTEGER", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactionDetails.put("itemName", new TableInfo.Column("itemName", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactionDetails.put("quantity", new TableInfo.Column("quantity", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactionDetails.put("price", new TableInfo.Column("price", "REAL", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsTransactionDetails.put("subtotal", new TableInfo.Column("subtotal", "REAL", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        final HashSet<TableInfo.ForeignKey> _foreignKeysTransactionDetails = new HashSet<TableInfo.ForeignKey>(0);
        final HashSet<TableInfo.Index> _indicesTransactionDetails = new HashSet<TableInfo.Index>(0);
        final TableInfo _infoTransactionDetails = new TableInfo("transaction_details", _columnsTransactionDetails, _foreignKeysTransactionDetails, _indicesTransactionDetails);
        final TableInfo _existingTransactionDetails = TableInfo.read(db, "transaction_details");
        if (!_infoTransactionDetails.equals(_existingTransactionDetails)) {
          return new RoomOpenHelper.ValidationResult(false, "transaction_details(com.bengkel.app.data.local.entity.TransactionDetailEntity).\n"
                  + " Expected:\n" + _infoTransactionDetails + "\n"
                  + " Found:\n" + _existingTransactionDetails);
        }
        final HashMap<String, TableInfo.Column> _columnsSyncQueue = new HashMap<String, TableInfo.Column>(10);
        _columnsSyncQueue.put("id", new TableInfo.Column("id", "INTEGER", true, 1, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsSyncQueue.put("entityType", new TableInfo.Column("entityType", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsSyncQueue.put("action", new TableInfo.Column("action", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsSyncQueue.put("entityLocalId", new TableInfo.Column("entityLocalId", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsSyncQueue.put("payloadJson", new TableInfo.Column("payloadJson", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsSyncQueue.put("status", new TableInfo.Column("status", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsSyncQueue.put("attempts", new TableInfo.Column("attempts", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsSyncQueue.put("errorMessage", new TableInfo.Column("errorMessage", "TEXT", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsSyncQueue.put("createdAt", new TableInfo.Column("createdAt", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsSyncQueue.put("ownerUserId", new TableInfo.Column("ownerUserId", "INTEGER", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        final HashSet<TableInfo.ForeignKey> _foreignKeysSyncQueue = new HashSet<TableInfo.ForeignKey>(0);
        final HashSet<TableInfo.Index> _indicesSyncQueue = new HashSet<TableInfo.Index>(0);
        final TableInfo _infoSyncQueue = new TableInfo("sync_queue", _columnsSyncQueue, _foreignKeysSyncQueue, _indicesSyncQueue);
        final TableInfo _existingSyncQueue = TableInfo.read(db, "sync_queue");
        if (!_infoSyncQueue.equals(_existingSyncQueue)) {
          return new RoomOpenHelper.ValidationResult(false, "sync_queue(com.bengkel.app.data.local.entity.SyncQueueEntity).\n"
                  + " Expected:\n" + _infoSyncQueue + "\n"
                  + " Found:\n" + _existingSyncQueue);
        }
        return new RoomOpenHelper.ValidationResult(true, null);
      }
    }, "79cdfd6af68a7bf1726104eccd7eb8f8", "4d3256345d6c5c2c669a8ce73aa01389");
    final SupportSQLiteOpenHelper.Configuration _sqliteConfig = SupportSQLiteOpenHelper.Configuration.builder(config.context).name(config.name).callback(_openCallback).build();
    final SupportSQLiteOpenHelper _helper = config.sqliteOpenHelperFactory.create(_sqliteConfig);
    return _helper;
  }

  @Override
  @NonNull
  protected InvalidationTracker createInvalidationTracker() {
    final HashMap<String, String> _shadowTablesMap = new HashMap<String, String>(0);
    final HashMap<String, Set<String>> _viewTables = new HashMap<String, Set<String>>(0);
    return new InvalidationTracker(this, _shadowTablesMap, _viewTables, "users","vehicles","services","products","bookings","transactions","transaction_details","sync_queue");
  }

  @Override
  public void clearAllTables() {
    super.assertNotMainThread();
    final SupportSQLiteDatabase _db = super.getOpenHelper().getWritableDatabase();
    try {
      super.beginTransaction();
      _db.execSQL("DELETE FROM `users`");
      _db.execSQL("DELETE FROM `vehicles`");
      _db.execSQL("DELETE FROM `services`");
      _db.execSQL("DELETE FROM `products`");
      _db.execSQL("DELETE FROM `bookings`");
      _db.execSQL("DELETE FROM `transactions`");
      _db.execSQL("DELETE FROM `transaction_details`");
      _db.execSQL("DELETE FROM `sync_queue`");
      super.setTransactionSuccessful();
    } finally {
      super.endTransaction();
      _db.query("PRAGMA wal_checkpoint(FULL)").close();
      if (!_db.inTransaction()) {
        _db.execSQL("VACUUM");
      }
    }
  }

  @Override
  @NonNull
  protected Map<Class<?>, List<Class<?>>> getRequiredTypeConverters() {
    final HashMap<Class<?>, List<Class<?>>> _typeConvertersMap = new HashMap<Class<?>, List<Class<?>>>();
    _typeConvertersMap.put(UserDao.class, UserDao_Impl.getRequiredConverters());
    _typeConvertersMap.put(VehicleDao.class, VehicleDao_Impl.getRequiredConverters());
    _typeConvertersMap.put(ServiceDao.class, ServiceDao_Impl.getRequiredConverters());
    _typeConvertersMap.put(ProductDao.class, ProductDao_Impl.getRequiredConverters());
    _typeConvertersMap.put(BookingDao.class, BookingDao_Impl.getRequiredConverters());
    _typeConvertersMap.put(TransactionDao.class, TransactionDao_Impl.getRequiredConverters());
    _typeConvertersMap.put(SyncQueueDao.class, SyncQueueDao_Impl.getRequiredConverters());
    return _typeConvertersMap;
  }

  @Override
  @NonNull
  public Set<Class<? extends AutoMigrationSpec>> getRequiredAutoMigrationSpecs() {
    final HashSet<Class<? extends AutoMigrationSpec>> _autoMigrationSpecsSet = new HashSet<Class<? extends AutoMigrationSpec>>();
    return _autoMigrationSpecsSet;
  }

  @Override
  @NonNull
  public List<Migration> getAutoMigrations(
      @NonNull final Map<Class<? extends AutoMigrationSpec>, AutoMigrationSpec> autoMigrationSpecs) {
    final List<Migration> _autoMigrations = new ArrayList<Migration>();
    return _autoMigrations;
  }

  @Override
  public UserDao userDao() {
    if (_userDao != null) {
      return _userDao;
    } else {
      synchronized(this) {
        if(_userDao == null) {
          _userDao = new UserDao_Impl(this);
        }
        return _userDao;
      }
    }
  }

  @Override
  public VehicleDao vehicleDao() {
    if (_vehicleDao != null) {
      return _vehicleDao;
    } else {
      synchronized(this) {
        if(_vehicleDao == null) {
          _vehicleDao = new VehicleDao_Impl(this);
        }
        return _vehicleDao;
      }
    }
  }

  @Override
  public ServiceDao serviceDao() {
    if (_serviceDao != null) {
      return _serviceDao;
    } else {
      synchronized(this) {
        if(_serviceDao == null) {
          _serviceDao = new ServiceDao_Impl(this);
        }
        return _serviceDao;
      }
    }
  }

  @Override
  public ProductDao productDao() {
    if (_productDao != null) {
      return _productDao;
    } else {
      synchronized(this) {
        if(_productDao == null) {
          _productDao = new ProductDao_Impl(this);
        }
        return _productDao;
      }
    }
  }

  @Override
  public BookingDao bookingDao() {
    if (_bookingDao != null) {
      return _bookingDao;
    } else {
      synchronized(this) {
        if(_bookingDao == null) {
          _bookingDao = new BookingDao_Impl(this);
        }
        return _bookingDao;
      }
    }
  }

  @Override
  public TransactionDao transactionDao() {
    if (_transactionDao != null) {
      return _transactionDao;
    } else {
      synchronized(this) {
        if(_transactionDao == null) {
          _transactionDao = new TransactionDao_Impl(this);
        }
        return _transactionDao;
      }
    }
  }

  @Override
  public SyncQueueDao syncQueueDao() {
    if (_syncQueueDao != null) {
      return _syncQueueDao;
    } else {
      synchronized(this) {
        if(_syncQueueDao == null) {
          _syncQueueDao = new SyncQueueDao_Impl(this);
        }
        return _syncQueueDao;
      }
    }
  }
}
