# pembuatan laravel project
composer create-project laravel/laravel backend

#buat project vue
pnpm create vue@latest

# compose up
podman compose up -d 2>&1 | tee documentation/document/compose-up.txt

# check running containers
podman ps 2>&1 | tee documentation/document/podman-ps.txt

# migrate and seed database
podman exec -it uts-kepl-backend php artisan migrate --seed 2>&1 | tee documentation/document/migrate.txt

# check container logs
podman logs uts-kepl-backend 2>&1 | tee documentation/document/container-logs.txt

# ==============================================================================
# PERINTAH PENGUJIAN OBSERVABILITAS, STRUCTURED LOGGING, DAN HEALTHCHECK
# ==============================================================================

# 1. Pembangunan ulang dan pengaktifan service dengan konfigurasi logging stdout & healthcheck
podman compose up -d --build 2>&1 | tee documentation/document/compose-up.txt

# 2. Pemeriksaan status seluruh container (kolom STATUS berstatus healthy)
podman ps 2>&1 | tee documentation/document/podman-ps.txt

# 3. Pengujian endpoint /health saat seluruh service (db & cache) normal (HTTP 200 OK)
curl -i http://localhost:8007/health 2>&1 | tee documentation/document/health-200-healthy.txt

# 4. Pengambilan structured logs dari stdout container backend (mencatat info, warning, dan context who/action/reason)
podman compose logs backend 2>&1 | tee documentation/document/structured-logs-stdout.txt

# 5. Simulasi kegagalan basis data: mematikan container db secara sengaja
podman compose stop db 2>&1 | tee documentation/document/db-stop.txt

# 6. Pengujian endpoint /health saat database mati (HTTP 503 Service Unavailable dan menyebutkan database bermasalah)
curl -i http://localhost:8007/health 2>&1 | tee documentation/document/health-503-unhealthy.txt

# 7. Pembuktian status container backend berubah menjadi unhealthy pada podman ps
podman ps 2>&1 | tee documentation/document/db-stopped-unhealthy-ps.txt

# 8. Pembuktian log container backend mencatat penyebab kegagalan koneksi database
podman compose logs backend 2>&1 | tail -n 35 | tee documentation/document/db-stopped-unhealthy-logs.txt

# 9. Pemulihan layanan database: menyalakan kembali container db
podman compose start db 2>&1 | tee documentation/document/db-start.txt

# 10. Verifikasi endpoint /health kembali berstatus 200 OK setelah db pulih
curl -i http://localhost:8007/health 2>&1 | tee documentation/document/health-recovered-200.txt

# 11. Pembuktian status container backend kembali menjadi healthy pada podman ps
podman ps 2>&1 | tee documentation/document/db-restarted-healthy-ps.txt

# 12. Pemeriksaan pembatasan ukuran log (max-size 10MB dan max-file) pada konfigurasi container
podman inspect uts-kepl-backend --format '{{json .HostConfig.LogConfig}}' 2>&1 | tee documentation/document/logging-config.txt

# ==============================================================================
# PERINTAH PERTEMUAN 11: MONITORING PROMETHEUS, GRAFANA, CADVISOR, NODE-EXPORTER
# ==============================================================================

# 1. Pengaktifan seluruh stack aplikasi dan 4 layanan pemantau (prometheus, grafana, cadvisor, node-exporter)
podman compose up -d --build 2>&1 | tee documentation/document/p11-compose-up.txt

# 2. Pemeriksaan status seluruh container aplikasi dan pemantau
podman ps 2>&1 | tee documentation/document/p11-podman-ps.txt

# 3. Pengujian endpoint /metrics pada backend Laravel dalam format Prometheus
curl -s http://localhost:8007/metrics 2>&1 | tee documentation/document/p11-metrics-output.txt

# 4. Pemeriksaan status seluruh target Prometheus (/api/v1/targets) harus UP
curl -s http://localhost:9090/api/v1/targets 2>&1 | tee documentation/document/p11-targets-up.txt

# 5. Verifikasi keamanan Grafana: pastikan login admin/admin ditolak (401) dan password baru berhasil (200)
(curl -s -i -u "admin:admin" http://localhost:3002/api/org | head -n 5; echo "---"; curl -s -i -u "admin:KeplGrafana2026!" http://localhost:3002/api/org | head -n 10) 2>&1 | tee documentation/document/p11-grafana-auth.txt

# 6. Simulasi alarm: mematikan layanan backend secara sengaja untuk memicu alert LayananBackendMati
podman compose stop backend 2>&1 | tee documentation/document/p11-backend-stop.txt

# 7. Verifikasi status alert di Prometheus API (/api/v1/alerts) sudah berubah menjadi FIRING
curl -s http://localhost:9090/api/v1/alerts 2>&1 | tee documentation/document/p11-alert-firing.txt