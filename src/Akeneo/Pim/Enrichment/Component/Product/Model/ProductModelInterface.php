<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Category\Infrastructure\Component\Classification\CategoryAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Model\StateUpdatedAware;
use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Product model interface.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface ProductModelInterface extends
    EntityWithValuesInterface,
    TimestampableInterface,
    VersionableInterface,
    CategoryAwareInterface,
    EntityWithFamilyVariantInterface,
    EntityWithAssociationsInterface,
    EntityWithQuantifiedAssociationsInterface,
    StateUpdatedAware
{
    /**
     * Gets the ID of the product model.
     */
    public function getId(): ?int;

    /**
     * Gets the identifier of the product model.
     *
     * @return string
     */
    public function getCode(): ?string;

    /**
     * Sets the product model identifier.
     */
    public function setCode(string $code): ProductModelInterface;

    /**
     * Gets the products of the product model.
     */
    public function getProducts(): Collection;

    /**
     * Adds an product to the product model.
     *
     *
     * @throws \LogicException
     *
     */
    public function addProduct(ProductInterface $product): ProductModelInterface;

    /**
     * Removes an product from the product model.
     *
     *
     */
    public function removeProduct(ProductInterface $product): ProductModelInterface;

    /**
     * If a node is a tree root, it's the tree starting point and therefore
     * defines the tree itself.
     */
    public function isRoot(): bool;


    /**
     * Adds a child product model to this product model.
     *
     *
     */
    public function addProductModel(ProductModelInterface $productModel): ProductModelInterface;

    /**
     * Removes a child product model from this product model.
     *
     *
     */
    public function removeProductModel(ProductModelInterface $productModel): ProductModelInterface;

    /**
     * Predicates to know if this product model has children product models.
     */
    public function hasProductModels(): bool;

    /**
     * Gets the children product model of this product model.
     */
    public function getProductModels(): Collection;

    public function isRootProductModel(): bool;

    /**
     * Get product model label
     *
     *
     */
    public function getLabel(?string $localeCode = null, ?string $scopeCode = null): string;

    /**
     * Get product model image
     */
    public function getImage(): ?ValueInterface;

    /**
     * Return the categories for the current level
     */
    public function getCategoriesForCurrentLevel(): Collection;
}
