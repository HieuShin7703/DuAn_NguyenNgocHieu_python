import mysql.connector
from ketnoidb.ketnoi_mySQL import connect_mysql

def updateDanhMuc(id_danh_muc, ten_moi, mo_ta_moi):
    try:
        # 1. Kết nối tới MySQL
        connection = connect_mysql()
        if connection is None:
            print("⚠️ Không thể kết nối MySQL.")
            return

        cursor = connection.cursor()

        # 2. Câu lệnh SQL cập nhật danh mục
        sql = """
        UPDATE danhmuc 
        SET ten_danh_muc = %s, mo_ta = %s 
        WHERE id = %s
        """
        data = (ten_moi, mo_ta_moi, id_danh_muc)

        # 3. Thực thi lệnh và commit
        cursor.execute(sql, data)
        connection.commit()

        # 4. Kiểm tra kết quả
        if cursor.rowcount > 0:
            print(f"✅ Đã cập nhật danh mục ID = {id_danh_muc} thành công.")
        else:
            print("⚠️ Không tìm thấy danh mục cần cập nhật.")

    except mysql.connector.Error as e:
        print("❌ Lỗi khi cập nhật danh mục:", e)

    finally:
        # 5. Đóng kết nối
        if connection.is_connected():
            cursor.close()
            connection.close()
