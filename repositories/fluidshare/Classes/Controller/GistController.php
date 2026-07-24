<?php
namespace FluidTYPO3\Fluidshare\Controller;

use FluidTYPO3\Fluidshare\Domain\Model\Gist;
use FluidTYPO3\Fluidshare\Domain\Repository\ExtensionRepository;
use FluidTYPO3\Fluidshare\Domain\Repository\GistRepository;
use FluidTYPO3\Fluidshare\Domain\Repository\TagRepository;
use FluidTYPO3\Fluidshare\Fetcher\GistDataFetcher;
use FluidTYPO3\Fluidshare\Fetcher\Response;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Psr\Http\Message\ResponseInterface;

/**
 * Class GistController
 *
 * @package FluidTYPO3\Fluidshare\Controller
 */
class GistController extends ActionController {

	/**
	 * @var GistRepository
	 */
	protected $gistRepository;

	/**
	 * @var TagRepository
	 */
	protected $tagRepository;

	/**
	 * @var ExtensionRepository
	 */
	protected $extensionRepository;

	protected AssetCollector $assetCollector;

	/**
	 * @param GistRepository $gistRepository
	 * @return void
	 */
	public function injectGistRepository(GistRepository $gistRepository) {
		$this->gistRepository = $gistRepository;
	}

	/**
	 * @param TagRepository $tagRepository
	 * @return void
	 */
	public function injectTagRepository(TagRepository $tagRepository) {
		$this->tagRepository = $tagRepository;
	}

	/**
	 * @param ExtensionRepository $extensionRepository
	 * @return void
	 */
	public function injectExtensionRepository(ExtensionRepository $extensionRepository) {
		$this->extensionRepository = $extensionRepository;
	}

	public function injectAssetCollector(AssetCollector $assetCollector): void
	{
		$this->assetCollector = $assetCollector;
	}

	/**
	 * @param integer $tag
	 * @param integer $extension
	 */
	public function listAction($tag = NULL, $extension = NULL): ResponseInterface
	{
		if ($this->isFilterContentElement()) {
			$this->assignFilterVariables();
			return $this->htmlResponse();
		}

		$query = $this->gistRepository->createQuery();
		$constraints = array(
			$query->equals('confirmed', TRUE)
		);
		if (NULL !== $tag) {
			$constraints[] = $query->contains('tags', $tag);
		}
		if (NULL !== $extension) {
			$constraints[] = $query->contains('extensions', $extension);
		}
		if (1 === count($constraints)) {
			$query->matching($constraints[0]);
		} elseif (0 < count($constraints)) {
			$query->matching($query->logicalAnd(...$constraints));
		}
		$query->setOrderings(array(
			'crdate' => 'DESC'
		));
		$gists = $query->execute();
		$this->view->assign('gists', $gists);
		return $this->htmlResponse();
	}

	/**
	 * @param Gist $gist
	 */
	public function displayAction(Gist $gist): ResponseInterface
	{
		if ($this->isFilterContentElement()) {
			$this->assignFilterVariables();
			return $this->htmlResponse();
		}

		$response = $this->fetchGistDataAndLoadStylesheet($gist);
		$this->view->assign('gist', $gist);
		$this->view->assign('response', $response);
		return $this->htmlResponse();
	}

	/**
	 * @param Gist $gist
	 */
	public function newAction(Gist $gist = NULL): ResponseInterface
	{
		$this->view->assign('gist', $gist);
		$this->view->assign('tags', $this->tagRepository->findAll());
		$this->view->assign('extensions', $this->extensionRepository->findAll());
		if (NULL !== $this->request->getOriginalRequest()) {
			$this->view->assign('submission', $this->request->getOriginalRequest()->getArgument('gist'));
		}
		return $this->htmlResponse();
	}

	/**
	 * @param Gist $gist
	 */
	public function confirmAction(Gist $gist): ResponseInterface
	{
		$response = $this->fetchGistDataAndLoadStylesheet($gist);
		$this->view->assign('gist', $gist);
		$this->view->assign('response', $response);
		return $this->htmlResponse();
	}

	/**
	 * @param Gist $gist
	 */
	public function createAction(Gist $gist): ResponseInterface
	{
		$this->addFlashMessage(LocalizationUtility::translate('messages.created', 'fluidshare'));
		$this->gistRepository->add($gist);
		return $this->redirect('list');
	}

	/**
	 * @param Gist $gist
	 * @return Response
	 */
	protected function fetchGistDataAndLoadStylesheet(Gist $gist) {
		$fetcher = new GistDataFetcher();
		$response = $fetcher->fetch($gist->getUrl());
		$stylesheet = ObjectAccess::getPropertyPath($response, 'json.stylesheet');
		if (is_string($stylesheet) && '' !== $stylesheet) {
			$this->assetCollector->addStyleSheet('fluidshare-gist-' . sha1($stylesheet), $stylesheet);
		}
		return $response;
	}

	private function isFilterContentElement(): bool
	{
		$contentObject = $this->request->getAttribute('currentContentObject');
		return 'fluidshare_filter' === ($contentObject?->data['CType'] ?? NULL);
	}

	private function assignFilterVariables(): void
	{
		$displayArguments = $this->getDisplayArguments();
		$this->view->assign('renderFilter', TRUE);
		$this->view->assign('tags', $this->tagRepository->findAll());
		$this->view->assign('extensions', $this->extensionRepository->findAll());
		$this->view->assign('selectedTag', $this->normalizeFilterValue($displayArguments['tag'] ?? NULL));
		$this->view->assign('selectedExtension', $this->normalizeFilterValue($displayArguments['extension'] ?? NULL));
	}

	/**
	 * @return array<string, mixed>
	 */
	private function getDisplayArguments(): array
	{
		$queryArguments = $this->request->getQueryParams()['tx_fluidshare_display'] ?? array();
		$parsedBody = $this->request->getParsedBody();
		$bodyArguments = is_array($parsedBody)
			? ($parsedBody['tx_fluidshare_display'] ?? array())
			: array();
		return array_replace(
			is_array($queryArguments) ? $queryArguments : array(),
			is_array($bodyArguments) ? $bodyArguments : array()
		);
	}

	/**
	 * @param mixed $value
	 */
	private function normalizeFilterValue($value): ?int
	{
		if (!is_scalar($value) || !ctype_digit((string) $value)) {
			return NULL;
		}
		$value = (int) $value;
		return 0 < $value ? $value : NULL;
	}
}
