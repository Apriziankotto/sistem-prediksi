import subprocess
import sys
from pathlib import Path
from datetime import datetime

BASE_DIR = Path(__file__).resolve().parent

SCRIPTS = [
    "01_split_data.py",
    "02_feature_engineering.py",
    "03_train_random_forest.py",
]

print("=== RETRAIN PIPELINE DIMULAI ===")
print("Waktu mulai:", datetime.now().strftime("%d-%m-%Y %H:%M:%S"))
print("Folder kerja:", BASE_DIR)

for script in SCRIPTS:
    script_path = BASE_DIR / script

    if not script_path.exists():
        raise FileNotFoundError(f"Script tidak ditemukan: {script_path}")

    print("\n" + "=" * 60)
    print(f"Menjalankan: {script}")
    print("=" * 60)

    result = subprocess.run(
        [sys.executable, str(script_path)],
        cwd=str(BASE_DIR),
        text=True,
        capture_output=True
    )

    print(result.stdout)

    if result.stderr:
        print("=== STDERR ===")
        print(result.stderr)

    if result.returncode != 0:
        raise RuntimeError(f"Pipeline berhenti karena error pada {script}")

print("\n=== RETRAIN PIPELINE SELESAI ===")
print("Waktu selesai:", datetime.now().strftime("%d-%m-%Y %H:%M:%S"))