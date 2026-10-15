/* Local fixtures only: shared customer area layout and non-submitting UI interactions. */
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
(async()=>{
 const dir=path.resolve(__dirname,'../../writable/cache/buyer-account-review'),port=fs.readFileSync(path.join(dir,'chrome-profile/DevToolsActivePort'),'utf8').split('\n')[0];
 const tabs=await(await fetch(`http://127.0.0.1:${port}/json/list`)).json(),ws=new WebSocket(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);await new Promise(r=>ws.onopen=r);
 let seq=0,checks=0;const pending=new Map();
 ws.onmessage=e=>{const m=JSON.parse(e.data);if(m.id){const p=pending.get(m.id);if(!p)return;pending.delete(m.id);clearTimeout(p.timer);m.error?p.reject(new Error(m.error.message)):p.resolve(m.result)}};
 const call=(method,params={})=>new Promise((resolve,reject)=>{const id=++seq,timer=setTimeout(()=>reject(new Error('Timeout '+method)),10000);pending.set(id,{resolve,reject,timer});ws.send(JSON.stringify({id,method,params}))});
 const wait=ms=>new Promise(r=>setTimeout(r,ms));
 const evaluate=async expression=>{const r=await call('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.text);return r.result.value};
 try{
 const runtimeErrors=[];ws.addEventListener('message',e=>{const m=JSON.parse(e.data);if(m.method==='Runtime.exceptionThrown')runtimeErrors.push(m.params.exceptionDetails.text)});
 await call('Page.enable');await call('Runtime.enable');await call('Network.enable');await call('Network.setBlockedURLs',{urls:['http://*','https://*']});
 const pages=['account','wishlist','cart','voucher','point','pointHistory','transaction'];
 for(const page of pages)for(const state of ['empty','filled']){
  await call('Page.navigate',{url:'file:///'+path.join(dir,`${page}-${state}.html`).replaceAll('\\','/')});await wait(200);
  for(const width of [320,360,390,430,600,768,991,1024,1440]){
   await call('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<768});await wait(100);await evaluate(`window.scrollTo({top:0,left:0,behavior:'instant'})`);
   const a=await evaluate(`(()=>{const s=e=>getComputedStyle(e),r=e=>{const b=e.getBoundingClientRect();return {top:b.top,bottom:b.bottom,left:b.left,right:b.right,width:b.width,height:b.height}},content=document.querySelector('.account-content-card'),outer=document.querySelector('.konten'),tabs=document.querySelector('.account-mobile-tabs'),active=tabs.querySelector('[aria-current]'),side=document.querySelector('.account-side-card');return {scroll:document.documentElement.scrollWidth,content:r(content),main:r(outer),footer:r(document.querySelector('footer')),shadow:s(content).boxShadow,border:s(content).borderTopWidth,head:parseFloat(s(document.querySelector('h1')).fontSize),tabs:{display:s(tabs).display,rect:r(tabs),active:r(active)},side:s(side).display,controls:[...content.querySelectorAll('.btn,.form-control')].filter(e=>r(e).height>0).map(e=>({rect:r(e)})),hero:[...document.querySelectorAll('.account-hero,.luna-point-hero,.luna-point-balance')].map(e=>s(e).backgroundImage)}})()`);
   assert.ok(a.scroll<=width+1,`${page}/${state}/${width}: overflow ${a.scroll}`);assert.ok(a.content.left>=0&&a.content.right<=width+1);assert.ok(a.footer.top>=a.main.bottom-1);assert.equal(a.shadow,'none');assert.equal(a.border,'0px');assert.ok(a.head<=24);a.hero.forEach(bg=>assert.equal(bg,'none'));
   if(width<992){assert.equal(a.side,'none');assert.equal(a.tabs.display,'flex');assert.ok(a.tabs.active.left>=a.tabs.rect.left-1&&a.tabs.active.right<=a.tabs.rect.right+1,'Active menu visible')}
   else{assert.notEqual(a.side,'none');assert.equal(a.tabs.display,'none')}
   a.controls.forEach(c=>{assert.ok(c.rect.height>=44,`${page}/${width}: touch target`);assert.ok(c.rect.right<=width+1,`${page}/${width}: control overflow`)});checks++;
   if(state==='filled'&&[390,1440].includes(width)){const shot=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});fs.writeFileSync(path.join(dir,`${page}-${width}.png`),Buffer.from(shot.data,'base64'))}
  }
 }
 const click=async selector=>{const p=await evaluate(`(()=>{const e=document.querySelector(${JSON.stringify(selector)});e.scrollIntoView({block:'center',behavior:'instant'});const r=e.getBoundingClientRect();return {x:r.x+r.width/2,y:r.y+r.height/2}})()`);for(const type of ['mousePressed','mouseReleased'])await call('Input.dispatchMouseEvent',{type,...p,button:'left',clickCount:1});await wait(150)};
 await call('Page.navigate',{url:'file:///'+path.join(dir,'voucher-filled.html').replaceAll('\\','/')});await wait(200);await call('Emulation.setDeviceMetricsOverride',{width:390,height:844,deviceScaleFactor:1,mobile:true});await click('.voucher-heading button');assert.equal(await evaluate(`document.activeElement.id`),'redeem-code');assert.equal(await evaluate(`document.body.style.overflow`),'hidden');assert.equal(await evaluate(`(()=>{const m=document.querySelector('#modal-redeem'),r=m.firstElementChild.getBoundingClientRect();return r.left>=0&&r.right<=innerWidth&&parseInt(getComputedStyle(m).zIndex)>1080&&parseFloat(getComputedStyle(m.querySelector('input')).fontSize)>=16})()`),true);await call('Input.dispatchKeyEvent',{type:'keyDown',key:'Escape',code:'Escape',windowsVirtualKeyCode:27});await call('Input.dispatchKeyEvent',{type:'keyUp',key:'Escape',code:'Escape',windowsVirtualKeyCode:27});assert.equal(await evaluate(`document.getElementById('modal-redeem').classList.contains('d-none')`),true);assert.equal(await evaluate(`document.body.style.overflow`),'');await click('#btn-redeem1');assert.equal(await evaluate(`document.querySelector('.input-redeem.show')!==null`),true);checks++;
 await call('Page.navigate',{url:'file:///'+path.join(dir,'transaction-filled.html').replaceAll('\\','/')});await wait(200);await click('.accordion-button');await wait(350);assert.equal(await evaluate(`document.getElementById('collapse0').classList.contains('show')`),true);assert.equal(await evaluate(`document.documentElement.scrollWidth<=innerWidth+1`),true);checks++;
 assert.deepEqual(runtimeErrors,[]);console.log(`PASS: ${checks} customer layout/interaction cases across 7 pages, empty/filled data and 9 widths; no remote requests or form submissions.`);
 }finally{ws.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
