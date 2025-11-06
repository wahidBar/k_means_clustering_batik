# ======================================
#  KMeans DB pipeline with auto-elbow
#  - saves: normalized, elbow, centroid_per_iter,
#    euclidean_per_iter, cluster_per_iter, mean_distance_per_iter, final outputs
# ======================================
from sklearn.cluster import KMeans
from sklearn.preprocessing import MinMaxScaler
from sklearn.cluster._kmeans import kmeans_plusplus
import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
from pathlib import Path
import mysql.connector
import os

# ---------------- CONFIG ----------------
OUTDIR = Path("/var/www/akbarkaal/k_means_clustering_batik/hasil_cluster_umkm")
OUTDIR.mkdir(parents=True, exist_ok=True)

MAX_ITERS = 100
TOL = 1e-6
PRINT_ROWS = None
CSV_FLOAT_FMT = "%.2f"
pd.options.display.float_format = '{:.2f}'.format

# ---------------- DATABASE ----------------
db_config = {
    "host": "localhost",
    "user": "pma",
    "password": "1234567",
    "database": "db_k_means_clustering_batik",
}

print("🔗 Menghubungkan ke database...")
conn = mysql.connector.connect(**db_config)

query = """
SELECT
    p.partner_id,
    p.business_name AS `NAMA UMKM`,
    p.validation_status AS partner_status,
    JSON_EXTRACT(p.pemasaran, '$') AS pemasaran,
    SUM(CASE WHEN mp.validation_status = 'Approved' THEN mp.total_quantity ELSE 0 END) AS kuantitas
FROM batik_umkm_partner p
JOIN monthly_production mp ON mp.partner_id = p.partner_id
WHERE p.validation_status = 'Terverifikasi'
GROUP BY p.partner_id, p.business_name, p.validation_status, pemasaran
HAVING kuantitas > 0;
"""

df = pd.read_sql(query, conn)
print(f"✅ Data berhasil diambil: {len(df)} baris")

# ---------------- HELPERS ----------------
def save_csv(df_obj, filename):
    path = OUTDIR / filename
    df_obj.to_csv(path, index=False, float_format=CSV_FLOAT_FMT)
    print(f"💾 Disimpan: {path}")

def save_plot(fig, pngname):
    path = OUTDIR / pngname
    fig.savefig(path, bbox_inches="tight")
    plt.close(fig)
    print(f"💾 Disimpan: {path}")

def print_head(df_obj, title):
    print("\n" + "="*8 + f" {title} " + "="*8)
    with pd.option_context('display.max_rows', PRINT_ROWS, 'display.max_columns', None):
        print(df_obj.head(PRINT_ROWS).to_string(index=False))
    print("="*40 + "\n")

# ---------------- CLEAN & MAP ----------------
def map_pemasaran(p):
    if pd.isna(p): return np.nan
    t = str(p).lower()
    if "luar" in t and "nasional" in t and "lokal" in t: return 3
    if "luar" in t and "nasional" in t: return 3
    if "nasional" in t and "lokal" in t: return 2
    if "nasional" in t: return 2
    return 1

df["PEMASARAN_CODE"] = df["pemasaran"].apply(map_pemasaran)
df["KUANTITAS_CLEAN"] = df["kuantitas"].astype(float)
df = df.dropna(subset=["KUANTITAS_CLEAN", "PEMASARAN_CODE"]).reset_index(drop=True)

print_head(df[["NAMA UMKM", "kuantitas", "pemasaran", "KUANTITAS_CLEAN", "PEMASARAN_CODE"]],
           "Data Bersih (KUANTITAS & PEMASARAN)")

# ---------------- NORMALIZE (raw + rounded) ----------------
scaler = MinMaxScaler()
X = df[["KUANTITAS_CLEAN", "PEMASARAN_CODE"]].to_numpy(dtype=float)
X_norm = scaler.fit_transform(X)  # full precision internal

# store raw normalized columns (for elbow & computation)
df["QUANTITY_NORM_RAW"] = X_norm[:, 0]
df["PEMASARAN_NORM_RAW"] = X_norm[:, 1]

# store rounded normalized for human reading
df["QUANTITY_NORM"] = np.round(df["QUANTITY_NORM_RAW"], 2)
df["PEMASARAN_NORM"] = np.round(df["PEMASARAN_NORM_RAW"], 2)

save_csv(df, "01_data_normalized.csv")
print_head(df[["NAMA UMKM", "KUANTITAS_CLEAN", "PEMASARAN_CODE", "QUANTITY_NORM", "PEMASARAN_NORM"]],
           "01_data_normalized (sample)")

# ==============================
# 📉 METODE ELBOW (SSE) & PLOT
# Use the logic you provided to determine k_opt
# ==============================
X_for_elbow = df[["QUANTITY_NORM_RAW", "PEMASARAN_NORM_RAW"]].to_numpy()
sse = []
# safe K_range: 1 .. min(10, n_samples)
max_k = min(10, max(1, len(df)))
K_range = list(range(1, max_k + 1))

for k in K_range:
    km = KMeans(n_clusters=k, init="k-means++", n_init=20, random_state=42)
    km.fit(X_for_elbow)
    sse.append(km.inertia_)

elbow_df = pd.DataFrame({"k": K_range, "SSE": sse})
save_csv(elbow_df, "02_elbow_sse.csv")

fig = plt.figure(figsize=(6, 4))
plt.plot(K_range, sse, "bo-")
plt.xlabel("Jumlah Cluster (k)")
plt.ylabel("SSE (Inertia)")
plt.title("Elbow Method - SSE vs k")
plt.grid(True)
plt.tight_layout()
save_plot(fig, "03_elbow_plot.png")

print("💾 Disimpan: 02_elbow_sse.csv & 03_elbow_plot.png")

# ==============================
# 🎯 TENTUKAN K OPTIMAL (stabil, fallback ke 3)
# ==============================
if len(sse) < 3:
    k_opt = min(3, len(df))
else:
    sse_diff = np.diff(sse)
    sse_ratio = np.abs(sse_diff / np.array(sse[:-1]))
    threshold = 0.1 * np.max(sse_ratio)
    candidate = int(np.argmax(sse_ratio < threshold) + 1)
    k_opt = candidate if 2 <= candidate <= 6 else 3

print(f"\n🔍 Jumlah cluster optimal (Elbow Method): k = {k_opt}")

# ------------------ use k_opt for rest of pipeline ------------------

# ---------------- INITIAL CENTROIDS (kmeans++) ----------------
init_centroids, _ = kmeans_plusplus(X_norm, k_opt, random_state=42)
init_cent_df = pd.DataFrame(np.round(init_centroids, 2), columns=["QUANTITY_NORM", "PEMASARAN_NORM"])
init_cent_df.insert(0, "CENTROID_ID", range(1, k_opt+1))
save_csv(init_cent_df, "04_initial_centroids_norm.csv")

init_cent_orig = scaler.inverse_transform(init_centroids)
init_cent_orig_df = pd.DataFrame(np.round(init_cent_orig, 2), columns=["KUANTITAS", "PEMASARAN"])
init_cent_orig_df.insert(0, "CENTROID_ID", range(1, k_opt+1))
save_csv(init_cent_orig_df, "05_initial_centroids_orig.csv")

# ---------------- MANUAL K-MEANS ITERATIONS (with logs) ----------------
n = X_norm.shape[0]
k = k_opt
np.random.seed(42)
centroids = init_centroids.copy()

# Containers for aggregate logs
centroids_all = []       # list dict: iteration, cluster, qty_norm, pemasaran_norm
euclidean_logs = []      # list dict per point per iter
cluster_assignments = {int(pid): [] for pid in df["partner_id"]}
mean_distance_logs = []

print("\n🔁 Menjalankan iterasi K-Means manual dan menyimpan logs per iterasi...")
for iteration in range(1, MAX_ITERS + 1):
    # distances (n x k)
    distances = np.sqrt(((X_norm[:, np.newaxis, :] - centroids[np.newaxis, :, :]) ** 2).sum(axis=2))
    labels = np.argmin(distances, axis=1)

    # save euclidean per point for this iteration
    for i in range(n):
        row = {"iteration": iteration, "partner_id": int(df.iloc[i]["partner_id"])}
        for c in range(k):
            row[f"euclidean_c{c+1}"] = round(float(distances[i, c]), 6)
        row["assigned_cluster"] = int(labels[i]) + 1
        euclidean_logs.append(row)
        cluster_assignments[int(df.iloc[i]["partner_id"])].append(int(labels[i]) + 1)

    # mean distance per cluster
    mean_distances = []
    for c in range(k):
        mask = labels == c
        if np.any(mask):
            mean_d = float(np.mean(distances[mask, c]))
            mean_distances.append(mean_d)
        else:
            mean_distances.append(np.nan)
    mean_row = {"iteration": iteration}
    for c in range(k):
        mean_row[f"mean_dist_c{c+1}"] = round(mean_distances[c], 6) if not np.isnan(mean_distances[c]) else None
    mean_distance_logs.append(mean_row)

    # save centroid positions for this iteration
    for c in range(k):
        centroids_all.append({
            "iteration": iteration,
            "cluster": c+1,
            "qty_norm": round(float(centroids[c, 0]), 4),
            "pemasaran_norm": round(float(centroids[c, 1]), 4)
        })
    # also write centroid file per iteration (readable)
    cent_df_iter = pd.DataFrame([{
        "cluster": c+1,
        "qty_norm": round(float(centroids[c, 0]), 4),
        "pemasaran_norm": round(float(centroids[c, 1]), 4)
    } for c in range(k)])
    cent_df_iter.to_csv(OUTDIR / f"centroid_iter_{iteration}.csv", index=False)

    # update centroids
    new_centroids = np.array([X_norm[labels == c].mean(axis=0) if np.any(labels == c) else centroids[c] for c in range(k)])
    delta = np.linalg.norm(new_centroids - centroids)
    print(f"Iter {iteration}: Δcentroid = {delta:.6f}")

    centroids = new_centroids

    # if converged -> break
    if delta < TOL:
        print(f"✅ Konvergen setelah {iteration} iterasi\n")
        break

# ---------------- SAVE AGGREGATE LOGS ----------------
centroids_all_df = pd.DataFrame(centroids_all)
centroids_all_df.to_csv(OUTDIR / "04_centroid_per_iterasi.csv", index=False)

euclid_df = pd.DataFrame(euclidean_logs)
euclid_df.to_csv(OUTDIR / "05_euclidean_per_iterasi.csv", index=False)

# cluster per iter - wide format
cluster_wide = pd.DataFrame({"partner_id": list(cluster_assignments.keys())})
for it in range(1, iteration + 1):
    cluster_wide[f"cluster_iter_{it}"] = [cluster_assignments[pid][it-1] for pid in cluster_wide["partner_id"]]
cluster_wide.to_csv(OUTDIR / "06_cluster_per_iterasi.csv", index=False)

mean_df = pd.DataFrame(mean_distance_logs)
mean_df.to_csv(OUTDIR / "07_mean_distance_per_iter.csv", index=False)

# final centroids norm & original
final_centroids_norm = pd.DataFrame({
    "cluster": np.arange(1, k+1),
    "qty_norm": np.round(centroids[:, 0], 4),
    "pemasaran_norm": np.round(centroids[:, 1], 4)
})
final_centroids_norm.to_csv(OUTDIR / "08_centroid_final_norm.csv", index=False)

final_centroids_orig = pd.DataFrame(scaler.inverse_transform(centroids), columns=["total_quantity", "pemasaran_code"])
final_centroids_orig["cluster"] = np.arange(1, k+1)
final_centroids_orig[["total_quantity"]] = final_centroids_orig[["total_quantity"]].round(2)
final_centroids_orig.to_csv(OUTDIR / "09_centroid_final_original.csv", index=False)

# final clustered table
df["cluster"] = labels + 1
# add per-iter columns for convenience
for it in range(1, iteration + 1):
    df[f"cluster_iter_{it}"] = cluster_wide[f"cluster_iter_{it}"].values
df.to_csv(OUTDIR / "10_final_cluster_table.csv", index=False)

# final elbow & normalized already saved earlier
print("💾 Disimpan semua file:\n - 01..10 (see folder)")

# ---------------- UPDATE DATABASE (final clusters) ----------------
cursor = conn.cursor()
for _, row in df.iterrows():
    cursor.execute(
        "UPDATE batik_umkm_partner SET cluster = %s WHERE partner_id = %s",
        (int(row["cluster"]), int(row["partner_id"]))
    )
conn.commit()
cursor.close()
conn.close()

print("\n✅ Semua data berhasil diupdate dan disimpan.")
print(f"📁 Hasil lengkap ada di folder: {OUTDIR.resolve()}")
