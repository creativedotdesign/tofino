/// <reference types="vitest" />
import { defineConfig, loadEnv } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import eslintPlugin from '@nabla/vite-plugin-eslint';
import { svgSpritemap } from 'vite-plugin-svg-spritemap';
import VitePluginBrowserSync from 'vite-plugin-browser-sync';
import path from 'path';
import vue from '@vitejs/plugin-vue';
import { onProxyRes } from './src/js/helpers/middleware';
import devAssetRewriter from './src/js/helpers/assetRewriter';
import { bold, lightMagenta } from 'kolorist';
import graphqlLoader from 'vite-plugin-graphql-loader';

export default ({ mode }: { mode: string }) => {
  const env = loadEnv(mode, process.cwd(), '');

  return defineConfig({
    publicDir: path.resolve(__dirname, './src/public'),
    root: path.resolve(__dirname, './src'),
    base: env.NODE_ENV === 'production' ? `${env.VITE_THEME_PATH}/dist/` : '/',
    build: {
      outDir: path.resolve(__dirname, 'dist'),
      emptyOutDir: true,
      manifest: true,
      minify: true,
      sourcemap: env.NODE_ENV === 'production' ? false : 'inline',
      target: 'es2021',
      rollupOptions: {
        input: {
          app: '/js/app.ts',
          admin: '/js/admin.ts',
        },
        external: ['jquery'],
        output: {
          globals: {
            jquery: 'jQuery',
          },
          manualChunks: (id) => {
            // Split vendor chunks by package name
            if (id.includes('node_modules')) {
              return id.split('node_modules/')[1].split('/')[0];
            }
          },
        },
      },
    },
    optimizeDeps: {
      include: ['vue', 'pinia', 'webfontloader', 'tua-body-scroll-lock'],
    },
    plugins: [
      tailwindcss(),
      vue(),
      eslintPlugin(),
      VitePluginBrowserSync({
        dev: {
          bs: {
            online: true,
            notify: false,
            port: 3002,
            proxy: {
              target: env.VITE_ASSET_URL,
              ws: true,
              proxyReq: [
                (proxyReq) => {
                  proxyReq.setHeader('Browser-Sync', true);
                },
              ],
            },
          },
        },
      }),
      svgSpritemap({
        pattern: 'src/sprite/*.svg',
        filename: 'sprite.svg',
        prefix: 'icon',
      }),
      devAssetRewriter(),
      graphqlLoader(),
      {
        // Log the proxy server address in the console
        name: 'log-proxy-address',
        configureServer(server) {
          if (server.printUrls) {
            const originalPrintUrls = server.printUrls;

            server.printUrls = () => {
              console.log(
                `  ${lightMagenta('➜')}  ${bold('Proxy:   ')}${lightMagenta(env.VITE_ASSET_URL + '/' || 'N/A')}`
              );
              originalPrintUrls();
            };
          }
        },
      },
    ],
    define: { __VUE_PROD_DEVTOOLS__: false },
    server: {
      host: true,
      cors: true,
      strictPort: true,
      port: 3000,
      proxy: {
        '/graphql/': {
          target: env.VITE_ASSET_URL,
          changeOrigin: true,
          selfHandleResponse: true, // Indicates that the response should be handled by the proxy
          configure: (proxy) => {
            proxy.on('proxyRes', onProxyRes);
          },
        },
        '/wp-content/uploads/': {
          target: env.VITE_ASSET_URL,
          changeOrigin: true,
        },
        '/wp-admin': {
          target: env.VITE_ASSET_URL,
          changeOrigin: true,
        },
      },
      hmr: {
        port: 3000,
        host: 'localhost',
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
