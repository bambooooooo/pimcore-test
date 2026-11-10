<?php

declare(strict_types=1);

namespace App\Controller;

use App\EventSubscriber\ProtectedEndpointSubscriber;
use App\Security\ProtectedEndpoint;
use App\Studio\ToolbarContextBuilder;
use Pimcore\Model\DataObject;
use Pimcore\Model\Element\Service as ElementService;
use Pimcore\Model\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Backend of the Megstyl Pimcore Studio plugin (assets/studio).
 */
#[Route('/studio-ext', name: 'studio_ext_')]
#[ProtectedEndpoint]
class StudioExtensionController
{
    public const MAX_CHILDREN = 500;

    public function __construct(private readonly ToolbarContextBuilder $contextBuilder)
    {
    }

    #[Route('/object/{id}/toolbar', name: 'object_toolbar', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function toolbar(int $id, Request $request): Response
    {
        $object = DataObject\Concrete::getById($id);
        $user = $this->user($request);

        if (!$object)
        {
            return new JsonResponse(['error' => 'Object not found'], Response::HTTP_NOT_FOUND);
        }

        if ($user && !$object->isAllowed('view', $user))
        {
            return new JsonResponse(['error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse($this->contextBuilder->build($object, $user));
    }

    /**
     * Creates children with the class of the parent - successor of the classic admin Ctrl+M shortcut.
     * Body: {"keys": ["KEY-1", "KEY-2"]} (brace patterns are expanded by the plugin).
     */
    #[Route('/object/{id}/children', name: 'object_add_children', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function addChildren(int $id, Request $request): Response
    {
        $parent = DataObject\Concrete::getById($id);
        $user = $this->user($request);
        $keys = $request->toArray()['keys'] ?? null;

        if (!$parent)
        {
            return new JsonResponse(['error' => 'Parent object not found'], Response::HTTP_NOT_FOUND);
        }

        if (!is_array($keys) || !$keys || count($keys) > self::MAX_CHILDREN || array_filter($keys, static fn ($k) => !is_string($k) || trim($k) === ''))
        {
            return new JsonResponse(['error' => sprintf('"keys" must be 1-%d non-empty strings', self::MAX_CHILDREN)], Response::HTTP_BAD_REQUEST);
        }

        if ($user && !$parent->isAllowed('create', $user))
        {
            return new JsonResponse(['error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $class = $parent::class;
        $added = [];
        $failed = [];

        foreach ($keys as $key)
        {
            $key = ElementService::getValidKey($key, 'object');

            if (DataObject\Service::pathExists($parent->getRealFullPath() . '/' . $key))
            {
                $failed[] = ['key' => $key, 'error' => 'already exists'];
                continue;
            }

            try
            {
                /** @var DataObject\Concrete $child */
                $child = new $class();
                $child->setParent($parent);
                $child->setKey($key);
                $child->setPublished(false);
                $child->setUserOwner($user?->getId() ?? 0);
                $child->setUserModification($user?->getId() ?? 0);
                $child->save();

                $added[] = ['id' => $child->getId(), 'key' => $key];
            }
            catch (\Throwable $e)
            {
                $failed[] = ['key' => $key, 'error' => $e->getMessage()];
            }
        }

        return new JsonResponse(['added' => $added, 'failed' => $failed], $added ? Response::HTTP_CREATED : Response::HTTP_OK);
    }

    private function user(Request $request): ?User
    {
        $user = $request->attributes->get(ProtectedEndpointSubscriber::ATTR_ADMIN_USER);

        return $user instanceof User ? $user : null;
    }
}
