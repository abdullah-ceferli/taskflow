# Integration security

- Personal tokens default to `projects:read` and `tasks:read`, accept only enumerated abilities, expire by default after 30 days, and can be rotated or revoked in the UI.
- Plaintext token and webhook secrets are shown once. Sanctum hashes tokens; webhook signing secrets use Laravel's encrypted cast.
- Webhook endpoints must use HTTPS. Deliveries contain an event id, timestamp, actor, subject, and sanitized domain data.
- Consumers verify `X-TaskFlow-Timestamp` and `X-TaskFlow-Signature`, reject stale timestamps, and keep received event ids for replay protection.
- Delivery jobs use bounded retries and exponential backoff; manual replay creates a new delivery log.
- Idempotency records are scoped by workspace and actor, fingerprint the request, and expire after 24 hours.
- CSV jobs query only records visible to the requesting actor in the selected workspace. Files use private storage and expire after one hour.
