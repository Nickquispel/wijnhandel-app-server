<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * Orderline
 *
 * @ORM\Table(name="orderline", indexes={@ORM\Index(name="order_id", columns={"order_id"})})
 * @ORM\Entity
 * @ApiResource(
 *     collectionOperations={"get","post"},
 *     itemOperations={"get"}
 * )
 */
class Orderline
{
    /**
     * @var int
     *
     * @ORM\Column(name="orderline_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $orderlineId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="orderlinesequence", type="integer", nullable=true)
     */
    private $orderlinesequence;

    /**
     * @var string|null
     *
     * @ORM\Column(name="product_name", type="string", length=255, nullable=true)
     */
    private $productName;

    /**
     * @var int|null
     *
     * @ORM\Column(name="qty", type="integer", nullable=true)
     */
    private $qty;

    /**
     * @var float|null
     *
     * @ORM\Column(name="price_each", type="float", precision=10, scale=0, nullable=true)
     */
    private $priceEach;

    /**
     * @var \Order
     *
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="orderLines")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="order_id")
     * 
     */
    private $order;

    public function getOrderlineId(): ?int
    {
        return $this->orderlineId;
    }

    public function getOrderlinesequence(): ?int
    {
        return $this->orderlinesequence;
    }

    public function setOrderlinesequence(?int $orderlinesequence): self
    {
        $this->orderlinesequence = $orderlinesequence;

        return $this;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(?string $productName): self
    {
        $this->productName = $productName;

        return $this;
    }

    public function getQty(): ?int
    {
        return $this->qty;
    }

    public function setQty(?int $qty): self
    {
        $this->qty = $qty;

        return $this;
    }

    public function getPriceEach(): ?float
    {
        return $this->priceEach;
    }

    public function setPriceEach(?float $priceEach): self
    {
        $this->priceEach = $priceEach;

        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;

        return $this;
    }


}
