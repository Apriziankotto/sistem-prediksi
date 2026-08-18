import json
import joblib
import numpy as np
import pandas as pd

from pathlib import Path
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import (
    mean_squared_error,
    r2_score
)
from sklearn.model_selection import (
    ParameterGrid,
    TimeSeriesSplit
)

# PATH
INPUT_DIR = Path("data_ready")
OUTPUT_DIR = Path("hasil_random_forest")
OUTPUT_DIR.mkdir(exist_ok=True)

TRAIN_FILE = INPUT_DIR / "train_ready.csv"
TEST_FILE = INPUT_DIR / "test_ready.csv"
FEATURE_FILE = INPUT_DIR / "features.json"

TARGET = "Total_Aktual"
RANDOM_STATE = 42

# 1. LOAD FEATURES
print("Load Fitur")
with open(FEATURE_FILE,"r",encoding="utf-8") as f:
    FEATURES = json.load(f)
print("Jumlah fitur:",len(FEATURES))

# 2. LOAD DATA
print("\nLoad Data")
train = pd.read_csv( TRAIN_FILE)
test = pd.read_csv(TEST_FILE)

# 3. VALIDASI DATA
def check_column(df, name):
    required = FEATURES + [TARGET]
    missing = [
        col
        for col in required
        if col not in df.columns
    ]
    if missing:
        raise ValueError(
            f"{name} tidak memiliki kolom: {missing}"
        )
check_column(train,"Data Latih")
check_column(test,"Data Uji")

# 4. PEMBENTUKAN X DAN Y
def make_xy(df):
    X = df[FEATURES].copy()
    y = df[TARGET].copy()

    # fitur numerik
    X = X.apply(pd.to_numeric,errors="coerce")
    X = X.replace([np.inf, -np.inf],np.nan)
    X = X.fillna(0)
    # target
    y = pd.to_numeric(y,errors="coerce")

    # Validasi target
    if y.isna().any():
        raise ValueError(
            "Target Total_Aktual memiliki nilai NaN."
        )

    if (y < 0).any():
        raise ValueError(
            "Target Total_Aktual memiliki nilai negatif."
        )
    return X, y

X_train, y_train = make_xy(train)
X_test, y_test = make_xy(test)

print("\nUkuran Data")
print("Data Latih:",X_train.shape)
print("Data Uji:",X_test.shape)


# 5. PERHITUNGAN METRIK
def calculate_metrics(
    y_true,y_pred):
    
    # Pastikan tidak ada prediksi negatif
    y_pred = np.maximum(
        y_pred,
        0
    )

    # RMSE
    rmse = np.sqrt(mean_squared_error(y_true,y_pred))

    # R2
    r2 = r2_score(y_true,y_pred)

    # WAPE
    wape = (
        np.sum(np.abs(y_true - y_pred))/max(np.sum(y_true),1)) * 100

    return {
        "RMSE": rmse,
        "R2": r2,
        "WAPE_%": wape
    }

# 6. TIME SERIES CROSS VALIDATION (TUNING)
print("\nProses Tuning")

tscv = TimeSeriesSplit(n_splits=5)
base_param = {
    "random_state": RANDOM_STATE,
    "n_jobs": 4,
    "bootstrap": True
}
param_grid = {
    "n_estimators":[100,200,300],
    "max_depth":[30,40, None],
    "min_samples_split":[2,5,10],
    "min_samples_leaf":[3, 5, 10],
    "max_features":["sqrt", 0.5]
}

parameter_list = list(ParameterGrid(param_grid))
print("Jumlah kombinasi:",len(parameter_list))

hasil_cv = []
best_rmse = float("inf")
best_params = None
for no, param in enumerate(
    parameter_list,
    start=1
):
    print(f"\nKombinasi {no}/{len(parameter_list)}")
    fold_rmse = []

    for fold, ( train_idx, val_idx) in enumerate(tscv.split(X_train), start=1):
        X_train_fold = X_train.iloc[train_idx]
        X_val_fold = X_train.iloc[val_idx]

        y_train_fold = y_train.iloc[train_idx]
        y_val_fold = y_train.iloc[val_idx]

        model = RandomForestRegressor(
            **base_param,
            **param
        )
        model.fit(X_train_fold,y_train_fold)
        pred = model.predict(X_val_fold)
        metric = calculate_metrics(y_val_fold,pred)

        fold_rmse.append(metric["RMSE"])
        print(f"Fold {fold} RMSE : {metric['RMSE']}")

    avg_rmse = np.mean(fold_rmse)
    hasil_cv.append(
        {
        **param,
        "Fold1_RMSE": fold_rmse[0],
        "Fold2_RMSE": fold_rmse[1],
        "Fold3_RMSE": fold_rmse[2],
        "Fold4_RMSE": fold_rmse[3],
        "Fold5_RMSE": fold_rmse[4],
        "Average_RMSE": avg_rmse
        }
    )
    print("Average RMSE:", avg_rmse)
    if avg_rmse < best_rmse:
        best_rmse = avg_rmse
        best_params = param

cv_result = pd.DataFrame(hasil_cv)
cv_result = cv_result.sort_values("Average_RMSE")
cv_result.to_excel(OUTPUT_DIR /"hasil_cross_validation.xlsx",index=False)

print("\nHyperparameter Terbaik")
print(best_params)
print("RMSE CV:",best_rmse)

# 7. TRAIN MODEL FINAL
print("\nTrain Model Final")
final_model = RandomForestRegressor(
    **base_param,
    **best_params
)
final_model.fit(X_train,y_train)


# 8. EVALUASI DATA UJI
print("\nEvaliasi Data Uji")
pred_test = final_model.predict(X_test)
pred_test = np.maximum(pred_test,0)
hasil_prediksi = test.copy()
hasil_prediksi["Aktual"] = np.round(y_test,2)
hasil_prediksi["Prediksi"] = np.round(pred_test,2)
hasil_prediksi["Error"] = (hasil_prediksi["Aktual"] -hasil_prediksi["Prediksi"])
hasil_prediksi["Abs_Error"] = (hasil_prediksi["Error"].abs())
hasil_prediksi.to_excel(OUTPUT_DIR /"hasil_prediksi_test.xlsx",index=False)
metric_test = calculate_metrics(y_test,pred_test)
print(metric_test)
pd.DataFrame([metric_test]).to_excel(OUTPUT_DIR /"evaluasi_test.xlsx",index=False)

# 9. EVALUASI BERDASARKAN RANGE ACTUAL
print("\nEvaluasi Berdasarkan Range ACTUAL")
def kategori(nilai):
    if nilai <= 100:
        return "1-100"
    elif nilai <= 500:
        return "101-500"
    elif nilai <= 1000:
        return "501-1000"
    elif nilai <= 5000:
        return "1001-5000"
    elif nilai <= 15000:
        return "5001-15000"
    else:
        return ">15000"
# Buat kategori berdasarkan nilai actual
hasil_prediksi["Range_Aktual"] = (hasil_prediksi["Aktual"].apply(kategori))
range_eval = []
for grup, data in hasil_prediksi.groupby("Range_Aktual"):
    rmse = np.sqrt(mean_squared_error(data["Aktual"],data["Prediksi"]))
    wape = (np.sum(np.abs(data["Aktual"]-data["Prediksi"]))/max(np.sum(data["Aktual"]),1)) * 100

    range_eval.append(
        {
        "Range_Aktual": grup,
        "Jumlah_Data": len(data),
        "RMSE": rmse,
        "WAPE_%": wape
        }
    )
range_result = pd.DataFrame(range_eval)
range_result.to_excel(OUTPUT_DIR /"evaluasi_range_actual.xlsx",index=False)
print(range_result)

# 10. FEATURE IMPORTANCE
importance = pd.DataFrame({"Fitur": FEATURES,"Importance":final_model.feature_importances_})
importance = importance.sort_values("Importance",ascending=False)
importance.to_excel(OUTPUT_DIR /"feature_importance.xlsx",index=False)

# 11. SIMPAN MODEL

joblib.dump(final_model,OUTPUT_DIR /"random_forest_final.pkl")
with open(OUTPUT_DIR /"best_params.json","w") as f:
    json.dump(
        best_params,
        f,
        indent=4
    )
print("\nSelesai")