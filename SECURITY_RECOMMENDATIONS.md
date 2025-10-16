# Security review recommendations

## External observation
- Direct HTTPS requests to `https://k.irgeo.org/` currently answer with HTTP 403 (Envoy). Confirm that this is expected (for example, protected by a WAF or IP allowlist) and that Android/iOS API clients are still able to authenticate successfully. 【dea390†L1-L8】

## Configuration hardening
- Populate the `INTERNAL_ACCESS_TOKEN`, `MAINTENANCE_ACCESS_TOKEN`, and `CRON_ACCESS_TOKEN` environment variables with long, unique secrets so the new internal-token middleware can authenticate app traffic without falling back to shared defaults. Rotate these secrets periodically and distribute them to the mobile clients through a secure channel. 【F:core/config/security.php†L4-L18】
- Keep `CURL_VERIFY_SSL=true` in production so that outbound requests keep validating TLS certificates; only flip it off for troubleshooting, and even then prefer shipping the correct CA bundle instead. 【F:core/config/security.php†L12-L18】【F:core/app/Lib/CurlRequest.php†L24-L74】

## Public endpoint protections
- Add request throttling (for example, `throttle:10,1`) to the `/contact` and `/subscribe` routes so automated bots cannot flood ticket creation or subscription attempts. These POST routes currently lack any rate limit middleware. 【F:core/routes/web.php†L26-L36】
- Tighten `contactSubmit` validation by requiring a valid email format and trimming overly long payloads (message/subject length limits) so malformed data and resource-exhaustion attempts are rejected early. 【F:core/app/Http/Controllers/SiteController.php†L41-L86】
- Consider logging the source IP and User-Agent for contact submissions to aid abuse monitoring and blocking while keeping captcha challenges in place. 【F:core/app/Http/Controllers/SiteController.php†L41-L86】

## Scheduled job safety
- Review every database-configurable cron action: attackers who gain write access to the `cron_jobs` table could point `action` or `url` fields at malicious code. Restrict allowable controller classes/methods to a vetted list and validate URLs before dispatching them. 【F:core/app/Http/Controllers/CronController.php†L33-L54】
- Prefer running cron tasks through Laravel's scheduler (`php artisan schedule:run`) instead of the public `/cron` endpoint in production, keeping the HTTP entry point only as a last-resort fallback behind the internal token and network ACLs. 【F:core/routes/web.php†L6-L14】【F:core/app/Http/Controllers/CronController.php†L17-L74】

## Mobile client coordination
- Update the Android/iOS apps to send the agreed internal token (header or bearer) when calling `/clear` and `/cron`. Without that change, production requests will start receiving 403 responses from the middleware. 【F:core/routes/web.php†L6-L14】【F:core/app/Http/Middleware/VerifyInternalToken.php†L14-L43】

