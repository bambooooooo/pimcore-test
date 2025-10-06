<?php

namespace App\MessageHandler;

use App\Message\PsMessage;
use App\Service\PrestashopService;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Product;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

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

            if($obj instanceof Product)
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

    private function getHash(DataObject\Group|Product $obj, bool $excludeImage = false)
    {
        $data = [];

        if($obj instanceof Product)
        {
            $data = [
                $obj->getName("pl"),
                $obj->getName("en")
            ];

            return uuid_create();

        }
        elseif($obj instanceof DataObject\Group)
        {
            $data = [
                $obj->getName("pl") ?? "",
                $obj->getName("en") ?? "",
                $obj->getDescription("pl") ?? "",
                $obj->getDescription("en") ?? ""
            ];

            if(!$excludeImage)
            {
                $data[] = $obj->getImage()?->getChecksum() ?? 0;
            }
        }

        return hash('sha256', json_encode($data));
    }

    private function updateProduct(Product $product)
    {
        $this->logger->info("Updating [" . $product->getKey() . "]...");

        if($product->isPublished())
        {
            if($product->getPs_megstyl_pl())
            {
                if($product->getPs_megstyl_pl_id())
                {
                    if($product->getPs_megstyl_pl_version() == $this->getHash($product))
                    {
                        $this->logger->info('[~] Same version. Skipping.');
                        return;
                    }

                    $this->logger->info('[~] Update #' . $product->getId() . ' [' . $product->getKey() . '] ');
                    $this->updateProductInPrestashop($product);
                }
                else
                {
                    $this->logger->info('[+] Add #' . $product->getId() . ' [' . $product->getKey() . '] ');
                    $this->addProductInPrestashop($product);
                }
            }
            else
            {
                $this->removeProductInPrestashop($product);

                if($product->getPs_megstyl_pl_version())
                {
                    $this->logger->info("Category deactivated ==> Remove version info");
                    $product->setPs_megstyl_pl_version(null);
                    $product->save();
                }
            }
        }
        else
        {
            if($product->getPs_megstyl_pl_version())
            {
                $this->removeProductInPrestashop($product);

                $this->logger->info("Category deactivated ==> Remove version info");
                $product->setPs_megstyl_pl_version(null);
                $product->save();
            }
        }

        $this->logger->info("Done.");
    }

    private function updateProductInPrestashop(Product $product)
    {
        $this->updateProductImages($product);
    }

    private function addProductInPrestashop(Product $product)
    {
//        $schema = $this->ps->get("products", "blank");
        $version = $this->getHash($product, true);

        $xml = new SimpleXMLElement("<prestashop xmlns:xlink=\"http://www.w3.org/1999/xlink\"></prestashop>");
        $prod = $xml->addChild("product");

        $prod->addChild("id_manufacturer", 1);
        $prod->addChild("id_supplier", 1);
        $prod->addChild("id_category_default", 6);
        $prod->addChild("new", 1);
        $prod->addChild("cache_default_attribute", 1);
        $prod->addChild("id_default_image");
        $prod->addChild("id_default_combination");
        $prod->addChild("id_tax_rules_group", 1);
        $prod->addChild("type", 1);
        $prod->addChild("id_shop_default", 1);
        $prod->addChild("reference", $product->getId());
        $prod->addChild("supplier_reference", $product->getId() . "-" . $product->getKey());
        $prod->addChild("location");
        $prod->addChild("width", 0.0000);
        $prod->addChild("height", 0.0000);
        $prod->addChild("depth", 0.0000);
        $prod->addChild("weight", 0.0000);
        $prod->addChild("quantity_discount", 0);
        $prod->addChild("ean13");
        $prod->addChild("isbn");
        $prod->addChild("upc");
        $prod->addChild("mpn", $product->getId());

        $prod->addChild("cache_is_pack", 0);
        $prod->addChild("cache_has_attachments", 0);
        $prod->addChild("is_virtual", 0);
        $prod->addChild("state", 1);
        $prod->addChild("additional_delivery_times");
        $prod->addChild("delivery_in_stock");
        $prod->addChild("delivery_out_stock");
        $prod->addChild("product_type", "standard");
        $prod->addChild("on_sale", 0);
        $prod->addChild("online_only", 0);
        $prod->addChild("ecotax", 0);
        $prod->addChild("minimal_quantity", 0);
        $prod->addChild("low_stock_threshold", 0);
        $prod->addChild("low_stock_alert", 0);

        $prod->addChild("price", 100.0);
        $prod->addChild("wholesale_price", 100.0);

        $prod->addChild("unity");

        $prod->addChild("unit_price", 100.0);
        $prod->addChild("unit_price_ratio");

        $prod->addChild('additional_shipping_cost');
        $prod->addChild('customizable');
        $prod->addChild('text_fields');
        $prod->addChild('uploadable_files');
        $prod->addChild('active');
        $prod->addChild('redirect_type');
        $prod->addChild('id_type_redirected');
        $prod->addChild('available_for_order', 1);
        $prod->addChild('available_date');
        $prod->addChild('show_condition');
        $prod->addChild('condition');
        $prod->addChild('show_price');
        $prod->addChild('indexed');
        $prod->addChild('visibility');
        $prod->addChild('advanced_stock_management');
        $prod->addChild('pack_stock_type');
        $prod->addChild('meta_description');
        $prod->addChild('meta_keywords');
        $prod->addChild('meta_title');
        $prod->addChild('link_rewrite');
        $name = $prod->addChild("name");
        $pl = $name->addChild("language", $product->getName("pl"));
        $pl->addAttribute("id", 1);
        $en = $name->addChild("language", $product->getName("en"));
        $en->addAttribute("id", 2);
        $prod->addChild('description');
        $prod->addChild('description_short');
        $prod->addChild('available_now');
        $prod->addChild('available_later');


        $prod->addChild("active", "1");

        $associations = $prod->addChild("associations");
        $categories = $associations->addChild("categories");
        $category = $categories->addChild("category");
        $catId = $category->addChild("id", 6);

        $accessories = $associations->addChild("accessories");
        $accessories->addAttribute("nodeType", "product");
        $accessories->addAttribute("api", "products");

        $siblings = $this->getNestedPrestashopSiblings($product);

        foreach($siblings as $psProductId)
        {
            $accessoryProduct = $accessories->addChild("product");
            $accessoryProduct->addChild("id", $psProductId);
        }


        $res = $this->ps->post("products", $xml, ["id_shop_group" => 1]);

        $product->setPs_megstyl_pl_id((int)$res->product->id);

        $product->setPs_megstyl_pl_version($version);
        $product->save();
    }

    private function updateProductImages(Product $product)
    {
        $image = $product->getImage()->getThumbnail("800x800_png");
        $stream = $image->getStream();

        $this->logger->info($image->getFrontendPath());

        $tempFile = tempnam(sys_get_temp_dir(), 'pim_image_') . ".png";
        $this->logger->info($tempFile);

        file_put_contents($tempFile, stream_get_contents($stream));

        $this->ps->uploadImage("images/products/" . $product->getPs_megstyl_pl_id() . "/", $tempFile);
    }

    private function getNestedPrestashopSiblings(Product $product, array $items = []): array
    {
        $superParent = $this->getFirstModelParent($product);

        if($superParent)
        {
            $this->logger->info("Super parent: " . $superParent->getKey());
        }
        else
        {
            $this->logger->info("No super parent found");
            return [];
        }

        $children = $this->getNestedActualChildren($superParent);

        foreach($children as $child)
        {
            if($child instanceof Product && $child->getPs_megstyl_pl() && $child->getPs_megstyl_pl_id())
            {
                $this->logger->info("Reference presta product " . $child->getKey() . " with presta id: " . $child->getPs_megstyl_pl_id());
                $items[] = $child->getPs_megstyl_pl_id();
            }
            else
            {
                $this->logger->info("Product " . $child->getKey() . " skipped due no presta id found.");
            }
        }

        return $items;
    }

    private function getFirstModelParent(Product $product): Product|null
    {
        $parent = $product->getParent();

        if($parent instanceof Product)
        {
            if($parent->getObjectType() == "MODEL")
                return $parent;

            if($parent->getObjectType() == "ACTUAL" || $parent->getObjectType() == "SKU")
            {
                return $this->getFirstModelParent($parent);
            }
        }

        return null;
    }

    private function getNestedActualChildren(Product $product, array $children = []): array
    {
        foreach($product->getChildren(includingUnpublished: true) as $child)
        {
            if($child instanceof Product)
            {
                if($child->getObjectType() == "SKU")
                {
                    $children = array_merge($children, $this->getNestedActualChildren($child));
                }
                elseif($child->getObjectType() == "ACTUAL")
                {
                    $children[] = $child;
                    $children = array_merge($children, $this->getNestedActualChildren($child));
                }
            }
        }

        return $children;
    }

    private function removeProductInPrestashop(Product $product)
    {

    }

    private function updateProductSet(DataObject\ProductSet $productSet)
    {

    }
}
