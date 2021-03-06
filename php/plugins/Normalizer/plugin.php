<?php
/**
 * elFinder Plugin Normalizer
 * 
 * UTF-8 Normalizer of file-name and file-path etc.
 * nfc(NFC): Canonical Decomposition followed by Canonical Composition
 * nfkc(NFKC): Compatibility Decomposition followed by Canonical
 * 
 * This plugin require Class "Normalizer" (PHP 5 >= 5.3.0, PECL intl >= 1.0.0)
 * or PEAR package "I18N_UnicodeNormalizer"
 * 
 * ex. binding, configure on connector options
 *	$opts = array(
 *		'bind' => array(
 *			'mkdir.pre mkfile.pre rename.pre archive.pre' => array(
 *				'Plugin.Normalizer.cmdPreprocess'
 *			),
 *			'upload.presave' => array(
 *				'Plugin.Normalizer.onUpLoadPreSave'
 *			)
 *		),
 *		// global configure (optional)
 *		'plugin' => array(
 *			'Normalizer' => array(
 *				'enable' => true,
 *			)
 *		),
 *		// each volume configure (optional)
 *		'roots' => array(
 *			array(
 *				'driver' => 'LocalFileSystem',
 *				'path'   => '/path/to/files/',
 *				'URL'    => 'http://localhost/to/files/'
 *				'plugin' => array(
 *					'Normalizer' => array(
 *						'enable' => true,
 *						'nfc'    => true,
 *						'nfkc'   => true
 *					)
 *				)
 *			)
 *		)
 *	);
 *
 * @package elfinder
 * @author Naoki Sawada
 * @license New BSD
 */
use dosamigos\transliterator\TransliteratorHelper;

class elFinderPluginNormalizer
{
    private $opts = array();

    public function __construct($opts) {
        $defaults = array(
            'enable' => true, // For control by volume driver
        );

        $this->opts = array_merge($defaults, $opts);
    }

    public function cmdPreprocess($cmd, &$args, $elfinder, $volume) {
        $opts = $this->getOpts($volume);
        if (! $opts['enable']) {
            return false;
        }

        if (isset($args['name'])) {
            $args['name'] = $this->normalize($args['name']);
        }
        return true;
    }

    public function onUpLoadPreSave(&$path, &$name, $src, $elfinder, $volume) {
        $opts = $this->getOpts($volume);
        if (! $opts['enable']) {
            return false;
        }

        if ($path) {
            $path = $this->normalize($path);
        }
        $name = $this->normalize($name);
        return false;
    }

    private function getOpts($volume) {
        $opts = $this->opts;
        if (is_object($volume)) {
            $volOpts = $volume->getOptionsPlugin('Normalizer');
            if (is_array($volOpts)) {
                $opts = array_merge($this->opts, $volOpts);
            }
        }
        return $opts;
    }

    private function normalize($str) {
        return TransliteratorHelper::process($str);
    }
}
