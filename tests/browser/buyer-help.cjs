/* Local fixtures only: shared customer area layout and non-submitting UI interactions. */
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
(async()=>{
 const dir=path.resolve(__dirname,'../../writable/cache/buyer-help-review'),port=fs.readFileSync(path.join(dir,'chrome-profile/DevToolsActivePort'),'utf8').split('\n')[0];
 const tabs=await(await fetch(`http://127.0.0.1:${port}/json/list`)).json(),ws=new WebSocket(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);await new Promise(r=>ws.onopen=r);
 let seq=0,checks=0;const pending=new Map();
 ws.onmessage=e=>{const m=JSON.parse(e.data);if(m.id){const p=pending.get(m.id);if(!p)return;pending.delete(m.id);clearTimeout(p.timer);m.error?p.reject(new Error(m.error.message)):p.resolve(m.result)}};
 const call=(method,params={})=>new Promise((resolve,reject)=>{const id=++seq,timer=setTimeout(()=>reject(new Error('Timeout '+method)),10000);pending.set(id,{resolve,reject,timer});ws.send(JSON.stringify({id,method,params}))});
 const wait=ms=>new Promise(r=>setTimeout(r,ms));
 const evaluate=async expression=>{const r=await call('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.text);return r.result.value};
 try{
 const runtimeErrors=[];ws.addEventListener('message',e=>{const m=JSON.parse(e.data);if(m.method==='Runtime.exceptionThrown')runtimeErrors.push(m.params.exceptionDetails.text)});
 await call('Page.enable');await call('Runtime.enable');await call('Network.enable');await call('Network.setBlockedURLs',{urls:['http://*','https://*']});

 const cases=[['faq','initial'],['syaratdanketentuan','initial'],['kebijakanprivasi','initial'],['lacakPengiriman','initial'],['lacakPengiriman','error'],['lacakPengiriman','success'],['lacakPengiriman','internal'],['lacakPengiriman','no-history']];
 const navigate=async(page,state,theme,query='')=>{await call('Page.navigate',{url:'file:///'+path.join(dir,`${page}-${state}-${theme}.html`).replaceAll('\\','/')+query});await wait(200)};
 for(const [page,state] of cases)for(const theme of ['default','sand']){
  await navigate(page,state,theme);
  for(const width of [320,360,390,430,600,768,991,1024,1440]){
   await call('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<768});await wait(100);await evaluate(`window.scrollTo({top:0,left:0,behavior:'instant'})`);
   const a=await evaluate(`(()=>{const main=document.querySelector('main.buyer-help-page'),r=e=>{const b=e.getBoundingClientRect();return {left:b.left,right:b.right,top:b.top,bottom:b.bottom,height:b.height}},s=e=>getComputedStyle(e),h=[...document.querySelectorAll('.buyer-mobile-header,.buyer-desktop-header')].find(e=>s(e).display!=='none');return {width:document.documentElement.scrollWidth,main:r(main),footer:r(document.querySelector('footer')),header:r(h),h1:parseFloat(s(main.querySelector('h1')).fontSize),links:[...main.querySelectorAll('.buyer-help-nav a')].map(r),controls:[...main.querySelectorAll('input,select,button')].map(r),grads:[...main.querySelectorAll('.tracking-hero-card,.tracking-summary,.tracking-timeline-card')].map(e=>s(e).backgroundImage)}})()`);
   assert.ok(a.width<=width+1,`${page}/${state}/${theme}/${width}: overflow ${a.width}`);assert.ok(a.main.top>=a.header.bottom-1,'Content below header');assert.ok(a.footer.top>=a.main.bottom-1,'Footer below help content');assert.ok(a.h1<=32);
   for(const b of [...a.links,...a.controls])assert.ok(b.left>=0&&b.right<=width+1&&b.height>=44,`${page}/${width}: accessible controls`);
   a.grads.forEach(g=>assert.equal(g,'none'));checks++;
   if(theme==='sand'&&[390,1440].includes(width)&&['initial','success'].includes(state)){const shot=await call('Page.captureScreenshot',{format:'png'});fs.writeFileSync(path.join(dir,`${page}-${state}-${width}.png`),Buffer.from(shot.data,'base64'))}
  }
 }
 await call('Emulation.setDeviceMetricsOverride',{width:390,height:844,deviceScaleFactor:1,mobile:true});
 for(const query of ['?a=6#flush-collapse6','?a=999','?a=abc','?a=-1']){await navigate('faq','initial','sand',query);assert.equal(await evaluate(`document.querySelectorAll('.accordion-collapse.show').length`),query.startsWith('?a=6#')?1:0);checks++}
 await navigate('faq','initial','sand');
 const rect=await evaluate(`(()=>{const e=document.querySelector('.accordion-button');e.scrollIntoView({block:'center',behavior:'instant'});const r=e.getBoundingClientRect();return {x:r.x+r.width/2,y:r.y+r.height/2}})()`);
 for(const type of ['mousePressed','mouseReleased'])await call('Input.dispatchMouseEvent',{type,...rect,button:'left',clickCount:1});await wait(400);
 assert.equal(await evaluate(`document.querySelector('#flush-collapse1').classList.contains('show')`),true);assert.equal(await evaluate(`document.documentElement.scrollWidth<=innerWidth+1`),true);checks++;
 assert.deepEqual(runtimeErrors,[]);console.log(`PASS: ${checks} local help layout and interaction cases; 2 themes, 9 widths, no API calls or form submissions.`);
 }finally{ws.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
