from mysql.connector import Error

from ketnoidb.ketnoi_mySQL import connect_mysql

def insert_danhmuc(tendm, mota):
    """Hàm thêm mới một danh mục"""
    try:
        connection = connect_mysql()
        if connection is None:
            print("⚠️ Không thể kết nối MySQL.")
            return

        cursor = connection.cursor()
        sql = "INSERT INTO danhmuc (ten_danh_muc, mo_ta) VALUES (%s, %s)"
        data = (tendm, mota)
        cursor.execute(sql, data)
        connection.commit()
        last_id = cursor.lastrowid
        print(f"✅ Đã thêm danh mục: {tendm}")
        return last_id
    except Error as e:
        print("❌ Lỗi khi thêm danh mục:", e)

    finally:
        if connection and connection.is_connected():
            cursor.close()
            connection.close()
