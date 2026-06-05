# 0005 - Guest auth-code flow + voter-based authorization

- **Status:** Accepted
- **Date:** pre-upgrade

## Context

Returns can be started by guests (no shop account) as well as by signed-in customers. Guests
must be authenticated against a specific order without a password, and access to a return must
be controlled consistently across both flows.

## Decision

- **Guest flow** is gated by a one-time **`AuthCode`** emailed to the customer: start at
  `/rma-start`, receive a code (`AuthCode` entity), verify at `/rma-start/{code}`, then fill
  the form. The code lifecycle is handled by `Services/AuthCode/*`
  (factory, hash generator, secret generator, expiry calculator).
- **Signed-in customers** go through the shop account (`/account/return-history`).
- **Authorization** for accessing a return is centralised in
  `Security/OrderReturnAuthorizer` (+ `OrderReturnAuthorizerStorage`) and
  `Security/Voter/OrderReturnVoter` (modern `supports()` / `voteOnAttribute()`).

## Consequences

- Authorization decisions live in the voter/authorizer, not in scattered controller checks;
  new access rules go there.
- Guest authentication is code-based and time-limited; do not add password or session
  assumptions to the guest flow.
- Treat `AuthCode` secrets/hashes as sensitive - keep generation and expiry inside the
  `Services/AuthCode/*` services.
