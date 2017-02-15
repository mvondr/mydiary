<?php

namespace AppBundle\Controller;

use AppBundle\Entity\DiaryEntry;
use AppBundle\Entity\Tag;
use AppBundle\Form\BaseDiaryEntryType;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DiaryController
 * @package AppBundle\Controller
 */
class DiaryController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @return Response
     */
    public function homeAction(Request $request)
    {
        return $this->render(
            'diary/home.html.twig'
        );
    }

    /**
     * @Route("/add", name="add")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function addAction(Request $request)
    {
        $entry = new DiaryEntry(new DateTime("now", new DateTimeZone("Europe/Prague")), "my diary entry", "diary");
        $form = $this->createForm(BaseDiaryEntryType::class, $entry);
        // buttons
        $form->add(
            'save',
            SubmitType::class,
            [
                'attr' => [
                    'class' => "btn btn-lg btn-success"
                ]
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $diaryEntry = $form->getData();
                $em = $this->getDoctrine()->getManager();
                $em->persist($entry);
                $em->flush();
                $this->addFlash(
                    'success',
                    'Your entry "' . $diaryEntry->getNote() . '" has been saved!'
                );
            }
            return $this->redirectToRoute("index");
        }

        return $this->render('diary/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/edit/{id}", name="edit")
     * @param Request $request
     * @param int $id Diary entry id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var DiaryEntry $diaryEntry */
        $diaryEntry = $em->getRepository("AppBundle:DiaryEntry")->find($id);
        $form = $this->createForm(BaseDiaryEntryType::class, $diaryEntry);
        /** @var PersistentCollection $tags */
        $tags = $diaryEntry->getTags();
        $tags = $tags->toArray();
        $choices = [];
        /** @var Tag $oneTag */
        foreach ($tags as $oneTag) {
            $choices[] = $oneTag->getText();
        }

        $form->add('tags', ChoiceType::class, [
            'choices' => $choices
        ]);

        // buttons
        $form->add(
            'update',
            SubmitType::class,
            [
                'attr' => [
                    'class' => "btn btn-lg btn-success"
                ]
            ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('update')->isClicked()) {
                $diaryEntry = $form->getData();
                $em->flush();
                $this->addFlash(
                    'success',
                    'Your entry "' . $diaryEntry->getNote() . '" has been updated!'
                );
            }
            return $this->redirectToRoute("index");
        }
        if ($diaryEntry) {
            return $this->render('diary/edit.html.twig', array(
                'form' => $form->createView(),
                'id' => $diaryEntry->getId(),
                'shortNote' => $this->get('app.utils.text')->shorten($diaryEntry->getNote()),
            ));
        }
        return $this->redirectToRoute("index");
    }

    /**
     * @Route("/delete/{id}", name="delete")
     * @param Request $request
     * @param int $id Diary entry id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $diaryEntry = $em->getRepository("AppBundle:DiaryEntry")->find($id);
        if ($diaryEntry) {
            $em->remove($diaryEntry);
            $em->flush();
            $this->addFlash(
                'success',
                'Your entry "' . $diaryEntry->getNote() . '" has been deleted!'
            );
        }
        return $this->redirectToRoute("index");
    }

    /**
     * @param Request $request
     * @param $id
     */
    public function showAction(Request $request, $id)
    {

    }

    /**
     * @Route("/index", name="index")
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $diaryEntries = $em->getRepository('AppBundle:DiaryEntry')->findAllDesc();
        return $this->render(
            'diary/index.html.twig',
            ['diaryEntries' => $diaryEntries]
        );
    }
}
