/// <reference types="vitest" />
import { defineConfig, loadEnv } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import eslintPlugin from '@nabla/vite-plugin-eslint';
import VitePluginSvgSpritemap from '@spiriit/vite-plugin-svg-spritemap';
import path from 'node:path';
import vue from '@vitejs/plugin-vue';
import { createProxyHandler } from './src/js/build/middleware.ts';
import devAssetRewriter from './src/js/build/assetRewriter.ts';
import hotFile from './src/js/build/hotFile.ts';
import cloudflareTunnel from './src/js/build/cloudflareTunnel.ts';
import phpFullReload from './src/js/build/phpFullReload.ts';
import injectPluginSources from './src/js/build/injectPluginSources.ts';
import pluginAtAlias from './src/js/build/pluginAtAlias.ts';
import { findTofinoPluginDirs, findSiblingThemeDirs } from './src/js/build/tofinoPlugins.ts';
import { resolveTunnelConfig } from './src/js/build/tunnelConfig.ts';
import { bold, lightMagenta } from 'kolorist';
import graphqlLoader from 'vite-plugin-graphql-loader';

const themeDir = import.meta.dirname;

export default ({ mode }: { mode: string }) => {
  const env = loadEnv(mode, process.cwd(), '');
  const devProfile = env.VITE_DEV_PROFILE || 'local';

  // Derive feature flags from the active profile — single source of truth.
  const tunnel = devProfile === 'tunnel' ? resolveTunnelConfig(env) : undefined;

  // Tofino-aligned plugins are discovered by their `module.json` manifests —
  // the same convention the PHP side uses via `tofino/register_modules`. A
  // plugin without one is left untouched: not watched, not scanned by
  // Tailwind, not served through the dev server's HMR pipeline.
  const pluginsDir = path.resolve(themeDir, '../../plugins');
  const tofinoPluginDirs = findTofinoPluginDirs(pluginsDir);

  // Sibling child themes (e.g. compose-theme) are discovered the same way —
  // by `modules/<slug>/module.json` manifests. They run their own builds but
  // are served via /@fs/ and watched for full-reload when this dev server is
  // the active one.
  const themesDir = path.resolve(themeDir, '..');
  const siblingThemeDirs = findSiblingThemeDirs(themesDir, themeDir);

  const phpWatchPatterns = [
    'templates/**/*.php',
    'modules/**/*.php',
    'features/**/*.php',
    'settings/**/*.php',
    'theme/**/*.php',
    '*.php',
    // Per-plugin patterns derived from the discovered set. The phpFullReload
    // handler triggers full-reload on `.php` changes; non-PHP extensions are
    // tracked here purely so Vite's HMR sees changes to plugin source files
    // served through this dev server via /@fs/.
    ...tofinoPluginDirs.flatMap((dir) => [
      `${dir}/modules/**/*.php`,
      `${dir}/modules/**/*.{ts,vue,css}`,
      `${dir}/app.{ts,css}`,
    ]),
    ...siblingThemeDirs.flatMap((dir) => [
      `${dir}/modules/**/*.php`,
      `${dir}/components/**/*.php`,
      `${dir}/features/**/*.php`,
      `${dir}/templates/**/*.php`,
      `${dir}/*.php`,
      `${dir}/modules/**/*.{ts,vue,css}`,
      `${dir}/components/**/*.css`,
      `${dir}/features/**/*.{ts,vue,css}`,
      `${dir}/app.{ts,css}`,
    ]),
  ];

  // Tailwind `@source` globs (absolute paths) injected into app.css in dev
  // only — see injectPluginSources. Production builds never see these, so
  // the theme's dist CSS stays free of plugin-only utilities (each plugin
  // emits its own CSS from its own Vite build).
  const pluginTailwindSources = tofinoPluginDirs.flatMap((dir) => [
    `${dir}/modules/**/*.{php,vue,ts,js}`,
    `${dir}/app.ts`,
  ]);

  return defineConfig({
    publicDir: path.resolve(themeDir, './src/public'),
    root: path.resolve(themeDir, './src'),
    base: env.NODE_ENV === 'production' ? `${env.VITE_THEME_PATH}/dist/` : '/',
    build: {
      outDir: path.resolve(themeDir, 'dist'),
      emptyOutDir: true,
      manifest: true,
      sourcemap: env.NODE_ENV === 'production' ? false : 'inline',
      target: 'es2022',
      rollupOptions: {
        preserveEntrySignatures: 'exports-only',
        input: {
          app: '/js/app.ts',
          styles: '/js/styles.ts',
          admin: '/js/admin.ts',
          vue: '/js/vendor/vue.ts',
          pinia: '/js/vendor/pinia.ts',
        },
        external: (id, importer) => {
          if (id === 'jquery') {
            return true;
          }

          return id === 'vue' && Boolean(importer?.includes('/node_modules/pinia/'));
        },
        output: {
          entryFileNames: 'assets/[name]-[hash].js',
          chunkFileNames: 'assets/[name]-[hash].js',
          globals: {
            jquery: 'jQuery',
          },
          manualChunks: (id) => {
            // Split vendor chunks by package name
            if (id.includes('node_modules')) {
              if (id.includes('node_modules/vue/') || id.includes('node_modules/@vue/')) {
                return 'vendor-vue';
              }

              if (
                id.includes('node_modules/pinia/') ||
                id.includes('node_modules/@vue/devtools-api/')
              ) {
                return 'vendor-pinia';
              }

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
      pluginAtAlias({ themeSrcDir: path.resolve(themeDir, './src') }),
      injectPluginSources({ sources: pluginTailwindSources }),
      tailwindcss(),
      vue(),
      eslintPlugin(),
      VitePluginSvgSpritemap(
        [
          path.resolve(themeDir, 'src/sprite/*.svg'),
          path.resolve(themeDir, 'features/*/icons/*.svg'),
          // Tofino-aligned plugins: any icons under modules/<slug>/icons get
          // sprited alongside theme/feature icons. Plugins reference symbols
          // via <use href="#icon-<name>"> the same way the theme does.
          ...tofinoPluginDirs.map((dir) => `${dir}/modules/*/icons/*.svg`),
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
        // Allow the theme, the sibling plugins directory, and the themes
        // directory so Tofino-aligned plugins and child themes can be served
        // (and HMR'd) from this dev server via /@fs/.
        allow: [
          path.resolve(themeDir),
          path.resolve(themeDir, '../../plugins'),
          path.resolve(themeDir, '..'),
        ],
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
        // `@` is handled by the `pluginAtAlias` plugin above so it can
        // resolve relative to the importer (theme src vs plugin module dir).
        // Vite's built-in alias plugin runs before user plugins with
        // `enforce: 'pre'`, so a static `@` entry here would steal plugin
        // imports before they could be redirected.
        '@features': path.resolve(themeDir, './features'),
        '@modules': path.resolve(themeDir, './modules'),
        vue: 'vue/dist/vue.esm-bundler.js',
        // Full vue-i18n build with compiler — plugin code loads locale JSON
        // as plain objects and needs runtime message compilation.
        'vue-i18n': 'vue-i18n/dist/vue-i18n.esm-bundler.js',
      },
    },
  });
};
