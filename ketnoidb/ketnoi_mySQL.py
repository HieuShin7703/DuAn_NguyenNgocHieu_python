import mysql.connector
from mysql.connector import Error

def connect_mysql():
    """
    Hàm kết nối đến cơ sở dữ liệu MySQL.
    Trả về đối tượng connection nếu thành công, hoặc None nếu thất bại.
    """
    connection = None
    try:
        connection = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="quanlynhathuoc"
        )
        if connection.is_connected():
            print("✅ Kết nối MySQL thành công!")
    except Error as e:
        print(f"❌ Lỗi khi kết nối MySQL: {e}")
    return connection
