const { merge } = require('webpack-merge');
const common = require('./webpack.common.js');
var config = {
     mode: 'production',
     devtool: false,
     watch: false, 
};

module.exports = merge(common, config);
