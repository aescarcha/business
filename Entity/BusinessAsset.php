<?php

namespace Aescarcha\BusinessBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * BusinessAsset
 *
 * @ORM\Table(name="business_assets")
 * @ORM\Entity(repositoryClass="Aescarcha\BusinessBundle\Repository\BusinessAssetRepository")
 */
class BusinessAsset
{

    use SoftDeleteableEntity;
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var string

     * @Assert\Length(min="3", max="255")
     
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var Entity\Business
     *
     * @ORM\ManyToOne(targetEntity="Aescarcha\BusinessBundle\Entity\Business", fetch="EAGER")
     * @Assert\NotNull()
     */
    private $business;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=32, nullable=true, options={"default" : "image"})
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @var int
     *
     * @ORM\Column(name="width", type="integer", nullable=true)
     */
    private $width;

    /**
     * @var int
     *
     * @ORM\Column(name="height", type="integer", nullable=true)
     */
    private $height;

    /**
     * @var int
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=false)
     */
    private $order = 1;

    /**
     * @var int
     *
     * @ORM\Column(name="isThumb", type="integer", nullable=false, options={"default" : 0})
     */
    private $isThumb = 0;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return BusinessAsset
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set business
     *
     * @param guid $business
     *
     * @return BusinessAsset
     */
    public function setBusiness($business)
    {
        $this->business = $business;

        return $this;
    }

    /**
     * Get business
     *
     * @return Entity\Business
     */
    public function getBusiness()
    {
        return $this->business;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return BusinessAsset
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set path
     *
     * @param string $path
     *
     * @return BusinessAsset
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set width
     *
     * @param integer $width
     *
     * @return BusinessAsset
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     *
     * @return BusinessAsset
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }


    /**
     * Set order
     *
     * @param integer $order
     *
     * @return BusinessAsset
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return int $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set isThumb
     *
     * @param integer $isThumb
     *
     * @return BusinessAsset
     */
    public function setIsThumb($isThumb)
    {
        $this->isThumb = $isThumb;

        return $this;
    }

    /**
     * Get isThumb
     *
     * @return int isThumb
     */
    public function getIsThumb()
    {
        return $this->isThumb;
    }
}

