//La función devuelve el objeto XMLHttpRequest creado para que pueda ser utilizado en otras partes del código para realizar solicitudes Ajax.
function objetoAjax() {
  var xmlhttp = null;

  if (window.XMLHttpRequest) {
    xmlhttp = new XMLHttpRequest();
  } else if (window.ActiveXObject) {
    try {
      xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
      try {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
      } catch (E) {
        xmlhttp = null;
      }
    }
  }
  return xmlhttp;
}

function enviarDatos(){
	//comprobar si el módulo está siendo utilizado en un entorno de Node.js o en un entorno de navegador
	;(function(name,context,definition) {
		if (typeof module !== 'undefined' && module.exports) {
			module.exports = definition();
		} else if (typeof define === 'function' && define.amd) {
			define(definition);
		} else {
			context[name] = definition();
		}
	} )
//El propósito de esta función es generar una huella digital única basada en varias propiedades del navegador y del sistema del usuario.
	('Fingerprint', this, function() {
		'use strict'; var Fingerprint = function(options) {
			var nativeForEach, nativeMap;
			nativeForEach = Array.prototype.forEach;
			nativeMap = Array.prototype.map;
			this.each = function(obj, iterator, context) {
				if (obj === null) {
					return;
				}
				if (nativeForEach && obj.forEach === nativeForEach) {
					obj.forEach(iterator, context);
				} else if (obj.length === +obj.length) {
					for (var i = 0, l = obj.length; i < l; i++) {
						if (iterator.call(context, obj[i], i, obj) === {}) return;
					}
				} else {
					for (var key in obj) {
						if (obj.hasOwnProperty(key)) {
							if (iterator.call(context, obj[key], key, obj) === {}) return;
						}
					}
				}
			};
			this.map = function(obj, iterator, context) {
				var results = [];
				if (obj == null) return results;
				if (nativeMap && obj.map === nativeMap) return obj.map(iterator, context);
				this.each(obj, function(value, index, list) {
					results[results.length] = iterator.call(context, value, index, list);
				});
				return results;
			};
			if (typeof options == 'object') {
				this.hasher = options.hasher;
				this.screen_resolution = options.screen_resolution;
				this.screen_orientation = options.screen_orientation;
				this.canvas = options.canvas;
				this.ie_activex = options.ie_activex;
			} else if (typeof options == 'function') {
				this.hasher = options;
			}
		};
//Este código define una función que se utiliza para generar un identificador único (fingerprint) del navegador del usuario
		Fingerprint.prototype = {
			get: function() {
				var keys = []; keys.push(navigator.userAgent); keys.push(navigator.language);
				keys.push(screen.colorDepth);
				if (this.screen_resolution) {
					var resolution = this.getScreenResolution();
					if (typeof resolution !== 'undefined') {
						keys.push(this.getScreenResolution().join('x'));
					}
				}
				keys.push(new Date().getTimezoneOffset());
				keys.push(this.hasSessionStorage());
				keys.push(this.hasLocalStorage());
				keys.push(!!window.indexedDB);
				if (document.body) {
					keys.push(typeof(document.body.addBehavior));
				} else {
					keys.push(typeof undefined);
				}
				keys.push(typeof(window.openDatabase));
				keys.push(navigator.cpuClass);
				keys.push(navigator.platform);
				keys.push(navigator.doNotTrack);
				keys.push(this.getPluginsString());
				if (this.canvas && this.isCanvasSupported()) {
					keys.push(this.getCanvasFingerprint());
				}
				if (this.hasher) {
					return this.hasher(keys.join('###'), 31);
				} else {
					return this.murmurhash3_32_gc(keys.join('###'), 31);
				}
			},
			murmurhash3_32_gc: function(key, seed) {
				var remainder, bytes, h1, h1b, c1, c2, k1, i;
				remainder = key.length & 3;
				bytes = key.length - remainder;
				h1 = seed; c1 = 0xcc9e2d51;
				c2 = 0x1b873593;
				i = 0;
				while (i < bytes) {
					k1 = ((key.charCodeAt(i) & 0xff)) | ((key.charCodeAt(++i) & 0xff) << 8) | ((key.charCodeAt(++i) & 0xff) << 16) | ((key.charCodeAt(++i) & 0xff) << 24);
					++i; k1 = ((((k1 & 0xffff) * c1) + ((((k1 >>> 16) * c1) & 0xffff) << 16))) & 0xffffffff;
					k1 = (k1 << 15) | (k1 >>> 17); k1 = ((((k1 & 0xffff) * c2) + ((((k1 >>> 16) * c2) & 0xffff) << 16))) & 0xffffffff;
					h1 ^= k1;
					h1 = (h1 << 13) | (h1 >>> 19);
					h1b = ((((h1 & 0xffff) * 5) + ((((h1 >>> 16) * 5) & 0xffff) << 16))) & 0xffffffff;
					h1 = (((h1b & 0xffff) + 0x6b64) + ((((h1b >>> 16) + 0xe654) & 0xffff) << 16));
				}
				k1 = 0;
				switch (remainder) {
					case 3: k1 ^= (key.charCodeAt(i + 2) & 0xff) << 16;
					case 2: k1 ^= (key.charCodeAt(i + 1) & 0xff) << 8;
					case 1: k1 ^= (key.charCodeAt(i) & 0xff);
					k1 = (((k1 & 0xffff) * c1) + ((((k1 >>> 16) * c1) & 0xffff) << 16)) & 0xffffffff;
					k1 = (k1 << 15) | (k1 >>> 17);
					k1 = (((k1 & 0xffff) * c2) + ((((k1 >>> 16) * c2) & 0xffff) << 16)) & 0xffffffff;
					h1 ^= k1; } h1 ^= key.length; h1 ^= h1 >>> 16;
					h1 = (((h1 & 0xffff) * 0x85ebca6b) + ((((h1 >>> 16) * 0x85ebca6b) & 0xffff) << 16)) & 0xffffffff;
					h1 ^= h1 >>> 13;
					h1 = ((((h1 & 0xffff) * 0xc2b2ae35) + ((((h1 >>> 16) * 0xc2b2ae35) & 0xffff) << 16))) & 0xffffffff;
					h1 ^= h1 >>> 16;
					return h1 >>> 0;
			},
			hasLocalStorage: function() {
				try {
					return !!window.localStorage;
				}
				catch (e) {
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
				var elem = document.createElement('canvas');
				return !!(elem.getContext && elem.getContext('2d'));
			},
			isIE: function() {
				if (navigator.appName === 'Microsoft Internet Explorer') {
					return true;
				} else if (navigator.appName === 'Netscape' && /Trident/.test(navigator.userAgent)) {
					return true;
				} return false;
			},
			getPluginsString: function() {
				if (this.isIE() && this.ie_activex) {
					return this.getIEPluginsString();
				} else {
					return this.getRegularPluginsString();
				}
			},
			getRegularPluginsString: function() {
				return this.map(navigator.plugins, function(p) {
					var mimeTypes = this.map(p, function(mt) {
						return [mt.type, mt.suffixes].join('~');
					}).join(',');
					return [p.name, p.description, mimeTypes].join('::');
				}, this).join(';');
			},
			getIEPluginsString: function() {
				if (window.ActiveXObject) {
					var names = ['ShockwaveFlash.ShockwaveFlash', 'AcroPDF.PDF', 'PDF.PdfCtrl', 'QuickTime.QuickTime', 'rmocx.RealPlayer G2 Control', 'rmocx.RealPlayer G2 Control.1', 'RealPlayer.RealPlayer(tm) ActiveX Control (32-bit)', 'RealVideo.RealVideo(tm) ActiveX Control (32-bit)', 'RealPlayer', 'SWCtl.SWCtl', 'WMPlayer.OCX', 'AgControl.AgControl', 'Skype.Detection' ];
					return this.map(names, function(name) {
						try {
							new ActiveXObject(name); return name;
						} catch (e) {
							return null;
						}
					}).join(';');
				} else {
					return "";
				}
			},
			getScreenResolution: function() {
				var resolution;
				if (this.screen_orientation) {
					resolution = (screen.height > screen.width) ? [screen.height, screen.width] : [screen.width, screen.height];
				} else {
					resolution = [screen.height, screen.width];
				}
				return resolution;
			},
			getCanvasFingerprint: function() {
				var canvas = document.createElement('canvas');
				var ctx = canvas.getContext('2d'); var txt = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()_+-={}|[]\:"<>?;,.';
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
			}
		};
		return Fingerprint;
	});
// obtener la versión del reproductor Flash instalado en el navegador del usuario.
	function fingerprint_flash() {
		"use strict";
		var strOnError, objPlayerVersion, strVersion, strOut;
		strOnError = "N/A";
		objPlayerVersion = null;
		strVersion = null;
		strOut = null;
		try {
			objPlayerVersion = swfobject.getFlashPlayerVersion();
			strVersion = objPlayerVersion.major + "." + objPlayerVersion.minor + "." + objPlayerVersion.release;
			if (strVersion === "0.0.0") {
				strVersion = "N/A";
			}
			strOut = strVersion;
			return strOut;
		} catch (err) {
			return strOnError;
		}
	}
//Este código es una función que detecta el navegador web que está utilizando el usuario y devuelve una cadena que indica el nombre del navegador y su versión.
function fingerprint_browser() {
    "use strict";
    const userAgent = navigator.userAgent.toLowerCase();
    const browsers = {
        "msie": (ua) => {
            let version = ua.indexOf("trident/7") > -1 ? 11 :
                ua.indexOf("trident/6") > -1 ? 10 :
                ua.indexOf("trident/5") > -1 ? 9 :
                ua.indexOf("trident/4") > -1 ? 8 : null;
            return version ? `Internet Explorer ${version}` : null;
        },
        "firefox": (ua) => {
            return /firefox[\/\s](\d+\.\d+)/.test(ua) ? `Firefox ${RegExp.$1}` : null;
        },
        "opera": (ua) => {
            return /opera[\/\s](\d+\.\d+)/.test(ua) ? `Opera ${RegExp.$1}` : null;
        },
        "chrome": (ua) => {
            return /chrome[\/\s](\d+\.\d+)/.test(ua) ? `Chrome ${RegExp.$1}` : null;
        },
        "safari": (ua) => {
            return /version[\/\s](\d+\.\d+)/.test(ua) ? `Safari ${RegExp.$1}` : null;
        },
        "mozilla": (ua) => {
            return /rv[\/\s](\d+\.\d+)/.test(ua) ||
                   /mozilla[\/\s](\d+\.\d+)/.test(ua) ? `Mozilla ${RegExp.$1}` : null;
        },
        "binget": (ua) => {
            return /binget[\/\s](\d+\.\d+)/.test(ua) ? `Library (BinGet) ${RegExp.$1}` : null;
        },
        "curl": (ua) => {
            return /curl[\/\s](\d+\.\d+)/.test(ua) ? `Library (cURL) ${RegExp.$1}` : null;
        },
        "java": (ua) => {
            return /java[\/\s](\d+\.\d+)/.test(ua) ? `Library (Java) ${RegExp.$1}` : null;
        },
        "libwww-perl": (ua) => {
            return /libwww-perl[\/\s](\d+\.\d+)/.test(ua) ? `Library (libwww-perl) ${RegExp.$1}` : null;
        },
        "microsoft url control": (ua) => {
            return /microsoft url control -[\s](\d+\.\d+)/.test(ua) ? `Library (Microsoft URL Control) ${RegExp.$1}` : null;
        }
    };
    for (let browser in browsers) {
        const result = browsers[browser](userAgent);
        if (result !== null) {
            return result;
        }
    }
    return "Unknown";
}
//dibuja una cadena de texto en él, después convierte el contenido del lienzo en una imagen y la devuelve como un dato de URL
	function fingerprint_canvas() {
		"use strict";
		var strOnError, canvas, strCText, strText, strOut;
		strOnError = "Error";
		canvas = null;
		strCText = null;
		strText = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ`~1!2@3#4$5%6^7&8*9(0)-_=+[{]}|;:',<.>/?";
		strOut = null;
		try {
			canvas = document.createElement('canvas');
			strCText = canvas.getContext('2d');
			strCText.textBaseline = "top";
			strCText.font = "14px 'Arial'";
			strCText.textBaseline = "alphabetic";
			strCText.fillStyle = "#f60";
			strCText.fillRect(125, 1, 62, 20);
			strCText.fillStyle = "#069";
			strCText.fillText(strText, 2, 15);
			strCText.fillStyle = "rgba(102, 204, 0, 0.7)";
			strCText.fillText(strText, 4, 17);
			strOut = canvas.toDataURL();
			return strOut;
		} catch (err) {
			return strOnError;
		}
	}
//Este código intenta obtener el tipo de conexión a internet del dispositivo del usuario
	function fingerprint_connection() {
		"use strict";
		var strOnError, strConnection, strOut;
		strOnError = "N/A";
		strConnection = null;
		strOut = null;
		try {
			// only on android
			strConnection = navigator.connection.type;
			strOut = strConnection;
		} catch (err) {
			// return N/A if navigator.connection object does not apply to this device
			return strOnError;
		}
		return strOut;
	}
//Este código verifica si las cookies están habilitadas en el navegador del usuario
	function fingerprint_cookie() {
		"use strict";
		var strOnError, bolCookieEnabled, bolOut;
		strOnError = "Error";
		bolCookieEnabled = null;
		bolOut = null;
		try {
			bolCookieEnabled = (navigator.cookieEnabled) ? true : false;
			//if not IE4+ nor NS6+
			if (typeof navigator.cookieEnabled === "undefined" && !bolCookieEnabled) {
				document.cookie = "testcookie";
				bolCookieEnabled = (document.cookie.indexOf("testcookie") !== -1) ? true : false;
			}
			bolOut = bolCookieEnabled;
			return bolOut;
		} catch (err) {
			return strOnError;
		}
	}
//La función "fingerprint_display" intenta obtener información sobre la pantalla del usuario y devuelve un string que contiene la profundidad de color, el ancho y la altura de la pantalla
	function fingerprint_display() {
		"use strict";
		var strSep, strPair, strOnError, strScreen, strDisplay, strOut;
		strSep = "|";
		strPair = "=";
		strOnError = "Error";
		strScreen = null;
		strDisplay = null;
		strOut = null;
		try {
			strScreen = window.screen;
			if (strScreen) {
				strDisplay = strScreen.colorDepth + strSep + strScreen.width + strSep + strScreen.height + strSep + strScreen.availWidth + strSep + strScreen.availHeight;
			}
			strOut = strDisplay;
			return strOut;
		} catch (err) {
			return strOnError;
		}
	}
//Este código se utiliza para obtener información sobre la suavidad de las fuentes de texto en la pantalla del usuario.
	function fingerprint_fontsmoothing() {
		"use strict";
		var strOnError, strFontSmoothing, canvasNode, ctx, i, j, imageData, alpha, strOut;
		strOnError = "Unknown";
		strFontSmoothing = null;
		canvasNode = null;
		ctx = null;
		imageData = null;
		alpha = null;
		strOut = null;
		if (typeof(screen.fontSmoothingEnabled) !== "undefined") {
			strFontSmoothing = screen.fontSmoothingEnabled;
		} else {
			try {
				fontsmoothing = "false";
				canvasNode = document.createElement('canvas');
				canvasNode.width = "35";
				canvasNode.height = "35";
				canvasNode.style.display = 'none';
				document.body.appendChild(canvasNode);
				ctx = canvasNode.getContext('2d');
				ctx.textBaseline = "top";
				ctx.font = "32px Arial";
				ctx.fillStyle = "black";
				ctx.strokeStyle = "black";
				ctx.fillText("O", 0, 0);
				for (j = 8; j <= 32; j = j + 1) {
					for (i = 1; i <= 32; i = i + 1) {
						imageData = ctx.getImageData(i, j, 1, 1).data;
						alpha = imageData[3];
						if (alpha !== 255 && alpha !== 0) {
							strFontSmoothing = "true"; // font-smoothing must be on.
						}
					}
				}
				strOut = strFontSmoothing;
			} catch (err) {
				return strOnError;
			}
		}
		strOut = strFontSmoothing;
		return strOut;
	}
//Este código es una función que se encarga de obtener una huella digital de las fuentes instaladas en el sistema del usuario
	function fingerprint_fonts() {
		"use strict";
		var strOnError, style, fonts, count, template, fragment, divs, i, font, div, body, result, e;
		strOnError = "Error";
		style = null;
		fonts = null;
		font = null;
		count = 0;
		template = null;
		divs = null;
		e = null;
		div = null;
		body = null;
		i = 0;
		try {
			style = "position: absolute; visibility: hidden; display: block !important";
			fonts = ["Abadi MT Condensed Light", "Adobe Fangsong Std", "Adobe Hebrew", "Adobe Ming Std", "Agency FB", "Aharoni", "Andalus", "Angsana New", "AngsanaUPC", "Aparajita", "Arab", "Arabic Transparent", "Arabic Typesetting", "Arial Baltic", "Arial Black", "Arial CE", "Arial CYR", "Arial Greek", "Arial TUR", "Arial", "Batang", "BatangChe", "Bauhaus 93", "Bell MT", "Bitstream Vera Serif", "Bodoni MT", "Bookman Old Style", "Braggadocio", "Broadway", "Browallia New", "BrowalliaUPC", "Calibri Light", "Calibri", "Californian FB", "Cambria Math", "Cambria", "Candara", "Castellar", "Casual", "Centaur", "Century Gothic", "Chalkduster", "Colonna MT", "Comic Sans MS", "Consolas", "Constantia", "Copperplate Gothic Light", "Corbel", "Cordia New", "CordiaUPC", "Courier New Baltic", "Courier New CE", "Courier New CYR", "Courier New Greek", "Courier New TUR", "Courier New", "DFKai-SB", "DaunPenh", "David", "DejaVu LGC Sans Mono", "Desdemona", "DilleniaUPC", "DokChampa", "Dotum", "DotumChe", "Ebrima", "Engravers MT", "Eras Bold ITC", "Estrangelo Edessa", "EucrosiaUPC", "Euphemia", "Eurostile", "FangSong", "Forte", "FrankRuehl", "Franklin Gothic Heavy", "Franklin Gothic Medium", "FreesiaUPC", "French Script MT", "Gabriola", "Gautami", "Georgia", "Gigi", "Gisha", "Goudy Old Style", "Gulim", "GulimChe", "GungSeo", "Gungsuh", "GungsuhChe", "Haettenschweiler", "Harrington", "Hei S", "HeiT", "Heisei Kaku Gothic", "Hiragino Sans GB", "Impact", "Informal Roman", "IrisUPC", "Iskoola Pota", "JasmineUPC", "KacstOne", "KaiTi", "Kalinga", "Kartika", "Khmer UI", "Kino MT", "KodchiangUPC", "Kokila", "Kozuka Gothic Pr6N", "Lao UI", "Latha", "Leelawadee", "Levenim MT", "LilyUPC", "Lohit Gujarati", "Loma", "Lucida Bright", "Lucida Console", "Lucida Fax", "Lucida Sans Unicode", "MS Gothic", "MS Mincho", "MS PGothic", "MS PMincho", "MS Reference Sans Serif", "MS UI Gothic", "MV Boli", "Magneto", "Malgun Gothic", "Mangal", "Marlett", "Matura MT Script Capitals", "Meiryo UI", "Meiryo", "Menlo", "Microsoft Himalaya", "Microsoft JhengHei", "Microsoft New Tai Lue", "Microsoft PhagsPa", "Microsoft Sans Serif", "Microsoft Tai Le", "Microsoft Uighur", "Microsoft YaHei", "Microsoft Yi Baiti", "MingLiU", "MingLiU-ExtB", "MingLiU_HKSCS", "MingLiU_HKSCS-ExtB", "Miriam Fixed", "Miriam", "Mongolian Baiti", "MoolBoran", "NSimSun", "Narkisim", "News Gothic MT", "Niagara Solid", "Nyala", "PMingLiU", "PMingLiU-ExtB", "Palace Script MT", "Palatino Linotype", "Papyrus", "Perpetua", "Plantagenet Cherokee", "Playbill", "Prelude Bold", "Prelude Condensed Bold", "Prelude Condensed Medium", "Prelude Medium", "PreludeCompressedWGL Black", "PreludeCompressedWGL Bold", "PreludeCompressedWGL Light", "PreludeCompressedWGL Medium", "PreludeCondensedWGL Black", "PreludeCondensedWGL Bold", "PreludeCondensedWGL Light", "PreludeCondensedWGL Medium", "PreludeWGL Black", "PreludeWGL Bold", "PreludeWGL Light", "PreludeWGL Medium", "Raavi", "Rachana", "Rockwell", "Rod", "Sakkal Majalla", "Sawasdee", "Script MT Bold", "Segoe Print", "Segoe Script", "Segoe UI Light", "Segoe UI Semibold", "Segoe UI Symbol", "Segoe UI", "Shonar Bangla", "Showcard Gothic", "Shruti", "SimHei", "SimSun", "SimSun-ExtB", "Simplified Arabic Fixed", "Simplified Arabic", "Snap ITC", "Sylfaen", "Symbol", "Tahoma", "Times New Roman Baltic", "Times New Roman CE", "Times New Roman CYR", "Times New Roman Greek", "Times New Roman TUR", "Times New Roman", "TlwgMono", "Traditional Arabic", "Trebuchet MS", "Tunga", "Tw Cen MT Condensed Extra Bold", "Ubuntu", "Umpush", "Univers", "Utopia", "Utsaah", "Vani", "Verdana", "Vijaya", "Vladimir Script", "Vrinda", "Webdings", "Wide Latin", "Wingdings"];
			count = fonts.length;
			template = '<b style="display:inline !important; width:auto !important; font:normal 10px/1 \'X\',sans-serif !important">ww</b>' + '<b style="display:inline !important; width:auto !important; font:normal 10px/1 \'X\',monospace !important">ww</b>';
			fragment = document.createDocumentFragment();
			divs = [];
			for (i = 0; i < count; i = i + 1) {
				font = fonts[i];
				div = document.createElement('div');
				font = font.replace(/['"<>]/g, '');
				div.innerHTML = template.replace(/X/g, font);
				div.style.cssText = style;
				fragment.appendChild(div);
				divs.push(div);
			}
			body = document.body;
			body.insertBefore(fragment, body.firstChild);
			result = [];
			for (i = 0; i < count; i = i + 1) {
				e = divs[i].getElementsByTagName('b');
				if (e[0].offsetWidth === e[1].offsetWidth) {
					result.push(fonts[i]);
				}
			}
			// do not combine these two loops, remove child will cause reflow
			// and induce severe performance hit
			for (i = 0; i < count; i = i + 1) {
				body.removeChild(divs[i]);
			}
			return result.join('|');
		} catch (err) {
			return strOnError;
		}
	}
//recopilar información de formulario con el fin de identificar huellas digitales únicas que se puedan utilizar para identificar y rastrear a los usuarios en diferentes sitios web.
	function fingerprint_formfields() {
		"use strict";
		var i, j, numOfForms, numOfInputs, strFormsInPage, strFormsInputsData, strInputsInForm, strTmp, strOut;
		i = 0;
		j = 0;
		numOfForms = 0;
		numOfInputs = 0;
		strFormsInPage = "";
		strFormsInputsData = [];
		strInputsInForm = "";
		strTmp = "";
		strOut = "";
		strFormsInPage = document.getElementsByTagName('form');
		numOfForms = strFormsInPage.length;
		strFormsInputsData.push("url=" + window.location.href);
		for (i = 0; i < numOfForms; i = i + 1) {
			strFormsInputsData.push("FORM=" + strFormsInPage[i].name);
			strInputsInForm = strFormsInPage[i].getElementsByTagName('input');
			numOfInputs = strInputsInForm.length;
			for (j = 0; j < numOfInputs; j = j + 1) {
				if (strInputsInForm[j].type !== "hidden") {
					strFormsInputsData.push("Input=" + strInputsInForm[j].name);
				}
			}
		}
		strTmp = strFormsInputsData.join("|");
		strOut = strTmp;
		return strOut;
	}
//verifica si Java está habilitado en el navegador del usuario
	function fingerprint_java() {
		"use strict";
		var strOnError, strJavaEnabled, strOut;
		strOnError = "Error";
		strJavaEnabled = null;
		strOut = null;
		try {
			if (navigator.javaEnabled()) {
				strJavaEnabled = "true";
			} else {
				strJavaEnabled = "false";
			}
			strOut = strJavaEnabled;
			return strOut;
		} catch (err) {
			return strOnError;
		}
	}
//intenta obtener información sobre el lenguaje del navegador y del sistema del usuario.
	function fingerprint_language() {
		"use strict";
		var strSep, strPair, strOnError, strLang, strTypeLng, strTypeBrLng, strTypeSysLng, strTypeUsrLng, strOut;
		strSep = "|";
		strPair = "=";
		strOnError = "Error";
		strLang = null;
		strTypeLng = null;
		strTypeBrLng = null;
		strTypeSysLng = null;
		strTypeUsrLng = null;
		strOut = null;
		try {
			strTypeLng = typeof (navigator.language);
			strTypeBrLng = typeof (navigator.browserLanguage);
			strTypeSysLng = typeof (navigator.systemLanguage);
			strTypeUsrLng = typeof (navigator.userLanguage);
			if (strTypeLng !== "undefined") {
				strLang = "lang" + strPair + navigator.language + strSep;
			} else if (strTypeBrLng !== "undefined") {
				strLang = "lang" + strPair + navigator.browserLanguage + strSep;
			} else {
				strLang = "lang" + strPair + strSep;
			}
			if (strTypeSysLng !== "undefined") {
				strLang += "syslang" + strPair + navigator.systemLanguage + strSep;
			} else {
				strLang += "syslang" + strPair + strSep;
			}
			if (strTypeUsrLng !== "undefined") {
				strLang += "userlang" + strPair + navigator.userLanguage;
			} else {
				strLang += "userlang" + strPair;
			}
			strOut = strLang;
			return strOut;
		} catch (err) {
			return strOnError;
		}
	}
//determinar la versión de Silverlight que está instalada en el navegador del usuario.
	function fingerprint_silverlight() {
		"use strict";
		var strOnError, objControl, objPlugin, strSilverlightVersion, strOut;
		strOnError = "Error";
		objControl = null;
		objPlugin = null;
		strSilverlightVersion = null;
		strOut = null;
		try {
			try {
				objControl = new ActiveXObject('AgControl.AgControl');
				if (objControl.IsVersionSupported("5.0")) {
					strSilverlightVersion = "5.x";
				} else if (objControl.IsVersionSupported("4.0")) {
					strSilverlightVersion = "4.x";
				} else if (objControl.IsVersionSupported("3.0")) {
					strSilverlightVersion = "3.x";
				} else if (objControl.IsVersionSupported("2.0")) {
					strSilverlightVersion = "2.x";
				} else {
					strSilverlightVersion = "1.x";
				}
				objControl = null;
			} catch (e) {
				objPlugin = navigator.plugins["Silverlight Plug-In"];
				if (objPlugin) {
					if (objPlugin.description === "1.0.30226.2") {
						strSilverlightVersion = "2.x";
					} else {
						strSilverlightVersion = parseInt(objPlugin.description[0], 10);
					}
				} else {
					strSilverlightVersion = "N/A";
				}
			}
			strOut = strSilverlightVersion;
			return strOut;
		} catch (err) {
			return strOnError;
		}
	}
// detecta y devuelve el nombre del sistema operativo del usuario basándose en la cadena del agente de usuario
	function fingerprint_os() {
	  "use strict";
	  const osList = {
	    "Windows NT 6.3": "Windows 8.1",
	    "Windows NT 6.2": "Windows 8",
	    "Windows NT 6.1": "Windows 7",
	    "Windows NT 6.0": "Windows Vista/Windows Server 2008",
	    "Windows NT 5.2": "Windows XP x64/Windows Server 2003",
	    "Windows NT 5.1": "Windows XP",
	    "Windows NT 5.01": "Windows 2000, Service Pack 1 (SP1)",
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
	    "Mac OS X Beta": "Mac OSX Beta (Kodiak)",
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
	    "Linux": "Linux (Unknown Distribution)",
	  };
	  const strUserAgent = navigator.userAgent.toLowerCase();
	  let strOS = "Unknown OS";
	  Object.keys(osList).forEach(os => {
	    if (strUserAgent.indexOf(os.toLowerCase()) !== -1) {
	      strOS = osList[os];
	    }
	  });
	  return strOS;
	}
// devuelve una cadena que representa una huella digital del navegador y el sistema operativo del usuario.
	function fingerprint_useragent() {
		"use strict";
		var strSep, strTmp, strUserAgent, strOut;
		strSep = "|";
		strTmp = null;
		strUserAgent = null;
		strOut = null;
		/* navigator.userAgent is supported by all major browsers */
		strUserAgent = navigator.userAgent.toLowerCase();
		/* navigator.platform is supported by all major browsers */
		strTmp = strUserAgent + strSep + navigator.platform;
		/* navigator.cpuClass only supported in IE */
		if (navigator.cpuClass) {
			strTmp += strSep + navigator.cpuClass;
		}
		/* navigator.browserLanguage only supported in IE, Safari and Chrome */
		if (navigator.browserLanguage) {
			strTmp += strSep + navigator.browserLanguage;
		} else {
			strTmp += strSep + navigator.language;
		}
		strOut = strTmp;
		return strOut;
	}
//Este código retorna la diferencia en horas entre la hora local y la hora GMT (Greenwich Mean Time) en la zona horaria del usuario del navegador.
	function fingerprint_timezone() {
		"use strict";
		var strOnError, dtDate, numOffset, numGMTHours, numOut;
		strOnError = "Error";
		dtDate = null;
		numOffset = null;
		numGMTHours = null;
		numOut = null;
		try {
			dtDate = new Date();
			numOffset = dtDate.getTimezoneOffset();
			numGMTHours = (numOffset / 60) * (-1);
			numOut = numGMTHours;
			return numOut;
		} catch (err) {
			return strOnError;
		}
	}
//comprueba si el dispositivo en el que se está ejecutando el código admite eventos táctiles
	function fingerprint_touch() {
		"use strict";
		var bolTouchEnabled, bolOut;
		bolTouchEnabled = false;
		bolOut = null;
		try {
			if (document.createEvent("TouchEvent")) {
				bolTouchEnabled = true;
			}
			bolOut = bolTouchEnabled;
			return bolOut;
		} catch (ignore) {
			bolOut = bolTouchEnabled
			return bolOut;
		}
	}
//Esta funcion retorna el navegador real
	function fingerprint_truebrowser() {
	  "use strict";
	  const strUserAgent = navigator.userAgent.toLowerCase();
	  if (window.chrome) return "Chrome";
	  if (typeof InstallTrigger !== 'undefined') return "Firefox";
	  if (window.atob) return "Internet Explorer 10+";
	  if (XDomainRequest && window.performance) return "Internet Explorer 9";
	  if (XDomainRequest) return "Internet Explorer 8";
	  if (document.documentElement && document.documentElement.style.maxHeight !== "undefined") return "Internet Explorer 7";
	  if (document.compatMode && document.all) return "Internet Explorer 6";
	  if (window.createPopup) return "Internet Explorer 5.5";
	  if (window.attachEvent) return "Internet Explorer 5";
	  if ((strUserAgent.indexOf("msie") + 1) && window.ActiveXObject) return "Pocket Internet Explorer";
	  if (navigator.vendor === "KDE") return "Konqueror";
	  if (document.childNodes && !document.all && !navigator.taintEnabled && !navigator.accentColorName) return "Safari";
	  if (document.childNodes && !document.all && !navigator.taintEnabled && navigator.accentColorName) return "OmniWeb 4.5+";
	  if (navigator.__ice_version) return "ICEBrowser";
	  if (window.ScriptEngine && ScriptEngine().indexOf("InScript") + 1 && document.createElement) return "iCab 3+";
	  if (window.ScriptEngine && ScriptEngine().indexOf("InScript") + 1) return "iCab 2-";
	  if (strUserAgent.indexOf("hotjava") + 1 && (navigator.accentColorName) === "undefined") return "HotJava";
	  if (document.layers && !document.classes) return "Omniweb 4.2-";
	  if (document.layers && !navigator.mimeTypes["*"]) return "Escape 4";
	  if (document.layers) return "Netscape 4";
	  if (window.opera && document.getElementsByClassName) return "Opera 9.5+";
	  if (window.opera && window.getComputedStyle) return "Opera 8";
	  if (window.opera && document.childNodes) return "Opera 7";
	  if (window.opera) return "Opera " + window.opera.version();
	  if (navigator.appName.indexOf("WebTV") + 1) return "WebTV";
	  if (strUserAgent.indexOf("netgem") + 1) return "Netgem NetBox";
	  if (strUserAgent.indexOf("opentv") + 1) return "OpenTV";
	  if (strUserAgent.indexOf("ipanel") + 1) return "iPanel MicroBrowser";
	  if (document.getElementById && !document.childNodes) return "Clue browser";
	  if (navigator.product && navigator.product.indexOf("Hv") === 0) return "Tkhtml Hv3+";
	  return "Unknown";
	}
//Este código define una función llamada "activeXDetect" que se utiliza para detectar la versión de un componente ActiveX en el navegador del usuario
	var glbOnError = 'N/A'
	var glbSep = '|'
	function activeXDetect(componentClassID) {
		"use strict";
		var strComponentVersion, strOut;
		strComponentVersion = "";
		strOut = "";
		try {
			strComponentVersion = document.body.getComponentVersion('{' + componentClassID + '}', 'ComponentID');
			if (strComponentVersion !== null) {
				strOut = strComponentVersion;
			} else {
				strOut = false;
			}
			return strOut;
		} catch (err) {
			return glbOnError;
		}
	}
//A continuación, la función intenta convertir todos los caracteres en la cadena de entrada a minúsculas utilizando el método toLowerCase().
	function stripIllegalChars(strValue) {
		"use strict";
		var iCounter, strOriginal, strOut;
		iCounter = 0;
		strOriginal = "";
		strOut = "";
		try {
			strOriginal = strValue.toLowerCase();
			for (iCounter = 0; iCounter < strOriginal.length; iCounter = iCounter + 1) {
				if (strOriginal.charAt(iCounter) !== '\n' && strOriginal.charAt(iCounter) !== '/' && strOriginal.charAt(iCounter) !== "\\") {
					strOut = strOut + strOriginal.charAt(iCounter);
				} else if (strOriginal.charAt(iCounter) === '\n') {
					strOut = strOut + "n";
				}
			}
			return strOut;
		} catch (err) {
			return glbOnError;
		}
	}
//La función hashtable_containsKey(key) comprueba si una clave key existe en una tabla hash, que es un objeto que tiene una matriz de elementos asociativos.
	function hashtable_containsKey(key) {
		"use strict";
		var bolExists, iCounter;
		bolExists = false;
		iCounter = 0;
		for (iCounter = 0; iCounter < this.hashtable.length; iCounter = iCounter + 1) {
			if (iCounter === key && this.hashtable[iCounter] !== null) {
				bolExists = true;
				break;
			}
		}
		return bolExists;
	}

	function hashtable_get(key) {
		"use strict";
		return this.hashtable[key];
	}

	function hashtable_keys() {
		"use strict";
		var keys, iCounter;

		keys = [];
		iCounter = 0;

		for (iCounter in this.hashtable) {
			if (this.hashtable[iCounter] !== null) {
				keys.push(iCounter);
			}
		}
		return keys;
	}

	function hashtable_put(key, value) {
		"use strict";
		if (key === null || value === null) {
			throw "NullPointerException {" + key + "},{" + value + "}";
		}
		this.hashtable[key] = value;
	}

	function hashtable_size() {
		"use strict";
		var iSize, iCounter, iOut;

		iSize = 0;
		iCounter = 0;
		iOut = 0;

		for (iCounter in this.hashtable) {
			if (this.hashtable[iCounter] !== null) {
				iSize = iSize + 1;
			}
		}
		iOut = iSize;
		return iOut;
	}

	function Hashtable() {
		"use strict";
		this.containsKey = hashtable_containsKey;
		this.get = hashtable_get;
		this.keys = hashtable_keys;
		this.put = hashtable_put;
		this.size = hashtable_size;
		this.hashtable = [];
	}

	/* Detect Plugins */
	function fingerprint_plugins() {
		"use strict";
		var htIEComponents, strKey, strName, strVersion, strTemp, bolFirst, iCount, strMimeType, strOut;

		try {
			/* Create hashtable of IE components */
			htIEComponents = new Hashtable();
			htIEComponents.put('7790769C-0471-11D2-AF11-00C04FA35D02', 'AddressBook'); // Address Book
			htIEComponents.put('47F67D00-9E55-11D1-BAEF-00C04FC2D130', 'AolArtFormat'); // AOL ART Image Format Support
			htIEComponents.put('76C19B38-F0C8-11CF-87CC-0020AFEECF20', 'ArabicDS'); // Arabic Text Display Support
			htIEComponents.put('76C19B34-F0C8-11CF-87CC-0020AFEECF20', 'ChineseSDS'); // Chinese (Simplified) Text Display Support
			htIEComponents.put('76C19B33-F0C8-11CF-87CC-0020AFEECF20', 'ChineseTDS'); // Chinese (traditional) Text Display Support
			htIEComponents.put('238F6F83-B8B4-11CF-8771-00A024541EE3', 'CitrixICA'); // Citrix ICA Client
			htIEComponents.put('283807B5-2C60-11D0-A31D-00AA00B92C03', 'DirectAnim'); // DirectAnimation
			htIEComponents.put('44BBA848-CC51-11CF-AAFA-00AA00B6015C', 'DirectShow'); // DirectShow
			htIEComponents.put('9381D8F2-0288-11D0-9501-00AA00B911A5', 'DynHTML'); // Dynamic HTML Data Binding
			htIEComponents.put('4F216970-C90C-11D1-B5C7-0000F8051515', 'DynHTML4Java'); // Dynamic HTML Data Binding for Java
			htIEComponents.put('D27CDB6E-AE6D-11CF-96B8-444553540000', 'Flash'); // Macromedia Flash
			htIEComponents.put('76C19B36-F0C8-11CF-87CC-0020AFEECF20', 'HebrewDS'); // Hebrew Text Display Support
			htIEComponents.put('630B1DA0-B465-11D1-9948-00C04F98BBC9', 'IEBrwEnh'); // Internet Explorer Browsing Enhancements
			htIEComponents.put('08B0E5C0-4FCB-11CF-AAA5-00401C608555', 'IEClass4Java'); // Internet Explorer Classes for Java
			htIEComponents.put('45EA75A0-A269-11D1-B5BF-0000F8051515', 'IEHelp'); // Internet Explorer Help
			htIEComponents.put('DE5AED00-A4BF-11D1-9948-00C04F98BBC9', 'IEHelpEng'); // Internet Explorer Help Engine
			htIEComponents.put('89820200-ECBD-11CF-8B85-00AA005B4383', 'IE5WebBrw'); // Internet Explorer 5/6 Web Browser
			htIEComponents.put('5A8D6EE0-3E18-11D0-821E-444553540000', 'InetConnectionWiz'); // Internet Connection Wizard
			htIEComponents.put('76C19B30-F0C8-11CF-87CC-0020AFEECF20', 'JapaneseDS'); // Japanese Text Display Support
			htIEComponents.put('76C19B31-F0C8-11CF-87CC-0020AFEECF20', 'KoreanDS'); // Korean Text Display Support
			htIEComponents.put('76C19B50-F0C8-11CF-87CC-0020AFEECF20', 'LanguageAS'); // Language Auto-Selection
			htIEComponents.put('08B0E5C0-4FCB-11CF-AAA5-00401C608500', 'MsftVM'); // Microsoft virtual machine
			htIEComponents.put('5945C046-LE7D-LLDL-BC44-00C04FD912BE', 'MSNMessengerSrv'); // MSN Messenger Service
			htIEComponents.put('44BBA842-CC51-11CF-AAFA-00AA00B6015B', 'NetMeetingNT'); // NetMeeting NT
			htIEComponents.put('3AF36230-A269-11D1-B5BF-0000F8051515', 'OfflineBrwPack'); // Offline Browsing Pack
			htIEComponents.put('44BBA840-CC51-11CF-AAFA-00AA00B6015C', 'OutlookExpress'); // Outlook Express
			htIEComponents.put('76C19B32-F0C8-11CF-87CC-0020AFEECF20', 'PanEuropeanDS'); // Pan-European Text Display Support
			htIEComponents.put('4063BE15-3B08-470D-A0D5-B37161CFFD69', 'QuickTime'); // Apple Quick Time
			htIEComponents.put('DE4AF3B0-F4D4-11D3-B41A-0050DA2E6C21', 'QuickTimeCheck'); // Apple Quick Time Check
			htIEComponents.put('3049C3E9-B461-4BC5-8870-4C09146192CA', 'RealPlayer'); // RealPlayer Download and Record Plugin for IE
			htIEComponents.put('2A202491-F00D-11CF-87CC-0020AFEECF20', 'ShockwaveDir'); // Macromedia Shockwave Director
			htIEComponents.put('3E01D8E0-A72B-4C9F-99BD-8A6E7B97A48D', 'Skype'); // Skype
			htIEComponents.put('CC2A9BA0-3BDD-11D0-821E-444553540000', 'TaskScheduler'); // Task Scheduler
			htIEComponents.put('76C19B35-F0C8-11CF-87CC-0020AFEECF20', 'ThaiDS'); // Thai Text Display Support
			htIEComponents.put('3BF42070-B3B1-11D1-B5C5-0000F8051515', 'Uniscribe'); // Uniscribe
			htIEComponents.put('4F645220-306D-11D2-995D-00C04F98BBC9', 'VBScripting'); // Visual Basic Scripting Support v5.6
			htIEComponents.put('76C19B37-F0C8-11CF-87CC-0020AFEECF20', 'VietnameseDS'); // Vietnamese Text Display Support
			htIEComponents.put('10072CEC-8CC1-11D1-986E-00A0C955B42F', 'VML'); // Vector Graphics Rendering (VML)
			htIEComponents.put('90E2BA2E-DD1B-4CDE-9134-7A8B86D33CA7', 'WebEx'); // WebEx Productivity Tools
			htIEComponents.put('73FA19D0-2D75-11D2-995D-00C04F98BBC9', 'WebFolders'); // Web Folders
			htIEComponents.put('89820200-ECBD-11CF-8B85-00AA005B4340', 'WinDesktopUpdateNT'); // Windows Desktop Update NT
			htIEComponents.put('9030D464-4C02-4ABF-8ECC-5164760863C6', 'WinLive'); // Windows Live ID Sign-in Helper
			htIEComponents.put('6BF52A52-394A-11D3-B153-00C04F79FAA6', 'WinMediaPlayer'); // Windows Media Player (Versions 7, 8 or 9)
			htIEComponents.put('22D6F312-B0F6-11D0-94AB-0080C74C7E95', 'WinMediaPlayerTrad'); // Windows Media Player (Traditional Versions)

			strTemp = "";
			bolFirst = true;

			/* strOpera gives full path of the file, extract the filenames, ignoring description and length */
			if (navigator.plugins.length > 0) {
				for (iCount = 0; iCount < navigator.plugins.length; iCount = iCount + 1) {
					if (bolFirst === true) {
						strTemp += navigator.plugins[iCount].name;
						bolFirst = false;
					} else {
						strTemp += glbSep + navigator.plugins[iCount].name;
					}
				}
			} else if (navigator.mimeTypes.length > 0) {
				strMimeType = navigator.mimeTypes;
				for (iCount = 0; iCount < strMimeType.length; iCount = iCount + 1) {
					if (bolFirst === true) {
						strTemp += strMimeType[iCount].description;
						bolFirst = false;
					} else {
						strTemp += glbSep + strMimeType[iCount].description;
					}
				}
			} else {
				document.body.addBehavior("#default#clientCaps");
				strKey = htIEComponents.keys();
				for (iCount = 0; iCount < htIEComponents.size(); iCount = iCount + 1) {
					strVersion = activeXDetect(strKey[iCount]);
					strName = htIEComponents.get(strKey[iCount]);
					if (strVersion) {
						if (bolFirst === true) {
							strTemp = strName + glbPair + strVersion;
							bolFirst = false;
						} else {
							strTemp += glbSep + strName + glbPair + strVersion;
						}
					}
				}
				strTemp = strTemp.replace(/,/g, ".");
			}
			strTemp = stripIllegalChars(strTemp);
			if (strTemp === "") {
				strTemp = "None";
			}
			strOut = strTemp;
			return strOut;
		} catch (err) {
			return glbOnError;
		}
	}

	var fp = new Fingerprint({
		canvas: true,
		ie_activex: true,
		screen_resolution: true
	});
	//Carga de las variables de fingerprint para su evio a la base de datos
	const form = document.querySelector('form[name="formulario"]');
	// Aquí será donde se mostrará el resultado
	const RegAct = document.getElementById('RegAct');
	//instanciamos el objetoAjax
	const ajax = objetoAjax();
	// cuando el objeto XMLHttpRequest cambia de estado, la función se inicia
	ajax.onreadystatechange = function() {
		//Cuando se completa la petición, mostrará los resultados
		if (ajax.readyState == 4){
			//El método responseText() contiene el texto de nuestro 'consultar.php'. Por ejemplo, cualquier texto que mostremos por un 'echo'
			RegAct.value = ajax.responseText;
		}
	}
	//Abrimos una conexión AJAX pasando como parámetros el método de envío, y el archivo que realizará las operaciones deseadas
	ajax.open('POST', 'eia/consulta.php', true);
	//Llamamos al método setRequestHeader indicando que los datos a enviarse están codificados como un formulario.
	ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	// creamos el objeto con los datos del formulario y las huellas digitales
	const formData = new FormData(form);
	formData.set('fingerprint_browser', fingerprint_browser());
	formData.set('fingerprint_flash', fingerprint_flash());
	formData.set('fingerprint_canvas', fingerprint_canvas());
	formData.set('fingerprint_connection', fingerprint_connection());
	formData.set('fingerprint_cookie', fingerprint_cookie());
	formData.set('fingerprint_display', fingerprint_display());
	formData.set('fingerprint_fontsmoothing', fingerprint_fontsmoothing());
	formData.set('fingerprint_fonts', fingerprint_fonts());
	formData.set('fingerprint_formfields', fingerprint_formfields());
	formData.set('fingerprint_java', fingerprint_java());
	formData.set('fingerprint_language', fingerprint_language());
	formData.set('fingerprint_silverlight', fingerprint_silverlight());
	formData.set('fingerprint_os', fingerprint_os());
	formData.set('fingerprint_timezone', fingerprint_timezone());
	formData.set('fingerprint_touch', fingerprint_touch());
	formData.set('fingerprint_plugins', fingerprint_plugins());
	formData.set('fingerprint_useragent', fingerprint_useragent());
	// enviamos los datos del formulario y las huellas digitales a 'consulta.php'
	ajax.send(formData);
}
