import { chromium } from 'playwright';
import { mkdirSync } from 'fs';

const BASE = 'http://declass.test';
const OUT = 'public/img/mockups';
mkdirSync(OUT, { recursive: true });

const login = async (page, email) => {
    await page.goto(BASE + '/login');
    await page.fill('input[name="slug"]', 'escola-demo');
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"], button:has-text("Entrar"), input[type="submit"]');
    await page.waitForLoadState('networkidle');
};

const shot = async (page, url, name, wait = 900) => {
    await page.goto(BASE + url, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await page.waitForTimeout(wait);
    await page.screenshot({ path: `${OUT}/${name}.png`, fullPage: false });
    console.log('OK', name);
};

const browser = await chromium.launch();

// ---- SECRETARIA (desktop) ----
const desk = await browser.newContext({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 });
let page = await desk.newPage();
await login(page, 'secretaria@escolademo.test');
await shot(page, '/admin/home', 'desktop-secretaria-home', 1400);
await shot(page, '/admin/turma', 'desktop-secretaria-turmas');
await shot(page, '/admin/aluno', 'desktop-secretaria-alunos');
await desk.close();

// ---- SECRETARIA (mobile) ----
const mob = await browser.newContext({ viewport: { width: 390, height: 844 }, isMobile: true, deviceScaleFactor: 2 });
page = await mob.newPage();
await login(page, 'secretaria@escolademo.test');
await shot(page, '/admin/home', 'mobile-secretaria-home', 1400);
await shot(page, '/admin/turma', 'mobile-secretaria-turmas');
await mob.close();

// ---- FAMILIA/ALUNO (desktop + mobile) ----
const desk2 = await browser.newContext({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 });
page = await desk2.newPage();
await login(page, 'responsavel.demo@escolademo.test');
await shot(page, '/portal', 'desktop-portal-home', 1400);
await shot(page, '/portal/notas', 'desktop-portal-notas', 1200);
await desk2.close();

const mob2 = await browser.newContext({ viewport: { width: 390, height: 844 }, isMobile: true, deviceScaleFactor: 2 });
page = await mob2.newPage();
await login(page, 'responsavel.demo@escolademo.test');
await shot(page, '/portal', 'mobile-portal-home', 1400);
await shot(page, '/portal/frequencia', 'mobile-portal-frequencia', 1200);
await mob2.close();

await browser.close();
console.log('DONE');
