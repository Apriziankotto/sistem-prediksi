import pandas as pd

df = pd.read_excel("dataset_final2.xlsx")

stat_bahan = (
    df.groupby("IdBahan")["Total_Aktual"]
      .agg(["count", "mean", "median", "std", "max"])
)

stat_bahan["CV"] = stat_bahan["std"] / stat_bahan["mean"]

print(stat_bahan.sort_values("CV", ascending=False).head(20))