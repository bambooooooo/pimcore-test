import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        include: ['tests/js/**/*.test.js'],
        environment: 'jsdom',
        environmentOptions: {
            jsdom: { url: 'https://pim.example.com/pimcore-studio/' },
        },
        restoreMocks: true,
    },
});
