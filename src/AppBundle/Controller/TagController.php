<?php

namespace AppBundle\Controller;

use AppBundle\Form\TagType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class TagController
 * @package AppBundle\Controller
 */
class TagController extends Controller
{
    /**
     * @Route(
     *     "/tag/index/{orderBy}/{sort}",
     *     name="tag_index",
     *     defaults={"orderBy": "id", "sort": "asc"},
     *     requirements={"orderBy": "id|text", "sort": "asc|desc"},
     *     )
     * @param Request $request
     * @param string $orderBy Database column for order by clause
     * @param string $sort sorting parameter of database order by clause
     * @return Response
     */
    public function indexAction(Request $request, $orderBy = "id", $sort = "asc")
    {
        $em = $this->getDoctrine()->getManager();
        $tags = $em->getRepository("AppBundle:Tag")->findAllOrderBy($orderBy, $sort);
        return $this->render("tag/index.html.twig", ['tags' => $tags]);
    }

    /**
     * @Route("/tag/edit/{id}", name="tag_edit")
     * @param Request $request
     * @param int $id Id of the tag
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $tag = $em->getRepository("AppBundle:Tag")->find($id);
        $form = $this->createForm(TagType::class, $tag);

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

        // submit form
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('update')->isClicked()) {
                $tag = $form->getData();
                $em->flush();
                $this->addFlash(
                    'success',
                    'Your tag "' . $tag->getText() . '" has been updated!'
                );
            }
            return $this->redirectToRoute("tag_index");
        }
        if ($tag) {
            return $this->render('tag/edit.html.twig', array(
                'form' => $form->createView(),
                'id' => $tag->getId(), // for Delete button
            ));
        }

        echo "Hm, we are here...";
    }
}
