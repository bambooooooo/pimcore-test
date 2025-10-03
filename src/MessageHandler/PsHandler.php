<?php

namespace App\MessageHandler;

use App\Message\PsMessage;
use App\Service\PrestashopService;
use Container2xfCRlT\getMessenger_Retry_MultiplierRetryStrategy_PimcoreAssetUpdateService;
use Pimcore\Model\DataObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Mailer\Messenger\MessageHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use function Psy\info;

#[AsMessageHandler]
class PsHandler
{
    public function __construct(private readonly PrestashopService $ps, private readonly LockFactory $lockFactory, private readonly LoggerInterface $logger)
    {

    }

    public function __invoke(PsMessage $message)
    {
        $obj = DataObject::getById($message->getObjectId());

        if(!$obj)
        {
            return;
        }

        $key = new Key("obj_" . $message->getObjectId());
        $lock = $this->lockFactory->createLock($key);

        try
        {
            $this->logger->info("Trying to lock " . $message->getObjectId() . "...");
            $lock->acquire(true);
            $this->logger->info("Lock acquired.");

            if($obj instanceof DataObject\Group)
            {
                $this->updateGroup($obj);
            }

            if($obj instanceof DataObject\Product)
            {
                $this->updateProduct($obj);
            }

            if($obj instanceof DataObject\ProductSet)
            {
                $this->updateProductSet($obj);
            }
        }
        finally
        {
            $lock->release();
            $this->logger->info("Lock released.");
        }
    }

    private function updateGroup(DataObject\Group $group): void
    {
        if($group->isPublished())
        {
            if($group->getPs_megstyl_pl())
            {
                if($group->getPs_megstyl_pl_id())
                {
                    if($group->getPs_megstyl_pl_version() == $this->getHash($group))
                    {
                        $this->logger->info('[~] Same version. Skipping.');
                        return;
                    }

                    $this->logger->info('[~] Update #' . $group->getId() . ' [' . $group->getKey() . '] ');
                    $this->updateGroupInPrestashop($group);
                }
                else
                {
                    $this->logger->info('[+] Add #' . $group->getId() . ' [' . $group->getKey() . '] ');
                    $this->addGroupInPrestashop($group);
                }
            }
            else
            {
                $this->removeOrDeactivateGroupInPrestashop($group);

                if($group->getPs_megstyl_pl_version())
                {
                    $this->logger->info("Category deactivated ==> Remove version info");
                    $group->setPs_megstyl_pl_version(null);
                    $group->save();
                }
            }
        }
        else
        {
            if($group->getPs_megstyl_pl_version())
            {
                $this->removeOrDeactivateGroupInPrestashop($group);

                $this->logger->info("Category deactivated ==> Remove version info");
                $group->setPs_megstyl_pl_version(null);
                $group->save();
            }
        }
    }

    private function removeOrDeactivateGroupInPrestashop(DataObject\Group $group): void
    {
        try
        {
            if($this->tryDeleteCategory($group))
            {
                $group->setPs_megstyl_pl_version(null);
                $group->setPs_megstyl_pl_id(null);
                $group->save();

                return;
            }

            $this->deactivateGroup($group);
        }
        catch(\Exception|\Throwable $e)
        {
            $this->logger->warning($e->getMessage());
        }
    }

    private function deactivateGroup(DataObject\Group $group): void
    {
        $schema = $this->ps->get("categories/" . $group->getPs_megstyl_pl_id());

        unset($schema->category->level_depth);
        unset($schema->category->nb_products_recursive);

        $schema->category->active = 0;

        $this->ps->put("categories/" . $group->getPs_megstyl_pl_id(), $schema, false);
    }

    private function tryDeleteCategory(DataObject\Group $group): bool
    {
        try
        {
            $this->ps->delete("categories/" . $group->getPs_megstyl_pl_id());
            return true;
        }
        catch(\Exception|\Throwable $e)
        {
            $this->logger->warning($e->getMessage());
        }

        return false;
    }

    private function updateGroupInPrestashop(DataObject\Group $group): void
    {
        $schema = $this->ps->get("categories/" . $group->getPs_megstyl_pl_id());

        $schema->category->name->xpath("language[@id=1]")[0][0] = $group->getName("pl");
        $schema->category->name->xpath("language[@id=2]")[0][0] = $group->getName("en");
        $schema->category->link_rewrite->xpath("language[@id=1]")[0][0] = $this->ps->getLinkRewrite($group->getName("pl"));
        $schema->category->link_rewrite->xpath("language[@id=2]")[0][0] = $this->ps->getLinkRewrite($group->getName("en"));
        $schema->category->description->xpath("language[@id=1]")[0][0] = $group->getDescription("pl") ?? "";
        $schema->category->description->xpath("language[@id=2]")[0][0] = $group->getDescription("en") ?? "";

        unset($schema->category->level_depth);
        unset($schema->category->nb_products_recursive);

        $schema->category->active = $group->getPs_megstyl_pl() == true ? "1" : "0";

        $version = $this->getHash($group);

        try
        {
            $this->ps->put("categories/" . $group->getPs_megstyl_pl_id(), $schema, false);

            $this->updateGroupImage($group);

            $group->setPs_megstyl_pl_version($version);
            $group->save();
        }
        catch (\Exception|\Throwable $exception)
        {
            echo 'Error: ' . $exception->getMessage() . PHP_EOL;
        }
    }

    private function addGroupInPrestashop(DataObject\Group $group): void
    {
        $NEW_CATEGORY_BUCKET_ID = 354;

        $schema = $this->ps->get("categories", "blank");

        unset($schema->category->id);

        $schema->category->id_parent = $NEW_CATEGORY_BUCKET_ID;
        $schema->category->active = 1;
        // $schema->category->id_shop_default = ;
        $schema->category->is_root_category = 0;
        $schema->category->name->xpath("language[@id=1]")[0][0] = $group->getName("pl");
        $schema->category->name->xpath("language[@id=2]")[0][0] = $group->getName("en");
        $schema->category->link_rewrite->xpath("language[@id=1]")[0][0] = $this->ps->getLinkRewrite($group->getName("pl"));
        $schema->category->link_rewrite->xpath("language[@id=2]")[0][0] = $this->ps->getLinkRewrite($group->getName("en"));
        $schema->category->description->xpath("language[@id=1]")[0][0] = $group->getDescription("pl") ?? "";
        $schema->category->description->xpath("language[@id=2]")[0][0] = $group->getDescription("en") ?? "";
        // $schema->category->meta_title = ;
        // $schema->category->meta_description = ;
        // $schema->category->meta_keywords = ;

        $version = $this->getHash($group, true);

        $res = $this->ps->post("categories", $schema);
        $group->setPs_megstyl_pl_id((int)$res->category->id);

        $group->setPs_megstyl_pl_version($version);
        $group->save();
    }

    private function updateGroupImage(DataObject\Group $group): void
    {
        $this->logger->info("Updating image...");

        $method = "POST";

        if($this->ps->head("images/categories/" . $group->getPs_megstyl_pl_id()) == 200)
        {
            $method = "PUT";
        }

        $image = $group->getImage()->getThumbnail("ps_cat_banner_1330x260");
        $stream = $image->getStream();

        $tempFile = tempnam(sys_get_temp_dir(), 'pim_image_') . ".jpg";
        $this->logger->info($tempFile . PHP_EOL);

        file_put_contents($tempFile, stream_get_contents($stream));

        $this->ps->uploadImage("images/categories/" . $group->getPs_megstyl_pl_id(), $tempFile, $method);
    }

    private function getHash(DataObject\Group $group, bool $excludeImage = false)
    {
        $data = [
            $group->getName("pl") ?? "",
            $group->getName("en") ?? "",
            $group->getDescription("pl") ?? "",
            $group->getDescription("en") ?? ""
        ];

        if(!$excludeImage)
        {
            $data[] = $group->getImage()?->getChecksum() ?? 0;
        }

        return hash('sha256', json_encode($data));
    }

    private function updateProduct(DataObject\Product $product)
    {

    }

    private function updateProductSet(DataObject\ProductSet $productSet)
    {

    }
}
