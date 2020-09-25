<?php

namespace App\Controller\Home;

use App\Entity\Home\Shopcart;
use App\Form\Home\ShopcartType;
use App\Repository\Home\ShopcartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/home/shopcart")
 */
class ShopcartController extends AbstractController
{
    /**
     * @Route("/", name="home_shopcart_index", methods="GET")
     */
    public function index(ShopcartRepository $shopcartRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        //dump($user);
        //echo $user->getid();
        //die();

        $em= $this->getDoctrine()->getManager();
        $sql="SELECT p.title,p.sprice, s.*
       FROM shopcart s, product p
       WHERE s.productid= p.id and userid= :userid";

        $statement= $em->getConnection()->prepare($sql);
        $statement->bindValue('userid', $user->getid());
        $statement->execute();
        $shopcart = $statement->fetchAll();

        return $this->render('home/shopcart/index.html.twig', ['shopcarts' => $shopcart]);
    }

    /**
     * @Route("/new", name="home_shopcart_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $shopcart = new Shopcart();
        $form = $this->createForm(ShopcartType::class, $shopcart);
        $form->handleRequest($request);

        echo $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('add-item', $submittedToken)) {

            if ($form->isSubmitted()) {
                $em = $this->getDoctrine()->getManager();
                $user = $this->getUser();
                $shopcart->setUserid($user->getid());

                $em->persist($shopcart);
                $em->flush();

                return $this->redirectToRoute('home_shopcart_index');
            }

            return $this->render('home/shopcart/new.html.twig', [
                'shopcart' => $shopcart,
                'form' => $form->createView(),
            ]);
        }
    }

    /**
     * @Route("/{id}", name="home_shopcart_show", methods="GET")
     */
    public function show(Shopcart $shopcart): Response
    {
        return $this->render('home/shopcart/show.html.twig', ['shopcart' => $shopcart]);
    }

    /**
     * @Route("/{id}/edit", name="home_shopcart_edit", methods="GET|POST")
     */
    public function edit(Request $request, Shopcart $shopcart): Response
    {
        $form = $this->createForm(ShopcartType::class, $shopcart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('home_shopcart_edit', ['id' => $shopcart->getId()]);
        }

        return $this->render('home/shopcart/edit.html.twig', [
            'shopcart' => $shopcart,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/{id}/del", name="home_shopcart_del", methods="GET|POST")
     */
    public function del(Request $request, Shopcart $shopcart): Response
    {

        $em=$this->getDoctrine()->getManager();
        $em->remove($shopcart);
        $em->flush();

        return $this->redirectToRoute('home_shopcart_index');
        $this->addFlash('success','Ürün Silindi');
    }




    /**
     * @Route("/{id}", name="home_shopcart_delete")
     */
    public function delete(Request $request, Shopcart $shopcart): Response
    {
        if ($this->isCsrfTokenValid('delete'.$shopcart->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($shopcart);
            $em->flush();
        }

        return $this->redirectToRoute('home_shopcart_index');
    }
}
