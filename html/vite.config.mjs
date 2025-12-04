import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import { resolve } from 'path'
import { BootstrapVueNextResolver } from "bootstrap-vue-next";
import Components from 'unplugin-vue-components/vite';

export default defineConfig({
  plugins: [
    laravel({
      input: ["resources/js/app.js"],
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    Components({
      resolvers: [BootstrapVueNextResolver()],
    }),
  ],
  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/js'),
      '@resources': resolve(__dirname, 'resources'),
      '@img': resolve(__dirname, 'resources/img'),
      '@public': resolve(__dirname, 'public'),
    },
  },
  server: {
    host: '127.0.0.1',
    port: 8080,
    hmr: {
      host: '127.0.0.1',
      port: 8080,
    },
  },
  build: {
    outDir: 'public/build',
    rollupOptions: {
      output: {
        manualChunks: undefined,
      },
    },
  },
  define: {
    'process.env': process.env,
  },
})