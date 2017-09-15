<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\Pairing;
use Math\Combinatorics\Combination;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Oefenweb\Statistics\Statistics;
use Exception;

class CalculateCommand extends Command
{
    private $pairings = [];
    private $circumferences = [];
    private $sunDistances = [];

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

        foreach ($dates as $date => $members) {
            $this->splitToPairings($date, $members);
        }

        foreach ($this->pairings as $pairing) {
            $circumference = $pairing->getCircumference();
            $this->circumferences[] = $circumference;
            $sunDistance = $pairing->getSunDistance();
            $this->sunDistances[] = $sunDistance;
        }

        $cmean = Statistics::mean($this->circumferences);
        $cstddev = Statistics::standardDeviation($this->circumferences);

        $cmax = $cmean + $cstddev;
        $cmin = $cmean - $cstddev;

        foreach ($this->pairings as $pairing) {
            $circumference = $pairing->getCircumference();
            if ($circumference < $cmin or $circumference > $cmax) {
                // dump($pairing);
            }
        }

        $smean = Statistics::mean($this->sunDistances);
        $sstddev = Statistics::standardDeviation($this->sunDistances);

        $output->writeln(sprintf('Globe earth circumference, mean: %s km, stddev: %s', $cmean, $cstddev));
        $output->writeln(sprintf('Flat earth sun distance, mean: %s km, stddev: %s', $smean, $sstddev));
        $output->writeln(sprintf('Total data pairings: %s', count($this->circumferences)));

    }

    protected function splitToPairings($date, $members)
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
                $this->pairings[] = $pairing;
            } catch (ContextErrorException $e) {
                continue;
            }
        }
    }

}
