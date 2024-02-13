
  const { createProxyMiddleware } = require("http-proxy-middleware");

  module.exports = {
    // Use the 'middleware' option to create a proxy that masks the deep URL.
    middleware: [
      // This middleware intercepts requests to the root and proxies them to the deep path.
      createProxyMiddleware("/", {
        target:
          "http://localhost/projects/create-prisma-php-app/metadata-test",
        changeOrigin: true,
        pathRewrite: {
          "^/": "/projects//create-prisma-php-app//metadata-test", // Rewrite the path.
        },
      }),
    ],
    proxy: "http://localhost:3000", // Proxy the BrowserSync server.
    serveStatic: ["src/app"], // Serve static files from this directory.
    files: "src/**/*.*",
    notify: false,
  };