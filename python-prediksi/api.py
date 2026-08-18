from pathlib import Path
from typing import Dict, List, Optional

import joblib
import numpy as np
import pandas as pd
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field


BASE_DIR = Path(__file__).resolve().parent
HASIL_DIR = BASE_DIR / "hasil_random_forest"
MODEL_PATH = HASIL_DIR / "model_random_forest_operasional.joblib"

if not MODEL_PATH.exists():
    raise FileNotFoundError(f"Model tidak ditemukan di: {MODEL_PATH}")

model = joblib.load(MODEL_PATH)

if not hasattr(model, "feature_names_in_"):
    raise RuntimeError("Model harus dilatih memakai DataFrame pandas.")

FEATURES: List[str] = list(model.feature_names_in_)

app = FastAPI(title="API Prediksi Random Forest Operasional")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


class PredictRequest(BaseModel):
    Tahun: int = Field(..., example=2026)
    Bulan: int = Field(..., ge=1, le=12, example=7)
    IdBahan: str = Field(..., example="B14")

    Total_Permintaan: float = Field(..., example=40)
    Jumlah_SPK: float = Field(..., example=3)
    Total_Jumlah_Item: float = Field(..., example=10)

    fitur_historis: Optional[Dict[str, float]] = Field(default=None)


def safe_value(value) -> float:
    if value is None:
        return 0.0
    try:
        if pd.isna(value):
            return 0.0
    except Exception:
        pass
    return float(value)


def safe_log1p(value) -> float:
    value = max(safe_value(value), 0)
    return float(np.log1p(value))


def make_input_row(data: PredictRequest) -> pd.DataFrame:
    row = {feature: 0.0 for feature in FEATURES}

    for col in ["Tahun", "Bulan", "Total_Permintaan", "Jumlah_SPK", "Total_Jumlah_Item"]:
        if col in FEATURES:
            row[col] = safe_value(getattr(data, col))

    log_map = {
        "Total_Permintaan_log": data.Total_Permintaan,
        "Jumlah_SPK_log": data.Jumlah_SPK,
        "Total_Jumlah_Item_log": data.Total_Jumlah_Item,
    }

    for col, value in log_map.items():
        if col in FEATURES:
            row[col] = safe_log1p(value)

    if data.fitur_historis:
        for key, value in data.fitur_historis.items():
            if key in FEATURES:
                row[key] = safe_value(value)

    total_permintaan = row.get("Total_Permintaan", 0.0)
    jumlah_spk = row.get("Jumlah_SPK", 0.0)
    total_item = row.get("Total_Jumlah_Item", 0.0)

    lag1 = row.get("lag1_aktual", 0.0)
    lag2 = row.get("lag2_aktual", 0.0)
    roll3 = row.get("roll3_aktual_mean", 0.0)
    roll6 = row.get("roll6_aktual_mean", 0.0)

    turunan = {
        "selisih_permintaan_lag1": total_permintaan - lag1,
        "selisih_permintaan_roll3": total_permintaan - roll3,
        "rasio_permintaan_aktual_lag1": total_permintaan / (lag1 + 1),
        "rasio_permintaan_roll3": total_permintaan / (roll3 + 1),
        "rasio_permintaan_roll6": total_permintaan / (roll6 + 1),
        "rasio_item_spk": total_item / (jumlah_spk + 1),
        "rasio_permintaan_spk": total_permintaan / (jumlah_spk + 1),
        "rasio_permintaan_item": total_permintaan / (total_item + 1),
        "trend_lag1_lag2": lag1 - lag2,
    }

    for col, value in turunan.items():
        if col in FEATURES:
            row[col] = safe_value(value)

    input_df = pd.DataFrame([row], columns=FEATURES)
    input_df = input_df.apply(pd.to_numeric, errors="coerce").fillna(0)

    return input_df


def find_column(df: pd.DataFrame, candidates: List[str]):
    for col in candidates:
        if col in df.columns:
            return col
    return None


@app.get("/")
def home():
    return {
        "message": "API Prediksi Random Forest Operasional aktif",
        "model_path": str(MODEL_PATH),
        "jumlah_fitur_model": len(FEATURES),
        "fitur_model": FEATURES,
        "endpoint": {
            "predict": "/predict",
            "dashboard": "/dashboard",
        },
    }


@app.post("/predict")
def predict(data: PredictRequest):
    try:
        input_df = make_input_row(data)

        pred_log = float(model.predict(input_df)[0])
        prediction = float(np.expm1(pred_log))

        if prediction < 0:
            prediction = 0

        return {
            "prediction": round(prediction, 2),
            "satuan": "Total_Aktual",
            "input": data.model_dump(),
        }

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/dashboard")
def dashboard_summary():
    try:
        pred_path = HASIL_DIR / "hasil_prediksi_semua.csv"

        if not pred_path.exists():
            raise HTTPException(
                status_code=404,
                detail=f"File tidak ditemukan: {pred_path}",
            )

        df = pd.read_csv(pred_path)

        actual_col = find_column(
            df,
            ["Total_Aktual", "Aktual", "y_true", "actual"]
        )

        pred_col = find_column(
            df,
            ["Prediksi_Total_Aktual", "Prediksi", "y_pred", "prediction"]
        )

        if actual_col is None:
            raise HTTPException(status_code=500, detail="Kolom aktual tidak ditemukan.")

        if pred_col is None:
            raise HTTPException(status_code=500, detail="Kolom prediksi tidak ditemukan.")

        for col in [actual_col, pred_col, "Total_Permintaan", "Jumlah_SPK", "Total_Jumlah_Item"]:
            if col in df.columns:
                df[col] = pd.to_numeric(df[col], errors="coerce").fillna(0)

        jumlah_bahan = int(df["IdBahan"].nunique()) if "IdBahan" in df.columns else 0

        jumlah_bahan_aktif = (
            int(df[df[actual_col] > 0]["IdBahan"].nunique())
            if "IdBahan" in df.columns
            else 0
        )

        selisih_penggunaan = float((df[pred_col] - df[actual_col]).sum())

        df_rasio = df[df[actual_col] > 0].copy()
        rata_rasio = (
            float((df_rasio[pred_col] / df_rasio[actual_col]).mean() * 100)
            if len(df_rasio) > 0
            else 0
        )

        permintaan_vs_aktual = []
        if {"Tahun", "Bulan", "Total_Permintaan"}.issubset(df.columns):
            temp = (
                df.groupby(["Tahun", "Bulan"], as_index=False)
                .agg({
                    "Total_Permintaan": "sum",
                    actual_col: "sum",
                })
            )
            temp["Periode"] = temp["Tahun"].astype(str) + "-" + temp["Bulan"].astype(str).str.zfill(2)
            permintaan_vs_aktual = temp[["Periode", "Total_Permintaan", actual_col]].to_dict("records")

        top_bahan = []
        if "IdBahan" in df.columns:
            agg = {actual_col: "sum", pred_col: "sum"}
            if "Nama_Bahan" in df.columns:
                agg["Nama_Bahan"] = "first"

            temp = (
                df.groupby("IdBahan", as_index=False)
                .agg(agg)
                .sort_values(actual_col, ascending=False)
                .head(10)
            )
            top_bahan = temp.to_dict("records")

        distribusi_kategori = []
        if "Kategori_Bahan" in df.columns:
            temp = (
                df.groupby("Kategori_Bahan", as_index=False)[actual_col]
                .sum()
                .sort_values(actual_col, ascending=False)
            )
            distribusi_kategori = temp.to_dict("records")

        prediksi_penggunaan = []
        if {"Tahun", "Bulan"}.issubset(df.columns):
            temp = (
                df.groupby(["Tahun", "Bulan"], as_index=False)[pred_col]
                .sum()
            )
            temp["Periode"] = temp["Tahun"].astype(str) + "-" + temp["Bulan"].astype(str).str.zfill(2)
            prediksi_penggunaan = temp[["Periode", pred_col]].to_dict("records")

        aktual_vs_prediksi = []
        if {"Tahun", "Bulan"}.issubset(df.columns):
            temp = (
                df.groupby(["Tahun", "Bulan"], as_index=False)
                .agg({
                    actual_col: "sum",
                    pred_col: "sum",
                })
            )
            temp["Periode"] = temp["Tahun"].astype(str) + "-" + temp["Bulan"].astype(str).str.zfill(2)
            aktual_vs_prediksi = temp[["Periode", actual_col, pred_col]].to_dict("records")

        return {
            "cards": {
                "jumlah_bahan": jumlah_bahan,
                "jumlah_bahan_aktif": jumlah_bahan_aktif,
                "selisih_penggunaan": round(selisih_penggunaan, 2),
                "rata_rata_rasio": round(rata_rasio, 2),
            },
            "charts": {
                "permintaan_vs_aktual": permintaan_vs_aktual,
                "top_10_bahan": top_bahan,
                "distribusi_kategori": distribusi_kategori,
                "prediksi_penggunaan": prediksi_penggunaan,
                "aktual_vs_prediksi": aktual_vs_prediksi,
            },
        }

    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))