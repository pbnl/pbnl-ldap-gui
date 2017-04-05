<?php

/**
 * Created by PhpStorm.
 * User: paul
 * Date: 05.04.17
 * Time: 14:03
 */

namespace AppBundle\Entity\material;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="MaterialPiece")
 */
class MaterialPiece
{
    /**
     * @ORM\Column(type="string", length=200)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="array")
     */
    private $offersIds;

    /**
     * Set name
     *
     * @param string $name
     *
     * @return materialPiece
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set offersIds
     *
     * @param array $offersIds
     *
     * @return materialPiece
     */
    public function setOffersIds($offersIds)
    {
        $this->offersIds = $offersIds;

        return $this;
    }

    /**
     * Get offersIds
     *
     * @return array
     */
    public function getOffersIds()
    {
        return $this->offersIds;
    }
}
