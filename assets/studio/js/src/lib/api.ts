/**
 * Calls to the Megstyl endpoints. They live outside /pimcore-studio/api, the Studio session cookie
 * authenticates them (#[ProtectedEndpoint]).
 */
export class HttpError extends Error {
  constructor (public readonly status: number, message: string) {
    super(message)
  }
}

async function request (url: string, init: RequestInit = {}): Promise<Response> {
  const response = await fetch(url, { credentials: 'same-origin', ...init })

  if (!response.ok) {
    throw new HttpError(response.status, (await response.text()) || response.statusText)
  }

  return response
}

export async function getJson<T> (url: string): Promise<T> {
  return await (await request(url, { headers: { Accept: 'application/json' } })).json() as T
}

export async function getText (url: string): Promise<string> {
  return await (await request(url)).text()
}

/** Form-encoded POST, like Ext.Ajax params in the classic admin (controllers read $request->get()). */
export async function postForm (url: string, params: Record<string, string | number>): Promise<Response> {
  const body = new URLSearchParams()
  Object.entries(params).forEach(([k, v]) => { body.set(k, String(v)) })

  return await request(url, { method: 'POST', body })
}

export async function postJson<T> (url: string, payload: unknown): Promise<T> {
  return await (await request(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify(payload)
  })).json() as T
}

export async function send (url: string, method: 'GET' | 'PATCH' = 'GET'): Promise<Response> {
  return await request(url, { method })
}

/** Item lists of the lookup endpoints (/labelsize, /price-levels, ...): {data: [...]} */
export async function getList<T> (url: string): Promise<T[]> {
  return (await getJson<{ data?: T[] }>(url)).data ?? []
}

export function openInNewTab (url: string): void {
  window.open(url, '_blank')
}
