/* Local fixtures only: shared customer area layout and non-submitting UI interactions. */
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
(async()=>{
 const dir=path.resolve(__dirname,'../../writable/cache/home-flash-review'),port=fs.readFileSync(path.join(dir,'chrome-profile/DevToolsActivePort'),'utf8').split('\n')[0];
 const tabs=await(await fetch(`http://127.0.0.1:${port}/json/list`)).json(),ws=new WebSocket(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);await new Promise(r=>ws.onopen=r);
 let seq=0,checks=0;const pending=new Map();
 ws.onmessage=e=>{const m=JSON.parse(e.data);if(m.id){const p=pending.get(m.id);if(!p)return;pending.delete(m.id);clearTimeout(p.timer);m.error?p.reject(new Error(m.error.message)):p.resolve(m.result)}};
 const call=(method,params={})=>new Promise((resolve,reject)=>{const id=++seq,timer=setTimeout(()=>reject(new Error('Timeout '+method)),10000);pending.set(id,{resolve,reject,timer});ws.send(JSON.stringify({id,method,params}))});
 const wait=ms=>new Promise(r=>setTimeout(r,ms));
 const evaluate=async expression=>{const r=await call('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.text);return r.result.value};
 try{
 const runtimeErrors=[];ws.addEventListener('message',e=>{const m=JSON.parse(e.data);if(m.method==='Runtime.exceptionThrown')runtimeErrors.push(m.params.exceptionDetails.text)});
 await call('Page.enable');await call('Runtime.enable');await call('Network.enable');await call('Network.setBlockedURLs',{urls:['http://*','https://*']});



 await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]});
 const navigate=async(state,theme)=>{await call('Page.navigate',{url:'file:///'+path.join(dir,`${state}-${theme}.html`).replaceAll('\\','/')});await wait(200)};
 for(const state of ['single','normal','long','expired'])for(const theme of ['default','sand']){
  await navigate(state,theme);
  for(const width of [320,360,390,430,600,768,991,1024,1440]){
   await call('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<768});await wait(100);await evaluate(`window.scrollTo({top:0,left:0,behavior:'instant'})`);
   const a=await evaluate(`(()=>{const f=document.querySelector('.flash-sale-section'),r=e=>{const b=e.getBoundingClientRect();return {left:b.left,right:b.right,top:b.top,bottom:b.bottom,width:b.width,height:b.height}},s=e=>getComputedStyle(e),title=f.querySelector('h2'),shell=f.querySelector('.flash-sale-shell'),timer=f.querySelector('.flash-countdown');return {width:document.documentElement.scrollWidth,shell:{shadow:s(shell).boxShadow,bg:s(shell).backgroundImage,border:s(shell).borderTopWidth},title:{rect:r(title),size:parseFloat(s(title).fontSize),weight:s(title).fontWeight},timer:{rect:r(timer),shadow:s(timer).boxShadow,digits:[...timer.querySelectorAll('[data-unit]')].map(e=>e.textContent)},cta:r(f.querySelector('.flash-sale-see-all')),card:[...f.querySelectorAll('.flash-sale-card')].map(e=>({shadow:s(e).boxShadow,price:r(e.querySelector('.flash-sale-price')),title:e.querySelector('h3').title,clamp:s(e.querySelector('h3')).webkitLineClamp,fit:s(e.querySelector('img:not(.flash-sale-watermark)')).objectFit})),first:r(f.querySelector('.flash-sale-card')),prev:f.querySelector('[data-flash-prev]').disabled,next:f.querySelector('[data-flash-next]').disabled,nav:[...f.querySelectorAll('.flash-sale-nav')].map(r)}})()`);
   assert.ok(a.width<=width+1,`${state}/${theme}/${width}: overflow ${a.width}`);assert.equal(a.shell.shadow,'none');assert.equal(a.shell.bg,'none');assert.equal(a.shell.border,'0px');assert.equal(a.title.weight,'500');assert.ok(a.title.size>=18&&a.title.size<=22&&a.title.rect.right<=width+1);assert.equal(a.timer.shadow,'none');assert.ok(a.timer.rect.right<=width&&a.cta.right<=width&&a.cta.height>=44);assert.ok(a.first.width>=130);assert.equal(a.prev,true);
   for(const card of a.card){assert.equal(card.shadow,'none');assert.equal(card.clamp,'2');assert.equal(card.fit,'contain');assert.ok(card.title);assert.ok(Math.abs(card.price.top-a.card[0].price.top)<2)}
   a.timer.digits.forEach(n=>assert.match(n,/^\d{2,}$/));if(state==='expired')assert.deepEqual(a.timer.digits,['00','00','00']);if(state==='single')assert.equal(a.next,true);
   if(theme==='sand'&&state==='normal'&&[390,1440].includes(width)){const shot=await call('Page.captureScreenshot',{format:'png'});fs.writeFileSync(path.join(dir,`flash-sale-${width}.png`),Buffer.from(shot.data,'base64'))}checks++;
  }
 }
 for(const theme of ['default','sand']){
  await navigate('normal',theme);await call('Emulation.setDeviceMetricsOverride',{width:1440,height:900,deviceScaleFactor:1,mobile:false});await wait(150);
  const click=async selector=>{const p=await evaluate(`(()=>{const r=document.querySelector(${JSON.stringify(selector)}).getBoundingClientRect();return {x:r.x+r.width/2,y:r.y+r.height/2}})()`);for(const type of ['mousePressed','mouseReleased'])await call('Input.dispatchMouseEvent',{type,...p,button:'left',clickCount:1});await wait(250)};
  await click('[data-flash-next]');assert.ok(await evaluate(`document.querySelector('[data-flash-products]').scrollLeft>0`));assert.equal(await evaluate(`document.querySelector('[data-flash-prev]').disabled`),false);
  await click('[data-flash-prev]');assert.ok(await evaluate(`document.querySelector('[data-flash-products]').scrollLeft<=1`));checks++;
 }
 assert.deepEqual(runtimeErrors,[]);console.log(`PASS: ${checks} Flash Sale layout/interaction cases, 2 themes and 9 widths; expired/single/long settings included; no API requests.`);
 }finally{ws.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
