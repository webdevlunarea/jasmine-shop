/* Local fixtures only: editorial About page across themes and responsive widths. */
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
(async()=>{
 const dir=path.resolve(__dirname,'../../writable/cache/buyer-contact-review'),port=fs.readFileSync(path.join(dir,'chrome-profile/DevToolsActivePort'),'utf8').split('\n')[0];
 const tabs=await(await fetch(`http://127.0.0.1:${port}/json/list`)).json(),ws=new WebSocket(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);await new Promise(r=>ws.onopen=r);
 let seq=0,checks=0;const pending=new Map(),errors=[];
 ws.onmessage=e=>{const m=JSON.parse(e.data);if(m.method==='Runtime.exceptionThrown')errors.push(m.params.exceptionDetails.text);if(m.id){const p=pending.get(m.id);if(!p)return;pending.delete(m.id);clearTimeout(p.timer);m.error?p.reject(new Error(m.error.message)):p.resolve(m.result)}};
 const call=(method,params={})=>new Promise((resolve,reject)=>{const id=++seq,timer=setTimeout(()=>reject(new Error('Timeout '+method)),10000);pending.set(id,{resolve,reject,timer});ws.send(JSON.stringify({id,method,params}))});
 const wait=ms=>new Promise(r=>setTimeout(r,ms));
 const evaluate=async expression=>{const r=await call('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.text);return r.result.value};



 try {
 await call('Page.enable');await call('Runtime.enable');await call('Network.enable');await call('Network.setBlockedURLs',{urls:['http://*','https://*']});
 for(const page of ['contact','form'])for(const state of ['empty','filled'])for(const theme of ['default','sand']){
  await call('Page.navigate',{url:'file:///'+path.join(dir,`${page}-${state}-${theme}.html`).replaceAll('\\','/')});await wait(150);
  for(const width of [320,360,390,430,600,768,991,1024,1440]){
   await call('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<768});await wait(60);
   await evaluate(`window.scrollTo({top:0,left:0,behavior:'instant'})`);
   const a=await evaluate(`(()=>{const m=document.querySelector('main'),r=e=>e.getBoundingClientRect(),s=e=>getComputedStyle(e),layout=m.querySelector('.contact-layout,.contact-form-layout');return {scroll:document.documentElement.scrollWidth,top:r(m).top,bottom:r(m).bottom,header:r(document.querySelector(innerWidth<992?'.buyer-mobile-header':'.buyer-desktop-header')).bottom,footer:r(document.querySelector('footer')).top,columns:s(layout).gridTemplateColumns.split(' ').length,color:s(m.querySelector('h1')).color,size:parseFloat(s(m.querySelector('h1')).fontSize),links:[...m.querySelectorAll('.contact-channel,.contact-faq-link,.contact-text-link,.contact-button')].map(e=>({height:r(e).height,right:r(e).right,shadow:s(e).boxShadow})),inputs:[...m.querySelectorAll('input:not([type="hidden"]),textarea')].map(e=>({font:parseFloat(s(e).fontSize),right:r(e).right,width:r(e).width})),title:m.querySelector('h1').textContent}})()`);
   assert.ok(a.scroll<=width+1,`${page}/${state}/${theme}/${width} horizontal overflow ${a.scroll}`);
   assert.ok(a.top>=a.header-1,'Main/header overlap');assert.ok(a.footer>=a.bottom-1,'Footer overlaps content');assert.equal(a.columns,width<768?1:2);
   assert.equal(a.color,theme==='sand'?'rgb(47, 45, 32)':'rgb(36, 59, 107)');assert.ok(a.size<=40);
   a.links.forEach(l=>{assert.ok(l.height>=44);assert.ok(l.right<=width+1);assert.equal(l.shadow,'none')});
   a.inputs.forEach(i=>{assert.ok(i.font>=16);assert.ok(i.width>100&&i.right<=width+1)});checks++;
   if(state==='filled'&&theme==='sand'&&[390,1440].includes(width)){
    const shot=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:true,clip:{x:0,y:0,width,height:Math.ceil(a.bottom+30),scale:1}});fs.writeFileSync(path.join(dir,`${page}-${width}.png`),Buffer.from(shot.data,'base64'));
   }
  }
  if(page==='form'){
   assert.equal(await evaluate(`document.querySelector('#btn-kirim').disabled`),true);
   await evaluate(`(()=>{const e=document.querySelector('[name="nama"]');e.value='   ';e.dispatchEvent(new Event('input',{bubbles:true}));const p=document.querySelector('[name="nohp"]');p.value='081234567890';p.dispatchEvent(new Event('input',{bubbles:true}))})()`);
   assert.equal(await evaluate(`document.querySelector('#btn-kirim').disabled`),true);
   await evaluate(`(()=>{const e=document.querySelector('[name="nama"]');e.value='Pelanggan Contoh';e.dispatchEvent(new Event('input',{bubbles:true}))})()`);
   assert.equal(await evaluate(`document.querySelector('#btn-kirim').disabled`),false);
   const submission=await evaluate(`(()=>{const f=document.querySelector('form#contact-message-form');window.testSubmissions=[];f.addEventListener('submit',event=>{event.preventDefault();window.testSubmissions.push(Object.fromEntries(new FormData(f)))});f.requestSubmit();return {data:window.testSubmissions[0],disabled:document.querySelector('#btn-kirim').disabled}})()`);
   assert.equal(submission.data.nohp,'081234567890');assert.equal(submission.data.nama,'Pelanggan Contoh');assert.equal(submission.disabled,true);checks++;
   await evaluate(`window.dispatchEvent(new Event('pageshow'))`);
   assert.equal(await evaluate(`document.querySelector('#btn-kirim').disabled`),false);
   await evaluate(`(()=>{const e=document.querySelector('[name="nohp"]');e.value='+62 812-3456-7890';e.dispatchEvent(new Event('input',{bubbles:true}));document.querySelector('#contact-message-form').requestSubmit()})()`);
   assert.equal(await evaluate(`window.testSubmissions.at(-1).nohp`),'+62 812-3456-7890');checks++;
  }
 }
 assert.deepEqual(errors,[]);console.log(`PASS: ${checks} contact/form responsive, theme and form checks. No messages submitted to server.`);
 }finally{ws.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
