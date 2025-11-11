from commom.deletedanhmuc import deleteDanhMuc

while True:
    ma_danhmuc = input("Nhập vào mã danh mục cần xóa: ")
    deleteDanhMuc(ma_danhmuc)
    con=input("TIẾP TỤC y, THOÁT THÌ NHẤN KÝ TỰ BẤT KỲ")
    if con != "y":
        break