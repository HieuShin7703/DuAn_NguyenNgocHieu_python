from mysql.connector import Error
from ketnoidb.ketnoi_mySQL import connect_mysql

def get_all_danhmuc():
    """Hàm lấy danh sách tất cả danh mục"""
    try:
        connection = connect_mysql()
        if connection is None:
            print("⚠️ Không thể kết nối MySQL.")
            return

        cursor = connection.cursor()
        sql = "SELECT id, ten_danh_muc, mo_ta FROM danhmuc"
        cursor.execute(sql)
        result = cursor.fetchall()

        if not result:
            print("⚠️ Không có danh mục nào trong cơ sở dữ liệu.")
        else:
            print("✅ Danh sách danh mục:")
            for row in result:
                print(f"ID: {row[0]}, Tên danh mục: {row[1]}, Mô tả: {row[2]}")

        return result

    except Error as e:
        print("❌ Lỗi khi lấy danh sách danh mục:", e)

    finally:
        if connection and connection.is_connected():
            cursor.close()
            connection.close()
