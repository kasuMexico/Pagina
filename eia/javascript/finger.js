"use strict";

/*****************************************************************************
 * Función objetoAjax
 * Crea y retorna un objeto XMLHttpRequest compatible con la mayoría de navegadores.
 *****************************************************************************/
function objetoAjax() {
    if (window.XMLHttpRequest) {
        return new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        try {
            return new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                return new ActiveXObject("Microsoft.XMLHTTP");
            } catch (E) {
                return null;
            }
        }
    }
    return null;
}

/*****************************************************************************
 * Función enviarDatos
 * Esta función encapsula la generación de la huella digital (fingerprint)
 * mediante el módulo Fingerprint. Se define como un IIFE (Immediately Invoked Function Expression)
 * para que pueda ser utilizado tanto en entornos CommonJS/AMD como en el navegador.
 *****************************************************************************/
function enviarDatos() {
    (function(name, context, definition) {
        if (typeof module !== 'undefined' && module.exports) {
            module.exports = definition();
        } else if (typeof define === 'function' && define.amd) {
            define(definition);
        } else {
            context[name] = definition();
        }
    })('Fingerprint', this, function() {
        "use strict";
        // Constructor de Fingerprint: recibe opciones para ajustar el fingerprint.
        function Fingerprint(options) {
            this.hasher = (options && typeof options === 'object') ? options.hasher : null;
            this.screen_resolution = options && options.screen_resolution;
            this.screen_orientation = options && options.screen_orientation;
            this.canvas = options && options.canvas;
            this.ie_activex = options && options.ie_activex;
        }
        Fingerprint.prototype = {
            // get(): Recopila diversas propiedades y genera una cadena hash única.
            get: function() {
                const keys = [];
                keys.push(navigator.userAgent);
                keys.push(navigator.language);
                keys.push(screen.colorDepth);
                if (this.screen_resolution) {
                    const resolution = this.getScreenResolution();
                    if (resolution) {
                        keys.push(resolution.join('x'));
                    }
                }
                keys.push(new Date().getTimezoneOffset());
                keys.push(this.hasSessionStorage());
                keys.push(this.hasLocalStorage());
                keys.push(!!window.indexedDB);
                keys.push(document.body ? typeof document.body.addBehavior : typeof undefined);
                keys.push(typeof window.openDatabase);
                keys.push(navigator.cpuClass);
                keys.push(navigator.platform);
                keys.push(navigator.doNotTrack);
                keys.push(this.getPluginsString());
                if (this.canvas && this.isCanvasSupported()) {
                    keys.push(this.getCanvasFingerprint());
                }
                const keyStr = keys.join('###');
                if (this.hasher) {
                    return this.hasher(keyStr, 31);
                } else {
                    return this.murmurhash3_32_gc(keyStr, 31);
                }
            },
            // murmurhash3_32_gc(): Algoritmo hash para generar el fingerprint.
            murmurhash3_32_gc: function(key, seed) {
                let remainder = key.length & 3;
                let bytes = key.length - remainder;
                let h1 = seed, c1 = 0xcc9e2d51, c2 = 0x1b873593;
                let i = 0;
                while (i < bytes) {
                    let k1 = ((key.charCodeAt(i) & 0xff)) |
                             ((key.charCodeAt(++i) & 0xff) << 8) |
                             ((key.charCodeAt(++i) & 0xff) << 16) |
                             ((key.charCodeAt(++i) & 0xff) << 24);
                    ++i;
                    k1 = (((k1 & 0xffff) * c1) + ((((k1 >>> 16) * c1) & 0xffff) << 16)) & 0xffffffff;
                    k1 = (k1 << 15) | (k1 >>> 17);
                    k1 = (((k1 & 0xffff) * c2) + ((((k1 >>> 16) * c2) & 0xffff) << 16)) & 0xffffffff;
                    h1 ^= k1;
                    h1 = (h1 << 13) | (h1 >>> 19);
                    let h1b = (((h1 & 0xffff) * 5) + ((((h1 >>> 16) * 5) & 0xffff) << 16)) & 0xffffffff;
                    h1 = ((h1b & 0xffff) + 0x6b64 + ((((h1b >>> 16) + 0xe654) & 0xffff) << 16));
                }
                let k1 = 0;
                switch (remainder) {
                    case 3: k1 ^= (key.charCodeAt(i + 2) & 0xff) << 16;
                    case 2: k1 ^= (key.charCodeAt(i + 1) & 0xff) << 8;
                    case 1: k1 ^= (key.charCodeAt(i) & 0xff);
                        k1 = (((k1 & 0xffff) * c1) + ((((k1 >>> 16) * c1) & 0xffff) << 16)) & 0xffffffff;
                        k1 = (k1 << 15) | (k1 >>> 17);
                        k1 = (((k1 & 0xffff) * c2) + ((((k1 >>> 16) * c2) & 0xffff) << 16)) & 0xffffffff;
                        h1 ^= k1;
                }
                h1 ^= key.length;
                h1 ^= h1 >>> 16;
                h1 = (((h1 & 0xffff) * 0x85ebca6b) + ((((h1 >>> 16) * 0x85ebca6b) & 0xffff) << 16)) & 0xffffffff;
                h1 ^= h1 >>> 13;
                h1 = ((((h1 & 0xffff) * 0xc2b2ae35) + ((((h1 >>> 16) * 0xc2b2ae35) & 0xffff) << 16))) & 0xffffffff;
                h1 ^= h1 >>> 16;
                return h1 >>> 0;
            },
            hasLocalStorage: function() {
                try {
                    return !!window.localStorage;
                } catch (e) {
                    return true;
                }
            },
            hasSessionStorage: function() {
                try {
                    return !!window.sessionStorage;
                } catch (e) {
                    return true;
                }
            },
            isCanvasSupported: function() {
                const elem = document.createElement('canvas');
                return !!(elem.getContext && elem.getContext('2d'));
            },
            isIE: function() {
                return navigator.appName === 'Microsoft Internet Explorer' ||
                       (navigator.appName === 'Netscape' && /trident/.test(navigator.userAgent));
            },
            getPluginsString: function() {
                return this.isIE() && this.ie_activex ? this.getIEPluginsString() : this.getRegularPluginsString();
            },
            getRegularPluginsString: function() {
                return this.map(navigator.plugins, p => {
                    const mimeTypes = this.map(p, mt => [mt.type, mt.suffixes].join('~')).join(',');
                    return [p.name, p.description, mimeTypes].join('::');
                }, this).join(';');
            },
            getIEPluginsString: function() {
                if (window.ActiveXObject) {
                    const names = [
                        'ShockwaveFlash.ShockwaveFlash',
                        'AcroPDF.PDF',
                        'PDF.PdfCtrl',
                        'QuickTime.QuickTime',
                        'rmocx.RealPlayer G2 Control',
                        'rmocx.RealPlayer G2 Control.1',
                        'RealPlayer.RealPlayer(tm) ActiveX Control (32-bit)',
                        'RealVideo.RealVideo(tm) ActiveX Control (32-bit)',
                        'RealPlayer',
                        'SWCtl.SWCtl',
                        'WMPlayer.OCX',
                        'AgControl.AgControl',
                        'Skype.Detection'
                    ];
                    return this.map(names, name => {
                        try {
                            new ActiveXObject(name);
                            return name;
                        } catch (e) {
                            return null;
                        }
                    }).join(';');
                }
                return "";
            },
            getScreenResolution: function() {
                return this.screen_orientation ?
                    (screen.height > screen.width ? [screen.height, screen.width] : [screen.width, screen.height]) :
                    [screen.height, screen.width];
            },
            getCanvasFingerprint: function() {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const txt = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()_+-={}|[]\\:"<>?;,.';
                ctx.textBaseline = "top";
                ctx.font = "14px 'Arial'";
                ctx.textBaseline = "alphabetic";
                ctx.fillStyle = "#f60";
                ctx.fillRect(125, 1, 62, 20);
                ctx.fillStyle = "#069";
                ctx.fillText(txt, 2, 15);
                ctx.fillStyle = "rgba(102, 204, 0, 0.7)";
                ctx.fillText(txt, 4, 17);
                return canvas.toDataURL();
            },
            // Métodos auxiliares each y map para recorrer objetos y arrays.
            each: function(obj, iterator, context) {
                if (obj == null) return;
                if (Array.prototype.forEach && obj.forEach === Array.prototype.forEach) {
                    obj.forEach(iterator, context);
                } else if (obj.length === +obj.length) {
                    for (let i = 0, l = obj.length; i < l; i++) {
                        iterator.call(context, obj[i], i, obj);
                    }
                } else {
                    for (let key in obj) {
                        if (obj.hasOwnProperty(key)) {
                            iterator.call(context, obj[key], key, obj);
                        }
                    }
                }
            },
            map: function(obj, iterator, context) {
                const results = [];
                if (obj == null) return results;
                if (Array.prototype.map && obj.map === Array.prototype.map) return obj.map(iterator, context);
                this.each(obj, function(value, index, list) {
                    results.push(iterator.call(context, value, index, list));
                });
                return results;
            }
        };
        return Fingerprint;
    });

    // Fin del módulo Fingerprint

    /*****************************************************************************
     * Función fingerprint_flash:
     * Obtiene la versión del reproductor Flash instalado utilizando swfobject.
     *****************************************************************************/
    function fingerprint_flash() {
        "use strict";
        try {
            const objPlayerVersion = swfobject.getFlashPlayerVersion();
            let version = objPlayerVersion.major + "." + objPlayerVersion.minor + "." + objPlayerVersion.release;
            return (version === "0.0.0") ? "N/A" : version;
        } catch (err) {
            return "N/A";
        }
    }

    /*****************************************************************************
     * Función fingerprint_browser:
     * Determina el navegador a partir del userAgent y retorna una cadena con el nombre y la versión.
     *****************************************************************************/
    function fingerprint_browser() {
        "use strict";
        const ua = navigator.userAgent.toLowerCase();
        if (ua.indexOf("msie") !== -1 || ua.indexOf("trident") !== -1) {
            if (ua.indexOf("trident/7") > -1) return "Internet Explorer 11";
            if (ua.indexOf("trident/6") > -1) return "Internet Explorer 10";
            if (ua.indexOf("trident/5") > -1) return "Internet Explorer 9";
            if (ua.indexOf("trident/4") > -1) return "Internet Explorer 8";
            return "Internet Explorer";
        } else if (/firefox[\/\s](\d+\.\d+)/.test(ua)) {
            return "Firefox " + RegExp.$1;
        } else if (/opera[\/\s](\d+\.\d+)/.test(ua)) {
            return "Opera " + RegExp.$1;
        } else if (/chrome[\/\s](\d+\.\d+)/.test(ua)) {
            return "Chrome " + RegExp.$1;
        } else if (/version[\/\s](\d+\.\d+)/.test(ua)) {
            return "Safari " + RegExp.$1;
        } else {
            return "Unknown";
        }
    }

    /*****************************************************************************
     * Función fingerprint_canvas:
     * Genera un fingerprint basado en el contenido renderizado en un elemento canvas.
     *****************************************************************************/
    function fingerprint_canvas() {
        "use strict";
        try {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const txt = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ`~1!2@3#4$5%6^7&8*9(0)-_=+[{]}|;:\'",<.>/?';
            ctx.textBaseline = "top";
            ctx.font = "14px 'Arial'";
            ctx.textBaseline = "alphabetic";
            ctx.fillStyle = "#f60";
            ctx.fillRect(125, 1, 62, 20);
            ctx.fillStyle = "#069";
            ctx.fillText(txt, 2, 15);
            ctx.fillStyle = "rgba(102, 204, 0, 0.7)";
            ctx.fillText(txt, 4, 17);
            return canvas.toDataURL();
        } catch (err) {
            return "Error";
        }
    }

    /*****************************************************************************
     * Función fingerprint_connection:
     * Intenta obtener el tipo de conexión (aplicable en dispositivos Android).
     *****************************************************************************/
    function fingerprint_connection() {
        "use strict";
        try {
            return navigator.connection.type || "N/A";
        } catch (err) {
            return "N/A";
        }
    }

    /*****************************************************************************
     * Función fingerprint_cookie:
     * Verifica si las cookies están habilitadas en el navegador.
     *****************************************************************************/
    function fingerprint_cookie() {
        "use strict";
        try {
            let cookieEnabled = navigator.cookieEnabled;
            if (typeof cookieEnabled === "undefined" || !cookieEnabled) {
                document.cookie = "testcookie";
                cookieEnabled = document.cookie.indexOf("testcookie") !== -1;
            }
            return cookieEnabled;
        } catch (err) {
            return "Error";
        }
    }

    /*****************************************************************************
     * Función fingerprint_display:
     * Devuelve información de la pantalla: colorDepth, dimensiones y área disponible.
     *****************************************************************************/
    function fingerprint_display() {
        "use strict";
        try {
            const s = window.screen;
            return s.colorDepth + "|" + s.width + "|" + s.height + "|" + s.availWidth + "|" + s.availHeight;
        } catch (err) {
            return "Error";
        }
    }

    /*****************************************************************************
     * Función fingerprint_fontsmoothing:
     * Detecta si el suavizado de fuentes está activado, ya sea mediante la propiedad screen.fontSmoothingEnabled
     * o a través de un método alternativo con canvas.
     *****************************************************************************/
    function fingerprint_fontsmoothing() {
        "use strict";
        try {
            if (typeof screen.fontSmoothingEnabled !== "undefined") {
                return screen.fontSmoothingEnabled.toString();
            } else {
                let canvas = document.createElement('canvas');
                canvas.width = 35;
                canvas.height = 35;
                canvas.style.display = 'none';
                document.body.appendChild(canvas);
                let ctx = canvas.getContext('2d');
                ctx.textBaseline = "top";
                ctx.font = "32px Arial";
                ctx.fillStyle = "black";
                ctx.fillText("O", 0, 0);
                let pixelData = ctx.getImageData(1, 1, 1, 1).data;
                document.body.removeChild(canvas);
                return (pixelData[3] !== 255).toString();
            }
        } catch (err) {
            return "Error";
        }
    }

    /*****************************************************************************
     * Función fingerprint_fonts:
     * Recopila una lista de fuentes instaladas en el sistema, renderándolas en elementos ocultos
     * y comparando dimensiones para determinar si están disponibles.
     *****************************************************************************/
    function fingerprint_fonts() {
        "use strict";
        try {
            const style = "position: absolute; visibility: hidden; display: block !important";
            const fonts = [ "Abadi MT Condensed Light", "Adobe Fangsong Std", "Adobe Hebrew", "Adobe Ming Std", 
                "Agency FB", "Aharoni", "Andalus", "Angsana New", "AngsanaUPC", "Aparajita", "Arab", 
                "Arabic Transparent", "Arabic Typesetting", "Arial Baltic", "Arial Black", "Arial CE", 
                "Arial CYR", "Arial Greek", "Arial TUR", "Arial", "Batang", "BatangChe", "Bauhaus 93", 
                "Bell MT", "Bitstream Vera Serif", "Bodoni MT", "Bookman Old Style", "Braggadocio", "Broadway",
                "Browallia New", "BrowalliaUPC", "Calibri Light", "Calibri", "Californian FB", "Cambria Math", 
                "Cambria", "Candara", "Castellar", "Casual", "Centaur", "Century Gothic", "Chalkduster", 
                "Colonna MT", "Comic Sans MS", "Consolas", "Constantia", "Copperplate Gothic Light", "Corbel", 
                "Cordia New", "CordiaUPC", "Courier New Baltic", "Courier New CE", "Courier New CYR", 
                "Courier New Greek", "Courier New TUR", "Courier New", "DFKai-SB", "DaunPenh", "David", 
                "DejaVu LGC Sans Mono", "Desdemona", "DilleniaUPC", "DokChampa", "Dotum", "DotumChe",
                // ... (lista continua)
                "Wingdings"
            ];
            const count = fonts.length;
            const template = '<b style="display:inline; font:normal 10px/1 \'X\',sans-serif;">ww</b>' +
                             '<b style="display:inline; font:normal 10px/1 \'X\',monospace;">ww</b>';
            const fragment = document.createDocumentFragment();
            const divs = [];
            for (let i = 0; i < count; i++) {
                let font = fonts[i].replace(/['"<>]/g, '');
                const div = document.createElement('div');
                div.innerHTML = template.replace(/X/g, font);
                div.style.cssText = style;
                fragment.appendChild(div);
                divs.push(div);
            }
            document.body.insertBefore(fragment, document.body.firstChild);
            const result = [];
            for (let i = 0; i < count; i++) {
                const bTags = divs[i].getElementsByTagName('b');
                if (bTags[0].offsetWidth === bTags[1].offsetWidth) {
                    result.push(fonts[i]);
                }
            }
            // Se eliminan los divs agregados para evitar reflow innecesario.
            for (let i = 0; i < count; i++) {
                document.body.removeChild(divs[i]);
            }
            let strTemp = stripIllegalChars(result.join('|'));
            return strTemp === "" ? "None" : strTemp;
        } catch (err) {
            return glbOnError;
        }
    }

    /*****************************************************************************
     * Función fingerprint_formfields:
     * Recopila información de todos los formularios y campos de entrada no ocultos.
     *****************************************************************************/
    function fingerprint_formfields() {
        "use strict";
        const forms = document.getElementsByTagName('form');
        let data = [];
        data.push("url=" + window.location.href);
        for (let i = 0; i < forms.length; i++) {
            data.push("FORM=" + forms[i].name);
            const inputs = forms[i].getElementsByTagName('input');
            for (let j = 0; j < inputs.length; j++) {
                if (inputs[j].type !== "hidden") {
                    data.push("Input=" + inputs[j].name);
                }
            }
        }
        return data.join("|");
    }

    /*****************************************************************************
     * Función fingerprint_java:
     * Verifica si Java está habilitado en el navegador.
     *****************************************************************************/
    function fingerprint_java() {
        "use strict";
        try {
            return navigator.javaEnabled() ? "true" : "false";
        } catch (err) {
            return "Error";
        }
    }

    /*****************************************************************************
     * Función fingerprint_language:
     * Obtiene información del lenguaje configurado en el navegador y en el sistema.
     *****************************************************************************/
    function fingerprint_language() {
        "use strict";
        const sep = "|";
        const pair = "=";
        let strLang = "";
        try {
            strLang += "lang" + pair + (navigator.language || navigator.browserLanguage || "") + sep;
            strLang += "syslang" + pair + (navigator.systemLanguage || "") + sep;
            strLang += "userlang" + pair + (navigator.userLanguage || "");
            return strLang;
        } catch (err) {
            return "Error";
        }
    }

    /*****************************************************************************
     * Función fingerprint_silverlight:
     * Determina la versión de Silverlight instalada, usando ActiveX o la información del plugin.
     *****************************************************************************/
    function fingerprint_silverlight() {
        "use strict";
        try {
            let version = "";
            try {
                const control = new ActiveXObject('AgControl.AgControl');
                if (control.IsVersionSupported("5.0")) {
                    version = "5.x";
                } else if (control.IsVersionSupported("4.0")) {
                    version = "4.x";
                } else if (control.IsVersionSupported("3.0")) {
                    version = "3.x";
                } else if (control.IsVersionSupported("2.0")) {
                    version = "2.x";
                } else {
                    version = "1.x";
                }
            } catch (e) {
                const plugin = navigator.plugins["Silverlight Plug-In"];
                if (plugin) {
                    version = (plugin.description === "1.0.30226.2") ? "2.x" : parseInt(plugin.description[0], 10).toString();
                } else {
                    version = "N/A";
                }
            }
            return version;
        } catch (err) {
            return "Error";
        }
    }

    /*****************************************************************************
     * Función fingerprint_os:
     * Detecta el sistema operativo del usuario comparando el userAgent.
     *****************************************************************************/
    function fingerprint_os() {
        "use strict";
        const osList = {
            "Windows NT 6.3": "Windows 8.1",
            "Windows NT 6.2": "Windows 8",
            "Windows NT 6.1": "Windows 7",
            "Windows NT 6.0": "Windows Vista/Windows Server 2008",
            "Windows NT 5.2": "Windows XP x64/Windows Server 2003",
            "Windows NT 5.1": "Windows XP",
            "Windows NT 5.01": "Windows 2000, SP1",
            "Windows NT 5.0": "Windows 2000",
            "Windows NT 4.0": "Windows NT 4.0",
            "Win98": "Windows 98",
            "Win95": "Windows 95",
            "Win16": "Windows 3.11",
            "Open BSD": "Open BSD",
            "Sun OS": "Sun OS",
            "Ubuntu": "Ubuntu",
            "iOS": "iOS (Unknown Device)",
            "iPad": "iOS (iPad)",
            "iPod": "iOS (iPod Touch)",
            "iPhone": "iOS (iPhone)",
            "Mac OS X Beta": "Mac OSX Beta",
            "Mac OS X 10.0": "Mac OSX Cheetah",
            "Mac OS X 10.1": "Mac OSX Puma",
            "Mac OS X 10.2": "Mac OSX Jaguar",
            "Mac OS X 10.3": "Mac OSX Panther",
            "Mac OS X 10.4": "Mac OSX Tiger",
            "Mac OS X 10.5": "Mac OSX Leopard",
            "Mac OS X 10.6": "Mac OSX Snow Leopard",
            "Mac OS X 10.7": "Mac OSX Lion",
            "Mac OS X 10.8": "Mac OSX Mountain Lion",
            "Mac OS X 10.9": "Mac OSX Mavericks",
            "Mac OS X 10.10": "Mac OSX Yosemite",
            "Mac OS X 10.11": "Mac OSX El Capitan",
            "Mac OS X 10.12": "Mac OSX Sierra",
            "Mac OS X 10.13": "Mac OSX High Sierra",
            "Mac OS X 10.14": "Mac OSX Mojave",
            "Mac OS X 10.15": "Mac OSX Catalina",
            "Mac OS X 11": "Mac OSX Big Sur",
            "Linux": "Linux (Unknown Distribution)"
        };
        const ua = navigator.userAgent.toLowerCase();
        let detectedOS = "Unknown OS";
        Object.keys(osList).forEach(os => {
            if (ua.indexOf(os.toLowerCase()) !== -1) {
                detectedOS = osList[os];
            }
        });
        return detectedOS;
    }

    /*****************************************************************************
     * Función fingerprint_useragent:
     * Devuelve el userAgent concatenado con la plataforma.
     *****************************************************************************/
    function fingerprint_useragent() {
        "use strict";
        return navigator.userAgent.toLowerCase() + "|" + navigator.platform;
    }

    /*****************************************************************************
     * Función fingerprint_timezone:
     * Retorna la diferencia en horas entre la hora local y GMT.
     *****************************************************************************/
    function fingerprint_timezone() {
        "use strict";
        try {
            const offset = new Date().getTimezoneOffset();
            return (-offset / 60);
        } catch (err) {
            return "Error";
        }
    }

    /*****************************************************************************
     * Función fingerprint_touch:
     * Detecta si el dispositivo soporta eventos táctiles.
     *****************************************************************************/
    function fingerprint_touch() {
        "use strict";
        try {
            return !!document.createEvent("TouchEvent");
        } catch (ignore) {
            return false;
        }
    }

    /*****************************************************************************
     * Función fingerprint_truebrowser:
     * Intenta determinar el navegador real usando condiciones adicionales más allá del userAgent.
     *****************************************************************************/
    function fingerprint_truebrowser() {
        "use strict";
        const ua = navigator.userAgent.toLowerCase();
        if (window.chrome) return "Chrome";
        if (typeof InstallTrigger !== 'undefined') return "Firefox";
        if (window.atob) return "Internet Explorer 10+";
        if (window.XDomainRequest && window.performance) return "Internet Explorer 9";
        if (window.XDomainRequest) return "Internet Explorer 8";
        if (document.documentElement && typeof document.documentElement.style.maxHeight !== 'undefined') return "Internet Explorer 7";
        if (document.compatMode && document.all) return "Internet Explorer 6";
        if (window.createPopup) return "Internet Explorer 5.5";
        if (window.attachEvent) return "Internet Explorer 5";
        if ((ua.indexOf("msie") + 1) && window.ActiveXObject) return "Pocket Internet Explorer";
        if (navigator.vendor === "KDE") return "Konqueror";
        if (document.childNodes && !document.all && !navigator.taintEnabled && !navigator.accentColorName) return "Safari";
        if (document.childNodes && !document.all && !navigator.taintEnabled && navigator.accentColorName) return "OmniWeb 4.5+";
        if (navigator.__ice_version) return "ICEBrowser";
        if (window.ScriptEngine && ScriptEngine().indexOf("InScript") + 1 && document.createElement) return "iCab 3+";
        if (window.ScriptEngine && ScriptEngine().indexOf("InScript") + 1) return "iCab 2-";
        if (ua.indexOf("hotjava") + 1 && (typeof navigator.accentColorName === "undefined")) return "HotJava";
        if (document.layers && !document.classes) return "Omniweb 4.2-";
        if (document.layers && !navigator.mimeTypes["*"]) return "Escape 4";
        if (document.layers) return "Netscape 4";
        if (window.opera && document.getElementsByClassName) return "Opera 9.5+";
        if (window.opera && window.getComputedStyle) return "Opera 8";
        if (window.opera && document.childNodes) return "Opera 7";
        if (window.opera) return "Opera " + window.opera.version();
        if (navigator.appName.indexOf("WebTV") + 1) return "WebTV";
        if (ua.indexOf("netgem") + 1) return "Netgem NetBox";
        if (ua.indexOf("opentv") + 1) return "OpenTV";
        if (ua.indexOf("ipanel") + 1) return "iPanel MicroBrowser";
        if (document.getElementById && !document.childNodes) return "Clue browser";
        if (navigator.product && navigator.product.indexOf("Hv") === 0) return "Tkhtml Hv3+";
        return "Unknown";
    }

    // Inyección de los valores de fingerprint en el elemento con id "FingerPrint".
    // Nota: Revisa el orden de asignación de las funciones; actualmente:
    // - El input 'truebrowser' recibe fingerprint_plugins()
    // - El input 'plugins' recibe fingerprint_useragent()
    // - El input 'useragent' recibe fingerprint_truebrowser()
    // Ajusta el orden si deseas que cada campo muestre la función correspondiente.
    const fp = new Fingerprint({
        canvas: true,
        ie_activex: true,
        screen_resolution: true
    });
    const uid = fp.get();
    document.getElementById("FingerPrint").innerHTML = "\
        <input name='fingerprint' type='text' id='fingerprint' value='" + uid + "' >\
        <input name='browser' type='text' id='browser' value='" + fingerprint_browser() + "' >\
        <input name='flash' type='text' id='flash' value='" + fingerprint_flash() + "' >\
        <input name='canvas' type='text' id='canvas' value='" + fingerprint_canvas() + "' >\
        <input name='connection' type='text' id='connection' value='" + fingerprint_connection() + "' >\
        <input name='cookie' type='text' id='cookie' value='" + fingerprint_cookie() + "' >\
        <input name='display' type='text' id='display' value='" + fingerprint_display() + "' >\
        <input name='fontsmoothing' type='text' id='fontsmoothing' value='" + fingerprint_fontsmoothing() + "' >\
        <input name='fonts' type='text' id='fonts' value='" + fingerprint_fonts() + "' >\
        <input name='formfields' type='text' id='formfields' value='" + fingerprint_formfields() + "' >\
        <input name='java' type='text' id='java' value='" + fingerprint_java() + "' >\
        <input name='language' type='text' id='language' value='" + fingerprint_language() + "' >\
        <input name='silverlight' type='text' id='silverlight' value='" + fingerprint_silverlight() + "' >\
        <input name='os' type='text' id='os' value='" + fingerprint_os() + "' >\
        <input name='timezone' type='text' id='timezone' value='" + fingerprint_timezone() + "' >\
        <input name='touch' type='text' id='touch' value='" + fingerprint_touch() + "' >\
        <input name='truebrowser' type='text' id='truebrowser' value='" + fingerprint_plugins() + "' >\
        <input name='plugins' type='text' id='plugins' value='" + fingerprint_useragent() + "' >\
        <input name='useragent' type='text' id='useragent' value='" + fingerprint_truebrowser() + "' >\
    ";
