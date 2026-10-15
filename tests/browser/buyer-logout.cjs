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

 try {
 await call('Page.enable'); await call('Runtime.enable'); await call('Network.enable');
 await call('Network.setBlockedURLs',{urls:['http://*','https://*']});
 for (const width of [320,390,768,1440]) {
  await navigate('member'); await resize(width);
  const open = async () => {
   if(width<992) await toggle();
   await evaluate(`document.querySelector('${width<992?'#buyer-account-menu a[data-buyer-logout]':'.account-nav-logout a'}').click()`);
   await pause();
  };
  await evaluate("localStorage.setItem('logout-test','retained')");
  const original = await evaluate('location.href');
  await open();
  assert.equal(await evaluate("document.querySelector('.buyer-logout-dialog').open && document.activeElement.hasAttribute('data-logout-cancel')"),true,'Cancel initially focused');
  const layout = await evaluate(`(()=>{const d=document.querySelector('.buyer-logout-dialog'),r=d.getBoundingClientRect();return {left:r.left,right:r.right,bottom:r.bottom,top:r.top,buttons:[...d.querySelectorAll('button')].map(e=>e.getBoundingClientRect().height)}})()`);
  assert.ok(layout.left>=0&&layout.right<=width&&layout.top>=0&&layout.bottom<=800);
  assert.ok(layout.buttons.every(h=>h>=44));
  const shot=await call('Page.captureScreenshot',{format:'png'});fs.writeFileSync(path.join(dir,`logout-${width}.png`),Buffer.from(shot.data,'base64'));
  await evaluate("document.querySelector('[data-logout-cancel]').click()"); await pause();
  assert.equal(await evaluate('location.href'),original);
  assert.equal(await evaluate("localStorage.getItem('logout-test')"),'retained');
  assert.equal(await evaluate("document.querySelector('.buyer-logout-dialog').open"),false); count++;
  await open(); await key('Escape'); await pause();
  assert.equal(await evaluate("document.querySelector('.buyer-logout-dialog').open"),false);
  assert.equal(await evaluate('location.href'),original);count++;
  await open(); await click(2,2);await pause();
  assert.equal(await evaluate("document.querySelector('.buyer-logout-dialog').open"),false);count++;
  await open();
  // Local file fixture only: expected destination is a nonexistent local file, never production.
  const expected = await evaluate(`document.querySelector('${width<992?'#buyer-account-menu a[data-buyer-logout]':'.account-nav-logout a'}').href`);
  assert.ok(expected.startsWith('file:///'));
  await call('Runtime.evaluate',{expression:"document.querySelector('[data-logout-confirm]').click()"});
  await new Promise(r=>setTimeout(r,250));
  const history=await call('Page.getNavigationHistory');
  assert.equal(history.entries[history.currentIndex].url,expected,'Confirmation preserves original logout route');count++;
 }
 assert.deepEqual(errors,[]);console.log(`Buyer logout browser checks: ${count} passed.`);
 } finally {ws.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
