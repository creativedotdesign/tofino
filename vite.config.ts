/// <reference types="vitest" />
import { defineConfig, loadEnv } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import eslintPlugin from '@nabla/vite-plugin-eslint';
import VitePluginSvgSpritemap from '@spiriit/vite-plugin-svg-spritemap';
import path from 'node:path';
import vue from '@vitejs/plugin-vue';
import { createProxyHandler } from './src/js/build/middleware';
import devAssetRewriter from './src/js/build/assetRewriter';
import hotFile from './src/js/build/hotFile';
import cloudflareTunnel from './src/js/build/cloudflareTunnel';
import phpFullReload from './src/js/build/phpFullReload';
import { resolveTunnelConfig } from './src/js/build/tunnelConfig';
import { bold, lightMagenta } from 'kolorist';
import graphqlLoader from 'vite-plugin-graphql-loader';

export default ({ mode }: { mode: string }) => {
  const env = loadEnv(mode, process.cwd(), '');
  const devProfile = env.VITE_DEV_PROFILE || 'local';

  // Derive feature flags from the active profile — single source of truth.
  const tunnel = devProfile === 'tunnel' ? resolveTunnelConfig(env) : undefined;

  const phpWatchPatterns = [
    'templates/**/*.php',
    'modules/**/*.php',
    'features/**/*.php',
    'settings/**/*.php',
    'theme/**/*.php',
    '*.php',
  ];

  return defineConfig({
    publicDir: path.resolve(__dirname, './src/public'),
    root: path.resolve(__dirname, './src'),
    base: env.NODE_ENV === 'production' ? `${env.VITE_THEME_PATH}/dist/` : '/',
    build: {
      outDir: path.resolve(__dirname, 'dist'),
      emptyOutDir: true,
      manifest: true,
      minify: false,
      sourcemap: env.NODE_ENV === 'production' ? false : 'inline',
      target: 'es2022',
      rollupOptions: {
        input: {
          app: '/js/app.ts',
          admin: '/js/admin.ts',
        },
        external: ['jquery'],
        output: {
          entryFileNames: 'assets/[name]-[hash].js',
          chunkFileNames: 'assets/[name]-[hash].js',
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
      include: ['vue', 'pinia', 'tua-body-scroll-lock'],
    },
    plugins: [
      tailwindcss(),
      vue(),
      eslintPlugin(),
      VitePluginSvgSpritemap(
        [
          path.resolve(__dirname, 'src/sprite/*.svg'),
          path.resolve(__dirname, 'features/*/icons/*.svg'),
        ],
        {
          prefix: 'icon-',
          output: {
            filename: '../sprite.svg',
            name: 'sprite.svg',
          },
        },
      ),
      devAssetRewriter({ publicUrl: tunnel?.publicUrl }),
      graphqlLoader(),
      phpFullReload({ patterns: phpWatchPatterns }),
      cloudflareTunnel({
        enabled: !!tunnel,
        tunnelId: env.VITE_TUNNEL_ID,
        tunnelName: env.VITE_TUNNEL_NAME,
        tunnelHostname: tunnel?.hostname,
        tunnelPublicUrl: tunnel?.publicUrl,
        siteOrigin: env.VITE_ASSET_URL,
        vitePort: Number(env.VITE_TUNNEL_PORT || 5173),
      }),
      hotFile({ publicUrl: tunnel?.publicUrl }),
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
      cors: true,
      strictPort: true,
      fs: {
        allow: [path.resolve(__dirname)],
      },
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
        '@features': path.resolve(__dirname, './features'),
        '@modules': path.resolve(__dirname, './modules'),
        vue: 'vue/dist/vue.esm-bundler.js',
      },
    },
  });
};
