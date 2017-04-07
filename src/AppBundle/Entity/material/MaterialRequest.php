<?php

/**
 * Created by PhpStorm.
 * User: paul
 * Date: 05.04.17
 * Time: 14:04
 */

namespace AppBundle\Entity\material;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="MaterialRequest")
 */
class MaterialRequest
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $stamm;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $materialPieceID;

    /**
     * @ORM\Column(type="decimal", scale=0)
     */
    private $quantity;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="decimal", scale=0)
     */
    private $requestYear;

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
     * Set stamm
     *
     * @param string $stamm
     *
     * @return materialProposal
     */
    public function setStamm($stamm)
    {
        $this->stamm = $stamm;

        return $this;
    }

    /**
     * Get stamm
     *
     * @return string
     */
    public function getStamm()
    {
        return $this->stamm;
    }

    /**
     * Set quantity
     *
     * @param string $quantity
     *
     * @return materialProposal
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return materialProposal
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }


    /**
     * Set materialPieceID
     *
     * @param string $materialPieceID
     *
     * @return materialProposal
     */
    public function setMaterialPieceID($materialPieceID)
    {
        $this->materialPieceID = $materialPieceID;

        return $this;
    }

    /**
     * Get materialPieceID
     *
     * @return string
     */
    public function getMaterialPieceID()
    {
        return $this->materialPieceID;
    }

    /**
     * Set requestYear
     *
     * @param string $requestYear
     *
     * @return MaterialRequest
     */
    public function setRequestYear($requestYear)
    {
        $this->requestYear = $requestYear;

        return $this;
    }

    /**
     * Get requestYear
     *
     * @return string
     */
    public function getRequestYear()
    {
        return $this->requestYear;
    }
}
