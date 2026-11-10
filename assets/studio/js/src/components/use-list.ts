import { useEffect, useState } from 'react'
import { getList } from '../lib/api'

export interface ListItem { id?: number | string, name: string, title?: string }

/** Loads a lookup list (e.g. /labelsize) once `enabled` turns true. */
export function useList (url: string, enabled: boolean): ListItem[] {
  const [items, setItems] = useState<ListItem[]>([])

  useEffect(() => {
    if (!enabled) return

    let active = true
    getList<ListItem>(url).then(list => { if (active) setItems(list) }).catch(() => {})

    return () => { active = false }
  }, [url, enabled])

  return items
}
