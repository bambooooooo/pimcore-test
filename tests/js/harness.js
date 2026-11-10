/**
 * Test harness for the custom Pimcore admin scripts in public/static/js.
 *
 * Each script runs in jsdom against a recording stand-in of ExtJS and the Pimcore admin globals:
 * every created component, Ajax call, store URL and window.open() is captured, so tests can click
 * buttons through their real handlers and check which backend URLs the admin UI calls.
 *
 * routes.json is exported from Symfony by bin/run-tests - URLs are checked against the real routes.
 */
import fs from 'node:fs';
import path from 'node:path';
import { expect, vi } from 'vitest';

const ROOT = path.resolve(import.meta.dirname, '../..');
export const SCRIPT_DIR = path.join(ROOT, 'public/static/js');
const ROUTES_FILE = path.join(import.meta.dirname, 'routes.json');

export const EVENTS = {
    postOpenObject: 'pimcore.postOpenObject',
    postOpenAsset: 'pimcore.postOpenAsset',
    pimcoreReady: 'pimcore.ready',
};

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
 * Finds the Symfony route serving a URL the admin JS calls (query string ignored).
 */
export function matchRoute(url, method = 'GET') {
    const pathname = new URL(url, window.location.origin).pathname;

    return loadRoutes().find((route) => route.regex.test(pathname) && (!route.methods || route.methods.includes(method))) ?? null;
}

function component(env, cls, config = {}) {
    const c = {
        $class: cls,
        config,
        items: [...(config.items ?? [])],
        text: config.text,
        value: config.value ?? (config.checked !== undefined ? config.checked : null),
        shown: false,
        closed: false,
        add(...items) {
            c.items.push(...items.flat());
            return items[0];
        },
        insert(index, item) {
            c.items.push(item ?? index);
        },
        show() {
            c.shown = true;
            return c;
        },
        hide() {
            c.shown = false;
        },
        close() {
            c.closed = true;
        },
        setText(text) {
            c.text = text;
        },
        getValue() {
            return c.value;
        },
        setValue(value) {
            c.value = value;
        },
        updateProgress(progress, text) {
            c.progress = progress;
            c.progressText = text;
        },
        load: vi.fn(),
        on: vi.fn(),
    };

    if (config.proxy?.url) {
        env.storeUrls.push(config.proxy.url);
    }
    env.components.push(c);

    return c;
}

/**
 * Installs the Pimcore admin globals and returns the recorder.
 */
export function createAdminEnv({ language = 'pl', websiteLanguages = ['pl', 'en', 'de'], environment = 'test' } = {}) {
    const env = {
        listeners: [],
        components: [],
        ajax: [],
        storeUrls: [],
        opened: [],
        messages: [],
        globals: {},
        cmps: {},
    };

    const constructor = (cls) => function (config) {
        return component(env, cls, config);
    };

    const messageBox = {
        OK: 'ok',
        OKCANCEL: 'okcancel',
        QUESTION: 'question',
        alert: (title, msg) => env.messages.push({ type: 'alert', title, msg }),
        confirm: (title, msg, fn) => env.messages.push({ type: 'confirm', title, msg, fn }),
        show: (config) => env.messages.push({ type: 'show', ...config }),
        prompt: (title, msg, fn) => env.messages.push({ type: 'prompt', title, msg, fn }),
    };

    globalThis.Ext = {
        create: (cls, config) => component(env, cls, config),
        Window: constructor('Ext.Window'),
        ProgressBar: constructor('Ext.ProgressBar'),
        Toolbar: { TextItem: constructor('Ext.Toolbar.TextItem') },
        util: { KeyMap: constructor('Ext.util.KeyMap') },
        Ajax: { request: (options) => env.ajax.push(options) },
        Msg: messageBox,
        MessageBox: messageBox,
        encode: JSON.stringify,
        decode: JSON.parse,
        override: (cls, members) => Object.assign(cls.prototype, members),
        getCmp: (id) => env.cmps[id],
    };

    globalThis.pimcore = {
        events: EVENTS,
        settings: { language, websiteLanguages, environment },
        helpers: {
            showNotification: vi.fn(),
            getValidFilename: vi.fn((name) => name),
            download: vi.fn(),
            getProgressWindowListeners: vi.fn(() => ({})),
        },
        globalmanager: { get: (id) => env.globals[id] },
        treenodelocator: { showInTree: vi.fn() },
        elementservice: { refreshNodeAllTrees: vi.fn() },
        object: {
            helpers: { gridTabAbstract: function GridTabAbstract() {} },
            gridexport: { csv: { type: 'csv' } },
        },
    };

    globalThis.Routing = {
        generate(name, params = {}) {
            const route = loadRoutes().find((r) => r.name === name);
            if (!route) {
                throw new Error(`Routing.generate: route "${name}" does not exist`);
            }
            if (route.options?.expose !== true) {
                throw new Error(`Routing.generate: route "${name}" is not exposed to JS (options: {expose: true})`);
            }

            const query = { ...params };
            const url = route.path.replace(/\{([^}]+)\}/g, (_, key) => {
                const value = query[key];
                delete query[key];
                return encodeURIComponent(value);
            });
            const qs = new URLSearchParams(query).toString();

            return qs ? `${url}?${qs}` : url;
        },
    };

    globalThis.t = (text) => text;
    globalThis.intval = (value) => parseInt(value, 10) || 0;
    globalThis.sprintf = (format, ...args) => args.reduce((s, arg) => s.replace('%s', arg), format);
    globalThis.alert = vi.fn();
    window.open = vi.fn((url) => env.opened.push(url));

    /** Evaluates a script from public/static/js and captures its Pimcore event listeners. */
    env.load = (...names) => {
        for (const name of names) {
            const code = fs.readFileSync(path.join(SCRIPT_DIR, `${name}.js`), 'utf8');
            const original = document.addEventListener;
            document.addEventListener = (type, fn, ...rest) =>
                type.startsWith('pimcore.') ? env.listeners.push({ type, fn }) : original.call(document, type, fn, ...rest);
            try {
                (0, eval)(code);
            } finally {
                document.addEventListener = original;
            }
        }

        return env;
    };

    env.fire = (type, detail = {}) => {
        env.listeners.filter((l) => l.type === type).forEach((l) => l.fn({ detail }));
    };

    /** Opens an object tab like Pimcore does and returns the tab's object (toolbar records added buttons). */
    env.openObject = (className, data = {}, { id = 123, key = 'object-key', type = 'object', classId = 'CL' } = {}) => {
        const object = {
            id,
            data: { general: { id, key, className, classId }, data, type },
            toolbar: component(env, 'toolbar'),
            reload: vi.fn(),
        };
        env.fire(EVENTS.postOpenObject, { object });

        return object;
    };

    env.openAsset = (type, mimetype, { id = 55 } = {}) => {
        const asset = { id, type, data: { mimetype }, toolbar: component(env, 'toolbar'), reload: vi.fn() };
        env.fire(EVENTS.postOpenAsset, { asset });

        return asset;
    };

    /** Every backend URL the scripts touched: [method, url]. */
    env.calledUrls = () => [
        ...env.ajax.map((a) => [a.method ?? (a.params ? 'POST' : 'GET'), a.url]),
        ...env.storeUrls.map((url) => ['GET', url]),
        ...env.opened.filter((url) => url.startsWith('/')).map((url) => ['GET', url]),
    ];

    /** Fails if the admin JS calls a URL that no Symfony route serves (moved/renamed endpoint). */
    env.expectAllUrlsRoutable = () => {
        const broken = env.calledUrls().filter(([method, url]) => !matchRoute(url, method));
        expect(broken, 'admin JS calls URLs without a matching route').toEqual([]);
    };

    env.respond = (request, body, { ok = true, status = 200 } = {}) => {
        const response = { status, responseText: typeof body === 'string' ? body : JSON.stringify(body) };
        (ok ? request.success : request.failure)?.call(request.scope, response);
    };

    return env;
}

/** Finds a toolbar entry (or menu item) by tooltip or text. */
export function findButton(container, label) {
    const items = container.items ?? container.menu ?? container;
    const found = items.find((item) => item.tooltip === label || item.text === label || item.config?.text === label);
    if (!found) {
        throw new Error(`Button "${label}" not found in [${items.map((i) => i.tooltip ?? i.text ?? i.$class).join(', ')}]`);
    }

    return found;
}

export function click(button) {
    (button.handler ?? button.config?.handler).call(button.scope ?? button);
}
