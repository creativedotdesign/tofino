import { defineConfig, loadEnv } from 'vite';
import vue from '@vitejs/plugin-vue';
import eslintPlugin from 'vite-plugin-eslint';
import liveReload from 'vite-plugin-live-reload';
import { createSvgIconsPlugin } from 'vite-plugin-svg-icons'
import path from 'path';

export default ({ mode }) => {
  process.env = {...process.env, ...loadEnv(mode, process.cwd())};

  return defineConfig({
    publicDir: path.resolve(__dirname, "./src/assets"),
    root: path.resolve(__dirname, "./src"),
    base: process.env.NODE_ENV === 'production' ? `${process.env.VITE_THEME_PATH}/dist/`: '/',
    build: {
      outDir: path.resolve(__dirname, 'dist'),
      emptyOutDir: true,
      manifest: true,
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
      vue(),
      eslintPlugin(),
      liveReload([`${__dirname}/*.php`, `${__dirname}/(lib|templates)/**/*.php`]),
      createSvgIconsPlugin({
        iconDirs: [path.resolve(process.cwd(), 'src/assets/svgs/sprites')],
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
        '@': '/js',
        'vue': 'vue/dist/vue.esm-bundler.js'
      },
    },
  });
};