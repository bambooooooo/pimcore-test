import { type IAbstractPlugin } from '@pimcore/studio-ui-bundle'
import { ContextMenuModule } from './modules/context-menu-module'
import { MainNavModule } from './modules/main-nav-module'
import { ToolbarModule } from './modules/toolbar-module'

export const MegstylPlugin: IAbstractPlugin = {
  name: 'MegstylPlugin',

  onStartup ({ moduleSystem }) {
    moduleSystem.registerModule(ToolbarModule)
    moduleSystem.registerModule(MainNavModule)
    moduleSystem.registerModule(ContextMenuModule)
  }
}
