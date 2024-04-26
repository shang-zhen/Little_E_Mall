import { fileURLToPath, URL } from "node:url";
import { defineConfig, loadEnv } from "vite";
import vue from "@vitejs/plugin-vue";
import AutoImport from "unplugin-auto-import/vite";
import Components from "unplugin-vue-components/vite";
import Icons from "unplugin-icons/vite";
import IconsResolver from "unplugin-icons/resolver";
import { ElementPlusResolver } from "unplugin-vue-components/resolvers";

// https://vitejs.dev/config/
export default defineConfig(({ mode }) => {
  // 此处加载当前 mode（例如 development）的环境变量
  process.env = { ...process.env, ...loadEnv(mode, process.cwd()) };
  return {
    base: "./",
    plugins: [
      vue(),
      AutoImport({
        imports: ["vue"],
        resolvers: [
          ElementPlusResolver(),
          // Auto import icon components
          // 自动导入图标组件
          IconsResolver({
            prefix: "Icon"
          })
        ],
        dts: "src/global/autoImport.d.ts"
      }),
      Components({
        resolvers: [
          // Auto register icon components
          // 自动注册图标组件
          IconsResolver({
            enabledCollections: ["ep"]
          }),
          // Auto register Element Plus components
          // 自动导入 Element Plus 组件
          ElementPlusResolver()
        ],
        dts: "src/global/components.d.ts"
      }),
      Icons({
        autoInstall: true
      })
    ],
    build: {
      rollupOptions: {
        output: {
          entryFileNames: "js/[name]-[hash].js",
          chunkFileNames: "js/[name]-[hash].js",
          assetFileNames(chunkInfo) {
            if (chunkInfo.name && chunkInfo.name.endsWith(".css")) {
              return "css/[name]-[hash].css";
            }
            const imgExt = [
              ".png",
              ".jpg",
              ".jpeg",
              ".webp",
              ".svg",
              ".gif",
              ".bmp",
              ".tiff",
              "ico"
            ];
            if (imgExt.some((ext) => chunkInfo.name && chunkInfo.name.endsWith(ext))) {
              return "imgs/[name]-[hash].[ext]";
            }
            return "assets/[name]-[hash].[ext]";
          },
          manualChunks(id) {
            if (!id.includes("node_modules")) return;
            if (id.includes("echarts")) {
              return "echarts";
            } else if (id.includes("element-plus")) {
              return "element-plus";
            } else if (id.includes("axios")) {
              return "axios";
            } else if (id.includes("zrender")) {
              return "zrender";
            } else if (id.includes("lodash-es")) {
              return "lodash-es";
            } else {
              return "vendor";
            }
          }
        }
      }
    },
    define: {
      "process.env": process.env
    },
    resolve: {
      alias: {
        "@": fileURLToPath(new URL("./src", import.meta.url))
      }
    },
    server: {
      port: 3001,
      proxy: {
        "^/api": {
          target: "http://127.0.0.1:3333",
          changeOrigin: true
        }
      }
    },
    css: {
      preprocessorOptions: {
        scss: {
          additionalData: `
          @import './src/assets/sass/global.sass';
          @import './src/assets/sass/theme.scss';
          @import './src/assets/sass/media.scss';
          `
        }
      }
    }
  };
});
