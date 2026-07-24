<?php
namespace FluidTYPO3\Fluidshare\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Gist extends AbstractEntity {

	/**
	 * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
	 * @TYPO3\CMS\Extbase\Annotation\Validate("Text")
	 * @var string
	 */
	protected $title;

	/**
	 * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
	 * @TYPO3\CMS\Extbase\Annotation\Validate("RegularExpression", options={"regularExpression": "/^https\:\/\/gist\.github\.com\//"})
	 * @var string
	 */
	protected $url;

	/**
	 * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
	 * @TYPO3\CMS\Extbase\Annotation\Validate("Text")
	 * @var string
	 */
	protected $summary;

	/**
	 * @var boolean
	 */
	protected $confirmed = FALSE;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\FluidTYPO3\Fluidshare\Domain\Model\Tag>
	 */
	protected $tags;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\FluidTYPO3\Fluidshare\Domain\Model\Extension>
	 */
	protected $extensions;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->tags = new ObjectStorage();
		$this->extensions = new ObjectStorage();
	}

	/**
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $url
	 * @return void
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @param string $summary
	 * @return void
	 */
	public function setSummary($summary) {
		$this->summary = $summary;
	}

	/**
	 * @return string
	 */
	public function getSummary() {
		return $this->summary;
	}

	/**
	 * @param boolean $confirmed
	 * @return void
	 */
	public function setConfirmed($confirmed) {
		$this->confirmed = $confirmed;
	}

	/**
	 * @return boolean
	 */
	public function isConfirmed() {
		return $this->confirmed;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\FluidTYPO3\Fluidshare\Domain\Model\Tag> $tags
	 * @return void
	 */
	public function setTags($tags) {
		$this->tags = $tags;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\FluidTYPO3\Fluidshare\Domain\Model\Tag>
	 */
	public function getTags() {
		return $this->tags;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\FluidTYPO3\Fluidshare\Domain\Model\Extension> $extensions
	 * @return void
	 */
	public function setExtensions($extensions) {
		$this->extensions = $extensions;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\FluidTYPO3\Fluidshare\Domain\Model\Extension>
	 */
	public function getExtensions() {
		return $this->extensions;
	}

}
