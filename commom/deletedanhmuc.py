import mysql.connector
from ketnoidb.ketnoi_mySQL import connect_mysql

def deleteDanhMuc(id_danh_muc):
    try:
        # 1. Kết nối tới MySQL
        connection = connect_mysql()
        if connection is None:
            print("⚠️ Không thể kết nối MySQL.")
            return

        cursor = connection.cursor()

        # 2. Xóa sản phẩm liên quan trước (nếu có)
        sql_delete_sanpham = "DELETE FROM sanpham WHERE id_danh_muc = %s"
        cursor.execute(sql_delete_sanpham, (id_danh_muc,))
        connection.commit()

        # 3. Xóa danh mục
        sql_delete_danhmuc = "DELETE FROM danhmuc WHERE id = %s"
        cursor.execute(sql_delete_danhmuc, (id_danh_muc,))
        connection.commit()

        # 4. Kiểm tra kết quả
        if cursor.rowcount > 0:
            print(f"✅ Đã xóa danh mục có ID = {id_danh_muc} (và các sản phẩm liên quan).")
        else:
            print("⚠️ Không tìm thấy danh mục cần xóa.")

    except mysql.connector.Error as e:
        # Xử lý lỗi cụ thể của khóa ngoại
        if e.errno == 1451:
            print("❌ Không thể xóa danh mục vì vẫn còn sản phẩm thuộc danh mục này.")
        else:
            print("❌ Lỗi khi xóa danh mục:", e)

    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()
