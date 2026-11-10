/**
 * Bash-like brace expansion for object keys: "SZAFA-{80,100}" -> ["SZAFA-80", "SZAFA-100"].
 *
 * Groups multiply ("{A,B}-{1,2}"), nest ("K{1,2{a,b}}") and may hold an empty option ("P{,-L}").
 * A group without a comma ("X{1}") and escaped braces ("A\{1,2\}") stay literal.
 */
export function expandBraces (pattern: string): string[] {
  return expand(tokenize(pattern))
}

type Token = string

function tokenize (pattern: string): Token[] {
  const tokens: Token[] = []
  let literal = ''

  for (let i = 0; i < pattern.length; i++) {
    const c = pattern[i]

    if (c === '\\' && i + 1 < pattern.length) {
      literal += c + pattern[++i]
    } else if (c === '{' || c === ',' || c === '}') {
      if (literal !== '') tokens.push(literal)
      literal = ''
      tokens.push(c)
    } else {
      literal += c
    }
  }

  if (literal !== '') tokens.push(literal)

  return tokens
}

/** Index of the "}" closing the "{" at `open`, plus the top-level commas in between; null when unbalanced. */
function findGroup (tokens: Token[], open: number): { close: number, commas: number[] } | null {
  let depth = 0
  const commas: number[] = []

  for (let i = open; i < tokens.length; i++) {
    if (tokens[i] === '{') depth++
    else if (tokens[i] === '}') depth--
    else if (tokens[i] === ',' && depth === 1) commas.push(i)

    if (depth === 0) return { close: i, commas }
  }

  return null
}

function expand (tokens: Token[]): string[] {
  let results = ['']

  for (let i = 0; i < tokens.length; i++) {
    const token = tokens[i]
    const group = token === '{' ? findGroup(tokens, i) : null

    if (group !== null && group.commas.length > 0) {
      const bounds = [i, ...group.commas, group.close]
      const options: string[] = []

      for (let b = 0; b < bounds.length - 1; b++) {
        options.push(...expand(tokens.slice(bounds[b] + 1, bounds[b + 1])))
      }

      results = results.flatMap(prefix => options.map(option => prefix + option))
      i = group.close
    } else {
      results = results.map(prefix => prefix + token)
    }
  }

  return results
}
