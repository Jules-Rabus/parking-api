import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@': '/src',
      '@components': '/src/components',
    },
  },
  test: {
    environment: 'jsdom', // Simulates a browser environment
    globals: true,
    setupFiles: './tests/unit/setup.ts',
  },
});
