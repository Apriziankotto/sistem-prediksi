import pandas as pd
from pathlib import Path
from sqlalchemy import create_engine


#KONFIGURASI DATABASE
DB_HOST = "127.0.0.1"
DB_PORT = "3306"
DB_NAME = "prediksi-bahan"
DB_USER = "root"
DB_PASSWORD = ""

DATABASE_URL = (
    f"mysql+pymysql://{DB_USER}:{DB_PASSWORD}"
    f"@{DB_HOST}:{DB_PORT}/{DB_NAME}"
)
engine = create_engine(DATABASE_URL)

OUTPUT_DIR = Path("data_split")
OUTPUT_DIR.mkdir(exist_ok=True)

TRAIN_MONTHS = 52
TEST_MONTHS = 12

REQUIRED_COLUMNS = [
    "Tahun",
    "Bulan",
    "IdBahan",
    "Total_Permintaan",
    "Total_Aktual",
    "Jumlah_SPK",
    "Total_Jumlah_Item",
    "Tanggal"
]

# AMBIL DATA DARI DATABASE
print("Baca Dataset dari Database")
QUERY = """
SELECT
    tanggal,
    tahun,
    bulan,
    kode_bahan,
    total_permintaan,
    total_aktual,
    jumlah_spk,
    total_jumlah_item
FROM rekap_bahan_bulanan
"""
#read query menggunakan koneksi engine dan disimpan di df (data frame)
df = pd.read_sql(QUERY, engine)

print(f"Jumlah data berhasil diambil : {len(df)}")

# RENAME KOLOM
df = df.rename(columns={
    "tanggal": "Tanggal",
    "tahun": "Tahun",
    "bulan": "Bulan",
    "kode_bahan": "IdBahan",
    "total_permintaan": "Total_Permintaan",
    "total_aktual": "Total_Aktual",
    "jumlah_spk": "Jumlah_SPK",
    "total_jumlah_item": "Total_Jumlah_Item"
})


# VALIDASI KOLOM
#hapus spasi berlebih di awal atau akhir
df.columns = df.columns.str.strip() 
#utk periksa kolom, jika kolom tdk ada di df masukkan ke daftar missing
missing = [
    col
    for col in REQUIRED_COLUMNS
    if col not in df.columns
]

if missing:
    raise ValueError(
        f"Kolom tidak ditemukan: {missing}"
    )

# KONVERSI TANGGAL
#konversi ke fotmat yang dpt diproses pandas
df["Tanggal"] = pd.to_datetime(
    df["Tanggal"],
    errors="coerce"
)
#hapus tanggal tdk valid
df = df.dropna(
    subset=["Tanggal"]
)
#sort data
df = df.sort_values(
    ["Tanggal", "IdBahan"]
).reset_index(drop=True)
#cek jumlah bulan
unique_months = sorted(
    df["Tanggal"].unique()
)
required_months = TRAIN_MONTHS + TEST_MONTHS

print(f"\nJumlah bulan tersedia : {len(unique_months)}")

if len(unique_months) < required_months:
    raise ValueError(
        f"Dataset minimal harus memiliki "
        f"{required_months} bulan."
    )

#ambil 64 bulan terakhir
if len(unique_months) > required_months:
    print(
        f"Mengambil {required_months} bulan terakhir."
    )
    unique_months = unique_months[-required_months:]

#split train dan test
train_months = unique_months[:TRAIN_MONTHS]
test_months = unique_months[TRAIN_MONTHS:]

# mengek apakah setiap nilai tanggal termasuk dalam train_months, jika iya masuk ke train
train = df[df["Tanggal"].isin(train_months)].copy()
# menggunakan copy utk membuat salinan
test = df[df["Tanggal"].isin(test_months)].copy()

#simpan data
train.to_csv(OUTPUT_DIR / "train.csv",index=False)

test.to_csv(OUTPUT_DIR / "test.csv",index=False)

#ringkasan hasil
print("\n" + "=" * 60)
print("HASIL SPLIT DATA")
print("=" * 60)

print(f"Data Latih : {train.shape}")
print(f"Data Uji   : {test.shape}")

print("\nPeriode Data Latih:")
print(
    pd.Timestamp(train_months[0]).strftime("%Y-%m"),
    "s.d.",
    pd.Timestamp(train_months[-1]).strftime("%Y-%m")
)

print("\nPeriode Data Uji:")
print(
    pd.Timestamp(test_months[0]).strftime("%Y-%m"),
    "s.d.",
    pd.Timestamp(test_months[-1]).strftime("%Y-%m")
)

print(
    "\nJumlah bulan Data Latih :",
    train["Tanggal"].nunique()
)

print(
    "Jumlah bulan Data Uji   :",
    test["Tanggal"].nunique()
)


# CEK KOLOM OUTPUT

print("\nKolom Data Train:")
print(train.columns.tolist())

print("\nKolom Data Test:")
print(test.columns.tolist())

print("\nFile berhasil dibuat:")
print(
    f" - {OUTPUT_DIR / 'train.csv'}"
)

print(
    f" - {OUTPUT_DIR / 'test.csv'}"
)

print("\nSplit data selesai.")