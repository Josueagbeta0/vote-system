/**
 * Browser Fingerprinting - Génère une empreinte unique du navigateur
 * IMPORTANT: Ceci respecte la vie privée - pas de tracking, juste anti-fraude
 */

function generateFingerprint() {
    const components = [];
    
    // 1. User Agent
    components.push(navigator.userAgent);
    
    // 2. Langue
    components.push(navigator.language || navigator.userLanguage);
    
    // 3. Résolution d'écran
    components.push(screen.width + 'x' + screen.height);
    components.push(screen.colorDepth);
    
    // 4. Timezone
    components.push(new Date().getTimezoneOffset());
    
    // 5. Plugins (limité pour éviter trop de variations)
    if (navigator.plugins) {
        const pluginsList = Array.from(navigator.plugins)
            .map(p => p.name)
            .slice(0, 5) // Limiter à 5 plugins
            .join(',');
        components.push(pluginsList);
    }
    
    // 6. Canvas fingerprinting (détection basique)
    try {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        ctx.textBaseline = 'top';
        ctx.font = '14px Arial';
        ctx.fillText('Vote System', 2, 2);
        components.push(canvas.toDataURL().slice(0, 50)); // Limiter la taille
    } catch(e) {
        components.push('canvas-error');
    }
    
    // 7. WebGL vendor et renderer
    try {
        const gl = document.createElement('canvas').getContext('webgl');
        if (gl) {
            const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
            if (debugInfo) {
                components.push(gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL));
                components.push(gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL));
            }
        }
    } catch(e) {
        components.push('webgl-error');
    }
    
    // 8. Touch support
    components.push('ontouchstart' in window);
    
    // 9. Platform
    components.push(navigator.platform);
    
    // 10. Hardware concurrency (nombre de CPU)
    components.push(navigator.hardwareConcurrency || 'unknown');
    
    // Générer un hash SHA-256
    const fingerprint = components.join('|||');
    return hashString(fingerprint);
}

/**
 * Fonction de hachage simple (SHA-256 simplifié pour le navigateur)
 */
async function hashString(str) {
    const msgBuffer = new TextEncoder().encode(str);
    const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    return hashHex;
}

/**
 * Injecter l'empreinte dans le formulaire d'inscription
 */
async function injectFingerprint(formId) {
    const fingerprint = await generateFingerprint();
    
    // Créer un champ caché dans le formulaire
    const form = document.getElementById(formId);
    if (form) {
        let fingerprintField = document.getElementById('browser_fingerprint');
        if (!fingerprintField) {
            fingerprintField = document.createElement('input');
            fingerprintField.type = 'hidden';
            fingerprintField.id = 'browser_fingerprint';
            fingerprintField.name = 'browser_fingerprint';
            form.appendChild(fingerprintField);
        }
        fingerprintField.value = fingerprint;
    }
}

/**
 * Collecter des informations supplémentaires sur l'appareil
 */
function getDeviceInfo() {
    return {
        screen: {
            width: screen.width,
            height: screen.height,
            availWidth: screen.availWidth,
            availHeight: screen.availHeight,
            colorDepth: screen.colorDepth,
            pixelDepth: screen.pixelDepth
        },
        navigator: {
            userAgent: navigator.userAgent,
            language: navigator.language,
            languages: navigator.languages,
            platform: navigator.platform,
            hardwareConcurrency: navigator.hardwareConcurrency,
            deviceMemory: navigator.deviceMemory,
            maxTouchPoints: navigator.maxTouchPoints
        },
        timezone: {
            offset: new Date().getTimezoneOffset(),
            name: Intl.DateTimeFormat().resolvedOptions().timeZone
        }
    };
}

/**
 * Injecter les infos de l'appareil dans le formulaire
 */
function injectDeviceInfo(formId) {
    const deviceInfo = getDeviceInfo();
    const form = document.getElementById(formId);
    
    if (form) {
        let deviceField = document.getElementById('device_fingerprint');
        if (!deviceField) {
            deviceField = document.createElement('input');
            deviceField.type = 'hidden';
            deviceField.id = 'device_fingerprint';
            deviceField.name = 'device_fingerprint';
            form.appendChild(deviceField);
        }
        deviceField.value = JSON.stringify(deviceInfo);
    }
}

// Auto-exécution au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Détecter le formulaire d'inscription
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        injectFingerprint('registerForm');
        injectDeviceInfo('registerForm');
    }
});