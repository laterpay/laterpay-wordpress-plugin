var respMod   = require("resp-modifier");
var httpProxy = require("http-proxy");
var http      = require("http");

var utils     = require("./lib/utils");

/**
 * @param opts
 * @param [additionalRules]
 * @param [additionalMiddleware]
 * @returns {*}
 * @param errHandler
 */
function init(opts, additionalRules, additionalMiddleware, errHandler) {

    var proxyServer = httpProxy.createProxyServer();
    var hostHeader  = utils.getProxyHost(opts);

    if (!errHandler) {
        errHandler = function (err) {
            console.log(err.message);
        }
    }

    var server = http.createServer(function(req, res) {

        var middleware  = respMod({
            rules: getRules(req.headers.host)
        });

        var next = function () {
            proxyServer.web(req, res, {
                target: opts.target,
                headers: {
                    host: hostHeader,
                    "accept-encoding": "identity",
                    agent: false
                }
            });
        };

        if (additionalMiddleware) {
            additionalMiddleware(req, res, function (success) {
                if (success) {
                    return;
                }
                utils.handleIe(req);
                middleware(req, res, next);
            });
        } else {
            utils.handleIe(req);
            middleware(req, res, next);
        }
    }).on("error", errHandler);

    // Handle proxy errors
    proxyServer.on("error", errHandler);

    // Remove headers
    proxyServer.on("proxyRes", function (res) {
        if (res.statusCode === 302) {
            var origin = require("url").parse(res.req._headers.origin);
            res.headers.location = utils.handleRedirect(res.headers.location, opts, origin.host);
        }
        utils.removeHeaders(res.headers, ["content-length", "content-encoding"]);
    });

    function getRules(host) {

        var rules = [utils.rewriteLinks(opts, host)];

        if (additionalRules) {
            if (Array.isArray(additionalRules)) {
                additionalRules.forEach(function (rule) {
                    rules.push(rule);
                })
            } else {
                rules.push(additionalRules);
            }
        }
        return rules;
    }

    return server;
}

module.exports = {
    init: init
};

