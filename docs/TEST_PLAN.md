# Test Plan — Online Bike Rental System
Student: NDARIHORANYE THIERRY (224008249)

**Environment:** PHP 8.x, MySQL/MariaDB (XAMPP), Chrome. Start from a fresh `schema.sql` + `seed.sql`.
**Result column:** automated rows (T1–T23) are executed by `tests/smoke_test.sh` (all 23 PASS in my run, see README note on environment). Manual rows (M1–M4) must be ticked by hand and screenshotted for evidence.

| ID | Type | Precondition | Action / input | Expected result |
|---|---|---|---|---|
| T1 | Failure | Logged out | Login `admin` + wrong password | Stay on login; "Invalid username or password" (same message for unknown user) |
| T2 | Failure | Logged out | Submit login with empty fields | Field errors "Username is required" / "Password is required" |
| T3 | Failure/Security | Logged out | POST login without CSRF token | HTTP 403 |
| T4 | Happy | Seeded | Login `admin` / `Admin@123` | 302 redirect to dashboard (Post-Redirect-Get) + welcome flash |
| T5 | Failure/Security | Logged out | Open `?route=bikes` | 302 redirect to login |
| T6 | Failure | Logged in | Create bike with empty model and rate −5 | No row inserted; errors shown; entered code preserved |
| T7 | Happy | Logged in | Create bike `BK-T…`, model "Test Bike", rate 1000, available | Row inserted; redirect to bike page; success flash |
| T8 | Failure | T7 done | Create another bike with the same code | "A bike with this code already exists"; no second row |
| T9 | Security | Logged in | Create bike with model `Bad <script>alert(1)</script>` | Bike list shows escaped text (`&lt;script&gt;`); script does not run |
| T10 | Happy (API) | Logged in | `GET ?route=api-bikes&search=trek` | JSON `{data:[…],meta:{count}}` containing Trek Marlin |
| T11 | Failure (API) | Logged in | `GET ?route=api-bikes&status=bogus` | HTTP 400 with JSON message |
| T12 | Failure (API) | Logged out | `GET ?route=api-bikes` | HTTP 401 with JSON message |
| T13 | Security | Logged in | API search `' OR '1'='1` | `count` = 0 (treated as literal text by prepared statement) |
| T14 | Happy | An available bike exists | Start rental for customer 1 | Rental created, status active, bike becomes rented |
| T15 | Failure | T14 done | Start another rental for the same (now rented) bike | "This bike is not available for rent"; no new row |
| T16 | Happy | T14 done | Return the bike | Status completed, end_time set, cost = ceil(hours) × rate, bike available again |
| T17 | Failure/Security | T16 done | Open `?route=rental-return&id=N` via GET | HTTP 405 (mutations are POST-only) |
| T18 | Failure | Bike has rentals | Delete that bike | Blocked by FK RESTRICT; friendly "cannot be deleted" message, no crash |
| T19 | Failure/Security | Logged in as `staff1` | Open `?route=users` | HTTP 403 |
| T20 | Happy | Logged in as admin | Open `?route=users` | HTTP 200, user list |
| T21 | Failure | Any | Open `?route=does-not-exist` | HTTP 404 page |
| T22 | Happy | Logged out | Register valid username/email/password | Account created (role staff); redirect to login |
| T23 | Failure | Logged out | Register `ab`, `bad`, `short` | Error messages; no account created |
| M1 | Manual / UI | Logged in | Resize to ≤768px | Menu collapses behind ☰ button; tables scroll horizontally; no page overflow |
| M2 | Manual / Async | Bikes page | Type "tr" quickly, then clear | Only one request after 300 ms pause (Network tab); stale responses cancelled; empty state message for no match |
| M3 | Manual / Failure | Bikes page | Disable JavaScript, reload, use Filter button | Server-rendered list and filter still work (progressive enhancement) |
| M4 | Manual / Concurrency & refresh | Rental just created | Press F5 on the redirected page | No duplicate rental (PRG) |

## Evidence to attach before submission
Screenshots of: dashboard, failed validation with preserved input, live-search Network tab showing JSON, test output (`bash tests/smoke_test.sh` on your MySQL), and the phpMyAdmin tables view.
