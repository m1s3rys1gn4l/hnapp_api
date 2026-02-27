# OTP API Configuration Guide

## Overview
The OTP service uses **GreenWeb SMS API** (Bangladesh) to send verification codes.

## Configuration

Add these variables to your `.env` file:

```env
OTP_PROVIDER=greenweb
OTP_API_KEY=your_greenweb_token_here
```

## GreenWeb SMS API

### API Details
- **Provider**: GreenWeb (Bangladesh)
- **Endpoint**: `http://api.greenweb.com.bd/api.php`
- **Method**: POST (form-encoded)
- **Website**: https://greenweb.com.bd/

### Request Format
```php
POST http://api.greenweb.com.bd/api.php
Content-Type: application/x-www-form-urlencoded

to=8801712345678&message=Your OTP: 123456&token=your_token_here
```

### Response
- **Success**: Starts with `Ok`
- **Failure**: Error message string

### Message Format
The system sends Bangla + English messages:
```
Hisab Nikash আপনার ওটিপি কোড: 123456
Valid for 10 minutes. #123456
```

## Testing

### Development Mode
In local environment, OTPs are logged to `storage/logs/laravel.log` instead of sending real SMS.

### Get Test OTP
```bash
curl http://localhost:8000/api/auth/otp/test?phone=01712345678
```

Response:
```json
{
  "test_otp": "123456",
  "message": "For development/testing only"
}
```

## Popular SMS Providers

### Bangladesh SMS Providers
- **SSL Wireless**: https://sslwireless.com/
- **Banglalink**: https://www.banglalink.net/en/sms-api
- **Grameenphone**: SMS API
- **Robi**: Bulk SMS Service

### International Providers
- **Twilio**: https://www.twilio.com/
- **AWS SNS**: https://aws.amazon.com/sns/
- **Vonage (Nexmo)**: https://www.vonage.com/

## API Endpoints

### Public Endpoints (No Auth Required)

#### Send OTP
```
POST /api/auth/otp/send
{
  "phone": "01712345678"
}
```

#### Verify OTP
```
POST /api/auth/otp/verify
{
  "phone": "01712345678",
  "otp_code": "123456"
}
```

### Protected Endpoints (Require Firebase Auth)

#### Send Phone Verification OTP (After Email Login)
```
POST /api/auth/phone/send-otp
Headers: Authorization: Bearer <firebase_token>
{
  "phone": "01712345678"
}
```

#### Verify Phone Number
```
POST /api/auth/phone/verify
Headers: Authorization: Bearer <firebase_token>
{
  "phone": "01712345678",
  "otp_code": "123456"
}
```

#### Check Phone Verification Status
```
GET /api/auth/phone/status
Headers: Authorization: Bearer <firebase_token>
```

## Security Best Practices

1. **Rate Limiting**: Maximum 1 OTP per minute per phone number
2. **OTP Expiry**: 10 minutes
3. **Maximum Attempts**: 3 attempts per OTP
4. **Cache Storage**: OTPs stored in Laravel cache (Redis/Database)
5. **Production**: Disable test endpoint in production

## Troubleshooting

### OTP Not Received
1. Check `storage/logs/laravel.log` for errors
2. Verify API credentials in `.env`
3. Test API endpoint with Postman
4. Check phone number format (should start with country code)

### API Errors
Common errors logged:
- "OTP API URL not configured" - Missing `OTP_API_URL` in `.env`
- "OTP API failed" - API returned error (check API logs)
- "Failed to send OTP" - Network/connection error

## Phone Number Format

Phone numbers are automatically normalized:
- `01712345678` → `8801712345678` (Bangladesh)
- `+8801712345678` → `8801712345678`
- International format supported

## Next Steps

1. Sign up for an SMS provider
2. Get API credentials
3. Update `.env` with your API details
4. Test with development endpoint
5. Deploy to production
