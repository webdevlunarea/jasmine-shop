/* Local fixtures only: shared customer area layout and non-submitting UI interactions. */
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
(async()=>{
 const dir=path.resolve(__dirname,'../../writable/cache/buyer-product-review'),port=fs.readFileSync(path.join(dir,'chrome-profile/DevToolsActivePort'),'utf8').split('\n')[0];
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
 for(const state of ['member','anonymous','inactive','out-of-stock','no-reviews','long'])for(const theme of ['default','sand']){
  await navigate(state,theme);
  for(const width of [320,360,390,430,600,768,991,1024,1440]){
   await call('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<768});await wait(120);await evaluate(`window.scrollTo({top:0,left:0,behavior:'instant'})`);
   const a=await evaluate(`(()=>{const root=document.querySelector('.buyer-product-page'),r=e=>{const b=e.getBoundingClientRect();return {left:b.left,right:b.right,top:b.top,bottom:b.bottom,width:b.width,height:b.height}},s=e=>getComputedStyle(e),bar=root.querySelector('.product-mobile-purchase-bar'),gallery=root.querySelector('.img-produk-besar');return {width:document.documentElement.scrollWidth,main:r(root),gallery:r(gallery),info:r(root.querySelector('.product-detail-info')),title:parseFloat(s(root.querySelector('h1')).fontSize),bar:{rect:r(bar),display:s(bar).display,shadow:s(bar).boxShadow,position:s(bar).position,controls:[...bar.querySelectorAll('.btn')].map(r)},actions:s(root.querySelector('.product-purchase-actions')).display,variants:[...root.querySelectorAll('#varian-group label')].map(r),thumbs:[...root.querySelectorAll('.img-produk-select')].map(r),slides:[...root.querySelectorAll('.img-produk-prev')].map(r),current:s(root.querySelector('.img-produk-prev-baru')).backgroundSize}})()`);
   assert.ok(a.width<=width+1,`${state}/${theme}/${width}: overflow ${a.width}`);assert.ok(Math.abs(a.gallery.width-a.gallery.height)<2);assert.ok(a.gallery.left>=0&&a.gallery.right<=width+1);assert.ok(a.title<=26);assert.equal(a.current,'contain');a.slides.forEach(slide=>assert.ok(Math.abs(slide.width-a.gallery.width)<2));a.variants.forEach(v=>assert.ok(v.height>=44&&v.left>=0&&v.right<=width+1));a.thumbs.forEach(t=>assert.ok(t.height>=44&&t.width>=44));
   if(width<992){assert.equal(a.bar.display,'flex');assert.equal(a.bar.position,'fixed');assert.equal(a.bar.shadow,'none');assert.ok(Math.abs(a.bar.rect.bottom-900)<2);assert.equal(a.actions,'none');a.bar.controls.forEach(c=>assert.ok(c.height>=44&&c.width>=44&&c.right<=width));}
   else{assert.equal(a.bar.display,'none');assert.equal(a.actions,'flex');assert.ok(a.gallery.right<a.info.left)}
   if(state==='member'&&theme==='sand'&&[390,1440].includes(width)){const shot=await call('Page.captureScreenshot',{format:'png'});fs.writeFileSync(path.join(dir,`product-${width}.png`),Buffer.from(shot.data,'base64'))}
   await evaluate(`window.scrollTo({top:document.documentElement.scrollHeight,left:0,behavior:'instant'})`);await wait(100);
   assert.equal(await evaluate(`(()=>{const f=document.querySelector('footer').getBoundingClientRect(),b=document.querySelector('.product-mobile-purchase-bar'),s=getComputedStyle(b);return s.display==='none'||f.bottom<=b.getBoundingClientRect().top+1})()`),true,`${state}/${width}: footer must clear purchase bar`);checks++;
  }
 }
 const click=async selector=>{const p=await evaluate(`(()=>{const e=document.querySelector(${JSON.stringify(selector)});e.scrollIntoView({block:'center',behavior:'instant'});const r=e.getBoundingClientRect();return {x:r.x+r.width/2,y:r.y+r.height/2}})()`);await wait(50);for(const type of ['mousePressed','mouseReleased'])await call('Input.dispatchMouseEvent',{type,...p,button:'left',clickCount:1});await wait(100)};
 for(const width of [390,1440]){
  await navigate('member','sand');await call('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<768});await wait(150);
  await click('label[for="btnradio1"]');
  const empty=await evaluate(`({disabled:[...document.querySelectorAll('.btn-beli-product,.btn-beli-product-checkout')].every(e=>e.disabled),stock:document.getElementById('stok').textContent,fields:[...document.querySelectorAll('.product-wishlist-form')].map(f=>({variant:f.querySelector('[name="varian"]').value,image:f.querySelector('[name="index_gambar"]').value,action:f.getAttribute('action'),fill:f.querySelector('.product-wishlist-icon').getAttribute('fill')}))})`);
  assert.equal(empty.disabled,true);assert.equal(empty.stock,'Stok habis');empty.fields.forEach(f=>{assert.equal(f.variant,'Natural kayu');assert.equal(f.image,'2');assert.equal(f.action,'/addwishlist/12');assert.equal(f.fill,'none')});
  await click('label[for="btnradio2"]');assert.equal(await evaluate(`document.querySelectorAll('.btn-beli-product:disabled,.btn-beli-product-checkout:disabled').length`),0);assert.equal(await evaluate(`document.querySelector('.btn-beli-product-checkout').parentNode.getAttribute('action')`),'/addcart/12/Abu-abu/1/checkout');
  await click('label[for="btnradio0"]');assert.equal(await evaluate(`document.querySelector('.product-wishlist-form').getAttribute('action')`),'/delwishlist/12');assert.equal(await evaluate(`document.querySelector('.product-wishlist-icon').getAttribute('fill')`),'currentColor');
  await evaluate(`document.querySelectorAll('.img-produk-select')[3].focus()`);for(const type of ['keyDown','keyUp'])await call('Input.dispatchKeyEvent',{type,key:'Enter',code:'Enter',windowsVirtualKeyCode:13});await wait(120);
  assert.equal(await evaluate(`document.querySelectorAll('.img-produk-select')[3].getAttribute('aria-pressed')`),'true');assert.equal(await evaluate(`Math.abs(document.querySelector('.img-produk-besar').scrollLeft-document.querySelector('.img-produk-besar').clientWidth*3)<2`),true);
  await evaluate(`document.querySelector('.img-produk-besar').scrollTo({left:0,behavior:'instant'})`);await wait(100);assert.equal(await evaluate(`document.querySelectorAll('.img-produk-select')[0].getAttribute('aria-pressed')`),'true');
  await click('[data-bs-target="#flush-collapse2"]');await wait(150);assert.equal(await evaluate(`document.querySelector('#flush-collapse2').classList.contains('show')`),true);checks++;
 }

 for(const [width,height] of [[390,667],[844,390]]){
  await navigate('member','sand');await call('Emulation.setDeviceMetricsOverride',{width,height,deviceScaleFactor:1,mobile:true});await wait(150);await evaluate(`window.scrollTo({top:document.documentElement.scrollHeight,behavior:'instant'})`);await wait(100);
  assert.equal(await evaluate(`(()=>{const b=document.querySelector('.product-mobile-purchase-bar').getBoundingClientRect(),f=document.querySelector('footer').getBoundingClientRect();return document.documentElement.scrollWidth<=innerWidth+1&&Math.abs(b.bottom-innerHeight)<2&&f.bottom<=b.top+1})()`),true);checks++;
 }
 await navigate('member','sand');await call('Emulation.setDeviceMetricsOverride',{width:390,height:844,deviceScaleFactor:1,mobile:true});await wait(120);
 await evaluate(`document.getElementById('star4').focus()`);for(const type of ['keyDown','keyUp'])await call('Input.dispatchKeyEvent',{type,key:' ',code:'Space',windowsVirtualKeyCode:32});assert.equal(await evaluate(`document.getElementById('star4').checked`),true);checks++;
 assert.deepEqual(runtimeErrors,[]);console.log(`PASS: ${checks} product-detail layout/interaction cases, 6 account/data states, 2 themes, 9 widths; variant mapping, wishlist, stock guards and keyboard gallery verified without submissions.`);
 }finally{ws.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
