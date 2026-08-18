import pandas as pd
import numpy as np

# ==========================
# Sebelum transformasi (dari dataset asli)
# ==========================
df_asli = pd.read_excel("../dataset_final.xlsx")

skew_sebelum = (
    df_asli.groupby("IdBahan")["Total_Aktual"]
      .skew()
      .reset_index(name="Skewness")
)

# ==========================
# Sesudah transformasi (dari data ready, train + test digabung)
# ==========================
df_train = pd.read_csv("../data_ready/train_ready.csv")
df_test = pd.read_csv("../data_ready/test_ready.csv")
df_ready = pd.concat([df_train, df_test], ignore_index=True)

skew_sesudah = (
    df_ready.groupby("IdBahan")["y_log"]
      .skew()
      .reset_index(name="Skewness")
)

# Fungsi kategori
def kategori_skew(x):
    if pd.isna(x):
        return "Data tidak cukup"
    elif abs(x) < 0.5:
        return "Hampir Simetris"
    elif abs(x) < 1:
        return "Skew Sedang"
    else:
        return "Skew Tinggi"

skew_sebelum["Kategori"] = skew_sebelum["Skewness"].apply(kategori_skew)
skew_sesudah["Kategori"] = skew_sesudah["Skewness"].apply(kategori_skew)

# Hitung jumlah tiap kategori
sebelum = skew_sebelum["Kategori"].value_counts()
sesudah = skew_sesudah["Kategori"].value_counts()

kategori = [
    "Hampir Simetris",
    "Skew Sedang",
    "Skew Tinggi",
    "Data tidak cukup"
]

hasil = pd.DataFrame({
    "Kategori": kategori,
    "Sebelum Log": [sebelum.get(k, 0) for k in kategori],
    "Sesudah Log": [sesudah.get(k, 0) for k in kategori]
})

hasil["Perubahan"] = hasil["Sesudah Log"] - hasil["Sebelum Log"]

total = hasil["Sebelum Log"].sum()
hasil["Sebelum Log (%)"] = (hasil["Sebelum Log"] / total * 100).round(1)
hasil["Sesudah Log (%)"] = (hasil["Sesudah Log"] / total * 100).round(1)

print(hasil)