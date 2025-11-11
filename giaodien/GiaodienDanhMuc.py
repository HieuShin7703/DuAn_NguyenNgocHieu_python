import tkinter as tk
from tkinter import ttk, messagebox

from commom.deletedanhmuc import deleteDanhMuc
from commom.getAll_danhmuc import get_all_danhmuc
from commom.insertdanhmuc import insert_danhmuc
from commom.updatedanhmuc import updateDanhMuc

def them_danhmuc():
    """Thêm danh mục mới và giữ lại dữ liệu vừa thêm"""
    ten = entry_ten.get().strip()
    mota = entry_mota.get().strip()
    if not ten:
        messagebox.showwarning("Thiếu thông tin", "Vui lòng nhập tên danh mục!")
        return

    last_id = insert_danhmuc(ten, mota)  # 👈 lấy ID vừa thêm
    load_data()  # Cập nhật lại bảng

    if last_id:
        # 👇 chọn dòng vừa thêm
        for item in tree.get_children():
            values = tree.item(item, "values")
            if str(values[0]) == str(last_id):
                tree.selection_set(item)
                tree.focus(item)
                tree.see(item)

                # đồng thời hiển thị lại thông tin trong ô nhập
                entry_ten.delete(0, tk.END)
                entry_ten.insert(0, ten)
                entry_mota.delete(0, tk.END)
                entry_mota.insert(0, mota)
                break



def sua_danhmuc():
    """Cập nhật thông tin danh mục"""
    try:
        selected = tree.selection()[0]
        id_dm = tree.item(selected)["values"][0]
        ten = entry_ten.get().strip()
        mota = entry_mota.get().strip()
        if not ten:
            messagebox.showwarning("Thiếu thông tin", "Vui lòng nhập tên danh mục!")
            return
        updateDanhMuc(id_dm, ten, mota)
        load_data()
    except IndexError:
        messagebox.showwarning("Chưa chọn danh mục", "Vui lòng chọn danh mục để sửa!")


def xoa_danhmuc():
    """Xóa danh mục được chọn"""
    try:
        selected = tree.selection()[0]
        id_dm = tree.item(selected)["values"][0]
        confirm = messagebox.askyesno("Xác nhận xóa", "Bạn có chắc muốn xóa danh mục này?")
        if confirm:
            deleteDanhMuc(id_dm)
            load_data()
    except IndexError:
        messagebox.showwarning("Chưa chọn danh mục", "Vui lòng chọn danh mục để xóa!")

def on_select(event):
    """Khi chọn 1 dòng trong bảng thì hiện lên ô nhập"""
    try:
        selected = tree.selection()[0]
        values = tree.item(selected)["values"]
        entry_ten.delete(0, tk.END)
        entry_ten.insert(0, values[1])
        entry_mota.delete(0, tk.END)
        entry_mota.insert(0, values[2])
    except IndexError:
        pass

def load_data():
    """Hiển thị danh sách danh mục ra bảng"""
    for row in tree.get_children():
        tree.delete(row)
    danh_sach = get_all_danhmuc()
    if danh_sach:
        for dm in danh_sach:
            tree.insert("", "end", values=(dm[0], dm[1], dm[2]))

root = tk.Tk()
root.title("💊 Quản lý Danh mục")
root.geometry("700x450")
root.resizable(False, False)

# Frame nhập thông tin
frame_input = tk.LabelFrame(root, text="Thông tin danh mục", padx=10, pady=10)
frame_input.pack(fill="x", padx=10, pady=10)

tk.Label(frame_input, text="Tên danh mục:").grid(row=0, column=0, sticky="w")
entry_ten = tk.Entry(frame_input, width=40)
entry_ten.grid(row=0, column=1, padx=10)

tk.Label(frame_input, text="Mô tả:").grid(row=1, column=0, sticky="w")
entry_mota = tk.Entry(frame_input, width=40)
entry_mota.grid(row=1, column=1, padx=10)

# Frame nút
frame_buttons = tk.Frame(root)
frame_buttons.pack(pady=5)
tk.Button(frame_buttons, text="➕ Thêm", width=12, command= them_danhmuc).grid(row=0, column=0, padx=5)
tk.Button(frame_buttons, text="✏️ Sửa", width=12, command= sua_danhmuc).grid(row=0, column=1, padx=5)
tk.Button(frame_buttons, text="🗑️ Xóa", width=12, command= xoa_danhmuc).grid(row=0, column=2, padx=5)
tk.Button(frame_buttons, text="🔄 Làm mới", width=12).grid(row=0, column=3, padx=5)

# Bảng hiển thị
columns = ("ID", "Tên danh mục", "Mô tả")
tree = ttk.Treeview(root, columns=columns, show="headings", height=12)
for col in columns:
    tree.heading(col, text=col)
    tree.column(col, anchor="center", width=200)
tree.pack(fill="both", expand=True, padx=10, pady=10)

# Gắn sự kiện chọn hàng
tree.bind("<<TreeviewSelect>>", on_select)

load_data()

root.mainloop()
