# TaskFlow incident response

1. Acknowledge the alert, assign incident commander and technical owner, open a timestamped incident record, and identify the affected release/workspace without copying secrets.
2. Contain impact: disable the relevant feature flag, revoke suspicious tokens, pause a compromised integration, or roll back the application artifact. Preserve audit and structured logs.
3. Diagnose with correlation IDs, aggregate operations metrics, failed-job data, activity audit export, and the versioned release diff. Treat login/token spikes and authorization denials as security signals.
4. Recover using a forward fix or application rollback. Use database restore only through the approved backup procedure and verify authorization boundaries after recovery.
5. Confirm liveness/readiness, core Web/API smoke tests, queue age, notifications, webhooks, and error rate before closing mitigation.
6. Complete a blameless review with impact, timeline, root cause, detection gap, corrective owner and due date. Rotate exposed secrets and retain evidence according to policy.

Authorization regressions are release blockers. Re-run the role/ability/policy matrix and workspace-isolation suites after any auth, policy, middleware, repository scope, token, invitation, export, attachment, or webhook change.
