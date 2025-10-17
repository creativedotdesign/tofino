import { test, expect } from '@playwright/test';
import dotenv from 'dotenv';

// Load environment variables from .env file
dotenv.config();

test.describe('Tofino tests', () => {
  const baseUrl = process.env.VITE_ASSET_URL;

  test.beforeEach(async ({ page }) => {
    await page.goto(baseUrl);
  });

  test('Check alert', async ({ page }) => {
    const alert = await page.locator('[data-alert-id]');

    // Check for alert to be shown
    await expect(alert).toBeVisible();
  });

  test('Mobile navigation tests', async ({ page }) => {
    const pageBody = await page.locator('body');
    const menu = await page.locator('#main-menu');
    const openBtn = await page.locator('[data-playwright="open-mobile-menu"]');

    // Set mobile viewport
    await page.setViewportSize({ width: 500, height: 900 });

    // Make sure no scroll lock class on body
    await expect(pageBody).not.toHaveClass(/menu-open/);

    // Open Menu
    await openBtn.click();

    // Make sure scroll lock class on body
    await expect(pageBody).toHaveClass(/menu-open/);

    // Check if open menu is viewport height
    await expect(menu).toBeInViewport({ ratio: 1 });
  });

  test('Tablet navigation tests', async ({ page }) => {
    const pageBody = await page.locator('body');
    const menu = await page.locator('#main-menu');
    const openBtn = await page.locator('[data-playwright="open-mobile-menu"]');
    const closeBtn = await page.locator('[data-playwright="close-mobile-menu"]');

    // Set tablet viewport
    await page.setViewportSize({ width: 834, height: 1112 });

    // Open Menu
    await openBtn.click();

    // Check if open menu is viewport height
    await expect(menu).toBeInViewport({ ratio: 1 });

    // Close Menu
    await closeBtn.click();

    // Menu should not be visible
    await expect(menu).not.toBeVisible();

    // Make sure no scroll lock class on body
    await expect(pageBody).not.toHaveClass(/menu-open/);

    // Check escape closes menu
    await openBtn.click();
    await page.keyboard.press('Escape');

    // Menu should not be visible
    await expect(menu).not.toBeVisible();

    // Make sure no scroll lock class on body
    await expect(pageBody).not.toHaveClass(/menu-open/);
  });

  // Desktop Tests
  test('Desktop sticky menu', async ({ page }) => {
    const pageBodyFixed = await page.locator('body.menu-fixed');
    const header = await page.locator('header');

    if ((await pageBodyFixed.count()) > 0) {
      // Check header for sticky top class
      await expect(header).toHaveClass(/sticky-top/);

      // Scroll down 200px, check if visible
      await page.evaluate(() => window.scrollBy(0, 200));
      await expect(header).toBeVisible();

      // Scroll to bottom, check if visible
      page.evaluate(() => window.scrollTo(0, document.documentElement.scrollHeight));
      await expect(header).toBeVisible();

      // Scroll back to top, check if visible
      page.evaluate(() => window.scrollTo(0, -document.documentElement.scrollHeight));
      await expect(header).toBeVisible();
    }
  });
});
