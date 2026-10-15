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
 await call('Page.enable');await call('Runtime.enable');await call('Network.enable');await call('Network.setBlockedURLs',{urls:['http://*','https://*']});
 await call('Page.addScriptToEvaluateOnNewDocument',{source:`
 window.testFeeMode='normal';window.feeCalls=[];
 window.fetch=async(url,options={})=>{
  if(url==='/getadminfee'){
   const body=JSON.parse(options.body);window.feeCalls.push(body);
   const mode=window.testFeeMode;
   if(mode==='slow')await new Promise(r=>setTimeout(r,body.bank==='bca'?180:20));
   if(mode==='pending')await new Promise(r=>setTimeout(r,150));
   const fee=body.bank==='rekening'?0:body.bank==='qris'?Math.ceil(body.nominal*.007):4000;
   return {ok:mode!=='error',json:async()=>({biayaAdmin:mode==='invalid'?-1:fee})};
  }
  return {ok:true,json:async()=>({success:true,data:{quotes:[{token:'test-free',courier_code:'internal',cost:0},{token:'test-paid',courier_code:'jne',service_code:'REG',cost:125000}]}})};
 };`});
 const money=s=>Number(s.replace(/[^0-9]/g,''));
 const choose=async value=>{await evaluate(`(()=>{const e=document.querySelector('input[name="pembayaran"][value="${value}"]');e.checked=true;e.dispatchEvent(new Event('change',{bubbles:true}))})()`);await wait(60)};
 for(const state of ['member','guest','all-discounts'])for(const theme of ['default','sand']){
  await call('Page.navigate',{url:'file:///'+path.join(dir,state+'-'+theme+'.html').replaceAll('\\','/')});await wait(200);
  await evaluate(`loadShippingQuotes()`);await wait(50);
  if(state==='guest')await evaluate(`document.querySelector('[name="emailPem"]').value='guest@example.test'`);
  await evaluate(`document.querySelector('#checkbox-syarat').checked=true;refreshCheckoutButtonState();document.querySelector('[value="test-paid"]').click()`);await wait(50);
  const base=state==='all-discounts'?1780000:state==='member'?1900000:1950000;
  const recap=await evaluate(`({shipping:document.querySelector('#payment-shipping-cost').textContent,base:document.querySelector('#payment-base-total').textContent,fee:document.querySelector('#biaya-admin').textContent,total:document.querySelector('#biaya-total').textContent,disabled:document.querySelector('#payment-submit').disabled})`);
  assert.equal(money(recap.shipping),125000);assert.equal(money(recap.base),base);assert.equal(money(recap.fee),4000);assert.equal(money(recap.total),base+4000);assert.equal(recap.disabled,false);checks++;
  await choose('qris');assert.equal(money(await evaluate(`document.querySelector('#biaya-admin').textContent`)),Math.ceil(base*.007));checks++;
  await choose('rekening');assert.equal(money(await evaluate(`document.querySelector('#biaya-total').textContent`)),base);checks++;
  await evaluate(`openMetode()`);await wait(50);
  for(const width of [320,390,768,1440]){
   await call('Emulation.setDeviceMetricsOverride',{width,height:800,deviceScaleFactor:1,mobile:width<768});await wait(50);
   const a=await evaluate(`(()=>{const d=document.querySelector('#modal-metode-bayar'),p=d.firstElementChild,r=p.getBoundingClientRect();return {left:r.left,right:r.right,height:r.height,overflow:p.scrollWidth>p.clientWidth,rows:[...p.querySelectorAll('dl>div')].map(e=>e.scrollWidth<=e.clientWidth),retry:document.querySelector('#payment-fee-retry').getClientRects().length}})()`);
   assert.ok(a.left>=0&&a.right<=width&&a.height<=800);assert.equal(a.overflow,false);assert.ok(a.rows.every(Boolean));assert.equal(a.retry,0);checks++;
   if(state==='all-discounts'&&theme==='sand'&&[390,1440].includes(width)){
    await evaluate(`document.querySelector('.payment-breakdown').scrollIntoView({block:'center'})`);await wait(50);
    const shot=await call('Page.captureScreenshot',{format:'png'});fs.writeFileSync(path.join(dir,`payment-${width}.png`),Buffer.from(shot.data,'base64'));
   }
  }
  await evaluate(`testFeeMode='slow'`);await choose('bca');await choose('rekening');await wait(200);
  assert.equal(money(await evaluate(`document.querySelector('#biaya-admin').textContent`)),0,'Old response must not replace newest method');checks++;
  await evaluate(`testFeeMode='pending';document.querySelector('[value="qris"]').checked=true;void refreshPaymentFees()`);
  assert.equal(await evaluate(`document.querySelector('#payment-submit').disabled`),true);await wait(180);checks++;
  await evaluate(`testFeeMode='error';refreshPaymentFees()`);await wait(50);
  assert.equal(await evaluate(`document.querySelector('#payment-submit').disabled&&!document.querySelector('#payment-fee-retry').hidden`),true);checks++;
  await evaluate(`testFeeMode='invalid';refreshPaymentFees()`);await wait(50);
  assert.equal(await evaluate(`document.querySelector('#payment-submit').disabled`),true);checks++;
  await evaluate(`testFeeMode='normal';document.querySelector('#payment-fee-retry').click()`);await wait(50);
  assert.equal(await evaluate(`document.querySelector('#payment-submit').disabled`),false);checks++;
  await evaluate(`resetShippingSelection()`);await wait(50);
  assert.equal(await evaluate(`document.querySelector('#payment-submit').disabled`),true);checks++;
 }
 assert.deepEqual(errors,[]);console.log(`PASS: ${checks} payment recap, responsive modal, fee race/failure/retry checks. No real transactions.`);
 }finally{ws.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
