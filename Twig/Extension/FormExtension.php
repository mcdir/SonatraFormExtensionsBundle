<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Twig\Extension;

use Symfony\Component\Form\FormView;
use Symfony\Bridge\Twig\Form\TwigRendererInterface;

/**
 * FormExtension extends Twig with form capabilities.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class FormExtension extends \Twig_Extension
{
    /**
     * @var TwigRendererInterface
     */
    public $renderer;

    /**
     * @var array
     */
    protected $globalJavascripts;

    /**
     * @var array
     */
    protected $globalStylesheets;

    /**
     * Constructor.
     *
     * @param TwigRendererInterface $renderer
     */
    public function __construct(TwigRendererInterface $renderer)
    {
        $this->renderer = $renderer;
        $this->globalJavascripts = array();
        $this->globalStylesheets = array();
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->renderer->setEnvironment($environment);
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('form_javascript',         'compile', array('node_class' => 'Symfony\Bridge\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('form_stylesheet',         'compile', array('node_class' => 'Symfony\Bridge\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('form_assets_widget',      'compile', array('node_class' => 'Sonatra\Bundle\FormExtensionsBundle\Twig\Node\SearchAndRenderBlockAssetsNode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('form_global_javascripts', array($this, 'renderGlobalJavascripts'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('form_global_stylesheets', array($this, 'renderGlobalStylesheets'), array('is_safe' => array('html'))),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonatra_form_extensions';
    }

    /**
     * Searches and renders a block and assets (javascript and stylesheet) for
     * a given name suffix.
     *
     * @param FormView $view            The view for which to render the block
     * @param string   $blockNameSuffix The suffix of the block name
     * @param array    $variables       The variables to pass to the template
     *
     * @return string The HTML markup
     */
    public function searchAndRenderBlockAssets(FormView $view, $blockNameSuffix, array $variables = array())
    {
        $output = $this->renderer->searchAndRenderBlock($view, $blockNameSuffix, $variables);
        $asset = array('view' => $view, 'variables' => $variables);
        $this->globalJavascripts[] = $asset;
        $this->globalStylesheets[] = $asset;

        return $output;
    }

    /**
     * Render global form twig of javascript.
     *
     * @return string
     */
    public function renderGlobalJavascripts()
    {
        $output = '';

        foreach ($this->globalJavascripts as $js) {
            $output .= $this->renderer->searchAndRenderBlock($js['view'], 'javascript', $js['variables']) . "\n";
        }

        $this->globalJavascripts = array();

        return $output;
    }

    /**
     * Render global form twig of javascript.
     *
     * @return string
     */
    public function renderGlobalStyleSheets()
    {
        $output = '';

        foreach ($this->globalStylesheets as $js) {
            $output .= $this->renderer->searchAndRenderBlock($js['view'], 'stylesheet', $js['variables']) . "\n";
        }

        $this->globalStylesheets = array();

        return $output;
    }
}
