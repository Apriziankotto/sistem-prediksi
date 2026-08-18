import sys #baca input dari laravel
import json #baca dan krm json
import joblib # memuat model pkl & encoder
import numpy as np #operasi numerik & transformasi log
import pandas as pd #buat dataframe

from pathlib import Path

# Lokasi scrip berada
BASE_DIR = Path(__file__).resolve().parent

MODEL_PATH = BASE_DIR / "hasil_random_forest" / "random_forest_final.pkl"
FEATURE_PATH = BASE_DIR / "data_ready" / "features.json"
ENCODER_PATH = BASE_DIR / "data_ready" / "encoder_idbahan.pkl"

# load model
model = joblib.load(MODEL_PATH)

#load data fitur
with open(FEATURE_PATH, "r", encoding="utf-8") as f:
    FEATURES = json.load(f)

encoder = joblib.load(ENCODER_PATH)


# memastikan nilai menjadi float
def safe_float(v):
    try:
        return float(v)
    except:
        return 0.0

#
def log1p_safe(v):
    return np.log1p(max(safe_float(v), 0))


# Terima data dari laravel
payload = json.loads(sys.stdin.read())

items = payload.get("items", [])
#validasi keberadaan data
if len(items) == 0:
    print(json.dumps({
        "success": False,
        "message": "Tidak ada data yang diterima."
    }))
    sys.exit()


# Membuat dataset
rows = []

for item in items:
    feature = {}
    feature["Bulan"] = int(item["Bulan"])
    feature["Total_Permintaan"] = safe_float(
        item["Total_Permintaan"]
    )
    feature["Jumlah_SPK"] = safe_float(
        item["Jumlah_SPK"]
    )
    feature["Total_Jumlah_Item"] = safe_float(
        item["Total_Jumlah_Item"]
    )
    # bahan di encoder disini
    encoded = encoder.transform(
        [[item["IdBahan"]]]
    )[0]
    id_columns = encoder.get_feature_names_out(
        ["IdBahan"]
    )
    for col, value in zip(id_columns, encoded):
        feature[col] = value

    row = []

    #cek apakah semua fitur tersedia
    missing_features = [
        col for col in FEATURES
        if col not in feature
    ]

    if missing_features:
        raise ValueError(
            f"Feature tidak ditemukan: {missing_features}"
        )

    row = [feature[col] for col in FEATURES]

    rows.append(row)

# Data frame
X = pd.DataFrame(
    rows,
    columns=FEATURES
)
#memastikan semua data numerik
X = X.apply(
    pd.to_numeric,
    errors="coerce"
).fillna(0)


# Prediksi
pred_log = model.predict(X)
pred_raw = np.expm1(pred_log)
#jika prediksi negatif dipaksa menjadi 0
pred_final = np.ceil(
    np.maximum(
        pred_raw,
        0
    )
)


# Outputnys
results = []
for item, raw, pred in zip(
    items,
    pred_raw,
    pred_final
):
    results.append({
        "IdBahan": item["IdBahan"],
        "Prediksi_Raw": float(raw),
        "Prediksi": float(pred)

    })

print(json.dumps({
    "success": True,
    "results": results

}))