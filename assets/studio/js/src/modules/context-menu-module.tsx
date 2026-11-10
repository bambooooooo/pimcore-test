import React from 'react'
import { type AbstractModule, container } from '@pimcore/studio-ui-bundle'
import { serviceIds } from '@pimcore/studio-ui-bundle/app'
import { Icon, useAlertModal, useFormModal, useMessage } from '@pimcore/studio-ui-bundle/components'
import { contextMenuConfig, type ContextMenuRegistryInterface, type DataObjectTreeContextMenuProps } from '@pimcore/studio-ui-bundle/modules/app'
import { useRefreshTree } from '@pimcore/studio-ui-bundle/modules/element'
import { postJson } from '../lib/api'
import { expandBraces } from '../lib/brace-expansion'
import { urls } from '../lib/urls'

export const MAX_CHILDREN = 500

interface AddChildrenResult { added: Array<{ id: number, key: string }>, failed: Array<{ key: string, error: string }> }

/**
 * "Add children..." in the data object tree: creates objects of the same class below the clicked object,
 * the key may be a brace pattern, e.g. SZAFA-{80,100}-{BIALA,CZARNA}. Classic admin: Ctrl+M (addChildren.js).
 */
export const ContextMenuModule: AbstractModule = {
  onInit (): void {
    const registry = container.get<ContextMenuRegistryInterface>(serviceIds['App/ContextMenuRegistry/ContextMenuRegistry'])
    const config = contextMenuConfig.dataObjectTree

    registry.registerToSlot(config.name, {
      name: 'megstyl-add-children',
      priority: config.priority.addObject + 1,
      useMenuItem: (context: DataObjectTreeContextMenuProps) => {
        const formModal = useFormModal()
        const alertModal = useAlertModal()
        const message = useMessage()
        const { refreshTree } = useRefreshTree('data-object')
        const target = context.target

        if (target.type !== 'object' && target.type !== 'variant') return null
        if (target.permissions?.create === false) return null

        return {
          key: 'megstyl-add-children',
          label: 'Add children (key pattern)...',
          icon: <Icon value="add-something" />,
          onClick: () => {
            formModal.input({
              title: 'Enter new object(s) key or pattern',
              label: 'e.g. SZAFA-{80,100}',
              onOk: async (pattern: string) => {
                const keys = expandBraces(String(pattern ?? '').trim()).filter(k => k !== '')

                if (keys.length === 0 || keys.length > MAX_CHILDREN) {
                  alertModal.warn({ title: 'Warning', content: `The pattern gives ${keys.length} keys (1-${MAX_CHILDREN} allowed).` })
                  return
                }

                try {
                  const result = await postJson<AddChildrenResult>(urls.addChildren(Number(target.id)), { keys })
                  void message.success(`Dodano: ${result.added.length} / ${keys.length} obiektów`)

                  if (result.failed.length > 0) {
                    alertModal.warn({ title: 'Not created', content: result.failed.map(f => `${f.key}: ${f.error}`).join('\n') })
                  }

                  refreshTree(target.id)
                } catch (e) {
                  alertModal.error({ title: 'Error', content: e instanceof Error ? e.message : String(e) })
                } finally {
                  context.onComplete?.()
                }
              }
            })
          }
        }
      }
    })
  }
}
