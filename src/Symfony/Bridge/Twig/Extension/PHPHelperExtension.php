<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Twig\Extension;

use Symfony\Bundle\TwigBundle\TokenParser\RenderTokenParser;
use Symfony\Component\Templating\Helper\HelperInterface;

/**
 * A twig extension that wraps a PHP template helper.
 *
 * @author Ethan Resnick <hi@ethanresnick.com>
 */
class PHPHelperExtension extends \Twig_Extension
{
    private $helper;

    private $twigFunctions;


    public function __construct(HelperInterface $helper)
    {
        $this->helper = $helper;
        $name         = $helper->getName();
        
        foreach(get_class_methods($helper) as $method) {
            $this->twigFunctions[$name.'.'.$method] = new \Twig_Function_Method($this, $method);
        }
    }

    public function getFunctions()
    {
        return $this->twigFunctions;
    }

    public function __call($method, $args)
    {
        if(!method_exists($this->helper, $method)) {
            throw new \LogicException(sprintf("The method %s doesn't exist on this class's helper", $method));
        }

        return call_user_func_array(array($this->helper, $method), $args); 
    }

    public function getName()
    {
        return 'phphelper_'.$this->helper->getName();
    }
}