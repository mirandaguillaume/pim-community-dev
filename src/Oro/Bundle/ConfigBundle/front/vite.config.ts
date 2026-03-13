import {defineConfig} from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
  plugins: [react()],
  root: __dirname,
  server: {
    port: 3000,
    proxy: {
      '/rest': 'http://localhost:8080',
      '/api': 'http://localhost:8080',
    },
  },
  resolve: {
    alias: {
      'akeneo-design-system': path.resolve(__dirname, '../../../../../front-packages/akeneo-design-system/src'),
      '@akeneo-pim-community/shared': path.resolve(__dirname, '../../../../../front-packages/shared/src'),
    },
  },
});
