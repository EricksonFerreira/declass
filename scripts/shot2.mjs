import { chromium } from 'playwright';
import { mkdirSync } from 'fs';
const BASE = 'http://declass.test', OUT = 'public/img/mockups';
mkdirSync(OUT, { recursive: true });
const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
const page = await ctx.newPage();
await page.goto(BASE + '/login');
await page.fill('input[name="slug"]', 'escola-demo');
await page.fill('input[name="email"]', 'secretaria@escolademo.test');
await page.fill('input[name="password"]', 'password');
await page.click('button[type="submit"]');
await page.waitForLoadState('networkidle');
for (const [url, name] of [['/admin/solicitacao-matricula', 'desktop-secretaria-matricula'], ['/admin/mensalidade', 'desktop-secretaria-mensalidade']]) {
    await page.goto(BASE + url, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await page.waitForTimeout(1200);
    await page.screenshot({ path: `${OUT}/${name}.png` });
    console.log('OK', name);
}
await browser.close();
