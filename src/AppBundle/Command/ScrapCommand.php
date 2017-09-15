<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use InvalidArgumentException;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use DateTime;
use Exception;

class ScrapCommand extends Command
{
    private $client;
    private $crawler;
    private $output;
    private $data = [];

    private static function DMStoDD($input)
    {
        $deg = " " ;
        $min = " " ;
        $sec = " " ;
        $inputM = " " ;


        // print "<br> Input is ".$input." <br>";

        for ($i=0; $i < strlen($input); $i++)
        {
            $tempD = $input[$i];
             //print "<br> TempD [$i] is : $tempD";

            if ($tempD == iconv("UTF-8", "ISO-8859-1//TRANSLIT", '°') )
            {
                $newI = $i + 1 ;
                //print "<br> newI is : $newI";
                $inputM =  substr($input, $newI, -1) ;
                break;
            }//close if degree

            $deg .= $tempD ;
        }//close for degree

         //print "InputM is ".$inputM." <br>";

        for ($j=0; $j < strlen($inputM); $j++)
        {
            $tempM = $inputM[$j];
             //print "<br> TempM [$j] is : $tempM";

            if ($tempM == "'" or $tempM == iconv("UTF-8", "ISO-8859-1//TRANSLIT", '’'))
            {
                $newI = $j + 1 ;
                 //print "<br> newI is : $newI";
                $sec =  substr($inputM, $newI, -1) ;
                break;
             }//close if minute
             $min .= $tempM ;
        }//close for min

        $result =  $deg+( (( $min*60)+($sec) ) /3600 );

        return $deg + ($min / 60) + ($sec / 3600);
    }

    private static function convertDMSToDecimal($input)
    {
        $input = trim($input);
        if (preg_match('/^([0-9.-]+)[NSEWnsew]/', $input, $matches)) {
            $degrees = $matches[1];
            return $degrees;
        }
        if (!preg_match('/^(\d+)(°|º)\s*(\d+)(\'|’|′)?\s*(\w)/', $input, $matches)) {
            throw new Exception(sprintf('Invalid input: "%s"', $input));
        }
        $degree = $matches[1];
        $minutes = $matches[3];
        $dir = strtoupper($matches[5]);
        $result = $degree + ($minutes / 60);
        if ($dir == 'W' or $dir == 'S') {
            $result = -$result;
        }
        return $result;
    }

    private static function convertDMSToDecimal2($latlng) {
        echo "input: '$latlng'\n";
        $valid = false;
        $decimal_degrees = 0;
        $degrees = 0; $minutes = 0; $seconds = 0; $direction = 1;
        // Determine if there are extra periods in the input string
        $num_periods = substr_count($latlng, '.');
        if ($num_periods > 1) {
            $temp = preg_replace('/\./', ' ', $latlng, $num_periods - 1); // replace all but last period with delimiter
            $temp = trim(preg_replace('/[a-zA-Z]/','',$temp)); // when counting chunks we only want numbers
            $chunk_count = count(explode(" ",$temp));
            if ($chunk_count > 2) {
                $latlng = $temp; // remove last period
            } else {
                $latlng = str_replace("."," ",$latlng); // remove all periods, not enough chunks left by keeping last one
            }
        }

        // Remove unneeded characters
        $latlng = trim($latlng);
        $latlng = str_replace("º","",$latlng);
        $latlng = str_replace("'","",$latlng);
        $latlng = str_replace("\"","",$latlng);
        $latlng = substr($latlng,0,1) . str_replace('-', ' ', substr($latlng,1)); // remove all but first dash

        if ($latlng != "") {
            // DMS with the direction at the start of the string
            if (preg_match("/^([nsewNSEW]?)\s*(\d{1,3})\s+(\d{1,3})\s+(\d+\.?\d*)$/",$latlng,$matches)) {
                $valid = true;
                $degrees = intval($matches[2]);
                $minutes = intval($matches[3]);
                $seconds = floatval($matches[4]);
                if (strtoupper($matches[1]) == "S" || strtoupper($matches[1]) == "W")
                    $direction = -1;
            }
            // DMS with the direction at the end of the string
            if (preg_match("/^(-?\d{1,3})\s+(\d{1,3})\s+(\d+(?:\.\d+)?)\s*([nsewNSEW]?)$/",$latlng,$matches)) {
                $valid = true;
                $degrees = intval($matches[1]);
                $minutes = intval($matches[2]);
                $seconds = floatval($matches[3]);
                if (strtoupper($matches[4]) == "S" || strtoupper($matches[4]) == "W" || $degrees < 0) {
                    $direction = -1;
                    $degrees = abs($degrees);
                }
            }
            if ($valid) {
                // A match was found, do the calculation
                $decimal_degrees = ($degrees + ($minutes / 60) + ($seconds / 3600)) * $direction;
            } else {
                // Decimal degrees with a direction at the start of the string
                if (preg_match("/^(-?\d+(?:\.\d+)?)\s*([nsewNSEW]?)$/",$latlng,$matches)) {
                    $valid = true;
                    if (strtoupper($matches[2]) == "S" || strtoupper($matches[2]) == "W" || $degrees < 0) {
                        $direction = -1;
                        $degrees = abs($degrees);
                    }
                    $decimal_degrees = $matches[1] * $direction;
                }
                // Decimal degrees with a direction at the end of the string
                if (preg_match("/^([nsewNSEW]?)\s*(\d+(?:\.\d+)?)$/",$latlng,$matches)) {
                    $valid = true;
                    if (strtoupper($matches[1]) == "S" || strtoupper($matches[1]) == "W")
                        $direction = -1;
                    $decimal_degrees = $matches[2] * $direction;
                }
            }
        }
        if ($valid) {
            echo "output: '$decimal_degrees'\n";
            return $decimal_degrees;
        } else {
            echo "invalid\n";
            return false;
        }
    }

    protected function configure()
    {
        $this
            ->setName('app:scrap')
            ->setDescription('Scrap eratosthenes.eu')
            ->setHelp('Scrap')
        ;
    }

    private function getMonths()
    {
        $mname = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];
        $months =[];
        foreach(range(2005, 2017) as $year) {
        // foreach(range(2006, 2006) as $year) {
            foreach ($mname as $month) {
                $months[] = sprintf('%s %s', $year, $month);
            }
        }
        return $months;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        file_put_contents("output.csv", '');
        $this->output = $output;
        $this->client = new Client();
        $this->crawler = $this->client->request('GET', 'http://www.eratosthenes.eu/spip/');
        $links = [];
        foreach ($this->getMonths() as $month) {
            $linksCrawler = $this->crawler->selectLink($month);
            try {
                $link = $linksCrawler->link();
                $links[] = $link;
            } catch (InvalidArgumentException $e) {
            }
        }

        foreach($links as $link) {
            $this->processMonth($link);
        }

    }

    private function processMonth($link)
    {
        $output = $this->output;
        $title = null;

        $crawler = $this->client->click($link);
        $crawler->filter('h1')->each(function($node) use (&$title) {
            $title = $node->text();
        });

        $output->writeln(sprintf("<info>%s</info>", $title));
        preg_match('/(\d{4})/', $title, $matches);
        $year = $matches[1];

        $links = $crawler->selectLink('continue')->links();
        foreach($links as $link) {
            $this->processSchool($link, $year);
        }
    }

    private function processSchool($link, $year)
    {
        $output = $this->output;
        $count = 0;

        $crawler = $this->client->click($link);
        $crawler->filter('h1')->each(function($node) use ($output) {
            $output->writeln($node->text());
        });

        $offset = 0;
        $tds1 = $crawler->filter('table')->eq(0)->filter('tbody tr td');
        $city = trim($tds1->eq(1)->text());
        $country = trim($tds1->eq(2)->text());
        $school = trim($tds1->eq(3)->text());
        if (preg_match('/^([0-9.,-]+)$/', $school)) {
            $offset= -1;
            $school = '';
        }
        $lat = trim($tds1->eq($offset+4)->text());
        if (preg_match('/[NSEW]/i', $lat)) {
            $lat = preg_replace('/’/', '\'', $lat);
            $lat = self::convertDMSToDecimal($lat);
        }
        $lng = trim($tds1->eq($offset+5)->text());
        if (preg_match('/[NSEW]/i', $lng)) {
            $lng = preg_replace('/’/', '\'', $lng);
            $lng = self::convertDMSToDecimal($lng);
        }

        try {
            $trs = $crawler->filter('table')->eq(1)->filter('tbody tr');
            foreach ($trs as $tr) {
                $offset = 0;
                $ctr = new Crawler($tr);
                $tds = $ctr->filter('td');
                $monthdate = trim($tds->eq($offset+0)->text());
                if (preg_match('/average/i', $monthdate)) {
                    continue;
                }
                if (preg_match('/[x\/\+\.]/', $monthdate)) {
                    $offset = 1;
                    $monthdate = trim($tds->eq($offset+0)->text());
                }
                if (!preg_match('/[0-9]/', $monthdate)) {
                    $output->writeln(sprintf('<error>skipped: monthdate = "%s"</error>', $monthdate));
                    continue;
                }
                $monthdate = preg_replace('/octobe$/i', 'October', $monthdate);
                $datetext =  $monthdate . ' ' . $year;
                try {
                    $date = new DateTime($datetext);
                } catch (Exception $e) {
                    $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
                    continue;
                }
                $gnomon = trim($tds->eq($offset+1)->text());
                $gnomon = preg_replace('/ ?cm/', '', $gnomon);
                $gnomon = preg_replace('/,/', '.', $gnomon);
                $gnomon = trim($gnomon);

                $shadow = trim($tds->eq($offset+2)->text());
                $shadow = preg_replace('/ ?cm.*$/', '', $shadow);
                $shadow = preg_replace('/,/', '.', $shadow);
                $shadow = trim($shadow);

                $angle = trim($tds->eq($offset+3)->text());
                $angles = explode('=', $angle);
                if (isset($angles[0])) {
                    $angle1 = trim($angles[0]);
                } else {
                    $angle1 = '';
                }
                if (isset($angles[1])) {
                    $angle2 = trim($angles[1]);
                } else {
                    $angle2 = '';
                }

                if (preg_match('/[\\’]/', $angle1)) {
                    // echo $angle1 . "\n";
                    $angle1 = self::DMStoDD($angle1);
                    // echo $angle1 . "\n";
                }
                $angle1 = preg_replace('/[^0-9.]/', '', $angle1);

                if (preg_match('/[\\’]/', $angle2)) {
                    // echo $angle2 . "\n";
                    $angle2 = self::DMStoDD($angle2);
                    // echo $angle2 . "\n";
                }
                $angle2 = preg_replace('/[^0-9.]/', '', $angle2);

                $this->processData(
                    $city, $country, $school, $lat, $lng, $date, $gnomon, $shadow, $angle1, $angle2
                );
                $count++;
            }
        } catch (InvalidArgumentException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }
        if ($count == 0) {
            $output->writeln(sprintf('<error>Zero entries recorded!</error>'));
        } else {
            $output->writeln(sprintf('<question>%s entries recorded</question>', $count));
        }
    }

    private function processData($city, $country, $school, $lat, $lng, DateTime $date, $gnomon, $shadow, $angle1, $angle2)
    {
        $line = sprintf('"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"',
                preg_replace('/\"/', '', $city),
                preg_replace('/\"/', '', $country),
                preg_replace('/\"/', '', $school),
                $lat, $lng, $date->format('Y-m-d'), $gnomon, $shadow, $angle1, $angle2). "\n";
        file_put_contents("output.csv", $line, FILE_APPEND);
    }
}
