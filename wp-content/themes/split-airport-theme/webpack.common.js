const path = require("path");
const webpack = require("webpack");
const glob = require("glob");
const TerserPlugin = require("terser-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const RemoveEmptyScriptsPlugin = require("webpack-remove-empty-scripts");
const { CleanWebpackPlugin } = require("clean-webpack-plugin");
const PostCssPipelineWebpackPlugin = require("postcss-pipeline-webpack-plugin");
const Postcss = require("postcss");
const CriticalSplit = require("postcss-critical-split");
const Cssnano = require("cssnano");
const DiscardDuplicates = require("postcss-discard-duplicates");
const ESLintPlugin = require("eslint-webpack-plugin");
const StylelintPlugin = require("stylelint-webpack-plugin");

// Object with all JS paths for all JS

const allJs = [];
const allJsObject = {};
allJs["js/all/all"] = glob.sync("./assets/js/**/**/*.js");

// reorder array (global, header, ..., footer)

let footerJS = allJs["js/all/all"].findIndex(
    (element) => element === "./assets/js/footer.js"
);

allJs["js/all/all"].splice(footerJS, 1);
allJs["js/all/all"].push("./assets/js/footer.js");

let globalJS = allJs["js/all/all"].findIndex(
    (element) => element === "./assets/js/global.js"
);

allJs["js/all/all"].splice(globalJS, 1);
allJs["js/all/all"].splice(0, 0, "./assets/js/global.js");

let headerJS = allJs["js/all/all"].findIndex(
    (element) => element === "./assets/js/header.js"
);

allJs["js/all/all"].splice(headerJS, 1);
allJs["js/all/all"].splice(1, 0, "./assets/js/header.js");

Object.assign(allJsObject, allJs);

// Object with all SCSS paths for all CSS

const allcss = [];
const allcssObject = {};
allcss["css/all/all"] = glob
    .sync("./assets/scss/**/**/*.scss")
    .filter((item) => {
        // Add global folder on exception
        let itemSplit = item.split("/");
        if (!itemSplit.includes("global")) {
            return item;
        }
    });

// reorder array (global, ..., footer)

let footer = allcss["css/all/all"].findIndex(
    (element) => element === "./assets/scss/footer.scss"
);

allcss["css/all/all"].splice(footer, 1);
allcss["css/all/all"].push("./assets/scss/footer.scss");

let global = allcss["css/all/all"].findIndex(
    (element) => element === "./assets/scss/global.scss"
);

allcss["css/all/all"].splice(global, 1);
allcss["css/all/all"].splice(0, 0, "./assets/scss/global.scss");

Object.assign(allcssObject, allcss);

// Object with all JS paths for blocks

const sourcePathsBlocksJs = glob
    .sync("./blocks/**/*.js")
    .reduce((entryPaths, item) => {
        const path = item.split("/");
        path.pop();
        let name = path.join("/");
        name = name.replace(".", "");
        entryPaths["js/parts" + name] = item;
        return entryPaths;
    }, {});

// Object with all CSS paths for blocks

const sourcePathsBlocksCss = glob
    .sync("./blocks/**/*.scss")
    .reduce((entryPaths, item) => {
        const path = item.split("/");
        path.pop();
        let name = path.join("/");
        name = name.replace(".", "");
        entryPaths["css/parts" + name] = item;
        return entryPaths;
    }, {});

// Object with all JS paths for regular templates

const sourcePathsRegularJs = glob
    .sync("./assets/js/*.js")
    .reduce((entryPaths, item) => {
        const path = item.split("/");
        let name = path.slice(-1);
        name = name[0].replace(".js", "");
        if (name != "footer" || name != "header" || name != "global") {
            entryPaths["js/parts/regular/" + name] = [
                "./assets/js/global.js",
                "./assets/js/header.js",
                item,
                "./assets/js/footer.js",
            ];
        }
        if (name === "footer" || name === "header" || name === "global") {
            entryPaths["js/parts/regular/" + name] = item;
        }

        return entryPaths;
    }, {});

// Object with all CSS paths for regular templates

const sourcePathsRegularCss = glob
    .sync("./assets/scss/*.scss")
    .reduce((entryPaths, item) => {
        const path = item.split("/");
        let name = path.slice(-1);
        name = name[0].replace(".scss", "");
        if (name != "footer" || name != "global") {
            entryPaths["css/parts/regular/" + name] = [
                "./assets/scss/global.scss",
                item,
                "./assets/scss/footer.scss",
            ];
        }
        if (name === "footer" || name === "global" || name === "header") {
            entryPaths["css/parts/regular/" + name] = item;
        }
        return entryPaths;
    }, {});

// Object with all JS paths for Woo

const sourcePathsWooJs = glob
    .sync("./assets/js/woo/**/*.js")
    .reduce((entryPaths, item) => {
        const path = item.split("/");
        let name = path.slice(-1);
        name = name[0].replace(".js", "");
        entryPaths["js/parts/regular/woo/" + name] = [
            "./assets/js/global.js",
            "./assets/js/header.js",
            item,
            "./assets/js/footer.js",
        ];

        return entryPaths;
    }, {});

// Object with all CSS paths for Woo

const sourcePathsRegularCssWoo = glob
    .sync("./assets/scss/woo/*.scss")
    .reduce((entryPaths, item) => {
        const path = item.split("/");
        let name = path.slice(-1);
        name = name[0].replace(".scss", "");
        if (name != "footer" || name != "global") {
            entryPaths["css/parts/regular/woo/" + name] = [
                "./assets/scss/global.scss",
                item,
                "./assets/scss/footer.scss",
            ];
        }

        return entryPaths;
    }, {});

const allPathsMerge = Object.assign(
    {},
    allJsObject,
    allcssObject,
    sourcePathsBlocksJs,
    sourcePathsBlocksCss,
    sourcePathsRegularJs,
    sourcePathsWooJs,
    sourcePathsRegularCss,
    sourcePathsRegularCssWoo
);

var config = {
    entry: allPathsMerge,
    output: {
        path: path.resolve(__dirname, "dist"),
        filename: "[name].min.js",
    },
    plugins: [
        new RemoveEmptyScriptsPlugin(),

        // Extracts js to css
        new MiniCssExtractPlugin({
            filename: "[name].css",
        }),

        // A webpack plugin to remove/clean your build folder(s).
        // new CleanWebpackPlugin(),

        // Output for critical css
        new PostCssPipelineWebpackPlugin({
            processor: Postcss([
                CriticalSplit({
                    output: CriticalSplit.output_types.CRITICAL_CSS,
                }),
            ]),
            transformName: (name) => {
                let fullPath;
                name = name.split("/");
                let singlePath = name.slice(-2);
                if (singlePath[0] === "woo") {
                    fullPath = "css/parts/critical/" + singlePath.join("/");
                } else if (singlePath[0] === "all") {
                    fullPath = "css/all/critical/" + name.pop();
                } else {
                    fullPath = "css/parts/critical/" + name.pop();
                }

                return fullPath;
            },
        }),

        // Output for regular css
        new PostCssPipelineWebpackPlugin({
            processor: Postcss([
                CriticalSplit({
                    output: CriticalSplit.output_types.REST_CSS,
                }),
            ]),
            transformName: (name) => name,
        }),

        // Removes the duplicates
        new PostCssPipelineWebpackPlugin({
            processor: Postcss([DiscardDuplicates()]),
            transformName: (name) => name,
        }),

        // CSS minification
        new PostCssPipelineWebpackPlugin({
            processor: Postcss([Cssnano()]),
            transformName: (name) => name,
        }),

        // Global (JS)
        new webpack.ProvidePlugin({
            $: "jquery",
            jQuery: "jquery",
        }),

        // JS Linter
        new ESLintPlugin({
            overrideConfigFile: path.resolve(__dirname, ".eslintrc"),
            //context: path.resolve(__dirname, "./assets/js"),
            files: [path.resolve(__dirname, "./js/") + "**/*.js", path.resolve(__dirname, "./assets/components/") + "**/*.js", path.resolve(__dirname, "./blocks/") + "**/**/*.js"]
        }),

        // CSS Linter
        new StylelintPlugin({
            configFile: path.resolve(__dirname, "stylelint.config.js"),
            //context: [path.resolve(__dirname, "./assets/scss"), path.resolve(__dirname, "./blocks") ] ,
            files: [path.resolve(__dirname, "./assets/scss/") + "**/*.scss", path.resolve(__dirname, "./assets/scss/") + "**/*.css", path.resolve(__dirname, "./blocks/") + "**/**/*.scss", path.resolve(__dirname, "./blocks/") + "**/**/*.css"],
        }),
    ],
    module: {
        rules: [
            {
                test: /\.m?js$/,
                exclude: /(node_modules|bower_components)/,
                use: {
                    loader: "babel-loader",
                    options: {
                        presets: ["@babel/preset-env"],
                        targets: {
                            browsers: [
                                "last 2 Chrome versions",
                                "last 2 Firefox versions",
                                "last 2 Safari versions",
                                "last 2 iOS versions",
                                "last 1 Android version",
                                "last 1 ChromeAndroid version",
                            ],
                        },
                    },
                },
            },
            {
                test: /\.s?css$/,
                exclude: /(node_modules|bower_components)/,
                use: [
                    // Creates `style` nodes from JS strings
                    MiniCssExtractPlugin.loader,
                    // Translates CSS into CommonJS
                    {
                        loader: "css-loader",
                        options: {
                            sourceMap: false,
                            url: false,
                        },
                    },
                    "postcss-loader",
                    // Compiles Sass to CSS
                    {
                        loader: "sass-loader",
                        options: {
                            sourceMap: false,
                            sassOptions: {
                                outputStyle: "expanded",
                            },
                        },
                    },
                ],
            },

        ],
    },
    optimization: {
        minimize: true,
        minimizer: [new TerserPlugin()],
    },
    externals: {
        jquery: "jQuery",
    },
};

module.exports = config;
