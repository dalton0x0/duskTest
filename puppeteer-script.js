const puppeteer = require('puppeteer');
const fs = require('fs');
require('dotenv').config();

(async () => {
    console.log('Starting puppeteer');

    const browser = await puppeteer.launch({
        headless: process.env.PUPPETEER_HEADLESS === 'true',
        args: ['--start-maximized']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });

    const cookiesFilePath = 'storage/app/cookies-ypareo-itic.json';

    if (fs.existsSync(cookiesFilePath)) {
        console.log('Loading cookies');
        const cookies = JSON.parse(fs.readFileSync(cookiesFilePath, 'utf8'));
        await page.setCookie(...cookies);
    }

    await page.setUserAgent("Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36");

    console.log('Dashboard');
    await page.goto('https://iticparis.ymag.cloud/index.php/', { waitUntil: 'networkidle2' });

    const isLoggedIn = await page.$('.user-info');
    if (isLoggedIn) {
        console.log('Already connected');
        await page.screenshot({ path: 'storage/app/screenshot-already-logged-in.png' });
        console.log('Screenshot taken');
    } else {
        console.log('Not connected ! Trying to connect');

        await page.screenshot({ path: 'storage/app/screenshot-before-login.png' });

        try {
            await page.waitForSelector('input[name="login"]', { timeout: 5000 });
        } catch (error) {
            console.error('Error username not found');
            await page.screenshot({ path: 'storage/app/screenshot-login-error.png' });
            await browser.close();
            return;
        }

        await page.type('input[name="login"]', process.env.PUPPETEER_USERNAME);
        await page.type('input[name="password"]', process.env.PUPPETEER_PASSWORD);

        await page.screenshot({ path: 'storage/app/screenshot-filled-login-form.png' });

        console.log('Connecting');
        await Promise.all([
            page.click('input[type="submit"]'),
            page.waitForNavigation({ waitUntil: 'networkidle2' })
        ]);

        console.log('Connected');
        await page.screenshot({ path: 'storage/app/screenshot-after-login.png' });

        const cookies = await page.cookies();
        fs.writeFileSync(cookiesFilePath, JSON.stringify(cookies, null, 2));

        console.log('Cookies written');
    }

    console.log('Script complete');
    await browser.close();
})();
