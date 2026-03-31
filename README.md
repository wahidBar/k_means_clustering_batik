🧠 K-Means Clustering for UMKM Batik Sumenep
Data-Driven Segmentation System (Laravel + Python Integration)

A hybrid web application that integrates Laravel (backend system) with Python (machine learning processing) to cluster UMKM Batik businesses based on production capacity and market reach.

📌 Overview

This project is built to analyze and segment UMKM Batik in Sumenep using the K-Means clustering algorithm, enabling data-driven insights for:

Business classification
Market expansion strategies
Government or stakeholder decision-making

The system automatically processes real data and groups UMKM into meaningful clusters.

🏗️ Architecture (Real Implementation)
Laravel (PHP)
│
├── Controller (ClusteringController.php)
│        ↓
│   Execute Python Script
│        ↓
Python (ClusteringController.py)
│
├── Data Fetch (MySQL)
├── Preprocessing (MinMaxScaler)
├── K-Means Training
├── Elbow Method (Auto K Detection)
│
└── Output → /hasil_cluster_umkm (CSV / Result)
│
↓
Laravel reads & displays results
⚙️ Tech Stack
Backend System
Laravel (PHP Framework)
MVC Architecture
MySQL Database
Machine Learning
Python 3
Pandas
NumPy
Scikit-learn (KMeans)
Matplotlib (Elbow Visualization)
Integration
Shell execution (shell_exec)
Shared database (MySQL)
File-based output (CSV)
🔥 Key Features
✅ Automatic UMKM clustering using K-Means
✅ Auto-detection of optimal cluster (Elbow Method)
✅ Data normalization (MinMaxScaler)
✅ Integration Laravel ↔ Python (real execution)
✅ Result export (CSV in /hasil_cluster_umkm)
✅ Database-driven processing (no dummy data)
🧠 Machine Learning Pipeline
1. Data Source

Data fetched directly from MySQL:

Monthly Production
Market Coverage
2. Preprocessing
MinMaxScaler()
Normalize values to avoid bias between features
3. Clustering
KMeans(n_clusters=k, init='k-means++')
4. Optimal K Detection

Using Elbow Method:

Iterates multiple K values
Measures inertia (distance)
Automatically determines best K
5. Output

Generated files:

/hasil_cluster_umkm/
├── clustering_result.csv
├── centroid.csv
├── elbow_plot.png
🔌 Laravel ↔ Python Integration
Execution from Laravel:
$output = shell_exec("python3 app/Http/Controllers/ClusteringController.py");
Flow:
User triggers clustering from Laravel
Laravel executes Python script
Python processes data & saves result
Laravel reads result and displays
📂 Project Structure (Based on Your Code)
K_MEANS_CLUSTERING_BATIK/
│
├── app/
│   └── Http/
│       └── Controllers/
│           ├── ClusteringController.php
│           └── ClusteringController.py  👈 ML Logic
│
├── database/
├── hasil_cluster_umkm/ 👈 Output hasil clustering
├── resources/views/
├── routes/
├── .env
└── ...
🗄️ Database Configuration (Python Side)
db_config = {
    "host": "localhost",
    "user": "pma",
    "password": "1234567",
    "database": "db_k_means_clustering_batik"
}
📊 Example Clustering Result
Cluster	Description
C1	High Production - Wide Market
C2	Medium Production - Regional
C3	Low Production - Local
🚀 Installation
1. Clone Project
git clone https://github.com/yourusername/k_means_clustering_batik.git
cd k_means_clustering_batik
2. Setup Laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
3. Setup Python Environment
python3 -m venv .venv
source .venv/bin/activate

pip install pandas numpy scikit-learn matplotlib mysql-connector-python
4. Run Application
php artisan serve
5. Run Clustering

Trigger via:

Web button (recommended)
Or manually:
python3 app/Http/Controllers/ClusteringController.py
🎯 Use Cases
📊 UMKM classification & segmentation
🏛 Government data analysis
📈 Business growth strategy
🧠 Data science learning implementation
🏆 Why This Project Stands Out
🔥 Real-world dataset (not dummy)
🔥 Hybrid architecture (Laravel + Python ML)
🔥 Auto clustering optimization (Elbow Method)
🔥 Clean separation between system & ML logic
🔥 Production-like structure (controller, DB, output)
📬 Contact
📧 Email: your-email@example.com
💻 GitHub: github.com/yourusername
💼 LinkedIn: linkedin.com/in/yourprofile
⭐ Final Note

This project demonstrates how machine learning can be practically integrated into a production-ready web application, bridging backend engineering with data science.
