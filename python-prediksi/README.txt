PERCOBAAN RANDOM FOREST REGRESSION
==================================

Tujuan:
Memprediksi Total_Aktual penggunaan bahan/material bulanan menggunakan algoritma Random Forest Regression.

Urutan menjalankan file:
1. python 01_split_data.py
2. python 02_feature_engineering.py
3. python 03_train_random_forest.py
4. python 04_analisis_error.py

File input utama:
- dataset_final2.xlsx
- Sheet: DATASET FINAL

Output utama:
- data_split/train.csv
- data_split/valid.csv
- data_split/test.csv
- data_ready/train_ready.csv
- data_ready/valid_ready.csv
- data_ready/test_ready.csv
- hasil_random_forest/model_random_forest.pkl
- hasil_random_forest/hasil_evaluasi_model.xlsx
- hasil_random_forest/hasil_prediksi_test.csv
- hasil_random_forest/feature_importance.xlsx
- hasil_random_forest/top_30_error_test.xlsx
- hasil_random_forest/ringkasan_error_per_bahan.xlsx

Catatan penting:
- Split data dibuat berdasarkan waktu, bukan random.
- Feature lag dan rolling dibuat secara aman untuk time series.
- Validasi dan test tetap memakai riwayat dari data bulan sebelumnya.
- Target dilatih dalam bentuk log1p(Total_Aktual), lalu prediksi dikembalikan ke skala asli.
- Model utama adalah RandomForestRegressor.
