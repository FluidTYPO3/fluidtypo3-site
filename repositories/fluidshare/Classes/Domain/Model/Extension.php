<?php
namespace FluidTYPO3\Fluidshare\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Extension extends AbstractEntity {

	/**
	 * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
	 * @TYPO3\CMS\Extbase\Annotation\Validate("Text")
	 * @var string
	 */
	protected $extensionKey;

	/**
	 * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
	 * @TYPO3\CMS\Extbase\Annotation\Validate("Text")
	 * @var string
	 */
	protected $extensionName;

	/**
	 * @param string $key
	 * @return void
	 */
	public function setExtensionKey($key) {
		$this->extensionKey = $key;
	}

	/**
	 * @return string
	 */
	public function getExtensionKey() {
		return $this->extensionKey;
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public function setExtensionName($name) {
		$this->extensionName = $name;
	}

	/**
	 * @return string
	 */
	public function getExtensionName() {
		return $this->extensionName;
	}

}
