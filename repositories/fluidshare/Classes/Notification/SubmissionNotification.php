<?php

declare(strict_types=1);

namespace FluidTYPO3\Fluidshare\Notification;

use FluidTYPO3\Fluidshare\Domain\Model\Extension;
use FluidTYPO3\Fluidshare\Domain\Model\Gist;
use FluidTYPO3\Fluidshare\Domain\Model\Tag;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class SubmissionNotification
{
    private const BACKEND_USER_TABLE = 'be_users';
    private const SENDER_ADDRESS = 'noreply@fluidtypo3.org';
    private const SENDER_NAME = 'FluidTYPO3.org';

    public function __construct(
        private ConnectionPool $connectionPool,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
    ) {}

    public function send(Gist $gist): void
    {
        $recipient = $this->getAdminEmail();
        if ($recipient === null) {
            $this->logger->error(
                'A code example submission notification could not be sent because no active admin has a valid email address.',
            );
            return;
        }

        $email = (new Email())
            ->from(new Address(self::SENDER_ADDRESS, self::SENDER_NAME))
            ->to($recipient)
            ->subject('New FluidTYPO3 code example: ' . $this->normalizeSubject($gist->getTitle()))
            ->text($this->buildBody($gist));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error(
                'The code example submission was saved, but its admin notification email could not be sent.',
                ['exception' => $exception],
            );
        }
    }

    private function getAdminEmail(): ?string
    {
        $maintainerUids = array_values(array_filter(
            array_map(
                static fn(mixed $uid): int => (int)$uid,
                (array)($GLOBALS['TYPO3_CONF_VARS']['SYS']['systemMaintainers'] ?? []),
            ),
            static fn(int $uid): bool => $uid > 0,
        ));

        if ($maintainerUids !== []) {
            $emailsByUid = $this->findAdminEmails($maintainerUids);
            foreach ($maintainerUids as $uid) {
                $email = trim((string)($emailsByUid[$uid] ?? ''));
                if ($email !== '' && GeneralUtility::validEmail($email)) {
                    return $email;
                }
            }
        }

        foreach ($this->findAdminEmails() as $email) {
            $email = trim((string)$email);
            if ($email !== '' && GeneralUtility::validEmail($email)) {
                return $email;
            }
        }
        return null;
    }

    /**
     * @param list<int> $uids
     * @return array<int, string>
     */
    private function findAdminEmails(array $uids = []): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::BACKEND_USER_TABLE);
        $queryBuilder->getRestrictions()->removeAll();
        $constraints = [
            $queryBuilder->expr()->eq('admin', 1),
            $queryBuilder->expr()->eq('disable', 0),
            $queryBuilder->expr()->eq('deleted', 0),
            $queryBuilder->expr()->neq(
                'email',
                $queryBuilder->createNamedParameter(''),
            ),
        ];
        if ($uids !== []) {
            $constraints[] = $queryBuilder->expr()->in(
                'uid',
                $queryBuilder->createNamedParameter($uids, Connection::PARAM_INT_ARRAY),
            );
        }

        $rows = $queryBuilder
            ->select('uid', 'email')
            ->from(self::BACKEND_USER_TABLE)
            ->where(...$constraints)
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();

        $emails = [];
        foreach ($rows as $row) {
            $emails[(int)$row['uid']] = (string)$row['email'];
        }
        return $emails;
    }

    private function normalizeSubject(string $title): string
    {
        $title = trim((string)preg_replace('/\s+/u', ' ', $title));
        return mb_substr($title, 0, 160);
    }

    private function buildBody(Gist $gist): string
    {
        $extensions = [];
        foreach ($gist->getExtensions() as $extension) {
            if ($extension instanceof Extension) {
                $extensions[] = $extension->getExtensionName();
            }
        }

        $contexts = [];
        foreach ($gist->getTags() as $tag) {
            if ($tag instanceof Tag) {
                $contexts[] = $tag->getName();
            }
        }

        return implode("\n", [
            'A new code example has been submitted to FluidTYPO3.org.',
            '',
            'Title: ' . $gist->getTitle(),
            'Gist URL: ' . $gist->getUrl(),
            'Extensions: ' . ($extensions !== [] ? implode(', ', $extensions) : 'None selected'),
            'Contexts: ' . ($contexts !== [] ? implode(', ', $contexts) : 'None selected'),
            '',
            'Summary:',
            $gist->getSummary(),
            '',
            'The submission is awaiting review in the TYPO3 backend.',
        ]);
    }
}
