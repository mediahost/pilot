<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Curl\Diagnostics;
use Kdyby;
use Kdyby\Curl;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Panel extends Nette\Object
{

	/**
	 * @param \Exception $e
	 *
	 * @return array
	 */
	public static function renderException($e)
	{
		$click = class_exists('Nette\Diagnostics\Dumper')
			? function ($o, $c = TRUE) { return Nette\Diagnostics\Dumper::toHtml($o, array('collapse' => $c)); }
			: callback('Nette\Diagnostics\Helpers::clickableDump');

		$panel = array();

		if ($e instanceof Curl\FailedRequestException) {
			$panel['info'] = '<h3>Info</h3>' . $click($e->getInfo(), TRUE);
		}

		if ($e instanceof Curl\CurlException) {
			if ($e->getRequest()) {
				$panel['request'] = '<h3>Request</h3>' . $click($e->getRequest(), TRUE);
			}

			if ($e->getResponse()) {
				$panel['response'] = '<h3>Responses</h3>' . static::allResponses($e->getResponse());
			}
		}

		if (!empty($panel)) {
			return array(
				'tab' => 'Curl',
				'panel' => implode($panel)
			);
		}
	}



	/**
	 * @param \Kdyby\Curl\Response $response
	 *
	 * @return string
	 */
	public static function allResponses($response)
	{
		if (!$response instanceof Curl\Response) {
			return NULL;
		}

		$click = class_exists('Nette\Diagnostics\Dumper')
			? function ($o, $c = TRUE) { return Nette\Diagnostics\Dumper::toHtml($o, array('collapse' => $c)); }
			: callback('Nette\Diagnostics\Helpers::clickableDump');

		$responses = array($click($response, TRUE));
		while ($response = $response->getPrevious()) {
			$responses[] = $click($response, TRUE);
		}
		return implode('', $responses);
	}



	/**
	 * @return \Kdyby\Curl\Diagnostics\Panel
	 */
	public static function registerBluescreen()
	{
		Nette\Diagnostics\Debugger::$blueScreen->addPanel(array(get_called_class(), 'renderException'));
	}

}
