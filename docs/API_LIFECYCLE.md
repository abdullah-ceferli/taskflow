# API lifecycle and compatibility policy

## Version contract

- Stable endpoints live below `/api/v1`; every response includes `X-API-Version: v1`.
- Additive fields and endpoints may be released inside v1. Clients must ignore unknown response fields.
- Removing or renaming fields, changing status codes, tightening valid enum values, or changing authorization semantics requires a new major API version.
- OpenAPI is the public contract and contract tests protect representative envelopes and lifecycle headers.

## Deprecation

- A deprecated endpoint returns `Deprecation: true`, an RFC 8594 `Sunset` header, and a `Link` header to its successor documentation.
- Breaking endpoints receive at least 90 days' notice before sunset unless an active security issue requires faster removal.
- v1 currently returns `Deprecation: false`; no v1 endpoint has a scheduled sunset.

## Integration safety

- Sanctum abilities are selected per token and never replace policies or workspace scoping.
- Automated task creation supports `Idempotency-Key`; reuse with a changed payload returns HTTP 409.
- Webhooks are signed with HMAC-SHA256 over `{timestamp}.{raw-body}` and expose delivery/replay state.
- Report downloads are private, actor/workspace authorized, signed, and expire.
