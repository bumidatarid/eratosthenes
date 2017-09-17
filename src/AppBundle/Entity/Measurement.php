<?php

namespace AppBundle\Entity;
use DateTime;

class Measurement
{
    private $id;
    private $city;
    private $country;
    private $school;
    private $latitude;
    private $longitude;
    private $date;
    private $gnomon;
    private $shadow;
    private $angle;
    private $error;

    public static function newFromLine($line)
    {
        list($id, $city, $country, $school, $latitude, $longitude, $date, $gnomon, $shadow, $angle1, $angle2, $angleMeasured, $angle, $error) = explode('|', $line);
        $measurement = (new self)
            ->setId($id)
            ->setCity($city)
            ->setCountry($country)
            ->setSchool($school)
            ->setLatitude($latitude)
            ->setLongitude($longitude)
            ->setDate(new DateTime($date))
            ->setGnomon($gnomon)
            ->setShadow($shadow)
            ->setAngle($angle)
            ->setError($error)
        ;
        return $measurement;
    }

    public static function newEquinox()
    {
        $equinox = (new self)
            ->setId('e')
            ->setCity('Pontianak')
            ->setCountry('Indonesia')
            ->setSchool('-')
            ->setLatitude(0)
            ->setLongitude(0)
            ->setDate(new DateTime('2016-09-23'))
            ->setGnomon(0)
            ->setShadow(0)
            ->setAngle(0)
            ->setError(0)
        ;
        return $equinox;
    }

    private function __construct() {}

    // public function getMonthDate()
    // {
    //     return $this->date->format('m-d');
    // }

    public function getMonthDateRange()
    {
        return $this->getMonthDateRange1();
    }

    public function getMonthDateRange1()
    {
        return [
            (clone $this->date)->format('m-d'),
        ];
    }

    public function getMonthDateRange3()
    {
        return [
            (clone $this->date)->modify('-1 day')->format('m-d'),
            (clone $this->date)->format('m-d'),
            (clone $this->date)->modify('+1 day')->format('m-d'),
        ];
    }

    public function getMonthDateRange5()
    {
        return [
            (clone $this->date)->modify('-2 day')->format('m-d'),
            (clone $this->date)->modify('-1 day')->format('m-d'),
            (clone $this->date)->format('m-d'),
            (clone $this->date)->modify('+1 day')->format('m-d'),
            (clone $this->date)->modify('+2 day')->format('m-d'),
        ];
    }

    public function getMonthDateRange7()
    {
        return [
            (clone $this->date)->modify('-3 day')->format('m-d'),
            (clone $this->date)->modify('-2 day')->format('m-d'),
            (clone $this->date)->modify('-1 day')->format('m-d'),
            (clone $this->date)->format('m-d'),
            (clone $this->date)->modify('+1 day')->format('m-d'),
            (clone $this->date)->modify('+2 day')->format('m-d'),
            (clone $this->date)->modify('+3 day')->format('m-d'),
        ];
    }

    /**
     * Gets the value of city.
     *
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Sets the value of city.
     *
     * @param mixed $city the city
     *
     * @return self
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Gets the value of country.
     *
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Sets the value of country.
     *
     * @param mixed $country the country
     *
     * @return self
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Gets the value of school.
     *
     * @return mixed
     */
    public function getSchool()
    {
        return $this->school;
    }

    /**
     * Sets the value of school.
     *
     * @param mixed $school the school
     *
     * @return self
     */
    public function setSchool($school)
    {
        $this->school = $school;

        return $this;
    }

    /**
     * Gets the value of latitude.
     *
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Sets the value of latitude.
     *
     * @param mixed $latitude the latitude
     *
     * @return self
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Gets the value of longitude.
     *
     * @return mixed
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Sets the value of longitude.
     *
     * @param mixed $longitude the longitude
     *
     * @return self
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Gets the value of date.
     *
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Sets the value of date.
     *
     * @param mixed $date the date
     *
     * @return self
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Gets the value of gnomon.
     *
     * @return mixed
     */
    public function getGnomon()
    {
        return $this->gnomon;
    }

    /**
     * Sets the value of gnomon.
     *
     * @param mixed $gnomon the gnomon
     *
     * @return self
     */
    public function setGnomon($gnomon)
    {
        $this->gnomon = $gnomon;

        return $this;
    }

    /**
     * Gets the value of shadow.
     *
     * @return mixed
     */
    public function getShadow()
    {
        return $this->shadow;
    }

    /**
     * Sets the value of shadow.
     *
     * @param mixed $shadow the shadow
     *
     * @return self
     */
    public function setShadow($shadow)
    {
        $this->shadow = $shadow;

        return $this;
    }

    /**
     * Gets the value of angle.
     *
     * @return mixed
     */
    public function getAngle()
    {
        return $this->angle;
    }

    /**
     * Sets the value of angle.
     *
     * @param mixed $angle the angle
     *
     * @return self
     */
    public function setAngle($angle)
    {
        $this->angle = $angle;

        return $this;
    }

    /**
     * Gets the value of id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the value of id.
     *
     * @param mixed $id the id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets the value of error.
     *
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Sets the value of error.
     *
     * @param mixed $error the error
     *
     * @return self
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }
}