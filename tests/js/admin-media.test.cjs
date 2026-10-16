const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../public/js/admin-media.js'), 'utf8');

function harness() {
    const input = {files:[],value:'',listeners:{},attributes:{},addEventListener(n,fn){this.listeners[n]=fn;},setAttribute(n,v){this.attributes[n]=v;},setCustomValidity(v){this.invalid=v;}};
    Object.defineProperty(input,'value',{set(v){this._value=v;if(v==='')this.files=[];},get(){return this._value;}});
    const preview = {src:'saved.webp',dataset:{},getAttribute(){return this.src;},classList:{remove(){}}};
    const feedback = {dataset:{},textContent:''};
    const reset = {hidden:true,addEventListener(n,fn){this.click=fn;}};
    const state = {textContent:'Tersimpan'};
    const form = {listeners:[],addEventListener(n,fn){this.listeners.push(fn);}};
    input.form = form;
    const map = {'[data-image-input]':input,'[data-image-preview]':preview,'[data-image-feedback]':feedback,'[data-reset-image]':reset,'[data-image-state]':state};
    const field = {dataset:{maxBytes:'4194304'},querySelector(q){return map[q]||null;}};
    const document = {querySelectorAll(q){return q==='.admin-image-field'?[field]:[];},querySelector(){return null;},getElementById(){return null;}};
    const decoded=[]; const revoked=[];
    class Image {constructor(){decoded.push(this);this.naturalWidth=800;this.naturalHeight=600;}set src(v){this._src=v;}}
    let id=0;
    vm.runInNewContext(source,{document,Image,URL:{createObjectURL(){return `blob:test-${++id}`;},revokeObjectURL(v){revoked.push(v);}}});
    const choose = file => {input.files=[file];input._value=file.name;input.listeners.change();};
    return {input,preview,feedback,reset,state,form,decoded,revoked,choose};
}
test('valid selection keeps FileList mounted and previews only after decode',()=>{
    const h=harness();const file={name:'meja.webp',type:'image/webp',size:2048};h.choose(file);
    assert.equal(h.preview.src,'saved.webp');h.decoded[0].onload();
    assert.equal(h.input.files[0],file);assert.equal(h.preview.src,'blob:test-1');assert.equal(h.reset.hidden,false);
    assert.match(h.feedback.textContent,/meja.webp/);assert.equal(h.input.invalid,'');
});
test('reset cancels new file only and restores saved media',()=>{
    const h=harness();h.choose({name:'meja.avif',type:'image/avif',size:2048});h.decoded[0].onload();h.reset.click();
    assert.equal(h.input.files.length,0);assert.equal(h.preview.src,'saved.webp');assert.deepEqual(h.revoked,['blob:test-1']);
});
test('invalid extension or MIME never replaces old preview',()=>{
    for(const file of [{name:'fake.webp',type:'image/png',size:20},{name:'image.jpg',type:'image/jpeg',size:20}]){
        const h=harness();h.choose(file);assert.equal(h.input.files.length,0);assert.equal(h.preview.src,'saved.webp');assert.ok(h.input.invalid);assert.equal(h.decoded.length,0);
    }
});
test('oversized or empty files are blocked before object URL creation',()=>{
    for(const size of [0,4194305]){const h=harness();h.choose({name:'meja.webp',type:'image/webp',size});assert.equal(h.decoded.length,0);assert.ok(h.input.invalid);}
});
test('submission waits for preview decode and corrupt images clear pending files',()=>{
    const h=harness();h.choose({name:'meja.webp',type:'image/webp',size:2048});let prevented=false;
    h.form.listeners[0]({preventDefault(){prevented=true;}});assert.equal(prevented,true);
    h.decoded[0].onerror();assert.equal(h.preview.src,'saved.webp');assert.equal(h.input.files.length,0);assert.ok(h.input.invalid);
});
test('late decode from a replaced file cannot overwrite the latest preview',()=>{
    const h=harness();h.choose({name:'first.webp',type:'image/webp',size:2048});h.choose({name:'second.webp',type:'image/webp',size:2048});
    h.decoded[1].onload();h.decoded[0].onload();assert.equal(h.preview.src,'blob:test-2');assert.match(h.feedback.textContent,/second.webp/);
});
test('excessive image dimensions are rejected without replacing saved media',()=>{
    const h=harness();h.choose({name:'huge.webp',type:'image/webp',size:2048});h.decoded[0].naturalWidth=4097;h.decoded[0].onload();
    assert.equal(h.preview.src,'saved.webp');assert.equal(h.input.files.length,0);assert.ok(h.input.invalid);
});
