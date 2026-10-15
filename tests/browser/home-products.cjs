/* Local fixtures only: shared customer area layout and non-submitting UI interactions. */
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
(async()=>{
 const dir=path.resolve(__dirname,'../../writable/cache/home-products-review'),port=fs.readFileSync(path.join(dir,'chrome-profile/DevToolsActivePort'),'utf8').split('\n')[0];
 const tabs=await(await fetch(`http://127.0.0.1:${port}/json/list`)).json(),ws=new WebSocket(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);await new Promise(r=>ws.onopen=r);
 let seq=0,checks=0;const pending=new Map();
 ws.onmessage=e=>{const m=JSON.parse(e.data);if(m.id){const p=pending.get(m.id);if(!p)return;pending.delete(m.id);clearTimeout(p.timer);m.error?p.reject(new Error(m.error.message)):p.resolve(m.result)}};
 const call=(method,params={})=>new Promise((resolve,reject)=>{const id=++seq,timer=setTimeout(()=>reject(new Error('Timeout '+method)),10000);pending.set(id,{resolve,reject,timer});ws.send(JSON.stringify({id,method,params}))});
 const wait=ms=>new Promise(r=>setTimeout(r,ms));
 const evaluate=async expression=>{const r=await call('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.text);return r.result.value};
 try{
 const runtimeErrors=[];ws.addEventListener('message',e=>{const m=JSON.parse(e.data);if(m.method==='Runtime.exceptionThrown')runtimeErrors.push(m.params.exceptionDetails.text)});
 await call('Page.enable');await call('Runtime.enable');await call('Network.enable');await call('Network.setBlockedURLs',{urls:['http://*','https://*']});


 for(const theme of ['default','sand']){
  await call('Page.navigate',{url:'file:///'+path.join(dir,theme+'.html').replaceAll('\\','/')});await wait(200);
  for(const width of [320,360,390,430,600,768,991,1024,1440]){
   await call('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<768});await wait(100);await evaluate(`window.scrollTo({top:0,left:0,behavior:'instant'})`);
   const a=await evaluate(`(()=>{const r=e=>{const b=e.getBoundingClientRect();return {left:b.left,right:b.right,top:b.top,bottom:b.bottom,width:b.width,height:b.height}},sections=[...document.querySelectorAll('.home-product-section')];return {width:document.documentElement.scrollWidth,sections:sections.map(e=>({title:e.querySelector('h2').textContent,size:parseFloat(getComputedStyle(e.querySelector('h2')).fontSize),weight:getComputedStyle(e.querySelector('h2')).fontWeight,font:getComputedStyle(e.querySelector('h2')).fontFamily,heading:r(e.querySelector('h2')),link:r(e.querySelector('.home-product-section-link')),cards:[...e.querySelectorAll('.store-product-card')].map(c=>({rect:r(c),price:r(c.querySelector('.harga')),shadow:getComputedStyle(c).boxShadow})),rect:r(e)}))}})()`);
   assert.ok(a.width<=width+1,`Overflow at ${width}`);assert.equal(a.sections.length,2);assert.deepEqual(a.sections.map(s=>s.title),['Produk Terpopuler','Produk Terbaru']);
   for(const section of a.sections){assert.ok(section.size>=18&&section.size<=22);assert.equal(section.weight,'500');assert.ok(section.font.includes('Plus Jakarta Sans'));assert.equal(section.cards.length,10);assert.ok(section.link.height>=44&&section.link.right<=width);assert.ok(section.heading.right<=section.link.left||section.heading.bottom<=section.link.top);for(const c of section.cards){assert.ok(c.rect.right<=width+1);assert.equal(c.shadow,'none')}for(let i=1;i<section.cards.length;i++){const x=section.cards[i-1],y=section.cards[i];if(Math.abs(x.rect.top-y.rect.top)<2)assert.ok(Math.abs(x.price.top-y.price.top)<2,'Prices stay aligned')}}
   assert.ok(a.sections[1].rect.top>=a.sections[0].rect.bottom);
   if(theme==='sand'&&[390,1440].includes(width)){for(const id of ['popular','latest']){await evaluate(`(()=>{const e=document.getElementById('home-${id}-products'),header=[...document.querySelectorAll('.buyer-mobile-header,.buyer-desktop-header')].find(e=>getComputedStyle(e).display!=='none');window.scrollTo({top:scrollY+e.getBoundingClientRect().top-header.getBoundingClientRect().bottom-20,behavior:'instant'})})()`);await wait(150);const shot=await call('Page.captureScreenshot',{format:'png'});fs.writeFileSync(path.join(dir,`${id}-${width}.png`),Buffer.from(shot.data,'base64'))}}
   checks++;
  }
 }
 assert.deepEqual(runtimeErrors,[]);console.log(`PASS: ${checks} home collection layouts, 2 themes and 9 widths; no remote requests.`);
 }finally{ws.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
