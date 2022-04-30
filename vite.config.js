import { defineConfig, loadEnv, splitVendorChunkPlugin } from 'vite';
import vue from '@vitejs/plugin-vue';
import VueTypeImports from 'vite-plugin-vue-type-imports';
import eslintPlugin from 'vite-plugin-eslint';
import liveReload from 'vite-plugin-live-reload';
import { createSvgIconsPlugin } from 'vite-plugin-svg-icons';
import path from 'path';

export default ({ mode }) => {
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
          globals: {
            jquery: 'jQuery',
          },
        },
      },
    },
    plugins: [
      splitVendorChunkPlugin(),
      VueTypeImports(),
      vue({
        reactivityTransform: true,
      }),
      eslintPlugin(),
      liveReload([`${__dirname}/*.php`, `${__dirname}/(lib|templates)/**/*.php`]),
      createSvgIconsPlugin({
        iconDirs: [path.resolve(process.cwd(), 'src/sprite')],
        symbolId: 'icon-[name]',
        customDomId: 'tofino-sprite',
      }),
    ],
    server: {
      cors: true,
      strictPort: true,
      port: 3000,
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
