import { expect, test } from '@playwright/test';
import { hasCarouselLabel, tagCarouselScopes } from '../support/utils/carousel';

test('recognizes a named wrapper between the section and Swiper track', async ({ page }) => {
  await page.setContent(
    '<section data-module="cards"><div data-component="carousel" role="region" aria-label="Resources"><div class="swiper">Slides</div></div><div data-component="carousel" role="region" aria-label="News"><div class="swiper">Slides</div></div></section>',
  );
  expect(await page.evaluate(tagCarouselScopes, '.swiper')).toBe(2);
  for (const scope of await page.locator('[data-carousel-scope]').all()) {
    expect(await scope.evaluate(hasCarouselLabel, '.swiper')).toBe(true);
  }
});

test('rejects an unnamed carousel even when it has a role description', async ({ page }) => {
  await page.setContent(
    '<section data-module="cards" aria-roledescription="carousel"><div class="swiper">Slides</div></section>',
  );
  expect(await page.locator('section').evaluate(hasCarouselLabel, '.swiper')).toBe(false);
});

test('resolves heading references and rejects missing references', async ({ page }) => {
  await page.setContent(
    '<h2 id="title">Featured resources</h2><section aria-labelledby="title"><div class="swiper">Slides</div></section>',
  );
  expect(await page.locator('section').evaluate(hasCarouselLabel, '.swiper')).toBe(true);
  await page.locator('section').evaluate((el) => el.setAttribute('aria-labelledby', 'missing'));
  expect(await page.locator('section').evaluate(hasCarouselLabel, '.swiper')).toBe(false);
});

test('a decorative synchronized track does not hide an unnamed exposed track', async ({ page }) => {
  await page.setContent(
    '<section><div class="swiper" aria-hidden="true">Media</div><div class="swiper">Text</div></section>',
  );
  expect(await page.locator('section').evaluate(hasCarouselLabel, '.swiper')).toBe(false);
});

test('keeps synchronized media and text in one named carousel scope', async ({ page }) => {
  await page.setContent(`
    <section data-module="messages" role="group" aria-label="Key messages">
      <div class="swiper">Media</div>
      <div class="swiper">Text</div>
    </section>
  `);
  expect(await page.evaluate(tagCarouselScopes, '.swiper')).toBe(1);
  expect(await page.locator('section').evaluate(hasCarouselLabel, '.swiper')).toBe(true);
});
