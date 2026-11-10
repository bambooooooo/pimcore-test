import { afterEach, beforeEach, describe, expect, it } from 'vitest';
import { createAdminEnv, EVENTS } from './harness.js';

describe('addChildren.js - Ctrl+M creates objects from a key pattern', () => {
    let env;

    beforeEach(() => {
        env = createAdminEnv().load('braceExpansion', 'addChildren');
        env.fire(EVENTS.pimcoreReady);
    });

    afterEach(() => env.expectAllUrlsRoutable());

    const keyMap = () => env.components.find((c) => c.$class === 'Ext.util.KeyMap');
    const activeTab = (tab) => {
        env.cmps.pimcore_panel_tabs = { getActiveTab: () => tab };
    };

    it('binds Ctrl+M', () => {
        expect(keyMap().config).toMatchObject({ key: 'm', ctrl: true, alt: false, shift: false });
    });

    it.each([
        ['no open tab', null, 'Open object first that need to have sibling first.'],
        ['document or asset tab', { object: null }, 'Can not add children with kind other than object'],
        ['folder', { object: { data: { type: 'folder' } } }, 'Can not add folder children. Objects only.'],
    ])('refuses when %s', (_, tab, message) => {
        activeTab(tab);

        keyMap().config.fn();

        expect(env.messages).toEqual([expect.objectContaining({ type: 'show', msg: message })]);
        expect(env.ajax).toHaveLength(0);
    });

    it('creates one object per expanded key in the open object and refreshes the tree', () => {
        activeTab({ object: { data: { type: 'object', general: { id: 7, className: 'Product', classId: 'PR' } } } });

        keyMap().config.fn();
        const prompt = env.messages.find((m) => m.type === 'prompt');
        prompt.fn('ok', 'SZAFA-{80,100}');

        expect(env.ajax).toHaveLength(2);
        expect(env.ajax.map((a) => a.url)).toEqual(['/admin/object/add', '/admin/object/add']);
        expect(env.ajax[0]).toMatchObject({ method: 'POST', params: { parentId: 7, className: 'Product', classId: 'PR' } });

        env.ajax.forEach((request) => env.respond(request, { success: true }));

        expect(pimcore.helpers.showNotification).toHaveBeenCalledWith('Ok', 'Dodano: 2 / 2 obiektów', 'ok', 'Ok');
        expect(pimcore.elementservice.refreshNodeAllTrees).toHaveBeenCalledWith('object', 7);
    });

    it('does nothing when the prompt is cancelled', () => {
        activeTab({ object: { data: { type: 'object', general: { id: 7 } } } });

        keyMap().config.fn();
        env.messages.find((m) => m.type === 'prompt').fn('cancel', 'X{1,2}');

        expect(env.ajax).toHaveLength(0);
    });
});
