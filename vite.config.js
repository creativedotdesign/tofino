import { defineConfig, loadEnv } from 'vite';
import vue from '@vitejs/plugin-vue';
import eslintPlugin from 'vite-plugin-eslint';
import liveReload from 'vite-plugin-live-reload';
import critical from 'rollup-plugin-critical';
import viteSvgIcons from 'vite-plugin-svg-icons';

import path from 'path';

const isProd = process.env.NODE_ENV;

console.log(isProd);

//   const http = require('http');

//   const jsdom = require("jsdom");
// const { JSDOM } = jsdom;

// const urls = [];

// const parseSiteMap = () => {

//   // get the raw XML from the RSS feed
//   const url = 'http://redding.test/page-sitemap.xml';

//   //const url = `${process.env.VITE_ASSET_URL}$/sitemap.xml`;

// let data = '';



//   http.get(url, (resp) => {
//     console.log('Running request!');

//     // A chunk of data has been received.
//     resp.on('data', (chunk) => {
//       data += chunk;
//     });

//     resp.on('end', () => {

//       const dom = new JSDOM(data, {contentType:'text/xml'})
//       const document = dom.window.document;

//       const nodes = document.querySelectorAll('urlset url loc');


//        nodes.forEach(node => urls.push(node.textContent));

       

//        // console.log(urls);
//     });


//   }).on("error", (err) => {
//     console.log("Error: " + err.message);
//   });

//   // fetch url and parse as xml document

//   // const response = await fetch(url)
//   // const xml = await response.text()
//   // const doc = new DOMParser().parseFromString(xml, 'text/xml')

//   // // console.log(xml)
//   // console.log(doc)

// }

export default ({ mode }) => {
  process.env = {...process.env, ...loadEnv(mode, process.cwd())};

  return defineConfig({
    publicDir: path.resolve(__dirname, "./src/assets"),
    root: path.resolve(__dirname, "./src"),
    base: isProd === 'production' ? `${process.env.VITE_THEME_PATH}/dist/`: '/',
    build: {
      outDir: path.resolve(__dirname, 'dist'),
      // outDir: `${process.env.VITE_ASSET_URL}${process.env.VITE_THEME_PATH}/dist/`,
      emptyOutDir: true,
      manifest: true,
      sourcemap: true,
      target: 'es2018',
      rollupOptions: {
        input: '/js/app.ts',
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
      viteSvgIcons({
        iconDirs: [path.resolve(process.cwd(), 'src/assets/svgs/sprites')],
        symbolId: 'icon-[name]',
      }),
      // critical({
      //   criticalUrl: `${process.env.VITE_ASSET_URL}/`,
      //   criticalBase: './dist/',
      //   criticalPages: [
      //       { uri: '', template: 'index' },
      //       // { uri: 'contact', template: 'contact/index' },
      //   ],
      //   criticalConfig: {
      //     // Add config here
      //   },
      // }),
      // host()
    ],
    // server: {
    //   port: 8888,
    //   origin: "http://localhost:8888",
    // },
    // server: {
    //   // origin: `http://tofino.test/${process.env.VITE_ASSET_URL}${process.env.VITE_THEME_PATH}`,
    //   cors: false,
    //   strictPort: true,
    //   port: 3000,
    //   proxy: {
    //     "/": {
    //       target: "http://tofino.test",
    //       changeOrigin: true,
    //       // rewrite: (path) => path.replace(/^\/api/, ""),
    //     }
    //   }
    // },

    server: {
      // required to load scripts from custom host
      cors: true,
      strictPort: true,
      port: 3000,
      hmr: {
        port: 3000,
        host: 'localhost',
        protocol: 'ws',
      },
    },

    // server: {
    //   cors: true,
    //   strictPort: true,
    //   port: 3000,
    //   host: 'tofino.test',
    //   origin: 'http://tofino.test/',
    //   hmr: {
    //     host: 'tofino.test',
    //     port: 80,
    //   },
    // },

    resolve: {
      alias: {
        '@': '/js',
        'vue': 'vue/dist/vue.esm-bundler.js'
      },
    },
  });
};
