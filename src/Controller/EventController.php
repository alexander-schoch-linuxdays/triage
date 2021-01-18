<?php

/*
 * This file is part of the thealternativezurich/triage project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Base\BaseDoctrineController;
use App\Entity\Event;
use App\Form\Event\EditEventType;
use App\Security\Voter\EventVoter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/events")
 */
class EventController extends BaseDoctrineController
{
    /**
     * @Route("/mine", name="event_mine")
     *
     * @return Response
     */
    public function mineAction()
    {
        $registrations = $this->getUser()->getRegistrations();

        return $this->render('event/mine.html.twig', ['events' => $registrations]);
    }

    /**
     * @Route("/all", name="event_moderate")
     *
     * @return Response
     */
    public function moderateAction()
    {
        $events = $this->getDoctrine()->getRepository(Event::class)->findBy([], ['public' => 'DESC', 'startDate' => 'ASC']);

        return $this->render('event/moderate.html.twig', ['events' => $events]);
    }

    /**
     * @Route("/new", name="event_new")
     *
     * @return Response
     */
    public function newAction(Request $request, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(EventVoter::EVENT_CREATE);

        $event = new Event();
        $form = $this->createForm(EditEventType::class, $event);
        $form->add('submit', SubmitType::class, ['translation_domain' => 'event', 'label' => 'new.submit']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->fastSave($event);

            $message = $translator->trans('new.success.created', [], 'event');
            $this->displaySuccess($message);

            return $this->redirectToRoute('index');
        }

        return $this->render('event/new.html.twig', ['form' => $form->createView()]);
    }
}
