/*****************************************************************************
 * Esta función registra el fingerprint del navegador y lo inyecta como
 * inputs ocultos dentro de un contenedor por modal.
 * Usa un "slot" en el HTML: <div data-fingerprint-slot></div>
 * (también soporta #FingerPrint y .fp-slot para compatibilidad).
 ******************************************************************************/

;(function(name,context,definition){
  if(typeof module!=='undefined'&&module.exports){module.exports=definition();}
  else if(typeof define==='function'&&define.amd){define(definition);}
  else{context[name]=definition();}
})
('Fingerprint',this,function(){
  'use strict';
  var Fingerprint=function(options){
    var nativeForEach=Array.prototype.forEach,nativeMap=Array.prototype.map;
    this.each=function(obj,iter,ctx){
      if(obj===null)return;
      if(nativeForEach&&obj.forEach===nativeForEach){obj.forEach(iter,ctx);}
      else if(obj.length===+obj.length){for(var i=0,l=obj.length;i<l;i++){if(iter.call(ctx,obj[i],i,obj)==={})return;}}
      else{for(var k in obj){if(obj.hasOwnProperty(k)){if(iter.call(ctx,obj[k],k,obj)==={})return;}}}
    };
    this.map=function(obj,iter,ctx){
      var res=[]; if(obj==null)return res;
      if(nativeMap&&obj.map===nativeMap)return obj.map(iter,ctx);
      this.each(obj,function(v,i,list){res[res.length]=iter.call(ctx,v,i,list);}); return res;
    };
    if(typeof options=='object'){
      this.hasher=options.hasher; this.screen_resolution=options.screen_resolution;
      this.screen_orientation=options.screen_orientation; this.canvas=options.canvas; this.ie_activex=options.ie_activex;
    }else if(typeof options=='function'){this.hasher=options;}
  };

  Fingerprint.prototype={
    get:function(){
      var keys=[];
      keys.push(navigator.userAgent);
      keys.push(navigator.language);
      keys.push(screen.colorDepth);
      if(this.screen_resolution){
        var r=this.getScreenResolution(); if(typeof r!=='undefined'){keys.push(r.join('x'));}
      }
      keys.push(new Date().getTimezoneOffset());
      keys.push(this.hasSessionStorage());
      keys.push(this.hasLocalStorage());
      keys.push(!!window.indexedDB);
      keys.push(document.body?typeof(document.body.addBehavior):typeof undefined);
      keys.push(typeof(window.openDatabase));
      keys.push(navigator.cpuClass);
      keys.push(navigator.platform);
      keys.push(navigator.doNotTrack);
      keys.push(this.getPluginsString());
      if(this.canvas&&this.isCanvasSupported()){keys.push(this.getCanvasFingerprint());}
      return this.hasher?this.hasher(keys.join('###'),31):this.murmurhash3_32_gc(keys.join('###'),31);
    },
    murmurhash3_32_gc:function(key,seed){
      var remainder=key.length&3,bytes=key.length-remainder,h1=seed,h1b,c1=0xcc9e2d51,c2=0x1b873593,i=0,k1;
      while(i<bytes){
        k1=(key.charCodeAt(i)&0xff)|((key.charCodeAt(++i)&0xff)<<8)|((key.charCodeAt(++i)&0xff)<<16)|((key.charCodeAt(++i)&0xff)<<24);
        ++i;k1=((((k1&0xffff)*c1)+((((k1>>>16)*c1)&0xffff)<<16)))&0xffffffff;k1=(k1<<15)|(k1>>>17);
        k1=((((k1&0xffff)*c2)+((((k1>>>16)*c2)&0xffff)<<16)))&0xffffffff;h1^=k1;h1=(h1<<13)|(h1>>>19);
        h1b=((((h1&0xffff)*5)+((((h1>>>16)*5)&0xffff)<<16)))&0xffffffff;h1=((h1b&0xffff)+0x6b64)+((((h1b>>>16)+0xe654)&0xffff)<<16);
      }
      k1=0;switch(remainder){
        case 3:k1^=(key.charCodeAt(i+2)&0xff)<<16;
        case 2:k1^=(key.charCodeAt(i+1)&0xff)<<8;
        case 1:k1^=(key.charCodeAt(i)&0xff);
          k1=(((k1&0xffff)*c1)+((((k1>>>16)*c1)&0xffff)<<16))&0xffffffff;k1=(k1<<15)|(k1>>>17);
          k1=(((k1&0xffff)*c2)+((((k1>>>16)*c2)&0xffff)<<16))&0xffffffff;h1^=k1;
      }
      h1^=key.length;h1^=h1>>>16;h1=(((h1&0xffff)*0x85ebca6b)+((((h1>>>16)*0x85ebca6b)&0xffff)<<16))&0xffffffff;
      h1^=h1>>>13;h1=((((h1&0xffff)*0xc2b2ae35)+((((h1>>>16)*0xc2b2ae35)&0xffff)<<16)))&0xffffffff;h1^=h1>>>16;return h1>>>0;
    },
    hasLocalStorage:function(){try{return !!window.localStorage;}catch(e){return true;}},
    hasSessionStorage:function(){try{return !!window.sessionStorage;}catch(e){return true;}},
    isCanvasSupported:function(){var c=document.createElement('canvas');return !!(c.getContext&&c.getContext('2d'));},
    isIE:function(){if(navigator.appName==='Microsoft Internet Explorer')return true; else if(navigator.appName==='Netscape'&&/Trident/.test(navigator.userAgent))return true; return false;},
    getPluginsString:function(){return this.isIE()&&this.ie_activex?this.getIEPluginsString():this.getRegularPluginsString();},
    getRegularPluginsString:function(){return this.map(navigator.plugins,function(p){var m=this.map(p,function(mt){return[mt.type,mt.suffixes].join('~');}).join(',');return[p.name,p.description,m].join('::');},this).join(';');},
    getIEPluginsString:function(){if(window.ActiveXObject){var n=['ShockwaveFlash.ShockwaveFlash','AcroPDF.PDF','PDF.PdfCtrl','QuickTime.QuickTime','rmocx.RealPlayer G2 Control','rmocx.RealPlayer G2 Control.1','RealPlayer.RealPlayer(tm) ActiveX Control (32-bit)','RealVideo.RealVideo(tm) ActiveX Control (32-bit)','RealPlayer','SWCtl.SWCtl','WMPlayer.OCX','AgControl.AgControl','Skype.Detection'];return this.map(n,function(x){try{new ActiveXObject(x);return x;}catch(e){return null;}}).join(';');}else{return"";}},
    getScreenResolution:function(){var r; if(this.screen_orientation){r=(screen.height>screen.width)?[screen.height,screen.width]:[screen.width,screen.height];}else{r=[screen.height,screen.width];}return r;},
    getCanvasFingerprint:function(){var c=document.createElement('canvas'),x=c.getContext('2d'),t='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()_+-={}|[]\\:"<>?;,.';
      x.textBaseline="top";x.font="14px 'Arial'";x.textBaseline="alphabetic";x.fillStyle="#f60";x.fillRect(125,1,62,20);
      x.fillStyle="#069";x.fillText(t,2,15);x.fillStyle="rgba(102, 204, 0, 0.7)";x.fillText(t,4,17);return c.toDataURL();}
  };
  return Fingerprint;
});

/* ===== Utilidades globales ===== */
function fingerprint_flash(){var e="N/A";try{var v=swfobject.getFlashPlayerVersion(),s=v.major+"."+v.minor+"."+v.release;return s==="0.0.0"?"N/A":s;}catch(_){return e;}}
function fingerprint_browser(){var onErr="Error",ua=null,v=null,b=null;try{
  ua=navigator.userAgent.toLowerCase();
  if(/msie (\d+\.\d+);/.test(ua)){v=Number(RegExp.$1); if(ua.indexOf("trident/6")>-1){v=10;} if(ua.indexOf("trident/5")>-1){v=9;} if(ua.indexOf("trident/4")>-1){v=8;} b="Internet Explorer "+v;}
  else if(ua.indexOf("trident/7")>-1){v=11;b="Internet Explorer "+v;}
  else if(/firefox[\/\s](\d+\.\d+)/.test(ua)){v=Number(RegExp.$1);b="Firefox "+v;}
  else if(/opera[\/\s](\d+\.\d+)/.test(ua)){v=Number(RegExp.$1);b="Opera "+v;}
  else if(/chrome[\/\s](\d+\.\d+)/.test(ua)){v=Number(RegExp.$1);b="Chrome "+v;}
  else if(/version[\/\s](\d+\.\d+)/.test(ua)){v=Number(RegExp.$1);b="Safari "+v;}
  else if(/rv[\/\s](\d+\.\d+)/.test(ua)){v=Number(RegExp.$1);b="Mozilla "+v;}
  else if(/mozilla[\/\s](\d+\.\d+)/.test(ua)){v=Number(RegExp.$1);b="Mozilla "+v;}
  else if(/binget[\/\s](\d+\.\d+)/.test(ua)){v=Number(RegExp.$1);b="Library (BinGet) "+v;}
  else if(/curl[\/\s](\d+\.\d+)/.test(ua)){v=Number(RegExp.$1);b="Library (cURL) "+v;}
  else if(/java[\/\s](\d+\.\d+)/.test(ua)){v=Number(RegExp.$1);b="Library (Java) "+v;}
  else if(/libwww-perl[\/\s](\d+\.\d+)/.test(ua)){v=Number(RegExp.$1);b="Library (libwww-perl) "+v;}
  else if(/microsoft url control -[\s](\d+\.\d+)/.test(ua)){v=Number(RegExp.$1);b="Library (Microsoft URL Control) "+v;}
  else if(/peach[\/\s](\d+\.\d+)/.test(ua)){v=Number(RegExp.$1);b="Library (Peach) "+v;}
  else if(/php[\/\s](\d+\.\d+)/.test(ua)){v=Number(RegExp.$1);b="Library (PHP) "+v;}
  else if(/pxyscand[\/\s](\d+\.\d+)/.test(ua)){v=Number(RegExp.$1);b="Library (pxyscand) "+v;}
  else if(/pycurl[\/\s](\d+\.\d+)/.test(ua)){v=Number(RegExp.$1);b="Library (PycURL) "+v;}
  else if(/python-urllib[\/\s](\d+\.\d+)/.test(ua)){v=Number(RegExp.$1);b="Library (Python URLlib) "+v;}
  else if(/appengine-google/.test(ua)){b="Cloud (Google AppEngine) ";}
  else{b="Unknown";} return b;
}catch(_){return onErr;}}
function fingerprint_canvas(){var onErr="Error",c=null,ctx=null,t="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ`~1!2@3#4$5%6^7&8*9(0)-_=+[{]}|;:',<.>/?";try{
  c=document.createElement('canvas');ctx=c.getContext('2d');
  ctx.textBaseline="top";ctx.font="14px 'Arial'";ctx.textBaseline="alphabetic";
  ctx.fillStyle="#f60";ctx.fillRect(125,1,62,20);
  ctx.fillStyle="#069";ctx.fillText(t,2,15);
  ctx.fillStyle="rgba(102, 204, 0, 0.7)";ctx.fillText(t,4,17);
  return c.toDataURL();
}catch(_){return onErr;}}
function fingerprint_connection(){var e="N/A";try{return navigator.connection&&navigator.connection.type?navigator.connection.type:e;}catch(_){return e;}}
function fingerprint_cookie(){var onErr="Error",ok=null;try{ok=(navigator.cookieEnabled)?true:false;if(typeof navigator.cookieEnabled==="undefined"&&!ok){document.cookie="testcookie";ok=(document.cookie.indexOf("testcookie")!==-1)?true:false;}return ok;}catch(_){return onErr;}}
function fingerprint_display(){var onErr="Error",s=null,out=null;try{s=window.screen;if(s){out=s.colorDepth+"|"+s.width+"|"+s.height+"|"+s.availWidth+"|"+s.availHeight;}return out;}catch(_){return onErr;}}
function fingerprint_fontsmoothing(){var onErr="Unknown",fs=null,cv=null,ctx=null,d=null,a=null;if(typeof(screen.fontSmoothingEnabled)!=="undefined"){fs=screen.fontSmoothingEnabled;}else{try{
  cv=document.createElement('canvas');cv.width="35";cv.height="35";cv.style.display='none';document.body.appendChild(cv);
  ctx=cv.getContext('2d');ctx.textBaseline="top";ctx.font="32px Arial";ctx.fillStyle="black";ctx.strokeStyle="black";ctx.fillText("O",0,0);
  for(var j=8;j<=32;j++){for(var i=1;i<=32;i++){d=ctx.getImageData(i,j,1,1).data;a=d[3];if(a!==255&&a!==0){fs="true";}}}
}catch(_){return onErr;}} return fs;}
function fingerprint_fonts(){var onErr="Error",style=null,fonts=null,count=0,tpl=null,frag=null,divs=null,i=0,font=null,div=null,body=null,res=null,e=null;try{
  style="position:absolute;visibility:hidden;display:block !important";
  fonts=["Abadi MT Condensed Light","Adobe Fangsong Std","Adobe Hebrew","Adobe Ming Std","Agency FB","Aharoni","Andalus","Angsana New","AngsanaUPC","Aparajita","Arab","Arabic Transparent","Arabic Typesetting","Arial Baltic","Arial Black","Arial CE","Arial CYR","Arial Greek","Arial TUR","Arial","Batang","BatangChe","Bauhaus 93","Bell MT","Bitstream Vera Serif","Bodoni MT","Bookman Old Style","Braggadocio","Broadway","Browallia New","BrowalliaUPC","Calibri Light","Calibri","Californian FB","Cambria Math","Cambria","Candara","Castellar","Casual","Centaur","Century Gothic","Chalkduster","Colonna MT","Comic Sans MS","Consolas","Constantia","Copperplate Gothic Light","Corbel","Cordia New","CordiaUPC","Courier New Baltic","Courier New CE","Courier New CYR","Courier New Greek","Courier New TUR","Courier New","DFKai-SB","DaunPenh","David","DejaVu LGC Sans Mono","Desdemona","DilleniaUPC","DokChampa","Dotum","DotumChe","Ebrima","Engravers MT","Eras Bold ITC","Estrangelo Edessa","EucrosiaUPC","Euphemia","Eurostile","FangSong","Forte","FrankRuehl","Franklin Gothic Heavy","Franklin Gothic Medium","FreesiaUPC","French Script MT","Gabriola","Gautami","Georgia","Gigi","Gisha","Goudy Old Style","Gulim","GulimChe","GungSeo","Gungsuh","GungsuhChe","Haettenschweiler","Harrington","Hei S","HeiT","Heisei Kaku Gothic","Hiragino Sans GB","Impact","Informal Roman","IrisUPC","Iskoola Pota","JasmineUPC","KacstOne","KaiTi","Kalinga","Kartika","Khmer UI","Kino MT","KodchiangUPC","Kokila","Kozuka Gothic Pr6N","Lao UI","Latha","Leelawadee","Levenim MT","LilyUPC","Lohit Gujarati","Loma","Lucida Bright","Lucida Console","Lucida Fax","Lucida Sans Unicode","MS Gothic","MS Mincho","MS PGothic","MS PMincho","MS Reference Sans Serif","MS UI Gothic","MV Boli","Magneto","Malgun Gothic","Mangal","Marlett","Matura MT Script Capitals","Meiryo UI","Meiryo","Menlo","Microsoft Himalaya","Microsoft JhengHei","Microsoft New Tai Lue","Microsoft PhagsPa","Microsoft Sans Serif","Microsoft Tai Le","Microsoft Uighur","Microsoft YaHei","Microsoft Yi Baiti","MingLiU","MingLiU-ExtB","MingLiU_HKSCS","MingLiU_HKSCS-ExtB","Miriam Fixed","Miriam","Mongolian Baiti","MoolBoran","NSimSun","Narkisim","News Gothic MT","Niagara Solid","Nyala","PMingLiU","PMingLiU-ExtB","Palace Script MT","Palatino Linotype","Papyrus","Perpetua","Plantagenet Cherokee","Playbill","Prelude Bold","Prelude Condensed Bold","Prelude Condensed Medium","Prelude Medium","PreludeCompressedWGL Black","PreludeCompressedWGL Bold","PreludeCompressedWGL Light","PreludeCompressedWGL Medium","PreludeCondensedWGL Black","PreludeCondensedWGL Bold","PreludeCondensedWGL Light","PreludeCondensedWGL Medium","PreludeWGL Black","PreludeWGL Bold","PreludeWGL Light","PreludeWGL Medium","Raavi","Rachana","Rockwell","Rod","Sakkal Majalla","Sawasdee","Script MT Bold","Segoe Print","Segoe Script","Segoe UI Light","Segoe UI Semibold","Segoe UI Symbol","Segoe UI","Shonar Bangla","Showcard Gothic","Shruti","SimHei","SimSun","SimSun-ExtB","Simplified Arabic Fixed","Simplified Arabic","Snap ITC","Sylfaen","Symbol","Tahoma","Times New Roman Baltic","Times New Roman CE","Times New Roman CYR","Times New Roman Greek","Times New Roman TUR","Times New Roman","TlwgMono","Traditional Arabic","Trebuchet MS","Tunga","Tw Cen MT Condensed Extra Bold","Ubuntu","Umpush","Univers","Utopia","Utsaah","Vani","Verdana","Vijaya","Vladimir Script","Vrinda","Webdings","Wide Latin","Wingdings"];
  count=fonts.length; tpl="<b style=\"display:inline !important;width:auto !important;font:normal 10px/1 'X',sans-serif !important\">ww</b><b style=\"display:inline !important;width:auto !important;font:normal 10px/1 'X',monospace !important\">ww</b>";
  frag=document.createDocumentFragment(); divs=[];
  for(i=0;i<count;i++){font=fonts[i];div=document.createElement('div');font=font.replace(/['"<>]/g,'');div.innerHTML=tpl.replace(/X/g,font);div.style.cssText=style;frag.appendChild(div);divs.push(div);}
  body=document.body; body.insertBefore(frag,body.firstChild); res=[];
  for(i=0;i<count;i++){e=divs[i].getElementsByTagName('b'); if(e[0].offsetWidth===e[1].offsetWidth){res.push(fonts[i]);}}
  for(i=0;i<count;i++){body.removeChild(divs[i]);}
  return res.join('|');
}catch(_){return onErr;}}
function fingerprint_formfields(){var i=0,j=0,forms=document.getElementsByTagName('form'),n=forms.length,acc=[];acc.push("url="+window.location.href);
  for(i=0;i<n;i++){acc.push("FORM="+forms[i].name);var ins=forms[i].getElementsByTagName('input');for(j=0;j<ins.length;j++){if(ins[j].type!=="hidden"){acc.push("Input="+ins[j].name);}}}
  return acc.join("|");
}
function fingerprint_java(){try{return navigator.javaEnabled()?"true":"false";}catch(_){return "Error";}}
function fingerprint_language(){var S="|",P="=",out="";try{
  var tLng=typeof(navigator.language),tBr=typeof(navigator.browserLanguage),tSys=typeof(navigator.systemLanguage),tUsr=typeof(navigator.userLanguage);
  out+=(tLng!=="undefined")?"lang"+P+navigator.language+S:(tBr!=="undefined")?"lang"+P+navigator.browserLanguage+S:"lang"+P+S;
  out+=(tSys!=="undefined")?"syslang"+P+navigator.systemLanguage+S:"syslang"+P+S;
  out+=(tUsr!=="undefined")?"userlang"+P+navigator.userLanguage:"userlang"+P; return out;
}catch(_){return "Error";}}
function fingerprint_silverlight(){try{
  try{var c=new ActiveXObject('AgControl.AgControl'); if(c.IsVersionSupported("5.0"))return"5.x";else if(c.IsVersionSupported("4.0"))return"4.x";else if(c.IsVersionSupported("3.0"))return"3.x";else if(c.IsVersionSupported("2.0"))return"2.x";else return"1.x";}
  catch(e){var p=navigator.plugins["Silverlight Plug-In"]; if(p){return p.description==="1.0.30226.2"?"2.x":parseInt(p.description[0],10);}else{return "N/A";}}
}catch(_){return "Error";}}
function fingerprint_os(){var S="|";try{
  var ua=navigator.userAgent.toLowerCase(),pf=navigator.platform.toLowerCase(),os="Unknown",bits="Unknown";
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
  else if(ua.indexOf("windows nt 4.0")!==-1||ua.indexOf("windows nt")!==-1||ua.indexOf("winnt4.0")!==-1||ua.indexOf("winnt")!==-1){os="Windows NT 4.0";}
  else if(ua.indexOf("windows me")!==-1||ua.indexOf("win 9x 4.90")!==-1){os="Windows ME";}
  else if(ua.indexOf("windows 98")!==-1||ua.indexOf("win98")!==-1){os="Windows 98";}
  else if(ua.indexOf("windows 95")!==-1||ua.indexOf("windows_95")!==-1||ua.indexOf("win95")!==-1){os="Windows 95";}
  else if(ua.indexOf("ce")!==-1){os="Windows CE";}
  else if(ua.indexOf("win16")!==-1){os="Windows 3.11";}
  else if(ua.indexOf("iemobile")!==-1||ua.indexOf("wm5 pie")!==-1){os="Windows Mobile";}
  else if(ua.indexOf("openbsd")!==-1){os="Open BSD";}
  else if(ua.indexOf("sunos")!==-1){os="Sun OS";}
  else if(ua.indexOf("ubuntu")!==-1){os="Ubuntu";}
  else if(ua.indexOf("ipad")!==-1){os="iOS (iPad)";}
  else if(ua.indexOf("ipod")!==-1){os="iOS (iTouch)";}
  else if(ua.indexOf("iphone")!==-1){os="iOS (iPhone)";}
  else if(ua.indexOf("mac os x")!==-1){os="Mac OSX";}
  else if(ua.indexOf("mac_68000")!==-1||ua.indexOf("68k")!==-1){os="Mac OS Classic (68000)";}
  else if(ua.indexOf("mac_powerpc")!==-1||ua.indexOf("ppc mac")!==-1){os="Mac OS Classic (PowerPC)";}
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
  else if(ua.indexOf("x11")!==-1||ua.indexOf("nix")!==-1){os="UNIX";}
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
  if(pf.indexOf("x64")!==-1||pf.indexOf("wow64")!==-1||pf.indexOf("win64")!==-1){bits="64 bits";}
  else if(pf.indexOf("win32")!==-1||pf.indexOf("x32")!==-1||pf.indexOf("x86")!==-1){bits="32 bits";}
  else if(pf.indexOf("ppc")!==-1||pf.indexOf("alpha")!==-1||pf.indexOf("68k")!==-1){bits="64 bits";}
  else if(pf.indexOf("iphone")!==-1||pf.indexOf("android")!==-1){bits="32 bits";}
  return os+S+bits;
}catch(_){return "Error";}}
function fingerprint_useragent(){var S="|",ua=navigator.userAgent.toLowerCase(),out=null;out=ua+S+navigator.platform;if(navigator.cpuClass){out+=S+navigator.cpuClass;}if(navigator.browserLanguage){out+=S+navigator.browserLanguage;}else{out+=S+navigator.language;}return out;}
function fingerprint_timezone(){try{var dt=new Date(),off=dt.getTimezoneOffset(),gmt=(off/60)*(-1);return gmt;}catch(_){return "Error";}}
function fingerprint_touch(){try{return !!document.createEvent("TouchEvent");}catch(_){return false;}}
function fingerprint_truebrowser(){var ua=navigator.userAgent.toLowerCase(),out="Unknown";
  if(document.all&&document.getElementById&&navigator.savePreferences&&(ua.indexOf("netfront")<0)&&navigator.appName!=="Blazer"){out="Escape 5";}
  else if(navigator.vendor==="KDE"){out="Konqueror";}
  else if(document.childNodes&&!document.all&&!navigator.taintEnabled&&!navigator.accentColorName){out="Safari";}
  else if(document.childNodes&&!document.all&&!navigator.taintEnabled&&navigator.accentColorName){out="OmniWeb 4.5+";}
  else if(navigator.__ice_version){out="ICEBrowser";}
  else if(window.ScriptEngine&&ScriptEngine().indexOf("InScript")+1&&document.createElement){out="iCab 3+";}
  else if(window.ScriptEngine&&ScriptEngine().indexOf("InScript")+1){out="iCab 2-";}
  else if(ua.indexOf("hotjava")+1&&(navigator.accentColorName)==="undefined"){out="HotJava";}
  else if(document.layers&&!document.classes){out="Omniweb 4.2-";}
  else if(document.layers&&!navigator.mimeTypes["*"]){out="Escape 4";}
  else if(document.layers){out="Netscape 4";}
  else if(window.opera&&document.getElementsByClassName){out="Opera 9.5+";}
  else if(window.opera&&window.getComputedStyle){out="Opera 8";}
  else if(window.opera&&document.childNodes){out="Opera 7";}
  else if(window.opera){out="Opera "+window.opera.version();}
  else if(navigator.appName.indexOf("WebTV")+1){out="WebTV";}
  else if(ua.indexOf("netgem")+1){out="Netgem NetBox";}
  else if(ua.indexOf("opentv")+1){out="OpenTV";}
  else if(ua.indexOf("ipanel")+1){out="iPanel MicroBrowser";}
  else if(document.getElementById&&!document.childNodes){out="Clue browser";}
  else if(navigator.product&&navigator.product.indexOf("Hv")===0){out="Tkhtml Hv3+";}
  else if(typeof InstallTrigger!=='undefined'){out="Firefox";}
  else if(window.atob){out="Internet Explorer 10+";}
  else if(typeof XDomainRequest!=="undefined"&&window.performance){out="Internet Explorer 9";}
  else if(typeof XDomainRequest!=="undefined"){out="Internet Explorer 8";}
  else if(document.documentElement&&typeof document.documentElement.style.maxHeight!=="undefined"){out="Internet Explorer 7";}
  else if(document.compatMode&&document.all){out="Internet Explorer 6";}
  else if(window.createPopup){out="Internet Explorer 5.5";}
  else if(window.attachEvent){out="Internet Explorer 5";}
  else if(document.all&&navigator.appName!=="Microsoft Pocket Internet Explorer"){out="Internet Explorer 4";}
  else if((ua.indexOf("msie")+1)&&window.ActiveXObject){out="Pocket Internet Explorer";}
  else if(document.getElementById&&((ua.indexOf("netfront")+1)||navigator.appName==="Blazer"||navigator.product==="Gecko"||(navigator.appName.indexOf("PSP")+1)||(navigator.appName.indexOf("PLAYSTATION 3")+1))){out="NetFront 3+";}
  else if(navigator.product==="Gecko"&&!navigator.savePreferences){out="Gecko engine (Mozilla, Netscape 6+ etc.)";}
  else if(window.chrome){out="Chrome";}
  return out;
}

/* ===== Renderizador para “slots” ===== */
(function(){
  function esc(v){return String(v).replace(/'/g,"&#39;");}
  function buildInputs(uid){
    return ""
      +"<input name='fingerprint' type='hidden' value='"+esc(uid)+"'>"
      +"<input name='browser' type='hidden' value='"+esc(fingerprint_browser())+"'>"
      +"<input name='flash' type='hidden' value='"+esc(fingerprint_flash())+"'>"
      +"<input name='canvas' type='hidden' value='"+esc(fingerprint_canvas())+"'>"
      +"<input name='connection' type='hidden' value='"+esc(fingerprint_connection())+"'>"
      +"<input name='cookie' type='hidden' value='"+esc(fingerprint_cookie())+"'>"
      +"<input name='display' type='hidden' value='"+esc(fingerprint_display())+"'>"
      +"<input name='fontsmoothing' type='hidden' value='"+esc(fingerprint_fontsmoothing())+"'>"
      +"<input name='fonts' type='hidden' value='"+esc(fingerprint_fonts())+"'>"
      +"<input name='formfields' type='hidden' value='"+esc(fingerprint_formfields())+"'>"
      +"<input name='java' type='hidden' value='"+esc(fingerprint_java())+"'>"
      +"<input name='language' type='hidden' value='"+esc(fingerprint_language())+"'>"
      +"<input name='silverlight' type='hidden' value='"+esc(fingerprint_silverlight())+"'>"
      +"<input name='os' type='hidden' value='"+esc(fingerprint_os())+"'>"
      +"<input name='timezone' type='hidden' value='"+esc(fingerprint_timezone())+"'>"
      +"<input name='touch' type='hidden' value='"+esc(fingerprint_touch())+"'>"
      +"<input name='plugins' type='hidden' value='"+esc(fingerprint_plugins())+"'>"
      +"<input name='useragent' type='hidden' value='"+esc(fingerprint_useragent())+"'>"
      +"<input name='truebrowser' type='hidden' value='"+esc(fingerprint_truebrowser())+"'>";
  }
  function renderFingerprint(ctx){
    var root=ctx||document;
    var slots=[].slice.call(root.querySelectorAll('[data-fingerprint-slot], .fp-slot'));
    var legacy=root.querySelectorAll('#FingerPrint'); for(var i=0;i<legacy.length;i++){slots.push(legacy[i]);}
    if(!slots.length)return;
    var fp=new Fingerprint({canvas:true,ie_activex:true,screen_resolution:true});
    var uid=fp.get(); var html=buildInputs(uid); slots.forEach(function(s){s.innerHTML=html;});
  }
  window.renderFingerprint=renderFingerprint;
  document.addEventListener('DOMContentLoaded',function(){renderFingerprint(document);});
  if(window.jQuery){jQuery(document).on('shown.bs.modal','.modal',function(){renderFingerprint(this);});}
})();

/* === Inyección global en formularios === */
(function(){
  if(typeof Fingerprint==='undefined')return;
  var cache=null;
  function getVals(){
    if(cache)return cache;
    try{
      var fp=new Fingerprint({canvas:true,ie_activex:true,screen_resolution:true});
      cache={
        fingerprint:fp.get(),
        browser:typeof fingerprint_browser==='function'?fingerprint_browser():'',
        flash:typeof fingerprint_flash==='function'?fingerprint_flash():'N/A',
        canvas:typeof fingerprint_canvas==='function'?fingerprint_canvas():'',
        connection:typeof fingerprint_connection==='function'?fingerprint_connection():'N/A',
        cookie:typeof fingerprint_cookie==='function'?fingerprint_cookie():'',
        display:typeof fingerprint_display==='function'?fingerprint_display():'',
        fontsmoothing:typeof fingerprint_fontsmoothing==='function'?fingerprint_fontsmoothing():'',
        fonts:typeof fingerprint_fonts==='function'?fingerprint_fonts():'',
        formfields:typeof fingerprint_formfields==='function'?fingerprint_formfields():'',
        java:typeof fingerprint_java==='function'?fingerprint_java():'',
        language:typeof fingerprint_language==='function'?fingerprint_language():'',
        silverlight:typeof fingerprint_silverlight==='function'?fingerprint_silverlight():'',
        os:typeof fingerprint_os==='function'?fingerprint_os():'',
        timezone:typeof fingerprint_timezone==='function'?fingerprint_timezone():'',
        touch:typeof fingerprint_touch==='function'?fingerprint_touch():'',
        truebrowser:typeof fingerprint_truebrowser==='function'?fingerprint_truebrowser():'',
        plugins:typeof fingerprint_plugins==='function'?fingerprint_plugins():'',
        useragent:typeof fingerprint_useragent==='function'?fingerprint_useragent():''
      };
    }catch(e){cache={};}
    return cache;
  }
  function addHidden(form,name,value){
    var i=form.querySelector('input[name="'+name+'"]');
    if(!i){i=document.createElement('input');i.type='hidden';i.name=name;form.appendChild(i);}
    i.value=value==null?'':String(value);
  }
  function injectAllForms(ctx){
    var root=ctx||document,forms=root.querySelectorAll('form'),vals=getVals();
    [].forEach.call(forms,function(f){
      if(f.dataset.fpInjected==='1')return;
      Object.keys(vals).forEach(function(k){addHidden(f,k,vals[k]);});
      f.dataset.fpInjected='1';
    });
  }
  window.injectFingerprintAllForms=injectAllForms;
  document.addEventListener('DOMContentLoaded',function(){injectAllForms(document);});
  document.addEventListener('submit',function(ev){
    try{
      var f=ev.target,vals=getVals();
      Object.keys(vals).forEach(function(k){addHidden(f,k,vals[k]);});
    }catch(_){}
  },true);
  if(window.jQuery){jQuery(document).on('shown.bs.modal','.modal',function(){injectAllForms(this);});}
})();