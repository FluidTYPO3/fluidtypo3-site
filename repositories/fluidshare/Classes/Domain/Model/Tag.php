<?php
namespace FluidTYPO3\Fluidshare\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Tag extends AbstractEntity {

	/**
	 * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
	 * @TYPO3\CMS\Extbase\Annotation\Validate("Text")
	 * @var string
	 */
	protected $name;

	/**
	 * @param string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

}
