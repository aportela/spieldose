# Spieldose API

If you want to write your own web/desktop/mobile client, you can do it through the following API.

**Requisites**: All data sent and received are in json format (utf-8 encoded)

## User (session) methods

### Sign up (must be allowed on "allowSignUp" configuration settings)

**Request method**: POST

**Request URL**: api/user/signup

**Request payload**:

```javascript
{
    "email": "myusername@myserver",
    "password": "secret"
}
```

**Available response codes**:

#### SUCCESS

HTTP 200 - The user account has been successfully registered

#### COMMON ERRORS

HTTP 409 - The email is already registered in the system

#### FATAL ERRORS

HTTP 400 - Any of the required parameters sent by the client is missing or is incorrect

The following response is sent when the email parameter is not found

```javascript
{
    "invalidOrMissingParams": ["id", "email"]
}
```

The following response is sent when the password parameter is not found

```javascript
{
    "invalidOrMissingParams": ["password"]
}
```

HTTP 500 - For more information you must activate the debug in the configuration and check the log files for errors

---

### Sign in

**Request method**: POST

**Request URL**: api/user/signin

**Request payload**:

```javascript
{
    "email": "myusername@myserver",
    "password": "secret"
}
```

**Available response codes**:

#### SUCCESS

HTTP 200 - The user session started successfully

#### COMMON ERRORS

HTTP 404 - The entered email does not exist as a registered user in the system

HTTP 401 - The password entered is incorrect

#### FATAL ERRORS

HTTP 400 - Any of the required parameters sent by the client is missing or is incorrect

The following response is sent when the email parameter is not found

```javascript
{
    "invalidOrMissingParams": ["id", "email"]
}
```

The following response is sent when the password parameter is not found

```javascript
{
    "invalidOrMissingParams": ["password"]
}
```

HTTP 500 - For more information you must activate the debug in the configuration and check the log files for errors

---

### Poll user session (request to keep the user logged since php server sessions expire due to inactivity)

**Request method**: GET

**Request URL**: api/user/poll
