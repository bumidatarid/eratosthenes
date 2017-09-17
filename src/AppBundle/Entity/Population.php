<?php

namespace AppBundle\Entity;
use Oefenweb\Statistics\Statistics;
use AppBundle\Entity\Pairing;

class Population
{
    private $pairings = [];
    private $circumferences = [];
    private $sunDistances = [];
    private $z = 1.96;
    private $circumferenceN;
    private $circumferenceMean;
    private $circumferenceStandardDeviation;
    private $circumferenceConfidenceInterval;
    private $circumferenceCoefficientOfVariation;
    private $sunDistanceN;
    private $sunDistanceMean;
    private $sunDistanceStandardDeviation;
    private $sunDistanceConfidenceInterval;
    private $sunDistanceCoefficientOfVariation;

    public function calculate()
    {
        foreach ($this->pairings as $pairing) {
            $circumference = $pairing->getCircumference();
            $this->circumferences[] = $circumference;
            $sunDistance = $pairing->getSunDistance();
            $this->sunDistances[] = $sunDistance;
        }

        $this->circumferenceN = count($this->circumferences);
        $this->circumferenceMean = Statistics::mean($this->circumferences);
        $this->circumferenceStandardDeviation = Statistics::standardDeviation($this->circumferences, false);
        $this->circumferenceCoefficientOfVariation = $this->z * $this->circumferenceStandardDeviation / sqrt($this->circumferenceN);
        $this->circumferenceCoefficientOfVariation = $this->circumferenceStandardDeviation / $this->circumferenceMean;

        $this->sunDistanceN = count($this->sunDistances);
        $this->sunDistanceMean = Statistics::mean($this->sunDistances);
        $this->sunDistanceStandardDeviation = Statistics::standardDeviation($this->sunDistances, false);
        $this->sunDistanceCoefficientOfVariation = $this->z * $this->sunDistanceStandardDeviation / sqrt($this->sunDistanceN);
        $this->sunDistanceCoefficientOfVariation = $this->sunDistanceStandardDeviation / $this->sunDistanceMean;
    }

    public function printData($output)
    {
        $output->writeln(sprintf('GLOBE EARTH CIRCUMFERENCE'));
        $output->writeln(sprintf('mean: %s', $this->circumferenceMean));
        $output->writeln(sprintf('stddev: %s', $this->circumferenceStandardDeviation));
        $output->writeln(sprintf('n: %s', $this->circumferenceN));
        $output->writeln(sprintf('confidence interval: %s ± %s', $this->circumferenceMean, $this->circumferenceConfidenceInterval));
        $output->writeln(sprintf('coefficient of variance: %s', $this->circumferenceCoefficientOfVariation));

        $output->writeln(sprintf('FLAT EARTH SUN DISTANCE'));
        $output->writeln(sprintf('mean: %s', $this->sunDistanceMean));
        $output->writeln(sprintf('stddev: %s', $this->sunDistanceStandardDeviation));
        $output->writeln(sprintf('n: %s', $this->sunDistanceN));
        $output->writeln(sprintf('confidence interval: %s ± %s', $this->sunDistanceMean, $this->sunDistanceConfidenceInterval));
        $output->writeln(sprintf('coefficient of variance: %s', $this->sunDistanceCoefficientOfVariation));
    }

    public function sliceData()
    {
        $slices = [];
        foreach ($this->pairings as $pairing) {
            $sliceId = $pairing->getSliceId();
            if (!isset($slices[$sliceId])) {
                $slices[$sliceId] = new self;
            }
            $slices[$sliceId]->addPairing($pairing);
        }
        ksort($slices);
        return $slices;
    }

    public function saveLatitudeToSunDistanceData($filename)
    {
        $f = fopen($filename, 'w');
        foreach ($this->pairings as $pairing) {
            fwrite($f, sprintf("%s %s\n", $pairing->getAverageLatitude(), $pairing->getSunDistance()));
        }
        fclose($f);
    }

    public function saveLatitudeToCircumferenceData($filename)
    {
        $f = fopen($filename, 'w');
        foreach ($this->pairings as $pairing) {
            fwrite($f, sprintf("%s %s\n", $pairing->getAverageLatitude(), $pairing->getCircumference()));
        }
        fclose($f);
    }

    public function addPairing(Pairing $pairing)
    {
        $this->pairings[] = $pairing;
        return $this;
    }


    /**
     * Gets the value of circumferences.
     *
     * @return mixed
     */
    public function getCircumferences()
    {
        return $this->circumferences;
    }

    /**
     * Sets the value of circumferences.
     *
     * @param mixed $circumferences the circumferences
     *
     * @return self
     */
    public function setCircumferences($circumferences)
    {
        $this->circumferences = $circumferences;

        return $this;
    }

    /**
     * Gets the value of sunDistances.
     *
     * @return mixed
     */
    public function getSunDistances()
    {
        return $this->sunDistances;
    }

    /**
     * Sets the value of sunDistances.
     *
     * @param mixed $sunDistances the sun distances
     *
     * @return self
     */
    public function setSunDistances($sunDistances)
    {
        $this->sunDistances = $sunDistances;

        return $this;
    }

    /**
     * Gets the value of sunDistanceMean.
     *
     * @return mixed
     */
    public function getSunDistanceMean()
    {
        return $this->sunDistanceMean;
    }

    /**
     * Sets the value of sunDistanceMean.
     *
     * @param mixed $sunDistanceMean the sun distance mean
     *
     * @return self
     */
    public function setSunDistanceMean($sunDistanceMean)
    {
        $this->sunDistanceMean = $sunDistanceMean;

        return $this;
    }

    /**
     * Gets the value of sunDistanceStandardDeviation.
     *
     * @return mixed
     */
    public function getSunDistanceStandardDeviation()
    {
        return $this->sunDistanceStandardDeviation;
    }

    /**
     * Sets the value of sunDistanceStandardDeviation.
     *
     * @param mixed $sunDistanceStandardDeviation the sun distance standard deviation
     *
     * @return self
     */
    public function setSunDistanceStandardDeviation($sunDistanceStandardDeviation)
    {
        $this->sunDistanceStandardDeviation = $sunDistanceStandardDeviation;

        return $this;
    }
}