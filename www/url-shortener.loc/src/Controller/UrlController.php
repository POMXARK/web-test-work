<?php

namespace App\Controller;

use App\Entity\Url;
use App\Repository\UrlRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UrlController extends AbstractController
{
    /**
     * @Route("/encode-url", name="encode_url")
     */
    public function encodeUrl(Request $request): JsonResponse
    {
        $url = new Url();
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);

        $existUrl = $urlRepository->findFirstByUrl($request->get('url'));
        if ($existUrl) {
            return $this->json([
                               'hash' => $existUrl->getHash()
                           ]);
        }

        $url->setUrl($request->get('url'));
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($url);
        $entityManager->flush();
        $hash = $url->getHash();
        $urlRepository->cacheHash($hash);

        return $this->json([
            'hash' => $hash
        ]);
    }

    /**
     * @Route("/decode-url", name="decode_url")
     * @throws NonUniqueResultException
     */
    public function decodeUrl(Request $request): JsonResponse
    {
        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $hash = $request->get('hash');
        $url = $urlRepository->findFirstByHash($hash);
        if (empty ($url) || !$urlRepository->availableHash($hash)) {
            return $this->json([
                'error' => 'Non-existent hash.'
            ]);
        }
        return $this->json([
            'url' => $url->getUrl()
        ]);
    }

    /**
     * @Route("/go-url", name="go_url")
     * @throws NonUniqueResultException
     */
    public function goUrl(Request $request)
    {
        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $hash = $request->get('hash');
        $url = $urlRepository->findFirstByHash($hash);
        if (empty ($url)) {
            return $this->json([
                                   'error' => 'Non-existent hash.'
                               ]);
        }
        $routeName = $url->getUrl();

        return $this->redirectToRoute($routeName, ['hash' => $hash]);
    }
}
