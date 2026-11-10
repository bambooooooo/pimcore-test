<?php

declare(strict_types=1);

namespace App\Studio;

use Pimcore\Bundle\StudioUiBundle\Build\BuildArchive;
use Pimcore\Bundle\StudioUiBundle\Build\BuildArchiveExtractionTrait;
use Pimcore\Bundle\StudioUiBundle\Build\BuildArchiveProviderInterface;

/**
 * Registers the Megstyl Pimcore Studio plugin (assets/studio).
 *
 * The committed archive assets/studio/dist/build-<id>.zip is extracted to public/studio-build on the server
 * (also by cache:warmup), so deployments need no Node.js. Rebuild with `npm run release` in assets/studio.
 */
final class WebpackEntryPointProvider implements BuildArchiveProviderInterface
{
    use BuildArchiveExtractionTrait;

    public function getEntryPoints(): array
    {
        return ['exposeRemote'];
    }

    public function getOptionalEntryPoints(): array
    {
        return [];
    }

    protected function buildArchive(): BuildArchive
    {
        return new BuildArchive(
            archiveGlob: dirname(__DIR__, 2) . '/assets/studio/dist/build*.zip',
            targetDir: dirname(__DIR__, 2) . '/public/studio-build',
        );
    }
}
