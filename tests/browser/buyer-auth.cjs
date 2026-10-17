/* Local-only auth UI regression. Forms are never submitted and external requests are blocked. */
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
(async()=>{
 const dir=path.resolve(__dirname,'../../writable/cache/buyer-auth-review'),port=fs.readFileSync(path.join(dir,'chrome-profile/DevToolsActivePort'),'utf8').split('\n')[0];
 const tabs=await(await fetch(`http://127.0.0.1:${port}/json/list`)).json(),ws=new WebSocket(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);await new Promise(r=>ws.onopen=r);
 let seq=0,checks=0;const pending=new Map();
 ws.onmessage=e=>{const m=JSON.parse(e.data);if(m.id){const p=pending.get(m.id);if(!p)return;pending.delete(m.id);clearTimeout(p.timer);m.error?p.reject(new Error(m.error.message)):p.resolve(m.result)}};
 const call=(method,params={})=>new Promise((resolve,reject)=>{const id=++seq,timer=setTimeout(()=>reject(new Error('Timeout '+method)),10000);pending.set(id,{resolve,reject,timer});ws.send(JSON.stringify({id,method,params}))});
 const wait=ms=>new Promise(r=>setTimeout(r,ms));
 const evaluate=async expression=>{const r=await call('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.text);return r.result.value};
 try{
 await call('Page.enable');await call('Runtime.enable');await call('Network.enable');await call('Network.setBlockedURLs',{urls:['http://*','https://*']});
 for(const name of ['login','login-errors','signup','signup-errors']){
  await call('Page.navigate',{url:'file:///'+path.join(dir,name+'.html').replaceAll('\\','/')});await wait(200);
  for(const width of [320,360,390,430,600,768,991,1024,1440]){
   await call('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<768});await wait(100);
   const result=await evaluate(`(()=>{const s=e=>getComputedStyle(e),r=e=>e.getBoundingClientRect(),panel=document.querySelector('.auth-panel'),footer=document.querySelector('footer');return {scroll:document.documentElement.scrollWidth,shadow:s(panel).boxShadow,border:s(panel).borderTopWidth,panelWidth:r(panel).width,panelLeft:r(panel).left,panelRight:r(panel).right,bg:s(document.querySelector('.auth-page')).backgroundImage,footerTop:r(footer).top,contentBottom:r(document.querySelector('.auth-page')).bottom,fields:[...document.querySelectorAll('.auth-field')].map(e=>{const input=e.querySelector('input'),label=e.querySelector('label');return {size:parseFloat(s(input).fontSize),height:r(input).height,labelBottom:r(label).bottom,inputTop:r(input).top,width:r(e).width,scroll:e.scrollWidth}}),buttons:[...document.querySelectorAll('.auth-google-btn,.auth-submit,.auth-password-toggle')].map(e=>({height:r(e).height,right:r(e).right})),errors:[...document.querySelectorAll('.invalid-feedback')].map(e=>s(e).display)}})()`);
   assert.ok(result.scroll<=width+1,`${name}/${width}: overflow`);assert.equal(result.shadow,'none');assert.equal(result.border,'0px');assert.equal(result.bg,'none');assert.ok(result.panelWidth<=420&&result.panelLeft>=0&&result.panelRight<=width);assert.ok(result.footerTop>=result.contentBottom-1);
   result.fields.forEach(f=>{assert.ok(f.size>=16);assert.ok(f.height>=48);assert.ok(f.labelBottom<=f.inputTop);assert.ok(f.scroll<=f.width+1)});result.buttons.forEach(b=>{assert.ok(b.height>=44);assert.ok(b.right<=width)});if(name.endsWith('errors'))result.errors.forEach(d=>assert.notEqual(d,'none'));checks++;
   if((width===390||width===1440)&&!name.endsWith('errors')){await evaluate(`window.scrollTo({top:0,left:0,behavior:'instant'})`);const shot=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});fs.writeFileSync(path.join(dir,`${name}-${width}.png`),Buffer.from(shot.data,'base64'))}
  }
 }
 const click=async selector=>{const point=await evaluate(`(()=>{const el=document.querySelector(${JSON.stringify(selector)});el.scrollIntoView({block:'center',behavior:'instant'});const r=el.getBoundingClientRect();return {x:r.x+r.width/2,y:r.y+r.height/2}})()`);for(const type of ['mousePressed','mouseReleased'])await call('Input.dispatchMouseEvent',{type,...point,button:'left',clickCount:1});};
 for(const page of ['login','signup']){
  await call('Page.navigate',{url:'file:///'+path.join(dir,page+'.html').replaceAll('\\','/')});await wait(200);await call('Emulation.setDeviceMetricsOverride',{width:390,height:844,deviceScaleFactor:1,mobile:true});
  assert.equal(await evaluate(`document.querySelector('.auth-submit').disabled`),true);
  await evaluate(`document.querySelectorAll('.auth-form input:not([type=hidden]):not([type=submit]):not([type=checkbox])').forEach(i=>{i.value=i.name==='email'?'buyer@example.test':i.name==='nohp'?'081234567890':i.name==='nama'?'Pembeli Uji':'ExamplePassword123';i.dispatchEvent(new Event('input',{bubbles:true}))})`);
  if(page==='signup'){assert.equal(await evaluate(`document.querySelector('.auth-submit').disabled`),true);await click('#syarat');}
  assert.equal(await evaluate(`document.querySelector('.auth-submit').disabled`),false);assert.equal(await evaluate(`document.querySelector('.auth-form').checkValidity()`),true);
  await click('.auth-password-toggle');assert.equal(await evaluate(`document.querySelector('input[name=sandi]').type`),'text');assert.equal(await evaluate(`document.querySelector('.auth-password-toggle').getAttribute('aria-label')`),'Sembunyikan sandi');await click('.auth-password-toggle');assert.equal(await evaluate(`document.querySelector('input[name=sandi]').type`),'password');
  if(page==='signup'){await click('#syarat');assert.equal(await evaluate(`document.querySelector('.auth-submit').disabled`),true);await click('#syarat');}
  await evaluate(`const i=document.querySelector('input[name=email]');i.value='invalid';i.dispatchEvent(new Event('input',{bubbles:true}))`);assert.equal(await evaluate(`document.querySelector('.auth-form').checkValidity()`),page==='login');
  await evaluate(`document.querySelector('input[name=email]').value=''`);assert.equal(await evaluate(`document.querySelector('.auth-form').checkValidity()`),false);checks++;
 }
 console.log(`PASS: ${checks} auth layout/interaction cases: 9 widths, inline errors, Google redirect, password toggle and signup consent; no form submitted.`);
 }finally{ws.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
