<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WPML\Core\Twig;

use WPML\Core\Twig\Cache\CacheInterface;
use WPML\Core\Twig\Cache\FilesystemCache;
use WPML\Core\Twig\Cache\NullCache;
use WPML\Core\Twig\Error\Error;
use WPML\Core\Twig\Error\LoaderError;
use WPML\Core\Twig\Error\RuntimeError;
use WPML\Core\Twig\Error\SyntaxError;
use WPML\Core\Twig\Extension\CoreExtension;
use WPML\Core\Twig\Extension\EscaperExtension;
use WPML\Core\Twig\Extension\ExtensionInterface;
use WPML\Core\Twig\Extension\GlobalsInterface;
use WPML\Core\Twig\Extension\InitRuntimeInterface;
use WPML\Core\Twig\Extension\OptimizerExtension;
use WPML\Core\Twig\Extension\StagingExtension;
use WPML\Core\Twig\Loader\ArrayLoader;
use WPML\Core\Twig\Loader\ChainLoader;
use WPML\Core\Twig\Loader\LoaderInterface;
use WPML\Core\Twig\Loader\SourceContextLoaderInterface;
use WPML\Core\Twig\Node\ModuleNode;
use WPML\Core\Twig\NodeVisitor\NodeVisitorInterface;
use WPML\Core\Twig\RuntimeLoader\RuntimeLoaderInterface;
use WPML\Core\Twig\TokenParser\TokenParserInterface;
/**
 * Stores the Twig configuration.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Environment
{
    const VERSION = '1.42.4';
    const VERSION_ID = 14204;
    const MAJOR_VERSION = 1;
    const MINOR_VERSION = 42;
    const RELEASE_VERSION = 4;
    const EXTRA_VERSION = '';
    protected $charset;
    protected $loader;
    protected $debug;
    protected $autoReload;
    protected $cache;
    protected $lexer;
    protected $parser;
    protected $compiler;
    protected $baseTemplateClass;
    protected $extensions;
    protected $parsers;
    protected $visitors;
    protected $filters;
    protected $tests;
    protected $functions;
    protected $globals;
    protected $runtimeInitialized = \false;
    protected $extensionInitialized = \false;
    protected $loadedTemplates;
    protected $strictVariables;
    protected $unaryOperators;
    protected $binaryOperators;
    protected $templateClassPrefix = '\\WPML\\Core\\__TwigTemplate_';
    protected $functionCallbacks = [];
    protected $filterCallbacks = [];
    protected $staging;
    private $originalCache;
    private $bcWriteCacheFile = \false;
    private $bcGetCacheFilename = \false;
    private $lastModifiedExtension = 0;
    private $extensionsByClass = [];
    private $runtimeLoaders = [];
    private $runtimes = [];
    private $optionsHash;
    /**
     * Constructor.
     *
     * Available options:
     *
     *  * debug: When set to true, it automatically set "auto_reload" to true as
     *           well (default to false).
     *
     *  * charset: The charset used by the templates (default to UTF-8).
     *
     *  * base_template_class: The base template class to use for generated
     *                         templates (default to \Twig\Template).
     *
     *  * cache: An absolute path where to store the compiled templates,
     *           a \Twig\Cache\CacheInterface implementation,
     *           or false to disable compilation cache (default).
     *
     *  * auto_reload: Whether to reload the template if the original source changed.
     *                 If you don't provide the auto_reload option, it will be
     *                 determined automatically based on the debug value.
     *
     *  * strict_variables: Whether to ignore invalid variables in templates
     *                      (default to false).
     *
     *  * autoescape: Whether to enable auto-escaping (default to html):
     *                  * false: disable auto-escaping
     *                  * true: equivalent to html
     *                  * html, js: set the autoescaping to one of the supported strategies
     *                  * name: set the autoescaping strategy based on the template name extension
     *                  * PHP callback: a PHP callback that returns an escaping strategy based on the template "name"
     *
     *  * optimizations: A flag that indicates which optimizations to apply
     *                   (default to -1 which means that all optimizations are enabled;
     *                   set it to 0 to disable).
     */
    public function __construct(\WPML\Core\Twig\Loader\LoaderInterface $loader = null, $options = [])
    {
        if (null !== $loader) {
            $this->setLoader($loader);
        } else {
            @\trigger_error('Not passing a "Twig\\Lodaer\\LoaderInterface" as the first constructor argument of "Twig\\Environment" is deprecated since version 1.21.', \E_USER_DEPRECATED);
        }
        $options = \array_merge(['debug' => \false, 'charset' => 'UTF-8', 'base_template_class' => '\\WPML\\Core\\Twig\\Template', 'strict_variables' => \false, 'autoescape' => 'html', 'cache' => \false, 'auto_reload' => null, 'optimizations' => -1], $options);
        $this->debug = (bool) $options['debug'];
        $this->charset = \strtoupper($options['charset']);
        $this->baseTemplateClass = $options['base_template_class'];
        $this->autoReload = null === $options['auto_reload'] ? $this->debug : (bool) $options['auto_reload'];
        $this->strictVariables = (bool) $options['strict_variables'];
        $this->setCache($options['cache']);
        $this->addExtension(new \WPML\Core\Twig\Extension\CoreExtension());
        $this->addExtension(new \WPML\Core\Twig\Extension\EscaperExtension($options['autoescape']));
        $this->addExtension(new \WPML\Core\Twig\Extension\OptimizerExtension($options['optimizations']));
        $this->addExtension(new \WPML\Core\Twig\Extension\ConstantExtension());
        $this->staging = new \WPML\Core\Twig\Extension\StagingExtension();
        // For BC
        if (\is_string($this->originalCache)) {
            $r = new \ReflectionMethod($this, 'writeCacheFile');
            if (__CLASS__ !== $r->getDeclaringClass()->getName()) {
                @\trigger_error('The Twig\\Environment::writeCacheFile method is deprecated since version 1.22 and will be removed in Twig 2.0.', \E_USER_DEPRECATED);
                $this->bcWriteCacheFile = \true;
            }
            $r = new \ReflectionMethod($this, 'getCacheFilename');
            if (__CLASS__ !== $r->getDeclaringClass()->getName()) {
                @\trigger_error('The Twig\\Environment::getCacheFilename method is deprecated since version 1.22 and will be removed in Twig 2.0.', \E_USER_DEPRECATED);
                $this->bcGetCacheFilename = \true;
            }
        }
    }
    /**
     * Gets the base template class for compiled templates.
     *
     * @return string The base template class name
     */
    public function getBaseTemplateClass()
    {
        return $this->baseTemplateClass;
    }
    /**
     * Sets the base template class for compiled templates.
     *
     * @param string $class The base template class name
     */
    public function setBaseTemplateClass($class)
    {
        $this->baseTemplateClass = $class;
        $this->updateOptionsHash();
    }
    /**
     * Enables debugging mode.
     */
    public function enableDebug()
    {
        $this->debug = \true;
        $this->updateOptionsHash();
    }
    /**
     * Disables debugging mode.
     */
    public function disableDebug()
    {
        $this->debug = \false;
        $this->updateOptionsHash();
    }
    /**
     * Checks if debug mode is enabled.
     *
     * @return bool true if debug mode is enabled, false otherwise
     */
    public function isDebug()
    {
        return $this->debug;
    }
    /**
     * Enables the auto_reload option.
     */
    public function enableAutoReload()
    {
        $this->autoReload = \true;
    }
    /**
     * Disables the auto_reload option.
     */
    public function disableAutoReload()
    {
        $this->autoReload = \false;
    }
    /**
     * Checks if the auto_reload option is enabled.
     *
     * @return bool true if auto_reload is enabled, false otherwise
     */
    public function isAutoReload()
    {
        return $this->autoReload;
    }
    /**
     * Enables the strict_variables option.
     */
    public function enableStrictVariables()
    {
        $this->strictVariables = \true;
        $this->updateOptionsHash();
    }
    /**
     * Disables the strict_variables option.
     */
    public function disableStrictVariables()
    {
        $this->strictVariables = \false;
        $this->updateOptionsHash();
    }
    /**
     * Checks if the strict_variables option is enabled.
     *
     * @return bool true if strict_variables is enabled, false otherwise
     */
    public function isStrictVariables()
    {
        return $this->strictVariables;
    }
    /**
     * Gets the current cache implementation.
     *
     * @param bool $original Whether to return the original cache option or the real cache instance
     *
     * @return CacheInterface|string|false A Twig\Cache\CacheInterface implementation,
     *                                     an absolute path to the compiled templates,
     *                                     or false to disable cache
     */
    public function getCache($original = \true)
    {
        return $original ? $this->originalCache : $this->cache;
    }
    /**
     * Sets the current cache implementation.
     *
     * @param CacheInterface|string|false $cache A Twig\Cache\CacheInterface implementation,
     *                                           an absolute path to the compiled templates,
     *                                           or false to disable cache
     */
    public function setCache($cache)
    {
        if (\is_string($cache)) {
            $this->originalCache = $cache;
            $this->cache = new \WPML\Core\Twig\Cache\FilesystemCache($cache);
        } elseif (\false === $cache) {
            $this->originalCache = $cache;
            $this->cache = new \WPML\Core\Twig\Cache\NullCache();
        } elseif (null === $cache) {
            @\trigger_error('Using "null" as the cache strategy is deprecated since version 1.23 and will be removed in Twig 2.0.', \E_USER_DEPRECATED);
            $this->originalCache = \false;
            $this->cache = new \WPML\Core\Twig\Cache\NullCache();
        } elseif ($cache instanceof \WPML\Core\Twig\Cache\CacheInterface) {
            $this->originalCache = $this->cache = $cache;
        } else {
            throw new \LogicException(\sprintf('Cache can only be a string, false, or a \\Twig\\Cache\\CacheInterface implementation.'));
        }
    }
    /**
     * Gets the cache filename for a given template.
     *
     * @param string $name The template name
     *
     * @return string|false The cache file name or false when caching is disabled
     *
     * @deprecated since 1.22 (to be removed in 2.0)
     */
    public function getCacheFilename($name)
    {
        @\trigger_error(\sprintf('The %s method is deprecated since version 1.22 and will be removed in Twig 2.0.', __METHOD__), \E_USER_DEPRECATED);
        $key = $this->cache->generateKey($name, $this->getTemplateClass($name));
        return !$key ? \false : $key;
    }
    /**
     * Gets the template class associated with the given string.
     *
     * The generated template class is based on the following parameters:
     *
     *  * The cache key for the given template;
     *  * The currently enabled extensions;
     *  * Whether the Twig C extension is available or not;
     *  * PHP version;
     *  * Twig version;
     *  * Options with what environment was created.
     *
     * @param string   $name  The name for which to calculate the template class name
     * @param int|null $index The index if it is an embedded template
     *
     * @return string The template class name
     */
    public function getTemplateClass($name, $index = null)
    {
        $key = $this->getLoader()->getCacheKey($name) . $this->optionsHash;
        return $this->templateClassPrefix . \hash('sha256', $key) . (null === $index ? '' : '___' . $index);
    }
    /**
     * Gets the template class prefix.
     *
     * @return string The template class prefix
     *
     * @deprecated since 1.22 (to be removed in 2.0)
     */
    public function getTemplateClassPrefix()
    {
        @\trigger_error(\sprintf('The %s method is deprecated since version 1.22 and will be removed in Twig 2.0.', __METHOD__), \E_USER_DEPRECATED);
        return $this->templateClassPrefix;
    }
    /**
     * Renders a template.
     *
     * @param string|TemplateWrapper $name    The template name
     * @param array                  $context An array of parameters to pass to the template
     *
     * @return string The rendered template
     *
     * @throws LoaderError  When the template cannot be found
     * @throws SyntaxError  When an error occurred during compilation
     * @throws RuntimeError When an error occurred during rendering
     */
    public function render($name, array $context = [])
    {
        return $this->load($name)->render($context);
    }
    /**
     * Displays a template.
     *
     * @param string|TemplateWrapper $name    The template name
     * @param array                  $context An array of parameters to pass to the template
     *
     * @throws LoaderError  When the template cannot be found
     * @throws SyntaxError  When an error occurred during compilation
     * @throws RuntimeError When an error occurred during rendering
     */
    public function display($name, array $context = [])
    {
        $this->load($name)->display($context);
    }
    /**
     * Loads a template.
     *
     * @param string|TemplateWrapper|\Twig\Template $name The template name
     *
     * @throws LoaderError  When the template cannot be found
     * @throws RuntimeError When a previously generated cache is corrupted
     * @throws SyntaxError  When an error occurred during compilation
     *
     * @return TemplateWrapper
     */
    public function load($name)
    {
        if ($name instanceof \WPML\Core\Twig\TemplateWrapper) {
            return $name;
        }
        if ($name instanceof \WPML\Core\Twig\Template) {
            return new \WPML\Core\Twig\TemplateWrapper($this, $name);
        }
        return new \WPML\Core\Twig\TemplateWrapper($this, $this->loadTemplate($name));
    }
    /**
     * Loads a template internal representation.
     *
     * This method is for internal use only and should never be called
     * directly.
     *
     * @param string $name  The template name
     * @param int    $index The index if it is an embedded template
     *
     * @return \Twig_TemplateInterface A template instance representing the given template name
     *
     * @throws LoaderError  When the template cannot be found
     * @throws RuntimeError When a previously generated cache is corrupted
     * @throws SyntaxError  When an error occurred during compilation
     *
     * @internal
     */
    public function loadTemplate($name, $index = null)
    {
        return $this->loadClass($this->getTemplateClass($name), $name, $index);
    }
    /**
     * @internal
     */
    public function loadClass($cls, $name, $index = null)
    {
        $mainCls = $cls;
        if (null !== $index) {
            $cls .= '___' . $index;
        }
        if (isset($this->loadedTemplates[$cls])) {
            return $this->loadedTemplates[$cls];
        }
        if (!\class_exists($cls, \false)) {
            if ($this->bcGetCacheFilename) {
                $key = $this->getCacheFilename($name);
            } else {
                $key = $this->cache->generateKey($name, $mainCls);
            }
            if (!$this->isAutoReload() || $this->isTemplateFresh($name, $this->cache->getTimestamp($key))) {
                $this->cache->load($key);
            }
            $source = null;
            if (!\class_exists($cls, \false)) {
                $loader = $this->getLoader();
                if (!$loader instanceof \WPML\Core\Twig\Loader\SourceContextLoaderInterface) {
                    $source = new \WPML\Core\Twig\Source($loader->getSource($name), $name);
                } else {
                    $source = $loader->getSourceContext($name);
                }
                $content = $this->compileSource($source);
                if ($this->bcWriteCacheFile) {
                    $this->writeCacheFile($key, $content);
                } else {
                    $this->cache->write($key, $content);
                    $this->cache->load($key);
                }
                if (!\class_exists($mainCls, \false)) {
                    /* Last line of defense if either $this->bcWriteCacheFile was used,
                     * $this->cache is implemented as a no-op or we have a race condition
                     * where the cache was cleared between the above calls to write to and load from
                     * the cache.
                     */
                    eval('?>' . $content);
                }
            }
            if (!\class_exists($cls, \false)) {
                throw new \WPML\Core\Twig\Error\RuntimeError(\sprintf('Failed to load Twig template "%s", index "%s": cache might be corrupted.', $name, $index), -1, $source);
            }
        }
        if (!$this->runtimeInitialized) {
            $this->initRuntime();
        }
        return $this->loadedTemplates[$cls] = new $cls($this);
    }
    /**
     * Creates a template from source.
     *
     * This method should not be used as a generic way to load templates.
     *
     * @param string $template The template source
     * @param string $name     An optional name of the template to be used in error messages
     *
     * @return TemplateWrapper A template instance representing the given template name
     *
     * @throws LoaderError When the template cannot be found
     * @throws SyntaxError When an error occurred during compilation
     */
    public function createTemplate($template, $name = null)
    {
        $hash = \hash('sha256', $template, \false);
        if (null !== $name) {
            $name = \sprintf('%s (string template %s)', $name, $hash);
        } else {
            $name = \sprintf('__string_template__%s', $hash);
        }
        $loader = new \WPML\Core\Twig\Loader\ChainLoader([new \WPML\Core\Twig\Loader\ArrayLoader([$name => $template]), $current = $this->getLoader()]);
        $this->setLoader($loader);
        try {
            $template = new \WPML\Core\Twig\TemplateWrapper($this, $this->loadTemplate($name));
        } catch (\Exception $e) {
            $this->setLoader($current);
            throw $e;
        } catch (\Throwable $e) {
            $this->setLoader($current);
            throw $e;
        }
        $this->setLoader($current);
        return $template;
    }
    /**
     * Returns true if the template is still fresh.
     *
     * Besides checking the loader for freshness information,
     * this method also checks if the enabled extensions have
     * not changed.
     *
     * @param string $name The template name
     * @param int    $time The last modification time of the cached template
     *
     * @return bool true if the template is fresh, false otherwise
     */
    public function isTemplateFresh($name, $time)
    {
        if (0 === $this->lastModifiedExtension) {
            foreach ($this->extensions as $extension) {
                $r = new \ReflectionObject($extension);
                if (\file_exists($r->getFileName()) && ($extensionTime = \filemtime($r->getFileName())) > $this->lastModifiedExtension) {
                    $this->lastModifiedExtension = $extensionTime;
                }
            }
        }
        return $this->lastModifiedExtension <= $time && $this->getLoader()->isFresh($name, $time);
    }
    /**
     * Tries to load a template consecutively from an array.
     *
     * Similar to load() but it also accepts instances of \Twig\Template and
     * \Twig\TemplateWrapper, and an array of templates where each is tried to be loaded.
     *
     * @param string|Template|\Twig\TemplateWrapper|array $names A template or an array of templates to try consecutively
     *
     * @return TemplateWrapper|Template
     *
     * @throws LoaderError When none of the templates can be found
     * @throws SyntaxError When an error occurred during compilation
     */
    public function resolveTemplate($names)
    {
        if (!\is_array($names)) {
            $names = [$names];
        }
        foreach ($names as $name) {
            if ($name instanceof \WPML\Core\Twig\Template) {
                return $name;
            }
            if ($name instanceof \WPML\Core\Twig\TemplateWrapper) {
                return $name;
            }
            try {
                return $this->loadTemplate($name);
            } catch (\WPML\Core\Twig\Error\LoaderError $e) {
                if (1 === \count($names)) {
                    throw $e;
                }
            }
        }
        throw new \WPML\Core\Twig\Error\LoaderError(\sprintf('Unable to find one of the following templates: "%s".', \implode('", "', $names)));
    }
    /**
     * Clears the internal template cache.
     *
     * @deprecated since 1.18.3 (to be removed in 2.0)
     */
    public function clearTemplateCache()
    {
        @\trigger_error(\sprintf('The %s method is deprecated since version 1.18.3 and will be removed in Twig 2.0.', __METHOD__), \E_USER_DEPRECATED);
        $this->loadedTemplates = [];
    }
    /**
     * Clears the template cache files on the filesystem.
     *
     * @deprecated since 1.22 (to be removed in 2.0)
     */
    public function clearCacheFiles()
    {
        @\trigger_error(\sprintf('The %s method is deprecated since version 1.22 and will be removed in Twig 2.0.', __METHOD__), \E_USER_DEPRECATED);
        if (\is_string($this->originalCache)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->originalCache), \RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
                if ($file->isFile()) {
                    @\unlink($file->getPathname());
                }
            }
        }
    }
    /**
     * Gets the Lexer instance.
     *
     * @return \Twig_LexerInterface
     *
     * @deprecated since 1.25 (to be removed in 2.0)
     */
    public function getLexer()
    {
        @\trigger_error(\sprintf('The %s() method is deprecated since version 1.25 and will be removed in 2.0.', __FUNCTION__), \E_USER_DEPRECATED);
        if (null === $this->lexer) {
            $this->lexer = new \WPML\Core\Twig\Lexer($this);
        }
        return $this->lexer;
    }
    public function setLexer(\WPML\Core\Twig_LexerInterface $lexer)
    {
        $this->lexer = $lexer;
    }
    /**
     * Tokenizes a source code.
     *
     * @param string|Source $source The template source code
     * @param string        $name   The template name (deprecated)
     *
     * @return TokenStream
     *
     * @throws SyntaxError When the code is syntactically wrong
     */
    public function tokenize($source, $name = null)
    {
        if (!$source instanceof \WPML\Core\Twig\Source) {
            @\trigger_error(\sprintf('Passing a string as the $source argument of %s() is deprecated since version 1.27. Pass a Twig\\Source instance instead.', __METHOD__), \E_USER_DEPRECATED);
            $source = new \WPML\Core\Twig\Source($source, $name);
        }
        if (null === $this->lexer) {
            $this->lexer = new \WPML\Core\Twig\Lexer($this);
        }
        return $this->lexer->tokenize($source);
    }
    /**
     * Gets the Parser instance.
     *
     * @return \Twig_ParserInterface
     *
     * @deprecated since 1.25 (to be removed in 2.0)
     */
    public function getParser()
    {
        @\trigger_error(\sprintf('The %s() method is deprecated since version 1.25 and will be removed in 2.0.', __FUNCTION__), \E_USER_DEPRECATED);
        if (null === $this->parser) {
            $this->parser = new \WPML\Core\Twig\Parser($this);
        }
        return $this->parser;
    }
    public function setParser(\WPML\Core\Twig_ParserInterface $parser)
    {
        $this->parser = $parser;
    }
    /**
     * Converts a token stream to a node tree.
     *
     * @return ModuleNode
     *
     * @throws SyntaxError When the token stream is syntactically or semantically wrong
     */
    public function parse(\WPML\Core\Twig\TokenStream $stream)
    {
        if (null === $this->parser) {
            $this->parser = new \WPML\Core\Twig\Parser($this);
        }
        return $this->parser->parse($stream);
    }
    /**
     * Gets the Compiler instance.
     *
     * @return \Twig_CompilerInterface
     *
     * @deprecated since 1.25 (to be removed in 2.0)
     */
    public function getCompiler()
    {
        @\trigger_error(\sprintf('The %s() method is deprecated since version 1.25 and will be removed in 2.0.', __FUNCTION__), \E_USER_DEPRECATED);
        if (null === $this->compiler) {
            $this->compiler = new \WPML\Core\Twig\Compiler($this);
        }
        return $this->compiler;
    }
    public function setCompiler(\WPML\Core\Twig_CompilerInterface $compiler)
    {
        $this->compiler = $compiler;
    }
    /**
     * Compiles a node and returns the PHP code.
     *
     * @return string The compiled PHP source code
     */
    public function compile(\WPML\Core\Twig_NodeInterface $node)
    {
        if (null === $this->compiler) {
            $this->compiler = new \WPML\Core\Twig\Compiler($this);
        }
        return $this->compiler->compile($node)->getSource();
    }
    /**
     * Compiles a template source code.
     *
     * @param string|Source $source The template source code
     * @param string        $name   The template name (deprecated)
     *
     * @return string The compiled PHP source code
     *
     * @throws SyntaxError When there was an error during tokenizing, parsing or compiling
     */
    public function compileSource($source, $name = null)
    {
        if (!$source instanceof \WPML\Core\Twig\Source) {
            @\trigger_error(\sprintf('Passing a string as the $source argument of %s() is deprecated since version 1.27. Pass a Twig\\Source instance instead.', __METHOD__), \E_USER_DEPRECATED);
            $source = new \WPML\Core\Twig\Source($source, $name);
        }
        try {
            return $this->compile($this->parse($this->tokenize($source)));
        } catch (\WPML\Core\Twig\Error\Error $e) {
            $e->setSourceContext($source);
            throw $e;
        } catch (\Exception $e) {
            throw new \WPML\Core\Twig\Error\SyntaxError(\sprintf('An exception has been thrown during the compilation of a template ("%s").', $e->getMessage()), -1, $source, $e);
        }
    }
    public function setLoader(\WPML\Core\Twig\Loader\LoaderInterface $loader)
    {
        if (!$loader instanceof \WPML\Core\Twig\Loader\SourceContextLoaderInterface && 0 !== \strpos(\get_class($loader), 'Mock_')) {
            @\trigger_error(\sprintf('Twig loader "%s" should implement Twig\\Loader\\SourceContextLoaderInterface since version 1.27.', \get_class($loader)), \E_USER_DEPRECATED);
        }
        $this->loader = $loader;
    }
    /**
     * Gets the Loader instance.
     *
     * @return LoaderInterface
     */
    public function getLoader()
    {
        if (null === $this->loader) {
            throw new \LogicException('You must set a loader first.');
        }
        return $this->loader;
    }
    /**
     * Sets the default template charset.
     *
     * @param string $charset The default charset
     */
    public function setCharset($charset)
    {
        $this->charset = \strtoupper($charset);
    }
    /**
     * Gets the default template charset.
     *
     * @return string The default charset
     */
    public function getCharset()
    {
        return $this->charset;
    }
    /**
     * Initializes the runtime environment.
     *
     * @deprecated since 1.23 (to be removed in 2.0)
     */
    public function initRuntime()
    {
        $this->runtimeInitialized = \true;
        foreach ($this->getExtensions() as $name => $extension) {
            if (!$extension instanceof \WPML\Core\Twig\Extension\InitRuntimeInterface) {
                $m = new \ReflectionMethod($extension, 'initRuntime');
                $parentClass = $m->getDeclaringClass()->getName();
                if ('Twig_Extension' !== $parentClass && 'WPML\\Core\\Twig\\Extension\\AbstractExtension' !== $parentClass) {
                    @\trigger_error(\sprintf('Defining the initRuntime() method in the "%s" extension is deprecated since version 1.23. Use the `needs_environment` option to get the \\Twig_Environment instance in filters, functions, or tests; or explicitly implement Twig\\Extension\\InitRuntimeInterface if needed (not recommended).', $name), \E_USER_DEPRECATED);
                }
            }
            $extension->initRuntime($this);
        }
    }
    /**
     * Returns true if the given extension is registered.
     *
     * @param string $class The extension class name
     *
     * @return bool Whether the extension is registered or not
     */
    public function hasExtension($class)
    {
        $class = \ltrim($class, '\\');
        if (!isset($this->extensionsByClass[$class]) && \class_exists($class, \false)) {
            // For BC/FC with namespaced aliases
            $class = new \ReflectionClass($class);
            $class = $class->name;
        }
        if (isset($this->extensions[$class])) {
            if ($class !== \get_class($this->extensions[$class])) {
                @\trigger_error(\sprintf('Referencing the "%s" extension by its name (defined by getName()) is deprecated since 1.26 and will be removed in Twig 2.0. Use the Fully Qualified Extension Class Name instead.', $class), \E_USER_DEPRECATED);
            }
            return \true;
        }
        return isset($this->extensionsByClass[$class]);
    }
    /**
     * Adds a runtime loader.
     */
    public function addRuntimeLoader(\WPML\Core\Twig\RuntimeLoader\RuntimeLoaderInterface $loader)
    {
        $this->runtimeLoaders[] = $loader;
    }
    /**
     * Gets an extension by class name.
     *
     * @param string $class The extension class name
     *
     * @return ExtensionInterface
     */
    public function getExtension($class)
    {
        $class = \ltrim($class, '\\');
        if (!isset($this->extensionsByClass[$class]) && \class_exists($class, \false)) {
            // For BC/FC with namespaced aliases
            $class = new \ReflectionClass($class);
            $class = $class->name;
        }
        if (isset($this->extensions[$class])) {
            if ($class !== \get_class($this->extensions[$class])) {
                @\trigger_error(\sprintf('Referencing the "%s" extension by its name (defined by getName()) is deprecated since 1.26 and will be removed in Twig 2.0. Use the Fully Qualified Extension Class Name instead.', $class), \E_USER_DEPRECATED);
            }
            return $this->extensions[$class];
        }
        if (!isset($this->extensionsByClass[$class])) {
            throw new \WPML\Core\Twig\Error\RuntimeError(\sprintf('The "%s" extension is not enabled.', $class));
        }
        return $this->extensionsByClass[$class];
    }
    /**
     * Returns the runtime implementation of a Twig element (filter/function/test).
     *
     * @param string $class A runtime class name
     *
     * @return object The runtime implementation
     *
     * @throws RuntimeError When the template cannot be found
     */
    public function getRuntime($class)
    {
        if (isset($this->runtimes[$class])) {
            return $this->runtimes[$class];
        }
        foreach ($this->runtimeLoaders as $loader) {
            if (null !== ($runtime = $loader->load($class))) {
                return $this->runtimes[$class] = $runtime;
            }
        }
        throw new \WPML\Core\Twig\Error\RuntimeError(\sprintf('Unable to load the "%s" runtime.', $class));
    }
    public function addExtension(\WPML\Core\Twig\Extension\ExtensionInterface $extension)
    {
        if ($this->extensionInitialized) {
            throw new \LogicException(\sprintf('Unable to register extension "%s" as extensions have already been initialized.', $extension->getName()));
        }
        $class = \get_class($extension);
        if ($class !== $extension->getName()) {
            if (isset($this->extensions[$extension->getName()])) {
                unset($this->extensions[$extension->getName()], $this->extensionsByClass[$class]);
                @\trigger_error(\sprintf('The possibility to register the same extension twice ("%s") is deprecated since version 1.23 and will be removed in Twig 2.0. Use proper PHP inheritance instead.', $extension->getName()), \E_USER_DEPRECATED);
            }
        }
        $this->lastModifiedExtension = 0;
        $this->extensionsByClass[$class] = $extension;
        $this->extensions[$extension->getName()] = $extension;
        $this->updateOptionsHash();
    }
    /**
     * Removes an extension by name.
     *
     * This method is deprecated and you should not use it.
     *
     * @param string $name The extension name
     *
     * @deprecated since 1.12 (to be removed in 2.0)
     */
    public function removeExtension($name)
    {
        @\trigger_error(\sprintf('The %s method is deprecated since version 1.12 and will be removed in Twig 2.0.', __METHOD__), \E_USER_DEPRECATED);
        if ($this->extensionInitialized) {
            throw new \LogicException(\sprintf('Unable to remove extension "%s" as extensions have already been initialized.', $name));
        }
        $class = \ltrim($name, '\\');
        if (!isset($this->extensionsByClass[$class]) && \class_exists($class, \false)) {
            // For BC/FC with namespaced aliases
            $class = new \ReflectionClass($class);
            $class = $class->name;
        }
        if (isset($this->extensions[$class])) {
            if ($class !== \get_class($this->extensions[$class])) {
                @\trigger_error(\sprintf('Referencing the "%s" extension by its name (defined by getName()) is deprecated since 1.26 and will be removed in Twig 2.0. Use the Fully Qualified Extension Class Name instead.', $class), \E_USER_DEPRECATED);
            }
            unset($this->extensions[$class]);
        }
        unset($this->extensions[$class]);
        $this->updateOptionsHash();
    }
    /**
     * Registers an array of extensions.
     *
     * @param array $extensions An array of extensions
     */
    public function setExtensions(array $extensions)
    {
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }
    }
    /**
     * Returns all registered extensions.
     *
     * @return ExtensionInterface[] An array of extensions (keys are for internal usage only and should not be relied on)
     */
    public function getExtensions()
    {
        return $this->extensions;
    }
    public function addTokenParser(\WPML\Core\Twig\TokenParser\TokenParserInterface $parser)
    {
        if ($this->extensionInitialized) {
            throw new \LogicException('Unable to add a token parser as extensions have already been initialized.');
        }
        $this->staging->addTokenParser($parser);
    }
    /**
     * Gets the registered Token Parsers.
     *
     * @return \Twig_TokenParserBrokerInterface
     *
     * @internal
     */
    public function getTokenParsers()
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        return $this->parsers;
    }
    /**
     * Gets registered tags.
     *
     * Be warned that this method cannot return tags defined by \Twig_TokenParserBrokerInterface classes.
     *
     * @return TokenParserInterface[]
     *
     * @internal
     */
    public function getTags()
    {
        $tags = [];
        foreach ($this->getTokenParsers()->getParsers() as $parser) {
            if ($parser instanceof \WPML\Core\Twig\TokenParser\TokenParserInterface) {
                $tags[$parser->getTag()] = $parser;
            }
        }
        return $tags;
    }
    public function addNodeVisitor(\WPML\Core\Twig\NodeVisitor\NodeVisitorInterface $visitor)
    {
        if ($this->extensionInitialized) {
            throw new \LogicException('Unable to add a node visitor as extensions have already been initialized.');
        }
        $this->staging->addNodeVisitor($visitor);
    }
    /**
     * Gets the registered Node Visitors.
     *
     * @return NodeVisitorInterface[]
     *
     * @internal
     */
    public function getNodeVisitors()
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        return $this->visitors;
    }
    /**
     * Registers a Filter.
     *
     * @param string|TwigFilter                $name   The filter name or a \Twig_SimpleFilter instance
     * @param \Twig_FilterInterface|TwigFilter $filter
     */
    public function addFilter($name, $filter = null)
    {
        if (!$name instanceof \WPML\Core\Twig\TwigFilter && !($filter instanceof \WPML\Core\Twig\TwigFilter || $filter instanceof \WPML\Core\Twig_FilterInterface)) {
            throw new \LogicException('A filter must be an instance of \\Twig_FilterInterface or \\Twig_SimpleFilter.');
        }
        if ($name instanceof \WPML\Core\Twig\TwigFilter) {
            $filter = $name;
            $name = $filter->getName();
        } else {
            @\trigger_error(\sprintf('Passing a name as a first argument to the %s method is deprecated since version 1.21. Pass an instance of "Twig_SimpleFilter" instead when defining filter "%s".', __METHOD__, $name), \E_USER_DEPRECATED);
        }
        if ($this->extensionInitialized) {
            throw new \LogicException(\sprintf('Unable to add filter "%s" as extensions have already been initialized.', $name));
        }
        $this->staging->addFilter($name, $filter);
    }
    /**
     * Get a filter by name.
     *
     * Subclasses may override this method and load filters differently;
     * so no list of filters is available.
     *
     * @param string $name The filter name
     *
     * @return \Twig_Filter|false
     *
     * @internal
     */
    public function getFilter($name)
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        if (isset($this->filters[$name])) {
            return $this->filters[$name];
        }
        foreach ($this->filters as $pattern => $filter) {
            $pattern = \str_replace('\\*', '(.*?)', \preg_quote($pattern, '#'), $count);
            if ($count) {
                if (\preg_match('#^' . $pattern . '$#', $name, $matches)) {
                    \array_shift($matches);
                    $filter->setArguments($matches);
                    return $filter;
                }
            }
        }
        foreach ($this->filterCallbacks as $callback) {
            if (\false !== ($filter = \call_user_func($callback, $name))) {
                return $filter;
            }
        }
        return \false;
    }
    public function registerUndefinedFilterCallback($callable)
    {
        $this->filterCallbacks[] = $callable;
    }
    /**
     * Gets the registered Filters.
     *
     * Be warned that this method cannot return filters defined with registerUndefinedFilterCallback.
     *
     * @return \Twig_FilterInterface[]
     *
     * @see registerUndefinedFilterCallback
     *
     * @internal
     */
    public function getFilters()
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        return $this->filters;
    }
    /**
     * Registers a Test.
     *
     * @param string|TwigTest              $name The test name or a \Twig_SimpleTest instance
     * @param \Twig_TestInterface|TwigTest $test A \Twig_TestInterface instance or a \Twig_SimpleTest instance
     */
    public function addTest($name, $test = null)
    {
        if (!$name instanceof \WPML\Core\Twig\TwigTest && !($test instanceof \WPML\Core\Twig\TwigTest || $test instanceof \WPML\Core\Twig_TestInterface)) {
            throw new \LogicException('A test must be an instance of \\Twig_TestInterface or \\Twig_SimpleTest.');
        }
        if ($name instanceof \WPML\Core\Twig\TwigTest) {
            $test = $name;
            $name = $test->getName();
        } else {
            @\trigger_error(\sprintf('Passing a name as a first argument to the %s method is deprecated since version 1.21. Pass an instance of "Twig_SimpleTest" instead when defining test "%s".', __METHOD__, $name), \E_USER_DEPRECATED);
        }
        if ($this->extensionInitialized) {
            throw new \LogicException(\sprintf('Unable to add test "%s" as extensions have already been initialized.', $name));
        }
        $this->staging->addTest($name, $test);
    }
    /**
     * Gets the registered Tests.
     *
     * @return \Twig_TestInterface[]
     *
     * @internal
     */
    public function getTests()
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        return $this->tests;
    }
    /**
     * Gets a test by name.
     *
     * @param string $name The test name
     *
     * @return \Twig_Test|false
     *
     * @internal
     */
    public function getTest($name)
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        if (isset($this->tests[$name])) {
            return $this->tests[$name];
        }
        foreach ($this->tests as $pattern => $test) {
            $pattern = \str_replace('\\*', '(.*?)', \preg_quote($pattern, '#'), $count);
            if ($count) {
                if (\preg_match('#^' . $pattern . '$#', $name, $matches)) {
                    \array_shift($matches);
                    $test->setArguments($matches);
                    return $test;
                }
            }
        }
        return \false;
    }
    /**
     * Registers a Function.
     *
     * @param string|TwigFunction                  $name     The function name or a \Twig_SimpleFunction instance
     * @param \Twig_FunctionInterface|TwigFunction $function
     */
    public function addFunction($name, $function = null)
    {
        if (!$name instanceof \WPML\Core\Twig\TwigFunction && !($function instanceof \WPML\Core\Twig\TwigFunction || $function instanceof \WPML\Core\Twig_FunctionInterface)) {
            throw new \LogicException('A function must be an instance of \\Twig_FunctionInterface or \\Twig_SimpleFunction.');
        }
        if ($name instanceof \WPML\Core\Twig\TwigFunction) {
            $function = $name;
            $name = $function->getName();
        } else {
            @\trigger_error(\sprintf('Passing a name as a first argument to the %s method is deprecated since version 1.21. Pass an instance of "Twig_SimpleFunction" instead when defining function "%s".', __METHOD__, $name), \E_USER_DEPRECATED);
        }
        if ($this->extensionInitialized) {
            throw new \LogicException(\sprintf('Unable to add function "%s" as extensions have already been initialized.', $name));
        }
        $this->staging->addFunction($name, $function);
    }
    /**
     * Get a function by name.
     *
     * Subclasses may override this method and load functions differently;
     * so no list of functions is available.
     *
     * @param string $name function name
     *
     * @return \Twig_Function|false
     *
     * @internal
     */
    public function getFunction($name)
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        if (isset($this->functions[$name])) {
            return $this->functions[$name];
        }
        foreach ($this->functions as $pattern => $function) {
            $pattern = \str_replace('\\*', '(.*?)', \preg_quote($pattern, '#'), $count);
            if ($count) {
                if (\preg_match('#^' . $pattern . '$#', $name, $matches)) {
                    \array_shift($matches);
                    $function->setArguments($matches);
                    return $function;
                }
            }
        }
        foreach ($this->functionCallbacks as $callback) {
            if (\false !== ($function = \call_user_func($callback, $name))) {
                return $function;
            }
        }
        return \false;
    }
    public function registerUndefinedFunctionCallback($callable)
    {
        $this->functionCallbacks[] = $callable;
    }
    /**
     * Gets registered functions.
     *
     * Be warned that this method cannot return functions defined with registerUndefinedFunctionCallback.
     *
     * @return \Twig_FunctionInterface[]
     *
     * @see registerUndefinedFunctionCallback
     *
     * @internal
     */
    public function getFunctions()
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        return $this->functions;
    }
    /**
     * Registers a Global.
     *
     * New globals can be added before compiling or rendering a template;
     * but after, you can only update existing globals.
     *
     * @param string $name  The global name
     * @param mixed  $value The global value
     */
    public function addGlobal($name, $value)
    {
        if ($this->extensionInitialized || $this->runtimeInitialized) {
            if (null === $this->globals) {
                $this->globals = $this->initGlobals();
            }
            if (!\array_key_exists($name, $this->globals)) {
                // The deprecation notice must be turned into the following exception in Twig 2.0
                @\trigger_error(\sprintf('Registering global variable "%s" at runtime or when the extensions have already been initialized is deprecated since version 1.21.', $name), \E_USER_DEPRECATED);
                //throw new \LogicException(sprintf('Unable to add global "%s" as the runtime or the extensions have already been initialized.', $name));
            }
        }
        if ($this->extensionInitialized || $this->runtimeInitialized) {
            // update the value
            $this->globals[$name] = $value;
        } else {
            $this->staging->addGlobal($name, $value);
        }
    }
    /**
     * Gets the registered Globals.
     *
     * @return array An array of globals
     *
     * @internal
     */
    public function getGlobals()
    {
        if (!$this->runtimeInitialized && !$this->extensionInitialized) {
            return $this->initGlobals();
        }
        if (null === $this->globals) {
            $this->globals = $this->initGlobals();
        }
        return $this->globals;
    }
    /**
     * Merges a context with the defined globals.
     *
     * @param array $context An array representing the context
     *
     * @return array The context merged with the globals
     */
    public function mergeGlobals(array $context)
    {
        // we don't use array_merge as the context being generally
        // bigger than globals, this code is faster.
        foreach ($this->getGlobals() as $key => $value) {
            if (!\array_key_exists($key, $context)) {
                $context[$key] = $value;
            }
        }
        return $context;
    }
    /**
     * Gets the registered unary Operators.
     *
     * @return array An array of unary operators
     *
     * @internal
     */
    public function getUnaryOperators()
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        return $this->unaryOperators;
    }
    /**
     * Gets the registered binary Operators.
     *
     * @return array An array of binary operators
     *
     * @internal
     */
    public function getBinaryOperators()
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        return $this->binaryOperators;
    }
    /**
     * @deprecated since 1.23 (to be removed in 2.0)
     */
    public function computeAlternatives($name, $items)
    {
        @\trigger_error(\sprintf('The %s method is deprecated since version 1.23 and will be removed in Twig 2.0.', __METHOD__), \E_USER_DEPRECATED);
        return \WPML\Core\Twig\Error\SyntaxError::computeAlternatives($name, $items);
    }
    /**
     * @internal
     */
    protected function initGlobals()
    {
        $globals = [];
        foreach ($this->extensions as $name => $extension) {
            if (!$extension instanceof \WPML\Core\Twig\Extension\GlobalsInterface) {
                $m = new \ReflectionMethod($extension, 'getGlobals');
                $parentClass = $m->getDeclaringClass()->getName();
                if ('Twig_Extension' !== $parentClass && 'WPML\\Core\\Twig\\Extension\\AbstractExtension' !== $parentClass) {
                    @\trigger_error(\sprintf('Defining the getGlobals() method in the "%s" extension without explicitly implementing Twig\\Extension\\GlobalsInterface is deprecated since version 1.23.', $name), \E_USER_DEPRECATED);
                }
            }
            $extGlob = $extension->getGlobals();
            if (!\is_array($extGlob)) {
                throw new \UnexpectedValueException(\sprintf('"%s::getGlobals()" must return an array of globals.', \get_class($extension)));
            }
            $globals[] = $extGlob;
        }
        $globals[] = $this->staging->getGlobals();
        return \call_user_func_array('array_merge', $globals);
    }
    /**
     * @internal
     */
    protected function initExtensions()
    {
        if ($this->extensionInitialized) {
            return;
        }
        $this->parsers = new \WPML\Core\Twig_TokenParserBroker([], [], \false);
        $this->filters = [];
        $this->functions = [];
        $this->tests = [];
        $this->visitors = [];
        $this->unaryOperators = [];
        $this->binaryOperators = [];
        foreach ($this->extensions as $extension) {
            $this->initExtension($extension);
        }
        $this->initExtension($this->staging);
        // Done at the end only, so that an exception during initialization does not mark the environment as initialized when catching the exception
        $this->extensionInitialized = \true;
    }
    /**
     * @internal
     */
    protected function initExtension(\WPML\Core\Twig\Extension\ExtensionInterface $extension)
    {
        // filters
        foreach ($extension->getFilters() as $name => $filter) {
            if ($filter instanceof \WPML\Core\Twig\TwigFilter) {
                $name = $filter->getName();
            } else {
                @\trigger_error(\sprintf('Using an instance of "%s" for filter "%s" is deprecated since version 1.21. Use \\Twig_SimpleFilter instead.', \get_class($filter), $name), \E_USER_DEPRECATED);
            }
            $this->filters[$name] = $filter;
        }
        // functions
        foreach ($extension->getFunctions() as $name => $function) {
            if ($function instanceof \WPML\Core\Twig\TwigFunction) {
                $name = $function->getName();
            } else {
                @\trigger_error(\sprintf('Using an instance of "%s" for function "%s" is deprecated since version 1.21. Use \\Twig_SimpleFunction instead.', \get_class($function), $name), \E_USER_DEPRECATED);
            }
            $this->functions[$name] = $function;
        }
        // tests
        foreach ($extension->getTests() as $name => $test) {
            if ($test instanceof \WPML\Core\Twig\TwigTest) {
                $name = $test->getName();
            } else {
                @\trigger_error(\sprintf('Using an instance of "%s" for test "%s" is deprecated since version 1.21. Use \\Twig_SimpleTest instead.', \get_class($test), $name), \E_USER_DEPRECATED);
            }
            $this->tests[$name] = $test;
        }
        // token parsers
        foreach ($extension->getTokenParsers() as $parser) {
            if ($parser instanceof \WPML\Core\Twig\TokenParser\TokenParserInterface) {
                $this->parsers->addTokenParser($parser);
            } elseif ($parser instanceof \WPML\Core\Twig_TokenParserBrokerInterface) {
                @\trigger_error('Registering a \\Twig_TokenParserBrokerInterface instance is deprecated since version 1.21.', \E_USER_DEPRECATED);
                $this->parsers->addTokenParserBroker($parser);
            } else {
                throw new \LogicException('getTokenParsers() must return an array of \\Twig_TokenParserInterface or \\Twig_TokenParserBrokerInterface instances.');
            }
        }
        // node visitors
        foreach ($extension->getNodeVisitors() as $visitor) {
            $this->visitors[] = $visitor;
        }
        // operators
        if ($operators = $extension->getOperators()) {
            if (!\is_array($operators)) {
                throw new \InvalidArgumentException(\sprintf('"%s::getOperators()" must return an array with operators, got "%s".', \get_class($extension), \is_object($operators) ? \get_class($operators) : \gettype($operators) . (\is_resource($operators) ? '' : '#' . $operators)));
            }
            if (2 !== \count($operators)) {
                throw new \InvalidArgumentException(\sprintf('"%s::getOperators()" must return an array of 2 elements, got %d.', \get_class($extension), \count($operators)));
            }
            $this->unaryOperators = \array_merge($this->unaryOperators, $operators[0]);
            $this->binaryOperators = \array_merge($this->binaryOperators, $operators[1]);
        }
    }
    /**
     * @deprecated since 1.22 (to be removed in 2.0)
     */
    protected function writeCacheFile($file, $content)
    {
        $this->cache->write($file, $content);
    }
    private function updateOptionsHash()
    {
        $hashParts = \array_merge(\array_keys($this->extensions), [(int) \function_exists('WPML\\Core\\twig_template_get_attributes'), \PHP_MAJOR_VERSION, \PHP_MINOR_VERSION, self::VERSION, (int) $this->debug, $this->baseTemplateClass, (int) $this->strictVariables]);
        $this->optionsHash = \implode(':', $hashParts);
    }
}
\class_alias('WPML\\Core\\Twig\\Environment', 'WPML\\Core\\Twig_Environment');
