<?php

namespace App\Events;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use App\Repository\InvoiceRepository;
use App\Entity\Invoice;

class InvoiceChronoSubscriber implements EventSubscriberInterface
{
    private $security;
    private $repository;

    public function __construct(Security $security, InvoiceRepository $repository)
    {
        $this->security = $security;
        $this->repository = $repository;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['setChronoForInvoice', EventPriorities::PRE_VALIDATE]
        ];
    }

    public function setChronoForInvoice(GetResponseForControllerResultEvent $event)
    {
        $method = $event->getRequest()->getMethod(); //POST, GET, PUT, ...
        $user = $this->security->getUser();

        $invoice = $event->getControllerResult();

        if ($invoice instanceof Invoice && $method === 'POST') {

            $invoice->setChrono($this->repository->findNextChrono($user));

            //TODO: A déplacer dans une classe dediée:

            if (empty($invoice->getSentAt())) {
                $invoice->setSentAt(new \DateTime());
            }
        }
    }
}
