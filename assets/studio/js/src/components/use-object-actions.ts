import { useAlertModal, useFormModal, useMessage } from '@pimcore/studio-ui-bundle/components'
import { useElementRefresh } from '@pimcore/studio-ui-bundle/modules/element'
import { HttpError, openInNewTab, postForm, send } from '../lib/api'
import { optimikQuestions } from '../lib/optimik'
import { type ToolbarContext, type TranslatableField } from '../lib/types'
import { urls } from '../lib/urls'

export const EAN_WARNING_THRESHOLD = 200
const TRANSLATE_DELAY_MS = 500

const sleep = async (ms: number): Promise<void> => { await new Promise(resolve => setTimeout(resolve, ms)) }

/**
 * Actions of the object editor toolbar, ported from the classic admin startup.js.
 */
export function useObjectActions (ctx: ToolbarContext) {
  const alertModal = useAlertModal()
  const formModal = useFormModal()
  const message = useMessage()
  const { refreshElement } = useElementRefresh('data-object')

  const reload = (): void => { refreshElement(ctx.id) }

  const fail = (e: unknown): void => {
    alertModal.error({ title: 'Error', content: e instanceof Error ? e.message : String(e) })
  }

  const confirm = async (title: string, content: string): Promise<boolean> =>
    await new Promise(resolve => {
      formModal.confirm({ title, content, onOk: () => { resolve(true) }, onCancel: () => { resolve(false) } })
    })

  return {
    open: openInNewTab,

    async assignEan (): Promise<void> {
      if (ctx.hasEan === true) {
        alertModal.warn({ title: 'Warning', content: 'EAN already exists!' })
        return
      }

      try {
        const result = await (await postForm(urls.addEan(), { id: ctx.id })).json() as { remaining?: number }

        if (typeof result.remaining === 'number' && result.remaining < EAN_WARNING_THRESHOLD) {
          void message.warning(`Less than ${EAN_WARNING_THRESHOLD} GTINs left: ${result.remaining}`)
        }

        reload()
      } catch (e) { fail(e) }
    },

    async computeCatalogPrices (common: boolean): Promise<void> {
      if (common && ctx.published && !(await confirm('Confirm', 'Are you sure you want to update CURRENT prices?'))) {
        return
      }

      try {
        await postForm(urls.computeCatalogPrice(), common ? { id: ctx.id, type: 'common' } : { id: ctx.id })
        reload()
      } catch (e) { fail(e) }
    },

    async translate (field: TranslatableField): Promise<void> {
      const source = (field === 'name' ? ctx.name : ctx.description) ?? ''
      const languages = ctx.websiteLanguages

      if (!(await confirm('Translate', `Translate [${source}] into ${languages.length} languages?`))) {
        return
      }

      let cancelled = false
      const progress = alertModal.info({
        title: 'Translating',
        content: '0%',
        okText: 'Cancel',
        onOk: () => { cancelled = true }
      })

      try {
        for (let i = 0; i < languages.length && !cancelled; i++) {
          progress.update({ content: `Translating into ${languages[i]}... (${Math.ceil(i / languages.length * 100)}%)` })
          await postForm(urls.translateName(), { id: ctx.id, origin: ctx.language, loc: languages[i], field, name: source })

          if (i < languages.length - 1) await sleep(TRANSLATE_DELAY_MS)
        }
      } catch (e) {
        fail(e instanceof HttpError && e.status === 429 ? new Error(`DeepL: ${e.message}`) : e)
      } finally {
        progress.destroy()
        reload()
      }
    },

    async patch (url: string): Promise<void> {
      try {
        await send(url, 'PATCH')
        reload()
      } catch (e) { fail(e) }
    },

    async enqueueFeed (setStatus: (text: string) => void): Promise<void> {
      setStatus('Enqueuing...')

      try {
        await send(urls.offerFeed(ctx.id))
        setStatus('Enqueued')
      } catch (e) { fail(e) }
    },

    openUserPriceList (kind: 'preview' | 'xlsx'): void {
      const url = urls.userPriceList(ctx.offers ?? [], ctx.key, kind)

      if (url === null) {
        alertModal.warn({ title: 'No offer assigned to the user', content: 'Assign an offer to the user first.' })
        return
      }

      openInNewTab(url)
    },

    openGroupImages (): void {
      if ((ctx.productCount ?? 0) === 0) {
        alertModal.warn({ title: 'Warning', content: 'Group has no Products and Sets!' })
        return
      }

      openInNewTab(urls.productImages(ctx.id))
    },

    async exportOptimik (): Promise<void> {
      for (const question of optimikQuestions(ctx.optimikWarnings)) {
        if (!(await confirm('Warning', question))) {
          void message.info('Operation cancelled by user')
          return
        }
      }

      openInNewTab(urls.optimikExport(ctx.id))
    },

    async status (): Promise<string> {
      return await (await send(urls.objectStatus(ctx.id))).text()
    }
  }
}

export type ObjectActions = ReturnType<typeof useObjectActions>
