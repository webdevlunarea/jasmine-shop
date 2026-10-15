/* Local fixtures only: editorial About page across themes and responsive widths. */
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
(async()=>{
 const dir=path.resolve(__dirname,'../../writable/cache/buyer-checkout-review'),port=fs.readFileSync(path.join(dir,'chrome-profile/DevToolsActivePort'),'utf8').split('\n')[0];
 const tabs=await(await fetch(`http://127.0.0.1:${port}/json/list`)).json(),ws=new WebSocket(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);await new Promise(r=>ws.onopen=r);
 let seq=0,checks=0;const pending=new Map(),errors=[];
 ws.onmessage=e=>{const m=JSON.parse(e.data);if(m.method==='Runtime.exceptionThrown')errors.push(m.params.exceptionDetails.text);if(m.id){const p=pending.get(m.id);if(!p)return;pending.delete(m.id);clearTimeout(p.timer);m.error?p.reject(new Error(m.error.message)):p.resolve(m.result)}};
 const call=(method,params={})=>new Promise((resolve,reject)=>{const id=++seq,timer=setTimeout(()=>reject(new Error('Timeout '+method)),10000);pending.set(id,{resolve,reject,timer});ws.send(JSON.stringify({id,method,params}))});
 const wait=ms=>new Promise(r=>setTimeout(r,ms));
 const evaluate=async expression=>{const r=await call('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.text);return r.result.value};

 try {
 await call('Page.enable');await call('Runtime.enable');await call('Network.enable');
 await call('Network.setBlockedURLs',{urls:['http://*','https://*']});
 // Synthetic rate responses only. No real API, address submission, payment or order creation.
 await call('Page.addScriptToEvaluateOnNewDocument',{source:`window.fetch=async()=>({ok:true,json:async()=>({success:true,data:{quotes:window.testQuotes??[{token:'fixture-internal',courier_code:'internal',service_name:'Pengiriman toko',cost:0},{token:'fixture-jne',courier_code:'jne',service_code:'REG',etd:'2-3 hari',cost:125000},...Array.from({length:8},(_,i)=>({token:'fixture-'+i,courier_code:'tiki',service_code:'REG',etd:'3-5 hari',cost:150000+i*10000}))]}})});`});
 for(const state of ['member','guest','no-voucher']) for(const theme of ['default','sand']) {
  await call('Page.navigate',{url:'file:///'+path.join(dir,state+'-'+theme+'.html').replaceAll('\\','/')});await wait(250);
  await evaluate(`document.querySelector('#checkbox-syarat').checked=false`);
  assert.equal(await evaluate(`document.querySelector('#btn-checkout').disabled`),true);
  await evaluate(`loadShippingQuotes()`);await wait(100);
  for(const width of [320,360,390,430,600,768,991,1024,1440]) {
   await call('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<768});await wait(80);
   await evaluate(`window.scrollTo({top:0,left:0,behavior:'instant'})`);
   const a=await evaluate(`(()=>{const r=e=>e.getBoundingClientRect(),s=e=>getComputedStyle(e),main=document.querySelector('main'),summary=main.querySelector('.checkout-summary'),delivery=main.querySelector('.checkout-delivery');return {scroll:document.documentElement.scrollWidth,top:r(main).top,bottom:r(main).bottom,header:r(document.querySelector(innerWidth<992?'.buyer-mobile-header':'.buyer-desktop-header')).bottom,footer:r(document.querySelector('footer')).top,columns:s(main.querySelector('.checkout-layout')).gridTemplateColumns.split(' ').length,summary:r(summary).toJSON(),delivery:r(delivery).toJSON(),color:s(summary).color,shippingHeight:r(document.querySelector('#shipping-options')).height,options:[...main.querySelectorAll('.shipping-option-card')].map(e=>({width:r(e).width,scroll:e.scrollWidth,shadow:s(e).boxShadow,bg:s(e).backgroundImage})),inputs:[...main.querySelectorAll('.checkout-address input,.checkout-address select')].filter(e=>r(e).height>0).map(e=>({width:r(e).width,scroll:e.scrollWidth,font:parseFloat(s(e).fontSize)})),h1:parseFloat(s(main.querySelector('h1')).fontSize)}})()`);
   assert.ok(a.scroll<=width+1,`${state}/${theme}/${width}: overflow ${a.scroll}`);
   assert.ok(a.top>=a.header-1,'Header overlap');assert.ok(a.footer>=a.bottom-1,'Footer overlap');
   assert.equal(a.columns,width<992?1:2);assert.ok(a.h1<=32);
   assert.equal(a.color,theme==='sand'?'rgb(47, 45, 32)':'rgb(36, 59, 107)');
   if(width<992)assert.ok(a.summary.top>=a.delivery.bottom);else assert.ok(a.summary.left>=a.delivery.right);
   assert.ok(a.shippingHeight<=301);a.options.forEach(o=>{assert.ok(o.scroll<=o.width+1);assert.equal(o.shadow,'none');assert.equal(o.bg,'none')});
   if(width<=575)a.inputs.forEach(i=>assert.ok(i.font>=16));
   if(state==='member'&&[390,1440].includes(width)){
    const shot=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:true,clip:{x:0,y:0,width,height:Math.ceil(a.bottom+40),scale:1}});fs.writeFileSync(path.join(dir,`${theme}-${width}.png`),Buffer.from(shot.data,'base64'));
   }
   checks++;
  }
  // Existing button gating, shipping selection, and totals, without submitting any forms.
  if(state==='guest')await evaluate(`document.querySelector('[name="emailPem"]').value='guest@example.test'`);
  await evaluate(`document.querySelector('#checkbox-syarat').checked=true;refreshCheckoutButtonState()`);
  assert.equal(await evaluate(`document.querySelector('#btn-checkout').disabled`),false);
  const before=await evaluate(`document.querySelector('#checkout-grand-total').textContent.trim()`);
  await evaluate(`document.querySelector('[value="fixture-jne"]').click()`);
  assert.equal(await evaluate(`document.querySelector('#shipping_quote_token').value`),'fixture-jne');
  assert.notEqual(await evaluate(`document.querySelector('#checkout-grand-total').textContent.trim()`),before);
  await evaluate(`document.querySelector('#btn-checkout').focus();document.querySelector('#btn-checkout').click()`);await wait(50);
  assert.equal(await evaluate(`!document.querySelector('#modal-metode-bayar').classList.contains('d-none')&&document.activeElement.classList.contains('checkout-dialog-close')`),true);
  const modal=await evaluate(`(()=>{const d=document.querySelector('#modal-metode-bayar'),r=d.firstElementChild.getBoundingClientRect();return {z:parseInt(getComputedStyle(d).zIndex),left:r.left,right:r.right,top:r.top,bottom:r.bottom}})()`);
  assert.ok(modal.z>1080&&modal.left>=0&&modal.right<=1440&&modal.top>=0&&modal.bottom<=900);
  await call('Input.dispatchKeyEvent',{type:'keyDown',key:'Escape',code:'Escape',windowsVirtualKeyCode:27});await wait(50);
  assert.equal(await evaluate(`document.querySelector('#modal-metode-bayar').classList.contains('d-none')&&document.activeElement.id==='btn-checkout'`),true);
  checks++;
  await call('Emulation.setDeviceMetricsOverride',{width:390,height:700,deviceScaleFactor:1,mobile:true});
  await evaluate(`document.querySelector('#btn-checkout').focus();document.querySelector('#btn-checkout').click()`);await wait(60);
  const mobileModal=await evaluate(`(()=>{const d=document.querySelector('#modal-metode-bayar'),r=d.firstElementChild.getBoundingClientRect();return {open:!d.classList.contains('d-none'),left:r.left,right:r.right,height:r.height}})()`);
  assert.ok(mobileModal.open&&mobileModal.left>=0&&mobileModal.right<=390&&mobileModal.height<=700);
  await call('Input.dispatchKeyEvent',{type:'keyDown',key:'Escape',code:'Escape',windowsVirtualKeyCode:27});await wait(50);checks++;
  if(state!=='guest') {
   await evaluate(`document.querySelector('button.tmb-input-redeem').focus();document.querySelector('button.tmb-input-redeem').click()`);await wait(50);
   assert.equal(await evaluate(`!document.querySelector('#modal-redeem').classList.contains('d-none')&&document.activeElement.id==='checkout-voucher-code'`),true);
   await call('Input.dispatchKeyEvent',{type:'keyDown',key:'Escape',code:'Escape',windowsVirtualKeyCode:27});await wait(50);
   assert.equal(await evaluate(`document.querySelector('#modal-redeem').classList.contains('d-none')&&document.activeElement.matches('button.tmb-input-redeem')`),true);checks++;
  }
  await evaluate(`testQuotes=[];loadShippingQuotes()`);await wait(50);
  assert.equal(await evaluate(`document.querySelector('#btn-checkout').disabled&&document.querySelector('#shipping_quote_token').value===''`),true);
  checks++;
  await evaluate(`testQuotes=undefined;loadShippingQuotes()`);await wait(50);
  await evaluate(`document.querySelector('[name="alamat_add"]').value='';document.querySelector('[name="alamat_add"]').dispatchEvent(new Event('input',{bubbles:true}))`);await wait(50);
  assert.equal(await evaluate(`document.querySelector('#btn-checkout').disabled`),true);checks++;
 }
 assert.deepEqual(errors,[]);console.log(`PASS: ${checks} checkout layout/theme/interaction cases (synthetic data only).`);
 }finally{ws.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
