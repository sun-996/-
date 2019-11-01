module.exports = {
  devServer: {
    proxy: {
      '/api': {
        target: 'http://localhost:8080/vivo_api/',
        ws: true,
        changeOrigin: true,
        pathRewrite: {
          '^/api': ''
        }
      }
    }
  }
}