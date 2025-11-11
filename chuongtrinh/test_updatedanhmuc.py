from commom.updatedanhmuc import updateDanhMuc

while True:
    id_danh_muc = int(input("Nhập ID danh mục cần cập nhật: "))
    ten_moi = input("Nhập tên danh mục mới: ")
    mo_ta_moi = input("Nhập mô tả mới: ")
    updateDanhMuc(id_danh_muc, ten_moi, mo_ta_moi)
    tiep_tuc = input("TIẾP TỤC (y), THOÁT thì nhấn ký tự bất kỳ: ")
    if tiep_tuc != "y":
        print("👋 Kết thúc chương trình cập nhật danh mục.")
        break
