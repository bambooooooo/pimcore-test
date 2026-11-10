import { type AbstractModule, container } from '@pimcore/studio-ui-bundle'
import { serviceIds } from '@pimcore/studio-ui-bundle/app'
import { type MainNavRegistry } from '@pimcore/studio-ui-bundle/modules/app'
import { openInNewTab } from '../lib/api'
import { urls } from '../lib/urls'

/** "Megstyl" group in the main navigation (classic admin: File menu entries). */
export const MainNavModule: AbstractModule = {
  onInit: (): void => {
    const nav = container.get<MainNavRegistry>(serviceIds.mainNavRegistry)

    nav.registerMainNavItem({ path: 'Megstyl', label: 'Megstyl', icon: 'factory', order: 900 })

    nav.registerMainNavItem({
      path: 'Megstyl/Factory',
      label: 'Megstyl Factory',
      useCustomMainNavItem: () => ({
        name: 'Megstyl Factory',
        icon: { type: 'name', value: 'factory' },
        onClick: () => { openInNewTab(document.location.origin + urls.factory()) }
      })
    })

    nav.registerMainNavItem({
      path: 'Megstyl/Docs',
      label: 'Megstyl Docs',
      useCustomMainNavItem: () => ({
        name: 'Megstyl Docs',
        icon: { type: 'name', value: 'book-open-01' },
        onClick: () => { openInNewTab(urls.docs(document.location.origin)) }
      })
    })
  }
}
