import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { json } from 'stream/consumers';

// https://vitejs.dev/config/
export default defineConfig(() => {
  return {
    plugins: [react()],
    build: {
      outDir: '../backend/public/dist'
    },
    define: {
      __APP_BASEURL__: JSON.stringify(process.env.app_baseURL),
      __MAX_FILE_SIZE__: 5000000,
      __INVALID_DATA_MSG__: JSON.stringify('Invalid data'),
      __DEFAULT_ERROR_MSG__: JSON.stringify('Error, something went wrong'),
      __ENV__: JSON.stringify(process.env.ENVIRONMENT)
    }
  };
})
