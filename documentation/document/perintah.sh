# pembuatan laravel project
composer create-project laravel/laravel backend

#buat project vue
pnpm create vue@latest

# compose up
podman compose up -d 2>&1 | tee documentation/document/compose-up.txt

# migrate and seed database
podman exec -it uts-kepl-backend php artisan migrate --seed 2>&1 | tee documentation/document/migrate.txt