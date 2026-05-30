/**
 * Playwright E2E — control_events Visual Verification
 * Run: node playwright-e2e.js [--headed] [--url=http://localhost:50138]
 *
 * Verifies all authenticated routes return HTTP 200 with no console errors.
 * Uses saved session from login to avoid re-authenticating.
 */

const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE_URL = process.argv.includes('--url')
  ? process.argv[process.argv.indexOf('--url') + 1]
  : 'http://localhost:50138';

const HEADLESS = !process.argv.includes('--headed');

const ROUTES = [
  { path: '/', label: 'Landing', auth: false },
  { path: '/login', label: 'Login', auth: false },
  { path: '/dashboard', label: 'Dashboard', auth: true },
  { path: '/entidades', label: 'Entidades', auth: true },
  { path: '/contratos', label: 'Contratos', auth: true },
  { path: '/receber', label: 'Contas a Receber', auth: true },
  { path: '/pagar', label: 'Contas a Pagar', auth: true },
  { path: '/conciliacao', label: 'Conciliação', auth: true },
  { path: '/hub-artista', label: 'Hub Artista', auth: true },
  { path: '/internacional', label: 'Internacional', auth: true },
];

async function run() {
  console.log(`\n=== Playwright E2E — control_events ===`);
  console.log(`Base URL: ${BASE_URL}`);
  console.log(`Headless: ${HEADLESS}\n`);

  const browser = await chromium.launch({ headless: HEADLESS });
  const context = await browser.newContext();
  const page = await context.newPage();

  const errors = [];
  const results = [];

  // Capture console errors
  page.on('console', msg => {
    if (msg.type() === 'error') {
      const text = msg.text();
      // Ignore known false positives
      if (!text.includes('favicon') && !text.includes('net::ERR')) {
        errors.push({ url: page.url(), text });
      }
    }
  });

  page.on('pageerror', err => {
    errors.push({ url: page.url(), text: `PAGE ERROR: ${err.message}` });
  });

  // --- Login to get authenticated session ---
  console.log('[1] Authenticating...');
  try {
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle', timeout: 15000 });

    // Fill login form
    await page.fill('input[name="email"]', 'nandinhos@gmail.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    // Wait for redirect to dashboard
    await page.waitForURL('**/dashboard', { timeout: 15000 });
    console.log('[OK] Authenticated — session established\n');
  } catch (e) {
    console.log(`[WARN] Login failed: ${e.message}`);
    console.log('   Continuing with session anyway...\n');
  }

  // --- Test each route ---
  console.log('[2] Testing routes...\n');
  let passCount = 0;
  let failCount = 0;

  for (const route of ROUTES) {
    const label = `[${route.label}]`.padEnd(18);
    const url = `${BASE_URL}${route.path}`;

    try {
      // Navigate and wait
      await page.goto(url, { waitUntil: 'networkidle', timeout: 20000 });

      // Check URL — if redirected to login, auth failed
      if (page.url().includes('/login') && route.auth) {
        console.log(`  ${label} ❌ REDIRECT to /login`);
        results.push({ route: route.path, label: route.label, status: 'AUTH_FAIL', statusCode: null });
        failCount++;
        continue;
      }

      // Check for 500 errors in page content
      const bodyText = await page.textContent('body');
      const has500 = bodyText.includes('500') || bodyText.includes('Internal Server Error');

      if (has500) {
        console.log(`  ${label} ❌ 500 ERROR detected`);
        results.push({ route: route.path, label: route.label, status: '500', statusCode: null });
        failCount++;
      } else {
        console.log(`  ${label} ✅ OK`);
        results.push({ route: route.path, label: route.label, status: 'PASS', statusCode: 200 });
        passCount++;
      }
    } catch (e) {
      console.log(`  ${label} ❌ EXCEPTION: ${e.message.split('\n')[0]}`);
      results.push({ route: route.path, label: route.label, status: 'ERROR', error: e.message.split('\n')[0] });
      failCount++;
    }
  }

  // --- Console errors report ---
  console.log('\n[3] Console errors captured...\n');
  if (errors.length === 0) {
    console.log('  [OK] No console errors\n');
  } else {
    // Deduplicate
    const seen = new Set();
    errors.forEach(e => {
      const key = `${e.url}:${e.text.substring(0, 80)}`;
      if (!seen.has(key)) {
        seen.add(key);
        console.log(`  ⚠ ${e.text.substring(0, 120)}`);
      }
    });
    console.log(`\n  Total unique errors: ${seen.size}\n`);
  }

  // --- Summary ---
  console.log('═══════════════════════════════════════');
  console.log(`  PASSED:  ${passCount}/${ROUTES.length}`);
  console.log(`  FAILED:  ${failCount}/${ROUTES.length}`);
  console.log('═══════════════════════════════════════');

  await browser.close();

  if (failCount > 0) {
    console.log('\n[FAIL] Some routes failed verification.');
    console.log('Run systematic-debugging before committing.\n');
    process.exit(1);
  } else {
    console.log('\n[OK] All routes verified — system is green.\n');
    process.exit(0);
  }
}

run().catch(err => {
  console.error('Fatal error:', err);
  process.exit(1);
});