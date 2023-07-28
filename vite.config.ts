/// <reference types="vitest" />
import { defineConfig, loadEnv } from 'vite';
import vue from '@vitejs/plugin-vue';
import eslintPlugin from 'vite-plugin-eslint';
import { createSvgIconsPlugin } from 'vite-plugin-svg-icons';
import VitePluginBrowserSync from 'vite-plugin-browser-sync';
import path from 'path';

export default ({ mode }: { mode: string }) => {
  process.env = { ...process.env, ...loadEnv(mode, process.cwd()) };

  return defineConfig({
    publicDir: path.resolve(__dirname, './src/public'),
    root: path.resolve(__dirname, './src'),
    base: process.env.NODE_ENV === 'production' ? `${process.env.VITE_THEME_PATH}/dist/` : '/',
    build: {
      outDir: path.resolve(__dirname, 'dist'),
      emptyOutDir: true,
      manifest: true,
      // minify: false,
      sourcemap: process.env.NODE_ENV === 'production' ? false : 'inline',
      target: 'es2018',
      rollupOptions: {
        input: {
          app: '/js/app.ts',
          admin: '/js/admin.ts',
        },
        external: ['jquery'],
        output: {
          entryFileNames: `[name].js`,
          chunkFileNames: `[name].js`,
          assetFileNames: `[name].[ext]`,
          manualChunks(id) {
            if (id.includes('node_modules')) {
              return id.toString().split('node_modules/')[1].split('/')[0].toString();
            }
          },
          globals: {
            jquery: 'jQuery',
          },
        },
      },
    },
    plugins: [
      vue({
        reactivityTransform: true,
      }),
      eslintPlugin(),
      VitePluginBrowserSync({
        bs: {
          online: true,
          notify: false,
          proxy: {
            target: process.env.VITE_ASSET_URL,
            ws: true,
            proxyReq: [
              (proxyReq) => {
                proxyReq.setHeader('Browser-Sync', true);
              },
            ],
          },
        },
      }),
      createSvgIconsPlugin({
        iconDirs: [path.resolve(process.cwd(), 'src/sprite')],
        symbolId: 'icon-[name]',
        customDomId: 'tofino-sprite',
      }),
    ],
    test: {
      include: [`${__dirname}/src/js/tests/*.ts`],
      globals: true,
      environment: 'jsdom',
      coverage: {
        provider: 'istanbul',
      },
    },
    server: {
      host: true,
      cors: true,
      strictPort: true,
      port: 3000,
      hmr: {
        port: 3000,
        protocol: 'ws',
      },
    },
    resolve: {
      alias: {
        '@': path.resolve(__dirname, './src'),
        vue: 'vue/dist/vue.esm-bundler.js',
      },
    },
  });
};
