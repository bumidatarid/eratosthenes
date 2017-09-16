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

        $z = 1.96; // 95%
        $cn = count($this->circumferences);
        $cmean = Statistics::mean($this->circumferences);
        $cstddev = Statistics::standardDeviation($this->circumferences, false);
        $cci = $z * $cstddev / sqrt($cn);

        foreach ($this->pairings as $pairing) {
            $circumference = $pairing->getCircumference();
        }

        $sn = count($this->sunDistances);
        $smean = Statistics::mean($this->sunDistances);
        $sstddev = Statistics::standardDeviation($this->sunDistances, false);
        $sci = $z * $sstddev / sqrt($sn);

        $output->writeln(sprintf('GLOBE EARTH CIRCUMFERENCE'));
        $output->writeln(sprintf('mean: %s', $cmean));
        $output->writeln(sprintf('stddev: %s', $cstddev));
        $output->writeln(sprintf('n: %s', $cn));
        $output->writeln(sprintf('confidence interval: %s ± %s', $cmean, $cci));
        $output->writeln(sprintf('FLAT EARTH SUN DISTANCE'));
        $output->writeln(sprintf('mean: %s', $smean));
        $output->writeln(sprintf('stddev: %s', $sstddev));
        $output->writeln(sprintf('n: %s', $sn));
        $output->writeln(sprintf('confidence interval: %s ± %s', $smean, $sci));
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
