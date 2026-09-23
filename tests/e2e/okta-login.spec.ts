import { expect, test } from '@playwright/test';

// The full SSO round-trip needs a real Okta org, so these live scenarios cover
// the login screen and the start of the OIDC redirect (which is all the package
// controls before handing off to Okta).

test.describe('Okta login on Nova', () => {
    test('the Nova login screen shows the Okta button linking to okta/login', async ({ page }) => {
        await page.goto('/nova/login');

        const button = page.locator('#okta-signin-submit');

        await expect(button).toBeVisible();
        await expect(button).toContainText('Log In with Okta');
        await expect(button).toHaveAttribute('href', /\/nova\/okta\/login$/);
    });

    test('okta/login starts the Okta OIDC authorization redirect', async ({ request }) => {
        const response = await request.get('/nova/okta/login', { maxRedirects: 0 });

        expect(response.status()).toBe(302);
        expect(response.headers()['location']).toContain('example.okta.com');
        expect(response.headers()['location']).toContain('/oauth2/v1/authorize');
        expect(response.headers()['location']).toContain('client_id=');
    });
});
