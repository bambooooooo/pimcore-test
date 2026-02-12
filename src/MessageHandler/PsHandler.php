<?php

namespace App\MessageHandler;

use App\Message\PsMessage;
use App\Service\PrestashopService;
use Exception;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Group;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Pimcore\Model\Notification\Service\NotificationService;
use Pimcore\Model\Version;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
class PsHandler
{
    public function __construct(private readonly PrestashopService $ps, private readonly LockFactory $lockFactory, private readonly LoggerInterface $logger, private readonly NotificationService $notificationService)
    {
        Version::disable();
    }

    public function __invoke(PsMessage $message): void
    {
        if ($message->getMode() == "delete") {
            $this->logger->warning("Delete from prestashop #" . $message->getId() . " after object removal");
            $this->ps->delete("products" . "/" . $message->getId());
            return;
        }

        $obj = DataObject::getById($message->getId());

        if (!$obj) {
            return;
        }

        $key = new Key("obj_" . $message->getId());
        $lock = $this->lockFactory->createLock($key);

        try {
            $this->logger->info("Trying to lock " . $message->getId() . "...");
            $lock->acquire(true);
            $this->logger->info("Lock acquired.");

            echo '#' . $obj->getId() . "\t" . $obj->getKey() . PHP_EOL;

            if ($obj instanceof Group) {
                $this->updateGroup($obj);
            }

            if ($obj instanceof Product) {
                $this->updateProduct($obj);
            }

            if ($obj instanceof ProductSet) {
                $this->updateProductSet($obj);
            }
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error($exception->getTraceAsString());
        } finally {
            $lock->release();
            $this->logger->info("Lock released.");
        }
    }

    private function updateGroup(Group $group): void
    {
        if (!$group->isPublished() || (!$group->getPs_megstyl_pl() && $group->getPs_megstyl_pl_id())) {
            $this->removeOrDeactivateGroupInPrestashop($group);
            return;
        }

        if ($group->isPublished() && $group->getPs_megstyl_pl() && !$group->getPs_megstyl_pl_id()) {
            $this->addGroupInPrestashop($group);
            return;
        }

        if ($group->isPublished() && $group->getPs_megstyl_pl() && $group->getPs_megstyl_pl_id()) {
            $this->updateGroupInPrestashop($group);
        }
    }

    private function removeOrDeactivateGroupInPrestashop(Group $group): void
    {
        try {
            if (!$this->tryDelete($group)) {
                $this->deactivateGroup($group);
            }
        } catch (Exception|Throwable $e) {
            $this->logger->warning($e->getMessage());
        }

        if ($group->getPs_megstyl_pl_id()) {
            $group->setPs_megstyl_pl_id(null);
            $group->save(["skip" => "3rd party integration"]);
        }
    }

    private function tryDelete(Group|Product|ProductSet $obj): bool
    {
        try {
            $this->logger->info("Deleting #" . $obj->getId() . " [prestaId=" . $obj->getPs_megstyl_pl_id() . "] ...");

            $kind = null;

            if ($obj instanceof Product) {
                $kind = "products";
            } else if ($obj instanceof ProductSet) {
                $kind = "products";
            } else if ($obj instanceof Group) {
                $kind = "categories";
            }

            if ($kind) {
                $this->ps->delete($kind . "/" . $obj->getPs_megstyl_pl_id());
            }

            return true;
        } catch (Exception|Throwable $e) {
            $this->logger->warning($e->getMessage());
        }

        return false;
    }

    private function deactivateGroup(Group $group): void
    {
        $schema = $this->ps->get("categories/" . $group->getPs_megstyl_pl_id());

        unset($schema->category->level_depth);
        unset($schema->category->nb_products_recursive);

        $schema->category->active = 0;

        $this->ps->put("categories/" . $group->getPs_megstyl_pl_id(), $schema);
    }

    private function addGroupInPrestashop(Group $group): void
    {
        $parentId = 2;

        if ($group->getPs_megstyl_pl_parent() && $group->getPs_megstyl_pl_parent()->getPs_megstyl_pl_id()) {
            $parentId = $group->getPs_megstyl_pl_parent()->getPs_megstyl_pl_id();
        }

        $schema = $this->ps->get("categories", "blank");

        unset($schema->category->id);

        $schema->category->id_parent = $parentId;
        $schema->category->active = 1;
        $schema->category->is_root_category = 0;
        $schema->category->name->xpath("language[@id=1]")[0][0] = $group->getName("pl");
        $schema->category->name->xpath("language[@id=2]")[0][0] = $group->getName("en");
        $schema->category->link_rewrite->xpath("language[@id=1]")[0][0] = $this->ps->getLinkRewrite($group->getName("pl"));
        $schema->category->link_rewrite->xpath("language[@id=2]")[0][0] = $this->ps->getLinkRewrite($group->getName("en"));
        $schema->category->description->xpath("language[@id=1]")[0][0] = $group->getDescription("pl") ?? "";
        $schema->category->description->xpath("language[@id=2]")[0][0] = $group->getDescription("en") ?? "";

        try {
            $res = $this->ps->post("categories", $schema);
            $group->setPs_megstyl_pl_id((int)$res->category->id);
            $this->updateGroupImage($group);
            $group->save(["skip" => "3rd party integration"]);
        } catch (Exception|Throwable $exception) {
            $this->logger->error($exception->getMessage());

            $group->setPs_megstyl_pl_id(null);
            $group->save(["skip" => "3rd party integration - error"]);
        }
    }

    private function updateGroupInPrestashop(Group $group): void
    {
        $schema = $this->ps->get("categories/" . $group->getPs_megstyl_pl_id());

        if ($group->getPs_megstyl_pl_parent() && $group->getPs_megstyl_pl_parent()->getPs_megstyl_pl_id()) {
            $schema->category->id_parent = $group->getPs_megstyl_pl_parent()->getPs_megstyl_pl_id();
        }

        $schema->category->name->xpath("language[@id=1]")[0][0] = $group->getName("pl");
        $schema->category->name->xpath("language[@id=2]")[0][0] = $group->getName("en");
        $schema->category->link_rewrite->xpath("language[@id=1]")[0][0] = $this->ps->getLinkRewrite($group->getName("pl"));
        $schema->category->link_rewrite->xpath("language[@id=2]")[0][0] = $this->ps->getLinkRewrite($group->getName("en"));
        $schema->category->description->xpath("language[@id=1]")[0][0] = $group->getDescription("pl") ?? "";
        $schema->category->description->xpath("language[@id=2]")[0][0] = $group->getDescription("en") ?? "";

        unset($schema->category->level_depth);
        unset($schema->category->nb_products_recursive);

        $schema->category->active = 1;

        try {
            $this->ps->put("categories/" . $group->getPs_megstyl_pl_id(), $schema, true);
            $this->updateGroupImage($group);
            $group->save(["skip" => "3rd party integration"]);
        } catch (Exception|Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    private function updateGroupImage(Group $group): void
    {
        $this->logger->info("Updating image...");

        $method = "POST";

        if ($this->ps->head("images/categories/" . $group->getPs_megstyl_pl_id()) == 200) {
            $method = "PUT";
        }

        $image = $group->getImage()->getThumbnail("ps_cat_banner_1330x260");
        $stream = $image->getStream();

        $tempFile = tempnam(sys_get_temp_dir(), 'pim_image_') . ".jpg";
        $this->logger->info($tempFile . PHP_EOL);

        file_put_contents($tempFile, stream_get_contents($stream));

        $this->ps->upload("images/categories/" . $group->getPs_megstyl_pl_id(), $tempFile, $method);
    }

    private function updateProduct(Product $product): void
    {
        $this->logger->info("Updating [" . $product->getKey() . "]...");

        $psId = DataObject\Service::useInheritedValues(false, function () use ($product) {
            return $product->getPs_megstyl_pl_id();
        });

        if (!$product->isPublished() || (!$product->getPs_megstyl_pl() && $psId)) {
            $this->removeOrDeactivateProductPrestashop($product);
            return;
        }

        if ($product->isPublished() && $product->getPs_megstyl_pl() && !$psId) {
            $this->addProductInPrestashop($product);
            return;
        }

        if ($product->isPublished() && $product->getPs_megstyl_pl() && $psId) {
            $this->updateProductInPrestashop($product);
            return;
        }

        $this->logger->info("Done.");
    }

    private function removeOrDeactivateProductPrestashop(Product|ProductSet $product): void
    {
        try {
            if (!$this->tryDelete($product)) {
                $this->deactivate($product);
            }
        } catch (Exception|Throwable $e) {
            $this->logger->warning($e->getMessage());
        }

        if ($product->getPs_megstyl_pl_id()) {
            $product->setPs_megstyl_pl_id(null);
            $product->save(["skip" => "3rd party integration"]);
        }
    }

    private function deactivate(Product|ProductSet $product): void
    {
        $xml = new SimpleXMLElement("<prestashop xmlns:xlink=\"http://www.w3.org/1999/xlink\"></prestashop>");

        $prod = $xml->addChild("product");
        $prod->addChild("id", $product->getPs_megstyl_pl_id());
        $prod->addChild("active", 0);

        $res = $this->ps->patch("products", $xml);
    }

    private function addProductInPrestashop(Product|ProductSet $obj)
    {
        if (!$obj->getPs_megstyl_pl_parent()->getPs_megstyl_pl_id()) {
            $this->logger->error("Cannot add product in Prestashop, because it does not have group assigned");
            return;
        }

        $xml = new SimpleXMLElement("<prestashop xmlns:xlink=\"http://www.w3.org/1999/xlink\"></prestashop>");
        $prod = $xml->addChild("product");

        $prod->addChild("id_manufacturer", 1);
        $prod->addChild("id_supplier", 1);
        $prod->addChild("id_category_default", $obj->getPs_megstyl_pl_parent()->getPs_megstyl_pl_id());
        $prod->addChild("new", 1);
        $prod->addChild("cache_default_attribute", 1);
        $prod->addChild("id_default_image");
        $prod->addChild("id_default_combination");
        $prod->addChild("id_tax_rules_group", 1);
        $prod->addChild("type", 1);
        $prod->addChild("id_shop_default", 1);
        $prod->addChild("reference", $obj->getId());
        $prod->addChild("supplier_reference", $obj->getId() . "-" . $obj->getKey());
        $prod->addChild("location");
        $prod->addChild("width", 0.0000);
        $prod->addChild("height", 0.0000);
        $prod->addChild("depth", 0.0000);
        $prod->addChild("weight", 0.0000);
        $prod->addChild("quantity_discount", 0);
        $prod->addChild("ean13", $obj->getEan());
        $prod->addChild("isbn");
        $prod->addChild("upc");
        $prod->addChild("mpn", $obj->getId());
        $prod->addChild("cache_is_pack", 0);
        $prod->addChild("cache_has_attachments", 0);
        $prod->addChild("is_virtual", 0);
        $prod->addChild("state", 1);
        $prod->addChild("additional_delivery_times");
        $prod->addChild("delivery_in_stock");
        $prod->addChild("delivery_out_stock");

        // threat productset as a simple product in this version
        // $prod->addChild("product_type", $obj instanceof Product ? "standard" : "pack");
        $prod->addChild("product_type", "standard");
        $prod->addChild("on_sale", 0);
        $prod->addChild("online_only", 0);
        $prod->addChild("ecotax", 0);
        $prod->addChild("minimal_quantity", 0);
        $prod->addChild("low_stock_threshold", 0);
        $prod->addChild("low_stock_alert", 0);
        $prod->addChild("price", $obj->getBasePrice()->getValue() * 1.784 * 1.23);
        $prod->addChild("wholesale_price", 100.0);
        $prod->addChild("unity");
        $prod->addChild("unit_price", 100.0);
        $prod->addChild("unit_price_ratio");
        $prod->addChild('additional_shipping_cost');
        $prod->addChild('customizable');
        $prod->addChild('text_fields');
        $prod->addChild('uploadable_files');
        $prod->addChild('active', 1);
        $prod->addChild('redirect_type');
        $prod->addChild('id_type_redirected');
        $prod->addChild('available_for_order', 1);
        $prod->addChild('available_date');
        $prod->addChild('show_condition');
        $prod->addChild('condition');
        $prod->addChild('show_price', 1);
        $prod->addChild('indexed');
        $prod->addChild('visibility', 'both');
        $prod->addChild('advanced_stock_management');
        $prod->addChild('pack_stock_type');
        $prod->addChild('meta_description');
        $prod->addChild('meta_keywords');
        $prod->addChild('meta_title');
        $prod->addChild('link_rewrite');
        $name = $prod->addChild("name");
        $pl = $name->addChild("language", $obj->getName("pl"));
        $pl->addAttribute("id", 1);
        $en = $name->addChild("language", $obj->getName("en"));
        $en->addAttribute("id", 2);
        $prod->addChild('description');
        $prod->addChild('description_short');
        $prod->addChild('available_now');
        $prod->addChild('available_later');
        $associations = $prod->addChild("associations");
        $categories = $associations->addChild("categories");
        $category = $categories->addChild("category");
        $category->addChild("id", $obj->getPs_megstyl_pl_parent()->getPs_megstyl_pl_id());
        $accessories = $associations->addChild("accessories");
        $accessories->addAttribute("nodeType", "product");
        $accessories->addAttribute("api", "products");

        if($obj->getSerie() && $obj->getSerie()->getPs_megstyl_pl() && $obj->getSerie()->getPs_megstyl_pl_id())
        {
            $cat = $categories->addChild("category");
            $cat->addChild("id", $obj->getSerie()->getPs_megstyl_pl_id());
        }

        foreach ($obj->getGroups() as $group) {
            if($group->getPs_megstyl_pl() && $group->getPs_megstyl_pl_id())
            {
                $cat = $categories->addChild("category");
                $cat->addChild("id", $group->getPs_megstyl_pl_id());
            }
        }

        if ($obj instanceof ProductSet && "set_items" == "disabled_feature") {
            $this->GetSetItems($associations, $obj);
        }

        $res = $this->ps->post("products", $xml, ["id_shop_group" => 1]);

        $obj->setPs_megstyl_pl_id((int)$res->product->id);
        $obj->save(["skip" => "3rd party integration"]);

        $this->updateImages($obj, clearFirst: false);
        $this->updateSiblings($obj);
        $this->updateFiles($obj, clearFirst: false);
    }

    /**
     * @param SimpleXMLElement|null $associations
     * @param ProductSet $obj
     * @return void
     */
    private function GetSetItems(?SimpleXMLElement $associations, ProductSet $obj): void
    {
        $bundle = $associations->addChild("product_bundle");
        $bundle->addAttribute("nodeType", "product");
        $bundle->addAttribute("api", "products");

        foreach ($obj->getSet() as $lis) {
            $li = $bundle->addChild("product");
            $li->addChild("id", $lis->getElement()->getPs_megstyl_pl_id());
            $li->addChild("id_product_attribute", 0);
            $li->addChild("quantity", $lis->getQuantity());
        }
    }

    private function updateImages(Product|ProductSet $product, bool $clearFirst = true)
    {
        $this->logger->info("Updating images");
        if ($clearFirst) {
            try {
                $currentImages = $this->ps->get("images/products/" . $product->getPs_megstyl_pl_id())->image->declination;

                $this->logger->info("Clear old images");
                $this->logger->info("Found " . $currentImages->count() . " images.");

                foreach ($currentImages as $im) {
                    $imId = $im['id'];
                    $this->ps->delete("images/products/" . $product->getPs_megstyl_pl_id() . "/" . $imId);
                }
            } catch (Throwable $t) {

            }
        }

        $images = [];

        if ($product instanceof Product) {
            $images = array_merge([$product->getImage()], $product->getImages()->getItems(), $product->getImagesModel()->getItems());
        } else if ($product instanceof ProductSet) {
            $images = array_merge([$product->getImage()], $product->getImages()->getItems());
        }

        $this->logger->info("Updating images...");

        foreach ($images as $im) {
            $image = $im->getThumbnail("webp_1600");
            $stream = $image->getStream();

            $tempFile = tempnam(sys_get_temp_dir(), 'pim_image_') . ".webp";
            $this->logger->info($image->getFrontendPath() . " => " . $tempFile);

            file_put_contents($tempFile, stream_get_contents($stream));

            $this->ps->upload("images/products/" . $product->getPs_megstyl_pl_id() . "/", $tempFile, "POST");
        }

        $this->logger->info("Done.");
    }

    private function updateSiblings(Product|ProductSet $obj)
    {
        $this->logger->info("Updating siblings...");

        if (!$obj->getPs_megstyl_pl_id()) {
            $this->logger->warning("Can not update presta product siblings due this product has no presta id");
            return;
        }

        $siblings = $this->getNestedSiblings($obj);
        if (count($siblings) < 2) {
            $this->logger->info("No siblings found for this product");
            return;
        }

        foreach ($siblings as $psProductId) {
            $xml = new SimpleXMLElement("<prestashop xmlns:xlink=\"http://www.w3.org/1999/xlink\"></prestashop>");
            $prod = $xml->addChild("product");

            $prod->addChild("id", $psProductId);

            $associations = $prod->addChild("associations");

            $accessories = $associations->addChild("accessories");
            $accessories->addAttribute("nodeType", "product");
            $accessories->addAttribute("api", "products");

            $s = [];

            foreach ($siblings as $psSiblingId) {
                if ($psSiblingId != $psProductId) {
                    $accessoryProduct = $accessories->addChild("product");
                    $accessoryProduct->addChild("id", $psSiblingId);

                    $s[] = $psSiblingId;
                }
            }

            $this->logger->info("P: " . $psProductId . ", siblings: " . join(", ", $s));

            $this->ps->patch("products/" . $psProductId, $xml);
        }
    }

    private function getNestedSiblings(Product|ProductSet $obj, array $items = []): array
    {
        $superParent = null;
        if ($obj instanceof Product) {
            $superParent = $this->getFirstModelParent($obj);
        } else {
            $superParent = $this->getFirstVirtualSet($obj);
        }

        if ($superParent) {
            $this->logger->debug("[*] Model: " . $superParent->getKey());
        } else {
            $this->logger->warning("[*] No super parent found");
            return [];
        }

        $children = [];

        if ($obj instanceof Product) {
            $children = $this->getNestedActualProductChildren($superParent);
        } else {
            $children = $this->getNestedPrestashopProductSetSiblings($superParent);
        }

        foreach ($children as $child) {
            if ($child->getPs_megstyl_pl() && $child->getPs_megstyl_pl_id()) {
                $this->logger->info("Reference presta product " . $child->getKey() . " with presta id: " . $child->getPs_megstyl_pl_id());
                $items[] = $child->getPs_megstyl_pl_id();
            } else {
                $this->logger->info("Object " . $child->getKey() . " skipped due no presta id found.");
            }
        }

        return $items;
    }

    private function getFirstModelParent(Product $product): Product|null
    {
        $parent = $product->getParent();

        if ($parent instanceof Product) {
            if ($parent->getObjectType() == "MODEL") return $parent;

            if ($parent->getObjectType() == "ACTUAL" || $parent->getObjectType() == "SKU") {
                return $this->getFirstModelParent($parent);
            }
        }

        return null;
    }

    private function getFirstVirtualSet(ProductSet $set): ProductSet|null
    {
        $parent = $set->getParent();

        if ($parent instanceof ProductSet) {
            if (!$parent->getSet()) {
                return $parent;
            }

            foreach($parent->getSet() as $lip)
            {
                /** @var Product $p */
                $p = $lip->getObject();
                if($p->getObjectType() != 'ACTUAL')
                {
                    return $parent;
                }
            }

            return $this->getFirstVirtualSet($parent);
        }

        return null;
    }

    private function getNestedActualProductChildren(Product $product, array $children = []): array
    {
        foreach ($product->getChildren(includingUnpublished: true) as $child) {
            if ($child instanceof Product) {
                if ($child->getObjectType() == "SKU") {
                    $children = array_merge($children, $this->getNestedActualProductChildren($child));
                } elseif ($child->getObjectType() == "ACTUAL") {
                    $children[] = $child;
                    $children = array_merge($children, $this->getNestedActualProductChildren($child));
                }
            }
        }

        return $children;
    }

    private function getNestedPrestashopProductSetSiblings(ProductSet $obj, array $children = []): array
    {
        foreach ($obj->getChildren(includingUnpublished: true) as $child) {
            if ($child instanceof ProductSet) {
                if ($child->getSet()) {
                    $children[] = $child;
                    $children = array_merge($children, $this->getNestedPrestashopProductSetSiblings($child));
                }
            }
        }

        return $children;
    }

    private function updateFiles(Product|ProductSet $product, bool $clearFirst = true)
    {
        if ($product instanceof ProductSet) return;

        $this->logger->info("======= FILES =======");

        if ($clearFirst) {
            $psProduct = $this->ps->get("products/" . $product->getPs_megstyl_pl_id());

            foreach ($psProduct->product->associations->attachments->attachment as $att) {
                $attId = $att->id;
                $this->logger->info("Removing file #" . $attId . "...");
                $this->ps->delete("attachments/" . $attId);
            }
        }

        if (!$product->getInstruction()) {
            return;
        }

        $stream = $product->getInstruction()->getStream();

        $tempFile = tempnam(sys_get_temp_dir(), 'pim_document_') . ".pdf";
        $this->logger->info($product->getInstruction()->getFrontendPath() . " => " . $tempFile);

        file_put_contents($tempFile, stream_get_contents($stream));

        $fileData = $this->ps->uploadFile("attachments/file", $tempFile, filename: "file");

        $fileData->attachment->file_name = $product->getInstruction()->getKey();
        $fileData->attachment->name->xpath("language[@id=1]")[0][0] = "Instrukcja";
        $fileData->attachment->name->xpath("language[@id=2]")[0][0] = "User manual";

        $prodAssoc = $fileData->attachment->associations->products->addChild("product");
        $prodAssoc->addChild("id", $product->getPs_megstyl_pl_id());

        $res = $this->ps->put("attachments/" . $fileData->attachment->id, $fileData);
    }

    private function updateProductInPrestashop(Product|ProductSet $obj)
    {
        $this->updateFields($obj);
        $this->updateSiblings($obj);
        $this->updateImages($obj);
        $this->updateFiles($obj);
    }

    private function updateFields(Product|ProductSet $obj)
    {
        $this->logger->info("Updating fields...");
        if (!$obj->getPs_megstyl_pl_parent()->getPs_megstyl_pl_id()) {
            $this->logger->error("Can not update product data.");
            return;
        }

        $xml = new SimpleXMLElement("<prestashop xmlns:xlink=\"http://www.w3.org/1999/xlink\"></prestashop>");
        $prod = $xml->addChild("product");
        $prod->addChild("id", $obj->getPs_megstyl_pl_id());
        $prod->addChild("id_category_default", $obj->getPs_megstyl_pl_parent()->getPs_megstyl_pl_id());
        $prod->addChild("id_manufacturer", 1);
        $prod->addChild("description_short", $obj->getKey());
        $prod->addChild("id_supplier", 1);
        $prod->addChild("id_category_default", $obj->getPs_megstyl_pl_parent()->getPs_megstyl_pl_id());
        $prod->addChild("reference", $obj->getId());
        $prod->addChild("supplier_reference", $obj->getId() . "-" . $obj->getKey());
        $prod->addChild("width", 0.0000);
        $prod->addChild("height", 0.0000);
        $prod->addChild("depth", 0.0000);
        $prod->addChild("weight", 0.0000);
        $prod->addChild("quantity_discount", 0);
        $prod->addChild("ean13", $obj->getEan());
        $prod->addChild("mpn", $obj->getId());
        $prod->addChild("price", $obj->getBasePrice()->getValue() * 1.784 * 1.23);
        $prod->addChild("unity", 1);
        $prod->addChild("wholesale_price", $obj->getBasePrice()->getValue() * 1.784 * 1.23);
        $prod->addChild("unit_price", $obj->getBasePrice()->getValue() * 1.784 * 1.23);
        $name = $prod->addChild("name");
        $pl = $name->addChild("language", $obj->getName("pl"));
        $pl->addAttribute("id", 1);
        $en = $name->addChild("language", $obj->getName("en"));
        $en->addAttribute("id", 2);
        $associations = $prod->addChild("associations");
        $categories = $associations->addChild("categories");
        $category = $categories->addChild("category");
        $category->addChild("id", $obj->getPs_megstyl_pl_parent()->getPs_megstyl_pl_id());

        if($obj->getSerie() && $obj->getSerie()->getPs_megstyl_pl() && $obj->getSerie()->getPs_megstyl_pl_id())
        {
            $cat = $categories->addChild("category");
            $cat->addChild("id", $obj->getSerie()->getPs_megstyl_pl_id());
        }

        foreach ($obj->getGroups() as $group) {
            if($group->getPs_megstyl_pl() && $group->getPs_megstyl_pl_id())
            {
                $cat = $categories->addChild("category");
                $cat->addChild("id", $group->getPs_megstyl_pl_id());
            }
        }

        if ($obj instanceof ProductSet && "set_items" == "disabled_feature") {
            $this->GetSetItems($associations, $obj);
        }

        $res = $this->ps->patch("products/" . $obj->getPs_megstyl_pl_id(), $xml);
    }

    private function updateProductSet(ProductSet $set): void
    {
        $this->logger->info("Updating [" . $set->getKey() . "]...");

        $psId = DataObject\Service::useInheritedValues(false, function () use ($set) {
            return $set->getPs_megstyl_pl_id();
        });

        if (!$set->isPublished() || (!$set->getPs_megstyl_pl() && $psId)) {
            $this->removeOrDeactivateProductPrestashop($set);
            return;
        }

        if ($set->isPublished() && $set->getPs_megstyl_pl() && !$psId) {
            $this->addProductSetInPrestashop($set);
            return;
        }

        if ($set->isPublished() && $set->getPs_megstyl_pl() && $psId) {
            $this->updateProductSetInPrestashop($set);
            return;
        }

        $this->logger->info("Done.");
    }

    private function addProductSetInPrestashop(ProductSet $set)
    {
        $this->addProductInPrestashop($set);
    }

    private function updateProductSetInPrestashop(ProductSet $set)
    {
        $this->updateProductInPrestashop($set);
    }
}
