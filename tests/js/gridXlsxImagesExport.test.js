import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { createAdminEnv, EVENTS } from './harness.js';

describe('gridXlsxImagesExport.js - "XLSX Export (Images)" in object grids', () => {
    let env;
    let original;

    beforeEach(() => {
        env = createAdminEnv();
        original = vi.fn(() => [{ text: 'CSV Export' }]);
        pimcore.object.helpers.gridTabAbstract.prototype.getExportButtons = original;
        env.load('gridXlsxImagesExport');
        env.fire(EVENTS.pimcoreReady);
    });

    afterEach(() => env.expectAllUrlsRoutable());

    const grid = () => {
        const tab = new pimcore.object.helpers.gridTabAbstract();
        Object.assign(tab, {
            exportPrepareUrl: '/admin/object-helper/get-export-jobs',
            classId: 'PR',
            gridLanguage: 'pl',
            context: { scope: 'objectEditor' },
            getGridParams: () => ({ classId: 'PR', folderId: 1 }),
            getGridConfig: () => ({ columns: { sku: { fieldConfig: { label: 'SKU' } }, image: {} } }),
            exportProcessCustom: vi.fn(),
        });

        return tab;
    };

    it('keeps the standard export buttons and adds the image export', () => {
        const buttons = grid().getExportButtons();

        expect(original).toHaveBeenCalled();
        expect(buttons.map((b) => b.text)).toEqual(['CSV Export', 'XLSX Export (Images)']);
    });

    it('prepares the export with grid columns and inheritance enabled', () => {
        const tab = grid();

        tab.exportPrepareCustom({ enableInheritance: true }, pimcore.object.gridexport.csv);

        const request = env.ajax[0];
        expect(request).toMatchObject({ method: 'POST', url: '/admin/object-helper/get-export-jobs' });
        expect(JSON.parse(request.params['fields[]'])).toEqual([
            { key: 'sku', label: 'SKU' },
            { key: 'image', label: 'image' },
        ]);
        expect(JSON.parse(request.params.settings)).toEqual({ enableInheritance: true });
        expect(JSON.parse(request.params.context)).toEqual({ scope: 'objectEditor' });
    });

    it('starts processing small exports without asking', () => {
        const tab = grid();
        tab.exportPrepareCustom({}, pimcore.object.gridexport.csv);

        env.respond(env.ajax[0], { success: true, jobs: [[1, 2], [3]], fileHandle: 'abc' });

        expect(tab.exportProcessCustom).toHaveBeenCalledWith([[1, 2], [3]], 'abc', expect.any(String), true, expect.any(String), pimcore.object.gridexport.csv);
        expect(env.messages).toHaveLength(0);
    });

    it('asks before exporting more than 25 objects', () => {
        const tab = grid();
        tab.exportPrepareCustom({}, pimcore.object.gridexport.csv);

        env.respond(env.ajax[0], { success: true, jobs: [Array(20).fill(1), Array(10).fill(1)], fileHandle: 'abc' });

        expect(tab.exportProcessCustom).not.toHaveBeenCalled();
        const confirm = env.messages.find((m) => m.type === 'confirm');
        confirm.fn('yes');
        expect(tab.exportProcessCustom).toHaveBeenCalledTimes(1);
    });

    it('downloads the result from the custom XLSX endpoint', () => {
        expect(grid().getDownloadUrlCustom('abc')).toBe('/bnix/download-xlsx-file?fileHandle=abc');
    });
});
