const path = require("path");
const mix = require("laravel-mix");
const webpack = require("webpack");
const dotenv = require("dotenv");
const CompressionPlugin = require("compression-webpack-plugin");
const fs = require("node:fs");

// Загружаем переменные окружения
dotenv.config();

mix.js("resources/js/app.js", "public/js").vue().version();

mix.options({
    hmrOptions: {
        host: "127.0.0.1",
        port: 8080,
    },
});

mix.sourceMaps()

mix.webpackConfig({
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "resources/js"),
            "@resources": path.resolve(__dirname, "resources"),
            "@img": path.resolve(__dirname, "resources/img"),
            "@public": path.resolve(__dirname, "public"),
        },
    },
    plugins: [
        new webpack.DefinePlugin({
            "process.env": JSON.stringify(dotenv.config().parsed),
        }),
	new CompressionPlugin({
            filename: "[path][base].gz",
            algorithm: "gzip",
            test: /\.(js|css|html|svg|json)$/,
            threshold: 10240,
            minRatio: 0.8,
        }),
    ],
});

mix.disableNotifications();

if (mix.inProduction()) {
    mix.version();
}
