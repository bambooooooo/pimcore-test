<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Keeps deeplinks of the removed classic admin UI working (bookmarks, emails, factory printouts).
 *
 * /admin/login/deeplink?object_123_object  ->  /pimcore-studio/data-object/123
 */
class LegacyAdminController
{
    private const STUDIO_TYPES = ['object' => 'data-object', 'asset' => 'asset', 'document' => 'document'];

    #[Route('/admin/login/deeplink', name: 'admin_deeplink', methods: ['GET'])]
    public function deeplink(Request $request): Response
    {
        $target = '/pimcore-studio/';

        if (preg_match('/^(object|asset|document)_(\d+)/', (string) $request->getQueryString(), $m))
        {
            $target .= self::STUDIO_TYPES[$m[1]] . '/' . $m[2];
        }

        return new RedirectResponse($target, Response::HTTP_MOVED_PERMANENTLY);
    }
}
