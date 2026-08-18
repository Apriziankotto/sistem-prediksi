import pandas as pd

# Membaca dataset
df = pd.read_excel("dataset_final.xlsx")
print(df.columns)

# Statistik Deskriptif
df.describe()
print(df.describe())

# # Hitung skewness setiap bahan
# skew_per_bahan = (
#     df.groupby('IdBahan')['Total_Aktual']
#       .skew()
#       .reset_index()
# )

# # Pengelompokkan berdasarkan tingkat skewness
# def kategori_skew(x):
#     if pd.isna(x):
#         return "Data tidak cukup"
#     elif abs(x) < 0.5:
#         return "Hampir Simetris"
#     elif abs(x) < 1:
#         return "Skew Sedang"
#     else:
#         return "Skew Tinggi"

# skew_per_bahan['Kategori'] = skew_per_bahan['Skewness'].apply(kategori_skew)

# print(skew_per_bahan['Kategori'].value_counts())

