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

# 1. Pembuatan feature branch baru untuk implementasi monitoring
git checkout -b feature/monitoring-prometheus-grafana

# 2. Pengujian otomatis backend Laravel setelah penambahan endpoint /metrics
cd backend && php artisan test && cd ..

# 3. Checkpoint 1: commit & push instrumentasi endpoint /metrics
git add backend/ && git commit -m "feat(backend): tambah endpoint /metrics dan middleware penghitung request untuk Prometheus"
git push -u origin feature/monitoring-prometheus-grafana

# 4. Build image & aktifkan stack aplikasi + 4 layanan pemantau
podman compose up -d --build 2>&1 | tee documentation/document/p11-compose-up.txt

# 5. Pembersihan container gagal setelah menghapus mount /var/run pada cadvisor, lalu restart stack
podman rm -f uts-kepl-grafana uts-kepl-prometheus uts-kepl-cadvisor
podman compose up -d 2>&1 | tee documentation/document/p11-compose-up.txt

# 6. Pemeriksaan status seluruh container aplikasi dan pemantau (8 container aktif)
podman ps 2>&1 | tee documentation/document/p11-podman-ps.txt

# 7. Pemanasan trafik & pengujian endpoint /health, /api/status, serta /metrics
curl -s http://localhost:8007/health
curl -s http://localhost:8007/api/status
curl -s http://localhost:8007/metrics 2>&1 | tee documentation/document/p11-metrics-output.txt

# 8. Pemeriksaan status seluruh target Prometheus (/api/v1/targets) harus UP
curl -s http://localhost:9090/api/v1/targets 2>&1 | tee documentation/document/p11-targets-up.txt

# 9. Verifikasi label cAdvisor Podman & muat ulang Grafana dengan query PromQL bersih
curl -s --get --data-urlencode 'query=sum by (container) (label_replace(container_memory_working_set_bytes{id=~".*libpod-.*\\.scope/container"}, "container", "$1", "id", ".*libpod-([a-f0-9]{12}).*"))' http://localhost:9090/api/v1/query
curl -s --get --data-urlencode 'query=sum by (container) (label_replace(rate(container_cpu_usage_seconds_total{id=~".*libpod-.*\\.scope/container"}[5m]), "container", "$1", "id", ".*libpod-([a-f0-9]{12}).*")) * 100' http://localhost:9090/api/v1/query
podman restart uts-kepl-grafana

# 10. Checkpoint 2: commit & push konfigurasi compose, prometheus, grafana, cadvisor, node-exporter
git add .env.example compose.yml prometheus.yml alerts.yml monitoring/ documentation/document/perintah.sh documentation/document/p11-compose-up.txt documentation/document/p11-metrics-output.txt documentation/document/p11-podman-ps.txt documentation/document/p11-targets-up.txt
git commit -m "feat(monitoring): add prometheus, grafana, cadvisor, node-exporter stack and verify targets UP"
git push origin feature/monitoring-prometheus-grafana

# 11. Penamaan ulang screenshot batch 1 (/targets, login Grafana, dan dashboard Grafana)
cd documentation/image/p11 && mv Screenshot_20261113_121249.png targets-up.png && mv Screenshot_20261113_121329.png grafana-login.png && mv Screenshot_20261113_121407.png grafana-dashboard.png && cd ../../..

# 12. Verifikasi keamanan Grafana: admin/admin ditolak (401), password baru berhasil (200)
(curl -s -i -u "admin:admin" http://localhost:3002/api/org | head -n 5; echo "---"; curl -s -i -u "admin:KeplGrafana2026!" http://localhost:3002/api/org | head -n 10) 2>&1 | tee documentation/document/p11-grafana-auth.txt

# 13. Simulasi alarm: mematikan layanan backend secara sengaja untuk memicu alert LayananBackendMati
podman compose stop backend 2>&1 | tee documentation/document/p11-backend-stop.txt

# 14. Verifikasi status alert di Prometheus API (/api/v1/alerts) berubah menjadi FIRING
curl -s http://localhost:9090/api/v1/alerts 2>&1 | tee documentation/document/p11-alert-firing.txt

# 15. Checkpoint 3: commit & push bukti auth Grafana, screenshot, dan alert FIRING
git add documentation/image/p11/ documentation/document/perintah.sh documentation/document/p11-grafana-auth.txt documentation/document/p11-backend-stop.txt documentation/document/p11-alert-firing.txt
git commit -m "test(monitoring): verify grafana auth, add screenshots, and trigger FIRING alert"
git push origin feature/monitoring-prometheus-grafana

# 16. Penamaan ulang screenshot batch 2 (dashboard backend DOWN, overview alerts, dan detail FIRING)
cd documentation/image/p11 && mv alert-firing.png alert-firing-overview.png && mv grafana-dashboard-backend-down.png alert-firing.png && mv Screenshot_20261113_122047.png grafana-dashboard-backend-down.png && cd ../../..

# 17. Pemulihan layanan: menyalakan kembali container backend setelah simulasi alert FIRING
podman compose start backend 2>&1 | tee documentation/document/p11-backend-start.txt

# 18. Verifikasi target Prometheus kembali UP dan daftar alert kembali kosong
curl -s http://localhost:9090/api/v1/targets 2>&1 | tee documentation/document/p11-targets-recovered.txt
curl -s http://localhost:9090/api/v1/alerts

# 19. Checkpoint 4: commit & push screenshot FIRING, log pemulihan, dan perintah lengkap
git add documentation/image/p11/ documentation/document/perintah.sh documentation/document/p11-backend-start.txt documentation/document/p11-targets-recovered.txt
git commit -m "docs(monitoring): add FIRING alert and recovery verification screenshots and logs"
git push origin feature/monitoring-prometheus-grafana