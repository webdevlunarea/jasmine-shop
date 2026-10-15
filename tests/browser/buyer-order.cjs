/* Local fixtures only: editorial About page across themes and responsive widths. */
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
(async()=>{
 const dir=path.resolve(__dirname,'../../writable/cache/buyer-order-review'),port=fs.readFileSync(path.join(dir,'chrome-profile/DevToolsActivePort'),'utf8').split('\n')[0];
 const tabs=await(await fetch(`http://127.0.0.1:${port}/json/list`)).json(),ws=new WebSocket(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);await new Promise(r=>ws.onopen=r);
 let seq=0,checks=0;const pending=new Map(),errors=[];
 ws.onmessage=e=>{const m=JSON.parse(e.data);if(m.method==='Runtime.exceptionThrown')errors.push(m.params.exceptionDetails.text);if(m.id){const p=pending.get(m.id);if(!p)return;pending.delete(m.id);clearTimeout(p.timer);m.error?p.reject(new Error(m.error.message)):p.resolve(m.result)}};
 const call=(method,params={})=>new Promise((resolve,reject)=>{const id=++seq,timer=setTimeout(()=>reject(new Error('Timeout '+method)),10000);pending.set(id,{resolve,reject,timer});ws.send(JSON.stringify({id,method,params}))});
 const wait=ms=>new Promise(r=>setTimeout(r,ms));
 const evaluate=async expression=>{const r=await call('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.text);return r.result.value};




 try {
 await call('Page.enable');await call('Runtime.enable');await call('Network.enable');await call('Network.setBlockedURLs',{urls:['http://*','https://*']});
 await call('Page.addScriptToEvaluateOnNewDocument',{source:`window.WebSocket=class {constructor(){} close(){}};Object.defineProperty(navigator,'clipboard',{value:{writeText:async text=>{window.copiedOrderText=text}}});`});
 const states=['pending','manual','verification','qris','processing','external','internal','completed','expired','cancelled'];
 fs.writeFileSync(path.join(dir,'proof-fixture.png'),Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j0ioAAAAASUVORK5CYII=','base64'));
 for(const state of states)for(const theme of ['default','sand']){
  await call('Page.navigate',{url:'file:///'+path.join(dir,state+'-'+theme+'.html').replaceAll('\\','/')});await wait(150);
  for(const width of [320,390,600,768,991,1024,1440]){
   await call('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<768});await wait(60);await evaluate(`window.scrollTo({top:0,left:0,behavior:'instant'})`);
   const a=await evaluate(`(()=>{const m=document.querySelector('main'),r=e=>e.getBoundingClientRect(),s=e=>getComputedStyle(e);return {scroll:document.documentElement.scrollWidth,top:r(m).top,bottom:r(m).bottom,header:r(document.querySelector(innerWidth<992?'.buyer-mobile-header':'.buyer-desktop-header')).bottom,footer:r(document.querySelector('footer')).top,color:s(m.querySelector('h1')).color,columns:s(m.querySelector('.order-detail-grid')).gridTemplateColumns.split(' ').length,title:m.querySelector('h1').textContent,controls:[...m.querySelectorAll('.btn,.tombol-copy,.order-back-link')].filter(e=>r(e).height>0).map(e=>({height:r(e).height,right:r(e).right})),panels:[...m.querySelectorAll('.order-detail-section')].map(e=>({scroll:e.scrollWidth,width:r(e).width})),shipping:[...m.querySelectorAll('.shipping-detail-panel')].map(e=>({shadow:s(e).boxShadow,bg:s(e).backgroundImage})),qr:document.querySelector('#modal-qr')?.classList.contains('d-none')}})()`);
   assert.ok(a.scroll<=width+1,`${state}/${theme}/${width}: overflow ${a.scroll}`);assert.ok(a.top>=a.header-1);assert.ok(a.footer>=a.bottom-1);assert.equal(a.color,theme==='sand'?'rgb(47, 45, 32)':'rgb(36, 59, 107)');assert.equal(a.columns,width<768?1:2);
   a.controls.forEach(c=>{assert.ok(c.height>=44);assert.ok(c.right<=width+1)});a.panels.forEach(p=>assert.ok(p.scroll<=p.width+1));a.shipping.forEach(s=>{assert.equal(s.shadow,'none');assert.equal(s.bg,'none')});if(state==='qris')assert.equal(a.qr,true);if(state==='completed')assert.equal(a.title,'Pesanan selesai');checks++;
   if(theme==='sand'&&['manual','external','completed','expired'].includes(state)&&[390,1440].includes(width)){
    const shot=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:true,clip:{x:0,y:0,width,height:Math.ceil(a.bottom+30),scale:1}});fs.writeFileSync(path.join(dir,`${state}-${width}.png`),Buffer.from(shot.data,'base64'));
   }
  }
  if(['pending','manual'].includes(state)){
   await evaluate(`document.querySelector('[data-order-copy]').click()`);await wait(30);
   assert.equal(await evaluate('window.copiedOrderText'),'123456789012345678901234');assert.equal(await evaluate(`document.querySelector('[data-order-copy]').textContent`),'Tersalin');checks++;
  }
  if(state==='manual'){
   await call('Emulation.setDeviceMetricsOverride',{width:390,height:700,deviceScaleFactor:1,mobile:true});
   await evaluate(`document.querySelector('[onclick="openUpload()"]').focus();document.querySelector('[onclick="openUpload()"]').click()`);await wait(50);
   const d=await evaluate(`(()=>{const e=document.querySelector('#modal-upload'),r=e.firstElementChild.getBoundingClientRect();return {open:!e.classList.contains('d-none'),z:parseInt(getComputedStyle(e).zIndex),width:r.width,focus:document.activeElement.className}})()`);
   assert.ok(d.open&&d.z>1080&&d.width<=390);assert.equal(d.focus,'order-dialog-close');checks++;
   const documentNode=await call('DOM.getDocument');const node=await call('DOM.querySelector',{nodeId:documentNode.root.nodeId,selector:'#bukti-bayar'});
   await call('DOM.setFileInputFiles',{nodeId:node.nodeId,files:[path.join(dir,'proof-fixture.png')]});await wait(100);
   assert.equal(await evaluate(`!document.querySelector('#btn-submit-bayar').disabled&&!document.querySelector('#preview-bayar').classList.contains('d-none')&&document.querySelector('#preview-bayar img').src.startsWith('blob:')`),true);checks++;
   await call('Input.dispatchKeyEvent',{type:'keyDown',key:'Escape',code:'Escape',windowsVirtualKeyCode:27});await wait(40);
   assert.equal(await evaluate(`document.querySelector('#modal-upload').classList.contains('d-none')&&document.activeElement.getAttribute('onclick')==='openUpload()'`),true);checks++;
  }
  if(state==='qris'){
   await call('Emulation.setDeviceMetricsOverride',{width:390,height:700,deviceScaleFactor:1,mobile:true});
   await evaluate(`document.querySelector('.order-qr-trigger').focus();document.querySelector('.order-qr-trigger').click()`);await wait(60);
   assert.equal(await evaluate(`!document.querySelector('#modal-qr').classList.contains('d-none')&&getComputedStyle(document.querySelector('#modal-qr img')).objectFit==='contain'&&document.activeElement.classList.contains('order-dialog-close')`),true);checks++;
   await call('Input.dispatchKeyEvent',{type:'keyDown',key:'Escape',code:'Escape',windowsVirtualKeyCode:27});await wait(40);
   assert.equal(await evaluate(`document.querySelector('#modal-qr').classList.contains('d-none')&&document.activeElement.classList.contains('order-qr-trigger')`),true);checks++;
  }
 }
 assert.deepEqual(errors,[]);console.log(`PASS: ${checks} order journey layouts/statuses, copy, QR and proof preview checks. No live payment, upload, cancellation or tracking calls.`);
 }finally{ws.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
