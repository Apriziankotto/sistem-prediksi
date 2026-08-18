import pandas as pd
import matplotlib.pyplot as plt

# Membaca dataset
df = pd.read_excel("dataset_final.xlsx")

# Melihat informasi awal
print(df.head())
print(df.describe())

plt.figure(figsize=(10,8))

plt.hist(df['Total_Aktual'], bins=300)

plt.xlabel("Jumlah Aktual")
plt.ylabel("Frekuensi")
plt.title("Distribusi Jumlah Aktual")

print(df['IdBahan'].nunique())

plt.show()