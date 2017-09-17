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

class CalculateCommand extends Command
{
    private $population;
    private $circumferences = [];
    private $sunDistances = [];
    private $slices = [];
    private $sliceCalculations = [];

    protected function configure()
    {
        $this
            ->setName('app:calculate')
            ->setDescription('Calculate data')
            ->setHelp('Calculate')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = file_get_contents('input.csv');
        $lines = explode("\n", $data);
        $measurements = [];
        foreach ($lines as $line) {
            $measurements[] = Measurement::newFromLine($line);
        }

        $dates = [];
        foreach ($measurements as $measurement) {
            // $dates[$measurement->getMonthDate()][] = $measurement;
            foreach ($measurement->getMonthDateRange() as $date) {
                $dates[$date][$measurement->getId()] = $measurement;
            }
        }

        $population = new Population;
        foreach ($dates as $date => $members) {
            $this->population = $this->saveToPopulation($population, $date, $members);
        }

        $population->calculate();
        $population->printData($output);
    }

    protected function saveToPopulation($population, $date, $members)
    {
        // dump([$date, count($members)]);
        $combinations = Combination::get($members, 2);
        foreach ($combinations as $combination) {
            if (count($combination) < 2) continue;
            try {
                $pairing = new Pairing(array_pop($combination), array_pop($combination));
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
