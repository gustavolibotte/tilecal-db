import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  base: '/dist/', 
  define: {
    __MYSQL_MAX_TEXT_SIZE__: 65535, 
    __DETECTOR_STYLE__: JSON.stringify('industrial'),
  },
  build: {
    outDir: '../backend/public/dist',
    emptyOutDir: true,
    rollupOptions: {
      output: {
        entryFileNames: `assets/[name].js`,
        chunkFileNames: `assets/[name].js`,
        assetFileNames: `assets/[name].[ext]`
      }
    }
  }
})