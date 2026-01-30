const { app, BrowserWindow, Menu, Tray, dialog, shell } = require('electron');
const { spawn, exec } = require('child_process');
const path = require('path');
const fs = require('fs');

// Keep references to prevent garbage collection
let mainWindow = null;
let tray = null;
let phpProcess = null;
let isQuitting = false;

// Server configuration
const PHP_PORT = 8000;
let SERVER_URL = `http://127.0.0.1:${PHP_PORT}`;
let useExternalServer = false;

// Get the application root directory
function getAppRoot() {
    if (app.isPackaged) {
        return path.join(process.resourcesPath, 'app');
    }
    return __dirname;
}

// Check if PHP is available
function checkPHP() {
    return new Promise((resolve) => {
        exec('php -v', (error) => {
            resolve(!error);
        });
    });
}


// Check if server is already running
function isServerRunning(port) {
    return new Promise((resolve) => {
        const http = require('http');
        const req = http.get(`http://127.0.0.1:${port}`, (res) => {
            resolve(true);
        });
        req.on('error', () => {
            resolve(false);
        });
        req.setTimeout(2000, () => {
            req.destroy();
            resolve(false);
        });
    });
}

// Load hosting configuration
function loadHostingConfig() {
    const appRoot = getAppRoot();
    // Check various locations for the config file
    // 1. Next to the executable/script
    // 2. In resources folder (for packaged app)
    // 3. In app root

    const possiblePaths = [
        path.join(path.dirname(process.execPath), 'hosting.json'),
        path.join(process.resourcesPath, 'hosting.json'),
        path.join(appRoot, 'hosting.json'),
        path.join(__dirname, 'hosting.json')
    ];

    console.log('Searching for hosting.json in:', possiblePaths);

    for (const configPath of possiblePaths) {
        if (fs.existsSync(configPath)) {
            try {
                const config = JSON.parse(fs.readFileSync(configPath, 'utf8'));
                if (config.url) {
                    console.log('Loaded hosting config from:', configPath);
                    console.log('Config:', config);
                    return config;
                }
            } catch (err) {
                console.error('Error reading hosting.json:', err);
            }
        }
    }
    return null;
}

// Start PHP Laravel server
function startPHPServer() {
    return new Promise((resolve, reject) => {
        const appRoot = getAppRoot();
        const artisanPath = path.join(appRoot, 'artisan');

        console.log('Starting PHP server...');
        console.log('App root:', appRoot);
        console.log('Artisan path:', artisanPath);

        // Check if artisan exists
        if (!fs.existsSync(artisanPath)) {
            console.error('Artisan file not found at:', artisanPath);
            reject(new Error('Artisan file not found'));
            return;
        }

        phpProcess = spawn('php', ['artisan', 'serve', '--port=' + PHP_PORT], {
            cwd: appRoot,
            stdio: ['ignore', 'pipe', 'pipe'],
            shell: true
        });

        phpProcess.stdout.on('data', (data) => {
            console.log('PHP Server:', data.toString());
            if (data.toString().includes('Server running')) {
                resolve();
            }
        });

        phpProcess.stderr.on('data', (data) => {
            console.error('PHP Server Error:', data.toString());
        });

        phpProcess.on('error', (error) => {
            console.error('Failed to start PHP server:', error);
            reject(error);
        });

        phpProcess.on('close', (code) => {
            console.log('PHP server exited with code:', code);
            if (!isQuitting) {
                // Server crashed, show error
                dialog.showErrorBox('Server Error', 'PHP server has stopped unexpectedly.');
            }
        });

        // Give the server some time to start
        setTimeout(() => {
            resolve();
        }, 3000);
    });
}

// Stop PHP server
function stopPHPServer() {
    if (phpProcess) {
        console.log('Stopping PHP server...');

        // On Windows, we need to kill the process tree
        if (process.platform === 'win32') {
            exec(`taskkill /pid ${phpProcess.pid} /T /F`, (error) => {
                if (error) {
                    console.error('Error killing PHP process:', error);
                }
            });
        } else {
            phpProcess.kill('SIGTERM');
        }

        phpProcess = null;
    }
}

// Create main application window
function createWindow() {
    mainWindow = new BrowserWindow({
        width: 1400,
        height: 900,
        minWidth: 1024,
        minHeight: 768,
        icon: path.join(__dirname, 'public', 'icons', 'kasirku-logo.png'),
        webPreferences: {
            nodeIntegration: false,
            contextIsolation: true,
            webSecurity: true
        },
        show: false,
        backgroundColor: '#1a1a2e',
        titleBarStyle: 'default',
        autoHideMenuBar: true
    });

    // Load the application
    mainWindow.loadURL(SERVER_URL);

    // Show window when ready
    mainWindow.once('ready-to-show', () => {
        mainWindow.show();
        mainWindow.focus();
    });

    // Handle window close
    mainWindow.on('close', (event) => {
        if (!isQuitting) {
            event.preventDefault();
            mainWindow.hide();

            // Show tray notification on first minimize
            if (tray) {
                tray.displayBalloon({
                    iconType: 'info',
                    title: 'KasirKu',
                    content: 'Aplikasi berjalan di system tray'
                });
            }
        }
    });

    mainWindow.on('closed', () => {
        mainWindow = null;
    });

    // Handle external links
    mainWindow.webContents.setWindowOpenHandler(({ url }) => {
        shell.openExternal(url);
        return { action: 'deny' };
    });

    // Handle navigation errors
    mainWindow.webContents.on('did-fail-load', (event, errorCode, errorDescription) => {
        console.error('Failed to load:', errorCode, errorDescription);

        // Retry after a short delay
        setTimeout(() => {
            mainWindow.loadURL(SERVER_URL);
        }, 2000);
    });
}

// Create system tray
function createTray() {
    const iconPath = path.join(__dirname, 'public', 'icons', 'kasirku-logo.png');

    // Check if icon exists, use default if not
    let trayIcon = iconPath;
    if (!fs.existsSync(iconPath)) {
        // Create a simple icon or use null
        trayIcon = null;
    }

    if (trayIcon) {
        tray = new Tray(trayIcon);
    } else {
        // Skip tray creation if no icon
        return;
    }

    const contextMenu = Menu.buildFromTemplate([
        {
            label: 'Buka KasirKu',
            click: () => {
                if (mainWindow) {
                    mainWindow.show();
                    mainWindow.focus();
                }
            }
        },
        {
            label: 'Buka di Browser',
            click: () => {
                shell.openExternal(SERVER_URL);
            }
        },
        { type: 'separator' },
        {
            label: 'Restart Server',
            click: async () => {
                stopPHPServer();
                await startPHPServer();
                if (mainWindow) {
                    mainWindow.reload();
                }
            }
        },
        {
            label: 'Reload Page',
            click: () => {
                if (mainWindow) {
                    mainWindow.reload();
                }
            }
        },
        {
            label: 'Clear Cache & Reload',
            click: () => {
                if (mainWindow) {
                    mainWindow.webContents.session.clearCache().then(() => {
                        mainWindow.reload();
                    });
                }
            }
        },
        { type: 'separator' },
        {
            label: 'Keluar',
            click: () => {
                isQuitting = true;
                app.quit();
            }
        }
    ]);

    tray.setToolTip('KasirKu - Point of Sale');
    tray.setContextMenu(contextMenu);

    tray.on('double-click', () => {
        if (mainWindow) {
            mainWindow.show();
            mainWindow.focus();
        }
    });
}

// Application ready
app.whenReady().then(async () => {
    // Check for external hosting config
    const hostingConfig = loadHostingConfig();

    if (hostingConfig && hostingConfig.use_external_server) {
        useExternalServer = true;
        SERVER_URL = hostingConfig.url;
        console.log('Using external server at:', SERVER_URL);
    }

    // Only check PHP if NOT using external server
    if (!useExternalServer) {
        // Check PHP availability
        const hasPHP = await checkPHP();
        if (!hasPHP) {
            dialog.showErrorBox(
                'PHP Tidak Ditemukan',
                'PHP tidak terinstall atau tidak ada di PATH. Silakan install PHP terlebih dahulu.'
            );
            app.quit();
            return;
        }
    }

    // Create loading window
    const loadingWindow = new BrowserWindow({
        width: 400,
        height: 200,
        frame: false,
        transparent: true,
        alwaysOnTop: true,
        webPreferences: {
            nodeIntegration: true
        }
    });

    loadingWindow.loadURL(`data:text/html;charset=utf-8,
        <html>
        <body style="
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 20px;
            font-family: system-ui, -apple-system, sans-serif;
        ">
            <div style="text-align: center; color: white;">
                <h2 style="margin: 0 0 20px 0;">🛒 KasirKu</h2>
                <p style="margin: 0;">Memulai server...</p>
                <div style="margin-top: 20px;">
                    <div style="
                        width: 40px;
                        height: 40px;
                        border: 4px solid rgba(255,255,255,0.3);
                        border-top-color: white;
                        border-radius: 50%;
                        animation: spin 1s linear infinite;
                        margin: 0 auto;
                    "></div>
                </div>
            </div>
        </body>
        <style>
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
        </style>
        </html>
    `);

    try {
        if (useExternalServer) {
            // Just wait a bit to ensure UI looks good then open
            await new Promise(resolve => setTimeout(resolve, 1500));
        } else {
            // Check if server is already running
            const serverRunning = await isServerRunning(PHP_PORT);

            if (serverRunning) {
                console.log('Server already running on port', PHP_PORT);
            } else {
                // Start PHP server
                await startPHPServer();
            }
        }

        // Close loading window
        loadingWindow.close();

        // Create main window
        createWindow();

        // Create system tray
        createTray();

    } catch (error) {
        loadingWindow.close();
        dialog.showErrorBox('Error', 'Gagal memulai server: ' + error.message);
        app.quit();
    }
});

// Handle all windows closed
app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') {
        // Don't quit, keep running in tray
    }
});

// Handle activate (macOS)
app.on('activate', () => {
    if (mainWindow === null) {
        createWindow();
    } else {
        mainWindow.show();
    }
});

// Handle before quit
app.on('before-quit', () => {
    isQuitting = true;
    if (!useExternalServer) {
        stopPHPServer();
    }
});

// Handle quit
app.on('quit', () => {
    if (!useExternalServer) {
        stopPHPServer();
    }
});

// Handle uncaught exceptions
process.on('uncaughtException', (error) => {
    console.error('Uncaught exception:', error);
    dialog.showErrorBox('Error', 'Terjadi kesalahan: ' + error.message);
});
