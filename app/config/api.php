<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 *
 * Copyright (c) 2020 Ronald M. Marasigan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @since Version 4
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
|--------------------------------------------------------------------------
| Enable/Disable API Helper
|--------------------------------------------------------------------------
|
| The API Helper is disabled by default for security reasons.
| Before enabling it you MUST set the jwt_secret and refresh_token_key
| below (see their notes). The API library refuses to start otherwise.
|
*/
$config['api_helper_enabled'] = FALSE;

/*
|--------------------------------------------------------------------------
| Payload Token Expiration
|--------------------------------------------------------------------------
|
| Used for Payload Token Expiration
|
*/
$config['payload_token_expiration'] = 900;


/*
|--------------------------------------------------------------------------
| Refresh Token Expiration
|--------------------------------------------------------------------------
|
| Used for Refresh Token Expiration
|
*/
$config['refresh_token_expiration'] = 604800;

/*
|--------------------------------------------------------------------------
| JWT Secret Token
|--------------------------------------------------------------------------
|
| Used for Securing endpoint
|
| REQUIRED when api_helper_enabled is TRUE. There is intentionally no
| default value: a secret that ships with the framework is public and
| lets anyone forge valid tokens.
|
| Provide it through the environment variable LAVALUST_JWT_SECRET.
| It must be at least 32 random characters. Generate one with:
|
|   php -r "echo bin2hex(random_bytes(32));"
|
| Never commit the real value to version control. If a secret was ever
| committed or exposed, rotate it. All existing tokens become invalid.
|
*/
$config['jwt_secret'] = getenv('JWT_SECRET') ?: '';

/*
|--------------------------------------------------------------------------
| Refresh Token
|--------------------------------------------------------------------------
|
| Used for Securing endpoint
|
| REQUIRED when api_helper_enabled is TRUE. There is intentionally no
| default value. It is used to hash refresh tokens before they are stored
| in the database and must be different from jwt_secret.
|
| Provide it through the environment variable LAVALUST_REFRESH_TOKEN_KEY.
| It must be at least 32 random characters. Generate one with:
|
|   php -r "echo bin2hex(random_bytes(32));"
|
*/
$config['refresh_token_key'] = getenv('REFRESH_TOKEN_KEY') ?: '';

/*
|--------------------------------------------------------------------------
| Verify User On Each Request
|--------------------------------------------------------------------------
|
| When TRUE, require_jwt() checks that the token's subject exists in the
| users table and takes role and scopes from the database instead of
| trusting the token claims. This costs one indexed query per request.
|
| Set to FALSE only if your users are not stored in the table below and
| you perform your own server-side authorization checks.
|
*/
$config['jwt_verify_user'] = TRUE;

/*
|--------------------------------------------------------------------------
| Users Table
|--------------------------------------------------------------------------
|
| Name of the table holding your users. It needs at least the columns
| "id" and "role". Used when refreshing tokens and when jwt_verify_user
| is TRUE.
|
*/
$config['users_table'] = 'users';

/*
|--------------------------------------------------------------------------
| Access-Control-Allow-Origin
|--------------------------------------------------------------------------
|
| Access-Control-Allow-Origin - change this to your domain if
| already deployed. '*' allows any website to call your API from
| a browser, so set your real domain in production.
|
*/
$config['allow_origin'] = '*';

/*
|--------------------------------------------------------------------------
| Refresh Token Table
|--------------------------------------------------------------------------
|
| This is the name of the table that will store the Refresh Token.
|
*/
$config['refresh_token_table'] = 'refresh_tokens';

/*
|--------------------------------------------------------------------------
| JWT Issuer
|--------------------------------------------------------------------------
| This is used for the JWT Issuer claim (iss). Change it to your
| application's name or URL.
|
*/
$config['jwt_issuer'] = 'your-app';

/*
|--------------------------------------------------------------------------
| JWT Audience
|--------------------------------------------------------------------------
| This is used for the JWT Audience claim (aud). Change it to identify
| the clients allowed to use the tokens.
|
*/

$config['jwt_audience'] = 'your-app-clients';

/*
|--------------------------------------------------------------------------
| Rate Limiting
|--------------------------------------------------------------------------
| These settings are used for API rate limiting.
|
*/
$config['rate_limit_enabled'] = true;

/*
|--------------------------------------------------------------------------
| Rate Limiting Requests and Seconds
|--------------------------------------------------------------------------
| These settings define the number of requests allowed and the time 
| window in seconds.
|
*/
$config['rate_limit_requests'] = 60;

/*
|--------------------------------------------------------------------------
| Rate Limiting Seconds
|--------------------------------------------------------------------------
| This setting defines the time window in seconds for rate limiting.
|
*/
$config['rate_limit_seconds'] = 60;