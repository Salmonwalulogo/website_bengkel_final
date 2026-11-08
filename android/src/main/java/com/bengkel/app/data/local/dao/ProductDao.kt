package com.bengkel.app.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import androidx.room.Update
import com.bengkel.app.data.local.entity.ProductEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface ProductDao {
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertProduct(product: ProductEntity)

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertProducts(products: List<ProductEntity>)

    @Update
    suspend fun updateProduct(product: ProductEntity)

    @Query("SELECT * FROM products")
    fun getAllProductsFlow(): Flow<List<ProductEntity>>

    @Query("SELECT * FROM products WHERE status = 'active'")
    fun getActiveProductsFlow(): Flow<List<ProductEntity>>

    @Query("SELECT * FROM products WHERE id = :id LIMIT 1")
    suspend fun getProductById(id: Long): ProductEntity?

    @Query("DELETE FROM products WHERE id = :id")
    suspend fun deleteProductById(id: Long)

    @Query("UPDATE products SET categoryId = :newCategoryId WHERE categoryId = :oldCategoryId")
    suspend fun replaceCategoryId(oldCategoryId: Long, newCategoryId: Long)

    @Query("SELECT COUNT(*) FROM products WHERE categoryId = :categoryId AND status = 'active'")
    suspend fun countActiveProductsInCategory(categoryId: Long): Int

    @Query("UPDATE products SET stok = stok - :quantity WHERE id = :productId")
    suspend fun decrementStock(productId: Long, quantity: Int)
}
