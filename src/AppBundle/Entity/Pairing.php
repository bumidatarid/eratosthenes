<?php

namespace AppBundle\Entity;
use Exception;

class Pairing
{
    private $measurement1;
    private $measurement2;

    public static function new($measurement1, $measurement2)
    {
        $latitude1 = $measurement1->getLatitude();
        $latitude2 = $measurement2->getLatitude();

        if (abs($latitude1 - $latitude2) <= 15) {
            throw new Exception('Invalid');
        }
        return new self($measurement1, $measurement2);
    }

    public static function newWithEquinox($measurement)
    {
        $latitude = $measurement->getLatitude();

        if (abs($latitude) <= 1) {
            throw new Exception('Invalid');
        }

        if (
            $measurement->getDate()->format('m-d') != '09-23'
            and $measurement->getDate()->format('m-d') != '03-20'
        )
        {
            throw new Exception('Invalid');
        }

        $equinox = Measurement::newEquinox();
        return new self($measurement, $equinox);
    }

    private function __construct($measurement1, $measurement2) {
        if (abs($measurement1->getAngle() - $measurement2->getAngle()) == 0) {
            throw new Exception('Invalid');
        }
        $this->measurement1 = $measurement1;
        $this->measurement2 = $measurement2;
    }

    public function getId()
    {
        return sprintf('%s-%s', $this->measurement1->getId(), $this->measurement2->getId());
    }

    public function getLatitude1()
    {
        return $this->measurement1->getLatitude();
    }

    public function getLatitude2()
    {
        return $this->measurement2->getLatitude();
    }

    public function getAverageLatitude()
    {
        return ($this->getLatitude1() + $this->getLatitude2())/2;
    }

    public function getSliceId()
    {
        return round($this->getAverageLatitude() / 5);
    }

    private $circumference;
    public function getCircumference()
    {
        if ($this->circumference) {
            return $this->circumference;
        }
        $latitude1 = $this->measurement1->getLatitude();
        $latitude2 = $this->measurement2->getLatitude();
        $angle1 = $this->measurement1->getAngle();
        $angle2 = $this->measurement2->getAngle();

        $latdiff = abs($latitude1 - $latitude2);
        $angdiff = abs($angle1 - $angle2);
        $distance = 111 * $latdiff;
        $circumference = $distance * 360 / $angdiff;
        $this->circumference = $circumference;
        return $circumference;
    }

    public function getErrorSum()
    {
        return $this->measurement1->getError() + $this->measurement2->getError();
    }

    private $sunDistance;
    public function getSunDistance()
    {
        if ($this->sunDistance) {
            return $this->sunDistance;
        }

        $latitude1 = $this->measurement1->getLatitude();
        $latitude2 = $this->measurement2->getLatitude();
        $angle1 = $this->measurement1->getAngle();
        $angle2 = $this->measurement2->getAngle();

        $latdiff = abs($latitude1 - $latitude2);
        $distance = 111 * $latdiff;
        $sunDistance = $distance / abs((tan(deg2rad($angle1))-tan(deg2rad($angle2))));

        $this->sunDistance = $sunDistance;
        return $sunDistance;
    }

    /**
     * Gets the value of measurement1.
     *
     * @return mixed
     */
    public function getMeasurement1()
    {
        return $this->measurement1;
    }

    /**
     * Sets the value of measurement1.
     *
     * @param mixed $measurement1 the measurement1
     *
     * @return self
     */
    public function setMeasurement1($measurement1)
    {
        $this->measurement1 = $measurement1;

        return $this;
    }

    /**
     * Gets the value of measurement2.
     *
     * @return mixed
     */
    public function getMeasurement2()
    {
        return $this->measurement2;
    }

    /**
     * Sets the value of measurement2.
     *
     * @param mixed $measurement2 the measurement2
     *
     * @return self
     */
    public function setMeasurement2($measurement2)
    {
        $this->measurement2 = $measurement2;

        return $this;
    }

    /**
     * Sets the value of circumference.
     *
     * @param mixed $circumference the circumference
     *
     * @return self
     */
    public function setCircumference($circumference)
    {
        $this->circumference = $circumference;

        return $this;
    }

    /**
     * Sets the value of sunDistance.
     *
     * @param mixed $sunDistance the sun distance
     *
     * @return self
     */
    public function setSunDistance($sunDistance)
    {
        $this->sunDistance = $sunDistance;

        return $this;
    }
}