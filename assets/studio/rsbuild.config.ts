import { defineConfig } from '@rsbuild/core'
import { pluginReact } from '@rsbuild/plugin-react'
import { pluginModuleFederation } from '@module-federation/rsbuild-plugin'
import { pluginGenerateEntrypoints, pluginWriteBuildId } from '@pimcore/studio-ui-bundle/rsbuild/plugins'
import { createDynamicRemote } from '@pimcore/studio-ui-bundle/rsbuild/utils'
import { getBuildGroupId } from '@pimcore/studio-ui-bundle/bundler/build-id'
import path from 'path'
import fs from 'fs'
import packages from './package.json'

// Megstyl plugin for Pimcore Studio, see docs/techdocs/studio.md.
//
// The build goes to public/studio-build/<id> (gitignored). `npm run release` packs it into
// dist/build-<id>.zip, which is committed; App\Studio\WebpackEntryPointProvider extracts it on the server,
// so production needs no Node.js. The id is a hash of this directory, unchanged sources give the same zip.
const buildId = getBuildGroupId(__dirname)
const buildRoot = path.resolve(__dirname, '..', '..', 'public', 'studio-build')
const buildPath = path.resolve(buildRoot, buildId)
const assetPrefix = '/studio-build/' + buildId

if (fs.existsSync(buildRoot)) {
  fs.readdirSync(buildRoot, { withFileTypes: true }).forEach((entry) => {
    if (entry.isDirectory() && entry.name !== buildId) {
      fs.rmSync(path.resolve(buildRoot, entry.name), { recursive: true, force: true })
    }
  })
}

fs.mkdirSync(buildPath, { recursive: true })

export default defineConfig({
  mode: process.env.NODE_ENV === 'development' ? 'development' : 'production',
  dev: { assetPrefix },
  source: {
    entry: { main: './js/src/main.ts' }
  },
  output: {
    manifest: true,
    assetPrefix,
    distPath: { root: buildPath }
  },
  tools: {
    bundlerChain: (chain) => {
      chain.output.uniqueName('megstyl_studio')
    }
  },
  plugins: [
    pluginWriteBuildId({ buildId }),
    pluginGenerateEntrypoints(),
    pluginReact(),
    pluginModuleFederation({
      name: 'megstyl_studio',
      filename: 'static/js/remoteEntry.js',
      exposes: {
        '.': './js/src/plugins.ts'
      },
      dts: false,
      remotes: {
        '@pimcore/studio-ui-bundle': createDynamicRemote('pimcore_studio_ui_bundle')
      },
      shared: {
        ...packages.dependencies,
        react: { singleton: true, eager: true, requiredVersion: false },
        'react-dom': { singleton: true, eager: true, requiredVersion: false }
      }
    })
  ]
})
