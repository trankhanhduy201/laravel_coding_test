# Coding Test Answers

## 1) Performance: `GET /api/tasks` for 1,000,000+ rows

- Use **cursor-based pagination** instead of offset pagination for large datasets (`WHERE id < :cursor ORDER BY id DESC LIMIT :limit`) to avoid scanning skipped rows.
- Add **proper DB indexes** for common filters/sorts: `status`, `assigned_to`, `user_id`, `created_at`, and composite indexes based on real query patterns (e.g. `(status, user_id)`).
- Keep responses lean with **field selection** (only return columns needed by list view), and avoid large text fields unless explicitly requested.
- Using **optimized joins** to avoid send too much query to DB created by **eager loading/lazy loading** to get associated data
- Add **query constraints and sane limits** (max page size) to prevent expensive requests and abusive scans.
- Run **background jobs** to analyze and tokenize data using the **N-gram algorithm**, build search indexes for **fuzzy matching**, and cache the indexed data in **Redis** to enable average **O(1)** key lookups and low-latency search responses.
- For advanced search at scale, move full-text/fuzzy search to **Elasticsearch/OpenSearch** (or DB full-text indexes where suitable).
- Use **read replicas** and connection pooling to split read-heavy task-list traffic from writes.

## 2) Security: Laravel API + Vue SPA

- Enforce **HTTPS everywhere** (HSTS, secure cookies, TLS-only endpoints) to protect credentials/tokens in transit.
- Prevent SQL injection by using **Eloquent/query builder parameter binding** only (never raw user input in SQL).
- Protect against XSS by relying on Vue’s auto-escaping, avoiding unsafe HTML rendering (`v-html`), and applying a **CSP**.
- Use **strong auth/token hygiene**: short token TTL, rotation/revocation, secure storage strategy, logout invalidation, and least-privilege scopes.
- Mitigate CSRF for cookie-based auth using Laravel CSRF protections + `SameSite` cookies; for token-based auth, still validate origin and CORS strictly.
- Lock down **CORS** to trusted origins, methods, and headers; avoid wildcard origins in production.
- Add **rate limiting / throttling** (login + API routes) and temporary lockouts to reduce brute force and abuse.
- Validate and sanitize all input via Form Requests; enforce strict rules for pagination/filter params and deny unexpected payloads.
- Implement robust authorization policies (already using policies) and audit sensitive actions (create/update/delete).
- Keep dependencies updated, disable debug in production, and manage secrets via environment/secret manager.

## 4.2) TypeScript / Vue

### 1. Main benefits of TypeScript in a Vue 3 SPA

- **Type safety** catches errors at build time (wrong prop types, API payload mismatches, nullable mistakes).
- Better **IDE support** (autocomplete, refactor safety, go-to-definition, symbol rename).
- Clearer contracts for components/composables/services, improving team maintainability.
- Safer API integration through typed request/response models.
- Easier scaling of codebase with fewer runtime surprises.

### 2. Vue 3 sample with `<script setup lang="ts">` and strongly typed props

```vue
<script setup lang="ts">
import { computed } from 'vue'

type UserRole = 'admin' | 'member'

interface UserCardProps {
  id: number
  name: string
  role?: UserRole
  active?: boolean
}

const props = withDefaults(defineProps<UserCardProps>(), {
  role: 'member',
  active: true,
})

const badgeColor = computed(() => {
  if (!props.active) return 'grey'
  return props.role === 'admin' ? 'red' : 'blue'
})
</script>

<template>
  <div class="user-card">
    <strong>#{{ props.id }} - {{ props.name }}</strong>
    <span :class="`badge badge-${badgeColor}`">{{ props.role }}</span>
  </div>
</template>
```
