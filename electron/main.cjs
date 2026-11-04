const { app, BrowserWindow, shell } = require('electron');
const path = require('path');
const fs = require('fs');
const http = require('http');
const { spawn, execSync } = require('child_process');

function getProjectRoot() {
    if (app.isPackaged) {
        return path.join(process.resourcesPath, 'app');
    }
    return path.join(__dirname, '..');
}

const PORT = process.env.LARAVEL_PORT || 8000;
const SERVER_URL = `http://127.0.0.1:${PORT}`;

let mainWindow = null;
let phpProcess = null;
let isQuitting = false;

// Ensure single instance lock
const gotTheLock = app.requestSingleInstanceLock();
if (!gotTheLock) {
    app.quit();
} else {
    app.on('second-instance', () => {
        if (mainWindow) {
            if (mainWindow.isMinimized()) mainWindow.restore();
            mainWindow.focus();
        }
    });
}

/**
 * Checks if the local Laravel HTTP server is responding.
 */
function checkServer(url) {
    return new Promise((resolve) => {
        const req = http.get(url, (res) => {
            // Any response code means the server is alive
            resolve(true);
        });
        req.on('error', () => {
            resolve(false);
        });
        req.setTimeout(1000, () => {
            req.destroy();
            resolve(false);
        });
    });
}

/**
 * Waits for the Laravel server to become ready by polling.
 */
async function waitForServer(url, maxRetries = 40, delayMs = 300) {
    for (let i = 0; i < maxRetries; i++) {
        const isUp = await checkServer(url);
        if (isUp) {
            return true;
        }
        await new Promise((resolve) => setTimeout(resolve, delayMs));
    }
    return false;
}

/**
 * Resolves the PHP executable path (embedded portable PHP vs system PHP).
 */
function getPhpBinary() {
    if (app.isPackaged) {
        const bundledPhpResource = path.join(process.resourcesPath, 'php', 'php.exe');
        if (fs.existsSync(bundledPhpResource)) {
            return bundledPhpResource;
        }
        const bundledPhpInApp = path.join(process.resourcesPath, 'app', 'electron', 'bin', 'php', 'php.exe');
        if (fs.existsSync(bundledPhpInApp)) {
            return bundledPhpInApp;
        }
    }
    const localBundledPhp = path.join(__dirname, 'bin', 'php', 'php.exe');
    if (fs.existsSync(localBundledPhp)) {
        return localBundledPhp;
    }
    return 'php';
}

/**
 * Spawns the local PHP artisan server if not already running.
 */
async function ensureServerRunning() {
    const isAlreadyRunning = await checkServer(SERVER_URL);
    if (isAlreadyRunning) {
        console.log(`[Electron] Laravel server already running at ${SERVER_URL}`);
        return;
    }

    const projectRoot = getProjectRoot();

    // Ensure .env exists in packaged app
    const envFile = path.join(projectRoot, '.env');
    const envExample = path.join(projectRoot, '.env.example');
    if (!fs.existsSync(envFile) && fs.existsSync(envExample)) {
        try {
            fs.copyFileSync(envExample, envFile);
        } catch (e) {
            console.error('[Electron] Failed to copy .env.example:', e);
        }
    }

    // Ensure sqlite database exists
    const dbDir = path.join(projectRoot, 'database');
    const dbFile = path.join(dbDir, 'database.sqlite');
    if (!fs.existsSync(dbDir)) {
        fs.mkdirSync(dbDir, { recursive: true });
    }
    if (!fs.existsSync(dbFile)) {
        fs.writeFileSync(dbFile, '');
    }

    const phpBinary = getPhpBinary();
    console.log(`[Electron] Starting Laravel server using "${phpBinary}" on port ${PORT}...`);

    phpProcess = spawn(phpBinary, ['artisan', 'serve', '--host=127.0.0.1', `--port=${PORT}`], {
        cwd: projectRoot,
        shell: true,
        stdio: 'pipe',
        windowsHide: true,
    });

    if (phpProcess.stdout) {
        phpProcess.stdout.on('data', (data) => {
            console.log(`[Laravel] ${data.toString().trim()}`);
        });
    }

    if (phpProcess.stderr) {
        phpProcess.stderr.on('data', (data) => {
            console.error(`[Laravel Error] ${data.toString().trim()}`);
        });
    }

    phpProcess.on('exit', (code, signal) => {
        if (!isQuitting) {
            console.log(`[Electron] PHP process exited with code ${code} signal ${signal}`);
        }
    });
}

/**
 * Gracefully terminates the background PHP server process.
 */
function cleanupPhpProcess() {
    isQuitting = true;
    if (phpProcess && phpProcess.pid) {
        console.log('[Electron] Terminating background PHP process...');
        try {
            if (process.platform === 'win32') {
                execSync(`taskkill /pid ${phpProcess.pid} /T /F`, { stdio: 'ignore' });
            } else {
                phpProcess.kill('SIGTERM');
            }
        } catch (err) {
            // Process may already be terminated
        }
        phpProcess = null;
    }
}

/**
 * Creates the main native application window.
 */
async function createWindow() {
    mainWindow = new BrowserWindow({
        width: 1366,
        height: 820,
        minWidth: 1024,
        minHeight: 640,
        title: 'Operational Manpower Roster',
        autoHideMenuBar: true,
        show: false,
        backgroundColor: '#f8fafc',
        webPreferences: {
            preload: path.join(__dirname, 'preload.cjs'),
            contextIsolation: true,
            nodeIntegration: false,
        },
    });

    // Handle external links safely via default browser
    mainWindow.webContents.setWindowOpenHandler(({ url }) => {
        if (url.startsWith('http://') || url.startsWith('https://')) {
            shell.openExternal(url);
            return { action: 'deny' };
        }
        return { action: 'allow' };
    });

    // Show window smoothly when content is ready
    mainWindow.once('ready-to-show', () => {
        mainWindow.show();
    });

    mainWindow.on('closed', () => {
        mainWindow = null;
    });

    console.log('[Electron] Waiting for Laravel server response...');
    const ready = await waitForServer(SERVER_URL);

    if (ready) {
        console.log(`[Electron] Loading ${SERVER_URL}...`);
        await mainWindow.loadURL(SERVER_URL);
    } else {
        console.error('[Electron] Failed to connect to Laravel server.');
        mainWindow.loadURL(`data:text/html;charset=utf-8,${encodeURIComponent(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Server Error</title>
                <style>
                    body { font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #f8fafc; color: #1e293b; }
                    .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); max-width: 500px; text-align: center; }
                    h2 { margin-top: 0; color: #dc2626; }
                    p { line-height: 1.5; color: #64748b; font-size: 14px; }
                    button { background: #0f766e; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 500; margin-top: 1rem; }
                    button:hover { background: #115e59; }
                </style>
            </head>
            <body>
                <div class="card">
                    <h2>Gagal Menghubungkan ke Server</h2>
                    <p>Layanan lokal Laravel belum siap merespons di port ${PORT}. Pastikan PHP CLI terpasang dan dapat dijalankan.</p>
                    <button onclick="window.location.href='${SERVER_URL}'">Coba Lagi</button>
                </div>
            </body>
            </html>
        `)}`);
        mainWindow.show();
    }
}

// Application Lifecycle
app.whenReady().then(async () => {
    await ensureServerRunning();
    await createWindow();

    app.on('activate', () => {
        if (BrowserWindow.getAllWindows().length === 0) {
            createWindow();
        }
    });
});

app.on('before-quit', () => {
    cleanupPhpProcess();
});

app.on('window-all-closed', () => {
    cleanupPhpProcess();
    if (process.platform !== 'darwin') {
        app.quit();
    }
});
