<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2023 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use PluginsWare\Component\YendifVideoShare\Administrator\Extension\YendifVideoShareComponent;

/**
 * The YendifVideoShare service provider.
 *
 * @since  2.0.0
 */
return new class implements ServiceProviderInterface {

	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function register( Container $container ) {
		$container->registerServiceProvider( new CategoryFactory( '\\PluginsWare\\Component\\YendifVideoShare' ) );
		$container->registerServiceProvider( new MVCFactory( '\\PluginsWare\\Component\\YendifVideoShare' ) );
		$container->registerServiceProvider( new ComponentDispatcherFactory( '\\PluginsWare\\Component\\YendifVideoShare' ) );
		$container->registerServiceProvider( new RouterFactory( '\\PluginsWare\\Component\\YendifVideoShare' ) );

		$container->set(
			ComponentInterface::class,
			function( Container $container ) {
				$component = new YendifVideoShareComponent( $container->get( ComponentDispatcherFactoryInterface::class ) );

				$component->setRegistry( $container->get( Registry::class ) );
				$component->setMVCFactory( $container->get( MVCFactoryInterface::class ) );
				$component->setCategoryFactory( $container->get( CategoryFactoryInterface::class ) );
				$component->setRouterFactory( $container->get( RouterFactoryInterface::class ) );

				return $component;
			}
		);
	}
};
