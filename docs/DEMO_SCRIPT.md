# 3-minute demo script
1. **(0:00) Problem & roles** – Bike shop: staff manage fleet, customers, rentals. Roles admin/staff. Show login (`admin`/`Admin@123`).
2. **(0:20) Architecture** – Sketch: Browser → front controller → Controller → Validator/Model → PDO → MySQL; views only render. Show folder tree.
3. **(0:45) Create / edit / delete** – Add a bike (valid), then submit an invalid one: errors show, input kept. Edit it; delete it (confirmation dialog).
4. **(1:15) Rental lifecycle** – New rental for an available bike → bike becomes "Rented" → try renting it again (rejected) → Return it: cost = started hours × rate.
5. **(1:50) Live search + JSON** – Bikes page: type "trek"; open DevTools → Network → show `api-bikes` JSON response and the debounce.
6. **(2:15) Security** – Show a form's hidden CSRF token; paste `<script>` into a model name and show it escaped; mention prepared statements and password hashing (show `password_hash` column in phpMyAdmin).
7. **(2:35) Database & tests** – phpMyAdmin: 4 tables, FKs, delete a bike with rentals → friendly error. Run `tests/smoke_test.sh` → 23/23.
8. **(2:50) Trade-offs** – Limitations: no pagination/payments; next: pagination, unit tests, HTTPS deployment.
