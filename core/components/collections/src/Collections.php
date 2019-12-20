<?php
namespace Collections;
use Collections\Model\CollectionResourceTemplate;
use Collections\Model\CollectionSetting;
use Collections\Model\CollectionTemplate;
use MODX\Revolution\modResource;
use MODX\Revolution\modX;

/**
 * The service class for Collections.
 *
 * @package collections
 */
class Collections
{
    /** @var \modX $modx */
    public $modx;
    public $namespace = 'collections';
    /** @var array $config */
    public $config = [];
    /** @var array $chunks */
    public $chunks = [];

    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $this->namespace = $this->getOption('namespace', $config, 'collections');

        $corePath = $this->getOption('core_path', $config, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/collections/');
        $assetsUrl = $this->getOption('assets_url', $config, $this->modx->getOption('assets_url', null, MODX_ASSETS_URL) . 'components/collections/');

        $taggerCorePath = $modx->getOption('tagger.core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/tagger/');

        if (file_exists($taggerCorePath . 'model/tagger/tagger.class.php')) {
            /** @var Tagger $tagger */
            $tagger = $modx->getService(
                'tagger',
                'Tagger',
                $taggerCorePath . 'model/tagger/',
                [
                    'core_path' => $taggerCorePath
                ]
            );
        } else {
            $tagger = null;
        }

        $quipCorePath = $modx->getOption('quip.core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/quip/');

        if (file_exists($quipCorePath . 'model/quip/quip.class.php')) {
            /** @var Quip $quip */
            $quip = $modx->getService(
                'quip',
                'Quip',
                $quipCorePath . 'model/quip/',
                [
                    'core_path' => $quipCorePath
                ]
            );
        } else {
            $quip = null;
        }

        $fredCorePath = $modx->getOption('fred.core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/fred/');

        if (file_exists($fredCorePath . 'model/fred/fred.class.php')) {
            /** @var Fred $fred */
            $fred = $modx->getService(
                'fred',
                'Fred',
                $fredCorePath . 'model/fred/',
                [
                    'core_path' => $fredCorePath
                ]
            );
        } else {
            $fred = null;
        }

        $this->config = array_merge([
            'assets_url' => $assetsUrl,
            'core_path' => $corePath,

            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
            'imagesUrl' => $assetsUrl . 'images/',

            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'chunksPath' => $corePath . 'elements/chunks/',
            'chunkSuffix' => '.chunk.tpl',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'processorsPath' => $corePath . 'processors/',
            'templatesPath' => $corePath . 'templates/',

            'taggerInstalled' => $tagger instanceof Tagger,
            'quipInstalled' => $quip instanceof Quip,
            'fredInstalled' => $fred instanceof Fred,
        ], $config);

        $this->modx->addPackage('collections', $this->config['modelPath']);
        $this->modx->lexicon->load('collections:default');
        $this->modx->lexicon->load('collections:selections');
    }

    /**
     * Get a local configuration option or a namespaced system setting by key.
     *
     * @param string $key The option key to search for.
     * @param array $options An array of options that override local options.
     * @param mixed $default The default value returned if the option is not found locally or as a
     * namespaced system setting; by default this value is null.
     * @return mixed The option value or the default value specified.
     */
    public function getOption($key, $options = [], $default = null)
    {
        $option = $default;
        if (!empty($key) && is_string($key)) {
            if ($options != null && array_key_exists($key, $options)) {
                $option = $options[$key];
            } elseif (array_key_exists($key, $this->config)) {
                $option = $this->config[$key];
            } elseif (array_key_exists("{$this->namespace}.{$key}", $this->modx->config)) {
                $option = $this->modx->getOption("{$this->namespace}.{$key}");
            }
        }
        return $option;
    }

    /**
     * @param modResource $collection
     * @return CollectionTemplate
     */
    public function getCollectionsView($collection)
    {
        $template = null;

        /** @var CollectionSetting $collectionSetting */
        $collectionSetting = $this->modx->getObject(CollectionSetting::class, ['collection' => $collection->id]);
        if ($collectionSetting) {
            if (intval($collectionSetting->template) > 0) {
                $template = $collectionSetting->Template;
            }
        }

        if ($template == null) {
            /** @var CollectionResourceTemplate $resourceTemplate */
            $resourceTemplate = $this->modx->getObject(CollectionResourceTemplate::class, ['resource_template' => $collection->template]);
            if ($resourceTemplate) {
                $template = $resourceTemplate->CollectionTemplate;
            } else {
                $template = $this->modx->getObject(CollectionTemplate::class, ['global_template' => 1]);
            }
        }

        return $template;
    }
}