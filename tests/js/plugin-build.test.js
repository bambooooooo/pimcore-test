import fs from 'node:fs';
import path from 'node:path';
import { describe, expect, it } from 'vitest';

const STUDIO = path.resolve(import.meta.dirname, '../../assets/studio');
const BUILD_ID = path.join(STUDIO, 'node_modules/@pimcore/studio-ui-bundle/bundler/build-id.ts');

/**
 * The plugin ships as the committed archive assets/studio/dist/build-<id>.zip, where <id> is a hash of the
 * sources. A source change without `npm run release` would leave production on the old build.
 */
describe('Studio plugin build archive', () => {
    const archives = fs.readdirSync(path.join(STUDIO, 'dist')).filter((f) => /^build-.*\.zip$/.test(f));

    it('is committed exactly once', () => {
        expect(archives).toHaveLength(1);
    });

    it.skipIf(!fs.existsSync(BUILD_ID))('matches the current sources (run `npm run release` in assets/studio)', async () => {
        const { getBuildGroupId } = await import(BUILD_ID);

        expect(archives[0]).toBe(`build-${getBuildGroupId(STUDIO)}.zip`);
    });
});
