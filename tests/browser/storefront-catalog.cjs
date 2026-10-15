/* Read-only browser checks against cards rendered from actual PHP fragments. */
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
(async()=>{
 const dir=path.resolve(__dirname,'../../writable/cache/catalog-review'),port=fs.readFileSync(path.join(dir,'chrome-profile/DevToolsActivePort'),'utf8').split('\n')[0];
 const tabs=await(await fetch(`http://127.0.0.1:${port}/json/list`)).json(),ws=new WebSocket(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);await new Promise(r=>ws.onopen=r);
 let seq=0,checks=0;const pending=new Map();
 ws.onmessage=e=>{const m=JSON.parse(e.data);if(m.id){const p=pending.get(m.id);if(!p)return;pending.delete(m.id);clearTimeout(p.timer);m.error?p.reject(new Error(m.error.message)):p.resolve(m.result)}};
 const call=(method,params={})=>new Promise((resolve,reject)=>{const id=++seq,timer=setTimeout(()=>reject(new Error('Timeout '+method)),10000);pending.set(id,{resolve,reject,timer});ws.send(JSON.stringify({id,method,params}))});
 const wait=ms=>new Promise(r=>setTimeout(r,ms));
 const evaluate=async expression=>{const r=await call('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.text);return r.result.value};
 try{
 await call('Page.enable');await call('Runtime.enable');await call('Network.enable');await call('Network.setBlockedURLs',{urls:['http://*','https://*']});
 await call('Page.navigate',{url:'file:///'+path.join(dir,'catalog.html').replaceAll('\\','/')});await wait(500);await evaluate('document.fonts.ready.then(()=>true)');
 for(const width of [320,360,390,430,575,600,768,1024,1440]){
  await call('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<768});await wait(150);
  const result=await evaluate(`(()=>{const s=e=>getComputedStyle(e),rect=e=>e.getBoundingClientRect();return {scroll:document.documentElement.scrollWidth,font:document.fonts.check('600 14px Montserrat'),titles:[...document.querySelectorAll('.jdl-section')].map(e=>({before:getComputedStyle(e,'::before').display,font:s(e).fontFamily})),grids:[...document.querySelectorAll('.card-group1')].map(e=>s(e).gridTemplateColumns),cards:[...document.querySelectorAll('.store-product-card')].map(e=>{const img=e.querySelector('.img-card1'),title=e.querySelector('h5');return {width:rect(e).width,scroll:e.scrollWidth,shadow:s(e).boxShadow,border:s(e).borderTopWidth,fit:s(img).objectFit,imageLoaded:img.naturalWidth>0,titleOverflow:s(title).overflow,titleClamp:s(title).webkitLineClamp,fields:[...e.querySelectorAll('.harga,.varian,.diskon-coret,.diskon,.terjual-badge')].map(f=>({name:f.className,display:s(f).display,w:rect(f).width,scroll:f.scrollWidth,left:rect(f).left,right:rect(f).right,cardleft:rect(e).left,cardright:rect(e).right}))}})}})()`);
  assert.ok(result.scroll<=width+1,`${width}: page overflow`);assert.ok(result.font,'Local Montserrat loaded');result.titles.forEach(t=>{assert.equal(t.before,'none');assert.ok(t.font.includes('Montserrat'))});
  if(width<=575)result.grids.forEach(g=>assert.equal(g.split(' ').length,2,`${width}: two columns`));
  result.cards.forEach((c,i)=>{assert.equal(c.shadow,'none');assert.equal(c.border,'0px');assert.equal(c.fit,'contain');assert.ok(c.imageLoaded);assert.equal(c.titleOverflow,'visible');assert.ok(c.scroll<=c.width+1,`${width}/${i}: card overflow`);c.fields.forEach(f=>{assert.notEqual(f.display,'none',`${width}/${i}: ${f.name} hidden`);assert.ok(f.scroll<=f.w+1,`${width}/${i}: ${f.name} clipped`);assert.ok(f.left>=f.cardleft-1&&f.right<=f.cardright+1,`${width}/${i}: ${f.name} outside card`)});checks++});
  if([390,1440].includes(width)){await evaluate("window.scrollTo(0,0)");const shot=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});fs.writeFileSync(path.join(dir,`catalog-${width}.png`),Buffer.from(shot.data,'base64'))}
 }
 console.log(`PASS: ${checks} responsive card checks across 9 widths, with actual PHP prices and variant data.`);
 }finally{ws.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
