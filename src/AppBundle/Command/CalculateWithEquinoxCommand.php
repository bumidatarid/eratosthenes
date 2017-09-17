<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\Pairing;
use AppBundle\Entity\Population;
use Math\Combinatorics\Combination;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Exception;

class CalculateWithEquinoxCommand extends Command
{
    private $population;
    private $circumferences = [];
    private $sunDistances = [];
    private $slices = [];
    private $sliceCalculations = [];

    protected function configure()
    {
        $this
            ->setName('app:calculatewithequinox')
            ->setDescription('Calculate data with equinox pairing')
            ->setHelp('Calculate')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = file_get_contents('input.csv');
        $lines = explode("\n", $data);
        foreach ($lines as $line) {
            $measurements[] = Measurement::newFromLine($line);
        }

        $population = new Population;
        foreach ($measurements as $measurement) {
            try {
                $pairing = Pairing::newWithEquinox($measurement);
                $population->addPairing($pairing);
            } catch (Exception $e) {}
        }

        $population->calculate();
        $population->printData($output);
        $population->saveLatitudeToSunDistanceEquinoxData('equinoxsundistance.dat');
        $population->saveLatitudeToCircumferenceEquinoxData('equinoxcircumference.dat');
    }

    protected function saveToPopulation($population, $date, $members)
    {
        // dump([$date, count($members)]);
        $combinations = Combination::get($members, 2);
        foreach ($combinations as $combination) {
            if (count($combination) < 2) continue;
            try {
                $pairing = Pairing::new(array_pop($combination), array_pop($combination));
                if (count($combination) > 0) {
                    continue;
                }
                $population->addPairing($pairing);
            } catch (Exception $e) {
                // echo $e->getMessage();
                continue;
            }
        }
    }

}
