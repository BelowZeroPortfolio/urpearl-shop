# Google OAuth Setup Guide

This guide explains how to configure Google OAuth authentication for UrPearl SHOP.

## Prerequisites

1. A Google Cloud Platform account
2. A project created in Google Cloud Console

## Setup Steps

### 1. Create Google OAuth Credentials

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Select your project or create a new one
3. Navigate to "APIs & Services" > "Credentials"
4. Click "Create Credentials" > "OAuth 2.0 Client IDs"
5. Configure the OAuth consent screen if prompted
6. Select "Web application" as the application type
7. Add authorized redirect URIs:
    - For local development: `http://localhost:8000/auth/google/callback`
    - For production: `https://yourdomain.com/auth/google/callback`

### 2. Configure Environment Variables

Copy the Client ID and Client Secret from Google Cloud Console and add them to your `.env` file:

```env
GOOGLE_CLIENT_ID=your_google_client_id_here
GOOGLE_CLIENT_SECRET=your_google_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

### 3. Test the Integration

1. Start your Laravel development server: `php artisan serve`
2. Visit `http://localhost:8000/login`
3. Click "Continue with Google"
4. Complete the OAuth flow

## Features Implemented

-   ✅ Google OAuth redirect
-   ✅ User creation with Google profile data
-   ✅ Automatic role assignment (buyer by default)
-   ✅ Admin user redirection to dashboard
-   ✅ User avatar from Google profile
-   ✅ Session management
-   ✅ Logout functionality
-   ✅ Responsive login page design
-   ✅ Error handling for OAuth failures

## User Roles

-   **Buyer**: Default role for new users, can shop and place orders
-   **Admin**: Can access admin dashboard and manage the platform

To make a user an admin, update their role in the database:

```sql
UPDATE users SET role = 'admin' WHERE email = 'admin@example.com';
```

## Security Notes

-   OAuth credentials should never be committed to version control
-   Use different credentials for development and production
-   Regularly rotate OAuth secrets
-   Monitor OAuth usage in Google Cloud Console

## Troubleshooting

### Common Issues

1. **"redirect_uri_mismatch" error**: Ensure the redirect URI in Google Cloud Console matches exactly with your application URL
2. **"invalid_client" error**: Check that your Client ID and Client Secret are correct
3. **"access_denied" error**: User cancelled the OAuth flow or your app needs verification

### Testing Without Database

The authentication system requires a database connection. Ensure your database is set up and migrations are run before testing.
