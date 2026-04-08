/// <reference types="vitest" />
import { defineConfig, loadEnv } from 'vite';
import tailwindcss from '@tailwindcss/postcss';
import eslintPlugin from '@nabla/vite-plugin-eslint';
import VitePluginSvgSpritemap from '@spiriit/vite-plugin-svg-spritemap';
import VitePluginBrowserSync from 'vite-plugin-browser-sync';
import path from 'node:path';
import vue from '@vitejs/plugin-vue';
import { createProxyHandler } from './src/js/helpers/middleware';
import devAssetRewriter from './src/js/helpers/assetRewriter';
import hotFile from './src/js/helpers/hotFile';
import cloudflareTunnel from './src/js/helpers/cloudflareTunnel';
import { resolveTunnelConfig } from './src/js/helpers/tunnelConfig';
import { bold, lightMagenta } from 'kolorist';
import graphqlLoader from 'vite-plugin-graphql-loader';

export default ({ mode }: { mode: string }) => {
  const env = loadEnv(mode, process.cwd(), '');
  const devProfile = env.VITE_DEV_PROFILE || 'local';

  // Derive feature flags from the active profile — single source of truth.
  const tunnel = devProfile === 'tunnel' ? resolveTunnelConfig(env) : undefined;
  const enableBrowserSync = devProfile === 'lan';

  const sharedBsOptions = {
    online: true,
    open: false,
    notify: false,
    ui: false,
    ghostMode: false,
    proxy: env.VITE_ASSET_URL,
  } as const;

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
      target: 'es2022',
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
    css: {
      postcss: {
        plugins: [tailwindcss()],
      },
    },
    optimizeDeps: {
      include: ['vue', 'pinia', 'tua-body-scroll-lock'],
    },
    plugins: [
      vue(),
      eslintPlugin(),
      VitePluginBrowserSync({
        dev: {
          enable: enableBrowserSync,
          bs: {
            online: true,
            notify: false,
            port: 3002,
            files: ['templates/**/*.php', 'inc/**/*.php', '*.php'],
            proxy: {
              target: env.VITE_ASSET_URL,
              ws: true,
            },
          },
        },
        preview: {
          enable: enableBrowserSync,
          bs: sharedBsOptions,
        },
        buildWatch: {
          enable: enableBrowserSync,
          bs: { ...sharedBsOptions, injectChanges: false },
        },
      }),
      VitePluginSvgSpritemap(path.resolve(__dirname, 'src/sprite/*.svg'), {
        prefix: 'icon-',
        output: {
          filename: '../sprite.svg',
          name: 'sprite.svg',
        },
      }),
      devAssetRewriter({ publicUrl: tunnel?.publicUrl }),
      graphqlLoader(),
      cloudflareTunnel({
        enabled: !!tunnel,
        tunnelId: env.VITE_TUNNEL_ID,
        tunnelName: env.VITE_TUNNEL_NAME,
        tunnelHostname: tunnel?.hostname,
        tunnelPublicUrl: tunnel?.publicUrl,
        siteOrigin: env.VITE_ASSET_URL,
        vitePort: Number(env.VITE_TUNNEL_PORT || 5173),
      }),
      hotFile({
        preferNetworkUrl: enableBrowserSync,
        publicUrl: tunnel?.publicUrl,
      }),
      {
        name: 'log-dev-urls',
        configureServer(server) {
          if (server.printUrls) {
            const originalPrintUrls = server.printUrls;

            server.printUrls = () => {
              console.log(
                `  ${lightMagenta('➜')}  ${bold('Profile: ')}${lightMagenta(devProfile)}`,
              );
              if (tunnel?.publicUrl) {
                console.log(
                  `  ${lightMagenta('➜')}  ${bold('Tunnel:  ')}${lightMagenta(tunnel.publicUrl)}`,
                );
              }
              console.log(
                `  ${lightMagenta('➜')}  ${bold('Proxy:   ')}${lightMagenta(env.VITE_ASSET_URL + '/' || 'N/A')}`,
              );
              originalPrintUrls();
            };
          }
        },
      },
    ],
    define: { __VUE_PROD_DEVTOOLS__: false },
    server: {
      host: enableBrowserSync,
      cors: true,
      strictPort: true,
      // port: 3000,
      proxy: {
        '/graphql/': {
          target: env.VITE_ASSET_URL,
          changeOrigin: true,
          selfHandleResponse: true,
          configure: (proxy) => {
            proxy.on('proxyRes', createProxyHandler(env.VITE_ASSET_URL));
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
      hmr: tunnel ? undefined : { host: 'localhost' },
    },
    resolve: {
      alias: {
        '@': path.resolve(__dirname, './src'),
        vue: 'vue/dist/vue.esm-bundler.js',
      },
    },
  });
};
