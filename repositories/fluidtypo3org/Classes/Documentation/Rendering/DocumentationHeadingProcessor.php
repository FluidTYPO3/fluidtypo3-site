<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Documentation\Rendering;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;

final readonly class DocumentationHeadingProcessor
{
    public function process(DocumentParsedEvent $event): void
    {
        $walker = $event->getDocument()->walker();
        while (($walkerEvent = $walker->next()) !== null) {
            $node = $walkerEvent->getNode();
            if (
                $walkerEvent->isEntering()
                && $node instanceof Heading
                && $node->getLevel() === 1
            ) {
                $node->setLevel(2);
            }
        }
    }
}
