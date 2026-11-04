const { spawn } = require('child_process');
const path = require('path');

const PROJECT_ROOT = path.join(__dirname, '..');

console.log('[DevRunner] Building latest frontend assets (Vite)...');
const build = spawn('npm', ['run', 'build'], {
    cwd: PROJECT_ROOT,
    shell: true,
    stdio: 'inherit',
});

build.on('close', (buildCode) => {
    if (buildCode !== 0) {
        console.error(`[DevRunner] Vite build failed with exit code ${buildCode}`);
        process.exit(buildCode);
    }

    console.log('[DevRunner] Frontend build complete. Launching Electron desktop window...');
    const electron = spawn('npx', ['electron', '.'], {
        cwd: PROJECT_ROOT,
        shell: true,
        stdio: 'inherit',
    });

    electron.on('close', (electronCode) => {
        console.log(`[DevRunner] Electron closed with exit code ${electronCode}`);
        process.exit(electronCode);
    });
});
