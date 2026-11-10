import { defineConfig } from 'vitest/config';

// Unit checks run without loading the development server or tunnel plugins.
export default defineConfig({
  test: {
    include: ['tests/unit/**/*.test.ts'],
    environment: 'node',
    clearMocks: true,
  },
});
