!function(){var Tool={},Hanlder={},Track=null;Tool.request=function(i){var e={url:i.url,data:i.data};if("string"!=typeof e.data){var r=e.data,t=[];for(var a in r)"uri"===a||"kws"===a?t.push(a+"="+encodeURIComponent(r[a])):t.push(a+"="+r[a]);kv=t.join("&")}else kv=r;if(o)o.src=e.url+"?"+kv+"&t="+(new Date).getTime();else{var o=new Image;o.src=e.url+"?"+kv+"&t="+(new Date).getTime()}},Tool.getBrowserInfo=function(){var i=navigator.userAgent.toLowerCase(),e=/msie [\d.]+;/gi,r=/firefox\/[\d.]+/gi,t=/chrome\/[\d.]+/gi,a=/safari\/[\d.]+/gi,o=/android\s+[\d.]+/gi,n=/version\/[\d.]+/gi;if(i.indexOf("android")>0)return i.match(o)[0];if(i.indexOf("iphone")>0){var d="",s="iphone";return i.match(n)&&i.match(n)[0]&&(d=i.match(n)[0].replace("version","")),s+=d}return i.indexOf("msie")>0?i.match(e)[0]:i.indexOf("firefox")>0?i.match(r)[0]:i.indexOf("chrome")>0?i.match(t)[0]:i.indexOf("safari")>0&&i.indexOf("chrome")<0?i.match(a)[0]:void 0},Tool.uuid=function(i,e){var r,t="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz".split(""),a=t,o=[];if(e=e||a.length,i)for(r=0;i>r;r++)o[r]=a[0|Math.random()*e];else{var n;for(o[8]=o[13]=o[18]=o[23]="-",o[14]="4",r=0;36>r;r++)o[r]||(n=0|16*Math.random(),o[r]=a[19==r?3&n|8:n])}return o.join("")},Tool.cookie={setCookie:function(i,e,r,t){var a=1095,o=new Date;o.setTime(o.getTime()+24*a*60*60*1e3),document.cookie=i+"="+escape(e)+";expires="+o.toGMTString()+";path="+r+";domain="+t+";"},getCookie:function(i){var e,r=new RegExp("(^| )"+i+"=([^;]*)(;|$)");return(e=document.cookie.match(r))?unescape(e[2]):null},delCookie:function(i){var e=new Date;e.setTime(e.getTime()-1);var r=getCookie(i);null!=r&&(document.cookie=i+"="+r+";expires="+e.toGMTString()+";")}},Tool.subUrlParams=function(char){var isExistchar=char||void 0,json=null,URL=location.href,URLParamsStr="",URLParamsKVArr=[];if(-1==URL.indexOf("?"))return json;URLParamsStr=void 0!=isExistchar?URL.substring(URL.indexOf("?")+1,URL.indexOf(isExistchar)):URL.substring(URL.indexOf("?")+1),-1!=URLParamsStr.indexOf("&")?URLParamsKVArr=URLParamsStr.split("&"):URLParamsKVArr.push(URLParamsStr);for(var jsonStr="{",json=null,i=0;i<URLParamsKVArr.length;i++){var arr=URLParamsKVArr[i].split("=");jsonStr+=i!=URLParamsKVArr.length-1?"'"+arr[0]+"':'"+arr[1]+"',":"'"+arr[0]+"':'"+arr[1]+"'"}return jsonStr+="}",json=eval("("+jsonStr+")")},Hanlder.websiteHanlder=function(i){var e={kid:"",src:"",uid:"",ref:"",kws:"",gid:"",vid:""},r=Tool.cookie.getCookie("_uc_id_");r?e.kid=r:(r="_uc_id__"+Tool.uuid(),Tool.cookie.setCookie("_uc_id_",r,"/",".baidu.com"),e.kid=r),window.hasOwnProperty("initParam")&&window.initParam.hasOwnProperty("kid")&&window.initParam.kid&&(e.kid=window.initParam.kid);var t=Tool.cookie.getCookie("channel");if(t)e.src=t;else{var a=void 0,o=Tool.subUrlParams();o&&(a=o.utm_source),a&&(Tool.cookie.setCookie("utm_source",a,"/",".baidu.com"),e.src=a)}for(var n=document.getElementsByTagName("meta"),d=0;d<n.length;d++)n[d].name&&"keywords"==n[d].name&&(e.kws=n[d].content);return e.uid=i.uid,e.gid=i.gid,document.referrer&&(e.ref=document.referrer||""),e},Hanlder.phoneHanlder=function(i){var e={kid:"",src:"",uid:"",ref:"",kws:"",gid:"",vid:""};e.kid=i.deviceID+Tool.uuid(),e.uid=i.uid,e.gid=i.gid,e.vid=i.vid,window.hasOwnProperty("initParam")&&window.initParam.hasOwnProperty("kid")&&window.initParam.kid&&(e.kid=window.initParam.kid)},Track=function(i){var e={tid:i.tid,type:i.type};this.tid=e.tid,this.type=e.type,this.pid=Tool.uuid()+10*Math.random(),this.url="../hcnzj/bi_tbj.gif"},Track.prototype.setURL=function(i){this.url=i},Track.prototype.pageView=function(i){var e={lcm:i.lcm||"",deviceID:i.deviceID||"",uid:i.uid||"",gid:i.gid||"",vid:i.vid||""},r={tid:"",kid:"",uat:"",sys:"",src:"",scr:"",lcm:"",uid:"0",uri:"",ref:"",kws:"",gid:"",pid:"",net:"",vid:"",res:"",log:"pv"},t=this,a=null;r.tid=t.tid,"website"==t.type?a=Hanlder.websiteHanlder(e):"plugin"==t.type&&(a=Hanlder.phoneHanlder(e)),r.kid=a.kid,r.sys=navigator.platform,r.uat=Tool.getBrowserInfo(),r.src=a.src,r.scr=screen.width+" x "+screen.height,r.lcm=e.lcm,r.uid=a.uid,r.uri=location.href,r.ref=a.ref,r.kws=a.kws,r.gid=a.gid,r.vid=a.vid,r.pid=t.pid,Tool.request({url:t.url,type:"jsonp",data:r,success:function(i){alert("finsh")},error:function(i){}})},Track.prototype.eventView=function(i){var e={cat:i.cat,act:i.act,lab:i.lab||"",val:i.val||"",cva:i.cva||""},r={log:"evt",tid:"",cat:"",act:"",lab:"",val:"",cva:"",pid:""},t=this;r.cat=e.cat,r.act=e.act,r.lab=e.lab,r.val=e.val,r.cva=e.cva,r.tid=t.tid,r.pid=t.pid,Tool.request({url:t.url,data:r})};var TBJ_TRACK=Track;"function"==typeof define?define(function(i,e,r){r.exports=TBJ_TRACK}):window.TBJ_TRACK=TBJ_TRACK}();