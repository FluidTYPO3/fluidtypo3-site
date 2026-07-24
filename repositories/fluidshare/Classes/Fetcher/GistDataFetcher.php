<?php
namespace FluidTYPO3\Fluidshare\Fetcher;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class GistDataFetcher
 *
 * @package FluidTYPO3\Fluidshare\Fetcher
 */
class GistDataFetcher implements FetcherInterface {

	/**
	 * @var array
	 */
	protected $storage = array();

	/**
	 * @param string $url
	 * @param string $format
	 * @return Response
	 */
	public function fetch($url, $format = 'json') {
		if ($format !== substr($url, 0 - strlen($format))) {
			$url .= '.' . $format;
		}
		if (TRUE === isset($this->storage[$url])) {
			return $this->storage[$url];
		}
		$response = new Response();
		$checksum = sha1($url);
		$temporaryFilePathAndFilename = GeneralUtility::getFileAbsFileName('typo3temp/fluidshare-gist-' . $checksum . '.json');
		if (FALSE === file_exists($temporaryFilePathAndFilename) || time() - 86400 > filemtime($temporaryFilePathAndFilename)) {
			$body = file_get_contents($url);
			file_put_contents($temporaryFilePathAndFilename, $body);
		} else {
			$body = file_get_contents($temporaryFilePathAndFilename);
		}
		$response->setBody($body);
		return $this->storage[$url] = $response;
	}

}
