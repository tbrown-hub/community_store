<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group as StoreGroup;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;

/**
 * @Entity
 * @Table(name="CommunityStoreProductGroups")
 */
class ProductGroup
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $pgID;

    /**
     * @Column(type="integer")
     */
    protected $pID;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product",inversedBy="groups",cascade={"persist"})
     * @JoinColumn(name="pID", referencedColumnName="pID", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @Column(type="integer")
     */
    protected $gID;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group")
     * @JoinColumn(name="gID", referencedColumnName="gID", onDelete="CASCADE")
     */
    protected $group;

    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup($group)
    {
        $this->group = $group;
    }

    private function setProductID($pID)
    {
        $this->pID = $pID;
    }

    private function setGroupID($gID)
    {
        $this->gID = $gID;
    }

    public function getProductID()
    {
        return $this->pID;
    }

    public function setProduct($product)
    {
        return $this->product = $product;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function getGroupID()
    {
        return $this->gID;
    }

    public function getID()
    {
        return $this->id;
    }

    public function setID($id)
    {
        $this->id = $id;
    }

    public static function getByID($pgID)
    {
        $em = \ORM::entityManager();

        return $em->find(get_class(), $pgID);
    }

    public static function getGroupsForProduct(StoreProduct $product)
    {
        $em = \ORM::entityManager();
        $productGroups = $em->getRepository(get_class())->findBy(['pID' => $product->getID()]);
        $groups = [];
        if (count($productGroups)) {
            foreach ($productGroups as $productGroup) {
                $groups[] = $productGroup->getGroup();
            }
        }

        return $groups;
    }

    public static function isProductInGroup(StoreProduct $product, StoreGroup $group)
    {
        $em = \ORM::entityManager();
        $gID = $group->getGroupID();

        $productGroup = $em->getRepository(get_class())->findBy(['pID' => $product->getID(), 'gID' => $gID]);
        if (count($productGroup)) {
            return true;
        }

        return false;
    }

    public static function getGroupIDsForProduct(StoreProduct $product)
    {
        $groups = self::getGroupsForProduct($product);
        $ids = [];
        if (count($groups)) {
            foreach ($groups as $g) {
                $ids[] = $g->getGroupID();
            }
        }

        return $ids;
    }

    public static function addGroupsForProduct(array $data, StoreProduct $product)
    {
        self::removeGroupsForProduct($product);
        //add new ones.
        if (!empty($data['pProductGroups'])) {
            foreach ($data['pProductGroups'] as $gID) {
                self::add($product, $gID);
            }
        }
    }

    public static function removeGroupsForProduct(StoreProduct $product)
    {
        $em = \ORM::entityManager();
        $groups = $em->getRepository(get_class())->findBy(['pID' => $product->getID()]);
        foreach ($groups as $productGroup) {
            $productGroup->delete();
        }
    }

    public static function removeProductsForGroup(StoreGroup $group)
    {
        $em = \ORM::entityManager();
        $groups = $em->getRepository(get_class())->findBy(['gID' => $group->getID()]);
        foreach ($groups as $productGroup) {
            $productGroup->delete();
        }
    }

    public static function add($product, $gID)
    {
        if (!is_object($product)) {
            $product = Product::getByID($product);
        }

        if ($product) {
            $group = StoreGroup::getByID($gID);
            if ($group) {
                $productGroup = new self();
                $productGroup->setProduct($product);
                $productGroup->setGroup($group);
                $productGroup->save();
            }

            return $productGroup;
        }

        return false;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->setID(null);
            $this->setProductID(null);
        }
    }

    public function save()
    {
        $em = \ORM::entityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = \ORM::entityManager();
        $em->remove($this);
        $em->flush();
    }
}
