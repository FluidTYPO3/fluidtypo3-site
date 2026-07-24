<?php
namespace FluidTYPO3\Fluidshare\Fetcher;

/**
 * Interface FetcherInterface
 * @package FluidTYPO3\Fluidshare\Fetcher
 */
interface FetcherInterface {

	/**
	 * @param string $url
	 * @param string $format
	 * @return string|NULL
	 */
	public function fetch($url, $format = 'json');

}
