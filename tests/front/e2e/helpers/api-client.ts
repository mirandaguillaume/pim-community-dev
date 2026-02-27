import {APIRequestContext} from '@playwright/test';

/**
 * REST API client for test data seeding — replaces FixturesContext.php.
 *
 * Uses the PIM REST API v1 with OAuth2 password grant.
 *
 * Token endpoint sourced from:
 *   - config/packages/security.yml (pattern: ^/api/oauth/v1/token)
 *   - tests/legacy/features/Context/FixturesContext.php (OAuth2 setup)
 *   - src/.../Connection/.../EndToEnd/ (token request examples)
 *
 * Default client_id / client_secret must match your .env or test fixtures.
 * Override via environment variables PIM_API_CLIENT_ID / PIM_API_CLIENT_SECRET.
 */

let tokenCache: {token: string; expiresAt: number} | null = null;

/**
 * Obtain an OAuth2 access token for the PIM REST API.
 *
 * Caches the token until 60 seconds before expiry.
 */
export async function getApiToken(request: APIRequestContext): Promise<string> {
  if (tokenCache && Date.now() < tokenCache.expiresAt) {
    return tokenCache.token;
  }

  const clientId = process.env.PIM_API_CLIENT_ID || '1_api_client_id';
  const clientSecret = process.env.PIM_API_CLIENT_SECRET || 'api_secret';

  const response = await request.post('/api/oauth/v1/token', {
    form: {
      grant_type: 'password',
      username: 'admin',
      password: 'admin',
      client_id: clientId,
      client_secret: clientSecret,
    },
  });

  if (!response.ok()) {
    throw new Error(`Failed to get API token: ${response.status()} ${await response.text()}`);
  }

  const data = await response.json();
  tokenCache = {
    token: data.access_token,
    expiresAt: Date.now() + (data.expires_in - 60) * 1000,
  };

  return data.access_token;
}

/**
 * Perform a GET request against the PIM REST API v1.
 *
 * @param path — path after /api/rest/v1, e.g. "/products" or "/families/shoes"
 */
export async function apiGet(request: APIRequestContext, path: string) {
  const token = await getApiToken(request);
  return request.get(`/api/rest/v1${path}`, {
    headers: {Authorization: `Bearer ${token}`},
  });
}

/**
 * Perform a POST request against the PIM REST API v1.
 *
 * @param path — path after /api/rest/v1, e.g. "/products"
 * @param data — JSON-serializable body
 */
export async function apiPost(request: APIRequestContext, path: string, data: unknown) {
  const token = await getApiToken(request);
  return request.post(`/api/rest/v1${path}`, {
    data,
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  });
}

/**
 * Perform a PATCH request against the PIM REST API v1.
 *
 * @param path — path after /api/rest/v1, e.g. "/products/sku123"
 * @param data — JSON-serializable body
 */
export async function apiPatch(request: APIRequestContext, path: string, data: unknown) {
  const token = await getApiToken(request);
  return request.patch(`/api/rest/v1${path}`, {
    data,
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  });
}

/** Clear the cached token (useful in test teardown). */
export function clearTokenCache(): void {
  tokenCache = null;
}
