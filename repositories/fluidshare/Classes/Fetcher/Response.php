<?php
namespace FluidTYPO3\Fluidshare\Fetcher;

/**
 * Class Response
 * @package FluidTYPO3\Fluidshare\Fetcher
 */
class Response {

	/**
	 * @var array
	 */
	protected $headers = array();

	/**
	 * @var integer
	 */
	protected $code = NULL;

	/**
	 * @var string
	 */
	protected $body = NULL;

	/**
	 * @var array
	 */
	protected $json = NULL;

	/**
	 * @param array $headers
	 * @return void
	 */
	public function setHeaders(array $headers) {
		$this->headers = $headers;
	}

	/**
	 * @return array
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * @param integer $code
	 * @return void
	 */
	public function setCode($code) {
		$this->code = $code;
	}

	/**
	 * @return integer
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * @param string $body
	 * @return void
	 */
	public function setBody($body) {
		$this->body = $body;
		$this->json = json_decode($body, JSON_OBJECT_AS_ARRAY);
	}

	/**
	 * @return string
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * @return array
	 */
	public function getJson() {
		return $this->json;
	}

}
