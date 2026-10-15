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
 const resize=async(width,height=800)=>{await call('Emulation.setDeviceMetricsOverride',{width,height,deviceScaleFactor:1,mobile:width<992});await pause();await evaluate("window.scrollTo({top:0,left:0,behavior:'instant'})");await pause()};
 const click=async(x,y)=>{for(const type of ['mousePressed','mouseReleased'])await call('Input.dispatchMouseEvent',{type,x,y,button:'left',clickCount:1})};
 const toggle=async()=>{const c=JSON.parse(await evaluate(`JSON.stringify((()=>{const r=document.getElementById('buyer-account-toggle').getBoundingClientRect();return {x:r.x+r.width/2,y:r.y+r.height/2}})())`));await click(c.x,c.y)};
 const key=async(key,modifiers=0)=>{for(const type of ['keyDown','keyUp'])await call('Input.dispatchKeyEvent',{type,key,code:key,modifiers,windowsVirtualKeyCode:key==='Tab'?9:27})};
 const navigate=async state=>{await call('Page.navigate',{url:'file:///'+path.join(dir,state+'.html').replaceAll('\\','/')});await pause()};
 try{
 await call('Page.enable');await call('Runtime.enable');await call('Network.enable');await call('Network.setBlockedURLs',{urls:['http://*','https://*']});
 for(const state of ['member','guest','anonymous','member-long-promo']){
  await navigate(state);
  for(const width of [320,360,390,430,600,601,768,820,991,992,1024,1440]){
   await resize(width);
   const a=JSON.parse(await evaluate(`JSON.stringify((()=>{const h=document.querySelector('.buyer-mobile-header'),d=document.querySelector('.buyer-desktop-header'),r=(innerWidth<992?h:d).getBoundingClientRect();return {scroll:document.documentElement.scrollWidth,scrollY:window.scrollY,mobile:getComputedStyle(h).display!=='none',desktop:getComputedStyle(d).display!=='none',headerTop:r.top,promoBottom:Math.max(...[...document.querySelectorAll('.buyer-promo-banner')].filter(e=>getComputedStyle(e).display!=='none').map(e=>e.getBoundingClientRect().bottom)),promoCount:[...document.querySelectorAll('.buyer-promo-banner')].filter(e=>getComputedStyle(e).display!=='none').length,icons:document.querySelectorAll('.mobile-bottom-nav .buyer-nav-icon').length,header:r.bottom,content:document.querySelector('main').getBoundingClientRect().top}})())`));
   assert.ok(a.scroll<=width+1,`${state}/${width}: horizontal overflow ${a.scroll}`);assert.equal(a.mobile,width<992);assert.equal(a.desktop,width>=992);assert.ok(a.content>=a.header,`${state}/${width}: content overlaps header ${JSON.stringify(a)}`);assert.equal(a.promoCount,1);assert.equal(a.icons,3);assert.ok(a.headerTop>=a.promoBottom-1,`${state}/${width}: promo covers navigation (${a.promoBottom} > ${a.headerTop})`);count++;
   if(width<992){
    const brand=JSON.parse(await evaluate(`JSON.stringify((()=>{const b=document.querySelector('.mobile-navbar-brand'),r=b.getBoundingClientRect(),a=document.querySelector('.mobile-navbar-actions').getBoundingClientRect();return {text:b.textContent.trim(),images:b.querySelectorAll('img').length,overlap:r.right>a.left+1&&r.bottom>a.top+1,actions:[...document.querySelectorAll('.mobile-navbar-actions > .btn')].map(e=>{const s=getComputedStyle(e),r=e.getBoundingClientRect();return {background:s.backgroundColor,border:s.borderTopWidth,shadow:s.boxShadow,width:r.width,height:r.height}})}})())`));
    assert.equal(brand.text,'Lunarea');assert.equal(brand.images,0);assert.equal(brand.overlap,false,`${state}/${width}: wordmark overlaps actions`);
    for(const action of brand.actions){assert.equal(action.background,'rgba(0, 0, 0, 0)');assert.equal(action.border,'0px');assert.equal(action.shadow,'none');assert.ok(action.width>=44&&action.height>=44)}
   }
   if(width<992){const visible=await evaluate(`(()=>{const t=document.querySelector('.account-mobile-tabs'),a=t.querySelector('[aria-current="page"]'),r=t.getBoundingClientRect(),v=a.getBoundingClientRect();return v.left>=r.left-1&&v.right<=r.right+1})()`);assert.equal(visible,true,`${state}/${width}: active account tab should be visible`)}
   if([320,390,768].includes(width)){
    assert.equal(await evaluate(`document.getElementById('buyer-mobile-search').hidden`),true,'Search initially collapsed');
    const c=JSON.parse(await evaluate(`JSON.stringify((()=>{const r=document.getElementById('buyer-search-toggle').getBoundingClientRect();return {x:r.x+r.width/2,y:r.y+r.height/2}})())`));
    await click(c.x,c.y);await pause();
    assert.equal(await evaluate(`!document.getElementById('buyer-mobile-search').hidden && document.getElementById('buyer-search-toggle').getAttribute('aria-expanded')==='true' && document.activeElement===document.querySelector('#buyer-mobile-search input')`),true,'Search opens and focuses input');
    if(await evaluate(`document.querySelector('#buyer-mobile-search input').value===''`))await call('Input.insertText',{text:'Lemari putih'});
    await key('Escape');assert.equal(await evaluate(`document.getElementById('buyer-mobile-search').hidden && document.activeElement.id==='buyer-search-toggle'`),true,'Escape closes search');
    await click(c.x,c.y);await pause();assert.equal(await evaluate(`document.querySelector('#buyer-mobile-search input').value`),'Lemari putih','Query retained after closing');
    await call('Input.dispatchMouseEvent',{type:'mouseWheel',x:width/2,y:600,deltaX:0,deltaY:160});await new Promise(r=>setTimeout(r,250));
    assert.equal(await evaluate(`document.getElementById('buyer-mobile-search').hidden && document.getElementById('buyer-search-toggle').getAttribute('aria-expanded')==='false'`),true,'Downward scrolling hides search');
    if(width===390){
     await evaluate("window.scrollTo({top:240,left:0,behavior:'instant'})");await pause();await click(c.x,c.y);await new Promise(r=>setTimeout(r,200));
     assert.equal(await evaluate(`!document.getElementById('buyer-mobile-search').hidden`),true,'Search must stay open when launched after scrolling');await key('Escape');
    }
    await evaluate("window.scrollTo({top:0,left:0,behavior:'instant'})");await pause();count++;
   }
   if(!state.startsWith('member')||width>=992)continue;
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
 await key('Escape');const navigationShot=await call('Page.captureScreenshot',{format:'png'});fs.writeFileSync(path.join(dir,'member-navigation-mobile.png'),Buffer.from(navigationShot.data,'base64'));
 // A configured promo can change height without a viewport resize.
 await evaluate(`document.querySelectorAll('.buyer-promo-banner p').forEach(p=>{p.textContent='Harga khusus pembelian pertama dan gratis ongkir hingga 100%. '.repeat(4)})`);await pause();
 assert.equal(await evaluate(`(()=>{const p=[...document.querySelectorAll('.buyer-promo-banner')].find(e=>getComputedStyle(e).display!=='none').getBoundingClientRect(),h=document.querySelector('.buyer-mobile-header').getBoundingClientRect();return h.top>=p.bottom-1&&document.querySelector('main').getBoundingClientRect().top>=h.bottom})()`),true,'Promo height changes must update navbar and spacer');count++;

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
 await navigate('theme-sand');await resize(1024,900);
 const preset=JSON.parse(await evaluate(`JSON.stringify((()=>{const b=[...document.querySelectorAll('.preset-btn')].find(e=>e.textContent.includes('Lunarea Sand'));b.scrollIntoView({block:'center',behavior:'instant'});const r=b.getBoundingClientRect();return {x:r.x+r.width/2,y:r.y+r.height/2}})())`));await click(preset.x,preset.y);
 assert.equal(await evaluate(`document.getElementById('primary').value.toUpperCase()`),'#2F2D20');
 assert.equal(await evaluate(`document.getElementById('accent').value.toUpperCase()`),'#C7A78A');
 assert.equal(await evaluate(`document.getElementById('background').value.toUpperCase()`),'#FEFEFF');count++;
 assert.deepEqual(errors,[],'No runtime errors expected');console.log(`PASS: ${count+2} navigation layout/interaction cases; screenshot saved locally.`);
 }finally{ws.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
