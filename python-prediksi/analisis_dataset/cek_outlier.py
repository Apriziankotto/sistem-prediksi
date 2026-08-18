import pandas as pd

# Membaca dataset
df = pd.read_excel("../dataset_final.xlsx")

# Fitur numerik
fitur = [
    "Jumlah_SPK",
    "Total_Jumlah_Item",
    "Total_Permintaan",
    "Total_Aktual"
]

hasil = []

for kolom in fitur:

    Q1 = df[kolom].quantile(0.25)
    Q3 = df[kolom].quantile(0.75)

    IQR = Q3 - Q1

    batas_bawah = Q1 - 1.5 * IQR
    batas_atas = Q3 + 1.5 * IQR

    jumlah_outlier = ((df[kolom] < batas_bawah) |
                      (df[kolom] > batas_atas)).sum()

    persentase = jumlah_outlier / len(df) * 100

    hasil.append([
        kolom,
        round(Q1, 2),
        round(Q3, 2),
        round(IQR, 2),
        round(batas_bawah, 2),
        round(batas_atas, 2),
        jumlah_outlier,
        round(persentase, 2)
    ])

hasil_outlier = pd.DataFrame(
    hasil,
    columns=[
        "Fitur",
        "Q1",
        "Q3",
        "IQR",
        "Batas Bawah",
        "Batas Atas",
        "Jumlah Outlier",
        "Persentase (%)"
    ]
)

print(hasil_outlier)