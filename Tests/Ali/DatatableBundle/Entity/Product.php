<?php

namespace Ali\DatatableBundle\Entity;

use Doctrine\ORM\Mappin as ORM;

/**
 * @Entity
 * @Table(name="products")
 */
class Product
{

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="string", length=100)
     */
    protected $name;

    /**
     * @Column(type="decimal", scale=2)
     */
    protected $price;

    /**
     * @Column(type="text")
     */
    protected $description;

    /**
     * @ORMM\OneToMany(targetEntity="Feature", mappedBy="product")
     * */
    protected $features;

    public function __construct()
    {
        $this->features = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getFeatures()
    {
        return $this->features;
    }

    public function setFeatures($features)
    {
        $this->features = $features;
        return $this;
    }

}