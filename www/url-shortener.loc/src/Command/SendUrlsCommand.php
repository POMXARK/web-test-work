<?php

namespace App\Command;

use App\Entity\Url;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Throwable;
use GuzzleHttp\Client;
use Tuupola\Base62Proxy as Base62;
/**
 *
 */
class SendUrlsCommand extends Command
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws InvalidArgumentException|GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $records = $this->em->getRepository(Url::class)->findAll();
        $out = [];
        $cache = new FilesystemAdapter();
        foreach ($records as $record) {
            $url = $record->getUrl();
            $cacheUrl = Base62::encode($url);
            try {
                if(!$cache->getItem($cacheUrl)->isHit()) {
                    $out[] = [  'url' => $url,
                                'created_date' => $record->getCreatedDate()->format('YmdHis')
                    ];
                    $cache->get($cacheUrl, function (ItemInterface $item) {
                        $item->expiresAfter(10); // 60
                    });
                }
            } catch (Throwable $e){
                dump($e);
            }
            // The callable will only be executed on a cache miss.
        }

        if(empty($out)) {
            dump('No new urls');
        } else {
            $data = '{"data": ' . json_encode($out) . '}';

            $client = new Client(['base_uri' => $_ENV['STATISTICS_SERVICE_URL'], 'timeout'  => 2.0]);
            $res = $client->request('POST', $this->route, ['body' => $data]);
            dump($res->getStatusCode());
            dump($res->getHeader('content-type')[0]);
            dump($res->getBody()->getContents());
        }

        return Command::SUCCESS;
    }

    public function setRoute(string $value)
    {
        $this->route = $value;
    }
}