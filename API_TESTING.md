# Hisab Nikash API Testing Guide

## Base URL
```
http://localhost:8000/api
```

## Authentication
All protected endpoints require a Firebase ID token in the Authorization header:
```
Authorization: Bearer <FIREBASE_ID_TOKEN>
```

## Endpoints

### Health Check
```
GET /api/
Response: {"status": "ok", "message": "Hisab Nikash API is running"}
```

### Sync Endpoints

#### Pull Data (Download from server)
```
POST /api/sync/pull
Headers: Authorization: Bearer <token>
Response: {
  "books": [...],
  "clients": [...],
  "transactions": [...]
}
```

#### Push Data (Upload to server)
```
POST /api/sync/push
Headers: Authorization: Bearer <token>
Body: {
  "books": [...],
  "clients": [...],
  "transactions": [...]
}
Response: {"message": "Data synced successfully"}
```

#### Bidirectional Sync
```
POST /api/sync
Headers: Authorization: Bearer <token>
Body: {
  "books": [...],
  "clients": [...],
  "transactions": [...]
}
Response: {
  "books": [...],
  "clients": [...],
  "transactions": [...]
}
```

### Books

#### List all books
```
GET /api/books
Headers: Authorization: Bearer <token>
```

#### Get single book
```
GET /api/books/{id}
Headers: Authorization: Bearer <token>
```

#### Create book
```
POST /api/books
Headers: Authorization: Bearer <token>
Body: {
  "id": "uuid-optional",
  "name": "Book Name",
  "is_pinned": false,
  "default_client_id": "uuid-optional"
}
```

#### Update book
```
PUT /api/books/{id}
Headers: Authorization: Bearer <token>
Body: {
  "name": "Updated Name",
  "is_pinned": true
}
```

#### Delete book
```
DELETE /api/books/{id}
Headers: Authorization: Bearer <token>
```

### Clients

#### List all clients
```
GET /api/clients
Headers: Authorization: Bearer <token>
```

#### Get single client
```
GET /api/clients/{id}
Headers: Authorization: Bearer <token>
```

#### Create client
```
POST /api/clients
Headers: Authorization: Bearer <token>
Body: {
  "id": "uuid-optional",
  "name": "Client Name"
}
```

#### Update client
```
PUT /api/clients/{id}
Headers: Authorization: Bearer <token>
Body: {
  "name": "Updated Name"
}
```

#### Delete client
```
DELETE /api/clients/{id}
Headers: Authorization: Bearer <token>
```

### Transactions

#### List all transactions (with optional filters)
```
GET /api/transactions?book_id={uuid}&client_id={uuid}&date_from={YYYY-MM-DD}&date_to={YYYY-MM-DD}
Headers: Authorization: Bearer <token>
```

#### Get single transaction
```
GET /api/transactions/{id}
Headers: Authorization: Bearer <token>
```

#### Create transaction
```
POST /api/transactions
Headers: Authorization: Bearer <token>
Body: {
  "id": "uuid-optional",
  "book_id": "uuid-required",
  "client_id": "uuid-optional",
  "type": "in|out",
  "amount": 1000.50,
  "note": "Payment description",
  "category": "category-name",
  "date": "2026-02-27"
}
```

#### Update transaction
```
PUT /api/transactions/{id}
Headers: Authorization: Bearer <token>
Body: {
  "amount": 1500.00,
  "note": "Updated description"
}
```

#### Delete transaction
```
DELETE /api/transactions/{id}
Headers: Authorization: Bearer <token>
```

## Testing with PowerShell

### Test Health Check (No Auth Required)
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/" -Method GET
```

### Test Protected Endpoints (Need Firebase Token)
```powershell
$token = "YOUR_FIREBASE_ID_TOKEN"
$headers = @{
    "Authorization" = "Bearer $token"
    "Content-Type" = "application/json"
}

# List books
Invoke-RestMethod -Uri "http://localhost:8000/api/books" -Method GET -Headers $headers

# Create book
$body = @{
    name = "Test Book"
    is_pinned = $false
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/books" -Method POST -Headers $headers -Body $body
```

## Error Responses

### 401 Unauthorized
```json
{
  "error": "Unauthorized",
  "message": "Firebase ID token is required"
}
```

### 422 Validation Error
```json
{
  "message": "The name field is required.",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

### 404 Not Found
```json
{
  "message": "No query results for model [App\\Models\\Book] {id}"
}
```

## Next Steps

1. **Flutter App Integration:**
   - Add `firebase_auth` package
   - Implement Firebase Authentication (Email/Password + Google Sign-In)
   - Create `ApiService` to communicate with this backend
   - Get Firebase ID token: `await FirebaseAuth.instance.currentUser?.getIdToken()`

2. **Data Sync Flow:**
   - User signs in → Get Firebase ID token
   - On app start → Call `/sync/pull` to download server data
   - When creating/updating locally → Call `/sync/push` to upload changes
   - Conflict resolution strategy: Server data wins (or implement custom logic)

3. **Offline Support:**
   - Keep using Hive for local storage
   - Queue failed API requests
   - Retry on network reconnection
   - Show sync status to user
