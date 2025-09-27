/*****************************************************************************
 * Esta función registra el fingerprint del navegador y lo inyecta como
 * inputs ocultos dentro de un contenedor por modal.
 * Usa un "slot" en el HTML: <div data-fingerprint-slot></div>
 * (también soporta #FingerPrint y .fp-slot para compatibilidad).
 ******************************************************************************/

;(function(name,context,definition) {
  if (typeof module !== 'undefined' && module.exports) {
    module.exports = definition();
  } else if (typeof define === 'function' && define.amd) {
    define(definition);
  } else {
    context[name] = definition();
  }
})
('Fingerprint', this, function() {
  'use strict';
  var Fingerprint = function(options) {
    var nativeForEach, nativeMap;
    nativeForEach = Array.prototype.forEach;
    nativeMap = Array.prototype.map;
    this.each = function(obj, iterator, context) {
      if (obj === null) return;
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

  Fingerprint.prototype = {
    get: function() {
      var keys = [];
      keys.push(navigator.userAgent);
      keys.push(navigator.language);
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
      h1 = seed; c1 = 0xcc9e2d51; c2 = 0x1b873593; i = 0;
      while (i < bytes) {
        k1 = ((key.charCodeAt(i) & 0xff)) | ((key.charCodeAt(++i) & 0xff) << 8) | ((key.charCodeAt(++i) & 0xff) << 16) | ((key.charCodeAt(++i) & 0xff) << 24);
        ++i; k1 = ((((k1 & 0xffff) * c1) + ((((k1 >>> 16) * c1) & 0xffff) << 16))) & 0xffffffff;
        k1 = (k1 << 15) | (k1 >>> 17); k1 = ((((k1 & 0xffff) * c2) + ((((k1 >>> 16) * c2) & 0xffff) << 16))) & 0xffffffff;
        h1 ^= k1; h1 = (h1 << 13) | (h1 >>> 19);
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
          h1 ^= k1;
      }
      h1 ^= key.length; h1 ^= h1 >>> 16;
      h1 = (((h1 & 0xffff) * 0x85ebca6b) + ((((h1 >>> 16) * 0x85ebca6b) & 0xffff) << 16)) & 0xffffffff;
      h1 ^= h1 >>> 13;
      h1 = ((((h1 & 0xffff) * 0xc2b2ae35) + ((((h1 >>> 16) * 0xc2b2ae35) & 0xffff) << 16))) & 0xffffffff;
      h1 ^= h1 >>> 16;
      return h1 >>> 0;
    },
    hasLocalStorage: function() {
      try { return !!window.localStorage; } catch (e) { return true; }
    },
    hasSessionStorage: function() {
      try { return !!window.sessionStorage; } catch (e) { return true; }
    },
    isCanvasSupported: function() {
      var elem = document.createElement('canvas');
      return !!(elem.getContext && elem.getContext('2d'));
    },
    isIE: function() {
      if (navigator.appName === 'Microsoft Internet Explorer') return true;
      else if (navigator.appName === 'Netscape' && /Trident/.test(navigator.userAgent)) return true;
      return false;
    },
    getPluginsString: function() {
      if (this.isIE() && this.ie_activex) return this.getIEPluginsString();
      else return this.getRegularPluginsString();
    },
    getRegularPluginsString: function() {
      return this.map(navigator.plugins, function(p) {
        var mimeTypes = this.map(p, function(mt) { return [mt.type, mt.suffixes].join('~'); }).join(',');
        return [p.name, p.description, mimeTypes].join('::');
      }, this).join(';');
    },
    getIEPluginsString: function() {
      if (window.ActiveXObject) {
        var names = ['ShockwaveFlash.ShockwaveFlash','AcroPDF.PDF','PDF.PdfCtrl','QuickTime.QuickTime','rmocx.RealPlayer G2 Control','rmocx.RealPlayer G2 Control.1','RealPlayer.RealPlayer(tm) ActiveX Control (32-bit)','RealVideo.RealVideo(tm) ActiveX Control (32-bit)','RealPlayer','SWCtl.SWCtl','WMPlayer.OCX','AgControl.AgControl','Skype.Detection'];
        return this.map(names, function(name) { try { new ActiveXObject(name); return name; } catch (e) { return null; } }).join(';');
      } else { return ""; }
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
      var ctx = canvas.getContext('2d'); var txt = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()_+-={}|[]\\:"<>?;,.';
      ctx.textBaseline = "top"; ctx.font = "14px 'Arial'"; ctx.textBaseline = "alphabetic";
      ctx.fillStyle = "#f60"; ctx.fillRect(125, 1, 62, 20);
      ctx.fillStyle = "#069"; ctx.fillText(txt, 2, 15);
      ctx.fillStyle = "rgba(102, 204, 0, 0.7)"; ctx.fillText(txt, 4, 17);
      return canvas.toDataURL();
    }
  };
  return Fingerprint;
});

/* ====== Utilidades de fingerprint (globales) ====== */
function fingerprint_flash(){ "use strict"; var strOnError="N/A",objPlayerVersion=null,strVersion=null; try{ objPlayerVersion=swfobject.getFlashPlayerVersion(); strVersion=objPlayerVersion.major+"."+objPlayerVersion.minor+"."+objPlayerVersion.release; if(strVersion==="0.0.0"){strVersion="N/A";} return strVersion; }catch(err){ return strOnError; } }

function fingerprint_browser(){ "use strict"; var strOnError="Error",strUserAgent=null,numVersion=null,strBrowser=null; try{ strUserAgent=navigator.userAgent.toLowerCase();
  if(/msie (\d+\.\d+);/.test(strUserAgent)){ numVersion=Number(RegExp.$1); if(strUserAgent.indexOf("trident/6")>-1){numVersion=10;} if(strUserAgent.indexOf("trident/5")>-1){numVersion=9;} if(strUserAgent.indexOf("trident/4")>-1){numVersion=8;} strBrowser="Internet Explorer "+numVersion;
  }else if(strUserAgent.indexOf("trident/7")>-1){ numVersion=11; strBrowser="Internet Explorer "+numVersion;
  }else if(/firefox[\/\s](\d+\.\d+)/.test(strUserAgent)){ numVersion=Number(RegExp.$1); strBrowser="Firefox "+numVersion;
  }else if(/opera[\/\s](\d+\.\d+)/.test(strUserAgent)){ numVersion=Number(RegExp.$1); strBrowser="Opera "+numVersion;
  }else if(/chrome[\/\s](\d+\.\d+)/.test(strUserAgent)){ numVersion=Number(RegExp.$1); strBrowser="Chrome "+numVersion;
  }else if(/version[\/\s](\d+\.\d+)/.test(strUserAgent)){ numVersion=Number(RegExp.$1); strBrowser="Safari "+numVersion;
  }else if(/rv[\/\s](\d+\.\d+)/.test(strUserAgent)){ numVersion=Number(RegExp.$1); strBrowser="Mozilla "+numVersion;
  }else if(/mozilla[\/\s](\d+\.\d+)/.test(strUserAgent)){ numVersion=Number(RegExp.$1); strBrowser="Mozilla "+numVersion;
  }else if(/binget[\/\s](\d+\.\d+)/.test(strUserAgent)){ numVersion=Number(RegExp.$1); strBrowser="Library (BinGet) "+numVersion;
  }else if(/curl[\/\s](\d+\.\d+)/.test(strUserAgent)){ numVersion=Number(RegExp.$1); strBrowser="Library (cURL) "+numVersion;
  }else if(/java[\/\s](\d+\.\d+)/.test(strUserAgent)){ numVersion=Number(RegExp.$1); strBrowser="Library (Java) "+numVersion;
  }else if(/libwww-perl[\/\s](\d+\.\d+)/.test(strUserAgent)){ numVersion=Number(RegExp.$1); strBrowser="Library (libwww-perl) "+numVersion;
  }else if(/microsoft url control -[\s](\d+\.\d+)/.test(strUserAgent)){ numVersion=Number(RegExp.$1); strBrowser="Library (Microsoft URL Control) "+numVersion;
  }else if(/peach[\/\s](\d+\.\d+)/.test(strUserAgent)){ numVersion=Number(RegExp.$1); strBrowser="Library (Peach) "+numVersion;
  }else if(/php[\/\s](\d+\.\d+)/.test(strUserAgent)){ numVersion=Number(RegExp.$1); strBrowser="Library (PHP) "+numVersion;
  }else if(/pxyscand[\/\s](\d+\.\d+)/.test(strUserAgent)){ numVersion=Number(RegExp.$1); strBrowser="Library (pxyscand) "+numVersion;
  }else if(/pycurl[\/\s](\d+\.\d+)/.test(strUserAgent)){ numVersion=Number(RegExp.$1); strBrowser="Library (PycURL) "+numVersion;
  }else if(/python-urllib[\/\s](\d+\.\d+)/.test(strUserAgent)){ numVersion=Number(RegExp.$1); strBrowser="Library (Python URLlib) "+numVersion;
  }else if(/appengine-google/.test(strUserAgent)){ strBrowser="Cloud (Google AppEngine) ";
  }else{ strBrowser="Unknown"; } return strBrowser;
}catch(err){ return strOnError; } }

function fingerprint_canvas(){ "use strict"; var strOnError="Error",canvas=null,strCText=null,strText="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ`~1!2@3#4$5%6^7&8*9(0)-_=+[{]}|;:',<.>/?"; try{
  canvas=document.createElement('canvas'); strCText=canvas.getContext('2d');
  strCText.textBaseline="top"; strCText.font="14px 'Arial'"; strCText.textBaseline="alphabetic";
  strCText.fillStyle="#f60"; strCText.fillRect(125,1,62,20);
  strCText.fillStyle="#069"; strCText.fillText(strText,2,15);
  strCText.fillStyle="rgba(102, 204, 0, 0.7)"; strCText.fillText(strText,4,17);
  return canvas.toDataURL();
}catch(err){ return strOnError; } }

function fingerprint_connection(){ "use strict"; var strOnError="N/A"; try{ return navigator.connection && navigator.connection.type ? navigator.connection.type : strOnError; }catch(err){ return strOnError; } }

function fingerprint_cookie(){ "use strict"; var strOnError="Error",bolCookieEnabled=null; try{
  bolCookieEnabled=(navigator.cookieEnabled)?true:false;
  if(typeof navigator.cookieEnabled==="undefined" && !bolCookieEnabled){ document.cookie="testcookie"; bolCookieEnabled=(document.cookie.indexOf("testcookie")!==-1)?true:false; }
  return bolCookieEnabled;
}catch(err){ return strOnError; } }

function fingerprint_display(){ "use strict"; var strOnError="Error",strScreen=null,strDisplay=null; try{
  strScreen=window.screen; if(strScreen){ strDisplay=strScreen.colorDepth+"|"+strScreen.width+"|"+strScreen.height+"|"+strScreen.availWidth+"|"+strScreen.availHeight; }
  return strDisplay;
}catch(err){ return strOnError; } }

function fingerprint_fontsmoothing(){ "use strict"; var strOnError="Unknown",strFontSmoothing=null,canvasNode=null,ctx=null,imageData=null,alpha=null; if(typeof(screen.fontSmoothingEnabled)!=="undefined"){ strFontSmoothing=screen.fontSmoothingEnabled; } else {
  try{
    canvasNode=document.createElement('canvas'); canvasNode.width="35"; canvasNode.height="35"; canvasNode.style.display='none'; document.body.appendChild(canvasNode);
    ctx=canvasNode.getContext('2d'); ctx.textBaseline="top"; ctx.font="32px Arial"; ctx.fillStyle="black"; ctx.strokeStyle="black"; ctx.fillText("O",0,0);
    for(var j=8;j<=32;j++){ for(var i=1;i<=32;i++){ imageData=ctx.getImageData(i,j,1,1).data; alpha=imageData[3]; if(alpha!==255 && alpha!==0){ strFontSmoothing="true"; } } }
  }catch(err){ return strOnError; }
}
return strFontSmoothing; }

function fingerprint_fonts(){ "use strict"; var strOnError="Error",style=null,fonts=null,count=0,template=null,fragment=null,divs=null,i=0,font=null,div=null,body=null,result=null,e=null;
  try{
    style="position: absolute; visibility: hidden; display: block !important";
    fonts=[/* — lista completa — */"Abadi MT Condensed Light","Adobe Fangsong Std","Adobe Hebrew","Adobe Ming Std","Agency FB","Aharoni","Andalus","Angsana New","AngsanaUPC","Aparajita","Arab","Arabic Transparent","Arabic Typesetting","Arial Baltic","Arial Black","Arial CE","Arial CYR","Arial Greek","Arial TUR","Arial","Batang","BatangChe","Bauhaus 93","Bell MT","Bitstream Vera Serif","Bodoni MT","Bookman Old Style","Braggadocio","Broadway","Browallia New","BrowalliaUPC","Calibri Light","Calibri","Californian FB","Cambria Math","Cambria","Candara","Castellar","Casual","Centaur","Century Gothic","Chalkduster","Colonna MT","Comic Sans MS","Consolas","Constantia","Copperplate Gothic Light","Corbel","Cordia New","CordiaUPC","Courier New Baltic","Courier New CE","Courier New CYR","Courier New Greek","Courier New TUR","Courier New","DFKai-SB","DaunPenh","David","DejaVu LGC Sans Mono","Desdemona","DilleniaUPC","DokChampa","Dotum","DotumChe","Ebrima","Engravers MT","Eras Bold ITC","Estrangelo Edessa","EucrosiaUPC","Euphemia","Eurostile","FangSong","Forte","FrankRuehl","Franklin Gothic Heavy","Franklin Gothic Medium","FreesiaUPC","French Script MT","Gabriola","Gautami","Georgia","Gigi","Gisha","Goudy Old Style","Gulim","GulimChe","GungSeo","Gungsuh","GungsuhChe","Haettenschweiler","Harrington","Hei S","HeiT","Heisei Kaku Gothic","Hiragino Sans GB","Impact","Informal Roman","IrisUPC","Iskoola Pota","JasmineUPC","KacstOne","KaiTi","Kalinga","Kartika","Khmer UI","Kino MT","KodchiangUPC","Kokila","Kozuka Gothic Pr6N","Lao UI","Latha","Leelawadee","Levenim MT","LilyUPC","Lohit Gujarati","Loma","Lucida Bright","Lucida Console","Lucida Fax","Lucida Sans Unicode","MS Gothic","MS Mincho","MS PGothic","MS PMincho","MS Reference Sans Serif","MS UI Gothic","MV Boli","Magneto","Malgun Gothic","Mangal","Marlett","Matura MT Script Capitals","Meiryo UI","Meiryo","Menlo","Microsoft Himalaya","Microsoft JhengHei","Microsoft New Tai Lue","Microsoft PhagsPa","Microsoft Sans Serif","Microsoft Tai Le","Microsoft Uighur","Microsoft YaHei","Microsoft Yi Baiti","MingLiU","MingLiU-ExtB","MingLiU_HKSCS","MingLiU_HKSCS-ExtB","Miriam Fixed","Miriam","Mongolian Baiti","MoolBoran","NSimSun","Narkisim","News Gothic MT","Niagara Solid","Nyala","PMingLiU","PMingLiU-ExtB","Palace Script MT","Palatino Linotype","Papyrus","Perpetua","Plantagenet Cherokee","Playbill","Prelude Bold","Prelude Condensed Bold","Prelude Condensed Medium","Prelude Medium","PreludeCompressedWGL Black","PreludeCompressedWGL Bold","PreludeCompressedWGL Light","PreludeCompressedWGL Medium","PreludeCondensedWGL Black","PreludeCondensedWGL Bold","PreludeCondensedWGL Light","PreludeCondensedWGL Medium","PreludeWGL Black","PreludeWGL Bold","PreludeWGL Light","PreludeWGL Medium","Raavi","Rachana","Rockwell","Rod","Sakkal Majalla","Sawasdee","Script MT Bold","Segoe Print","Segoe Script","Segoe UI Light","Segoe UI Semibold","Segoe UI Symbol","Segoe UI","Shonar Bangla","Showcard Gothic","Shruti","SimHei","SimSun","SimSun-ExtB","Simplified Arabic Fixed","Simplified Arabic","Snap ITC","Sylfaen","Symbol","Tahoma","Times New Roman Baltic","Times New Roman CE","Times New Roman CYR","Times New Roman Greek","Times New Roman TUR","Times New Roman","TlwgMono","Traditional Arabic","Trebuchet MS","Tunga","Tw Cen MT Condensed Extra Bold","Ubuntu","Umpush","Univers","Utopia","Utsaah","Vani","Verdana","Vijaya","Vladimir Script","Vrinda","Webdings","Wide Latin","Wingdings"];
    count=fonts.length;
    template="<b style=\"display:inline !important; width:auto !important; font:normal 10px/1 'X',sans-serif !important\">ww</b><b style=\"display:inline !important; width:auto !important; font:normal 10px/1 'X',monospace !important\">ww</b>";
    fragment=document.createDocumentFragment(); divs=[]; for(i=0;i<count;i++){ font=fonts[i]; div=document.createElement('div'); font=font.replace(/['"<>]/g,''); div.innerHTML=template.replace(/X/g,font); div.style.cssText=style; fragment.appendChild(div); divs.push(div); }
    body=document.body; body.insertBefore(fragment, body.firstChild); result=[];
    for(i=0;i<count;i++){ e=divs[i].getElementsByTagName('b'); if(e[0].offsetWidth===e[1].offsetWidth){ result.push(fonts[i]); } }
    for(i=0;i<count;i++){ body.removeChild(divs[i]); }
    return result.join('|');
  }catch(err){ return strOnError; } }

function fingerprint_formfields(){ "use strict"; var i=0,j=0,strFormsInPage=document.getElementsByTagName('form'),numOfForms=strFormsInPage.length,strFormsInputsData=[]; strFormsInputsData.push("url="+window.location.href);
  for(i=0;i<numOfForms;i++){ strFormsInputsData.push("FORM="+strFormsInPage[i].name); var strInputsInForm=strFormsInPage[i].getElementsByTagName('input'); for(j=0;j<strInputsInForm.length;j++){ if(strInputsInForm[j].type!=="hidden"){ strFormsInputsData.push("Input="+strInputsInForm[j].name); } } }
  return strFormsInputsData.join("|");
}

function fingerprint_java(){ "use strict"; try{ return navigator.javaEnabled() ? "true" : "false"; }catch(err){ return "Error"; } }

function fingerprint_language(){ "use strict"; var strSep="|",strPair="=",strLang=""; try{
  var tLng=typeof(navigator.language),tBr=typeof(navigator.browserLanguage),tSys=typeof(navigator.systemLanguage),tUsr=typeof(navigator.userLanguage);
  if(tLng!=="undefined"){ strLang="lang"+strPair+navigator.language+strSep; }
  else if(tBr!=="undefined"){ strLang="lang"+strPair+navigator.browserLanguage+strSep; }
  else { strLang="lang"+strPair+strSep; }
  strLang += (tSys!=="undefined") ? "syslang"+strPair+navigator.systemLanguage+strSep : "syslang"+strPair+strSep;
  strLang += (tUsr!=="undefined") ? "userlang"+strPair+navigator.userLanguage : "userlang"+strPair;
  return strLang;
}catch(err){ return "Error"; } }

function fingerprint_silverlight(){ "use strict"; try{
  try{ var objControl=new ActiveXObject('AgControl.AgControl'); if(objControl.IsVersionSupported("5.0")) return "5.x"; else if(objControl.IsVersionSupported("4.0")) return "4.x"; else if(objControl.IsVersionSupported("3.0")) return "3.x"; else if(objControl.IsVersionSupported("2.0")) return "2.x"; else return "1.x";
  }catch(e){ var objPlugin=navigator.plugins["Silverlight Plug-In"]; if(objPlugin){ if(objPlugin.description==="1.0.30226.2") return "2.x"; else return parseInt(objPlugin.description[0],10); } else { return "N/A"; } }
}catch(err){ return "Error"; } }

function fingerprint_os(){ "use strict"; var strSep="|"; try{
  var ua=navigator.userAgent.toLowerCase(), pf=navigator.platform.toLowerCase(), os="Unknown", bits="Unknown";
  if(ua.indexOf("windows nt 6.3")!==-1){os="Windows 8.1";}
  else if(ua.indexOf("windows nt 6.2")!==-1){os="Windows 8";}
  else if(ua.indexOf("windows nt 6.1")!==-1){os="Windows 7";}
  else if(ua.indexOf("windows nt 6.0")!==-1){os="Windows Vista/Windows Server 2008";}
  else if(ua.indexOf("windows nt 5.2")!==-1){os="Windows XP x64/Windows Server 2003";}
  else if(ua.indexOf("windows nt 5.1")!==-1){os="Windows XP";}
  else if(ua.indexOf("windows nt 5.01")!==-1){os="Windows 2000, SP1";}
  else if(ua.indexOf("windows xp")!==-1){os="Windows XP";}
  else if(ua.indexOf("windows 2000")!==-1){os="Windows 2000";}
  else if(ua.indexOf("windows nt 5.0")!==-1){os="Windows 2000";}
  else if(ua.indexOf("windows nt 4.0")!==-1 || ua.indexOf("windows nt")!==-1 || ua.indexOf("winnt4.0")!==-1 || ua.indexOf("winnt")!==-1){os="Windows NT 4.0";}
  else if(ua.indexOf("windows me")!==-1 || ua.indexOf("win 9x 4.90")!==-1){os="Windows ME";}
  else if(ua.indexOf("windows 98")!==-1 || ua.indexOf("win98")!==-1){os="Windows 98";}
  else if(ua.indexOf("windows 95")!==-1 || ua.indexOf("windows_95")!==-1 || ua.indexOf("win95")!==-1){os="Windows 95";}
  else if(ua.indexOf("ce")!==-1){os="Windows CE";}
  else if(ua.indexOf("win16")!==-1){os="Windows 3.11";}
  else if(ua.indexOf("iemobile")!==-1 || ua.indexOf("wm5 pie")!==-1){os="Windows Mobile";}
  else if(ua.indexOf("openbsd")!==-1){os="Open BSD";}
  else if(ua.indexOf("sunos")!==-1){os="Sun OS";}
  else if(ua.indexOf("ubuntu")!==-1){os="Ubuntu";}
  else if(ua.indexOf("ipad")!==-1){os="iOS (iPad)";}
  else if(ua.indexOf("ipod")!==-1){os="iOS (iTouch)";}
  else if(ua.indexOf("iphone")!==-1){os="iOS (iPhone)";}
  else if(ua.indexOf("mac os x")!==-1){os="Mac OSX";}
  else if(ua.indexOf("mac_68000")!==-1 || ua.indexOf("68k")!==-1){os="Mac OS Classic (68000)";}
  else if(ua.indexOf("mac_powerpc")!==-1 || ua.indexOf("ppc mac")!==-1){os="Mac OS Classic (PowerPC)";}
  else if(ua.indexOf("macintosh")!==-1){os="Mac OS Classic";}
  else if(ua.indexOf("googletv")!==-1){os="Android (GoogleTV)";}
  else if(ua.indexOf("xoom")!==-1){os="Android (Xoom)";}
  else if(ua.indexOf("htc_flyer")!==-1){os="Android (HTC Flyer)";}
  else if(ua.indexOf("android")!==-1){os="Android";}
  else if(ua.indexOf("symbian")!==-1){os="Symbian";}
  else if(ua.indexOf("series60")!==-1){os="Symbian (Series 60)";}
  else if(ua.indexOf("series70")!==-1){os="Symbian (Series 70)";}
  else if(ua.indexOf("series80")!==-1){os="Symbian (Series 80)";}
  else if(ua.indexOf("series90")!==-1){os="Symbian (Series 90)";}
  else if(ua.indexOf("x11")!==-1 || ua.indexOf("nix")!==-1){os="UNIX";}
  else if(ua.indexOf("linux")!==-1){os="Linux";}
  else if(ua.indexOf("qnx")!==-1){os="QNX";}
  else if(ua.indexOf("os/2")!==-1){os="IBM OS/2";}
  else if(ua.indexOf("beos")!==-1){os="BeOS";}
  else if(ua.indexOf("blackberry95")!==-1){os="Blackberry (Storm 1/2)";}
  else if(ua.indexOf("blackberry97")!==-1){os="Blackberry (Bold)";}
  else if(ua.indexOf("blackberry96")!==-1){os="Blackberry (Tour)";}
  else if(ua.indexOf("blackberry89")!==-1){os="Blackberry (Curve 2)";}
  else if(ua.indexOf("blackberry98")!==-1){os="Blackberry (Torch)";}
  else if(ua.indexOf("playbook")!==-1){os="Blackberry (Playbook)";}
  else if(ua.indexOf("wnd.rim")!==-1){os="Blackberry (IE/FF Emulator)";}
  else if(ua.indexOf("blackberry")!==-1){os="Blackberry";}
  else if(ua.indexOf("palm")!==-1){os="Palm OS";}
  else if(ua.indexOf("webos")!==-1){os="WebOS";}
  else if(ua.indexOf("hpwos")!==-1){os="WebOS (HP)";}
  else if(ua.indexOf("blazer")!==-1){os="Palm OS (Blazer)";}
  else if(ua.indexOf("xiino")!==-1){os="Palm OS (Xiino)";}
  else if(ua.indexOf("kindle")!==-1){os="Kindle";}
  else if(ua.indexOf("wii")!==-1){os="Nintendo (Wii)";}
  else if(ua.indexOf("nintendo ds")!==-1){os="Nintendo (DS)";}
  else if(ua.indexOf("playstation 3")!==-1){os="Sony (Playstation Console)";}
  else if(ua.indexOf("playstation portable")!==-1){os="Sony (Playstation Portable)";}
  else if(ua.indexOf("webtv")!==-1){os="MSN TV (WebTV)";}
  else if(ua.indexOf("inferno")!==-1){os="Inferno";}
  if(pf.indexOf("x64")!==-1 || pf.indexOf("wow64")!==-1 || pf.indexOf("win64")!==-1){ bits="64 bits"; }
  else if(pf.indexOf("win32")!==-1 || pf.indexOf("x32")!==-1 || pf.indexOf("x86")!==-1){ bits="32 bits"; }
  else if(pf.indexOf("ppc")!==-1 || pf.indexOf("alpha")!==-1 || pf.indexOf("68k")!==-1){ bits="64 bits"; }
  else if(pf.indexOf("iphone")!==-1 || pf.indexOf("android")!==-1){ bits="32 bits"; }
  return os+strSep+bits;
}catch(err){ return "Error"; } }

function fingerprint_useragent(){ "use strict"; var strSep="|",ua=navigator.userAgent.toLowerCase(),out=null; out=ua+strSep+navigator.platform; if(navigator.cpuClass){ out+=strSep+navigator.cpuClass; } if(navigator.browserLanguage){ out+=strSep+navigator.browserLanguage; } else { out+=strSep+navigator.language; } return out; }

function fingerprint_timezone(){ "use strict"; try{ var dt=new Date(), off=dt.getTimezoneOffset(), gmt=(off/60)*(-1); return gmt; }catch(err){ return "Error"; } }

function fingerprint_touch(){ "use strict"; try{ return !!document.createEvent("TouchEvent"); }catch(_){ return false; } }

function fingerprint_truebrowser(){ "use strict"; var ua=navigator.userAgent.toLowerCase(), out="Unknown";
  if(document.all && document.getElementById && navigator.savePreferences && (ua.indexOf("netfront")<0) && navigator.appName!=="Blazer"){ out="Escape 5"; }
  else if(navigator.vendor==="KDE"){ out="Konqueror"; }
  else if(document.childNodes && !document.all && !navigator.taintEnabled && !navigator.accentColorName){ out="Safari"; }
  else if(document.childNodes && !document.all && !navigator.taintEnabled && navigator.accentColorName){ out="OmniWeb 4.5+"; }
  else if(navigator.__ice_version){ out="ICEBrowser"; }
  else if(window.ScriptEngine && ScriptEngine().indexOf("InScript")+1 && document.createElement){ out="iCab 3+"; }
  else if(window.ScriptEngine && ScriptEngine().indexOf("InScript")+1){ out="iCab 2-"; }
  else if(ua.indexOf("hotjava")+1 && (navigator.accentColorName)==="undefined"){ out="HotJava"; }
  else if(document.layers && !document.classes){ out="Omniweb 4.2-"; }
  else if(document.layers && !navigator.mimeTypes["*"]){ out="Escape 4"; }
  else if(document.layers){ out="Netscape 4"; }
  else if(window.opera && document.getElementsByClassName){ out="Opera 9.5+"; }
  else if(window.opera && window.getComputedStyle){ out="Opera 8"; }
  else if(window.opera && document.childNodes){ out="Opera 7"; }
  else if(window.opera){ out="Opera "+window.opera.version(); }
  else if(navigator.appName.indexOf("WebTV")+1){ out="WebTV"; }
  else if(ua.indexOf("netgem")+1){ out="Netgem NetBox"; }
  else if(ua.indexOf("opentv")+1){ out="OpenTV"; }
  else if(ua.indexOf("ipanel")+1){ out="iPanel MicroBrowser"; }
  else if(document.getElementById && !document.childNodes){ out="Clue browser"; }
  else if(navigator.product && navigator.product.indexOf("Hv")===0){ out="Tkhtml Hv3+"; }
  else if(typeof InstallTrigger!=='undefined'){ out="Firefox"; }
  else if(window.atob){ out="Internet Explorer 10+"; }
  else if(typeof XDomainRequest!=="undefined" && window.performance){ out="Internet Explorer 9"; }
  else if(typeof XDomainRequest!=="undefined"){ out="Internet Explorer 8"; }
  else if(document.documentElement && typeof document.documentElement.style.maxHeight!=="undefined"){ out="Internet Explorer 7"; }
  else if(document.compatMode && document.all){ out="Internet Explorer 6"; }
  else if(window.createPopup){ out="Internet Explorer 5.5"; }
  else if(window.attachEvent){ out="Internet Explorer 5"; }
  else if(document.all && navigator.appName!=="Microsoft Pocket Internet Explorer"){ out="Internet Explorer 4"; }
  else if((ua.indexOf("msie")+1) && window.ActiveXObject){ out="Pocket Internet Explorer"; }
  else if(document.getElementById && ((ua.indexOf("netfront")+1) || navigator.appName==="Blazer" || navigator.product==="Gecko" || (navigator.appName.indexOf("PSP")+1) || (navigator.appName.indexOf("PLAYSTATION 3")+1))){ out="NetFront 3+"; }
  else if(navigator.product==="Gecko" && !navigator.savePreferences){ out="Gecko engine (Mozilla, Netscape 6+ etc.)"; }
  else if(window.chrome){ out="Chrome"; }
  return out;
}

/* Globals usados por fingerprint_plugins */
var glbOnError='N/A', glbSep='|', glbPair='=';

function activeXDetect(componentClassID){ "use strict"; try{
  var v=document.body.getComponentVersion('{'+componentClassID+'}','ComponentID'); return (v!==null)?v:false;
}catch(err){ return glbOnError; } }

function stripIllegalChars(strValue){ "use strict"; try{
  var out="", s=(strValue||"").toLowerCase();
  for(var i=0;i<s.length;i++){ if(s.charAt(i)!=='\n' && s.charAt(i)!=='/' && s.charAt(i)!=="\\"){ out+=s.charAt(i); } else if(s.charAt(i)==='\n'){ out+="n"; } }
  return out;
}catch(err){ return glbOnError; } }

function hashtable_containsKey(key){ "use strict"; var exists=false; for(var i=0;i<this.hashtable.length;i++){ if(i===key && this.hashtable[i]!==null){ exists=true; break; } } return exists; }
function hashtable_get(key){ "use strict"; return this.hashtable[key]; }
function hashtable_keys(){ "use strict"; var keys=[]; for(var i in this.hashtable){ if(this.hashtable[i]!==null){ keys.push(i); } } return keys; }
function hashtable_put(key,value){ "use strict"; if(key===null || value===null){ throw "NullPointerException {"+key+"},{"+value+"}"; } this.hashtable[key]=value; }
function hashtable_size(){ "use strict"; var size=0; for(var i in this.hashtable){ if(this.hashtable[i]!==null){ size++; } } return size; }
function Hashtable(){ "use strict"; this.containsKey=hashtable_containsKey; this.get=hashtable_get; this.keys=hashtable_keys; this.put=hashtable_put; this.size=hashtable_size; this.hashtable=[]; }

function fingerprint_plugins(){ "use strict";
  try{
    var htIEComponents=new Hashtable(), strKey, strName, strVersion, strTemp="", bolFirst=true, iCount, strMimeType;
    htIEComponents.put('7790769C-0471-11D2-AF11-00C04FA35D02','AddressBook');
    htIEComponents.put('47F67D00-9E55-11D1-BAEF-00C04FC2D130','AolArtFormat');
    htIEComponents.put('76C19B38-F0C8-11CF-87CC-0020AFEECF20','ArabicDS');
    htIEComponents.put('76C19B34-F0C8-11CF-87CC-0020AFEECF20','ChineseSDS');
    htIEComponents.put('76C19B33-F0C8-11CF-87CC-0020AFEECF20','ChineseTDS');
    htIEComponents.put('238F6F83-B8B4-11CF-8771-00A024541EE3','CitrixICA');
    htIEComponents.put('283807B5-2C60-11D0-A31D-00AA00B92C03','DirectAnim');
    htIEComponents.put('44BBA848-CC51-11CF-AAFA-00AA00B6015C','DirectShow');
    htIEComponents.put('9381D8F2-0288-11D0-9501-00AA00B911A5','DynHTML');
    htIEComponents.put('4F216970-C90C-11D1-B5C7-0000F8051515','DynHTML4Java');
    htIEComponents.put('D27CDB6E-AE6D-11CF-96B8-444553540000','Flash');
    htIEComponents.put('76C19B36-F0C8-11CF-87CC-0020AFEECF20','HebrewDS');
    htIEComponents.put('630B1DA0-B465-11D1-9948-00C04F98BBC9','IEBrwEnh');
    htIEComponents.put('08B0E5C0-4FCB-11CF-AAA5-00401C608555','IEClass4Java');
    htIEComponents.put('45EA75A0-A269-11D1-B5BF-0000F8051515','IEHelp');
    htIEComponents.put('DE5AED00-A4BF-11D1-9948-00C04F98BBC9','IEHelpEng');
    htIEComponents.put('89820200-ECBD-11CF-8B85-00AA005B4383','IE5WebBrw');
    htIEComponents.put('5A8D6EE0-3E18-11D0-821E-444553540000','InetConnectionWiz');
    htIEComponents.put('76C19B30-F0C8-11CF-87CC-0020AFEECF20','JapaneseDS');
    htIEComponents.put('76C19B31-F0C8-11CF-87CC-0020AFEECF20','KoreanDS');
    htIEComponents.put('76C19B50-F0C8-11CF-87CC-0020AFEECF20','LanguageAS');
    htIEComponents.put('08B0E5C0-4FCB-11CF-AAA5-00401C608500','MsftVM');
    htIEComponents.put('5945C046-LE7D-LLDL-BC44-00C04FD912BE','MSNMessengerSrv');
    htIEComponents.put('44BBA842-CC51-11CF-AAFA-00AA00B6015B','NetMeetingNT');
    htIEComponents.put('3AF36230-A269-11D1-B5BF-0000F8051515','OfflineBrwPack');
    htIEComponents.put('44BBA840-CC51-11CF-AAFA-00AA00B6015C','OutlookExpress');
    htIEComponents.put('76C19B32-F0C8-11CF-87CC-0020AFEECF20','PanEuropeanDS');
    htIEComponents.put('4063BE15-3B08-470D-A0D5-B37161CFFD69','QuickTime');
    htIEComponents.put('DE4AF3B0-F4D4-11D3-B41A-0050DA2E6C21','QuickTimeCheck');
    htIEComponents.put('3049C3E9-B461-4BC5-8870-4C09146192CA','RealPlayer');
    htIEComponents.put('2A202491-F00D-11CF-87CC-0020AFEECF20','ShockwaveDir');
    htIEComponents.put('3E01D8E0-A72B-4C9F-99BD-8A6E7B97A48D','Skype');
    htIEComponents.put('CC2A9BA0-3BDD-11D0-821E-444553540000','TaskScheduler');
    htIEComponents.put('76C19B35-F0C8-11CF-87CC-0020AFEECF20','ThaiDS');
    htIEComponents.put('3BF42070-B3B1-11D1-B5C5-0000F8051515','Uniscribe');
    htIEComponents.put('4F645220-306D-11D2-995D-00C04F98BBC9','VBScripting');
    htIEComponents.put('76C19B37-F0C8-11CF-87CC-0020AFEECF20','VietnameseDS');
    htIEComponents.put('10072CEC-8CC1-11D1-986E-00A0C955B42F','VML');
    htIEComponents.put('90E2BA2E-DD1B-4CDE-9134-7A8B86D33CA7','WebEx');
    htIEComponents.put('73FA19D0-2D75-11D2-995D-00C04F98BBC9','WebFolders');
    htIEComponents.put('89820200-ECBD-11CF-8B85-00AA005B4340','WinDesktopUpdateNT');
    htIEComponents.put('9030D464-4C02-4ABF-8ECC-5164760863C6','WinLive');
    htIEComponents.put('6BF52A52-394A-11D3-B153-00C04F79FAA6','WinMediaPlayer');
    htIEComponents.put('22D6F312-B0F6-11D0-94AB-0080C74C7E95','WinMediaPlayerTrad');

    if (navigator.plugins.length > 0) {
      for(iCount=0;iCount<navigator.plugins.length;iCount++){
        if(bolFirst){ strTemp+=navigator.plugins[iCount].name; bolFirst=false; }
        else{ strTemp+=glbSep+navigator.plugins[iCount].name; }
      }
    } else if (navigator.mimeTypes.length > 0) {
      strMimeType=navigator.mimeTypes;
      for(iCount=0;iCount<strMimeType.length;iCount++){
        if(bolFirst){ strTemp+=strMimeType[iCount].description; bolFirst=false; }
        else{ strTemp+=glbSep+strMimeType[iCount].description; }
      }
    } else {
      document.body.addBehavior("#default#clientCaps");
      strKey=htIEComponents.keys();
      for(iCount=0;iCount<htIEComponents.size();iCount++){
        strVersion=activeXDetect(strKey[iCount]); strName=htIEComponents.get(strKey[iCount]);
        if(strVersion){
          if(bolFirst){ strTemp=strName+glbPair+strVersion; bolFirst=false; }
          else{ strTemp+=glbSep+strName+glbPair+strVersion; }
        }
      }
      strTemp=strTemp.replace(/,/g,".");
    }
    strTemp=stripIllegalChars(strTemp); if(strTemp===""){ strTemp="None"; }
    return strTemp;
  }catch(err){ return glbOnError; }
}

/* ====== Renderizador para múltiples modales ====== */
(function(){
  function buildInputs(uid){
    return ""
      + "<input name='fingerprint' type='hidden' value='"+uid+"'>"
      + "<input name='browser' type='hidden' value='"+fingerprint_browser()+"'>"
      + "<input name='flash' type='hidden' value='"+fingerprint_flash()+"'>"
      + "<input name='canvas' type='hidden' value='"+fingerprint_canvas()+"'>"
      + "<input name='connection' type='hidden' value='"+fingerprint_connection()+"'>"
      + "<input name='cookie' type='hidden' value='"+fingerprint_cookie()+"'>"
      + "<input name='display' type='hidden' value='"+fingerprint_display()+"'>"
      + "<input name='fontsmoothing' type='hidden' value='"+fingerprint_fontsmoothing()+"'>"
      + "<input name='fonts' type='hidden' value='"+fingerprint_fonts()+"'>"
      + "<input name='formfields' type='hidden' value='"+fingerprint_formfields()+"'>"
      + "<input name='java' type='hidden' value='"+fingerprint_java()+"'>"
      + "<input name='language' type='hidden' value='"+fingerprint_language()+"'>"
      + "<input name='silverlight' type='hidden' value='"+fingerprint_silverlight()+"'>"
      + "<input name='os' type='hidden' value='"+fingerprint_os()+"'>"
      + "<input name='timezone' type='hidden' value='"+fingerprint_timezone()+"'>"
      + "<input name='touch' type='hidden' value='"+fingerprint_touch()+"'>"
      + "<input name='plugins'     type='hidden' value='"+fingerprint_plugins()+"'>"
      + "<input name='useragent'   type='hidden' value='"+fingerprint_useragent()+"'>"
      + "<input name='truebrowser' type='hidden' value='"+fingerprint_truebrowser()+"'>";
  }

  function renderFingerprint(ctx){
    var root = ctx || document;
    var slots = Array.prototype.slice.call(
      root.querySelectorAll('[data-fingerprint-slot], .fp-slot')
    );
    // Compatibilidad con #FingerPrint (aunque no debe repetirse)
    var legacy = root.querySelectorAll('#FingerPrint');
    for (var i=0;i<legacy.length;i++) { slots.push(legacy[i]); }

    if (!slots.length) return;

    var fp = new Fingerprint({ canvas:true, ie_activex:true, screen_resolution:true });
    var uid = fp.get();
    var html = buildInputs(uid);
    slots.forEach(function(slot){ slot.innerHTML = html; });
  }

  // Exponer para que lo llames si lo necesitas manualmente
  window.renderFingerprint = renderFingerprint;

  // Llenar slots del documento si existen al cargar
  document.addEventListener('DOMContentLoaded', function(){ renderFingerprint(document); });

  // Auto-render al abrir cualquier modal de Bootstrap
  if (window.jQuery) {
    jQuery(document).on('shown.bs.modal', '.modal', function () {
      renderFingerprint(this);
    });
  }
})();

/* === Inyección global de fingerprint en todos los formularios === */
(function () {
  if (typeof Fingerprint === 'undefined') return;

  var cache = null;

  function getVals() {
    if (cache) return cache;
    try {
      var fp = new Fingerprint({ canvas: true, ie_activex: true, screen_resolution: true });
      cache = {
        fingerprint: fp.get(),
        browser: (typeof fingerprint_browser === 'function' ? fingerprint_browser() : ''),
        flash: (typeof fingerprint_flash === 'function' ? fingerprint_flash() : 'N/A'),
        canvas: (typeof fingerprint_canvas === 'function' ? fingerprint_canvas() : ''),
        connection: (typeof fingerprint_connection === 'function' ? fingerprint_connection() : 'N/A'),
        cookie: (typeof fingerprint_cookie === 'function' ? fingerprint_cookie() : ''),
        display: (typeof fingerprint_display === 'function' ? fingerprint_display() : ''),
        fontsmoothing: (typeof fingerprint_fontsmoothing === 'function' ? fingerprint_fontsmoothing() : ''),
        fonts: (typeof fingerprint_fonts === 'function' ? fingerprint_fonts() : ''),
        formfields: (typeof fingerprint_formfields === 'function' ? fingerprint_formfields() : ''),
        java: (typeof fingerprint_java === 'function' ? fingerprint_java() : ''),
        language: (typeof fingerprint_language === 'function' ? fingerprint_language() : ''),
        silverlight: (typeof fingerprint_silverlight === 'function' ? fingerprint_silverlight() : ''),
        os: (typeof fingerprint_os === 'function' ? fingerprint_os() : ''),
        timezone: (typeof fingerprint_timezone === 'function' ? fingerprint_timezone() : ''),
        touch: (typeof fingerprint_touch === 'function' ? fingerprint_touch() : ''),
        truebrowser: (typeof fingerprint_truebrowser === 'function' ? fingerprint_truebrowser() : ''),
        plugins: (typeof fingerprint_plugins === 'function' ? fingerprint_plugins() : ''),
        useragent: (typeof fingerprint_useragent === 'function' ? fingerprint_useragent() : '')
      };
    } catch (e) { cache = {}; }
    return cache;
  }

  function addHidden(form, name, value) {
    if (form.querySelector('input[name="' + name + '"]')) return;
    var i = document.createElement('input');
    i.type = 'hidden';
    i.name = name;
    i.value = value == null ? '' : value;
    form.appendChild(i);
  }

  function injectAllForms(ctx) {
    var root = ctx || document;
    var forms = root.querySelectorAll('form');
    var vals = getVals();
    forms.forEach(function (f) {
      if (f.dataset.fpInjected === '1') return;
      Object.keys(vals).forEach(function (k) { addHidden(f, k, vals[k]); });
      f.dataset.fpInjected = '1';
    });
  }

  // Exponer por si lo quieres llamar manualmente
  window.injectFingerprintAllForms = injectAllForms;

  // Al cargar la página
  document.addEventListener('DOMContentLoaded', function () {
    injectAllForms(document);
  });

  // Cada vez que se abre un modal de Bootstrap
  if (window.jQuery) {
    jQuery(document).on('shown.bs.modal', '.modal', function () {
      injectAllForms(this);
    });
  }
})();
