import { app, BrowserWindow } from 'electron';
import { exec } from 'child_process';

function startXampp() {
    exec('C:\\xampp\\xampp_start.exe', (error) => {
        if (error) {
            console.log('XAMPP start error:', error.message);
        } else {
            console.log('XAMPP started successfully');
        }
    });
}

function createWindow() {
    const win = new BrowserWindow({
        width: 1400,
        height: 900,
        minWidth: 1000,
        minHeight: 700,
        title: 'Club Management System',
        webPreferences: {
            nodeIntegration: false,
            contextIsolation: true
        }
    });

    win.loadURL('http://localhost/club/public/index.html');
}

app.whenReady().then(() => {

    // Start XAMPP
    startXampp();

    // Thora wait taake Apache start ho jaye
    setTimeout(() => {
        createWindow();
    }, 5000);

    app.on('activate', () => {
        if (BrowserWindow.getAllWindows().length === 0) {
            createWindow();
        }
    });
});

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') {
        app.quit();
    }
});