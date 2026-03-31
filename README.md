# 🧠 K-Means Clustering UMKM Batik Sumenep

Sistem ini merupakan aplikasi berbasis **Laravel + Python** yang digunakan untuk melakukan **segmentasi UMKM Batik di Sumenep** menggunakan algoritma **K-Means Clustering**.

Aplikasi ini membantu mengelompokkan UMKM berdasarkan **produksi bulanan** dan **jangkauan pasar**, sehingga dapat digunakan untuk analisis bisnis dan pengambilan keputusan berbasis data.

---

## 🚀 Fitur Utama

📊 **Clustering Otomatis (K-Means)**  
Mengelompokkan UMKM ke dalam beberapa cluster berdasarkan data produksi dan pemasaran.

📈 **Auto Optimal Cluster (Elbow Method)**  
Menentukan jumlah cluster terbaik secara otomatis.

⚖️ **Normalisasi Data**  
Menggunakan *MinMaxScaler* agar hasil clustering lebih akurat.

🔗 **Integrasi Laravel + Python**  
Laravel sebagai backend, Python sebagai engine machine learning.

📂 **Export Hasil Clustering**  
Hasil disimpan dalam bentuk CSV:
- clustering_result.csv  
- centroid.csv  
- elbow_plot.png  

🗄️ **Database Driven**  
Menggunakan data real dari MySQL (bukan dummy).

---

## ⚙️ Teknologi

- **Laravel (PHP)** – Backend & Web System  
- **Python 3** – Machine Learning Processing  
- **Pandas & NumPy** – Data Processing  
- **Scikit-learn** – K-Means Algorithm  
- **Matplotlib** – Visualisasi Elbow Method  
- **MySQL** – Database  
- **Shell Execution** – Integrasi Laravel ke Python  

---

## 🧠 Cara Kerja Sistem

1. Data UMKM diambil dari database MySQL  
2. Data dinormalisasi menggunakan MinMaxScaler  
3. Sistem mencari jumlah cluster terbaik (Elbow Method)  
4. K-Means dijalankan untuk clustering  
5. Hasil disimpan ke folder `/hasil_cluster_umkm`  
6. Laravel menampilkan hasil ke user  

---

## 📁 Struktur Project
K_MEANS_CLUSTERING_BATIK/
│
├── app/Http/Controllers/
│ ├── ClusteringController.php
│ └── ClusteringController.py
│
├── hasil_cluster_umkm/
├── database/
├── resources/views/
├── routes/
└── .env

---

## 🔌 Integrasi Laravel ↔ Python

Laravel menjalankan Python script:

```php
$output = shell_exec("python3 app/Http/Controllers/ClusteringController.py");
🧪 Cara Menjalankan
Clone repository
git clone https://github.com/yourusername/k_means_clustering_batik.git
cd k_means_clustering_batik
Setup Laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
Setup Python
python3 -m venv .venv
source .venv/bin/activate

pip install pandas numpy scikit-learn matplotlib mysql-connector-python
Jalankan aplikasi
php artisan serve
Jalankan clustering
python3 app/Http/Controllers/ClusteringController.py
📊 Use Case
Analisis UMKM oleh pemerintah
Segmentasi bisnis
Penentuan strategi pemasaran
Implementasi machine learning di web app
📌 Catatan

Sistem ini menggunakan data real UMKM Batik Sumenep dan dirancang untuk menunjukkan bagaimana machine learning dapat diintegrasikan ke dalam aplikasi web backend secara nyata.

📜 Lisensi

Proyek ini menggunakan lisensi MIT.
Silakan digunakan, dikembangkan, dan dimodifikasi sesuai kebutuhan.


---

Kalau kamu mau versi yang **lebih “wah” lagi (pakai badge, screenshot, GIF demo, dll)** bilang aja — itu yang biasanya bikin recruiter langsung tertarik 🔥
