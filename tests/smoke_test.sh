#!/usr/bin/env bash
# Automated smoke tests (HTTP level) for the Bike Rental System.
# Usage:  BASE="http://localhost/BikeRental/public/index.php" bash tests/smoke_test.sh
# Needs: curl. Run against a freshly seeded database (schema.sql + seed.sql).
# Each line prints PASS/FAIL; exit code is the number of failures.
BASE="${BASE:-http://localhost:8000/index.php}"
JAR_ADMIN=$(mktemp); JAR_STAFF=$(mktemp); JAR_ANON=$(mktemp)
FAIL=0; N=0
SUFFIX=$RANDOM

check() { # name, condition-result(0 ok)
  N=$((N+1))
  if [ "$2" -eq 0 ]; then echo "PASS  T$N  $1"; else echo "FAIL  T$N  $1"; FAIL=$((FAIL+1)); fi
}
code()  { curl -s -o /dev/null -w '%{http_code}' "$@"; }
token() { curl -s -b "$1" -c "$1" "$BASE?route=${2:-login}" | grep -o 'name="csrf-token" content="[^"]*"' | head -1 | sed 's/.*content="//;s/"//'; }
post()  { # jar route data...  (csrf appended)
  local jar="$1" route="$2"; shift 2
  local t; t=$(token "$jar" dashboard); [ -z "$t" ] && t=$(token "$jar" login)
  curl -s -b "$jar" -c "$jar" -o /dev/null -w '%{http_code}|%{redirect_url}' -X POST "$BASE?route=$route" --data-urlencode "csrf=$t" "$@"
}
body()  { curl -s -b "$1" -c "$1" "$BASE?route=$2"; }

# ---- Authentication ----
out=$(curl -s -b "$JAR_ANON" -c "$JAR_ANON" -X POST "$BASE?route=login" --data-urlencode "csrf=$(token "$JAR_ANON" login)" -d "username=admin&password=wrong")
echo "$out" | grep -q 'Invalid username or password'; check "Login with wrong password is rejected (generic message)" $?

curl -s -b "$JAR_ANON" -X POST "$BASE?route=login" --data-urlencode "csrf=$(token "$JAR_ANON" login)" -d "username=&password=" | grep -q 'Username is required'; check "Login with empty fields shows validation errors" $?

[ "$(code -X POST "$BASE?route=login" -d 'username=admin&password=Admin@123')" = "403" ]; check "POST without CSRF token is rejected (403)" $?

r=$(post "$JAR_ADMIN" login -d "username=admin" --data-urlencode "password=Admin@123")
echo "$r" | grep -q '^302.*route=dashboard'; check "Admin login succeeds and redirects to dashboard (PRG)" $?

[ "$(code "$BASE?route=bikes")" = "302" ]; check "Protected page redirects anonymous visitor to login" $?

# ---- Bikes CRUD ----
CODE="BK-T$SUFFIX"
curl -s -b "$JAR_ADMIN" -X POST "$BASE?route=bike-save" --data-urlencode "csrf=$(token "$JAR_ADMIN" dashboard)" -d "bike_id=0&code=$CODE&model=&hourly_rate=-5&status=available" > /tmp/bike_invalid.html
grep -q 'Model is required' /tmp/bike_invalid.html && grep -q "value=\"$CODE\"" /tmp/bike_invalid.html; check "Invalid bike: field errors shown and valid input preserved" $?

r=$(post "$JAR_ADMIN" bike-save -d "bike_id=0&code=$CODE&model=Test+Bike&hourly_rate=1000&status=available")
echo "$r" | grep -q '^302.*route=bike-show'; check "Valid bike is created and redirected (PRG)" $?

curl -s -b "$JAR_ADMIN" -X POST "$BASE?route=bike-save" --data-urlencode "csrf=$(token "$JAR_ADMIN" dashboard)" -d "bike_id=0&code=$CODE&model=Dup&hourly_rate=1000&status=available" | grep -q 'already exists'; check "Duplicate bike code is rejected" $?

XSS='<script>alert(1)</script>'
post "$JAR_ADMIN" bike-save --data-urlencode "bike_id=0" --data-urlencode "code=BK-X$SUFFIX" --data-urlencode "model=Bad $XSS" -d "hourly_rate=900&status=available" >/dev/null
page=$(body "$JAR_ADMIN" bikes)
echo "$page" | grep -q '&lt;script&gt;' && ! echo "$page" | grep -q '<script>alert(1)'; check "XSS payload in model name is escaped on output" $?

# ---- JSON API ----
api=$(curl -s -b "$JAR_ADMIN" -H 'Accept: application/json' "$BASE?route=api-bikes&search=trek")
echo "$api" | grep -q '"data":\[' && echo "$api" | grep -q 'Trek Marlin'; check "API returns JSON data for a live search" $?
[ "$(code -b "$JAR_ADMIN" "$BASE?route=api-bikes&status=bogus")" = "400" ]; check "API rejects invalid status filter with 400" $?
[ "$(code "$BASE?route=api-bikes")" = "401" ]; check "API requires authentication (401)" $?
api=$(curl -s -b "$JAR_ADMIN" "$BASE?route=api-bikes&search=%27+OR+%271%27%3D%271")
echo "$api" | grep -q '"count":0'; check "SQL-injection string in search returns no rows (prepared statements)" $?

# ---- Rentals ----
before=$(curl -s -b "$JAR_ADMIN" "$BASE?route=api-bikes&status=available" | grep -o '"id":[0-9]*' | head -1 | cut -d: -f2)
r=$(post "$JAR_ADMIN" rental-save -d "rental_id=0&bike_id=$before&customer_id=1" --data-urlencode "start_time=$(date -d '-2 hours 30 minutes' '+%Y-%m-%dT%H:%M')")
RID=$(echo "$r" | grep -o 'id=[0-9]*' | cut -d= -f2)
[ -n "$RID" ]; check "Rental starts for an available bike" $?

curl -s -b "$JAR_ADMIN" -X POST "$BASE?route=rental-save" --data-urlencode "csrf=$(token "$JAR_ADMIN" dashboard)" -d "rental_id=0&bike_id=$before&customer_id=2" --data-urlencode "start_time=$(date '+%Y-%m-%dT%H:%M')" | grep -q 'not available'; check "Renting an already-rented bike is rejected" $?

post "$JAR_ADMIN" rental-return -d "id=$RID" >/dev/null
body "$JAR_ADMIN" "rental-show&id=$RID" | grep -q 'Completed'; check "Returning a bike completes the rental and computes cost" $?

[ "$(code -b "$JAR_ADMIN" "$BASE?route=rental-return&id=$RID")" = "405" ]; check "Return action via GET is refused (405)" $?

# ---- Referential integrity ----
r=$(post "$JAR_ADMIN" bike-delete -d "id=$before")
curl -s -b "$JAR_ADMIN" "$BASE?route=bikes" | grep -q 'cannot be deleted'; check "Deleting a bike that has rentals is blocked with a friendly message" $?

# ---- Authorisation ----
post "$JAR_STAFF" login -d "username=staff1" --data-urlencode "password=Staff@123" >/dev/null
[ "$(code -b "$JAR_STAFF" "$BASE?route=users")" = "403" ]; check "Staff user cannot open admin-only Users page (403)" $?
[ "$(code -b "$JAR_ADMIN" "$BASE?route=users")" = "200" ]; check "Admin can open Users page" $?
[ "$(code "$BASE?route=does-not-exist")" = "404" ]; check "Unknown route returns 404" $?

# ---- Registration ----
r=$(post "$JAR_ANON" register -d "username=u$SUFFIX&email=u$SUFFIX@example.com" --data-urlencode "password=Passw0rdX" --data-urlencode "password_confirm=Passw0rdX")
echo "$r" | grep -q '^302.*route=login'; check "Registration with valid data creates an account" $?
curl -s -b "$JAR_ANON" -X POST "$BASE?route=register" --data-urlencode "csrf=$(token "$JAR_ANON" register)" -d "username=ab&email=bad&password=short&password_confirm=x" | grep -q 'valid email'; check "Registration with invalid data shows errors" $?

echo "----"; echo "$((N-FAIL))/$N passed"
rm -f "$JAR_ADMIN" "$JAR_STAFF" "$JAR_ANON" /tmp/bike_invalid.html
exit $FAIL
