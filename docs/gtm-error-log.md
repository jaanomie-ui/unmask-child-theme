# GTM Error Log — UNMASK

**Container ID:** GTM-5NJKFDSX
**Started:** 2026-01-13

---

## Log Format

```
[TIMESTAMP] [SEVERITY] [COMPONENT] Description
- Context: additional details
- Resolution: how it was fixed (if applicable)
```

Severity Levels: `INFO` | `WARN` | `ERROR` | `CRITICAL`

---

## Error Log

### 2026-01-13

```
[2026-01-13 10:00] [INFO] [SETUP] GTM implementation started
- Container ID: GTM-5NJKFDSX
- GA4 ID: G-27MFSBL8G6
- Target: staging4.houseofanomie.com
```

```
[2026-01-13 10:00] [INFO] [DISCOVERY] header.php not found in child theme
- Context: Child theme inherits BuddyBoss parent header
- Resolution: Using wp_head and wp_body_open hooks instead of direct file edit
```

```
[2026-01-13 10:05] [INFO] [DEPLOY] SSH agent socket not found
- Context: /tmp/ssh-agent-unmask.sock did not exist
- Resolution: Started new ssh-agent and added ~/.ssh/id_ed25519 key
```

```
[2026-01-13 10:06] [INFO] [DEPLOY] functions.php deployed to staging
- File size: 54811 bytes
- Permissions set to 644
```

```
[2026-01-13 10:07] [INFO] [VERIFY] GTM code confirmed in page source
- Both instances (head script + noscript) present
- Container ID: GTM-5NJKFDSX
```

---

## Pending Verification

After deployment, check for:
- [ ] Console errors related to GTM
- [ ] Network failures loading gtm.js
- [ ] DataLayer initialization errors
- [ ] GA4 connection issues

---

## Notes

- Update this log as issues arise during implementation
- Include full error messages when possible
- Document all resolutions for future reference
