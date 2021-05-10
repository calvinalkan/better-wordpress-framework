<?php


	declare( strict_types = 1 );


	namespace WPEmerge\Factories;

	use Contracts\ContainerAdapter;
	use Psr\Log\LoggerInterface;
	use Psr\Log\NullLogger;
	use Whoops\Handler\JsonResponseHandler;
	use Whoops\Handler\PrettyPageHandler;
	use Whoops\Run;
	use Whoops\RunInterface;
	use WPEmerge\Exceptions\ConfigurationException;
	use WPEmerge\Exceptions\DebugErrorHandler;
	use WPEmerge\Exceptions\ProductionErrorHandler;

	class ErrorHandlerFactory {

		const ALLOWED_EDITORS = [

			'emacs',
			'idea',
			'macvim',
			'phpstorm',
			'sublime',
			'textmate',
			'xdebug',
			'vscode',
			'atom',
			'espresso'

		];

		public static function make( ContainerAdapter $container, bool $is_debug, bool $is_ajax_request, string $editor = null ) {

			if ( ! $is_debug ) {

				$logger = $container->offsetExists(LoggerInterface::class)
					? $container->make(LoggerInterface::class)
					: new NullLogger();

				return new ProductionErrorHandler( $container, $logger,  $is_ajax_request );

			}

			$whoops = new Run();

			$pretty_page_handler = new PrettyPageHandler();
			$pretty_page_handler->handleUnconditionally(true);

			$container->instance(RunInterface::class, $whoops);
			$container->instance(PrettyPageHandler::class, $pretty_page_handler);


			if ( $is_ajax_request ) {

				$json_handler = new JsonResponseHandler();
				$json_handler->addTraceToOutput(true);
				$whoops->appendHandler($json_handler);

			}

			if ( $editor ) {

				if ( ! in_array($editor , static::ALLOWED_EDITORS ) ) {

					throw new ConfigurationException(
						'The editor: ' . $editor . ' is not supported by Whoops.'
					);

				}

				$pretty_page_handler->setEditor($editor);

			}

			$whoops->appendHandler($pretty_page_handler);
			$whoops->allowQuit(false);
			$whoops->writeToOutput(true);


			return new DebugErrorHandler( $whoops );

		}

	}