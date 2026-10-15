/* Fixture-only responsive carousel regression. Generate with HomeCarouselViewTest.
   Connects only to the dedicated Chrome profile under writable/cache/home-carousel-review. */
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
(async()=>{
 const dir=path.resolve(__dirname,'../../writable/cache/home-carousel-review'),port=fs.readFileSync(path.join(dir,'chrome-profile/DevToolsActivePort'),'utf8').split('\n')[0];assert.match(port,/^\d+$/);
 const tabs=await(await fetch(`http://127.0.0.1:${port}/json/list`)).json(),ws=new WebSocket(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);await new Promise(r=>ws.onopen=r);
 let seq=0,checks=0;const pending=new Map(),errors=[];
 ws.onmessage=e=>{const m=JSON.parse(e.data);if(m.method==='Runtime.exceptionThrown')errors.push(m.params.exceptionDetails.text);if(m.id){const p=pending.get(m.id);if(!p)return;pending.delete(m.id);clearTimeout(p.timer);m.error?p.reject(new Error(m.error.message)):p.resolve(m.result)}};
 const call=(method,params={})=>new Promise((resolve,reject)=>{const id=++seq,timer=setTimeout(()=>reject(new Error('Timeout '+method)),10000);pending.set(id,{resolve,reject,timer});ws.send(JSON.stringify({id,method,params}))});
 const wait=ms=>new Promise(r=>setTimeout(r,ms));
 const evaluate=async expression=>{const r=await call('Runtime.evaluate',{expression,returnByValue:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.text);return r.result.value};
 const resize=async(width,height)=>{await call('Emulation.setDeviceMetricsOverride',{width,height,deviceScaleFactor:1,mobile:width<768});await wait(100)};
 const click=async selector=>{const r=JSON.parse(await evaluate(`JSON.stringify((()=>{const r=document.querySelector(${JSON.stringify(selector)}).getBoundingClientRect();return {x:r.x+r.width/2,y:r.y+r.height/2}})())`));for(const type of ['mousePressed','mouseReleased'])await call('Input.dispatchMouseEvent',{type,x:r.x,y:r.y,button:'left',clickCount:1});await wait(750)};
 try{
 await call('Page.enable');await call('Runtime.enable');await call('Network.enable');await call('Network.setBlockedURLs',{urls:['http://*','https://*']});
 for(const name of ['fallback','dynamic']){
  await call('Page.navigate',{url:'file:///'+path.join(dir,name+'.html').replaceAll('\\','/')});await wait(150);
  await evaluate(`bootstrap.Carousel.getOrCreateInstance(document.querySelector('.home-hero-carousel')).pause()`);let firstArtwork=null;
  for(const [width,height] of [[320,720],[360,800],[390,844],[430,900],[600,900],[767,1024],[768,1024],[1024,900],[1440,1000],[667,375]]){
   await resize(width,height);
   const a=JSON.parse(await evaluate(`JSON.stringify((()=>{const frame=document.querySelector('.carousel-item.active .home-hero-media'),img=frame.querySelector('img'),r=frame.getBoundingClientRect(),c=document.querySelector('.home-hero-carousel').getBoundingClientRect(),dots=document.querySelector('.carousel-indicators').getBoundingClientRect(),header=document.querySelector(innerWidth<992?'.buyer-mobile-header':'.buyer-desktop-header').getBoundingClientRect();return {width:r.width,height:r.height,rootTop:c.top,headerBottom:header.bottom,dotsDisplay:getComputedStyle(document.querySelector('.carousel-indicators')).display,rootHeight:c.height,scroll:document.documentElement.scrollWidth,fit:getComputedStyle(img).objectFit,naturalWidth:img.naturalWidth,naturalHeight:img.naturalHeight,artwork:img.currentSrc}})())`));
   assert.equal(a.dotsDisplay,'none');assert.ok(Math.abs(a.rootHeight-a.height)<2,'No indicator rail below banner');assert.equal(a.dotsDisplay,'none');assert.ok(Math.abs(a.rootHeight-a.height)<2,'No indicator rail below banner');assert.ok(a.scroll<=width+1,`${name}/${width}: overflow`);assert.ok(a.rootTop>=a.headerBottom,`${name}/${width}: header overlap`);assert.ok(a.naturalWidth>0,'Image must load');if(firstArtwork===null)firstArtwork=a.artwork;assert.equal(a.artwork,firstArtwork,'Changing viewport must not substitute a different banner');
   if(width<768){assert.equal(a.fit,'contain');assert.ok(Math.abs(a.width/a.height-a.naturalWidth/a.naturalHeight)<.01,'Mobile frame must follow original artwork ratio')}
   else assert.ok(Math.abs(a.width/a.height-a.naturalWidth/a.naturalHeight)<.01,'Desktop artwork ratio must remain unchanged');checks++;
  }
  for(const width of [320,430,1024]){
   await resize(width,900);await evaluate(`bootstrap.Carousel.getOrCreateInstance(document.querySelector('.home-hero-carousel')).to(0)`);await wait(750);
   await click('.carousel-control-next');assert.equal(await evaluate(`document.querySelector('.carousel-indicators button.active').getAttribute('data-bs-slide-to')`),'1');
   await click('.carousel-control-prev');assert.equal(await evaluate(`document.querySelector('.carousel-indicators button.active').getAttribute('data-bs-slide-to')`),'0');
   await click('.carousel-control-next');await click('.carousel-control-next');assert.equal(await evaluate(`document.querySelector('.carousel-indicators button.active').getAttribute('data-bs-slide-to')`),'2');checks++;
  }
 }
 await resize(390,844);const shot=await call('Page.captureScreenshot',{format:'png'});fs.writeFileSync(path.join(dir,'carousel-mobile.png'),Buffer.from(shot.data,'base64'));assert.deepEqual(errors,[]);console.log(`PASS: ${checks} responsive layout and slide-control cases.`);
 }finally{ws.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
