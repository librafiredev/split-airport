const path = require("path");
let rootPath = path.resolve(__dirname, "");
let rootPathArr = rootPath.split("\\");

if (rootPathArr.length <= 1) {
    rootPathArr = rootPath.split("/");
}

rootPathArr.splice(-3);

let projectName = rootPathArr.pop();

const BrowserSyncPlugin = require("browser-sync-webpack-plugin");
const { merge } = require("webpack-merge");
const common = require("./webpack.common.js");

var config = {
    mode: "development",
    devtool: "inline-source-map",
    watch: true,
    plugins: [
        new BrowserSyncPlugin({
            host: "localhost",
            port: 3000,
            proxy: "http://localhost/" + projectName,
        }),
    ],
};

module.exports = merge(common, config);
