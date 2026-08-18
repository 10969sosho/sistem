import { expect, test, type Page } from '@playwright/test';

const user = {
  id: 1,
  name: 'Admin User',
  email: 'admin@example.com',
};

const dashboard = {
  date: '2026-08-18',
  today_tasks: { count: 2, items: [{ id: 1, title: 'Review homepage' }] },
  overdue_tasks: { count: 1, items: [{ id: 2, title: 'Fix checkout bug' }] },
  week_tasks: { count: 3, items: [] },
  active_projects: { count: 4, items: [] },
  revisi_projects: { count: 1, items: [] },
  hosting_expiring: { count: 0, items: [] },
  domain_expiring: { count: 0, items: [] },
  unpaid_invoices: { count: 2, items: [] },
};

async function mockApi(page: Page) {
  await page.route('**/api/login', async (route) => {
    await route.fulfill({
      contentType: 'application/json',
      body: JSON.stringify({ data: { token: 'test-token', user } }),
    });
  });

  await page.route('**/api/me', async (route) => {
    await route.fulfill({
      contentType: 'application/json',
      body: JSON.stringify({ data: user }),
    });
  });

  await page.route('**/api/dashboard', async (route) => {
    await route.fulfill({
      contentType: 'application/json',
      body: JSON.stringify({ data: dashboard }),
    });
  });

  await page.route('**/api/logout', async (route) => {
    await route.fulfill({
      contentType: 'application/json',
      body: JSON.stringify({ message: 'Logout berhasil.' }),
    });
  });
}

test('redirects an unauthenticated visitor to login', async ({ page }) => {
  await page.goto('/');

  await expect(page).toHaveURL(/\/login$/);
  await expect(page.getByRole('heading', { name: 'Task Manager' })).toBeVisible();
  await expect(page.getByLabel('Email')).toBeVisible();
  await expect(page.getByLabel('Password')).toBeVisible();
});

test('logs in and renders dashboard widgets', async ({ page }) => {
  await mockApi(page);
  await page.goto('/login');

  await page.getByLabel('Email').fill(user.email);
  await page.getByLabel('Password').fill('password');
  await page.getByRole('button', { name: 'Sign in' }).click();

  await expect(page).toHaveURL(/\/$/);
  await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
  await expect(page.getByText('Task Hari Ini')).toBeVisible();
  await expect(page.getByText('Fix checkout bug')).toBeVisible();
  await expect(page.getByText('Tagihan Belum Lunas')).toBeVisible();
  await expect(page.getByRole('link', { name: 'Tasks' })).toBeVisible();
});

test('logs out from the dashboard', async ({ page }) => {
  await mockApi(page);
  await page.goto('/login');
  await page.getByLabel('Email').fill(user.email);
  await page.getByLabel('Password').fill('password');
  await page.getByRole('button', { name: 'Sign in' }).click();

  await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
  await page.getByRole('button', { name: 'Logout' }).click();

  await expect(page).toHaveURL(/\/login$/);
  await expect(page.getByRole('button', { name: 'Sign in' })).toBeVisible();
});
