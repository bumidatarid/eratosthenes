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
}