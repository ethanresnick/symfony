<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\TwigBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Adds tagged twig.extension services to twig service
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TwigEnvironmentPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('twig')) {
            return;
        }

        //add php template helpers as twig extensions
        $helperClass = $container->getParameter('twig.extension.phphelper.class');
        foreach($container->findTaggedServiceIds('templating.helper') as $id=>$attributes) {
            if (isset($attributes[0]['alias'])) {
                $definition = new Definition($helperClass);
                $definition->addTag('twig.extension');
                $definition->addArgument(new Reference($id));

                $container->setDefinition('twig.phphelper.'.$attributes[0]['alias'], $definition);
            }
        }

        $definition = $container->getDefinition('twig');

        // Extensions must always be registered before everything else.
        // For instance, global variable definitions must be registered
        // afterward. If not, the globals from the extensions will never
        // be registered.
        $calls = $definition->getMethodCalls();
        $definition->setMethodCalls(array());
        foreach ($container->findTaggedServiceIds('twig.extension') as $id => $attributes) {
            $definition->addMethodCall('addExtension', array(new Reference($id)));
        }
        $definition->setMethodCalls(array_merge($definition->getMethodCalls(), $calls));
    }
}
