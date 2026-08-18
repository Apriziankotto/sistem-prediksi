import json
import numpy as np
import pandas as pd

from pathlib import Path
from sklearn.preprocessing import OneHotEncoder
import joblib

# PATH
INPUT_DIR = Path("data_split")
OUTPUT_DIR = Path("data_ready")
OUTPUT_DIR.mkdir(exist_ok=True)

TARGET = "Total_Aktual"

print("Load Data")
train = pd.read_csv(INPUT_DIR / "train.csv")
test = pd.read_csv(INPUT_DIR / "test.csv")

# VALIDASI KOLOM
REQUIRED_COLUMNS = [
    "Tanggal",
    "IdBahan",
    "Bulan",
    "Total_Permintaan",
    "Jumlah_SPK",
    "Total_Jumlah_Item",
    "Total_Aktual"
]
for col in REQUIRED_COLUMNS:
    if col not in train.columns:
        raise ValueError(f"Kolom {col} tidak ditemukan pada data train")


# TRANFORMASI IDBAHAN
encoder = OneHotEncoder(
    handle_unknown="ignore",
    sparse_output=False
)
train_id = encoder.fit_transform(train[["IdBahan"]])
test_id = encoder.transform(test[["IdBahan"]])

id_columns = encoder.get_feature_names_out(["IdBahan"])

train_id = pd.DataFrame(train_id, columns=id_columns)
test_id = pd.DataFrame(test_id, columns=id_columns)

# Simpan encoder untuk prediksi nanti
joblib.dump(encoder,OUTPUT_DIR / "encoder_idbahan.pkl")

# 3. MENENTUKAN FITUR MODEL
BASE_FEATURES = [
    "Bulan",
    "Total_Permintaan",
    "Jumlah_SPK",
    "Total_Jumlah_Item"
]
FEATURES = (BASE_FEATURES +list(id_columns))

# 4. MEMBENTUK DATASET MODEL
def create_dataset(df, id_df):
    X = pd.concat(
        [
            df[BASE_FEATURES].reset_index(drop=True),
            id_df.reset_index(drop=True)
        ],
        axis=1
    )
    info = df[
        [
            "Tanggal",
            "IdBahan"
        ]
    ].reset_index(drop=True)
    target = df[
        [TARGET,]
    ].reset_index(drop=True)
    dataset = pd.concat(
        [
            info,
            X,
            target
        ],
        axis=1
    )
    return dataset

train_ready = create_dataset(train, train_id)
test_ready = create_dataset(test,test_id)

# 5. SIMPAN DATA READY
train_ready.to_csv( OUTPUT_DIR / "train_ready.csv",index=False)
test_ready.to_csv(OUTPUT_DIR / "test_ready.csv",index=False)

with open(OUTPUT_DIR / "features.json","w",encoding="utf-8") as f:
    json.dump(
        FEATURES,
        f,
        indent=4,
        ensure_ascii=False
    )

# HASIL
print("\nSelesai")
print("Train :",train_ready.shape)
print("Test  :",test_ready.shape)
print("Jumlah fitur :",len(FEATURES))
