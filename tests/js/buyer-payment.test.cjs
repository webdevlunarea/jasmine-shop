const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../public/js/buyer-payment.js'), 'utf8');

function fixture({ expiry = 102000, stored = null, blocked = false, hidden = false } = {}) {
    const clock = {}, message = {}, events = {}, buttonEvents = {};
    let elapsed = 0, tick, reloads = 0, saved = stored, confirm;
    const root = { dataset: { expiryMs: String(expiry), serverNow: '100000', orderId: 'LTEST' } };
    const button = { dataset: { cancelOrder: '/cancelorder/LTEST' }, addEventListener: (event, fn) => { buttonEvents[event] = fn; } };
    const document = { hidden, querySelectorAll: () => [button], addEventListener: (event, fn) => { events[event] = fn; },
        getElementById: id => ({ 'payment-deadline': root, waktu: clock, 'payment-deadline-message': message }[id]) };
    const context = { document, performance: { now: () => elapsed }, setInterval: fn => { tick = fn; },
        sessionStorage: { getItem: () => stored, setItem: (key, value) => { if (blocked) throw Error('blocked'); saved = value; } },
        window: { location: { reload: () => { reloads++; } }, triggerToast: (...args) => { confirm = args; } } };
    vm.runInNewContext(source, context);
    return { clock, message, events, buttonEvents, advance: ms => { elapsed = ms; tick(); }, reloads: () => reloads, saved: () => saved, confirm: () => confirm };
}

test('zero countdown refreshes backend once, never sets payment status', () => {
    const f = fixture(); assert.equal(f.clock.textContent, '00:00:02');
    f.advance(2001); assert.equal(f.clock.textContent, '00:00:00'); assert.equal(f.reloads(), 1);
    f.advance(5000); assert.equal(f.reloads(), 1); assert.equal(JSON.parse(f.saved()).count, 1);
});
test('expired status reads are bounded across reloads', () => {
    const f = fixture({ expiry: 90000, stored: JSON.stringify({ count: 5, at: 1 }) });
    assert.equal(f.reloads(), 0); assert.match(f.message.textContent, /Jangan membayar ulang/);
});
test('retry backoff avoids immediate reload loop', () => {
    const f = fixture({ expiry: 90000, stored: JSON.stringify({ count: 1, at: 99000 }) });
    assert.equal(f.reloads(), 0); f.advance(30000); assert.equal(f.reloads(), 1);
});
test('invalid deadline does not fabricate expiry or send requests', () => {
    const f = fixture({ expiry: 0 }); assert.equal(f.clock.textContent, '--:--:--'); assert.equal(f.reloads(), 0);
});
test('blocked storage and background tabs never start a reload loop', () => {
    const blocked = fixture({ expiry: 90000, blocked: true }); blocked.advance(50000); assert.equal(blocked.reloads(), 0);
    const hidden = fixture({ expiry: 90000, hidden: true }); assert.equal(hidden.reloads(), 0);
});
test('submitting cancellation pauses expiry refresh and uses existing POST confirmation', () => {
    const f = fixture(); f.buttonEvents.click(); assert.equal(f.confirm()[1], '/cancelorder/LTEST');
    f.events.submit(); f.advance(5000); assert.equal(f.reloads(), 0);
});
