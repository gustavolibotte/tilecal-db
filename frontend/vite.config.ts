import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  build: {
    outDir: '../backend/public/dist'
  },
  define: {
    __APP_BASEURL__: JSON.stringify(process.env.app_baseURL),
    __MAX_FILE_SIZE__: 5000000
  }
})
