/* Local fixtures only: editorial About page across themes and responsive widths. */
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
(async()=>{
 const dir=path.resolve(__dirname,'../../writable/cache/buyer-about-review'),port=fs.readFileSync(path.join(dir,'chrome-profile/DevToolsActivePort'),'utf8').split('\n')[0];
 const tabs=await(await fetch(`http://127.0.0.1:${port}/json/list`)).json(),ws=new WebSocket(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);await new Promise(r=>ws.onopen=r);
 let seq=0,checks=0;const pending=new Map();
 ws.onmessage=e=>{const m=JSON.parse(e.data);if(m.id){const p=pending.get(m.id);if(!p)return;pending.delete(m.id);clearTimeout(p.timer);m.error?p.reject(new Error(m.error.message)):p.resolve(m.result)}};
 const call=(method,params={})=>new Promise((resolve,reject)=>{const id=++seq,timer=setTimeout(()=>reject(new Error('Timeout '+method)),10000);pending.set(id,{resolve,reject,timer});ws.send(JSON.stringify({id,method,params}))});
 const wait=ms=>new Promise(r=>setTimeout(r,ms));
 const evaluate=async expression=>{const r=await call('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.text);return r.result.value};
 try{
 await call('Page.enable');await call('Runtime.enable');await call('Network.enable');await call('Network.setBlockedURLs',{urls:['http://*','https://*']});
 for(const [theme,color] of Object.entries({default:'rgb(36, 59, 107)',sand:'rgb(47, 45, 32)'})){
  await call('Page.navigate',{url:'file:///'+path.join(dir,theme+'.html').replaceAll('\\','/')});await wait(250);
  for(const width of [320,360,390,430,600,768,991,1024,1440]){
   await call('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<768});await wait(100);await evaluate(`window.scrollTo({top:0,left:0,behavior:'instant'})`);
   const a=await evaluate(`(()=>{const s=e=>getComputedStyle(e),r=e=>e.getBoundingClientRect(),main=document.querySelector('main'),img=main.querySelector('figure img'),h=main.querySelector('h1');return {scroll:document.documentElement.scrollWidth,mainTop:r(main).top,mainBottom:r(main).bottom,headerBottom:r(document.querySelector(innerWidth<992?'.buyer-mobile-header':'.buyer-desktop-header')).bottom,footerTop:r(document.querySelector('footer')).top,color:s(h).color,headingSize:parseFloat(s(h).fontSize),image:{loaded:img.naturalWidth>0,ratio:r(img).width/r(img).height,natural:img.naturalWidth/img.naturalHeight,fit:s(img).objectFit},columns:s(main.querySelector('.about-values-list')).gridTemplateColumns,values:[...main.querySelectorAll('.about-value')].map(e=>({shadow:s(e).boxShadow,bg:s(e).backgroundImage,radius:s(e).borderRadius,scroll:e.scrollWidth,width:r(e).width})),links:[...main.querySelectorAll('a')].map(e=>({height:r(e).height,right:r(e).right})),stats:[...main.querySelectorAll('dd')].map(e=>({top:r(e).top,scroll:e.scrollWidth,width:r(e).width}))}})()`);
   assert.ok(a.scroll<=width+1,`${theme}/${width}: overflow`);assert.ok(a.mainTop>=a.headerBottom-1);assert.ok(a.footerTop>=a.mainBottom-1);assert.equal(a.color,color);assert.ok(a.headingSize<=40);assert.ok(a.image.loaded);assert.equal(a.image.fit,'contain');assert.ok(Math.abs(a.image.ratio-a.image.natural)<.01);assert.equal(a.columns.split(' ').length,width<768?1:3);a.values.forEach(v=>{assert.equal(v.shadow,'none');assert.equal(v.bg,'none');assert.equal(v.radius,'0px');assert.ok(v.scroll<=v.width+1)});a.links.forEach(l=>{assert.ok(l.height>=44);assert.ok(l.right<=width+1)});a.stats.forEach(t=>{assert.ok(t.scroll<=t.width+1);assert.ok(Math.abs(t.top-a.stats[0].top)<1,'Stats align')});checks++;
   if([390,1440].includes(width)){const shot=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});fs.writeFileSync(path.join(dir,`${theme}-${width}.png`),Buffer.from(shot.data,'base64'));if(width===1440){const full=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:true,clip:{x:0,y:0,width,height:Math.ceil(a.mainBottom+10),scale:1}});fs.writeFileSync(path.join(dir,`${theme}-page.png`),Buffer.from(full.data,'base64'))}}
  }
 }
 console.log(`PASS: ${checks} About layout/theme checks: image ratio, content, spacing, stats and no-card design across nine widths.`);
 }finally{ws.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
