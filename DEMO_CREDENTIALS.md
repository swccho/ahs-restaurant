# Demo Admin Credentials

After running migrations and the demo seeder:

```bash
php artisan migrate
php artisan db:seed
```

Use these credentials to log in to the Admin API:

- **Email:** `admin@example.com`
- **Password:** `password`

**Login:** `POST /api/admin/login` with JSON body:

```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

Credentials are also printed when you run `php artisan db:seed`.
