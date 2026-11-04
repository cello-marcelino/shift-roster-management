const { contextBridge } = require('electron');

// Expose safe, minimal desktop environment properties to the renderer
contextBridge.exposeInMainWorld('electronAPI', {
    isDesktop: true,
    platform: process.platform,
    version: process.versions.electron,
});
