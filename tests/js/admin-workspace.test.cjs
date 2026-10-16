const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = name => fs.readFileSync(path.join(__dirname, '../../public/js', name), 'utf8');

class Element {
    constructor() {
        this.events = {}; this.dataset = {}; this.attributes = {}; this.hidden = false; this.value = ''; this.textContent = '';
        this.classes = new Set(); this.classList = {add:c=>this.classes.add(c), remove:c=>this.classes.delete(c), toggle:(c,v)=>v?this.classes.add(c):this.classes.delete(c)};
        this.queries = {}; this.queryLists = {};
    }
    addEventListener(name, handler) { (this.events[name] ||= []).push(handler); }
    emit(name, values = {}) { const event = {target:this, preventDefault(){this.defaultPrevented=true;}, ...values}; (this.events[name] || []).forEach(fn=>fn(event)); return event; }
    getAttribute(name) { return this.attributes[name]; }
    setAttribute(name, value) { this.attributes[name] = value; }
    removeAttribute(name) { delete this.attributes[name]; }
    querySelector(selector) { return this.queries[selector] || null; }
    querySelectorAll(selector) { return this.queryLists[selector] || []; }
    focus() { this.focused = true; }
    reportValidity() { return this.valid !== false; }
    getClientRects() { return [1]; }
}

function ordersHarness(options = {}) {
    const ids = Object.fromEntries(['admin-orders-data','orders-search','orders-search-feedback','orders-status','container-edit-resi','container-bukti-bayar','shipping-save-feedback','shipping-save-button','shipping-editor-form','shipping-awb','shipping-courier','internal-shipping-status','shipping-editor-title','shipping-editor-order','external-shipping-courier','awb-label','payment-proof-order','payment-proof-image','payment-proof-accept','payment-proof-reject','btn-reload','order-detail-0','order-detail-1'].map(id=>[id,new Element()]));
    const config = {orders:[{id_midtrans:'L227',nama_pen:'Pembeli Putih',email_cus:'putih@example.test',status:'Proses',resi:'Menunggu pengiriman',kurir:'JNE',shipment:{shipping_method_code:'rajaongkir_domestic',shipping_status:'Disiapkan'}},{id_midtrans:'L228',nama_pen:'Pembeli Kedua',email_cus:'dua@example.test',status:'Menunggu Pembayaran Rekening',bukti_bayar:'/imgbuktibayar/L228'}],csrfName:'csrf_test',csrfHash:'old-hash',wsUrl:'invalid-url',...options.config};
    ids['admin-orders-data'].textContent=JSON.stringify(config);
    const rows=[new Element(),new Element()]; rows.forEach((row,i)=>{row.dataset.orderIndex=String(i)});
    const toggles=[new Element(),new Element()]; toggles.forEach((e,i)=>{e.attributes={'aria-expanded':'false','aria-controls':'order-detail-'+i};ids['order-detail-'+i].hidden=true});
    const form=new Element(); form.queries['button[type="submit"]']=new Element();
    const clear=new Element(), empty=new Element(), shipping=new Element(), retry=new Element(), proof=new Element(), csrf=new Element();
    shipping.dataset.shippingIndex='0'; retry.dataset.shippingRetry='0'; proof.dataset.proofIndex='1'; csrf.name='csrf_test';
    const lists={'.list-customer[data-order-index]':rows,'[data-order-toggle]':toggles,'.order-sync-form':[form],'[data-complete-index]':[],'[data-shipping-index]':[shipping],'[data-shipping-retry]':[retry],'[data-proof-index]':[proof],'#container-bukti-bayar form':[ids['payment-proof-accept'],ids['payment-proof-reject']],'input[type="hidden"]':[csrf]};
    ids['container-bukti-bayar'].queryLists['button[type="submit"]']=[new Element(),new Element()];
    const document={getElementById:id=>ids[id]||null,querySelectorAll:q=>lists[q]||[],querySelector:q=>q==='.orders-search-empty'?empty:q==='[data-clear-order-search]'?clear:null};
    const window=new Element(); window.location={protocol:'https:',href:'',reload(){window.reloaded=true}};
    window.bootstrap={Modal:{getOrCreateInstance:el=>({show(){el.shown=true}})}};
    const requests=[];
    const fetch=async (url,init)=>{requests.push({url,init});if(options.fetch)return options.fetch(url,init);return {ok:true,json:async()=>({success:true,csrf_hash:'new-hash',message:'Tersimpan'})}};
    vm.runInNewContext(source('admin-orders.js'),{document,window,URL,fetch,WebSocket:class {constructor(){throw new Error('Unexpected socket')}}});
    return {ids,config,rows,toggles,form,clear,empty,shipping,retry,proof,csrf,window,requests};
}

test('order details toggle with keyboard-compatible buttons, not a row-wide click handler',()=>{
    const h=ordersHarness();h.toggles[0].emit('click');assert.equal(h.ids['order-detail-0'].hidden,false);assert.equal(h.toggles[0].getAttribute('aria-expanded'),'true');
    h.toggles[1].emit('click');assert.equal(h.ids['order-detail-0'].hidden,true);assert.equal(h.ids['order-detail-1'].hidden,false);
    h.toggles[1].emit('click');assert.equal(h.ids['order-detail-1'].hidden,true);
});
test('page-local search is case insensitive and clear restores rows without API requests',()=>{
    const h=ordersHarness();h.ids['orders-search'].value='PUTIH';h.ids['orders-search'].emit('input');assert.equal(h.rows[0].hidden,false);assert.equal(h.rows[1].hidden,true);
    h.ids['orders-search'].value='not found';h.ids['orders-search'].emit('input');assert.equal(h.empty.hidden,false);
    h.clear.emit('click');assert.equal(h.rows[1].hidden,false);assert.equal(h.empty.hidden,true);assert.equal(h.requests.length,0);
});
test('review form keeps required-field validation and blocks duplicate submissions',()=>{
    const h=ordersHarness();h.form.valid=false;assert.equal(h.form.emit('submit').defaultPrevented,true);assert.equal(h.form.dataset.sending,undefined);
    h.form.valid=true;h.form.emit('submit');assert.equal(h.form.queries['button[type="submit"]'].disabled,true);assert.equal(h.form.emit('submit').defaultPrevented,true);
});
test('shipping preserves status, uses POST plus CSRF and refreshes hidden tokens',async()=>{
    const h=ordersHarness();h.shipping.emit('click');assert.equal(h.ids['container-edit-resi'].shown,true);assert.equal(h.ids['shipping-courier'].readOnly,true);
    h.ids['shipping-editor-form'].emit('submit');assert.equal(h.requests.length,0);
    h.ids['shipping-awb'].value='TEST227';h.ids['shipping-editor-form'].emit('submit');await new Promise(setImmediate);
    assert.equal(h.requests.length,1);assert.equal(h.requests[0].url,'/editresi');assert.equal(h.requests[0].init.method,'POST');
    assert.deepEqual(JSON.parse(h.requests[0].init.body),{idMid:'L227',resi:'TEST227',kurir:'JNE',shipping_status:'Disiapkan',retry_sync:false,csrf_test:'old-hash'});
    assert.equal(h.csrf.value,'new-hash');h.ids['container-edit-resi'].emit('hidden.bs.modal');assert.equal(h.window.reloaded,true);
});
test('shipping in progress blocks double save and modal dismissal',async()=>{
    let resolve;const delayed=new Promise(r=>{resolve=r});const h=ordersHarness({fetch:()=>delayed});h.shipping.emit('click');h.ids['shipping-awb'].value='TEST227';
    h.ids['shipping-editor-form'].emit('submit');h.ids['shipping-editor-form'].emit('submit');assert.equal(h.requests.length,1);
    assert.equal(h.ids['container-edit-resi'].emit('hide.bs.modal').defaultPrevented,true);assert.equal(h.ids['shipping-awb'].disabled,true);
    resolve({ok:true,json:async()=>({success:true,message:'OK'})});await new Promise(setImmediate);assert.equal(h.ids['shipping-save-button'].disabled,false);
});
test('shipping error remains visible and retry-only request never submits as an order import',async()=>{
    const h=ordersHarness({fetch:async()=>({ok:false,json:async()=>({success:false,message:'Periksa status pusat',csrf_hash:'updated'})})});h.retry.emit('click');await new Promise(setImmediate);
    assert.equal(JSON.parse(h.requests[0].init.body).retry_sync,true);assert.equal(h.ids['shipping-save-feedback'].dataset.error,'true');assert.equal(h.ids['shipping-save-feedback'].textContent,'Periksa status pusat');assert.equal(h.csrf.value,'updated');
});
test('proof review keeps correct authenticated POST targets and does not submit automatically',()=>{
    const h=ordersHarness();h.proof.emit('click');assert.equal(h.ids['payment-proof-accept'].action,'/payorder/L228/confirm/accept');assert.equal(h.ids['payment-proof-reject'].action,'/payorder/L228/confirm/cancel');assert.equal(h.ids['payment-proof-accept'].hidden,false);assert.equal(h.ids['payment-proof-image'].src,'/imgbuktibayar/L228');assert.equal(h.requests.length,0);
});

test('mobile drawer restores focus, closes on Escape and unlocks on desktop resize',()=>{
    const toggle=new Element(),sidebar=new Element(),trigger=new Element(),close=new Element(),scrim=new Element(),body=new Element(),media=new Element(),last=new Element();media.matches=false;
    sidebar.queries['[data-admin-menu-close]']=close;sidebar.queryLists['a[href], button:not([disabled])']=[close,last];
    const document=new Element();document.body=body;document.getElementById=id=>id==='admin-menu-toggle'?toggle:id==='admin-navigation'?sidebar:null;document.querySelector=q=>q==='[data-admin-menu-open]'?trigger:null;document.querySelectorAll=q=>q==='[data-admin-menu-close]'?[close,scrim]:[];
    vm.runInNewContext(source('admin-workspace.js'),{document,window:{matchMedia:()=>media}});
    assert.equal(sidebar.inert,true);trigger.emit('click');assert.equal(toggle.checked,true);assert.equal(trigger.getAttribute('aria-expanded'),'true');assert.equal(sidebar.inert,false);assert.equal(close.focused,true);
    document.activeElement=last;assert.equal(document.emit('keydown',{key:'Tab'}).defaultPrevented,true);
    document.emit('keydown',{key:'Escape'});assert.equal(toggle.checked,false);assert.equal(trigger.focused,true);assert.equal(sidebar.inert,true);
    trigger.emit('click');media.matches=true;media.emit('change');assert.equal(toggle.checked,false);assert.equal(sidebar.inert,false);assert.equal(body.classes.has('admin-menu-open'),false);
});
