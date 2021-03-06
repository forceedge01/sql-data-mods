<?php

namespace Genesis\SQLExtensionWrapper\Extension\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Genesis\SQLExtensionWrapper\BaseProvider;
use Genesis\SQLExtensionWrapper\Command\Generate;
use Genesis\SQLExtensionWrapper\Command\DebugSQLCli;
use Genesis\SQLExtensionWrapper\Extension\Initializer\Initializer;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Extension class.
 */
class Extension implements ExtensionInterface
{
    const CONTEXT_INITIALISER = 'genesis.sqlapiwrapper.context_initialiser';

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * Create definition object to handle in the context?
     */
    public function process(ContainerBuilder $container)
    {
        return;
    }

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'GenesisSQLApiWrapperExtension';
    }

    /**
     * Initializes other extensions.
     *
     * This method is called immediately after all extensions are activated but
     * before any extension `configure()` method is called. This allows extensions
     * to hook into the configuration of other extensions providing such an
     * extension point.
     *
     */
    public function initialize(ExtensionManager $extensionManager)
    {
        return;
    }

    /**
     * Setups configuration for the extension.
     *
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->arrayNode('FailAid')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('output')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('enabled')->defaultValue(true)->end()
                                ->scalarNode('insert')->defaultValue(true)->end()
                                ->scalarNode('select')->defaultValue(true)->end()
                                ->scalarNode('update')->defaultValue(true)->end()
                                ->scalarNode('delete')->defaultValue(true)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('keyStore')->defaultValue('\Genesis\SQLExtension\Context\LocalKeyStore')->end()
                ->arrayNode('connection')
                    ->setDeprecated('Use "connections" configuration instead.')
                    ->children()
                        ->scalarNode('host')->defaultNull()->end()
                        ->scalarNode('engine')->isRequired()->end()
                        ->scalarNode('dbname')->defaultNull()->end()
                        ->scalarNode('port')->defaultNull()->end()
                        ->scalarNode('username')->defaultNull()->end()
                        ->scalarNode('password')->defaultNull()->end()
                        ->scalarNode('schema')->defaultNull()->end()
                        ->scalarNode('prefix')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('connections')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('host')->defaultNull()->end()
                            ->scalarNode('engine')->isRequired()->end()
                            ->scalarNode('dbname')->defaultNull()->end()
                            ->scalarNode('port')->defaultNull()->end()
                            ->scalarNode('username')->defaultNull()->end()
                            ->scalarNode('password')->defaultNull()->end()
                            ->scalarNode('schema')->defaultNull()->end()
                            ->scalarNode('prefix')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('dataModMapping')
                    ->ignoreExtraKeys(false)
                ->end()
                ->arrayNode('domainModMapping')
                    ->ignoreExtraKeys(false)
                ->end()
            ->end()
        ->end();
    }

    /**
     * Loads extension services into temporary container.
     *
     */
    public function load(ContainerBuilder $container, array $config)
    {
        // Merge into connections configuration.
        if (!empty($config['connection'])) {
            $config['connections'][0] = $config['connection'];
            unset($config['connection']);
        }

        if (empty($config['connections'])) {
            $config['connections'] = [];
        }

        $config = $this->resolveEnvVars($config);

        $container->setParameter('genesis.sqlapiwrapper.config.connections', $config['connections']);

        if (!isset($config['dataModMapping'])) {
            $config['dataModMapping'] = [];
        }
        $container->setParameter('genesis.sqlapiwrapper.config.datamodmapping', $config['dataModMapping']);

        if (!isset($config['domainModMapping'])) {
            $config['domainModMapping'] = [];
        }
        $container->setParameter('genesis.sqlapiwrapper.config.domainmodmapping', $config['domainModMapping']);
        $container->setParameter('genesis.sqlapiwrapper.config.failAidOptions', $config['FailAid']);

        $definition = new Definition(Initializer::class, [
            '%genesis.sqlapiwrapper.config.datamodmapping%',
            '%genesis.sqlapiwrapper.config.domainmodmapping%',
            '%genesis.sqlapiwrapper.config.failAidOptions%'
        ]);
        $definition->addTag(ContextExtension::INITIALIZER_TAG);
        $container->setDefinition(self::CONTEXT_INITIALISER, $definition);
        BaseProvider::setKeyStore($config['keyStore']);
        BaseProvider::setCredentials($config['connections']);
        $this->addDebugCommand($container);
        $this->addGenerateCommand($container);
    }

    private function addGenerateCommand($container)
    {
        $definition = new Definition(
            Generate::class,
            array(new Reference(self::CONTEXT_INITIALISER))
        );
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 1));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.genesis.generate', $definition);
    }

    private function addDebugCommand($container)
    {
        $definition = new Definition(
            DebugSQLCli::class,
            array(new Reference(self::CONTEXT_INITIALISER))
        );
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 1));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.genesis.debug', $definition);
    }

    /**
     * @param string $config
     *
     * @return array
     */
    private function resolveEnvVars(array $config)
    {
        foreach ($config['connections'] as $index => $connection) {
            foreach ($connection as $key => $value) {
                if (strpos($value, '%') === 0 && (strrpos($value, '%') === strlen($value) - 1)) {
                    $value = getenv(trim($value, '%'));
                }
                $config['connections'][$index][$key] = $value;
            }
        }

        return $config;
    }
}
