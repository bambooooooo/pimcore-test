import { type AbstractModule, container } from '@pimcore/studio-ui-bundle'
import { serviceIds } from '@pimcore/studio-ui-bundle/app'
import { componentConfig, type ComponentRegistry } from '@pimcore/studio-ui-bundle/modules/app'
import { AssetRotateButton } from '../components/asset-rotate-button'
import { ObjectToolbar } from '../components/object-toolbar'

export const ToolbarModule: AbstractModule = {
  onInit: (): void => {
    const registry = container.get<ComponentRegistry>(serviceIds['App/ComponentRegistry/ComponentRegistry'])

    registry.registerToSlot(componentConfig.dataObject.editor.toolbar.slots.left.name, {
      name: 'megstylObjectToolbar',
      component: ObjectToolbar,
      priority: 500
    })

    registry.registerToSlot(componentConfig.asset.editor.toolbar.slots.left.name, {
      name: 'megstylAssetRotate',
      component: AssetRotateButton,
      priority: 500
    })
  }
}
