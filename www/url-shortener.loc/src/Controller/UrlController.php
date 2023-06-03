<?php

namespace App\Controller;

use App\Entity\Url;
use App\Repository\UrlRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UrlController extends AbstractController
{
    /**
     * @Route("/encode-url", name="encode_url")
     *
     */
    public function encodeUrl(Request $request, ValidatorInterface $validator): Response
    {
        $url = new Url();
        $entityManager = $this->getDoctrine()->getManager();
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $existUrl = $urlRepository->findFirstByUrl($request->get('url'));

        if ($existUrl) {
            if ($errors = $urlRepository::validator($existUrl, $validator)) {
                return $this->json(['errors' => $errors]);
            }
            if (!$urlRepository->availableHash($existUrl->getHash())) {
                $entityManager->remove($existUrl);
                $entityManager->flush();
                return $this->json(['error' => 'Non-existent hash.']);
            }
            return $this->json(['hash' => $existUrl->getHash()]);
        }

        $url->setUrl($request->get('url'));

        if ($errors = $urlRepository::validator($url, $validator)) {
            return $this->json(['errors' => $errors]);
        }

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
    public function decodeUrl(Request $request, ValidatorInterface $validator): JsonResponse
    {
        /** @var UrlRepository $urlRepository */
        $entityManager = $this->getDoctrine()->getManager();
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $hash = $request->get('hash');
        $url = $urlRepository->findFirstByHash($hash);

        if (empty ($url)) {
            return $this->json(['error' => 'Non-existent hash.']);
        }
        if (!$urlRepository->availableHash($url->getHash())) {
            $entityManager->remove($url);
            $entityManager->flush();
            return $this->json(['error' => 'Non-existent hash.']);
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
        if (empty ($url) || !$urlRepository->availableHash($hash)) {
            return $this->json(['error' => 'Non-existent hash.']);
        }
        $redirectUrl = $url->getUrl();

        return $this->redirect($redirectUrl);
    }
}
