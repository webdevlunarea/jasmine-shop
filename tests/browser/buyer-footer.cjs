/* Local fixtures only: adaptive footer layout and mobile bottom-navigation clearance. */
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
(async()=>{
 const dir=path.resolve(__dirname,'../../writable/cache/buyer-footer-review'),port=fs.readFileSync(path.join(dir,'chrome-profile/DevToolsActivePort'),'utf8').split('\n')[0];
 const tabs=await(await fetch(`http://127.0.0.1:${port}/json/list`)).json(),ws=new WebSocket(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);await new Promise(r=>ws.onopen=r);
 let seq=0,checks=0;const pending=new Map();
 ws.onmessage=e=>{const m=JSON.parse(e.data);if(m.id){const p=pending.get(m.id);if(!p)return;pending.delete(m.id);clearTimeout(p.timer);m.error?p.reject(new Error(m.error.message)):p.resolve(m.result)}};
 const call=(method,params={})=>new Promise((resolve,reject)=>{const id=++seq,timer=setTimeout(()=>reject(new Error('Timeout '+method)),10000);pending.set(id,{resolve,reject,timer});ws.send(JSON.stringify({id,method,params}))});
 const wait=ms=>new Promise(r=>setTimeout(r,ms));
 const evaluate=async expression=>{const r=await call('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.text);return r.result.value};
 try{
 await call('Page.enable');await call('Runtime.enable');await call('Network.enable');await call('Network.setBlockedURLs',{urls:['http://*','https://*']});
 for(const name of ['full','compact','minimal','payment'])for(const length of ['short','long']){
  await call('Page.navigate',{url:'file:///'+path.join(dir,`${name}-${length}.html`).replaceAll('\\','/')});await wait(200);
  for(const width of [320,360,390,430,600,768,991,1024,1440])for(const height of [600,1000]){
   await call('Emulation.setDeviceMetricsOverride',{width,height,deviceScaleFactor:1,mobile:width<768});await wait(100);await evaluate(`window.scrollTo({top:document.documentElement.scrollHeight,left:0,behavior:'instant'})`);await wait(60);
   const result=await evaluate(`(()=>{const s=e=>getComputedStyle(e),r=e=>{const b=e.getBoundingClientRect();return {top:b.top,bottom:b.bottom,left:b.left,right:b.right}},f=document.querySelector('footer'),n=document.querySelector('.mobile-bottom-nav');return {viewport:innerHeight,bottomPadding:parseFloat(s(document.body).paddingBottom)||0,scroll:document.documentElement.scrollWidth,count:document.querySelectorAll('footer').length,footer:r(f),main:r(document.querySelector('main')),position:s(f).position,nav:n&&s(n).display!=='none'?r(n):null,links:[...f.querySelectorAll('a')].map(e=>({rect:r(e),height:e.getBoundingClientRect().height})),variant:f.className}})()`);
   assert.equal(result.count,1);assert.ok(result.scroll<=width+1,`${name}/${length}/${width}: horizontal overflow`);assert.ok(result.footer.top>=result.main.bottom-1,`${name}: footer overlaps content`);assert.notEqual(result.position,'fixed');assert.notEqual(result.position,'absolute');assert.ok(Math.abs(result.footer.bottom-(result.viewport-result.bottomPadding))<2,`${name}/${length}/${width}: footer must reach the bottom of available viewport`);
   if(result.nav)assert.ok(result.footer.bottom<=result.nav.top+1,`${name}/${width}: bottom nav covers footer`);
   assert.ok(result.variant.includes(name==='payment'?'minimal':name));result.links.forEach(l=>{assert.ok(l.height>=44,`${name}/${width}: link touch target`);assert.ok(l.rect.left>=-1&&l.rect.right<=width+1)})
   if(name==='full'){
    const clean=await evaluate(`(()=>{const f=document.querySelector('footer');return {shadow:getComputedStyle(f).boxShadow,gradient:getComputedStyle(f).backgroundImage,logos:f.querySelectorAll('.buyer-footer__payment-logos img').length,headings:[...f.querySelectorAll('h2')].map(e=>parseFloat(getComputedStyle(e).fontSize))}})()`);
    assert.equal(clean.shadow,'none');assert.equal(clean.gradient,'none');assert.equal(clean.logos,8);clean.headings.forEach(size=>assert.ok(size<=14));
    if(length==='short'&&height===1000&&[390,1440].includes(width)){const clip=await evaluate(`(()=>{const r=document.querySelector('footer').getBoundingClientRect();return {x:r.left,y:r.top+scrollY,width:r.width,height:r.height,scale:1}})()`);const shot=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:true,clip});fs.writeFileSync(path.join(dir,`full-footer-${width}.png`),Buffer.from(shot.data,'base64'))}
   }
   checks++;
   if(length==='short'&&width===390){const shot=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});fs.writeFileSync(path.join(dir,`${name}-mobile.png`),Buffer.from(shot.data,'base64'))}
  }
 }
 await call('Page.navigate',{url:'file:///'+path.join(dir,'full-short.html').replaceAll('\\','/')});await wait(200);
 await evaluate(`(()=>{const style=document.createElement('style');style.textContent=':root,*{--theme-primary:#2F2D20;--theme-primary-hover:#232217;--theme-soft:#F7F2ED;--theme-soft-2:#DFCBB9;--theme-background:#FEFEFF}';document.head.append(style)})()`);
 for(const width of [390,1440]){
  await call('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<768});await wait(100);
  const sand=await evaluate(`(()=>{const f=document.querySelector('footer');return {background:getComputedStyle(f).backgroundColor,heading:getComputedStyle(f.querySelector('h2')).color,overflow:document.documentElement.scrollWidth>innerWidth+1}})()`);
  assert.equal(sand.background,'rgb(247, 242, 237)');assert.equal(sand.heading,'rgb(47, 45, 32)');assert.equal(sand.overflow,false);checks++;
 }
 await call('Page.navigate',{url:'file:///'+path.join(dir,'payment-short.html').replaceAll('\\','/')});await wait(200);await evaluate(`document.querySelector('footer a').focus()`);await call('Input.dispatchKeyEvent',{type:'keyDown',key:'Tab',code:'Tab',windowsVirtualKeyCode:9});await call('Input.dispatchKeyEvent',{type:'keyUp',key:'Tab',code:'Tab',windowsVirtualKeyCode:9});assert.equal(await evaluate(`getComputedStyle(document.activeElement).outlineStyle!=='none'`),true);
 console.log(`PASS: ${checks} footer layout/clearance checks across four variants, short/long pages, two viewport heights and nine widths, plus Sand theme at mobile/desktop; keyboard focus visible.`);
 }finally{ws.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
