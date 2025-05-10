<?php

declare(strict_types=1);

namespace Valantic\ElasticaBridgeBundle\EventListener\Pimcore;

use Pimcore\Event\AssetEvents;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\DocumentEvents;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\Asset;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Valantic\ElasticaBridgeBundle\Messenger\Message\RefreshElement;

/**
 * An abstract listener for DataObject and Document listeners.
 * These listeners are automatically registered by the bundle and update Elasticsearch with
 * any changes made in Pimcore.
 */
class ChangeListener implements EventSubscriberInterface
{
    private static bool $isEnabled = true;

    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {}

    public function handle(ElementEventInterface $event): void
    {
        if (!$this->shouldHandle($event)) {
            return;
        }

        $this->messageBus->dispatch(new RefreshElement($event->getElement()));
    }

    public function handleDeleted(ElementEventInterface $event): void
    {
        if (!$this->shouldHandle($event)) {
            return;
        }

        // TODO: define a new message for deletion
        $this->messageBus->dispatch(new RefreshElement($event->getElement()));
    }

    public static function enableListener(): void
    {
        self::$isEnabled = true;
    }

    public static function disableListener(): void
    {
        self::$isEnabled = false;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AssetEvents::POST_ADD => 'handle',
            AssetEvents::POST_UPDATE => 'handle',
            AssetEvents::POST_DELETE => 'handleDeleted',
            DataObjectEvents::POST_ADD => 'handle',
            DataObjectEvents::POST_UPDATE => 'handle',
            DataObjectEvents::POST_DELETE => 'handleDeleted',
            DocumentEvents::POST_ADD => 'handle',
            DocumentEvents::POST_UPDATE => 'handle',
            DocumentEvents::POST_DELETE => 'handleDeleted',
        ];
    }

    private function shouldHandle(ElementEventInterface $event): bool
    {
        if (!self::$isEnabled) {
            return false;
        }

        if ($event->hasArgument('isAutoSave') && $event->getArgument('isAutoSave') === true) {
            return false;
        }

        // If a folder is created in the assets section in Pimcore 11 the type is set to Unknown.
        // https://github.com/pimcore/pimcore/issues/16363
        if ($event->getElement() instanceof Asset\Unknown || $event->getElement()->getType() === 'folder') {
            return false;
        }

        return true;
    }
}
