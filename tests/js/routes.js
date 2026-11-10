/**
 * Symfony routes for URL checks. routes.json is exported by bin/run-tests (debug:router --format=json),
 * so the plugin's URLs are checked against the real application routes.
 */
import fs from 'node:fs';
import path from 'node:path';

const ROUTES_FILE = path.join(import.meta.dirname, 'routes.json');

let routes = null;

export function loadRoutes() {
    if (!routes) {
        if (!fs.existsSync(ROUTES_FILE)) {
            throw new Error('tests/js/routes.json is missing - run bin/run-tests (it exports the Symfony routes first)');
        }
        routes = Object.entries(JSON.parse(fs.readFileSync(ROUTES_FILE, 'utf8'))).map(([name, route]) => ({
            name,
            ...route,
            methods: route.method === 'ANY' ? null : route.method.split('|'),
            regex: routeRegex(route),
        }));
    }

    return routes;
}

function routeRegex(route) {
    const requirements = typeof route.requirements === 'object' ? route.requirements : {};
    const pattern = route.path
        .split(/(\{[^}]+\})/)
        .map((part) => {
            const placeholder = part.match(/^\{([^}]+)\}$/);
            return placeholder
                ? `(${requirements[placeholder[1]] ?? '[^/]+'})`
                : part.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        })
        .join('');

    return new RegExp(`^${pattern}$`);
}

/**
 * Finds the Symfony route serving a URL (query string ignored).
 */
export function matchRoute(url, method = 'GET') {
    const pathname = new URL(url, 'https://pim.example.com').pathname;

    return loadRoutes().find((route) => route.regex.test(pathname) && (!route.methods || route.methods.includes(method))) ?? null;
}
