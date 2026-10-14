/* Local fixtures only. Generate with BUYER_NAV_ARTIFACTS=1 and BuyerNavigationViewTest.
   Connect to a dedicated headless Chrome profile under writable/cache/buyer-navigation-review. */
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
(async()=>{
 const dir=path.resolve(__dirname,'../../writable/cache/buyer-navigation-review');
 const port=fs.readFileSync(path.join(dir,'chrome-profile/DevToolsActivePort'),'utf8').split('\n')[0];assert.match(port,/^\d+$/);
 const tabs=await(await fetch(`http://127.0.0.1:${port}/json/list`)).json();
 const ws=new WebSocket(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);await new Promise(r=>ws.onopen=r);
 let id=0,count=0;const pending=new Map(),errors=[];
 ws.onmessage=e=>{const m=JSON.parse(e.data);if(m.method==='Runtime.exceptionThrown')errors.push(m.params.exceptionDetails.text);if(!m.id)return;const p=pending.get(m.id);if(!p)return;pending.delete(m.id);clearTimeout(p.timer);m.error?p.reject(new Error(m.error.message)):p.resolve(m.result)};
 const call=(method,params={})=>new Promise((resolve,reject)=>{const n=++id;const timer=setTimeout(()=>{pending.delete(n);reject(new Error('Timeout: '+method))},10000);pending.set(n,{resolve,reject,timer});ws.send(JSON.stringify({id:n,method,params}))});
 const evaluate=async expression=>{const r=await call('Runtime.evaluate',{expression,returnByValue:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.text);return r.result.value};
 const pause=()=>new Promise(r=>setTimeout(r,100));
 const resize=async(width,height=800)=>{await call('Emulation.setDeviceMetricsOverride',{width,height,deviceScaleFactor:1,mobile:width<992});await pause()};
 const click=async(x,y)=>{for(const type of ['mousePressed','mouseReleased'])await call('Input.dispatchMouseEvent',{type,x,y,button:'left',clickCount:1})};
 const toggle=async()=>{const c=JSON.parse(await evaluate(`JSON.stringify((()=>{const r=document.getElementById('buyer-account-toggle').getBoundingClientRect();return {x:r.x+r.width/2,y:r.y+r.height/2}})())`));await click(c.x,c.y)};
 const key=async(key,modifiers=0)=>{for(const type of ['keyDown','keyUp'])await call('Input.dispatchKeyEvent',{type,key,code:key,modifiers,windowsVirtualKeyCode:key==='Tab'?9:27})};
 const navigate=async state=>{await call('Page.navigate',{url:'file:///'+path.join(dir,state+'.html').replaceAll('\\','/')});await pause()};
 try{
 await call('Page.enable');await call('Runtime.enable');await call('Network.enable');await call('Network.setBlockedURLs',{urls:['http://*','https://*']});
 for(const state of ['member','guest','anonymous']){
  await navigate(state);
  for(const width of [320,360,390,430,600,601,768,820,991,992,1024,1440]){
   await resize(width);
   const a=JSON.parse(await evaluate(`JSON.stringify((()=>{const h=document.querySelector('.buyer-mobile-header'),d=document.querySelector('.buyer-desktop-header'),r=(innerWidth<992?h:d).getBoundingClientRect();return {scroll:document.documentElement.scrollWidth,mobile:getComputedStyle(h).display!=='none',desktop:getComputedStyle(d).display!=='none',header:r.bottom,content:document.querySelector('main').getBoundingClientRect().top}})())`));
   assert.ok(a.scroll<=width+1,`${state}/${width}: horizontal overflow ${a.scroll}`);assert.equal(a.mobile,width<992);assert.equal(a.desktop,width>=992);assert.ok(a.content>=a.header,`${state}/${width}: content overlaps header`);count++;
   if(width<992){const visible=await evaluate(`(()=>{const t=document.querySelector('.account-mobile-tabs'),a=t.querySelector('[aria-current="page"]'),r=t.getBoundingClientRect(),v=a.getBoundingClientRect();return v.left>=r.left-1&&v.right<=r.right+1})()`);assert.equal(visible,true,`${state}/${width}: active account tab should be visible`)}
   if(state!=='member'||width>=992)continue;
   await toggle();assert.equal(await evaluate(`!document.getElementById('buyer-account-menu').hidden && document.getElementById('buyer-account-toggle').getAttribute('aria-expanded')==='true' && document.documentElement.classList.contains('buyer-menu-open') && document.activeElement.hasAttribute('data-buyer-menu-close')`),true);
   await key('Tab',8);assert.equal(await evaluate(`document.activeElement.getAttribute('href')`),'/keluar');await key('Tab');assert.equal(await evaluate(`document.activeElement.hasAttribute('data-buyer-menu-close')`),true);
   await key('Escape');assert.equal(await evaluate(`document.getElementById('buyer-account-menu').hidden && !document.documentElement.classList.contains('buyer-menu-open') && document.activeElement.id==='buyer-account-toggle'`),true);
   await toggle();const close=JSON.parse(await evaluate(`JSON.stringify((()=>{const r=document.querySelector('[data-buyer-menu-close]').getBoundingClientRect();return {x:r.x+r.width/2,y:r.y+r.height/2}})())`));await click(close.x,close.y);assert.equal(await evaluate(`document.getElementById('buyer-account-menu').hidden`),true);
   await toggle();await click(2,798);assert.equal(await evaluate(`document.getElementById('buyer-account-menu').hidden`),true);count++;
  }
 }
 await navigate('member');await resize(667,375);await toggle();
 const p=JSON.parse(await evaluate(`JSON.stringify((()=>{const p=document.querySelector('.buyer-account-panel'),r=p.getBoundingClientRect();return {top:r.top,bottom:r.bottom,scrollable:p.scrollHeight>p.clientHeight}})())`));
 assert.ok(p.top>=0&&p.bottom<=375&&p.scrollable,'Landscape panel must fit and scroll');await resize(1024);assert.equal(await evaluate(`document.getElementById('buyer-account-menu').hidden && !document.documentElement.classList.contains('buyer-menu-open')`),true);
 await resize(390,844);await toggle();const shot=await call('Page.captureScreenshot',{format:'png'});fs.writeFileSync(path.join(dir,'member-menu-mobile.png'),Buffer.from(shot.data,'base64'));
 for(const page of ['categories-home','categories-all']){
  assert.ok(fs.existsSync(path.join(dir,page+'.html')), 'Generate category fixtures with BuyerNavigationViewTest first');
  await navigate(page);
  for(const [width,height] of [[320,568],[667,375],[768,600]]){
   await resize(width,height);
   const c=JSON.parse(await evaluate(`JSON.stringify((()=>{const r=document.querySelector('.kategori-arrow').getBoundingClientRect();return {x:r.x+r.width/2,y:r.y+r.height/2}})())`));await click(c.x,c.y);
   const bounds=JSON.parse(await evaluate(`JSON.stringify((()=>{const m=document.querySelector('.kategori-dropdown-menu'),r=m.getBoundingClientRect(),b=document.querySelector('.mobile-bottom-nav').getBoundingClientRect(),h=document.querySelector('.buyer-mobile-header').getBoundingClientRect();return {left:r.left,right:r.right,top:r.top,bottom:r.bottom,bottomLimit:b.top,header:h.bottom,open:document.querySelector('.kategori-wrap').classList.contains('show')}})())`));
   assert.ok(bounds.open&&bounds.left>=0&&bounds.right<=width&&bounds.top>=bounds.header&&bounds.bottom<=bounds.bottomLimit,`${page}/${width}: dropdown outside available viewport ${JSON.stringify(bounds)}`);
   await toggle();assert.equal(await evaluate(`!document.querySelector('.kategori-wrap').classList.contains('show') && !document.body.classList.contains('kategori-dropdown-open')`),true);await key('Escape');count++;
  }
 }
 assert.deepEqual(errors,[],'No runtime errors expected');console.log(`PASS: ${count+2} navigation layout/interaction cases; screenshot saved locally.`);
 }finally{ws.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
