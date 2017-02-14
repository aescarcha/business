<?php

namespace Aescarcha\BusinessBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Business
 *
 * @ORM\Table(name="business")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 * @ORM\Entity(repositoryClass="Aescarcha\BusinessBundle\Repository\BusinessRepository")
 */
class Business
{
    use SoftDeleteableEntity;
    
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Aescarcha\UserBundle\Entity\User", fetch="EAGER")
     * @Assert\NotNull()
     */
    protected $user;


    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Length(min="3", max="255")
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=500, nullable=true)
     */
    private $description;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="float", nullable=true)
     */
    private $latitude;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="float", nullable=true)
     */
    private $longitude;

    /**
     * @ORM\OneToMany(targetEntity="BusinessAsset", mappedBy="business", fetch="EXTRA_LAZY")
     * @Assert\Count(
     *      min = "0",
     *      max = "500",
     *      minMessage = "You must add at least one Asset",
     *      maxMessage = "You cannot add more than {{ limit }} assets"
     *      )
     */
    private $businessAssets;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    protected $created;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime")
     */
    protected $updated;

    public function __construct()
    {
        $this->businessAssets = new ArrayCollection();
    }


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
     * Set name
     *
     * @param string $name
     *
     * @return Business
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Business
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set latitude
     *
     * @param float $latitude
     *
     * @return Business
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     *
     * @return Business
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set user
     *
     * @param \Aescarcha\UserBundle\Entity\User $user
     * @return Business
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Aescarcha\UserBundle\Entity\User $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Post
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Post
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Get BusinessAssets
     *
     * @return ArrayCollection BusinessAsset
     */
    public function getBusinessAssets()
    {
        return $this->businessAssets;
    }

    /**
     * Get BusinessAssets
     *
     * @param ArrayCollection BusinessAsset
     * @return Business
     */
    public function setBusinessAssets( ArrayCollection $businessAssets )
    {
        $this->businessAssets = $businessAssets;
        return $this;
    }

    /**
     * Add BusinessAssets
     *
     * @param BusinessAsset BusinessAsset
     * @return Business
     */
    public function addBusinessAsset( BusinessAsset $businessAsset )
    {
        $this->businessAssets->add($businessAsset);
        return $this;
    }

    /**
     * Get the thumbnail from the asset collection
     * @return BusinessAsset
     */
    public function getThumb()
    {
        return $this->getBusinessAssets()->where('isThumb', 1);
    }

}

