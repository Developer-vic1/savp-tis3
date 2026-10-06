import test from 'node:test';
import assert from 'node:assert/strict';
import { crearPausaAcceso } from '../../resources/js/pausa-acceso.js';

const politica = { intentos: 3, pausa_inicial: 15, pausa_maxima: 300, vigencia: 3600 };

test('tres intentos, incremento progresivo y tope sin espera real', () => {
    let ahora = 100_000;
    const pausa = crearPausaAcceso(politica, {}, () => ahora);
    for (const segundos of [15, 30, 60, 120, 240, 300, 300]) {
        pausa.registrarFallo();
        assert.equal(pausa.restantes(), 0);
        pausa.registrarFallo();
        assert.equal(pausa.restantes(), 0);
        pausa.registrarFallo();
        assert.equal(pausa.restantes(), segundos);
        const antes = pausa.estado();
        pausa.registrarFallo();
        assert.deepEqual(pausa.estado(), antes, 'Los clics bloqueados no extienden la pausa.');
        ahora += segundos * 1000;
    }
});

test('restaurar tras recargar conserva plazo y nivel', () => {
    let ahora = 100_000;
    const pausa = crearPausaAcceso(politica, {}, () => ahora);
    for (let i = 0; i < 3; i++) pausa.registrarFallo();
    ahora += 5000;
    const restaurada = crearPausaAcceso(politica, pausa.estado(), () => ahora);
    assert.equal(restaurada.restantes(), 10);
    ahora += 10_000;
    for (let i = 0; i < 3; i++) restaurada.registrarFallo();
    assert.equal(restaurada.restantes(), 30);
});

test('una hora sin intentos y el reinicio eliminan el historial local', () => {
    let ahora = 100_000;
    const pausa = crearPausaAcceso(politica, {}, () => ahora);
    for (let i = 0; i < 3; i++) pausa.registrarFallo();
    ahora += 3_600_000;
    assert.equal(pausa.estado().nivel, 0);
    pausa.registrarFallo();
    pausa.reiniciar();
    assert.equal(pausa.estado().fallos, 0);
});

test('estado local dañado no produce bloqueos infinitos', () => {
    const pausa = crearPausaAcceso(politica, { nivel: Infinity, hasta: -1 }, () => 100_000);
    assert.equal(pausa.restantes(), 0);
    assert.equal(pausa.estado().nivel, 0);
});
