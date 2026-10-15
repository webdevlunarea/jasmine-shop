/* Local visual regression for the actual home-benefits PHP partial. */
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
(async()=>{
 const dir=path.resolve(__dirname,'../../writable/cache/home-benefits-review'),port=fs.readFileSync(path.join(dir,'chrome-profile/DevToolsActivePort'),'utf8').split('\n')[0];
 const tabs=await(await fetch(`http://127.0.0.1:${port}/json/list`)).json(),ws=new WebSocket(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);await new Promise(r=>ws.onopen=r);
 let seq=0,checks=0;const pending=new Map();
 ws.onmessage=e=>{const m=JSON.parse(e.data);if(m.id){const p=pending.get(m.id);if(!p)return;pending.delete(m.id);clearTimeout(p.timer);m.error?p.reject(new Error(m.error.message)):p.resolve(m.result)}};
 const call=(method,params={})=>new Promise((resolve,reject)=>{const id=++seq,timer=setTimeout(()=>reject(new Error('Timeout '+method)),10000);pending.set(id,{resolve,reject,timer});ws.send(JSON.stringify({id,method,params}))});
 const wait=ms=>new Promise(r=>setTimeout(r,ms));
 const evaluate=async expression=>{const r=await call('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.text);return r.result.value};
 try{
 await call('Page.enable');await call('Runtime.enable');await call('Network.enable');await call('Network.setBlockedURLs',{urls:['http://*','https://*']});
 for(const [theme,colors] of Object.entries({default:{bg:'rgb(250, 247, 242)',primary:'rgb(36, 59, 107)'},sand:{bg:'rgb(247, 242, 237)',primary:'rgb(47, 45, 32)'}})){
  await call('Page.navigate',{url:'file:///'+path.join(dir,theme+'.html').replaceAll('\\','/')});await wait(250);
  for(const width of [320,360,390,430,600,768,991,1024,1440]){
   await call('Emulation.setDeviceMetricsOverride',{width,height:800,deviceScaleFactor:1,mobile:width<768});await wait(100);
   const result=await evaluate(`(()=>{const s=e=>getComputedStyle(e),rect=e=>e.getBoundingClientRect(),box=document.querySelector('.home-benefits');return {scroll:document.documentElement.scrollWidth,bg:s(box).backgroundColor,color:s(box).color,columns:s(box.querySelector('ul')).gridTemplateColumns,items:[...box.querySelectorAll('li')].map(e=>({width:rect(e).width,scroll:e.scrollWidth,shadow:s(e).boxShadow,icon:s(e.querySelector('svg')).color,texts:[...e.querySelectorAll('h2,p')].map(t=>({display:s(t).display,width:rect(t).width,scroll:t.scrollWidth,size:parseFloat(s(t).fontSize)}))}))}})()`);
   assert.ok(result.scroll<=width+1,`${theme}/${width}: page overflow`);assert.equal(result.bg,colors.bg);assert.equal(result.color,colors.primary);assert.equal(result.columns.split(' ').length,width<992?1:3);
   assert.equal(result.items.length,3);result.items.forEach(e=>{assert.ok(e.scroll<=e.width+1);assert.equal(e.shadow,'none');assert.equal(e.icon,colors.primary);e.texts.forEach(t=>{assert.notEqual(t.display,'none');assert.ok(t.scroll<=t.width+1);assert.ok(t.size>=14)})});checks++;
   if([390,1440].includes(width)){const shot=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});fs.writeFileSync(path.join(dir,`${theme}-${width}.png`),Buffer.from(shot.data,'base64'))}
  }
 }
 // Large type must wrap naturally rather than clipping benefit descriptions.
 await evaluate(`document.documentElement.style.fontSize='200%'`);await call('Emulation.setDeviceMetricsOverride',{width:320,height:800,deviceScaleFactor:1,mobile:true});await wait(100);assert.equal(await evaluate(`document.documentElement.scrollWidth<=321`),true);checks++;
 console.log(`PASS: ${checks} benefit layout/theme checks including large text, mobile, tablet and desktop.`);
 }finally{ws.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
